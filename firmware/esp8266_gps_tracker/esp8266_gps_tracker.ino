void setup() {
  // 1. IMPORTANTE: Pon el Monitor Serie en 115200 baudios
  Serial.begin(115200); 
  
  // 2. Velocidad del GPS (Casi siempre es 9600)
  // Si sigues viendo basura, cambia el 9600 por 38400
  Serial2.begin(38400, SERIAL_8N1, 16, 17); 
  
  Serial.println("\n--- Buscando señal NMEA ---");
}

void loop() {
  while (Serial2.available() > 0) {
    char c = Serial2.read();
    Serial.print(c); // Usamos print para que intente mostrar el texto
  }
}