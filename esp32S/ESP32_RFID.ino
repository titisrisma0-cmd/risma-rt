/**
 * ================================================================================================
 * NAMA FILE: ESP32_RFID.ino
 * SISTEM IOT SMART RW: PEMBACA RFID RC522 (E-KTP / SMART CARD) MODEL JSON
 * DENGAN AUTO-FALLBACK HOTSPOT "HotSpot_ESP32S" (TIMEOUT 3 DETIK)
 * ================================================================================================
 * 
 * ATURAN KERJA & SPESIFIKASI:
 * 1. Deteksi E-KTP / Kartu Elektronik (13.56 MHz):
 *    - Membaca kartu E-KTP RI, e-Toll, Flazz, e-Money, kartu akses Mifare/NFC.
 *    - Mengekstrak nomor UID dan tipe kartu.
 *    - Mengirimkan data dalam format model JSON standar ke Server Laravel.
 * 
 * 2. Auto-Fallback HotSpot 3 Detik:
 *    - Saat menyala, ESP32 mencoba mencari sinyal WiFi yang tersimpan selama MAKSIMAL 3 DETIK.
 *    - Jika dalam 3 detik belum terhubung ke WiFi, ESP32 otomatis beralih menjadi Access Point (AP):
 *        * Nama HotSpot : HotSpot_ESP32S
 *        * Password     : 12345678
 *        * Alamat IP    : 192.168.4.1
 * 
 * 3. Konfigurasi Dinamis (Captive Portal Web):
 *    - Pengguna menghubungkan HP/Laptop ke "HotSpot_ESP32S", membuka browser di 192.168.4.1.
 *    - Memilih WiFi/Hotspot terdekat & memasukkan password.
 *    - Mengatur IP / Name Server tujuan pengiriman data JSON (contoh: http://192.168.1.15/api/rfid/scan).
 *    - Mengatur Device Security Token anti-hacker dari Dashboard Super Admin.
 *    - Semua data disimpan ke memori Flash NVS (Preferences) dan tidak hilang saat mati listrik.
 * 
 * 4. Model Data JSON:
 *    - Mengirimkan payload JSON lengkap dengan Header "X-Device-Token".
 *    - Menerima respon JSON dari Laravel (izin masuk, nama warga, buzzer signal).
 * 
 * ================================================================================================
 */

#include <WiFi.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <SPI.h>
#define MFRC522_SPICLOCK (1000000u)
#include <MFRC522.h>
#include <ArduinoJson.h>

// ================================================================================================
// 1. PINOUT RANGKAIAN (NODEMCU ESP-32S KE RFID-RC522)
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
 * PIN INDIKATOR OUTPUT:
 * - Buzzer (+)       -> ESP32 GPIO 13 (Kaki (-) ke GND)
 * - LED Hijau (+)    -> ESP32 GPIO 12 (via resistor 220 Ohm ke GND)
 * - LED Merah (+)    -> ESP32 GPIO 14 (via resistor 220 Ohm ke GND)
 * - Tombol BOOT      -> ESP32 GPIO 0 (Tombol bawaan board untuk reset manual WiFi)
 */

#define SS_PIN            5    // SDA pada RC522 -> GPIO 5
#define RST_PIN           22   // RST pada RC522 -> GPIO 22
#define BUZZER_PIN        13   // Buzzer (+)     -> GPIO 13
#define LED_GREEN_PIN     12   // LED Hijau (+)  -> GPIO 12
#define LED_RED_PIN       14   // LED Merah (+)  -> GPIO 14
#define RESET_BUTTON_PIN  0    // Tombol BOOT bawaan ESP32 -> GPIO 0

// ================================================================================================
// 2. KONFIGURASI HOTSPOT & TIMEOUT WIFI
// ================================================================================================
const char* AP_SSID_NAME     = "HotSpot_ESP32S";   // Nama HotSpot sesuai permintaan
const char* AP_PASSWORD_VAL  = "12345678";         // Password default 8 karakter
const unsigned long WIFI_TIMEOUT_MS = 10000;       // Batas toleransi sinyal WiFi: 10 DETIK (10000 ms)

const byte DNS_PORT = 53;

// Inisialisasi Driver
MFRC522 rfid(SS_PIN, RST_PIN);
Preferences preferences;
WebServer server(80);
DNSServer dnsServer;

// Variabel Konfigurasi Dinamis (Disimpan di Memori Flash NVS ESP32)
String cfg_wifi_ssid       = "";
String cfg_wifi_pass       = "";
String cfg_server_url      = "http://192.168.1.6:8000/api/rfid/scan"; // Default IP/Name Server tujuan JSON
String cfg_device_token    = "";                                   // Token pengaman diisi dari Dashboard Super Admin (tidak hardcode)
String cfg_device_id       = "ESP32-GATE-RW01";

bool isHotSpotActive = false;
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 2500; // Debounce jeda 2.5 detik kartu yang sama

// Buffer ATQA dari scan terakhir
byte lastDetectedATQA[2] = {0, 0};


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
// 4. PENYIMPANAN MEMORI NON-VOLATILE FLASH (NVS PREFERENCES)
// ================================================================================================

void loadStoredConfig() {
    preferences.begin("smart-rfid", true); // Mode baca
    cfg_wifi_ssid    = preferences.getString("ssid", "");
    cfg_wifi_pass    = preferences.getString("pass", "");
    cfg_server_url   = preferences.getString("url", "http://192.168.1.6:8000/api/rfid/scan");
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
// 5. WEB PORTAL KONFIGURASI DINAMIS (HOTSPOT "HotSpot_ESP32S")
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
    
    html += "<label>IP / Name Server Tujuan JSON *</label>";
    html += "<input type='text' name='url' value='" + cfg_server_url + "' placeholder='http://192.168.1.xxx/api/rfid/scan' required>";
    html += "<div class='note'>Contoh: http://192.168.1.15/api/rfid/scan atau domain https://rw.id/api/rfid/scan</div>";
    
    html += "<label>Device Security Token (Anti-Hacker) *</label>";
    html += "<input type='text' name='token' value='" + cfg_device_token + "' placeholder='Tempel Token API dari Dashboard Super Admin' required>";
    html += "<div class='note'>Dapatkan token dari Dashboard Super Admin &rarr; Menu Pengaturan Token API</div>";
    
    html += "<label>ID Perangkat Scanner</label>";
    html += "<input type='text' name='devid' value='" + cfg_device_id + "' placeholder='ESP32-GATE-RW01'>";
    
    html += "<button type='submit'>Simpan & Hubungkan ke WiFi &rarr;</button>";
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
// 6. PENGIRIMAN DATA KARTU ELEKTRONIK KE SERVER MODEL JSON
// ================================================================================================

void sendCardJsonToServer(String uidClean, String uidFormatted, byte uidSize, String sakHex, String atqaHex, String cardType) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[ERROR] Tidak dapat mengirim JSON: WiFi terputus!");
        soundDenied();
        return;
    }

    HTTPClient http;
    http.begin(cfg_server_url);

    // Header HTTP Model JSON & Token Keamanan (tanpa token di body)
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.addHeader("X-Device-Token", cfg_device_token); // Header Token Otentikasi Anti-Hacker

    // Konstruksi JSON Model Payload Pengiriman
    StaticJsonDocument<512> jsonDoc;
    jsonDoc["uid"]           = uidClean;
    jsonDoc["formatted_uid"] = uidFormatted;
    jsonDoc["uid_size"]      = uidSize;
    jsonDoc["sak"]           = sakHex;
    jsonDoc["atqa"]          = atqaHex;
    jsonDoc["card_type"]     = cardType;
    jsonDoc["device_id"]     = cfg_device_id;
    jsonDoc["timestamp"]     = millis() / 1000;

    String jsonPayload;
    serializeJson(jsonDoc, jsonPayload);

    Serial.println("\n--------------------------------------------------");
    Serial.print("[PENGIRIMAN JSON KE SERVER] -> "); Serial.println(cfg_server_url);
    Serial.print("Model JSON: "); Serial.println(jsonPayload);

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
            const char* accessMsg    = respDoc["access_message"] | "";
            const char* buzzerSignal = respDoc["buzzer_signal"] | "DENIED";
            bool isRegistered        = respDoc["is_registered"] | false;
            const char* verifyToken  = respDoc["verification_token"] | "";

            Serial.println();
            Serial.println("=========== HASIL VERIFIKASI SERVER ===========");
            Serial.print("Status Masuk    : "); Serial.println(accessGranted ? "DIIZINKAN (GRANTED)" : "DITOLAK (DENIED)");
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

            // Buzzer GPIO 13: 2 bip pendek jika server membalas sukses (HTTP 2xx), 1 bip panjang jika gagal
            if (httpResponseCode >= 200 && httpResponseCode < 300) {
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
        Serial.print("[HTTP ERROR] Gagal menghubungi Server Laravel. Kode: "); Serial.println(httpResponseCode);
        Serial.println("Tips: Pastikan URL Server benar dan Laptop berada di jaringan WiFi yang sama.");
        soundDenied();
    }

    http.end();
}


// ================================================================================================
// 7. SETUP UTAMA
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
        if (v < 0x10) {
            Serial.print("0");
        }
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
// 8. DETEKSI KARTU ADAPTIF (MIFARE + E-KTP ISO 14443-A 7-BYTE UID) & LOOP UTAMA
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
