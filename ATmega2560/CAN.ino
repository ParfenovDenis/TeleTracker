void initCANBus()
{
  byte canSpeed = CAN_500KBPS;
  if (EEPROM.read(CAN_SPEED_ADDRESS) == CAN_250KBPS)
    canSpeed = CAN_250KBPS;
  while (CAN_OK != CAN.begin(canSpeed))
  {
    Log.printPROGMEM(LOG_CAN_ERROR);
    writeLineOLEDProgmem(3, LOG_CAN_ERROR);
    delay(100);
  }
}


void readCANBus()
{
  unsigned char len = 0;
  unsigned char buf[8];

  if (CAN_MSGAVAIL == CAN.checkReceive())           // check if data coming
  {
    CAN.readMsgBuf(&len, buf);    // read data,  len: data length, buf: data buf

    unsigned int canId = CAN.getCanId();

    //Log.println("-----------------------------");
    //Log.print("Get data from ID: ");
    //Log.println(canId, HEX);
    lastCanMessageTimer = getMillis();
    switch (canId)
    {
      case  0xFC21:
        pFC21 = buf[1];
        if (buf[1] != pFC21)
          NEW_DATA = true;
        break;
      case  0xF200:

        if (pF200[0] != buf[0] || pF200[1] != buf[1])
        {
          NEW_DATA = true;
          pF200[0] = buf[0];
          pF200[1] = buf[1];
        }
        break;
      case  0x400:
        if (p400[1] != buf[4])
        {
          NEW_DATA = true;
          p400[0] = buf[3];
          p400[1] = buf[4];
        }

        break;
      case  0xEE00:
        if (buf[0] != pEE00)
          NEW_DATA = true;
        pEE00 = buf[0];
        break;
      case  0xC1EE:
        if (buf[3] != pC1EE[3] || buf[2] != pC1EE[2] || buf[1] != pC1EE[1] || buf[0] != pC1EE[0])
        {
          NEW_DATA = true;
          for (int i = 0; i < 4; i++)
            pC1EE[i] = buf[i];
        }
        break;
      case  0x6CEE:
        if (buf[7] != p6CEE)
        {
          NEW_DATA = true;
          p6CEE = buf[7];
        }
        break;
      case  0x10B:
        if (buf[1] != p10B)
        {
          NEW_DATA = true;
          p10B = buf[1];
        }
        break;
      case  0x300:
        if (buf[1] != p300)
        {
          NEW_DATA = true;
          p300 = buf[1];
        }
        break;
      case  0x700B:
        if (buf[2] != p700B[0] || buf[3] != p700B[1])
        {
          NEW_DATA = true;
          p700B[0] = buf[2];
          p700B[1] = buf[3];
        }
        break;

      case  0xE6EE:
        for (int i = 0; i < len; i++) {
          if (buf[i] != pE6EE[i])
            NEW_DATA = true;
          pE6EE[i] = buf[i];
        }
        break;
      case  0xF700:
        for (int i = 0; i < len; i++) {
          if (buf[i] != pF700[i])
            NEW_DATA = true;
          pF700[i] = buf[i];
        }
        break;
    }
#ifdef CANBUS_TRIGGERS
    if (NEW_DATA)
      onCanBusMessageReceive(canId);
#endif
  }
}
