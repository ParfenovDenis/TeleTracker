void writeLineOLED(int numLine,  const char line [])
{
  clearLineOLED(numLine);
#ifdef LCD_2004A
  lcd.setCursor(0, numLine);
#endif
#ifdef LCD_OLED_SSD1306
  display.setCursor(0, numLine * 8);
#endif
  printOLED(line);
}

void writeLineOLEDProgmem(int numLine,  const char *link)
{
  clearLineOLED(numLine);
  for (byte i = 0; i < strlen_P(link); i++) {
    if (i >= OLED_LINE_LENGTH)
      break;
    lineOLED[i] =  (char)pgm_read_byte(&link[i]);
  }
  printOLED(lineOLED);  
}

void clearLineOLED(int numLine)
{
#ifdef LCD_2004A
  lcd.setCursor(0, numLine);
  lcd.print("                    ");
#endif
#ifdef LCD_OLED_SSD1306
  uint8_t *  buffer =  display.getBuffer();
  int from = numLine * 128;
  int to = from + 128;
  for (int i = from; i < to; i++)
    buffer[i] = 0 ;
#endif
}



void printOLED (const char line [])
{
#ifdef LCD_2004A
  lcd.print(line);
#endif
#ifdef LCD_OLED_SSD1306
  display.println(line);
  display.display();
#endif
}


void refreshOLED() {
  if (getMillis() - refreshOLEDTimer > OLED_REFRESH_TIME)
  {
    if (oledGpsDataItem > 2)
      oledGpsDataItem = 0;

    //if (oledGpsDataItem == 0)

    sprintf(lineOLED, OLED_RPM,  int(convertToInt(p400) * 0.125), getMillis() / 1000, memoryFree());
    writeLineOLED(0, lineOLED);

    memset(lineOLED, '\0', OLED_LINE_LENGTH);
    switch (oledGpsDataItem)
    {
      case 0:
        sprintf(lineOLED, OLED_CSQ, rssi, ber, CSQ_TIMESTAMP / 1000);
        writeLineOLED(1, lineOLED);
        sprintf(lineOLED, "%d:%d:%d\0", hour, minute, second);
        writeLineOLED(2, lineOLED);
        break;
      case 1:
        sprintf(lineOLED, OLED_HTTP_LAST_REQUEST_TIME, HttpLastSuccessRequestTime, HttpLastRequestTime);
        writeLineOLED(1, lineOLED);
        sprintf(lineOLED, "%lu,%lu\0", latitude, longitude);
        writeLineOLED(2, lineOLED);
        break;
      case 2:
        sprintf(lineOLED, OLED_HTTP_REQUESTS_QTY, HttpSuccessRequestsQty, HttpTotalRequestsQty);
        writeLineOLED(1, lineOLED);
        sprintf(lineOLED, "%lu,%lu,%lu %ds\0", gps_satellites, gnss_satelites_used, CN0, gpsTimer / 1000);
        writeLineOLED(2, lineOLED);
        break;
    }
    oledGpsDataItem++;

    //sprintf(lineOLED, OLED_CSQ, rssi,ber);
    refreshOLEDTimer = getMillis();
  }
}

void initOLED()
{
#ifdef LCD_2004A
  lcd.init();
  lcd.backlight();
#endif
#ifdef LCD_OLED_SSD1306
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { // Address 0x3C for 128x32

    for (;;); // Don't proceed, loop forever
  }
  memset(lineOLED, '\0', OLED_LINE_LENGTH);
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);
  display.setCursor(0, 0);
#endif
}
