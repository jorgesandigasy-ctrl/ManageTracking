#include <TinyGPSPlus.h>
#include <SoftwareSerial.h>

#define GPS_RX_PIN 5  // D1
#define GPS_TX_PIN 4  // D2

SoftwareSerial gpsSerial(GPS_RX_PIN, GPS_TX_PIN);
TinyGPSPlus gps;

void setup() {
    Serial.begin(115200);
    gpsSerial.begin(9600);
    Serial.println("=== Test GPS NEO-6M ===");
}

void loop() {
    while (gpsSerial.available() > 0) {
        char c = gpsSerial.read();
        gps.encode(c);
        Serial.write(c); // muestra las tramas NMEA crudas
    }
}
