/**
 * ================================================================================================
 * SMART RW - SISTEM PEMBACA & PEREKAM E-KTP DENGAN PENGATURAN WIFI DINAMIS (CAPTIVE PORTAL)
 * ================================================================================================
 * 
 * Perangkat Keras: NodeMCU ESP-32S + Sensor RFID MFRC522 (13.56 MHz)
 * 
 * FITUR UTAMA:
 * 1. KONEKSI DINAMIS (CAPTIVE PORTAL):
 *    - Tidak perlu hardcode SSID/Password di sketch!
 *    - Jika belum ada WiFi tersimpan atau koneksi gagal, ESP32 otomatis menyalakan Access Point (AP)
 *      bernama "SMART-RW-CONFIG-ESP32".
 *    - Sambungkan HP/Laptop ke WiFi ESP32 tersebut, halaman konfigurasi otomatis muncul (192.168.4.1).
 *    - Scan WiFi terdekat, pilih SSID, masukkan password, atur IP Server Laravel dan Device Token.
 *    - Tersimpan permanen di memori Flash NVS (Preferences) dan tidak hilang saat ESP32 dimatikan.
 *    - Tombol BOOT (GPIO 0) dapat ditekan selama 3 detik untuk reset WiFi kapan saja.
 * 
 * 2. PEMBACAAN E-KTP & KEAMANAN TOKEN ANTI-HACKER:
 *    - Membaca UID E-KTP (ISO 14443A / Mifare 13.56 MHz).
 *    - Mengirim payload ke Laravel beserta "X-Device-Token" untuk mencegah injeksi scan liar.
 *    - Menerima respon status akses warga dan membunyikan nada buzzer (Granted / Denied / Pending).
 * 
 * Library yang Dibutuhkan:
 *  1. MFRC522 by GithubCommunity
 *  2. ArduinoJson by Benoit Blanchon (v6 atau v7)
 *  (WiFi, WebServer, DNSServer, dan Preferences sudah bawaan dari core ESP32)
 * ================================================================================================
 */

#include <WiFi.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ================================================================================================
// 1. PINOUT HARDWARE (NODEMCU ESP-32S KE RFID-RC522)
// ================================================================================================
#define SS_PIN            5    // SDA pada RC522 -> GPIO 5
#define RST_PIN           22   // RST pada RC522 -> GPIO 22
#define BUZZER_PIN        13   // Buzzer (+)     -> GPIO 13
#define LED_GREEN_PIN     12   // LED Hijau (+)  -> GPIO 12 (via resistor 220 Ohm)
#define LED_RED_PIN       14   // LED Merah (+)  -> GPIO 14 (via resistor 220 Ohm)
#define RESET_CONFIG_PIN  0    // Tombol BOOT bawaan ESP-32S -> GPIO 0

// Inisialisasi Instance Driver
MFRC522 rfid(SS_PIN, RST_PIN);
Preferences preferences;
WebServer server(80);
DNSServer dnsServer;

// Variabel Konfigurasi Dinamis (Tersimpan di Flash NVS)
String wifi_ssid       = "";
String wifi_password   = "";
String server_scan_url = "http://192.168.1.100/api/rfid/scan";
String device_token    = "smart-rw-iot-secret-key-2026";
String device_id       = "ESP32-GATE-RW01";

// Status Sistem
bool isPortalActive = false;
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 2500;

const byte DNS_PORT = 53;
const char* AP_SSID = "SMART-RW-CONFIG-ESP32"; // Nama WiFi Hotspot ESP32 saat konfigurasi


// ================================================================================================
// 2. FUNGSI FEEDBACK INDIKATOR (BUZZER & LED)
// ================================================================================================

void toneBeep(int freq, int durationMs) {
    tone(BUZZER_PIN, freq, durationMs);
    delay(durationMs);
    noTone(BUZZER_PIN);
}

void feedbackSuccess() {
    digitalWrite(LED_GREEN_PIN, HIGH);
    toneBeep(1800, 100);
    delay(70);
    toneBeep(2400, 150);
    delay(600);
    digitalWrite(LED_GREEN_PIN, LOW);
}

void feedbackDenied() {
    digitalWrite(LED_RED_PIN, HIGH);
    toneBeep(450, 450);
    digitalWrite(LED_RED_PIN, LOW);
}

void feedbackCardDetected() {
    toneBeep(2000, 60);
}

void feedbackPending() {
    for (int i = 0; i < 3; i++) {
        digitalWrite(LED_RED_PIN, HIGH);
        toneBeep(1200, 70);
        digitalWrite(LED_RED_PIN, LOW);
        delay(70);
    }
}


// ================================================================================================
// 3. PENYIMPANAN NVS (PREFERENCES)
// ================================================================================================

void loadConfiguration() {
    preferences.begin("smart-rw", true); // Read-only mode
    wifi_ssid       = preferences.getString("ssid", "");
    wifi_password   = preferences.getString("pass", "");
    server_scan_url = preferences.getString("url", "http://192.168.1.100/api/rfid/scan");
    device_token    = preferences.getString("token", "smart-rw-iot-secret-key-2026");
    device_id       = preferences.getString("devid", "ESP32-GATE-RW01");
    preferences.end();

    Serial.println("\n[NVS] Konfigurasi Dimuat:");
    Serial.print(" - SSID Tersimpan : "); Serial.println(wifi_ssid.isEmpty() ? "(KOSONG)" : wifi_ssid);
    Serial.print(" - Server URL     : "); Serial.println(server_scan_url);
    Serial.print(" - Device Token   : "); Serial.println(device_token.substring(0, 5) + "******");
    Serial.print(" - Device ID      : "); Serial.println(device_id);
}

void saveConfiguration(String ssid, String pass, String url, String token, String devid) {
    preferences.begin("smart-rw", false); // Read/Write mode
    preferences.putString("ssid", ssid);
    preferences.putString("pass", pass);
    preferences.putString("url", url);
    preferences.putString("token", token);
    preferences.putString("devid", devid);
    preferences.end();
    Serial.println("[NVS] Konfigurasi berhasil disimpan ke Flash Memory!");
}

void resetConfiguration() {
    preferences.begin("smart-rw", false);
    preferences.clear();
    preferences.end();
    Serial.println("[NVS] Konfigurasi direset ke setelan awal pabrik.");
}


// ================================================================================================
// 4. CAPTIVE PORTAL (PENGATURAN WIFI DINAMIS VIA WEB BROWSER)
// ================================================================================================

String getHtmlPage(String scannedWifiOptions) {
    String html = "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'>";
    html += "<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>";
    html += "<title>Konfigurasi WiFi | SMART RW</title>";
    html += "<style>";
    html += ":root { --primary: #0e7a4b; --bg: #f7faf8; --surface: #ffffff; --ink: #0f2a1d; --border: #dbe9e0; }";
    html += "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: var(--ink); margin: 0; padding: 20px; display: flex; justify-content: center; }";
    html += ".card { background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 28px 22px; max-width: 440px; width: 100%; box-shadow: 0 10px 30px rgba(14,122,75,0.08); }";
    html += ".brand { display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 800; font-size: 1.3rem; margin-bottom: 6px; color: var(--ink); }";
    html += ".brand span { color: var(--primary); }";
    html += ".subtitle { text-align: center; font-size: 0.85rem; color: #4b6355; margin-bottom: 22px; }";
    html += "label { font-size: 0.82rem; font-weight: 700; display: block; margin-bottom: 6px; }";
    html += "select, input { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 0.92rem; margin-bottom: 16px; outline: none; background: #fff; }";
    html += "select:focus, input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,122,75,0.18); }";
    html += "button { width: 100%; background: linear-gradient(135deg, #0e7a4b, #0a5c38); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; margin-top: 8px; }";
    html += ".badge { display: inline-block; background: #e3f4ea; color: #0a5c38; font-size: 0.75rem; padding: 4px 10px; border-radius: 99px; font-weight: 700; margin-bottom: 16px; }";
    html += "</style></head><body><div class='card'>";
    html += "<div class='brand'>SMART <span>RW</span> (ESP-32S)</div>";
    html += "<div class='subtitle'>Konfigurasi Hotspot & Koneksi Server IoT</div>";
    html += "<form method='POST' action='/save'>";
    
    html += "<label>Pilih WiFi / Hotspot Terdekat</label>";
    html += "<select name='ssid' id='ssidSelect'>" + scannedWifiOptions + "<option value=''>-- Ketik Manual di Bawah --</option></select>";
    
    html += "<label>Atau Nama SSID Manual</label>";
    html += "<input type='text' name='manual_ssid' placeholder='Jika nama WiFi tidak muncul'>";
    
    html += "<label>Kata Sandi WiFi *</label>";
    html += "<input type='password' name='password' placeholder='Password Hotspot/WiFi'>";
    
    html += "<hr style='border: none; border-top: 1px dashed var(--border); margin: 18px 0;'>";
    
    html += "<label>URL Endpoint Laravel Server *</label>";
    html += "<input type='text' name='url' value='" + server_scan_url + "' placeholder='http://192.168.1.xxx/api/rfid/scan'>";
    
    html += "<label>Device Security Token (Anti-Hacker) *</label>";
    html += "<input type='text' name='token' value='" + device_token + "' placeholder='Token rahasia .env'>";
    
    html += "<label>ID Perangkat Scanner</label>";
    html += "<input type='text' name='devid' value='" + device_id + "' placeholder='ESP32-GATE-RW01'>";
    
    html += "<button type='submit'>Simpan & Hubungkan ke WiFi &rarr;</button>";
    html += "</form></div></body></html>";
    return html;
}

void startCaptivePortal() {
    isPortalActive = true;
    Serial.println("\n[PORTAL] Memulai Mode Akses Point (Captive Portal)...");

    WiFi.mode(WIFI_AP);
    WiFi.softAP(AP_SSID);
    delay(300);

    IPAddress apIP = WiFi.softAPIP();
    Serial.println("==================================================");
    Serial.println("         CAPTIVE PORTAL WIFI AKTIF                ");
    Serial.print("Nama Hotspot ESP32 : "); Serial.println(AP_SSID);
    Serial.print("Alamat IP Portal   : "); Serial.println(apIP);
    Serial.println("Buka browser di HP/Laptop lalu akses 192.168.4.1");
    Serial.println("==================================================");

    // DNS Server untuk mengarahkan semua domain ke IP ESP32 (Captive Portal Magic)
    dnsServer.start(DNS_PORT, "*", apIP);

    // Scan WiFi yang tersedia untuk diisi ke dropdown
    int n = WiFi.scanNetworks();
    String wifiOptions = "";
    if (n == 0) {
        wifiOptions = "<option value=''>Tidak ada WiFi terdeteksi</option>";
    } else {
        for (int i = 0; i < n; ++i) {
            String item = WiFi.SSID(i);
            int rssi = WiFi.RSSI(i);
            wifiOptions += "<option value='" + item + "'>" + item + " (" + String(rssi) + " dBm)</option>";
        }
    }

    server.on("/", [wifiOptions]() {
        server.send(200, "text/html", getHtmlPage(wifiOptions));
    });

    server.on("/save", HTTP_POST, []() {
        String reqSsid = server.arg("ssid");
        String manualSsid = server.arg("manual_ssid");
        String reqPass = server.arg("password");
        String reqUrl = server.arg("url");
        String reqToken = server.arg("token");
        String reqDevId = server.arg("devid");

        String finalSsid = manualSsid.length() > 0 ? manualSsid : reqSsid;

        if (finalSsid.length() > 0) {
            saveConfiguration(finalSsid, reqPass, reqUrl, reqToken, reqDevId);
            String successHtml = "<!DOCTYPE html><html><body style='font-family:sans-serif; text-align:center; padding:40px;'>";
            successHtml += "<h2 style='color:#0e7a4b;'>Pengaturan Berhasil Disimpan!</h2>";
            successHtml += "<p>ESP32 sedang me-restart dan menghubungkan ke WiFi: <b>" + finalSsid + "</b></p>";
            successHtml += "<p>Silakan kembalikan koneksi HP Anda ke WiFi semula.</p></body></html>";
            server.send(200, "text/html", successHtml);
            delay(1500);
            ESP.restart(); // Restart sistem agar booting menggunakan WiFi baru
        } else {
            server.send(400, "text/plain", "Nama SSID tidak boleh kosong!");
        }
    });

    // Menangani redirect captive portal di Android, iOS, Windows
    server.onNotFound([]() {
        server.sendHeader("Location", "http://192.168.4.1/", true);
        server.send(302, "text/plain", "");
    });

    server.begin();
}


// ================================================================================================
// 5. PENGIRIMAN DATA SCAN KE LARAVEL LENGKAP DENGAN TOKEN KEAMANAN
// ================================================================================================

void sendScanToLaravel(String uidClean, String uidFormatted) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[ERROR] WiFi terputus! Mencoba rekoneksi...");
        feedbackDenied();
        return;
    }

    HTTPClient http;
    http.begin(server_scan_url);

    // Header Keamanan & Konten
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.addHeader("X-Device-Token", device_token);       // Token Utama di Header
    http.addHeader("Authorization", "Bearer " + device_token);

    // Payload JSON
    StaticJsonDocument<300> doc;
    doc["uid"]          = uidClean;
    doc["device_id"]    = device_id;
    doc["device_token"] = device_token;

    String payload;
    serializeJson(doc, payload);

    Serial.println("\n--------------------------------------------------");
    Serial.print("[KIRIM KE LARAVEL] -> "); Serial.println(server_scan_url);
    Serial.print("Payload JSON: "); Serial.println(payload);

    int httpCode = http.POST(payload);

    if (httpCode > 0) {
        String resp = http.getString();
        Serial.print("HTTP Status Code: "); Serial.println(httpCode);
        Serial.print("Respon Server   : "); Serial.println(resp);

        StaticJsonDocument<1024> respDoc;
        DeserializationError err = deserializeJson(respDoc, resp);

        if (!err) {
            bool accessGranted = respDoc["access_granted"] | false;
            const char* accessMsg = respDoc["access_message"] | "";
            const char* buzzerSignal = respDoc["buzzer_signal"] | "DENIED";
            bool isRegistered = respDoc["is_registered"] | false;

            Serial.println();
            Serial.println("=========== HASIL VERIFIKASI SMART RW ===========");
            Serial.print("Izin Akses       : "); Serial.println(accessGranted ? "DIIZINKAN (GRANTED)" : "DITOLAK (DENIED)");
            Serial.print("Pesan Sistem     : "); Serial.println(accessMsg);
            Serial.print("Status Perekaman : "); Serial.println(isRegistered ? "Terdaftar" : "Belum Terdaftar (Mode Rekam Siap)");

            if (isRegistered && respDoc.containsKey("registered_to") && !respDoc["registered_to"].isNull()) {
                const char* nama = respDoc["registered_to"]["nama_lengkap"] | "-";
                const char* nik  = respDoc["registered_to"]["nik"] | "-";
                Serial.print("Identitas Warga  : "); Serial.print(nama);
                Serial.print(" (NIK: "); Serial.print(nik); Serial.println(")");
            }
            Serial.println("==================================================");

            if (String(buzzerSignal) == "GRANTED") {
                feedbackSuccess();
            } else if (String(buzzerSignal) == "PENDING") {
                feedbackPending();
            } else {
                feedbackDenied();
            }
        } else {
            feedbackDenied();
        }
    } else {
        Serial.print("[ERROR HTTP] Gagal terhubung ke Laravel. Kode: "); Serial.println(httpCode);
        feedbackDenied();
    }
    http.end();
}


// ================================================================================================
// 6. SETUP UTAMA
// ================================================================================================

void setup() {
    Serial.begin(115200);
    delay(1000);

    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(LED_GREEN_PIN, OUTPUT);
    pinMode(LED_RED_PIN, OUTPUT);
    pinMode(RESET_CONFIG_PIN, INPUT_PULLUP); // Tombol BOOT (GPIO 0)

    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_GREEN_PIN, LOW);
    digitalWrite(LED_RED_PIN, LOW);

    Serial.println("\n");
    Serial.println("==================================================");
    Serial.println("       SMART RW - IOT RFID SCANNER (ESP-32S)      ");
    Serial.println("  Sistem Pembaca E-KTP Dinamis & Anti-Hacker      ");
    Serial.println("==================================================");

    // 1. Muat konfigurasi dari memori flash
    loadConfiguration();

    // 2. Inisialisasi Bus SPI & Sensor RFID RC522
    SPI.begin(18, 19, 23, 5); // SCK=18, MISO=19, MOSI=23, SS=5
    rfid.PCD_Init();
    delay(40);

    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    Serial.print("Status Sensor RC522: 0x");
    Serial.print(v, HEX);
    if (v == 0x91 || v == 0x92) {
        Serial.println(" (NORMAL & SIAP BACA)");
    } else {
        Serial.println(" (PERIKSA KABEL JUMPER)");
    }

    // 3. Coba koneksi ke WiFi tersimpan
    if (wifi_ssid.length() > 0) {
        Serial.print("Mencoba konek ke WiFi: "); Serial.println(wifi_ssid);
        WiFi.mode(WIFI_STA);
        WiFi.begin(wifi_ssid.c_str(), wifi_password.c_str());

        int count = 0;
        while (WiFi.status() != WL_CONNECTED && count < 20) {
            delay(500);
            Serial.print(".");
            digitalWrite(LED_GREEN_PIN, !digitalRead(LED_GREEN_PIN));
            count++;
        }
        digitalWrite(LED_GREEN_PIN, LOW);

        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("\n[WIFI TERKONEKSI!]");
            Serial.print("IP ESP32 : "); Serial.println(WiFi.localIP());
            feedbackSuccess();
        } else {
            Serial.println("\n[WIFI GAGAL] Tidak dapat terhubung ke WiFi tersimpan.");
            startCaptivePortal();
        }
    } else {
        Serial.println("[WIFI KOSONG] Belum ada konfigurasi WiFi tersimpan.");
        startCaptivePortal();
    }
}


// ================================================================================================
// 7. LOOP UTAMA
// ================================================================================================

void loop() {
    // A. Mode Captive Portal: Layani DNS & Web Server jika sedang konfigurasi
    if (isPortalActive) {
        dnsServer.processNextRequest();
        server.handleClient();

        // Indikator LED kedip pelan saat mode portal aktif
        static unsigned long lastBlink = 0;
        if (millis() - lastBlink > 700) {
            digitalWrite(LED_RED_PIN, !digitalRead(LED_RED_PIN));
            lastBlink = millis();
        }
        return;
    }

    // B. Cek Tombol BOOT (GPIO 0): Tekan 3 detik untuk reset dan buka portal WiFi kembali
    if (digitalRead(RESET_CONFIG_PIN) == LOW) {
        unsigned long pressStart = millis();
        while (digitalRead(RESET_CONFIG_PIN) == LOW) {
            delay(50);
            if (millis() - pressStart > 3000) {
                // Tahan 3 detik
                Serial.println("\n[TOMBOL BOOT DITEKAN 3 DETIK] Masuk ke mode Captive Portal...");
                toneBeep(1500, 300);
                startCaptivePortal();
                return;
            }
        }
    }

    // C. Pembacaan Kartu RFID RC522 (E-KTP)
    if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
        delay(40);
        return;
    }

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

    // Debounce Cooldown
    unsigned long now = millis();
    if (uidClean == lastScannedUid && (now - lastScanTime < SCAN_COOLDOWN_MS)) {
        delay(80);
        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
        return;
    }

    lastScannedUid = uidClean;
    lastScanTime = now;

    feedbackCardDetected();

    Serial.println("\n==================================================");
    Serial.println("[KARTU FISIK E-KTP DITEMPEL]");
    Serial.print("UID Bersih    : "); Serial.println(uidClean);
    Serial.print("UID Terformat : "); Serial.println(uidFormatted);

    // Kirim UID dan Token ke Laravel
    sendScanToLaravel(uidClean, uidFormatted);

    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(200);
}
