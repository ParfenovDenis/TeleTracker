
void setCommandHTTP()
{

  Log.printPROGMEM(LOG_SET_COMMAND_HTTP);
  Log.printPROGMEM(LOG_HTTP_STATE_IS);

  Log.printPROGMEM(HTTP_STATE == HTTP_STANDBY ? LOG_HTTP_STANDBY :
                   HTTP_STATE == HTTP_INIT ? LOG_HTTP_INIT :
                   HTTP_STATE == HTTP_URL ? LOG_HTTP_URL :
                   HTTP_STATE == HTTP_REQUEST_BYTES ? LOG_HTTP_REQUEST_BYTES :
                   HTTP_STATE == HTTP_DATA_TRANSFER ? LOG_HTTP_DATA_TRANSFER :
                   HTTP_STATE == HTTP_ACTION ? LOG_HTTP_ACTION :
                   HTTP_STATE == HTTP_READ ? LOG_HTTP_READ :
                   HTTP_STATE == HTTP_CLOSE ? LOG_HTTP_CLOSE :
                   HTTP_STATE == HTTP_ERROR ? LOG_HTTP_ERROR :
                   "");

  bool allowTransfer = true;
  switch (HTTP_STATE)
  {
    case HTTP_CLOSE: // 7
      {
        setHttpClose();
        DATA_TRANSFER_MODE = OFF;
        break;
      }
    case HTTP_DATA_TRANSFER: // 4
      setHttpDataTransfer();
      allowTransfer = false;
      break;
    case HTTP_ACTION: // 5
      setHttpAction();
      break;
    case HTTP_INIT: // 1
      setHttpInit();
      break;
    case HTTP_ERROR: // 8 TODO: сделать подсчёт ошибок. Если > 3 - перезагрузка
      allowTransfer = setHttpError();
      break;
    case HTTP_REQUEST_BYTES: // 3
      {
        DATA_TRANSFER_MODE = ON;
        allowTransfer = setHttpRequestBytes();
        break;
      }
    case HTTP_READ:
      {
        clearCommand();
        clearResponse();
        strcpyCommand(ATHTTPREAD);
        break;
      }
    case HTTP_URL: {
        setHttpUrl();
        break;
      }
    default:
      Log.printPROGMEM(LOG_SWITCH_DEFAULT);
      break;
  }

  c = 0;
  if (allowTransfer)
  {
    Log.printPROGMEM(LOG_ALLOW_TRANSFER);
    MODE = MODE_TRANSFER;
  }
  Log.printPROGMEM(LOG_BRACE);
}

void setHttpUrl() {

  strcpyCommand(&HTTP_URLS[pgm_read_byte(url)]);


  if (url == &ID_URL_LOG || url == &ID_URL_VIN_LOW) {
    byte b = (url == &ID_URL_LOG ? 47 : 51);
    for (int i = IMEI_ADDRES; i < IMEI_ADDRES + IMEI_LENGTH; i++) {
      command[b++] = EEPROM.read(i);
    }
    command[b++] = '"';
    command[b++] = '\r';
  }

  if (SOURSE_FILE == SOURCE_FILE_NONE)
    HTTP_STATE = HTTP_ACTION;
  else
    HTTP_STATE = HTTP_REQUEST_BYTES;
}

void setHttpInit() {
  Log.printPROGMEM(LOG_SET_HTTP_INIT);
  httpLifeCycleTimer = getMillis();
  //strncpy(command, HTTP_COMMANDS_INIT[h++], 60);
  Log.printVar(LOG_H, String(h));
  strcpyCommand(&HTTP_COMMANDS_INIT[h++]);
  Log.printVar(LOG_COMMAND, command);
  Log.printVar(LOG_C, String(c));
  Log.printPROGMEM(LOG_BRACE);
  if (h > LAST_HTTP_COMMAND_INDEX)
  {
    HTTP_STATE = HTTP_URL;
  }
}

bool setHttpError() {
  bool allowTransfer = true;
  Log.printPROGMEM(LOG_SET_HTTP_ERROR);
  if (h > 1)
  {
    h = 0;
    HTTP_STATE = HTTP_INIT;
    STANDBY();
    Log.printPROGMEM(LOG_H_IS_GREATER_THAN_1);
    Log.printPROGMEM(LOG_HTTP_STATE_HTTP_INIT);
    allowTransfer = false;
  } else  {
    //strncpy(command, HTTP_COMMANDS_CLOSE[h++], 31);
    strcpyCommand(&HTTP_COMMANDS_CLOSE[h++]);
    Log.printVar(LOG_COMMAND, command);
  }
  Log.printPROGMEM(LOG_BRACE);
  return allowTransfer;
}


bool setHttpRequestBytes() {
  return SOURSE_FILE == SOURCE_FILE_LOG ? setHttpRequestBytesFromLog() : setHttpRequestBytesFromCAN();
}

bool setHttpRequestBytesFromLog()
{
  if (ReqLength > 0) {
    clearCommand();
    sprintf(command, ATHTTPDATA, ReqLength);
    return true;
  }
  return  false;
}

bool setHttpRequestBytesFromCAN( ) {

  if (CANLogFile.size() > 0 || CANLogFile.available() > 0)
    CANLogFile.close();
  CANLogFile = SD.open("CAN.LOG");
  unsigned long left = CANLogFile.size() - fp;
  bool allowTransfer = true;
  Log.printPROGMEM(LOG_SET_HTTP_REQUEST_BYTES);
  Log.printVar(LOG_CAN_LOG_SIZE, String(CANLogFile.size()));
  ReqLength = POST_DATA_SIZE;
  Log.printVar(LOG_LEFT, String(left));
  if (POST_DATA_SIZE  >  left + IMEI_LENGTH + FP_BYTES )
  {
    Log.printPROGMEM(LOG_POST_DATA_SIZE_MORE_THAN);
    ReqLength = (left / PARAMS_SIZE) * PARAMS_SIZE + IMEI_LENGTH + FP_BYTES ;
  }
  Log.printVar(LOG_REQ_LENGTH, String(ReqLength));
  Log.printVar(LOG_FP, String(fp));

  if (ReqLength > IMEI_LENGTH + FP_BYTES)
  {
    Log.printPROGMEM(LOG_REQ_LENGTH_MORE_THAN);
    clearCommand();
    sprintf(command, ATHTTPDATA, ReqLength);
    Log.printVar(LOG_COMMAND, command);
  }  else {
    HTTP_STATE = HTTP_CLOSE;
    h = 0;
    allowTransfer = false;
  }
  Log.printPROGMEM(LOG_BRACE);
  return allowTransfer;
}

/*
  void sendHTTPREAD()
  {

  int i = 0;
  while (ATHTTPREAD[i] != '\0')
  {
    Serial2.write(ATHTTPREAD[i++]) ;
  }

  }*/

void setHttpDataTransfer() {

  return SOURSE_FILE == SOURCE_FILE_LOG ? setHttpDataTransferLog() : setHttpDataTransferCAN();
}


void setHttpDataTransferLog( )
{

  Log.printPROGMEM(LOG_SET_HTTP_DATA_TRANSFER);
  MORE_DATA = false;
  if (ReqLength > 0)
  {
    File ArduinoLogFile = SD.open("Arduino.log");
    ArduinoLogFile.seek(ArduinoLogFile.size() - ReqLength);
    unsigned long t1 = getMillis();
    for (unsigned int i = 0; i < ReqLength ; i++) {
      Serial2.write(ArduinoLogFile.read());
    }
    Log.printVar(LOG_SERIAL2_WRITE_TIME, String(getMillis() - t1));
    ArduinoLogFile.close();
    modeReceive();
    HTTP_STATE = HTTP_ACTION;
  }
  Log.printPROGMEM(LOG_BRACE);
}
void setHttpDataTransferCAN( )
{
  Log.printPROGMEM(LOG_SET_HTTP_DATA_TRANSFER);
  MORE_DATA = false;
  if (ReqLength > 0)
  {
    Log.printPROGMEM(LOG_REQ_LENGTH_MORE_THAN_0);
    Log.printPROGMEM(LOG_SERIAL2_WRITE);
    unsigned long t1 = getMillis();
    for (char i = 0; i < IMEI_LENGTH; i++)
      Serial2.write(EEPROM.read(IMEI_ADDRES + i));
    CANLogFile.seek(fp);
    byte * fp_chars = convertToChar(fp);
    for (int i = 0; i < FP_BYTES; i++) {
      Serial2.write(fp_chars[i]);
    }
    delete[] fp_chars;
    for (unsigned int i = IMEI_LENGTH + FP_BYTES; i < ReqLength ; i++) {
      Serial2.write(CANLogFile.read());
    }
    Log.printVar(LOG_SERIAL2_WRITE_TIME, String(getMillis() - t1));
    CANLogFile.close();
    modeReceive();
    HTTP_STATE = HTTP_ACTION;

  } else {
    Log.printPROGMEM(LOG_REQ_LENGTH_LESS_THAN_1);
    Log.printVar(LOG_REQ_LENGTH, String(ReqLength));
    HTTP_STATE = HTTP_CLOSE;
    h = 0;
  }
  Log.printPROGMEM(LOG_BRACE);
}

void setHttpAction()
{
  Log.printPROGMEM(LOG_SET_HTTP_ACTION);
  //strncpy(command, ATHTTPACTION, 17);
  strcpyCommand(ATHTTPACTION);
  if (SOURSE_FILE == SOURCE_FILE_NONE)
    command[14] = '0';
  Log.printVar(LOG_COMMAND, command);
  Log.printPROGMEM(LOG_BRACE);
  MORE_DATA = true;
  h = 0;
}

void setHttpClose()
{
  Log.printPROGMEM(LOG_SET_HTTP_CLOSE);
  //strncpy(command, HTTP_COMMANDS_CLOSE[h++], 31);
  strcpyCommand(&HTTP_COMMANDS_CLOSE[h++]);
  Log.printVar(LOG_COMMAND, command);
  if (h > 1)
  {
    Log.printPROGMEM(LOG_H_IS_GREATER_THAN_1);
    httpTimer = getMillis();
    HTTP_STATE = HTTP_STANDBY;
    unsigned long httpTime = httpTimer - httpLifeCycleTimer;
    Log.printVar(LOG_FILE_TRANSFERED, String(httpTime));
  }
  Log.printPROGMEM(LOG_BRACE);
}

bool isDownload()
{
  return strstr(response, "DOWNLOAD\0") != NULL ? true : false;
}

bool isHTTPACTION()
{
  return strstr(response, "+HTTPACTION:\0") != NULL ? true : false;
}

bool isATHTTPACTION()
{
  return strstr(response, "AT+HTTPACTION=1\0") != NULL ? true : false;
}

bool isHTTPOK()
{
  return (response[17] == '2' &&  response[18] == '0' ) ? true : false;
}

bool isHTTPUPDATE()
{
  return (response[17] == '2' &&  response[18] == '0' &&  response[19] == '9' ) ? true : false;
}

bool isHTTPERROR()
{
  return (response[17] == '5' &&  response[18] == '0' && response[19] == '0') ? true : false;
}

bool isHTTP_SYSTEM_ERROR()
{
  return (response[17] == '6' &&  response[18] == '0' && response[19] == '0') ? true : false;
}

unsigned long getHttpContentLength() {
  char * pch;
  pch = strstr(response, "+HTTPACTION:\0");
  unsigned long contentLength = 0;
  if (pch != NULL)
  {
    int i = 13;
    byte commas = 0;
    byte s = 0;
    char contentLengthStr[6] = "";
    while (pch[i] != '\0')
    {
      if (pch[i] == ',') {
        commas++;
        s = 0;
      }
      if (commas == 2 && isDigit(pch[i]))
      {
        contentLengthStr[s++] = pch[i];
      }
      i++;
    }
    contentLength = atol(contentLengthStr);
  }
  return contentLength;
}


void handleHTTPError ()
{
  Log.printPROGMEM(LOG_HANDLE_HTTP_ERROR);
  if (HTTP_STATE == HTTP_INIT)
  {
    clearCommand();
    clearResponse();
    if (h < 3)
      strcpyCommand(&HTTP_COMMANDS_CLOSE[1]);
    else
      strcpyCommand(&HTTP_COMMANDS_CLOSE[0]);
    h = 0;
    MODE = MODE_TRANSFER;
  } else {
    STANDBY();
  }

  /*if (HTTP_STATE == HTTP_ERROR) {
    Log.printPROGMEM(LOG_HTTP_STATE_IS_HTTP_ERROR);
    STANDBY();
    }
    else
    //if (HTTP_STATE == HTTP_INIT || HTTP_STATE == HTTP_REQUEST_BYTES || HTTP_STATE == HTTP_DATA_TRANSFER)
    {
    Log.printPROGMEM(LOG_HTTP_STATE_IS);
    Log.printPROGMEM(HTTP_STATE == HTTP_INIT ? LOG_HTTP_INIT :
                     HTTP_STATE == HTTP_REQUEST_BYTES ? LOG_HTTP_REQUEST_BYTES :
                     HTTP_STATE == HTTP_DATA_TRANSFER ? LOG_HTTP_DATA_TRANSFER : "");
    h = 0;

    clearCommand();
    HTTP_STATE = HTTP_ERROR;
    STANDBY();
    MORE_DATA = false;
    }*/

  Log.printPROGMEM(LOG_BRACE);
}



void HTTP_Triggers()
{
  if (isDownload())
  {
    Log.printPROGMEM(LOG_IS_DOWNLOAD);
    HTTP_STATE = HTTP_DATA_TRANSFER;
    STANDBY();
  }
  if (isATHTTPACTION() && isOK())
  {
    Log.printPROGMEM(LOG_IS_ATHTTPACTION_AND_IS_OK);
    clearResponse();
  }
  if (isHTTPACTION())
  {
    Log.printPROGMEM(LOG_IS_HTTPACTION);
    HttpTotalRequestsQty++;
    HttpLastRequestTime = getMillis() / 1000;
    HTTP_STATE = HTTP_CLOSE;
    if (isHTTPOK())
    {
      Log.printPROGMEM(LOG_IS_HTTPOK);
      //fp = CANLogFile.position();
      fp += ReqLength - IMEI_LENGTH - FP_BYTES;
      Log.printVar(LOG_FP, String(fp));
      EEPROM.put(FP_ADDRESS, fp);
      HttpSuccessRequestsQty++;
      HttpLastSuccessRequestTime = HttpLastRequestTime;
      if (isHTTPUPDATE()) {
        writeLineOLEDProgmem(3, OLED_HTTP_209);
        UpdateFirmware();
      } else {
        writeLineOLEDProgmem(3, OLED_HTTP_200);
        unsigned long contentLength = getHttpContentLength();
        if (contentLength > 0)
        {
          HTTP_STATE = HTTP_READ;
        }
      }
      if (SOURSE_FILE == SOURCE_FILE_LOG)
        SOURSE_FILE = SOURCE_FILE_CAN;
      if (url == &ID_URL_LOG)
        url = &ID_URL_CAN;
    } else if (isHTTPERROR()) {
      Log.printPROGMEM(LOG_IS_HTTPERROR);
      //CANLogFile.seek(fp);
      writeLineOLEDProgmem(3, OLED_HTTP_500);
    } else if (isHTTP_SYSTEM_ERROR()) {
      Log.printPROGMEM(LOG_IS_HTTP_SYSTEM_ERROR);
      Log.printVar(LOG_RESPONSE, response);
      //CANLogFile.seek(fp);
      writeLineOLEDProgmem(3, OLED_HTTP_600);
    } else {
      Log.printPROGMEM(LOG_UNKNOWN_ERROR);
      Log.printVar(LOG_RESPONSE, response, true);
      //CANLogFile.seek(fp);
      writeLineOLEDProgmem(3, OLED_HTTP_UNKNOWN_ERROR);
    }
    MORE_DATA = false;
    STANDBY();
    if (HttpLastRequestTime > HttpLastSuccessRequestTime + MAX_FAILED_REQUEST_PERIOD) {
      Log.printPROGMEM(LOG_MAX_FAILED_REQUEST_PERIOD);
      Log.printVar(LOG_HTTP_LAST_SUCCESS_REQUEST_TIME, String(HttpLastSuccessRequestTime));
      Log.printVar(LOG_HTTP_LAST_REQUEST_TIME, String(HttpLastRequestTime));
      ModemReboot();
    }
  }
}

void HTTP_Triggers_OK() {

  if (responseIs(ATHTTPREAD)) {
    byte b;
    byte n = 0;
    for (b = 0; b < MAX_RESPONSE_LENGTH; b++) {
      if (response[b] == '\n' && ++n >= 2)
        break;
    }
    unsigned char task = response[++b];
    if (task == 'l')
    {
      b += 2;
      char logLengthStr[5];
      byte bl = 0;
      for (b; b < MAX_RESPONSE_LENGTH; b++) {
        if (response[b] == '\0')
          break;
        if (bl < 5 && isDigit(response[b]))
          logLengthStr[bl++] = response[b];
      }
      unsigned int logLength = atoi(logLengthStr);
      File ArduinoLogFile = SD.open("Arduino.log");
      ReqLength = ArduinoLogFile.size() > logLength ? logLength : ArduinoLogFile.size();
      ArduinoLogFile.close();
      SOURSE_FILE = SOURCE_FILE_LOG;
      url = &ID_URL_LOG;
      HTTP_STATE = HTTP_URL;
    }
    STANDBY();
  }
}
