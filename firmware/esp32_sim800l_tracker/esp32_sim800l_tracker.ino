// ESP32 + SIM800L — Tracker de ubicación por torres celulares
// Conexiones:
//   SIM800L TX  →  ESP32 GPIO16 (RX2)
//   SIM800L RX  →  ESP32 GPIO17 (TX2)
//   SIM800L VCC →  4.0V (5V del ESP32 con diodo 1N4007 en serie)
//   SIM800L GND →  GND del ESP32

#include <Arduino.h>
#include <Preferences.h>   // Guardar api_key en flash

// ─── CONFIGURACIÓN ──────────────────────────────────────────────────────────
#define APN       "claro.pe"   // Cambia según tu operadora
#define APN_USER  "claro"
#define APN_PASS  "claro"

#define SERVIDOR  "192.168.18.14"
#define PUERTO    80
#define PATH_REGISTRO  "/managetracking/api/registrar_dispositivo.php"
#define PATH_UBICACION "/managetracking/api/registrar_ubicacion.php"

#define INTERVALO_MS  3600000UL   // 1 hora en milisegundos
// ─────────────────────────────────────────────────────────────────────────────

HardwareSerial sim(2);   // Serial2: RX=GPIO16, TX=GPIO17
Preferences prefs;

String imei    = "";
String api_key = "";

// ─── UTILIDADES AT ───────────────────────────────────────────────────────────

String enviarAT(const char* cmd, uint32_t espera = 1000) {
    sim.println(cmd);
    delay(espera);
    String resp = "";
    while (sim.available()) resp += (char)sim.read();
    Serial.print("[SIM] "); Serial.println(resp);
    return resp;
}

bool esperarRespuesta(const char* esperado, uint32_t timeout = 15000) {
    String buf = "";
    uint32_t inicio = millis();
    while (millis() - inicio < timeout) {
        while (sim.available()) {
            buf += (char)sim.read();
            if (buf.indexOf(esperado) != -1) return true;
        }
    }
    Serial.print("[TIMEOUT esperando] "); Serial.println(esperado);
    return false;
}

// ─── INICIALIZACIÓN SIM800L ──────────────────────────────────────────────────

bool iniciarSIM() {
    Serial.println(">> Inicializando SIM800L...");
    for (int i = 0; i < 5; i++) {
        String r = enviarAT("AT");
        if (r.indexOf("OK") != -1) break;
        if (i == 4) { Serial.println("SIM800L no responde"); return false; }
        delay(1000);
    }
    enviarAT("ATE0");           // Desactivar echo
    enviarAT("AT+CMEE=2");      // Errores verbosos

    // Leer IMEI
    String r = enviarAT("AT+CGSN");
    r.trim();
    int nl = r.indexOf('\n');
    if (nl != -1) r = r.substring(0, nl);
    r.trim();
    imei = r;
    Serial.print("IMEI: "); Serial.println(imei);

    // Esperar registro en red
    Serial.println(">> Esperando red GSM...");
    for (int i = 0; i < 30; i++) {
        String reg = enviarAT("AT+CREG?", 500);
        if (reg.indexOf(",1") != -1 || reg.indexOf(",5") != -1) {
            Serial.println("Red GSM OK");
            return true;
        }
        delay(1000);
    }
    Serial.println("Sin red GSM");
    return false;
}

// ─── GPRS ────────────────────────────────────────────────────────────────────

bool conectarGPRS() {
    Serial.println(">> Conectando GPRS...");
    enviarAT("AT+SAPBR=0,1", 2000);   // Cerrar si estaba abierto
    enviarAT("AT+SAPBR=3,1,\"Contype\",\"GPRS\"");
    enviarAT(("AT+SAPBR=3,1,\"APN\",\""   + String(APN)      + "\"").c_str());
    enviarAT(("AT+SAPBR=3,1,\"USER\",\""  + String(APN_USER) + "\"").c_str());
    enviarAT(("AT+SAPBR=3,1,\"PWD\",\""   + String(APN_PASS) + "\"").c_str());
    enviarAT("AT+SAPBR=1,1", 8000);   // Abrir bearer

    String estado = enviarAT("AT+SAPBR=2,1");
    if (estado.indexOf("1,1") != -1) {
        Serial.println("GPRS conectado");
        return true;
    }
    Serial.println("GPRS fallido");
    return false;
}

// ─── UBICACIÓN (torres celulares) ────────────────────────────────────────────

bool obtenerUbicacion(float &lat, float &lng) {
    Serial.println(">> Obteniendo ubicación...");
    // AT+CIPGSMLOC=1,1  →  +CIPGSMLOC: 0,longitud,latitud,fecha,hora
    String resp = enviarAT("AT+CIPGSMLOC=1,1", 12000);

    int idx = resp.indexOf("+CIPGSMLOC: 0,");
    if (idx == -1) {
        Serial.println("Error obteniendo ubicación");
        return false;
    }

    String datos = resp.substring(idx + 14);
    int c1 = datos.indexOf(',');
    int c2 = datos.indexOf(',', c1 + 1);
    if (c1 == -1 || c2 == -1) return false;

    lng = datos.substring(0, c1).toFloat();
    lat = datos.substring(c1 + 1, c2).toFloat();

    Serial.printf("Ubicación: %.6f, %.6f\n", lat, lng);
    return (lat != 0.0 || lng != 0.0);
}

// ─── HTTP POST (via comandos AT+HTTP) ────────────────────────────────────────

String httpPost(const char* path, const String& body) {
    String url = "http://" + String(SERVIDOR) + ":" + String(PUERTO) + String(path);

    enviarAT("AT+HTTPTERM", 500);   // Limpiar sesión previa
    enviarAT("AT+HTTPINIT");
    enviarAT("AT+HTTPPARA=\"CID\",1");
    enviarAT(("AT+HTTPPARA=\"URL\",\"" + url + "\"").c_str());
    enviarAT("AT+HTTPPARA=\"CONTENT\",\"application/json\"");

    String dataCmd = "AT+HTTPDATA=" + String(body.length()) + ",10000";
    sim.println(dataCmd);
    delay(500);
    if (!esperarRespuesta("DOWNLOAD", 5000)) {
        Serial.println("Error: no recibí DOWNLOAD");
        enviarAT("AT+HTTPTERM");
        return "";
    }
    sim.print(body);
    delay(2000);

    enviarAT("AT+HTTPACTION=1", 10000);   // POST
    delay(3000);

    String respuesta = enviarAT("AT+HTTPREAD");
    enviarAT("AT+HTTPTERM");
    return respuesta;
}

// ─── REGISTRO DEL DISPOSITIVO ─────────────────────────────────────────────────

bool registrarDispositivo() {
    // Recuperar api_key guardada en flash
    prefs.begin("tracker", false);
    api_key = prefs.getString("api_key", "");

    String body = "{\"mac_address\":\"" + imei +
                  "\",\"hostname\":\"ESP32-" + imei +
                  "\",\"tipo\":\"gps\"}";

    Serial.println(">> Registrando dispositivo...");
    String resp = httpPost(PATH_REGISTRO, body);

    if (resp.indexOf("\"ok\":true") != -1) {
        int ki = resp.indexOf("\"api_key\":\"");
        if (ki != -1) {
            ki += 11;
            int ke = resp.indexOf("\"", ki);
            api_key = resp.substring(ki, ke);
            prefs.putString("api_key", api_key);
            Serial.print("API key: "); Serial.println(api_key);
        }
        prefs.end();
        return true;
    }

    prefs.end();
    Serial.println("Error registrando dispositivo");
    return false;
}

// ─── ENVÍO DE UBICACIÓN ───────────────────────────────────────────────────────

void enviarUbicacion(float lat, float lng) {
    String body = "{\"mac_address\":\"" + imei +
                  "\",\"api_key\":\"" + api_key +
                  "\",\"latitud\":"  + String(lat, 6) +
                  ",\"longitud\":"   + String(lng, 6) + "}";

    Serial.println(">> Enviando ubicación...");
    String resp = httpPost(PATH_UBICACION, body);

    if (resp.indexOf("\"ok\":true") != -1) {
        Serial.println("Ubicación enviada OK");
    } else {
        Serial.println("Error enviando ubicación: " + resp);
    }
}

// ─── CICLO PRINCIPAL ──────────────────────────────────────────────────────────

uint32_t ultimoEnvio = 0;

void setup() {
    Serial.begin(115200);
    sim.begin(9600, SERIAL_8N1, 16, 17);   // RX=16, TX=17
    delay(3000);
    Serial.println("\n=== ESP32 SIM800L Tracker ===");

    if (!iniciarSIM())     { Serial.println("FALLO: SIM"); return; }
    if (!conectarGPRS())   { Serial.println("FALLO: GPRS"); return; }
    if (!registrarDispositivo()) { Serial.println("FALLO: Registro"); return; }

    float lat, lng;
    if (obtenerUbicacion(lat, lng)) {
        enviarUbicacion(lat, lng);
    }
    ultimoEnvio = millis();
}

void loop() {
    if (millis() - ultimoEnvio >= INTERVALO_MS) {
        float lat, lng;
        if (obtenerUbicacion(lat, lng)) {
            enviarUbicacion(lat, lng);
        }
        ultimoEnvio = millis();
    }
}
