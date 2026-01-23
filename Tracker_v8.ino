/**
   Адаптированная под библиотеку CAN-BUS_Shield_v2.3 версия. Ветка 6й версии. Без MANUAL_CONTROL и HTTP. Config вынесен наверх Tracker.h
   Закачка LOG-файла

*/
#include <SPI.h>
#include <avr/io.h>
#include <avr/wdt.h>
#include <EEPROM.h>

#include <Log.h>

#include <stdarg.h>

#include "Tracker.h"

//#include <SPI.h>
#include "mcp_can.h"

#ifdef LCD_OLED_SSD1306
#include "Adafruit_GFX.h"
#include "Adafruit_SSD1306.h"
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire);
#endif

#ifdef LCD_2004A
#include <LiquidCrystal_I2C.h>
#define BACKLIGHT_PIN     13

LiquidCrystal_I2C lcd(LCD_ADDRESS, 20, 4);

#endif

#include "mcp2515_can.h"
mcp2515_can CAN(SPI_CS_PIN); // Set CS pin

void(* reset) (void) = 0;

// для теста:
//unsigned long VinLowTimer = 0;
//const int VIN_LOW_PERIOD = 1000;

void setup() {
  //USART_Init2 ( MYUBRR );

  Serial2.begin(115200);
  Serial3.begin(9600);

  pinMode(POWER_KEY, OUTPUT);
  pinMode(PIN_PH2, OUTPUT); // PWR_GSM
  pinMode(PIN_PH3, OUTPUT); // GSM_EN
  pinMode(PIN_PF0, INPUT);//   VIN_DETECT
  pinMode(PIN_PJ2, OUTPUT); // GPS_EN
  pinMode(PIN_PJ4, INPUT); //  LNA_EN
  pinMode(PIN_PB6, OUTPUT); // SD_EN
  digitalWrite(PIN_PH3, HIGH);
  digitalWrite(PIN_PJ2, HIGH);
  digitalWrite(PIN_PB6, HIGH);

  wdt_disable();
  boot();

  initOLED();
  pPowerTimer = &buttonPowerTimer;
  pOledResetTimer = &buttonOledResetTimer;
  pinMode(BUTTON_POWER_PIN, INPUT);
  pinMode(BUTTON_OLED_RESET_PIN, INPUT);
  writeLineOLEDProgmem(3, LOG_START);

  bool LogInit = Log.init(11);
  Log.printPROGMEM(LOG_START);
  if (false == LogInit)
  {
    Log.printPROGMEM(LOG_SD_ERROR);
    writeLineOLEDProgmem(3, LOG_SD_ERROR);
    powerOff();
    while (1);
  }
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, LOW);
  checkPowerButton();
  Log.printVar(LOG_VERSION, String(VERSION));
  initCANBus();
  Log.printPROGMEM(LOG_CAN_OK);
  writeLineOLEDProgmem(3, LOG_CAN_OK);
  writeLineOLEDProgmem(3, LOG_SD_OK);
  Log.printPROGMEM(LOG_SD_OK);

  memset(command, '\0',  MAX_COMMAND_LENGTH);
  memset(response, '\0', MAX_RESPONSE_LENGTH);
  //memset(CGNSINF, '\0', CGNSINF_SIZE);

  EEPROM.get( FP_ADDRESS, fp);
  Log.printVar(LOG_FP, String(fp));
  Log.printVar(LOG_BOOT_ID, String(bootId));
  Log.atLogStr(String("bootId: "));
  Log.atLogStr(String(bootId, DEC));
  unsigned long canLogSize  = Log.getCANLogSize();
  if (fp > canLogSize)
  {
    Log.printPROGMEM(LOG_FP_INCORRECT);
    Log.printVar(LOG_CAN_LOG_SIZE, String(canLogSize));
    fp = canLogSize ;
    EEPROM.put( FP_ADDRESS, fp);
  }

  if (fp % PARAMS_SIZE != 0)
  {
    Log.printPROGMEM(LOG_FP_MULTIPLE_INCORRECT);
    Log.printVar(LOG_FP, String(fp));
    fp = fp / PARAMS_SIZE * PARAMS_SIZE;
    Log.printVar(LOG_CORRECT_FP, String(fp));
    EEPROM.put( FP_ADDRESS, fp);
  }

  if (sprintf(lineOLED, OLED_FP, fp))
    writeLineOLED(3, lineOLED);

  //on();
  wdt_enable (WDTO_8S);
}

void loop() {

  wdt_reset();
  checkPowerButton();
  // checkOledresetButton();
  refreshOLED();

  /////////////////////// Для отладки

  //////////////////////////////////////////////////////////////////
  // TODO: Проверить!! ограничить время работы до переполнения millis




  if (MODE == MODE_SLEEP && digitalRead(PIN_PF0) == HIGH && (getMillis() - lastCanMessageTimer <= MAX_CAN_INACTIVITY_TIME))
  {
    reset();
  }

  if (MODE == MODE_STANDBY)
  {
    if (digitalRead(PIN_PF0) == LOW ) {

      ModemSleep();
    } else {
      setCommand();
      /*Log.printVar(LOG_COMMAND, command);
        Log.printVar("c1", String(c));*/
    }
  }

  if (MODE == MODE_TRANSFER) {
    if (command[c] == '\0')
    {
      Log.printVar(LOG_MEMORY, String(memoryFree()));
      Log.printPROGMEM(LOG_COMMAND_SENT);
      Log.printVar(LOG_C, String(c));
      Log.printVar(LOG_COMMAND, String(command));
      strncpy(lineOLED, command, OLED_LINE_LENGTH - 1);
      lineOLED[OLED_LINE_LENGTH - 1] = '\0';
      writeLineOLED(3, lineOLED);
      modeReceive();
    } else
      Serial2.write(command[c++]);
  }

  if (MODE == MODE_RECEIVE) {
    callTrigger();
    if (Serial2.available()) {
      ch = Serial2.read();

      if (ch > 0 && ch < 128)
      {
        response[r++] = ch;
        receiveTimer = getMillis();
        // waitingReceiveTimer = 0;

      }
      Log.atLOG(ch);
    }
    // Повторяем запрос для AT команды
    else if (repeatTimer > 0 && ((getMillis() - repeatTimer) > MAX_REPEAT_TIME))
    {
      if (true == allowPwrKeyOn)
        on();
      else
        allowPwrKeyOn = true;
      Log.printPROGMEM(LOG_REPEAT_TIMER_OVERFLOW);
      repeatTimer = getMillis();
      if (getMillis() > MAX_MODEM_AT_TIME)
      {
        Log.term();
        reset();
      }
      c = 0;
      MODE = MODE_TRANSFER;
    }
    else if (r > 0)
    {
      if (r > 7 && true == isError())
      {
        Log.printPROGMEM(LOG_IS_ERROR);
        handleHTTPError();
      }
      if (true == isOK() )
      {
        Log.printPROGMEM(LOG_IS_OK);
        // обработка результата. сравниваем строку с ожидаемым ответом
        if (true == isATGSN())
        {
          Log.printPROGMEM(LOG_IS_ATGSN);
          setIMEI();
          clearResponse();
        }
        if (true == isCSQ())
        {
          Log.printPROGMEM(LOG_IS_ATCSQ);
          setCSQ();
          clearResponse();
        }
        if (true == isAT())
        {
          Log.printPROGMEM(LOG_IS_AT);
          repeatTimer = 0;
          SIM7000_STATE = SIM7000_READY;
          clearResponse();
        }
        HTTP_Triggers_OK();
        if (false == MORE_DATA) // если больше данных не ждём - переходим в режим ожидания
        {
          Log.printPROGMEM(LOG_MORE_DATA);
          STANDBY();
        } else
        {
          receiveTimer = getMillis();
        }
      }

      if (r >= 10) // Команды не возвращающие OK
      {
        if (true == isPowerDown()) // здесь нужно  сделать доп. проверку на SIM7000_STATE == REBOOT
        {
          Log.printPROGMEM(LOG_IS_POWERDOWN);
          ModemReboot();
          callTrigger();
        }
        if (response[r - 1] == '\n')
        {
          HTTP_Triggers();
        }
      }
    }
    // если ответ не распознан по истечении времени приема, просто переходим в режим ожидания
    if   (receiveTimer > 0 && (getMillis() - receiveTimer) > MaxReceiveTime)
    {
      Log.printPROGMEM(LOG_MAX_RECEIVE_TIME);
      Log.printVar(LOG_RESPONSE, response, true);
      Log.printVar(LOG_R, String(r));
      Log.printChars(response, MAX_RESPONSE_LENGTH);

      ModemReboot();
    }
    //перезагружаемся если превышено макс. время на начало ответа
    else if (waitingReceiveTimer > 0 && ( getMillis() - waitingReceiveTimer) > MAX_WAITING_RECEIVE_TIME )
    {
      Log.printPROGMEM(LOG_MAX_WAITING_RECEIVE_TIME);
      Log.printVar(LOG_RESPONSE, response, true);
      Log.printVar(LOG_R, String(r));
      ModemReboot();
      //не дождались ответа. перезагружаемся...
    }
  }

  readGPS();


  readCANBus();
  logging();

}
