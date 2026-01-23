
void USART_Init( unsigned int ubrr) {
  /* Set baud rate */
  UBRR1H = (unsigned char)(ubrr >> 8);
  UBRR1L = (unsigned char)ubrr;
  /* Enable receiver and transmitter */
  UCSR1B = (1 << RXEN1) | (1 << TXEN1);
  /* Set frame format: 8data, 2stop bit */
  UCSR1C = (0 << USBS1) | (3 << UCSZ10);
} // USART_Init


void USART_Init2( unsigned int ubrr) {
  /* Set baud rate */
  UBRR2H = (unsigned char)(ubrr >> 8);
  UBRR2L = (unsigned char)ubrr;
  /* Enable receiver and transmitter */
  UCSR2B = (1 << RXEN2) | (1 << TXEN2);
  /* Set frame format: 8data, 2stop bit */
  UCSR2C = (0 << USBS2) | (3 << UCSZ20);
} // USART_Init

void modeReceive()
{
  Log.atLogStr(String(getMillis()));
  MODE = MODE_RECEIVE;
  //waitingReceiveTimer = getMillis();
  receiveTimer = getMillis();
  Log.printPROGMEM(LOG_MODE_RECEIVE);

}
/*
void log5Long(unsigned long num)
{
  char str1[11];
  memset(str1, '\0', 11);
  sprintf(str1, "%lu", num);
  log5(str1);
}

void log5(char buff[])
{
  int i = 0;
  while (buff[i] != NULL) {
    Wire.beginTransmission(5);
    Wire.write(buff[i]);
    Wire.endTransmission();
    i++;
  }
  Wire.beginTransmission(5);
  Wire.write(13);
  Wire.write(10);
  Wire.endTransmission();
}*/

void boot()
{
  EEPROM.get( BOOT_ID_ADDRESS, bootId);
  bootId++;
  EEPROM.put( BOOT_ID_ADDRESS, bootId);
}

int memoryFree()
{
  int freeValue;
  if ((int)__brkval == 0)
    freeValue = ((int)&freeValue) - ((int)&__bss_end);
  else
    freeValue = ((int)&freeValue) - ((int)__brkval);
  return freeValue;
}

unsigned int convertToInt(unsigned char* pbytes)
{
  unsigned int n = 0;
  n = ((int)pbytes[1] << 8) | (int) pbytes[0];
  return n;
}


byte * convertToChar(unsigned long n)
{
  byte *pbytes = new  byte[4];
  pbytes[0] = (n >> 24);
  pbytes[1] = (n >> 16);
  pbytes[2] = (n >> 8);
  pbytes[3] = n;
  return pbytes;
}

void triggerSleep()
{
  Log.printPROGMEM(LOG_TRIGGER_SLEEP);
  MODE = MODE_SLEEP;
  SIM7000_STATE = SIM7000_NOT_READY;
  DATA_TRANSFER_MODE = OFF;
  HTTP_STATE = HTTP_STANDBY;
  // GPS_STATE = GPS_OFF;
  clearResponse();
  Log.term();
}

void triggerReboot()
{
  Log.printPROGMEM(LOG_TRIGGER_REBOOT);
  triggerSleep();
  reset();
}

void callTrigger()
{


  if (trigger == TRIGGER_REBOOT  )
    triggerReboot();
  if (trigger == TRIGGER_SLEEP)
    triggerSleep();

  trigger = TRIGGER_DEFAULT;
}




void powerOff() {
  Log.printPROGMEM(LOG_POWER_OFF);
  sprintf(lineOLED, "fp = %lu V = %u", fp, VERSION);
  writeLineOLED(1, lineOLED);
  sprintf(lineOLED, "bootId = %lu", bootId);
  writeLineOLED(2, lineOLED);
  writeLineOLEDProgmem(3, "POWER OFF");
  Log.term();
  digitalWrite(PIN_PH3, LOW);
  digitalWrite(PIN_PJ2, LOW);
  digitalWrite(PIN_PB6, LOW);

  while (1) {
    wdt_reset();
    digitalWrite(LED_BUILTIN, HIGH);
    delay(1000);
    digitalWrite(LED_BUILTIN, LOW);
    delay(1000);
  }
}

void checkPowerButton() {
  if (checkButton(BUTTON_POWER_PIN, pPowerTimer, BUTTON_POWER_PERIOD))
  {
    Log.printPROGMEM(LOG_CHECK_POWER_BUTTON);
    powerOff();
  }
}

void checkOledresetButton() {
  if (checkButton(BUTTON_OLED_RESET_PIN, pOledResetTimer, BUTTON_OLED_RESET_PERIOD))
    initOLED();
}



bool checkButton(const int buttonPin, unsigned long *buttonTimer, const unsigned int buttonPeriod) {
  bool pressed = false;
  int buttonState = digitalRead(buttonPin);
  if (buttonState == LOW) {
    if (*buttonTimer == 0)
      *buttonTimer = getMillis();
    else if (getMillis() - *buttonTimer > buttonPeriod)
    {
      pressed = true;

    }
  } else
    *buttonTimer = 0;
  return pressed;
}

#ifdef CANBUS_TRIGGERS
void onCanBusMessageReceive( unsigned int canId) {
  if (canId == 0x400)
  {
    if (sprintf(lineOLED, OLED_RPM, int(convertToInt(p400) * 0.125)))
    {
      writeLineOLED(0, lineOLED);
    }
  }
}
#endif

unsigned long getMillis()
{
  return millis();
}



/*
  void printParams(unsigned long param, char name_str [])
  {
  byte * chars = convertToChar(param);

  delete[] chars;
  }*/

void logging()
{
  if (NEW_DATA == true)
  {

    unsigned long millisec = getMillis();
    unsigned long memFree = memoryFree();
    byte * timestamp = convertToChar(millisec);
    byte * platitude = convertToChar(latitude);
    byte * plongitude = convertToChar(longitude);
    byte * paltitude = convertToChar(altitude);
    byte * pspeed = convertToChar(speed);
    byte * pcourse = convertToChar(course);
    byte * pgps_satellites = convertToChar(gps_satellites);
    byte * pgnss_satelites_used = convertToChar(gnss_satelites_used);
    byte * pglonass = convertToChar(glonass);
    byte * pCN0 = convertToChar(CN0);
    byte * pCGNSINF_TIMESTAMP = convertToChar(CGNSINF_TIMESTAMP);
    byte * pRssi = convertToChar(rssi);
    byte * pBer = convertToChar(ber);
    byte * pCSQ_TIMESTAMP = convertToChar(CSQ_TIMESTAMP);
    byte * pMemoryFree = convertToChar(memFree);
    //writeLineOLED(1, "");
    byte params [PARAMS_SIZE] = {  timestamp[0], timestamp[1], timestamp[2], timestamp[3], //0 - 3
                                   pFC21, // 4
                                   pF200[0], pF200[1], // 5 - 6
                                   p400[0], p400[1], // 7 - 8
                                   pEE00, // 9
                                   pC1EE[0], pC1EE[1], pC1EE[2], pC1EE[3], // 10 - 13
                                   p6CEE, // 14
                                   p10B, // 15
                                   p300, // 16
                                   p700B[0], p700B[1], // 17 - 18
                                   yearmonth, day, hour, minute, second,//19-23
                                   platitude[0], platitude[1], platitude[2], platitude[3], // 24-27
                                   plongitude[0], plongitude[1], plongitude[2], plongitude[3], // 28 -31
                                   paltitude[2], paltitude[3], pspeed[3], pcourse[2], pcourse[3], pgps_satellites[3], pgnss_satelites_used[3], pglonass[3], pCN0[3], // 32 - 40
                                   pCGNSINF_TIMESTAMP[0], pCGNSINF_TIMESTAMP[1], pCGNSINF_TIMESTAMP[2], pCGNSINF_TIMESTAMP[3], // 41 - 44
                                   pRssi[3], pBer[3], // 45 - 46
                                   pCSQ_TIMESTAMP[0], pCSQ_TIMESTAMP[1], pCSQ_TIMESTAMP[2], pCSQ_TIMESTAMP[3], // 47 - 50
                                   pMemoryFree[2], pMemoryFree[3] // 51 - 52
                                };
    /*
        printParams(millisec, "timestamp");
        printParams(latitude, "latitude");
        printParams(longitude, "longitude");
        printParams(altitude, "altitude");
        printParams(speed, "speed");
        printParams(course, "course");
        printParams(gps_satellites, "gps_satellites");
        printParams(gnss_satelites_used, "gnss_satelites_used");
        printParams(glonass, "glonass");
        printParams(CN0, "CN0");
        printParams(CGNSINF_TIMESTAMP, "CGNSINF_TIMESTAMP");
        printParams(rssi, "rssi");
        printParams(ber, "ber");
        printParams(CSQ_TIMESTAMP, "CSQ_TIMESTAMP");
        printParams(memFree, "memFree");*/

    if (( getMillis() - canLogTimer) > CAN_LOG_PERIOD )
    {

      Log.logCANBus(params, PARAMS_SIZE);
      canLogTimer = getMillis();
    }
    NEW_DATA = false;
    delete[] timestamp;
    delete[] platitude;
    delete[] plongitude;
    delete[] paltitude;
    delete[] pspeed;
    delete[] pcourse;
    delete[] pgps_satellites;
    delete[] pgnss_satelites_used;
    delete[] pglonass;
    delete[] pCN0;
    delete[] pCGNSINF_TIMESTAMP;
    delete[] pRssi;
    delete[] pBer;
    delete[] pCSQ_TIMESTAMP;
    delete[] pMemoryFree;
  }
}
