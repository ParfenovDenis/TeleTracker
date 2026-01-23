

int LAST_HTTP_COMMAND_INDEX = 5;

const char HTTP_INIT_1[] PROGMEM =    {"AT+SAPBR=3,1,\"APN\",\"internet\"\r\0"};
const char HTTP_INIT_2[] PROGMEM =    {"AT+SAPBR=1,1\r\0"};
const char HTTP_INIT_3[] PROGMEM =    {"AT+SAPBR=2,1\r"};
const char HTTP_INIT_4[] PROGMEM =    {"AT+HTTPINIT\r\0"};
const char HTTP_INIT_5[] PROGMEM =    {"AT+HTTPPARA=\"CID\",1\r\0"};
const char HTTP_INIT_6[] PROGMEM =    {"AT+HTTPPARA=\"CONTENT\",\"application/x-www-form-urlencoded\"\r\0"};


const char HTTP_URL_CAN[] PROGMEM = {"AT+HTTPPARA=\"URL\",\"http://\"\r\0"};
const char HTTP_URL_LOG[] PROGMEM = {"AT+HTTPPARA=\"URL\",\"http://\0"};
const char HTTP_URL_VIN_LOW[] PROGMEM = {"AT+HTTPPARA=\"URL\",\"http://\0"};
const char ID_URL_CAN PROGMEM = 0;
const char ID_URL_LOG PROGMEM = 1;
const char ID_URL_VIN_LOW PROGMEM = 2;

const char* const HTTP_URLS[] PROGMEM = {
  HTTP_URL_CAN, HTTP_URL_LOG, HTTP_URL_VIN_LOW
};

// было: 
char* url = &ID_URL_CAN;
//char* url = &ID_URL_VIN_LOW;



const char* const HTTP_COMMANDS_INIT[] PROGMEM = {
  HTTP_INIT_1, HTTP_INIT_2, HTTP_INIT_3, HTTP_INIT_4, HTTP_INIT_5, HTTP_INIT_6
};

const char HTTP_CLOSE_1[] PROGMEM = {"AT+HTTPTERM\r\0"};
const char HTTP_CLOSE_2[] PROGMEM = {"AT+SAPBR=0,1\r\0"};

const char* const HTTP_COMMANDS_CLOSE[] PROGMEM = {
  HTTP_CLOSE_1, HTTP_CLOSE_2
};
/*char HTTP_COMMANDS_CLOSE[2][15] = {
  {"AT+HTTPTERM\r\0"},
  {"AT+SAPBR=0,1\r\0"}
  };*/

const char ATHTTPACTION[] PROGMEM = "AT+HTTPACTION=1\r\0";
//const char ATHTTPPARA_USERDATA[] =  "AT+HTTPPARA=\"USERDATA\",\"imei=%s&fp=%lu\"\r\0";
const char ATHTTPDATA[] = "AT+HTTPDATA=%lu,100000\r\0";
const char ATHTTPREAD[] PROGMEM = "AT+HTTPREAD\r\0";


unsigned long httpTimer = 0;
unsigned long httpLifeCycleTimer = 0;
const unsigned long HTTP_PERIOD = 30000;

const char HTTP_STANDBY = 0;
const char HTTP_INIT = 1;
const char HTTP_USERDATA = 2;
const char HTTP_REQUEST_BYTES = 3;
const char HTTP_DATA_TRANSFER = 4;
const char HTTP_ACTION = 5;
const char HTTP_READ = 6;
const char HTTP_CLOSE = 7;
const char HTTP_ERROR = 8;
const char HTTP_URL = 9;
// было: 
char HTTP_STATE = HTTP_STANDBY;
//char HTTP_STATE = HTTP_INIT;


const char SOURCE_FILE_CAN = 0;
const char SOURCE_FILE_LOG  = 1;
const char SOURCE_FILE_NONE  = 2;
// было: 
char SOURSE_FILE = SOURCE_FILE_CAN;
//char SOURSE_FILE = SOURCE_FILE_NONE;

const int POST_DATA_SIZE = 9983;

const int MAX_FAILED_REQUEST_PERIOD = 3600;

unsigned int HttpLastSuccessRequestTime = 0;
unsigned int HttpLastRequestTime = 0;

unsigned int HttpTotalRequestsQty = 0;
unsigned int HttpSuccessRequestsQty = 0;



int h = 0; // счётчик для HTTP_COMMANDS
