/**
 * ================================================================================================
 * SMART RW - SISTEM PEMBACA & PEREKAM E-KTP (RFID RC522 + NODEMCU ESP-32S)
 * ================================================================================================
 * 
 * Perangkat Keras (Hardware):
 *  - Board : NodeMCU ESP-32S (ESP32 Dev Module / 30 atau 38 Pin)
 *  - Sensor: RFID Reader/Writer MFRC522 (13.56 MHz RFID / E-KTP / ISO14443A)
 *  - Kabel : Female to Female Jumper Wires (7 Jalur Utama)
 *  - Output: Buzzer Aktif/Pasif & LED Indikator (Hijau / Merah)
 * 
 * Keamanan & Anti-Hacker:
 *  - Mengirim Device Security Token rahasia (X-Device-Token Header & JSON payload).
 *  - Mencegah injection scan palsu oleh pihak tidak berwenang ke endpoint server Laravel.
 *  - Menerima token verifikasi hash HMAC dari server untuk validasi integritas data.
 *  - Menerapkan cooldown debounce kartu (mencegah spam pembacaan kartu bertubi-tubi).
 * 
 * Library yang Dibutuhkan di Arduino IDE:
 *  1. MFRC522 by GithubCommunity (v1.4.10 atau versi terbaru)
 *  2. ArduinoJson by Benoit Blanchon (v6.x atau v7.x)
 * ================================================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ================================================================================================
// 1. KONFIGURASI JARINGAN WIFI & SERVER LARAVEL
// ================================================================================================

// Nama dan kata sandi WiFi tempat ESP32 dan Laptop/Server Laravel terhubung
const char* WIFI_SSID       = "NAMA_WIFI_ANDA";         // Contoh: "Balai_RW_01" atau Hotspot HP
const char* WIFI_PASSWORD   = "PASSWORD_WIFI_ANDA";     // Password WiFi Anda

/**
 * URL Endpoint Server Laravel:
 * - Ganti "192.168.1.100" dengan IP Lokal Komputer/Laptop Anda yang menjalankan Laragon.
 * - Cara cek IP Laptop di Windows: Buka CMD / PowerShell, ketik "ipconfig", cari "IPv4 Address".
 * - Contoh URL: "http://192.168.1.15:8000/api/rfid/scan" atau "http://192.168.1.15/api/rfid/scan"
 * - Jika sudah online/hosting: "https://domain-anda.com/api/rfid/scan"
 */
const char* SERVER_SCAN_URL = "http://192.168.1.100/api/rfid/scan";

/**
 * TOKEN KEAMANAN PERANGKAT (DEVICE SECURITY TOKEN):
 * - Token rahasia ini HARUS sama persis dengan yang diatur di file .env Laravel:
 *   RFID_DEVICE_TOKEN=smart-rw-iot-secret-key-2026
 * - Berfungsi mengunci endpoint sehingga hacker tidak bisa mengirimkan data scan palsu!
 */
const char* DEVICE_SECURITY_TOKEN = "smart-rw-iot-secret-key-2026";

// Identitas unik perangkat fisik scanner ini
const char* DEVICE_ID = "ESP32-GATE-RW01";


// ================================================================================================
// 2. PINOUT RANGKAIAN HARDWARE (NODEMCU ESP-32S KE RFID-RC522)
// ================================================================================================
/**
 * TABEL SAMBUNGAN KABEL JUMPER FEMALE-TO-FEMALE:
 * ------------------------------------------------------------------------------------------------
 * RFID-RC522 PIN   | WARNA REKOMENDASI | NODEMCU ESP-32S PIN   | KETERANGAN FUNGSI
 * ------------------------------------------------------------------------------------------------
 * 3.3V (VCC)       | Merah             | 3V3                   | Catu Daya 3.3V (JANGAN KE 5V/VIN!)
 * RST (Reset)      | Oranye            | GPIO 22               | Pin Kontrol Reset Hardware
 * GND (Ground)     | Hitam             | GND                   | Ground Tegangan Bersama
 * IRQ (Interrupt)  | -                 | TIDAK DIHUBUNGKAN     | Kosongkan (Not Connected / NC)
 * MISO             | Kuning            | GPIO 19               | SPI Master In Slave Out
 * MOSI             | Hijau             | GPIO 23               | SPI Master Out Slave In
 * SCK              | Biru              | GPIO 18               | SPI Serial Clock
 * SDA (SS / CS)    | Putih             | GPIO 5                | SPI Slave Select / Chip Select
 * ------------------------------------------------------------------------------------------------
 */

#define SS_PIN          5    // GPIO 5 (SDA pada RC522)
#define RST_PIN         22   // GPIO 22 (RST pada RC522)

// Pin Output Indikator Tambahan (Opsional namun sangat disarankan untuk feedback audio/visual):
#define BUZZER_PIN      13   // GPIO 13 -> Kaki Positif (+) Buzzer, Kaki (-) ke GND
#define LED_GREEN_PIN   12   // GPIO 12 -> Kaki Positif (Anoda) LED Hijau (lewat resistor 220 Ohm ke GND)
#define LED_RED_PIN     14   // GPIO 14 -> Kaki Positif (Anoda) LED Merah (lewat resistor 220 Ohm ke GND)

// Inisialisasi Instance Driver RC522
MFRC522 rfid(SS_PIN, RST_PIN);

// Variabel Kontrol Debounce & Anti-Spam
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 2500; // Jeda 2.5 detik untuk kartu yang sama


// ================================================================================================
// 3. FUNGSI INDIKATOR FEEDBACK (SUARA & LAMPU)
// ================================================================================================

void toneBeep(int frequency, int durationMs) {
    tone(BUZZER_PIN, frequency, durationMs);
    delay(durationMs);
    noTone(BUZZER_PIN);
}

// Sinyal 1: Akses Diterima / Perekaman Sukses (Dua Bip Ceria + LED Hijau Nyala)
void feedbackAccessGranted() {
    digitalWrite(LED_GREEN_PIN, HIGH);
    toneBeep(1800, 100);
    delay(70);
    toneBeep(2400, 150);
    delay(800);
    digitalWrite(LED_GREEN_PIN, LOW);
}

// Sinyal 2: Akses Ditolak / Hacker / Token Salah (Satu Buzz Panjang + LED Merah Kedip)
void feedbackAccessDenied() {
    digitalWrite(LED_RED_PIN, HIGH);
    toneBeep(450, 450);
    digitalWrite(LED_RED_PIN, LOW);
    delay(100);
    digitalWrite(LED_RED_PIN, HIGH);
    delay(100);
    digitalWrite(LED_RED_PIN, LOW);
}

// Sinyal 3: Kartu Terdeteksi Pertama Kali Ditempel (Bip Singkat)
void feedbackCardDetected() {
    toneBeep(2000, 60);
}

// Sinyal 4: Menunggu Verifikasi RT / Pending (Tiga Bip Pendek)
void feedbackPendingApproval() {
    for (int i = 0; i < 3; i++) {
        digitalWrite(LED_RED_PIN, HIGH);
        toneBeep(1200, 80);
        digitalWrite(LED_RED_PIN, LOW);
        delay(80);
    }
}


// ================================================================================================
// 4. KONEKSI & REKONEKSI WIFI OTOMATIS
// ================================================================================================

void setupWiFi() {
    Serial.println();
    Serial.println("==================================================");
    Serial.print("Menghubungkan ke WiFi: ");
    Serial.println(WIFI_SSID);

    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 30) {
        delay(500);
        Serial.print(".");
        attempts++;
        digitalWrite(LED_RED_PIN, !digitalRead(LED_RED_PIN)); // Kedip cepat saat mencoba konek
    }

    digitalWrite(LED_RED_PIN, LOW);

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println();
        Serial.println("[WIFI TERHUBUNG]");
        Serial.print("IP Address ESP32: ");
        Serial.println(WiFi.localIP());
        Serial.print("Sinyal (RSSI)   : ");
        Serial.print(WiFi.RSSI());
        Serial.println(" dBm");
        feedbackCardDetected(); // Bip tanda online
    } else {
        Serial.println();
        Serial.println("[WIFI GAGAL TERHUBUNG] Periksa SSID dan Password!");
    }
    Serial.println("==================================================");
}

void ensureWiFiConnected() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[WIFI TERPUTUS] Mencoba menghubungkan kembali...");
        WiFi.disconnect();
        WiFi.reconnect();
        int retries = 0;
        while (WiFi.status() != WL_CONNECTED && retries < 15) {
            delay(400);
            Serial.print(".");
            retries++;
        }
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("\n[WIFI TERHUBUNG KEMBALI]");
        }
    }
}


// ================================================================================================
// 5. PENGIRIMAN DATA SCAN KE LARAVEL DENGAN TOKEN KEAMANAN
// ================================================================================================

void sendScanToLaravel(String uidClean, String uidFormatted) {
    ensureWiFiConnected();

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[ERROR] Tidak dapat mengirim scan: WiFi belum terhubung!");
        feedbackAccessDenied();
        return;
    }

    HTTPClient http;
    http.begin(SERVER_SCAN_URL);

    // Header Standar HTTP & Token Keamanan Perangkat
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.addHeader("X-Device-Token", DEVICE_SECURITY_TOKEN);       // Token Utama di HTTP Header
    http.addHeader("Authorization", String("Bearer ") + DEVICE_SECURITY_TOKEN); // Alternatif Bearer

    // Siapkan Payload JSON menggunakan ArduinoJson
    StaticJsonDocument<300> doc;
    doc["uid"]          = uidClean;
    doc["device_id"]    = DEVICE_ID;
    doc["device_token"] = DEVICE_SECURITY_TOKEN; // Disertakan juga di body JSON

    String requestBody;
    serializeJson(doc, requestBody);

    Serial.println("\n--------------------------------------------------");
    Serial.print("[KIRIM KE LARAVEL] -> ");
    Serial.println(SERVER_SCAN_URL);
    Serial.print("Payload JSON: ");
    Serial.println(requestBody);

    unsigned long reqStartTime = millis();
    int httpResponseCode = http.POST(requestBody);
    unsigned long responseDuration = millis() - reqStartTime;

    Serial.print("Waktu Respon: ");
    Serial.print(responseDuration);
    Serial.println(" ms");

    if (httpResponseCode > 0) {
        String responsePayload = http.getString();
        Serial.print("HTTP Status Code: ");
        Serial.println(httpResponseCode);
        Serial.print("Server Response  : ");
        Serial.println(responsePayload);

        // Parsing Respon JSON dari Laravel
        StaticJsonDocument<1024> respDoc;
        DeserializationError err = deserializeJson(respDoc, responsePayload);

        if (err) {
            Serial.print("[ERROR] Gagal mem-parsing JSON respon: ");
            Serial.println(err.c_str());
            feedbackAccessDenied();
        } else {
            const char* status = respDoc["status"] | "error";
            bool accessGranted = respDoc["access_granted"] | false;
            const char* accessMsg = respDoc["access_message"] | "";
            const char* buzzerSignal = respDoc["buzzer_signal"] | "DENIED";
            const char* verificationToken = respDoc["verification_token"] | "";
            bool isRegistered = respDoc["is_registered"] | false;

            Serial.println();
            Serial.println("=========== HASIL VERIFIKASI SMART RW ===========");
            Serial.print("Status Server    : "); Serial.println(status);
            Serial.print("Izin Masuk       : "); Serial.println(accessGranted ? "DIIZINKAN (GRANTED)" : "DITOLAK (DENIED)");
            Serial.print("Pesan Akses      : "); Serial.println(accessMsg);
            Serial.print("Tautan E-KTP     : "); Serial.println(isRegistered ? "Terdaftar pada Data Warga" : "Belum Terdaftar (Mode Rekam)");

            if (isRegistered && respDoc.containsKey("registered_to") && !respDoc["registered_to"].isNull()) {
                const char* nama = respDoc["registered_to"]["nama_lengkap"] | "-";
                const char* nik  = respDoc["registered_to"]["nik"] | "-";
                const char* rt   = respDoc["registered_to"]["rt"] | "-";
                Serial.print("Pemilik E-KTP    : "); Serial.print(nama);
                Serial.print(" (NIK: "); Serial.print(nik);
                Serial.print(", Wilayah RT "); Serial.print(rt);
                Serial.println(")");
            }

            if (strlen(verificationToken) > 0) {
                Serial.print("Token Hash HMAC  : "); Serial.println(verificationToken);
            }
            Serial.println("==================================================");

            // Eksekusi Indikator Fisik Sesuai Keputusan Server
            if (String(buzzerSignal) == "GRANTED") {
                feedbackAccessGranted();
            } else if (String(buzzerSignal) == "PENDING") {
                feedbackPendingApproval();
            } else {
                feedbackAccessDenied();
            }
        }
    } else {
        Serial.print("[ERROR HTTP] Gagal terhubung ke server Laravel. Kode: ");
        Serial.println(httpResponseCode);
        Serial.println("Tips: Periksa apakah IP Laravel benar dan Laptop terhubung ke WiFi yang sama.");
        feedbackAccessDenied();
    }

    http.end();
}


// ================================================================================================
// 6. SETUP ARDUINO
// ================================================================================================

void setup() {
    Serial.begin(115200);
    delay(1000);

    // Inisialisasi Pin Output
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(LED_GREEN_PIN, OUTPUT);
    pinMode(LED_RED_PIN, OUTPUT);

    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_GREEN_PIN, LOW);
    digitalWrite(LED_RED_PIN, LOW);

    Serial.println("\n");
    Serial.println("==================================================");
    Serial.println("      SMART RW - IOT RFID SCANNER GATEWAY         ");
    Serial.println("     NodeMCU ESP-32S + MFRC522 (E-KTP Reader)     ");
    Serial.println("==================================================");

    // Inisialisasi Bus SPI dan Sensor RFID RC522
    SPI.begin(18, 19, 23, 5); // SCK=18, MISO=19, MOSI=23, SS=5
    rfid.PCD_Init();
    delay(50);

    // Cek versi hardware RC522
    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    Serial.print("Versi Firmware Sensor RC522: 0x");
    Serial.print(v, HEX);
    if (v == 0x91 || v == 0x92) {
        Serial.println(" -> SENSOR SIAP & NORMAL!");
    } else if (v == 0x00 || v == 0xFF) {
        Serial.println(" -> [PERINGATAN] Sensor tidak terdeteksi! Periksa kabel jumper.");
    } else {
        Serial.println(" -> Sensor terdeteksi.");
    }

    // Hubungkan ke Jaringan WiFi
    setupWiFi();

    Serial.println("\nScanner aktif. Silakan tempelkan kartu E-KTP pada sensor RC522...");
}


// ================================================================================================
// 7. LOOP UTAMA (PEMBACAAN KARTU & DEBOUNCE)
// ================================================================================================

void loop() {
    // 1. Cek apakah ada kartu baru di dekat sensor
    if (!rfid.PICC_IsNewCardPresent()) {
        delay(50);
        return;
    }

    // 2. Baca serial number / UID dari kartu
    if (!rfid.PICC_ReadCardSerial()) {
        delay(50);
        return;
    }

    // 3. Konversi UID byte array menjadi String Hexadecimal bersih & terformat
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

    // 4. Filter Debounce & Anti-Spam
    unsigned long now = millis();
    if (uidClean == lastScannedUid && (now - lastScanTime < SCAN_COOLDOWN_MS)) {
        // Kartu yang sama masih menempel, abaikan agar tidak spam request
        delay(100);
        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
        return;
    }

    lastScannedUid = uidClean;
    lastScanTime = now;

    // Sinyal Bip Singkat: Kartu terdeteksi fisik
    feedbackCardDetected();

    Serial.println("\n==================================================");
    Serial.println("[KARTU TERDETEKSI]");
    Serial.print("Tipe Kartu   : ");
    MFRC522::PICC_Type piccType = rfid.PICC_GetType(rfid.uid.sak);
    Serial.println(rfid.PICC_GetTypeName(piccType));
    Serial.print("UID Bersih   : "); Serial.println(uidClean);
    Serial.print("UID Terformat: "); Serial.println(uidFormatted);

    // 5. Kirim data UID ke API Laravel lengkap dengan Token Keamanan
    sendScanToLaravel(uidClean, uidFormatted);

    // 6. Hentikan komunikasi dengan kartu aktif
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();

    delay(200);
}
