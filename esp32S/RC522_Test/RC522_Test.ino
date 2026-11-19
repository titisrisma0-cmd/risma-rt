#include <SPI.h>
#define MFRC522_SPICLOCK (1000000u)
#include <MFRC522.h>

#define SS_PIN     5
#define RST_PIN    22
#define BUZZER_PIN 13

MFRC522 rfid(SS_PIN, RST_PIN);

void beepTone(int freq, int durationMs) {
    tone(BUZZER_PIN, freq, durationMs);
    delay(durationMs);
    noTone(BUZZER_PIN);
}

// 5. Buzzer GPIO 13: 2 bip pendek saat kartu terbaca
void beepCardRead() {
    beepTone(2200, 80);
    delay(60);
    beepTone(2500, 80);
}

const char* statusToStr(MFRC522::StatusCode s) {
    switch (s) {
        case MFRC522::STATUS_OK:             return "OK";
        case MFRC522::STATUS_ERROR:          return "ERROR";
        case MFRC522::STATUS_COLLISION:      return "COLLISION";
        case MFRC522::STATUS_TIMEOUT:        return "TIMEOUT";
        case MFRC522::STATUS_NO_ROOM:        return "NO_ROOM";
        case MFRC522::STATUS_INTERNAL_ERROR: return "INTERNAL_ERROR";
        case MFRC522::STATUS_INVALID:        return "INVALID";
        case MFRC522::STATUS_CRC_WRONG:      return "CRC_WRONG";
        case MFRC522::STATUS_MIFARE_NACK:    return "MIFARE_NACK";
        default:                             return "UNKNOWN";
    }
}

void setup() {
    Serial.begin(115200);
    delay(1000);

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    // 1 bip pendek saat boot
    beepTone(2000, 90);

    Serial.println("\n=== RC522 TEST: DETEKSI E-KTP (ISO 14443-4, UID 7 BYTE) ===");
    Serial.println("Pin: SCK=18, MISO=19, MOSI=23, SS=5, RST=22, BUZZER=13");

    // 1. SPI 1 MHz, PCD_Init, RxGain_max tetap
    SPI.begin(18, 19, 23, 5);
    SPI.setFrequency(1000000);
    rfid.PCD_Init();
    delay(40);
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);

    // Perpanjang timer internal RC522 (~50 ms) agar chip CPU e-KTP cukup waktu merespons
    rfid.PCD_WriteRegister(MFRC522::TReloadRegH, 0x07);
    rfid.PCD_WriteRegister(MFRC522::TReloadRegL, 0xD0);

    // Pastikan antena aktif dan TxControlReg bernilai 0x03
    rfid.PCD_AntennaOn();
    rfid.PCD_WriteRegister(MFRC522::TxControlReg, 0x03);
    byte txVal = rfid.PCD_ReadRegister(MFRC522::TxControlReg);
    Serial.print("Inisialisasi Antena -> TxControlReg: 0x");
    if (txVal < 0x10) Serial.print("0");
    Serial.println(txVal, HEX);

    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    Serial.print("VersionReg: 0x");
    if (v < 0x10) Serial.print("0");
    Serial.println(v, HEX);

    Serial.println("Polling RequestA & WakeupA tiap 150 ms dimulai...\n");
}

void loop() {
    unsigned long now = millis();

    // 4. Setiap 5 detik matikan lalu nyalakan lagi antena (PCD_AntennaOff/On) dan pastikan TxControlReg bernilai 0x03
    static unsigned long lastAntennaReset = 0;
    if (now - lastAntennaReset >= 5000) {
        lastAntennaReset = now;

        rfid.PCD_AntennaOff();
        delay(10);
        rfid.PCD_AntennaOn();
        rfid.PCD_WriteRegister(MFRC522::TxControlReg, 0x03);
        byte txVal = rfid.PCD_ReadRegister(MFRC522::TxControlReg);

        Serial.print("[ANTENA 5S] Reset Antena -> TxControlReg: 0x");
        if (txVal < 0x10) Serial.print("0");
        Serial.print(txVal, HEX);
        if (txVal == 0x03) {
            Serial.println(" (OK 0x03)");
        } else {
            Serial.println(" (TxControlReg != 0x03)");
        }
    }

    // 2. Tiap 150 ms jalankan PICC_RequestA dan PICC_WakeupA
    static unsigned long lastPollTime = 0;
    if (now - lastPollTime >= 150) {
        lastPollTime = now;

        // Reset register modulasi sebelum polling
        rfid.PCD_WriteRegister(MFRC522::TxModeReg, 0x00);
        rfid.PCD_WriteRegister(MFRC522::RxModeReg, 0x00);
        rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0x26);

        // PICC_RequestA
        byte bufferATQA[2] = {0, 0};
        byte bufferSize = sizeof(bufferATQA);
        MFRC522::StatusCode reqStatus = rfid.PICC_RequestA(bufferATQA, &bufferSize);
        byte reqATQA[2] = {bufferATQA[0], bufferATQA[1]};

        // PICC_WakeupA
        bufferATQA[0] = 0;
        bufferATQA[1] = 0;
        bufferSize = sizeof(bufferATQA);
        delay(4);
        MFRC522::StatusCode wupStatus = rfid.PICC_WakeupA(bufferATQA, &bufferSize);
        byte wupATQA[2] = {bufferATQA[0], bufferATQA[1]};

        // Cetak status mentah satu baris ringkas HANYA ketika statusnya berubah
        static MFRC522::StatusCode lastReqStatus = (MFRC522::StatusCode) 999;
        static MFRC522::StatusCode lastWupStatus = (MFRC522::StatusCode) 999;

        if (reqStatus != lastReqStatus || wupStatus != lastWupStatus) {
            lastReqStatus = reqStatus;
            lastWupStatus = wupStatus;
            Serial.print("[STATUS] RequestA: ");
            Serial.print(statusToStr(reqStatus));
            Serial.print(" | WakeupA: ");
            Serial.println(statusToStr(wupStatus));
        }

        // 3. Jika ada kartu dan PICC_ReadCardSerial berhasil
        bool cardPresent = (reqStatus == MFRC522::STATUS_OK || reqStatus == MFRC522::STATUS_COLLISION ||
                            wupStatus == MFRC522::STATUS_OK || wupStatus == MFRC522::STATUS_COLLISION);

        if (cardPresent) {
            delay(5);
            bool readOk = rfid.PICC_ReadCardSerial();
            if (!readOk) {
                // Retry 1x untuk cascade level 2 e-KTP jika percobaan pertama belum selesai
                delay(5);
                byte retryBuf[2] = {0, 0};
                byte retryLen = sizeof(retryBuf);
                if (rfid.PICC_WakeupA(retryBuf, &retryLen) == MFRC522::STATUS_OK ||
                    rfid.PICC_WakeupA(retryBuf, &retryLen) == MFRC522::STATUS_COLLISION) {
                    delay(5);
                    readOk = rfid.PICC_ReadCardSerial();
                }
            }

            if (readOk) {
                // UID hex
                String uidHex = "";
                for (byte i = 0; i < rfid.uid.size; i++) {
                    if (rfid.uid.uidByte[i] < 0x10) uidHex += "0";
                    uidHex += String(rfid.uid.uidByte[i], HEX);
                    if (i < rfid.uid.size - 1) uidHex += ":";
                }
                uidHex.toUpperCase();

                // SAK hex
                char sakHex[5];
                sprintf(sakHex, "%02X", rfid.uid.sak);

                // ATQA hex (dari respon yang berhasil)
                byte atqa0 = (wupStatus == MFRC522::STATUS_OK || wupStatus == MFRC522::STATUS_COLLISION) ? wupATQA[0] : reqATQA[0];
                byte atqa1 = (wupStatus == MFRC522::STATUS_OK || wupStatus == MFRC522::STATUS_COLLISION) ? wupATQA[1] : reqATQA[1];
                char atqaHex[7];
                sprintf(atqaHex, "%02X%02X", atqa1, atqa0);

                // PICC_GetTypeName
                MFRC522::PICC_Type piccType = rfid.PICC_GetType(rfid.uid.sak);
                const __FlashStringHelper* typeName = rfid.PICC_GetTypeName(piccType);

                Serial.println("--------------------------------------------------");
                Serial.print("UID (HEX)    : "); Serial.println(uidHex);
                Serial.print("UID Size     : "); Serial.print(rfid.uid.size); Serial.println(" byte");
                Serial.print("SAK (hex)    : 0x"); Serial.println(sakHex);
                Serial.print("ATQA (hex)   : 0x"); Serial.println(atqaHex);
                Serial.print("Card Type    : "); Serial.println(typeName);
                Serial.println("--------------------------------------------------");

                // 5. Buzzer GPIO 13: 2 bip pendek saat kartu terbaca
                beepCardRead();

                // Hentikan komunikasi dengan kartu
                rfid.PICC_HaltA();
                rfid.PCD_StopCrypto1();

                // Reset last status agar transisi kartu dilepas terdeteksi
                lastReqStatus = (MFRC522::StatusCode) 999;
                lastWupStatus = (MFRC522::StatusCode) 999;

                delay(300);
            }
        }
    }

    delay(10);
}
