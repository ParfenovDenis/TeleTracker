#define FOSC 1843200// Clock Speed
#define BAUD 115200
#define MYUBRR FOSC/16/BAUD-1
extern int __bss_end;
extern void *__brkval;

///////////////////////////////////////////////////////////////////////////////////////
////////////////////// CONFIG ////////////////////////////////////////
#define VERSION_NUMBER 8
const unsigned int VERSION = VERSION_NUMBER;
//#define CAN_SPEED CAN_500KBPS /// CAN_500KBPS // CAN_250KBPS (IVECO 318)

#define LCD_ADDRESS  0x27 // 0x27 - blue light 0x3f - green light
#define LCD_2004A 1 //  #define LCD_OLED_SSD1306 1
//#define CANBUS_TRIGGERS 1

////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////

const int SPI_CS_PIN = 10;
const int CAN_INT_PIN = 2;

const char AT[] = "AT\r" ;
const char ATCPOWD[] = "AT+CPOWD=1\r"; // Power Off
const char ATCSQ[] = "AT+CSQ\r";
const char ATGSN[] = "AT+GSN\r";
const char ATSAPBR0[] = "AT+SAPBR=0,1\r\0";


/////////// OLED
const char OLED_HTTP_200[] PROGMEM = "HTTP 200";
const char OLED_HTTP_209[] PROGMEM = "HTTP 209";
const char OLED_HTTP_500[] PROGMEM = "HTTP 500";
const char OLED_HTTP_600[] PROGMEM = "HTTP 600";
const char OLED_HTTP_UNKNOWN_ERROR[] PROGMEM = "HTTP UNKNOWN ERROR";
///////////////////////////////////////

const int PARAMS_SIZE = 53;

#include "LOG_MESSAGES.h"


#include "HTTP.h"


#include "GPS.h"


/////// OLED
const char OLED_FP[] =  "FP=%lu";

const char OLED_RPM[] = "%dRPM %lus %d\0";
const char OLED_HTTP_LAST_REQUEST_TIME[] = "LAST %ds/%ds";
const char OLED_HTTP_REQUESTS_QTY[] = "REQUESTS %d/%d";
const char OLED_CSQ[] = "CSQ=%d,%d %ds";
#ifdef LCD_OLED_SSD1306
const char OLED_LINE_LENGTH = 21;
#endif
#ifdef LCD_2004A
const char OLED_LINE_LENGTH = 20;
#else
const char OLED_LINE_LENGTH = 20;
#endif
int oledGpsDataItem = 0;
char lineOLED[OLED_LINE_LENGTH];
//////////////////////////////////////////

unsigned long canLogTimer = 0;
const int CAN_LOG_PERIOD = 1000;

const bool ON = true;
const bool OFF = false;

bool DATA_TRANSFER_MODE = OFF;

bool NEW_DATA = false;


const int MAX_COMMAND_LENGTH = 1200;
const int MAX_RESPONSE_LENGTH = 200;



char command[MAX_COMMAND_LENGTH];
char response[MAX_RESPONSE_LENGTH];
char responseGPS[MAX_RESPONSE_LENGTH];


const int FP_BYTES = 4;

unsigned char rssi = 0;
unsigned char ber = 0;



/////// MODES
const unsigned int MODE_TRANSFER = 0;
const unsigned int MODE_MULTIPART_TRANSFER = 1;
const unsigned int MODE_RECEIVE = 2;
const unsigned int MODE_STANDBY = 3;
const unsigned int MODE_SLEEP = 4;
const unsigned int MODE_UPDATE = 5;
unsigned int MODE = MODE_STANDBY;
/////////////////////////////////////////
const int CSQ_SIZE = 6;

/////// TIMERS
unsigned long receiveTimer = 0;
unsigned long waitingReceiveTimer = 0;
unsigned long signalQualityTimer = 0;
unsigned long buttonPowerTimer = 0;
unsigned long buttonOledResetTimer = 0;
unsigned long repeatTimer = 0;
unsigned long refreshOLEDTimer = 0;
unsigned long lastCanMessageTimer = 0;
//////////////////////////////////////////

unsigned long t1, t2;


unsigned long CSQ_TIMESTAMP = 0;


bool allowPwrKeyOn = false;

unsigned long ReqLength = 0;

const unsigned long MAX_MODEM_AT_TIME = 60000;
const unsigned long  MAX_RECEIVE_TIME_DEFAULT = 101000;
const unsigned int MAX_RECEIVE_TIME_POWER_DOWN = 5000;
const unsigned long MAX_WAITING_RECEIVE_TIME = 101000;
const unsigned long SIGNAL_QUALITY_PERIOD = 3000;
const unsigned int MAX_REPEAT_TIME = 3000; // 
const unsigned int OLED_REFRESH_TIME = 2000;
const unsigned long MAX_CAN_INACTIVITY_TIME = 3600000;

unsigned long MaxReceiveTime = MAX_RECEIVE_TIME_DEFAULT;

//////// BUTTONS
const unsigned int BUTTON_POWER_PERIOD = 3000;
const int BUTTON_POWER_PIN = 5; //SB2
const int BUTTON_OLED_RESET_PERIOD = 500;
const int BUTTON_OLED_RESET_PIN = 32;
unsigned long *pPowerTimer;
unsigned long *pOledResetTimer;
///////////////////////////////////////


//////// TRIGGERS
const int TRIGGER_DEFAULT = 0;
const  int TRIGGER_REBOOT = 1;
const int TRIGGER_SLEEP = 2;
unsigned int trigger = TRIGGER_DEFAULT;
////////////////////////////////////




const byte SIM7000_NOT_READY = 0;
const byte SIM7000_READY = 1;
const byte SIM7000_IMEI_READY = 2;
byte SIM7000_STATE = SIM7000_NOT_READY;

bool MORE_DATA = false;

int r = 0; // counter char response
int c = 0; // counter char command
int r2 = 0; // counter char responseGPS

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 32
#define OLED_RESET 4

const int FP_ADDRESS = 0;
const int BOOT_ID_ADDRESS = 4;
const char IMEI_ADDRES = 8;
const char IMEI_LENGTH = 15;
const char CAN_SPEED_ADDRESS = 23;

unsigned long fp = 0; //  CAN.LOG
unsigned long bootId = 0; // id 
unsigned long start_fp = 0;
unsigned long finish_fp = 0;

File CANLogFile;
const int POWER_KEY = 6;
unsigned char ch, ch0;
int i = 0;

unsigned char  pFC21; // 
unsigned char  pF200 [2]; // 
unsigned char  p400 [2]; // 
unsigned char  pEE00; // 
//unsigned char  pE500 [8];
unsigned char  pC1EE [4]; // 
unsigned char  p6CEE; // 
unsigned char  p10B; // 
unsigned char  p300; // 
unsigned char  p700B [2]; // 
unsigned char  pE6EE [8]; // 
unsigned char  pF700[8]; // 
