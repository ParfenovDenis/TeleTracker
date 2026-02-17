void UpdateFirmware () {
  Log.printPROGMEM(LOG_UPDATE_FIRMWARE);
  unsigned long timerSerial2;
  const int RESPONSE_TIMEOUT = 10000;
  bool start_response_found = false;
  const char START_RESPONSE_STR[] = "+HTTPREAD: %lu\r\n";
  char startResponseStr[25];
  memset(startResponseStr, '\0', 25);
  unsigned long contentLength = getHttpContentLength();
  sprintf(startResponseStr, START_RESPONSE_STR, contentLength);
  unsigned int pos = 0;
  unsigned int last_position, lastPositionFirmWare;
  last_position = lastPositionFirmWare = contentLength - 1;
  last_position += 6; //   \13\10OK\13\10
  Log.printVar(LOG_LAST_POSITION, String(last_position));
  Log.printVar(LOG_LAST_POSITION_FIRMWARE, String(lastPositionFirmWare));
  unsigned int posFirmWare = 0;
  clearCommand();
  unsigned int startPositionFirmWare = 0;
  unsigned int cntSerialRead = 0;
  bool downloadTimeOut = false;//, responseTimeOut = false;

  unsigned int cntResponseChrs = 0;
  const char FirmwareFileName[] = "firmware.bin";
  File FirmwareFile;
  wdt_disable();
  //unsigned int countPart = 0;
  const unsigned long DOWNLOAD_TIMEOUT = 3600000;
  unsigned long downloadTimer =  millis();
  if (SD.exists(FirmwareFileName)) {
    Log.printPROGMEM(LOG_SD_REMOVE);
    SD.remove(FirmwareFileName);
  }
  FirmwareFile = SD.open(FirmwareFileName, FILE_WRITE);

  if (!FirmwareFile)
  {
   Log.printPROGMEM(LOG_CANT_CREATE_FIRMWARE_FILE);
    return;
  }
  while (posFirmWare <= lastPositionFirmWare) {
    strcpyCommand(ATHTTPREAD);
    pos = 0;
    timerSerial2 = millis();
    while  ( pos <= last_position )
    {
      if  (Serial2.available())
      {
        timerSerial2 = millis();
        ch = Serial2.read();

        cntSerialRead++;
        if (start_response_found == true) {
          if (c < MAX_COMMAND_LENGTH
              && posFirmWare == pos
              && posFirmWare <= lastPositionFirmWare
              && posFirmWare >= startPositionFirmWare) {
            command[c++] = ch;
            posFirmWare = pos + 1;
            cntResponseChrs++;
          }

        } else
        {
          if (c < MAX_COMMAND_LENGTH)
            command[c++] = ch;
          if (strstr(command, startResponseStr) != NULL)
          {
            last_position += pos + 1;
            lastPositionFirmWare += pos + 1;
            startPositionFirmWare = pos + 1;
            clearCommand();
            posFirmWare = pos + 1;

            start_response_found = true;
          }
        }
        pos++;
      }
      if (millis() - timerSerial2 > RESPONSE_TIMEOUT) {
        Log.printPROGMEM(LOG_RESPONSE_TIMEOUT); //LOG_POS_LAS
        Log.printPROGMEM(LOG_POS_LAST_POS);
        //responseTimeOut = true;
        Log.printOut((char) pos);
        Log.printOut('/');
        Log.printOut((char) last_position);
        Log.println();

        break;
      }
    }
    if (c > 0) {
      FirmwareFile.write(command, c);
      FirmwareFile.flush();
      clearCommand();
    }
    if (millis() - downloadTimer > DOWNLOAD_TIMEOUT)
    {
      Log.printPROGMEM(LOG_DOWNLOAD_TIMEOUT_BREAK);
      downloadTimeOut = true;
      Log.printPROGMEM(LOG_DOWNLOAD_TIMEOUT);
      break;
    }
  }
  FirmwareFile.close();
  if (downloadTimeOut == true)
  {
    Log.printPROGMEM(LOG_RETURN);
    SD.remove(FirmwareFileName);
    return;
  }
  Log.printPROGMEM(LOG_FIRMWARE_DOWNOLOADED);
  Log.term();
  EEPROM.write(0x1FF, 0xF0);
  wdt_enable(WDTO_500MS);
  wdt_reset();
  delay(600);
}
