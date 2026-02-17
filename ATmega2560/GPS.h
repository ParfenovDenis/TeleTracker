//const int CGNSINF_SIZE = 115;
unsigned long CGNSINF_TIMESTAMP = 0;
unsigned long gpsTimer = 0;
//char CGNSINF[CGNSINF_SIZE];
//const unsigned long GPS_PERIOD = 5000;
//const unsigned long MAX_GPS_INIT_TIME = 300000;
//const unsigned long MAX_GPS_COLD_START_TIME = 3600000;
//unsigned long lastGPSColdStartTimer = 0;

/*
const byte GPS_OFF = 0;
const byte GPS_ON = 1;
const byte GPS_READY = 2;
byte GPS_STATE = GPS_OFF;*/

//const char ATCGNSPWR[] = "AT+CGNSPWR=1\r";

unsigned char yearmonth, day, hour, minute, second;
unsigned long latitude = 0;
unsigned long longitude,  speed, course;
byte gps_satellites, gnss_satelites_used, CN0, glonass;
unsigned int altitude;
const char EMPTY_MESSAGE = 0;
const char START_MESSAGE = 1;
const char END_MESSAGE = 3;
const char VALID_MESSAGE = 7;
char hashMessageGPS[3];
char hMGPS = 0;
unsigned char statusMessage = EMPTY_MESSAGE;
