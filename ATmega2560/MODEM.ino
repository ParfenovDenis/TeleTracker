void setCommand()
{
  clearCommand();
  if (HTTP_STATE == HTTP_STANDBY && (getMillis() - lastCanMessageTimer > MAX_CAN_INACTIVITY_TIME))
  {
    Log.printPROGMEM(LOG_SLEEP);
    ModemSleep();
    return;
  }

  if (SIM7000_STATE == SIM7000_READY)
  {
    if (DATA_TRANSFER_MODE == OFF)
    {
      if (isValidIMEI() == true) {
        SIM7000_STATE = SIM7000_IMEI_READY;
      } else {
        strncpy(command, ATGSN, 7);
        Log.printVar(LOG_COMMAND, command);
        MODE = MODE_TRANSFER;
      }

      if ( getMillis() - signalQualityTimer > SIGNAL_QUALITY_PERIOD)
      {
        strncpy(command, ATCSQ, 7);
        Log.printVar(LOG_COMMAND, command);
        c = 0;
        MODE = MODE_TRANSFER;
        signalQualityTimer = getMillis();
        return;
      }

    }
  }
  else if (SIM7000_STATE == SIM7000_IMEI_READY)
  {

    if (HTTP_STATE == HTTP_STANDBY && (getMillis() - httpTimer > HTTP_PERIOD))
    {
      h = 0;
      HTTP_STATE = HTTP_INIT;// было: HTTP_STATE = HTTP_INIT;
    }
    if (HTTP_STATE != HTTP_STANDBY) {
      setCommandHTTP();
    }
  } else {
    strncpy(command, AT, 3);
    Log.printVar(LOG_COMMAND, command);
    repeatTimer = getMillis();
    MODE = MODE_TRANSFER;
  }
}

bool responseIs(int  addr) {
  int i = 0;
  bool isIdentical = true;
  while (pgm_read_byte(addr) != NULL) {
    if (response[i] != char(pgm_read_byte(addr)))
    {
      isIdentical = false;
      break;
    }
    i++;
    addr++;
  }
  return isIdentical;
}

void STANDBY()
{
  clearResponse();
  Log.printPROGMEM(LOG_STANDBY);
  MODE = MODE_STANDBY;
  receiveTimer = 0;
}

void clearResponse()
{
  r = 0;
  memset(response, '\0', MAX_RESPONSE_LENGTH);
}

void clearCommand()
{
  c = 0;
  memset(command, '\0', MAX_COMMAND_LENGTH);
}

void on()
{
  digitalWrite(PIN_PH2, HIGH);
  delay(500);
  digitalWrite(PIN_PH2, LOW);
  delay(1001);
  digitalWrite(PIN_PH2, HIGH);
  delay(2000);
}

void ModemPowerOff ()
{
  Log.printPROGMEM(LOG_MODEM_POWER_OFF);
  clearResponse();
  clearCommand();

  strncpy(command, ATCPOWD, 11);
  Log.printVar(LOG_COMMAND, command);
  MODE = MODE_TRANSFER;
  SIM7000_STATE = SIM7000_NOT_READY;
  //GPS_STATE = GPS_OFF;
  DATA_TRANSFER_MODE = OFF;
  HTTP_STATE = HTTP_STANDBY;
}

void ModemReboot()
{
  MaxReceiveTime = MAX_RECEIVE_TIME_POWER_DOWN;
  trigger = TRIGGER_REBOOT;
  ModemPowerOff();
}

void ModemSleep()
{
  trigger = TRIGGER_SLEEP;
  ModemPowerOff();
}

void setCSQ()
{

  int comma = 0;
  String rssi_str, ber_str;
  for (int i = 14; i < 20; i++)
  {
    if ( response[i] == ',')
    {
      comma++;
    }
    if (isDigit(response[i]))
    {
      if (comma == 0)
        rssi_str += response[i];
      if (comma == 1)
        ber_str += response[i];
    }
  }
  rssi = rssi_str.toInt();
  ber = ber_str.toInt();
  Log.printVar(LOG_RSSI, rssi_str);
  Log.printVar(LOG_BER, ber_str);

}

void strcpyCommand(int addr) {
  clearCommand();
  while (pgm_read_byte(addr) != NULL) {
    command[c++] = char(pgm_read_byte(addr));
    addr++;
  }
}

void strcpyCommand(const char* const * addr)
{
  uint16_t ptr = pgm_read_word(addr);
  clearCommand();
  while (pgm_read_byte(ptr) != NULL) {
    command[c++] = char(pgm_read_byte(ptr));
    ptr++;
  }

}

bool isError()
{
  bool res = true;
  char error_str[] = "ERROR\r\n";
  int j = 0;
  for (int i = r - 7; i < r; i++)
  {
    if (response[i] != error_str[j++])
    {
      res = false;
      break;
    }
  }
  return res;
}

bool isAT()
{
  return strncmp(response, "AT\r\r\n", 5) == 0 ? true : false;
}

bool isOK()
{
  //char response_last[5] = {response[r - 4], response[r - 3], response[r - 2], response[r - 1], '\0'};
  return  strstr(response, "\nOK\r\0") != NULL ? true : false;
  //return strcmp(response, "\nOK\r\n\0") == 0 ? true : false;
}

bool isATGSN()
{
  return strncmp(response, "AT+GSN", 6) == 0 ? true : false;
}

bool isCSQ()
{
  CSQ_TIMESTAMP = getMillis();
  return strncmp(response, "AT+CSQ", 6) == 0 ? true : false;
}

bool isPowerDown()
{
  return  strstr(response, "NORMAL POWER DOWN\0") != NULL ? true : false;
}

void setIMEI()
{
  int eepromImeiAddress = IMEI_ADDRES;
  for (int i = 0; i < MAX_RESPONSE_LENGTH; i++) {
    if (isDigit(response[i]) )
      EEPROM.write(eepromImeiAddress++, response[i]);
  }
}


bool  isValidIMEI()
{
  int nSum       = 0;
  char pPurported[IMEI_LENGTH];
  for (int i = 0; i < IMEI_LENGTH; i++)
  {
    pPurported[i] = EEPROM.read(IMEI_ADDRES + i);
  }
  if (pPurported[i] == '0' || pPurported[i] == 0)
    return false;
  int nParity    = (IMEI_LENGTH - 1) % 2;
  char cDigit[2] = "\0";
  for (int i = IMEI_LENGTH; i > 0 ; i--)
  {
    cDigit[0]  = pPurported[i - 1];
    int nDigit = atoi(cDigit);

    if (nParity == i % 2)
      nDigit = nDigit * 2;

    nSum += nDigit / 10;
    nSum += nDigit % 10;
  }
  return 0 == nSum % 10;
}
