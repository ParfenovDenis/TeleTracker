void parseRMC()
{
  int comma = 0;
  String time_str,   speed_str, course_str, date_str;
  char latitude_str[12], longitude_str[12];
  memset(latitude_str, '\0', 12);
  memset(longitude_str, '\0', 12);
  int i = 0;
  int l = 0;
  while (responseGPS[i] != '\0')
  {
    if (responseGPS[i] == ',')
    {
      l = 0;
      comma++;
    }
    else {
      if (comma == 1 && isDigit(responseGPS[i]) && time_str.length() < 6)
        time_str += responseGPS[i];
      if (comma == 3)
        latitude_str[l++] = responseGPS[i];
      if (comma == 5)
        longitude_str[l++] = responseGPS[i];
      if (comma == 7)
        speed_str += responseGPS[i];
      if (comma == 8)
        course_str += responseGPS[i];
      if (comma == 9 && isDigit(responseGPS[i]))
        date_str += responseGPS[i];
    }
    i++;
  }
  latitude = getLongValue(latitude_str);
  longitude = getLongValue(longitude_str);
  //Log.printVar()

  yearmonth = (date_str.substring(5, 6).toInt() << 4) | date_str.substring(2, 4).toInt();
  day = date_str.substring(0, 2).toInt();
  hour = time_str.substring(0, 2).toInt();
  minute = time_str.substring(2, 4).toInt();
  second = time_str.substring(4, 6).toInt();
  speed = speed_str.toInt();
  course = course_str.toInt();
  /*Log.printOut("parseRMC()\r\n");
    String GPStime;
    GPStime.concat((int)hour);
    GPStime.concat(":");
    GPStime.concat((int)minute);
    GPStime.concat(":");
    GPStime.concat((int)second);
    GPStime.concat("\r\n");
    Log.printOut(GPStime);*/
  clearResponseGPS();
}

void parseGGA()
{
  String gnss_satelites_used_str, altitude_str;
  int i = 0;
  int comma = 0;
  while (responseGPS[i] != '\0')
  {
    if (responseGPS[i] == ',')
      comma++;
    else {
      if (comma == 7 && isDigit(responseGPS[i]) )
        gnss_satelites_used_str += responseGPS[i];
      if (comma == 9)
        altitude_str += responseGPS[i];
    }
    i++;
  }
  gnss_satelites_used = gnss_satelites_used_str.toInt();
  altitude = altitude_str.toInt();
  clearResponseGPS();
}

void parseGSV ()
{
  String gps_satellites_str, CN0_str = "", satellite_ID_str, messageID_str;
  int i = 0;
  int comma = 0;
  byte satelliteID;
  //unsigned long t1, t2;
  //t1 = micros();
  while (responseGPS[i] != '\0')
  {
    if (responseGPS[i] == '*')
      break;
    if (responseGPS[i] == ',')
    {
      comma++;
      CN0_str = "";
    }
    else {
      if (comma == 2 && isDigit(responseGPS[i]))
        messageID_str += responseGPS[i];
      else if (comma == 3)
      {
        if (messageID_str.toInt() == 1)
          CN0 = 0;
        gps_satellites_str += responseGPS[i];
      }
      else if (comma == 4)
        satellite_ID_str += responseGPS[i];
      else if (comma == 7 || comma == 11 || comma == 15 || comma == 19)
      {
        CN0_str += responseGPS[i];
      }
      else if (comma == 8 || comma == 12 || comma == 16)
      {
        if (CN0_str.toInt() > CN0)
          CN0 = CN0_str.toInt();
        CN0_str = "";
      }
    }
    i++;
  }

  if (CN0_str.toInt() > CN0)
    CN0 = CN0_str.toInt();
  if (CN0 > 100)
  {


  }
  satelliteID = satellite_ID_str.toInt();
  if (satelliteID > 65 && satelliteID < 89)
    glonass = gps_satellites_str.toInt();
  else
    gps_satellites = gps_satellites_str.toInt();
  clearResponseGPS();
}

unsigned long getLongValue(char *coord)
{
  float llat = atof(coord);
  int ilat = llat / 100;
  float mins = fmod(llat, 100);
  unsigned long longLatitude = (ilat + (mins / 60)) * 1000000 ;
  return longLatitude;
}

bool isGGA()
{
  return strncmp(responseGPS, "GNGGA\0", 5) == 0 ? true : false;
}

bool isRMC()
{
  return strncmp(responseGPS, "GNRMC\0", 5) == 0 ? true : false;
}

bool isGSV()
{
  return (strncmp(responseGPS, "GPGSV\0", 5) == 0 || strncmp(responseGPS, "GLGSV\0", 5) == 0) ? true : false;
}

void clearResponseGPS()
{
  memset(responseGPS, '\0', MAX_RESPONSE_LENGTH);
  memset(hashMessageGPS, '\0', 3);
  r2 = 0;
  hMGPS = 0;

}

bool isValidGPSMessage()
{
  unsigned char i = 1;
  char _byte = responseGPS[0];
  while (responseGPS[i] != '\0')
  {
    _byte = _byte ^ responseGPS[i++];
    if (i > MAX_RESPONSE_LENGTH)
      break;
  }
  char hash[3];
  memset(hash, '\0', 3);
  sprintf(hash, "%X", _byte);

  if (strcmp(hashMessageGPS, hash) == 0)
    return true;
  return false;
}

void readGPS() {

  while (Serial3.available()) {
    unsigned char ch = Serial3.read();
    if (statusMessage == END_MESSAGE)
    {
      if (hMGPS < 2 && isAlphaNumeric(ch))
        hashMessageGPS[hMGPS++] = ch;
      else if (true == isValidGPSMessage())
      {
        if (isRMC() == true)
          parseRMC(); // 1904 microseconds
        /*else if (isGGA() == true)
          parseGGA(); // 624 microseconds
        else if (isGSV() == true)
          parseGSV(); // 1100 microseconds*/
        clearGPSMessage();
      } else
        clearGPSMessage();
      break;
    }
    if (statusMessage == START_MESSAGE )
    {
      if ( ch == '*') {
        statusMessage = END_MESSAGE;
        hMGPS = 0;
      } else {
        if (r2 < MAX_RESPONSE_LENGTH)
          responseGPS[r2++] = ch;
        else
          clearGPSMessage();
      }
    }
    else if (ch == '$')
      statusMessage = START_MESSAGE;
  }
}

void clearGPSMessage()
{

  statusMessage = EMPTY_MESSAGE;
  clearResponseGPS();
}
