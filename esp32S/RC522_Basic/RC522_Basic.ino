#include <SPI.h>
#define MFRC522_SPICLOCK (1000000u)
#include <MFRC522.h>

#define SS_PIN     5
#define RST_PIN    22
#define BUZZER_PIN 13

class MyMFRC522 : public MFRC522 {
public:
    using MFRC522::MFRC522;
    byte lastATQA[2] = {0, 0};

    // Override PICC_IsNewCardPresent untuk menyimpan ATQA tanpa mengubah perilaku aslinya
    bool PICC_IsNewCardPresent() override {
        byte bufferATQA[2] = {0, 0};
        byte bufferSize = sizeof(bufferATQA);

        PCD_WriteRegister(TxModeReg, 0x00);
        PCD_WriteRegister(RxModeReg, 0x00);
        PCD_WriteRegister(ModWidthReg, 0x26);

        StatusCode result = PICC_RequestA(bufferATQA, &bufferSize);
        if (result == STATUS_OK || result == STATUS_COLLISION) {
            lastATQA[0] = bufferATQA[0];
            lastATQA[1] = bufferATQA[1];
            return true;
        }
        return false;
    }
};

MyMFRC522 rfid(SS_PIN, RST_PIN);

unsigned long lastVersionPrint = 0;

void beepTone(int freq, int durationMs) {
    tone(BUZZER_PIN, freq, durationMs);
    delay(durationMs);
    noTone(BUZZER_PIN);
}

void beepCardRead() {
    beepTone(2200, 80);
    delay(60);
    beepTone(2500, 80);
}

void setup() {
    Serial.begin(115200);
    delay(1000);

    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    // Bip pendek saat boot
    beepTone(2000, 90);

    Serial.println("\n=== RC522 BASIC: PEMBACA RFID SEDERHANA ===");
    Serial.println("Pin: SCK=18, MISO=19, MOSI=23, SS=5, RST=22, BUZZER=13");

    // 1. Inisialisasi SPI 1 MHz, PCD_Init, RxGain_max
    SPI.begin(18, 19, 23, 5);
    SPI.setFrequency(1000000);
    rfid.PCD_Init();
    delay(40);
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);

    // 2. Tes tulis-baca register ModWidthReg (0x55 dan 0xAA)
    rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0x55);
    byte read1 = rfid.PCD_ReadRegister(MFRC522::ModWidthReg);

    rfid.PCD_WriteRegister(MFRC522::ModWidthReg, 0xAA);
    byte read2 = rfid.PCD_ReadRegister(MFRC522::ModWidthReg);

    if (read1 == 0x55 && read2 == 0xAA) {
        Serial.println("REGISTER OK");
    } else {
        Serial.println("REGISTER GAGAL");
    }

    // Re-init agar register ModWidthReg kembali normal
    rfid.PCD_Init();
    delay(40);
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);

    Serial.println("Siap membaca kartu (PICC_IsNewCardPresent & PICC_ReadCardSerial)...\n");
}

void loop() {
    unsigned long now = millis();

    // 3. Cetak VersionReg tiap 1 detik
    if (now - lastVersionPrint >= 1000) {
        lastVersionPrint = now;
        byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
        Serial.print("VersionReg: 0x");
        if (v < 0x10) {
            Serial.print("0");
        }
        Serial.println(v, HEX);
    }

    // 3. Pakai PICC_IsNewCardPresent dan PICC_ReadCardSerial
    if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
        String uidHex = "";
        for (byte i = 0; i < rfid.uid.size; i++) {
            if (rfid.uid.uidByte[i] < 0x10) {
                uidHex += "0";
            }
            uidHex += String(rfid.uid.uidByte[i], HEX);
            if (i < rfid.uid.size - 1) {
                uidHex += ":";
            }
        }
        uidHex.toUpperCase();

        char sakHex[5];
        sprintf(sakHex, "%02X", rfid.uid.sak);

        char atqaHex[7];
        sprintf(atqaHex, "%02X%02X", rfid.lastATQA[1], rfid.lastATQA[0]);

        // Cetak informasi kartu
        Serial.print("KARTU TERBACA, UID: "); Serial.println(uidHex);
        Serial.print("uid.size: "); Serial.println(rfid.uid.size);
        Serial.print("SAK: 0x"); Serial.println(sakHex);
        Serial.print("ATQA: 0x"); Serial.println(atqaHex);

        // Buzzer GPIO 13 dua bip pendek
        beepCardRead();

        // Halt & stop crypto
        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
        delay(300);
    }

    delay(20);
}
