/**
 * ================================================================================================
 * NAMA FILE: ESP32_RFID.ino
 * SISTEM IOT SMART RW: PEMBACA RFID RC522 (E-KTP / SMART CARD) MODEL JSON
 * DENGAN AUTO-FALLBACK HOTSPOT "HotSpot_ESP32S" (TIMEOUT 10 DETIK)
 * ================================================================================================
 *
 * ATURAN KERJA & SPESIFIKASI:
 * 1. Deteksi E-KTP / Kartu Elektronik (13.56 MHz):
 *    - Membaca kartu E-KTP RI, e-Toll, Flazz, e-Money, kartu akses Mifare/NFC.
 *    - Mengekstrak nomor UID dan tipe kartu.
 *    - Mengirimkan data dalam format model JSON standar ke Server Laravel.
 *
 * 2. Auto-Fallback HotSpot 10 Detik:
 *    - Saat menyala, ESP32 mencoba mencari sinyal WiFi yang tersimpan selama MAKSIMAL 10 DETIK.
 *    - Jika dalam 10 detik belum terhubung ke WiFi, ESP32 otomatis beralih menjadi Access Point (AP):
 *        * Nama HotSpot : HotSpot_ESP32S
 *        * Password     : 12345678
 *        * Alamat IP    : 192.168.4.1
 *
 * 3. Konfigurasi Dinamis (Captive Portal Web):
 *    - Pengguna menghubungkan HP/Laptop ke "HotSpot_ESP32S", membuka browser di 192.168.4.1.
 *    - Memilih WiFi/Hotspot terdekat & memasukkan password.
 *    - Mengatur URL Server tujuan pengiriman data JSON.
 *    - Mengatur Device Security Token anti-hacker dari Dashboard Super Admin.
 *    - Semua data disimpan ke memori Flash NVS (Preferences) dan tidak hilang saat mati listrik.
 *
 * 4. Model Data JSON:
 *    - Mengirimkan payload JSON lengkap dengan Header "X-Device-Token".
 *    - Menerima respon JSON dari Laravel (izin masuk, nama warga, buzzer signal).
 *
 * 5. HTTPS Support:
 *    - Mendukung URL https:// dengan validasi sertifikat TLS penuh (Root CA: ISRG Root X1).
 *    - Mendukung URL http:// untuk jaringan lokal.
 *    - NTP time sync otomatis setelah WiFi tersambung (diperlukan untuk validasi sertifikat TLS).
 *    - ALLOW_INSECURE_TLS = false (setInsecure() TIDAK digunakan secara default).
 *
 * ================================================================================================
 */

// ---- Opsi Darurat Diagnosa TLS (JANGAN ubah ke true di produksi) ----
#define ALLOW_INSECURE_TLS false

#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <SPI.h>
#define MFRC522_SPICLOCK (1000000u)
#include <MFRC522.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <time.h>

// ================================================================================================
// 1. PINOUT RANGKAIAN (NODEMCU ESP-32S KE RFID-RC522 & MOTOR SERVO)
// ================================================================================================
/**
 * SAMBUNGAN KABEL JUMPER FEMALE-TO-FEMALE:
 * - RC522 3.3V (VCC) -> ESP32 3V3 (PERINGATAN: JANGAN KE 5V / VIN!)
 * - RC522 RST        -> ESP32 GPIO 22
 * - RC522 GND        -> ESP32 GND
 * - RC522 IRQ        -> (KOSONG / NOT CONNECTED)
 * - RC522 MISO       -> ESP32 GPIO 19
 * - RC522 MOSI       -> ESP32 GPIO 23
 * - RC522 SCK        -> ESP32 GPIO 18
 * - RC522 SDA (SS)   -> ESP32 GPIO 5
 *
 * PIN INDIKATOR OUTPUT & SERVO PALANG:
 * - Buzzer (+)       -> ESP32 GPIO 13 (Kaki (-) ke GND)
 * - Motor Servo      -> ESP32 GPIO 27 (Signal Oranye/Kuning; VCC 5V/VIN; GND ke GND)
 * - LED Hijau (+)    -> ESP32 GPIO 12 (via resistor 220 Ohm ke GND)
 * - LED Merah (+)    -> ESP32 GPIO 14 (via resistor 220 Ohm ke GND)
 * - Tombol BOOT      -> ESP32 GPIO 0 (Tombol bawaan board untuk reset manual WiFi)
 */

#define SS_PIN            5    // SDA pada RC522 -> GPIO 5
#define RST_PIN           22   // RST pada RC522 -> GPIO 22
#define BUZZER_PIN        13   // Buzzer (+)     -> GPIO 13
#define SERVO_PIN         27   // Servo Signal   -> GPIO 27 (sudut tutup 0°, sudut buka 90°)
#define LED_GREEN_PIN     12   // LED Hijau (+)  -> GPIO 12
#define LED_RED_PIN       14   // LED Merah (+)  -> GPIO 14
#define RESET_BUTTON_PIN  0    // Tombol BOOT bawaan ESP32 -> GPIO 0

#define SERVO_ANGLE_CLOSED  0
#define SERVO_ANGLE_OPEN    90

// ================================================================================================
// 2. KONFIGURASI HOTSPOT & TIMEOUT WIFI
// ================================================================================================
const char* AP_SSID_NAME     = "HotSpot_ESP32S";   // Nama HotSpot sesuai permintaan
const char* AP_PASSWORD_VAL  = "12345678";          // Password default 8 karakter
const unsigned long WIFI_TIMEOUT_MS = 10000;        // Batas toleransi sinyal WiFi: 10 DETIK

const byte DNS_PORT = 53;

// Inisialisasi Driver
MFRC522 rfid(SS_PIN, RST_PIN);
Preferences preferences;
WebServer server(80);
DNSServer dnsServer;
Servo gateServo;

// State Kontrol Servo Palang Gerbang (Non-Blocking millis)
bool isGateOpen = false;
unsigned long gateOpenStartMs = 0;
unsigned long gateOpenDurationMs = 5000;
unsigned long lastGatePollMs = 0;
const unsigned long GATE_POLL_INTERVAL_MS = 1500; // Polling perintah buka gerbang tiap 1.5 detik

// Variabel Konfigurasi Dinamis (Disimpan di Memori Flash NVS ESP32)
String cfg_wifi_ssid       = "";
String cfg_wifi_pass       = "";
String cfg_server_url      = "https://smartrw.mehape.com/api/rfid/scan"; // Default URL produksi
String cfg_device_token    = "";   // Token pengaman diisi dari Dashboard Super Admin (tidak hardcode)
String cfg_device_id       = "ESP32-GATE-RW01";

bool isHotSpotActive = false;
bool ntpSynced       = false;      // Status sinkronisasi NTP
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 2500; // Debounce jeda 2.5 detik kartu yang sama

// Buffer ATQA dari scan terakhir
byte lastDetectedATQA[2] = {0, 0};

// ================================================================================================
// ROOT CA CERTIFICATE BUNDLE (Let's Encrypt Root YR + ISRG Root X1/X2 + Google GTS Root R1/R4)
// Digunakan untuk validasi sertifikat TLS https://smartrw.mehape.com
// ================================================================================================
static const char ROOT_CA_BUNDLE[] PROGMEM = R"EOF(
-----BEGIN CERTIFICATE-----
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
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw
TzELMAkGA1UEBhMCVVMxKTAnBgNVBAoTIEludGVybmV0IFNlY3VyaXR5IFJlc2Vh
cmNoIEdyb3VwMRUwEwYDVQQDEwxJU1JHIFJvb3QgWDEwHhcNMTUwNjA0MTEwNDM4
WhcNMzUwNjA0MTEwNDM4WjBPMQswCQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJu
ZXQgU2VjdXJpdHkgUmVzZWFyY2ggR3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBY
MTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAK3oJHP0FDfzm54rVygc
h77ct984kIxuPOZXoHj3dcKi/vVqbvYATyjb3miGbESTtrFj/RQSa78f0uoxmyF+
0TM8ukj13Xnfs7j/EvEhmkvBioZxaUpmZmyPfjxwv60pIgbz5MDmgK7iS4+3mX6U
A5/TR5d8mUgjU+g4rk8Kb4Mu0UlXjIB0ttov0DiNewNwIRt18jA8+o+u3dpjq+sW
T8KOEUt+zwvo/7V3LvSye0rgTBIlDHCNNAymg4VMk7BPZ7hm/ELNKjD+Jo2FR3qy
HB5T0Y3HsLuJvW5iB4YlcNHlsdu87kGJ55tukmi8mxdAQ4Q7e2RCOFvu396j3x+U
CB5iPNgiV5+I3lg02dZ77DnKxHZu8A/lJBdiB3QW0KtZB6awBdpUKD9jf1b0SHzU
vKBds0pjBqAlkd25HN7rOrFleaJ1/ctaJxQZBKT5ZPt0m9STJEadao0xAH0ahmbW
nOlFuhjuefXKnEgV4We0+UXgVCwOPjdAvBbI+e0ocS3MFEvzG6uBQE3xDk3SzynT
njh8BCNAw1FtxNrQHusEwMFxIt4I7mKZ9YIqioymCzLq9gwQbooMDQaHWBfEbwrb
wqHyGO0aoSCqI3Haadr8faqU9GY/rOPNk3sgrDQoo//fb4hVC1CLQJ13hef4Y53C
IrU7m2Ys6xt0nUW7/vGT1M0NPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNV
HRMBAf8EBTADAQH/MB0GA1UdDgQWBBR5tFnme7bl5AFzgAiIyBpY9umbbjANBgkq
hkiG9w0BAQsFAAOCAgEAVR9YqbyyqFDQDLHYGmkgJykIrGF1XIpu+ILlaS/V9lZL
ubhzEFnTIZd+50xx+7LSYK05qAvqFyFWhfFQDlnrzuBZ6brJFe+GnY+EgPbk6ZGQ
3BebYhtF8GaV0nxvwuo77x/Py9auJ/GpsMiu/X1+mvoiBOv/2X/qkSsisRcOj/KK
NFtY2PwByVS5uCbMiogziUwthDyC3+6WVwW6LLv3xLfHTjuCvjHIInNzktHCgKQ5
ORAzI4JMPJ+GslWYHb4phowim57iaztXOoJwTdwJx4nLCgdNbOhdjsnvzqvHu7Ur
TkXWStAmzOVyyghqpZXjFaH3pO3JLF+l+/+sKAIuvtd7u+Nxe5AW0wdeRlN8NwdC
jNPElpzVmbUq4JUagEiuTDkHzsxHpFKVK7q4+63SM1N95R1NbdWhscdCb+ZAJzVc
oyi3B43njTOQ5yOf+1CceWxG1bQVs5ZufpsMljq4Ui0/1lvh+wjChP4kqKOJ2qxq
4RgqsahDYVvTH9w7jXbyLeiNdd8XM2w9U/t7y0Ff/9yi0GE44Za4rF2LN9d11TPA
mRGunUHBcnWEvgJBQl9nJEiU0Zsnvgc/ubhPgXRR4Xq37Z0j4r7g1SgEEzwxA57d
emyPxgcYxn/eR44/KJ4EBs+lVDR3veyJm+kXQ99b21/+jh5Xos1AnX5iItreGCc=
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIICGzCCAaGgAwIBAgIQQdKd0XLq7qeAwSxs6S+HUjAKBggqhkjOPQQDAzBPMQsw
CQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJuZXQgU2VjdXJpdHkgUmVzZWFyY2gg
R3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBYMjAeFw0yMDA5MDQwMDAwMDBaFw00
MDA5MTcxNjAwMDBaME8xCzAJBgNVBAYTAlVTMSkwJwYDVQQKEyBJbnRlcm5ldCBT
ZWN1cml0eSBSZXNlYXJjaCBHcm91cDEVMBMGA1UEAxMMSVNSRyBSb290IFgyMHYw
EAYHKoZIzj0CAQYFK4EEACIDYgAEzZvVn4CDCuwJSvMWSj5cz3es3mcFDR0HttwW
+1qLFNvicWDEukWVEYmO6gbf9yoWHKS5xcUy4APgHoIYOIvXRdgKam7mAHf7AlF9
ItgKbppbd9/w+kHsOdx1ymgHDB/qo0IwQDAOBgNVHQ8BAf8EBAMCAQYwDwYDVR0T
AQH/BAUwAwEB/zAdBgNVHQ4EFgQUfEKWrt5LSDv6kviejM9ti6lyN5UwCgYIKoZI
zj0EAwMDaAAwZQIwe3lORlCEwkSHRhtFcP9Ymd70/aTSVaYgLXTWNLxBo1BfASdW
tL4ndQavEi51mI38AjEAi/V3bNTIZargCyzuFJ0nN6T5U6VR5CmD1/iQMVtCnwr1
/q4AaOeMSQ+2b1tbFfLn
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIIFVzCCAz+gAwIBAgINAgPlk28xsBNJiGuiFzANBgkqhkiG9w0BAQwFADBHMQsw
CQYDVQQGEwJVUzEiMCAGA1UEChMZR29vZ2xlIFRydXN0IFNlcnZpY2VzIExMQzEU
MBIGA1UEAxMLR1RTIFJvb3QgUjEwHhcNMTYwNjIyMDAwMDAwWhcNMzYwNjIyMDAw
MDAwWjBHMQswCQYDVQQGEwJVUzEiMCAGA1UEChMZR29vZ2xlIFRydXN0IFNlcnZp
Y2VzIExMQzEUMBIGA1UEAxMLR1RTIFJvb3QgUjEwggIiMA0GCSqGSIb3DQEBAQUA
A4ICDwAwggIKAoICAQC2EQKLHuOhd5s73L+UPreVp0A8of2C+X0yBoJx9vaMf/vo
27xqLpeXo4xL+Sv2sfnOhB2x+cWX3u+58qPpvBKJXqeqUqv4IyfLpLGcY9vXmX7w
Cl7raKb0xlpHDU0QM+NOsROjyBhsS+z8CZDfnWQpJSMHobTSPS5g4M/SCYe7zUjw
TcLCeoiKu7rPWRnWr4+wB7CeMfGCwcDfLqZtbBkOtdh+JhpFAz2weaSUKK0Pfybl
qAj+lug8aJRT7oM6iCsVlgmy4HqMLnXWnOunVmSPlk9orj2XwoSPwLxAwAtcvfaH
szVsrBhQf4TgTM2S0yDpM7xSma8ytSmzJSq0SPly4cpk9+aCEI3oncKKiPo4Zor8
Y/kB+Xj9e1x3+naH+uzfsQ55lVe0vSbv1gHR6xYKu44LtcXFilWr06zqkUspzBmk
MiVOKvFlRNACzqrOSbTqn3yDsEB750Orp2yjj32JgfpMpf/VjsPOS+C12LOORc92
wO1AK/1TD7Cn1TsNsYqiA94xrcx36m97PtbfkSIS5r762DL8EGMUUXLeXdYWk70p
aDPvOmbsB4om3xPXV2V4J95eSRQAogB/mqghtqmxlbCluQ0WEdrHbEg8QOB+DVrN
VjzRlwW5y0vtOUucxD/SVRNuJLDWcfr0wbrM7Rv1/oFB2ACYPTrIrnqYNxgFlQID
AQABo0IwQDAOBgNVHQ8BAf8EBAMCAYYwDwYDVR0TAQH/BAUwAwEB/zAdBgNVHQ4E
FgQU5K8rJnEaK0gnhS9SZizv8IkTcT4wDQYJKoZIhvcNAQEMBQADggIBAJ+qQibb
C5u+/x6Wki4+omVKapi6Ist9wTrYggoGxval3sBOh2Z5ofmmWJyq+bXmYOfg6LEe
QkEzCzc9zolwFcq1JKjPa7XSQCGYzyI0zzvFIoTgxQ6KfF2I5DUkzps+GlQebtuy
h6f88/qBVRRiClmpIgUxPoLW7ttXNLwzldMXG+gnoot7TiYaelpkttGsN/H9oPM4
7HLwEXWdyzRSjeZ2axfG34arJ45JK3VmgRAhpuo+9K4l/3wV3s6MJT/KYnAK9y8J
ZgfIPxz88NtFMN9iiMG1D53Dn0reWVlHxYciNuaCp+0KueIHoI17eko8cdLiA6Ef
MgfdG+RCzgwARWGAtQsgWSl4vflVy2PFPEz0tv/bal8xa5meLMFrUKTX5hgUvYU/
Z6tGn6D/Qqc6f1zLXbBwHSs09dR2CQzreExZBfMzQsNhFRAbd03OIozUhfJFfbdT
6u9AWpQKXCBfTkBdYiJ23//OYb2MI3jSNwLgjt7RETeJ9r/tSQdirpLsQBqvFAnZ
0E6yove+7u7Y/9waLd64NnHi/Hm3lCXRSHNboTXns5lndcEZOitHTtNCjv0xyBZm
2tIMPNuzjsmhDYAPexZ3FL//2wmUspO8IFgV6dtxQ/PeEMMA3KgqlbbC1j+Qa3bb
bP6MvPJwNQzcmRk13NfIRmPVNnGuV/u3gm3c
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIICCTCCAY6gAwIBAgINAgPlwGjvYxqccpBQUjAKBggqhkjOPQQDAzBHMQswCQYD
VQQGEwJVUzEiMCAGA1UEChMZR29vZ2xlIFRydXN0IFNlcnZpY2VzIExMQzEUMBIG
A1UEAxMLR1RTIFJvb3QgUjQwHhcNMTYwNjIyMDAwMDAwWhcNMzYwNjIyMDAwMDAw
WjBHMQswCQYDVQQGEwJVUzEiMCAGA1UEChMZR29vZ2xlIFRydXN0IFNlcnZpY2Vz
IExMQzEUMBIGA1UEAxMLR1RTIFJvb3QgUjQwdjAQBgcqhkjOPQIBBgUrgQQAIgNi
AATzdHOnaItgrkO4NcWBMHtLSZ37wWHO5t5GvWvVYRg1rkDdc/eJkTBa6zzuhXyi
QHY7qca4R9gq55KRanPpsXI5nymfopjTX15YhmUPoYRlBtHci8nHc8iMai/lxKvR
HYqjQjBAMA4GA1UdDwEB/wQEAwIBhjAPBgNVHRMBAf8EBTADAQH/MB0GA1UdDgQW
BBSATNbrdP9JNqPV2Py1PsVq8JQdjDAKBggqhkjOPQQDAwNpADBmAjEA6ED/g94D
9J+uHXqnLrmvT/aDHQ4thQEd0dlq7A/Cr8deVl5c1RxYIigL9zC2L7F8AjEA8GE8
p/SgguMh1YQdc4acLa/KNJvxn7kjNuK8YAOdgLOaVsjh4rsUecrNIdSUtUlD
-----END CERTIFICATE-----
)EOF";

// ================================================================================================
// 3. INDIKATOR SUARA (BUZZER) & LAMPU (LED)
// ================================================================================================

void toneBeep(int freq, int durationMs) {
    tone(BUZZER_PIN, freq, durationMs);
    delay(durationMs);
    noTone(BUZZER_PIN);
}

void soundGranted() {
    digitalWrite(LED_GREEN_PIN, HIGH);
    toneBeep(1800, 100);
    delay(70);
    toneBeep(2400, 150);
    delay(600);
    digitalWrite(LED_GREEN_PIN, LOW);
}

void soundDenied() {
    digitalWrite(LED_RED_PIN, HIGH);
    toneBeep(450, 450);
    digitalWrite(LED_RED_PIN, LOW);
}

void soundCardTap() {
    toneBeep(2100, 60);
}

void soundPending() {
    for (int i = 0; i < 3; i++) {
        digitalWrite(LED_RED_PIN, HIGH);
        toneBeep(1200, 70);
        digitalWrite(LED_RED_PIN, LOW);
        delay(70);
    }
}

// ================================================================================================
// 3B. KONTROL MOTOR SERVO PALANG GERBANG (NON-BLOCKING)
// ================================================================================================

void bukaDanTutupOtomatis(int durasiDetik) {
    if (durasiDetik <= 0) durasiDetik = 5;
    gateServo.write(SERVO_ANGLE_OPEN);
    isGateOpen = true;
    gateOpenStartMs = millis();
    gateOpenDurationMs = (unsigned long)durasiDetik * 1000UL;
    digitalWrite(LED_GREEN_PIN, HIGH);
    Serial.print("[SERVO] Palang DIBUKA (");
    Serial.print(SERVO_ANGLE_OPEN);
    Serial.print(" deg) - menutup otomatis dalam ");
    Serial.print(durasiDetik);
    Serial.println(" detik.");
}

void handleServoAutoClose() {
    if (isGateOpen && (millis() - gateOpenStartMs >= gateOpenDurationMs)) {
        gateServo.write(SERVO_ANGLE_CLOSED);
        isGateOpen = false;
        digitalWrite(LED_GREEN_PIN, LOW);
        Serial.print("[SERVO] Timer selesai -> Palang DITUTUP otomatis (");
        Serial.print(SERVO_ANGLE_CLOSED);
        Serial.println(" deg).");
    }
}


// ================================================================================================
// 4. PENYIMPANAN MEMORI NON-VOLATILE FLASH (NVS PREFERENCES)
// ================================================================================================

void loadStoredConfig() {
    preferences.begin("smart-rfid", true); // Mode baca
    cfg_wifi_ssid    = preferences.getString("ssid", "");
    cfg_wifi_pass    = preferences.getString("pass", "");
    cfg_server_url   = preferences.getString("url", "https://smartrw.mehape.com/api/rfid/scan");
    cfg_device_token = preferences.getString("token", "");
    cfg_device_id    = preferences.getString("devid", "ESP32-GATE-RW01");
    preferences.end();

    Serial.println("\n[FLASH NVS] Memuat Data Konfigurasi Tersimpan:");
    Serial.print(" - WiFi SSID Target : "); Serial.println(cfg_wifi_ssid.isEmpty() ? "(Belum Ada)" : cfg_wifi_ssid);
    Serial.print(" - Server URL Target: "); Serial.println(cfg_server_url);
    Serial.print(" - Device Token     : "); Serial.println(cfg_device_token.isEmpty() ? "(Belum Diatur)" : "(Tersimpan - disembunyikan)");
    Serial.print(" - Device ID        : "); Serial.println(cfg_device_id);
}

void saveStoredConfig(String ssid, String pass, String url, String token, String devid) {
    preferences.begin("smart-rfid", false); // Mode tulis
    preferences.putString("ssid", ssid);
    preferences.putString("pass", pass);
    preferences.putString("url", url);
    preferences.putString("token", token);
    preferences.putString("devid", devid);
    preferences.end();
    Serial.println("[FLASH NVS] Pengaturan baru berhasil disimpan permanen ke memori ESP32!");
}


// ================================================================================================
// 5. SINKRONISASI WAKTU NTP (Wajib untuk validasi sertifikat TLS)
// ================================================================================================

void syncNtpTime() {
    Serial.println("\n[NTP] Memulai sinkronisasi waktu (Asia/Jakarta UTC+7)...");
    // Zona waktu Asia/Jakarta: UTC+7, tanpa daylight saving
    configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");

    const unsigned long NTP_TIMEOUT_MS = 15000;
    unsigned long startNtp = millis();
    time_t now = 0;

    while (millis() - startNtp < NTP_TIMEOUT_MS) {
        now = time(nullptr);
        if (now > 1700000000UL) { // Epoch > Nov 2023 -> waktu valid
            ntpSynced = true;
            break;
        }
        delay(300);
        Serial.print(".");
    }
    Serial.println();

    if (ntpSynced) {
        struct tm timeinfo;
        gmtime_r(&now, &timeinfo);
        char timeBuf[32];
        strftime(timeBuf, sizeof(timeBuf), "%Y-%m-%d %H:%M:%S", &timeinfo);
        Serial.print("[NTP] Waktu berhasil disinkronkan: ");
        Serial.print(timeBuf);
        Serial.println(" UTC");
    } else {
        Serial.println("[NTP] PERINGATAN: Gagal sinkronisasi waktu dalam batas timeout.");
        Serial.println("[NTP] Validasi sertifikat TLS mungkin gagal karena waktu tidak valid.");
    }
}


// ================================================================================================
// 6. WEB PORTAL KONFIGURASI DINAMIS (HOTSPOT "HotSpot_ESP32S")
// ================================================================================================

String generatePortalHTML(String wifiOptions) {
    String html = "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>";
    html += "<title>Pengaturan WiFi & Server | ESP32-RFID</title>";
    html += "<style>";
    html += ":root { --primary: #0e7a4b; --bg: #f7faf8; --card: #ffffff; --ink: #0f2a1d; --border: #dbe9e0; }";
    html += "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--ink); margin: 0; padding: 20px; display: flex; justify-content: center; }";
    html += ".card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 26px 20px; max-width: 440px; width: 100%; box-shadow: 0 10px 30px rgba(14,122,75,0.08); }";
    html += ".header { text-align: center; margin-bottom: 20px; }";
    html += ".title { font-weight: 800; font-size: 1.25rem; margin-bottom: 4px; color: var(--ink); }";
    html += ".title span { color: var(--primary); }";
    html += ".badge { display: inline-block; background: #e3f4ea; color: #0a5c38; font-size: 0.75rem; padding: 4px 10px; border-radius: 99px; font-weight: 700; margin-top: 6px; }";
    html += "label { font-size: 0.82rem; font-weight: 700; display: block; margin-bottom: 6px; }";
    html += "select, input { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 0.92rem; margin-bottom: 15px; outline: none; background: #fff; }";
    html += "select:focus, input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,122,75,0.18); }";
    html += "button { width: 100%; background: linear-gradient(135deg, #0e7a4b, #0a5c38); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; margin-top: 10px; }";
    html += ".note { font-size: 0.78rem; color: #6f8578; margin-top: -10px; margin-bottom: 14px; }";
    html += "</style></head><body><div class='card'>";
    html += "<div class='header'>";
    html += "<div class='title'>ESP32 RFID <span>SMART RW</span></div>";
    html += "<div class='badge'>HotSpot: HotSpot_ESP32S (192.168.4.1)</div>";
    html += "</div>";

    html += "<form method='POST' action='/save'>";
    html += "<label>Pilih WiFi / Hotspot Terdeteksi</label>";
    html += "<select name='ssid' id='ssidSelect'>" + wifiOptions + "<option value=''>-- Ketik Manual di Bawah --</option></select>";

    html += "<label>Atau Nama SSID Manual</label>";
    html += "<input type='text' name='manual_ssid' placeholder='Isi jika SSID disembunyikan'>";

    html += "<label>Kata Sandi WiFi *</label>";
    html += "<input type='password' name='password' placeholder='Password Hotspot / WiFi'>";

    html += "<hr style='border: none; border-top: 1px dashed var(--border); margin: 18px 0;'>";

    html += "<label>URL Server Tujuan JSON *</label>";
    html += "<input type='text' name='url' value='" + cfg_server_url + "' placeholder='https://smartrw.mehape.com/api/rfid/scan' required>";
    html += "<div class='note'>Contoh: https://smartrw.mehape.com/api/rfid/scan &nbsp;|&nbsp; http://192.168.1.15/api/rfid/scan</div>";

    html += "<label>Device Security Token (Anti-Hacker) *</label>";
    html += "<input type='text' name='token' value='' placeholder='Tempel Token API dari Dashboard Super Admin' required>";
    html += "<div class='note'>Dapatkan token dari Dashboard Super Admin &rarr; Menu Pengaturan Token API</div>";

    html += "<label>ID Perangkat Scanner</label>";
    html += "<input type='text' name='devid' value='" + cfg_device_id + "' placeholder='ESP32-GATE-RW01'>";

    html += "<button type='submit'>Simpan &amp; Hubungkan ke WiFi &rarr;</button>";
    html += "</form></div></body></html>";
    return html;
}

void activateHotspotPortal() {
    isHotSpotActive = true;
    Serial.println("\n[HOTSPOT AKTIF] WiFi belum tersambung! Masuk ke Mode HotSpot...");

    WiFi.mode(WIFI_AP);
    WiFi.softAP(AP_SSID_NAME, AP_PASSWORD_VAL);
    delay(200);

    IPAddress apIP = WiFi.softAPIP();
    Serial.println("==================================================");
    Serial.println("           HOTSPOT ESP32 DIAKTIFKAN               ");
    Serial.print("Nama HotSpot : "); Serial.println(AP_SSID_NAME);
    Serial.print("Password     : "); Serial.println(AP_PASSWORD_VAL);
    Serial.print("IP Address   : "); Serial.println(apIP);
    Serial.println("Sambungkan HP ke WiFi 'HotSpot_ESP32S' lalu buka 192.168.4.1");
    Serial.println("==================================================");

    // DNS Server untuk mengarahkan seluruh domain ke IP ESP32 (Captive Portal)
    dnsServer.start(DNS_PORT, "*", apIP);

    // Scan WiFi yang ada di sekitar
    int totalWifi = WiFi.scanNetworks();
    String wifiListHtml = "";
    if (totalWifi == 0) {
        wifiListHtml = "<option value=''>Tidak ada WiFi terdeteksi</option>";
    } else {
        for (int i = 0; i < totalWifi; ++i) {
            String s = WiFi.SSID(i);
            int r = WiFi.RSSI(i);
            wifiListHtml += "<option value='" + s + "'>" + s + " (" + String(r) + " dBm)</option>";
        }
    }

    server.on("/", [wifiListHtml]() {
        server.send(200, "text/html", generatePortalHTML(wifiListHtml));
    });

    server.on("/save", HTTP_POST, []() {
        String formSsid    = server.arg("ssid");
        String formManual  = server.arg("manual_ssid");
        String formPass    = server.arg("password");
        String formUrl     = server.arg("url");
        String formToken   = server.arg("token");
        String formDevId   = server.arg("devid");

        String chosenSsid = formManual.length() > 0 ? formManual : formSsid;

        if (chosenSsid.length() > 0) {
            saveStoredConfig(chosenSsid, formPass, formUrl, formToken, formDevId);

            String resHtml = "<!DOCTYPE html><html><body style='font-family:sans-serif; text-align:center; padding:40px;'>";
            resHtml += "<h2 style='color:#0e7a4b;'>Pengaturan Berhasil Disimpan!</h2>";
            resHtml += "<p>ESP32 sedang merestart untuk menghubungkan ke: <b>" + chosenSsid + "</b></p>";
            resHtml += "<p>Tujuan Data JSON: <code>" + formUrl + "</code></p>";
            resHtml += "<p>Silakan kembalikan koneksi HP Anda ke WiFi semula.</p></body></html>";

            server.send(200, "text/html", resHtml);
            delay(1500);
            ESP.restart(); // Restart agar boot dengan setelan WiFi baru
        } else {
            server.send(400, "text/plain", "Nama SSID tidak boleh kosong!");
        }
    });

    // Handle captive portal redirect
    server.onNotFound([]() {
        server.sendHeader("Location", "http://192.168.4.1/", true);
        server.send(302, "text/plain", "");
    });

    server.begin();
}


// ================================================================================================
// 7. PENGIRIMAN DATA KARTU ELEKTRONIK KE SERVER MODEL JSON (HTTP + HTTPS)
// ================================================================================================

void sendCardJsonToServer(String uidClean, String uidFormatted, byte uidSize, String sakHex, String atqaHex, String cardType) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[ERROR] Tidak dapat mengirim JSON: WiFi terputus!");
        soundDenied();
        return;
    }

    bool useHttps = cfg_server_url.startsWith("https://");

    // --- Diagnosa NTP jika HTTPS ---
    if (useHttps && !ntpSynced) {
        // Coba sinkronisasi ulang sebelum membatalkan
        time_t now = time(nullptr);
        if (now > 1700000000UL) {
            ntpSynced = true;
        } else {
            Serial.println("[TLS WARN] NTP belum tersinkron. Waktu sistem tidak valid.");
            Serial.println("[TLS WARN] Sertifikat TLS mungkin dianggap tidak valid.");
        }
    }

    HTTPClient http;
    http.setTimeout(10000); // Timeout 10 detik

    bool beginOk = false;
    WiFiClientSecure secureClient;
    WiFiClient plainClient;

    if (useHttps) {
        secureClient.setTimeout(10000); // 10000 ms = 10 detik
#if ALLOW_INSECURE_TLS
        secureClient.setInsecure();
        Serial.println("[TLS WARN] ALLOW_INSECURE_TLS aktif: sertifikat TIDAK divalidasi!");
#else
        secureClient.setCACert(ROOT_CA_BUNDLE);
#endif
        beginOk = http.begin(secureClient, cfg_server_url);
    } else {
        plainClient.setTimeout(10000);
        beginOk = http.begin(plainClient, cfg_server_url);
    }

    if (!beginOk) {
        Serial.println("[HTTP ERROR] http.begin() gagal. Periksa URL atau koneksi WiFi.");
        soundDenied();
        return;
    }

    // Header HTTP Model JSON & Token Keamanan (tanpa token di body)
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.addHeader("X-Device-Token", cfg_device_token); // Header Token Otentikasi

    // Konstruksi JSON Model Payload Pengiriman
    StaticJsonDocument<512> jsonDoc;
    jsonDoc["uid"]           = uidClean;
    jsonDoc["formatted_uid"] = uidFormatted;
    jsonDoc["uid_size"]      = uidSize;
    jsonDoc["sak"]           = sakHex;
    jsonDoc["atqa"]          = atqaHex;
    jsonDoc["card_type"]     = cardType;
    jsonDoc["device_id"]     = cfg_device_id;
    jsonDoc["timestamp"]     = (uint32_t)(time(nullptr));

    String jsonPayload;
    serializeJson(jsonDoc, jsonPayload);

    Serial.println("\n--------------------------------------------------");
    Serial.print("[PENGIRIMAN JSON KE SERVER] -> "); Serial.println(cfg_server_url);
    Serial.print("Protokol    : "); Serial.println(useHttps ? "HTTPS (TLS)" : "HTTP (Plaintext)");
    Serial.print("NTP Synced  : "); Serial.println(ntpSynced ? "YA" : "TIDAK");
    Serial.print("Model JSON  : "); Serial.println(jsonPayload);

    unsigned long startReq = millis();
    int httpResponseCode = http.POST(jsonPayload);
    unsigned long duration = millis() - startReq;

    Serial.print("[HTTP POST] Kode Status HTTP: "); Serial.println(httpResponseCode);
    Serial.print("Waktu Respon Server: "); Serial.print(duration); Serial.println(" ms");

    if (httpResponseCode > 0) {
        String responseBody = http.getString();
        Serial.print("HTTP Status Code   : "); Serial.println(httpResponseCode);
        Serial.print("Respon Balasan JSON: "); Serial.println(responseBody);

        // Parsing Respon Balasan JSON dari Laravel
        StaticJsonDocument<1024> respDoc;
        DeserializationError err = deserializeJson(respDoc, responseBody);

        if (!err) {
            bool accessGranted       = respDoc["access_granted"] | false;
            bool gateOpen            = respDoc["gate_open"] | false;
            int autoCloseSec         = respDoc["auto_close_seconds"] | 5;
            const char* accessMsg    = respDoc["access_message"] | "";
            const char* buzzerSignal = respDoc["buzzer_signal"] | "DENIED";
            bool isRegistered        = respDoc["is_registered"] | false;
            const char* verifyToken  = respDoc["verification_token"] | "";

            Serial.println();
            Serial.println("=========== HASIL VERIFIKASI SERVER ===========");
            Serial.print("Status Masuk    : "); Serial.println(accessGranted ? "DIIZINKAN (GRANTED)" : "DITOLAK (DENIED)");
            Serial.print("Status Palang   : "); Serial.println(gateOpen ? "BUKA OTOMATIS" : "TETAP TERTUTUP");
            Serial.print("Pesan Sistem    : "); Serial.println(accessMsg);
            Serial.print("Status Database : "); Serial.println(isRegistered ? "Kartu Terdaftar Warga" : "Kartu Baru (Mode Rekam Siap)");

            if (isRegistered && respDoc.containsKey("registered_to") && !respDoc["registered_to"].isNull()) {
                const char* nama = respDoc["registered_to"]["nama_lengkap"] | "-";
                const char* nik  = respDoc["registered_to"]["nik"] | "-";
                const char* rt   = respDoc["registered_to"]["rt"] | "-";
                Serial.print("Identitas Warga : "); Serial.print(nama);
                Serial.print(" (NIK: "); Serial.print(nik);
                Serial.print(", RT "); Serial.print(rt); Serial.println(")");
            }

            if (strlen(verifyToken) > 0) {
                Serial.print("HMAC Signature  : "); Serial.println(verifyToken);
            }
            Serial.println("==================================================");

            if (httpResponseCode >= 200 && httpResponseCode < 300) {
                if (gateOpen) {
                    bukaDanTutupOtomatis(autoCloseSec);
                }
                soundGranted();
            } else {
                soundDenied();
            }
        } else {
            if (httpResponseCode >= 200 && httpResponseCode < 300) {
                soundGranted();
            } else {
                soundDenied();
            }
        }

    } else {
        // Kode negatif: error jaringan / TLS
        Serial.print("[HTTP ERROR] Gagal menghubungi server. Kode: ");
        Serial.println(httpResponseCode);
        Serial.print("[HTTP ERROR] Deskripsi: ");
        Serial.println(http.errorToString(httpResponseCode).c_str());

        // Diagnosa TLS mendalam via lastError
        if (useHttps) {
            char tlsErrBuf[128];
            int tlsErrCode = secureClient.lastError(tlsErrBuf, sizeof(tlsErrBuf));
            Serial.print("[TLS ERROR] Kode Error mbedTLS: ");
            Serial.print(tlsErrCode);
            Serial.print(" (0x");
            Serial.print(tlsErrCode, HEX);
            Serial.println(")");
            Serial.print("[TLS ERROR] Pesan Detail     : ");
            Serial.println(tlsErrBuf);

            time_t nowCheck = time(nullptr);
            Serial.print("[DIAGNOSA TLS] Waktu sistem saat ini (epoch): ");
            Serial.println((unsigned long)nowCheck);
            if (nowCheck < 1700000000UL) {
                Serial.println("[DIAGNOSA TLS] -> PERINGATAN: Waktu NTP belum valid! Sertifikat TLS tidak dapat diverifikasi.");
            }
            Serial.println("[DIAGNOSA TLS] Periksa: DNS, koneksi internet, dan validitas sertifikat server.");
        }
        soundDenied();
    }

    http.end();
}

// Bangun URL endpoint GET /api/rfid/gate-command dari cfg_server_url
String buildGateCommandUrl() {
    String url = cfg_server_url;
    int scanIdx = url.indexOf("/api/rfid/scan");
    if (scanIdx >= 0) {
        url = url.substring(0, scanIdx) + "/api/rfid/gate-command";
    } else {
        int thirdSlash = url.indexOf('/', 8); // setelah https:// atau http://
        if (thirdSlash >= 0) {
            url = url.substring(0, thirdSlash) + "/api/rfid/gate-command";
        } else {
            url += "/api/rfid/gate-command";
        }
    }
    String encodedDevId = cfg_device_id;
    encodedDevId.replace(" ", "%20");
    url += "?device_id=" + encodedDevId;
    return url;
}

void pollGateCommand() {
    if (WiFi.status() != WL_CONNECTED || isHotSpotActive || isGateOpen) {
        return;
    }

    String gateUrl = buildGateCommandUrl();
    bool useHttps = gateUrl.startsWith("https://");

    HTTPClient http;
    http.setTimeout(5000);

    bool beginOk = false;
    WiFiClientSecure secureClient;
    WiFiClient plainClient;

    if (useHttps) {
        secureClient.setTimeout(5000);
#if ALLOW_INSECURE_TLS
        secureClient.setInsecure();
#else
        secureClient.setCACert(ROOT_CA_BUNDLE);
#endif
        beginOk = http.begin(secureClient, gateUrl);
    } else {
        plainClient.setTimeout(5000);
        beginOk = http.begin(plainClient, gateUrl);
    }

    if (!beginOk) {
        return;
    }

    http.addHeader("Accept", "application/json");
    http.addHeader("X-Device-Token", cfg_device_token);

    int code = http.GET();
    if (code == 200) {
        String body = http.getString();
        StaticJsonDocument<256> doc;
        if (!deserializeJson(doc, body)) {
            bool shouldOpen = doc["gate_open"] | false;
            int autoClose = doc["auto_close_seconds"] | 5;
            if (shouldOpen) {
                Serial.println("\n[REMOTE GATE] Perintah buka gerbang diterima dari Aplikasi Warga!");
                bukaDanTutupOtomatis(autoClose);
                soundGranted();
            }
        }
    }
    http.end();
}


// ================================================================================================
// 8. SETUP UTAMA
// ================================================================================================

String wifiStatusToString(wl_status_t st) {
    switch (st) {
        case WL_IDLE_STATUS:     return "WL_IDLE_STATUS (0)";
        case WL_NO_SSID_AVAIL:   return "WL_NO_SSID_AVAIL (1)";
        case WL_SCAN_COMPLETED:  return "WL_SCAN_COMPLETED (2)";
        case WL_CONNECTED:       return "WL_CONNECTED (3)";
        case WL_CONNECT_FAILED:  return "WL_CONNECT_FAILED (4)";
        case WL_CONNECTION_LOST: return "WL_CONNECTION_LOST (5)";
        case WL_DISCONNECTED:    return "WL_DISCONNECTED (6)";
        case WL_NO_SHIELD:       return "WL_NO_SHIELD (255)";
        default:                 return "WL_UNKNOWN (" + String((int)st) + ")";
    }
}

void setup() {
    Serial.begin(115200);
    delay(1000);

    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(LED_GREEN_PIN, OUTPUT);
    pinMode(LED_RED_PIN, OUTPUT);
    pinMode(RESET_BUTTON_PIN, INPUT_PULLUP); // Tombol BOOT (GPIO 0)

    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_GREEN_PIN, LOW);
    digitalWrite(LED_RED_PIN, LOW);

    // Inisialisasi Motor Servo Palang Pos (posisi awal tertutup 0 derajat)
    ESP32PWM::allocateTimer(0);
    gateServo.setPeriodHertz(50);
    gateServo.attach(SERVO_PIN, 500, 2400);
    gateServo.write(SERVO_ANGLE_CLOSED);

    Serial.println("\n");
    Serial.println("==================================================");
    Serial.println("     ESP32 RFID SCANNER - SISTEM SMART RW         ");
    Serial.println("     Pembaca E-KTP / Smart Card Model JSON        ");
    Serial.println("==================================================");

    // 1. Muat konfigurasi tersimpan dari Flash NVS
    loadStoredConfig();

    // 2. Inisialisasi Bus SPI (1 MHz) & Sensor RFID RC522
    SPI.begin(18, 19, 23, 5); // SCK=18, MISO=19, MOSI=23, SS=5
    SPI.setFrequency(1000000);
    rfid.PCD_Init();
    delay(40);

    // Tes tulis-baca register ModWidthReg (0x55 lalu 0xAA)
    rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0x55);
    byte regTest1 = rfid.PCD_ReadRegister(MFRC522::ModWidthReg);
    rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0xAA);
    byte regTest2 = rfid.PCD_ReadRegister(MFRC522::ModWidthReg);

    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    if (regTest1 == 0x55 && regTest2 == 0xAA) {
        Serial.print("SENSOR OK (VersionReg 0x");
        if (v < 0x10) Serial.print("0");
        Serial.print(v, HEX);
        Serial.println(")");
    } else {
        Serial.println("SAMBUNGAN RC522 BERMASALAH");
    }

    // Re-init agar register kembali normal, lalu atur gain antena maksimum & timeout lebih lega untuk E-KTP
    rfid.PCD_Init();
    delay(40);
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    // Perpanjang timer internal RC522 (~50 ms) agar CPU chip E-KTP (ISO 14443-4) cukup waktu merespons
    rfid.PCD_WriteRegister(MFRC522::TReloadRegH, 0x07);
    rfid.PCD_WriteRegister(MFRC522::TReloadRegL, 0xD0);

    // 3. Pengecekan WiFi dengan batas waktu WIFI_TIMEOUT_MS (10000 ms)
    bool wifiConnected = false;
    wl_status_t lastWifiStatus = WL_DISCONNECTED;

    if (cfg_wifi_ssid.length() > 0) {
        Serial.print("\n[WIFI] Mencoba menyambung ke SSID [");
        Serial.print(cfg_wifi_ssid);
        Serial.print("] (timeout ");
        Serial.print(WIFI_TIMEOUT_MS);
        Serial.println(" ms)...");

        WiFi.softAPdisconnect(true);
        WiFi.mode(WIFI_STA);
        WiFi.begin(cfg_wifi_ssid.c_str(), cfg_wifi_pass.c_str());

        unsigned long startCheck = millis();
        while (millis() - startCheck < WIFI_TIMEOUT_MS) {
            lastWifiStatus = WiFi.status();
            if (lastWifiStatus == WL_CONNECTED) {
                wifiConnected = true;
                break;
            }
            delay(100);
            digitalWrite(LED_GREEN_PIN, !digitalRead(LED_GREEN_PIN));
        }
        digitalWrite(LED_GREEN_PIN, LOW);
        lastWifiStatus = WiFi.status();
    } else {
        Serial.println("\n[WIFI] Belum ada SSID WiFi tersimpan di Flash NVS.");
        lastWifiStatus = WL_NO_SSID_AVAIL;
    }

    // Evaluasi hasil koneksi WiFi
    if (wifiConnected) {
        // Pastikan hotspot HotSpot_ESP32S dimatikan setelah WiFi tersambung
        WiFi.softAPdisconnect(true);
        WiFi.mode(WIFI_STA);
        isHotSpotActive = false;
        bool hotspotDimatikan = ((WiFi.getMode() & WIFI_AP) == 0);

        Serial.println("\n==================================================");
        Serial.println("[WIFI STATUS] Koneksi WiFi BERHASIL");
        Serial.print(" - Kode Status     : "); Serial.println(wifiStatusToString(lastWifiStatus));
        Serial.print(" - SSID Terhubung  : "); Serial.println(WiFi.SSID());
        Serial.print(" - IP Address      : "); Serial.println(WiFi.localIP());
        Serial.print(" - RSSI Sinyal     : "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");
        Serial.print(" - HotSpot_ESP32S  : ");
        Serial.println(hotspotDimatikan ? "DIMATIKAN (WiFi Mode STA saja)" : "MASIH AKTIF");
        Serial.println("==================================================");
        soundGranted();

        // 4. Sinkronisasi NTP (wajib untuk validasi sertifikat TLS HTTPS)
        if (cfg_server_url.startsWith("https://")) {
            syncNtpTime();
        } else {
            Serial.println("[NTP] Dilewati (URL server menggunakan HTTP plaintext).");
            ntpSynced = false;
        }

    } else {
        Serial.println("\n==================================================");
        Serial.println("[WIFI STATUS] Koneksi WiFi GAGAL");
        Serial.print(" - Kode Status     : "); Serial.println(wifiStatusToString(lastWifiStatus));
        Serial.print(" - Timeout         : "); Serial.print(WIFI_TIMEOUT_MS); Serial.println(" ms");
        Serial.println(" - IP Address      : 0.0.0.0 (Tidak terhubung)");
        Serial.println(" - RSSI Sinyal     : N/A");
        Serial.println(" - HotSpot_ESP32S  : DIAKTIFKAN (Fallback Mode Konfigurasi)");
        Serial.println("==================================================");
        activateHotspotPortal();
    }
}


// ================================================================================================
// 9. DETEKSI KARTU ADAPTIF (MIFARE + E-KTP ISO 14443-A 7-BYTE UID) & LOOP UTAMA
// ================================================================================================

bool detectAndSelectCard() {
    static const byte GAIN_LEVELS[] = {
        MFRC522::RxGain_max,   // 48 dB
        MFRC522::RxGain_43dB,  // 43 dB (seimbang untuk E-KTP)
        MFRC522::RxGain_38dB,  // 38 dB (mencegah saturasi saat E-KTP ditempel rapat)
        MFRC522::RxGain_33dB   // 33 dB (standar bawaan RC522)
    };
    static byte gainIdx = 0;
    static unsigned long lastDebugLog = 0;

    // Reset register baud & modulasi sebelum polling
    rfid.PCD_WriteRegister(MFRC522::TxModeReg, 0x00);
    rfid.PCD_WriteRegister(MFRC522::RxModeReg, 0x00);
    rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0x26);
    rfid.PCD_SetAntennaGain(GAIN_LEVELS[gainIdx]);
    gainIdx = (gainIdx + 1) % 4;

    byte bufferATQA[2] = {0, 0};
    byte bufferSize = sizeof(bufferATQA);

    // 1. Coba REQA dulu, jika belum ada respons coba WUPA (membangunkan E-KTP yang sempat HALT)
    MFRC522::StatusCode reqStatus = rfid.PICC_RequestA(bufferATQA, &bufferSize);
    if (reqStatus != MFRC522::STATUS_OK && reqStatus != MFRC522::STATUS_COLLISION) {
        bufferSize = sizeof(bufferATQA);
        reqStatus = rfid.PICC_WakeupA(bufferATQA, &bufferSize);
    }

    if (reqStatus != MFRC522::STATUS_OK && reqStatus != MFRC522::STATUS_COLLISION) {
        return false;
    }

    lastDetectedATQA[0] = bufferATQA[0];
    lastDetectedATQA[1] = bufferATQA[1];

    // 2. Beri jeda singkat agar CPU chip E-KTP stabil lalu lakukan PICC_Select (4/7/10-byte UID)
    delay(5);
    MFRC522::StatusCode selStatus = rfid.PICC_Select(&rfid.uid);
    if (selStatus == MFRC522::STATUS_OK) {
        return true;
    }

    // 3. Retry 1x dengan WUPA jika Cascade Level 2 (7-byte UID E-KTP) gagal di percobaan pertama
    delay(10);
    bufferSize = sizeof(bufferATQA);
    MFRC522::StatusCode retryReq = rfid.PICC_WakeupA(bufferATQA, &bufferSize);
    if (retryReq == MFRC522::STATUS_OK || retryReq == MFRC522::STATUS_COLLISION) {
        lastDetectedATQA[0] = bufferATQA[0];
        lastDetectedATQA[1] = bufferATQA[1];
        delay(5);
        selStatus = rfid.PICC_Select(&rfid.uid);
        if (selStatus == MFRC522::STATUS_OK) {
            return true;
        }
    }

    // Log diagnosa jika kartu merespons RF (ATQA) tetapi gagal saat Select UID
    if (millis() - lastDebugLog >= 1500) {
        lastDebugLog = millis();
        Serial.print("[DEBUG RC522] Kartu merespons ATQA (0x");
        if (bufferATQA[0] < 0x10) Serial.print("0");
        Serial.print(bufferATQA[0], HEX);
        Serial.print(" 0x");
        if (bufferATQA[1] < 0x10) Serial.print("0");
        Serial.print(bufferATQA[1], HEX);
        Serial.print(") tetapi PICC_Select gagal: ");
        Serial.println(rfid.GetStatusCodeName(selStatus));
    }

    return false;
}

void loop() {
    // Selalu periksa timer non-blocking untuk menutup palang servo otomatis
    handleServoAutoClose();

    // Mode A: Jika sedang dalam Mode HotSpot Portal
    if (isHotSpotActive) {
        dnsServer.processNextRequest();
        server.handleClient();

        // Indikator LED kedip pelan saat mode HotSpot aktif
        static unsigned long lastBlink = 0;
        if (millis() - lastBlink > 600) {
            digitalWrite(LED_RED_PIN, !digitalRead(LED_RED_PIN));
            lastBlink = millis();
        }
        return;
    }

    // Mode A2: Polling berkala perintah buka gerbang dari Aplikasi Warga (tiap 1.5 detik)
    if (WiFi.status() == WL_CONNECTED && (millis() - lastGatePollMs >= GATE_POLL_INTERVAL_MS)) {
        lastGatePollMs = millis();
        pollGateCommand();
    }

    // Mode B: Tombol BOOT (GPIO 0) ditekan 3 detik untuk paksa buka HotSpot
    if (digitalRead(RESET_BUTTON_PIN) == LOW) {
        unsigned long pressTime = millis();
        while (digitalRead(RESET_BUTTON_PIN) == LOW) {
            delay(50);
            if (millis() - pressTime > 3000) {
                Serial.println("\n[TOMBOL BOOT DITEKAN 3 DETIK] Masuk ke HotSpot Portal...");
                toneBeep(1600, 300);
                activateHotspotPortal();
                return;
            }
        }
    }

    // Mode C: Pembacaan Kartu Elektronik (E-KTP / RFID)
    if (!detectAndSelectCard()) {
        delay(40);
        return;
    }

    // Deteksi Tipe Kartu dari PICC_GetType
    MFRC522::PICC_Type piccType = rfid.PICC_GetType(rfid.uid.sak);
    String cardTypeName = String(rfid.PICC_GetTypeName(piccType));

    // Format SAK dan ATQA ke string hex
    char sakHex[5];
    sprintf(sakHex, "%02X", rfid.uid.sak);

    char atqaHex[7];
    sprintf(atqaHex, "%02X%02X", lastDetectedATQA[1], lastDetectedATQA[0]);

    // Konversi UID ke String Hexadecimal
    String uidClean = "";
    String uidFormatted = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) {
            uidClean += "0";
            uidFormatted += "0";
        }
        uidClean += String(rfid.uid.uidByte[i], HEX);
        uidFormatted += String(rfid.uid.uidByte[i], HEX);
        if (i < rfid.uid.size - 1) {
            uidFormatted += ":";
        }
    }
    uidClean.toUpperCase();
    uidFormatted.toUpperCase();

    // Filter Anti-Spam (Debounce 2.5 Detik)
    unsigned long now = millis();
    if (uidClean == lastScannedUid && (now - lastScanTime < SCAN_COOLDOWN_MS)) {
        delay(80);
        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
        return;
    }

    lastScannedUid = uidClean;
    lastScanTime = now;

    // Suara Bip Singkat saat kartu disentuhkan
    soundCardTap();

    Serial.println("\n==================================================");
    Serial.println("[KARTU ELEKTRONIK TERDETEKSI]");
    Serial.print("UID (HEX)    : "); Serial.println(uidFormatted);
    Serial.print("UID Bersih   : "); Serial.println(uidClean);
    Serial.print("UID Size     : "); Serial.print(rfid.uid.size); Serial.println(" byte");
    Serial.print("SAK          : 0x"); Serial.println(sakHex);
    Serial.print("ATQA         : 0x"); Serial.println(atqaHex);
    Serial.print("Tipe Kartu   : "); Serial.println(cardTypeName);

    // Kirim Payload Model JSON ke Server Laravel (tanpa token di body)
    sendCardJsonToServer(uidClean, uidFormatted, rfid.uid.size, String(sakHex), String(atqaHex), cardTypeName);

    // Hentikan komunikasi dengan kartu saat ini
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(200);
}
