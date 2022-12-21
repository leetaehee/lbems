import unittest
import pymysql
import ConfigParser
import logging
import json
import datetime
from logging.handlers import RotatingFileHandler
from MyString import log_path

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class Util:
    config = ConfigParser.ConfigParser()
    config.read('lbems_config.ini')

    db_user=config.get('db_configuration','db_id')
    pass_decoded = config.get('db_configuration','db_passwd')
    ip_addr_decoded = config.get('db_configuration','db_host')
    port_decoded = int(config.get('db_configuration','db_port'))
    db_name_decoded = config.get('db_configuration','db_sid')

    def getAcpInfo(self, complex_code):
        mylogger.info("db_user = %s" , self.db_user)
        mylogger.info("pass_decoded = %s" , self.pass_decoded)
        mylogger.info("ip_addr_decoded = %s" , self.ip_addr_decoded)
        mylogger.info("port_decoded = %s" , self.port_decoded)
        mylogger.info("db_name_decoded = %s" , self.db_name_decoded)

        con = pymysql.connect(host=self.ip_addr_decoded, port=self.port_decoded, user=self.db_user, passwd=self.pass_decoded, db=self.db_name_decoded,charset='utf8',autocommit=True)
        cur = con.cursor()

        sensor_list_query = "select * from bems_complex_device_info where type='acp'"+" and complex_code_pk="+complex_code;
        mylogger.info("acp info = %s", sensor_list_query)
        cur.execute(sensor_list_query)
        acp_list=cur.fetchall()

        cur.close()
        con.close()

        return acp_list

    def getDeviceConnInfo(self, complex_code, type):
        mylogger.info("db_user = %s" , self.db_user)
        mylogger.info("pass_decoded = %s" , self.pass_decoded)
        mylogger.info("ip_addr_decoded = %s" , self.ip_addr_decoded)
        mylogger.info("port_decoded = %s" , self.port_decoded)
        mylogger.info("db_name_decoded = %s" , self.db_name_decoded)

        con = pymysql.connect(host=self.ip_addr_decoded, port=self.port_decoded, user=self.db_user, passwd=self.pass_decoded, db=self.db_name_decoded,charset='utf8',autocommit=True)
        cur = con.cursor()

        sensor_list_query = "select * from bems_complex_device_info where type='"+type+"\'"+" and complex_code_pk="+complex_code;
        mylogger.info("solar info = %s", sensor_list_query)
        cur.execute(sensor_list_query)
        solar_list=cur.fetchall()

        cur.close()
        con.close()

        return solar_list

    def insertSamChuckSolarInfo(self, solarInfoBytesArray, sensor_sn):

        pvVoltage = solarInfoBytesArray[0:1]        # scale 1
        pvCurrent = solarInfoBytesArray[1:2]        # scale 0.1
        pvPower = solarInfoBytesArray[2:3]          # scale 1
        rVoltage = solarInfoBytesArray[3:4]         # scale 1
        sVoltage = solarInfoBytesArray[4:5]         # scale 1
        tVoltage = solarInfoBytesArray[5:6]         # scale 1
        rCurrent = solarInfoBytesArray[6:7]         # scale 0.1
        sCurrent = solarInfoBytesArray[7:8]         # scale 0.1
        tCurrent = solarInfoBytesArray[8:9]         # scale 0.1
        genPower = solarInfoBytesArray[9:10]        # kw  # scale 0.1

        accumulationGenPowerLow = solarInfoBytesArray[10:11]  # kwh  # scale 1
        accumulationGenPowerHigh = solarInfoBytesArray[11:12]  # kwh  # scale 1
        accumulationGenPower = [accumulationGenPowerHigh[0], accumulationGenPowerLow[0]]

        todayGenPower = solarInfoBytesArray[12:13]     # kwh  # scale 1
        monthGenPower = solarInfoBytesArray[13:14]     # kwh  # scale 1
        wlSolationAmount = solarInfoBytesArray[14:15]  # W/m2  # scale 1
        inSolationAmount = solarInfoBytesArray[15:16]  # W/m2  # scale 1
        outerTemp = solarInfoBytesArray[16:17]         # outerTemp  # scale 0.1
        moduleTemp = solarInfoBytesArray[17:18]        # moduleTemp  # scale 0.1

        util = Util()

        pvVoltageInt = util.bytes_to_int(pvVoltage)
        pvCurrentInt = util.bytes_to_int(pvCurrent)
        pvPowerInt = util.bytes_to_int(pvPower)*1000
        rVoltageInt = util.bytes_to_int(rVoltage)
        sVoltageInt = util.bytes_to_int(sVoltage)
        tVoltageInt = util.bytes_to_int(tVoltage)
        rCurrentInt = util.bytes_to_int(rCurrent)
        sCurrentInt = util.bytes_to_int(sCurrent)
        tCurrentInt = util.bytes_to_int(tCurrent)
        genPowerInt = util.bytes_to_int(genPower)
        accumulationGenPowerInt = util.bytes_to_int(accumulationGenPower)*1000
        todayGenPowerInt = util.bytes_to_int(todayGenPower)*1000
        monthGenPowerInt = util.bytes_to_int(monthGenPower)*1000
        wlSolationAmountInt = util.bytes_to_int(wlSolationAmount)
        inSolationAmountInt = util.bytes_to_int(inSolationAmount)
        outerTempInt = util.bytes_to_int(outerTemp)
        moduleTempInt = util.bytes_to_int(moduleTemp)

        pvCurrentInt = float(pvCurrentInt) / 10
        pvPowerInt = float(pvPowerInt) / 10
        rCurrentInt = float(rCurrentInt) / 10
        sCurrentInt = float(sCurrentInt) / 10
        tCurrentInt = float(tCurrentInt) / 10
        genPowerInt = float(genPowerInt) / 10
        outerTempInt = float(outerTempInt) / 10
        moduleTempInt = float(moduleTempInt) / 10

        allSolarInfo = "pvVol:" + str(pvVoltageInt) + " pvCur:"+str(pvCurrentInt)+" pvPow:"+str(pvPowerInt)+" rVol:"+str(rVoltageInt)+" sVol:"+str(sVoltageInt)+" tVol:"+str(tVoltageInt)+" rCur:"+str(rCurrentInt)+\
                    " sCur:"+str(sCurrentInt)+" tCur:"+str(tCurrentInt)+" genPow:"+str(genPowerInt)+" accumGenPow:"+str(accumulationGenPowerInt)+" todayGenPow:"+str(todayGenPowerInt)+ \
                    " monthGenPow:"+str(monthGenPowerInt)+ " wlSolAmt:"+str(wlSolationAmountInt)+" inSolAmt:"+str(inSolationAmountInt)+" outTemp:"+str(outerTempInt)+" modTemp:"+str(moduleTempInt)

        #allSolarInfo="test"
        mylogger.info("all solar info = %s", allSolarInfo)
        #print("all solar info = %s", allSolarInfo)

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y%m%d%H%M%S")
        mylogger.info("timestampStr = %s", timestampStr)
        #print("timestampStr = %s", timestampStr)

        con = pymysql.connect(host=self.ip_addr_decoded, port=self.port_decoded, user=self.db_user,
                              passwd=self.pass_decoded, db=self.db_name_decoded, charset='utf8', autocommit=True)
        cur = con.cursor()

        insert_query = """insert into bems_meter_solar(sensor_sn, val_date, total_wh, error_code, info) values (%s, %s, %s, %s, %s)"""
        mylogger.info("insert_query = %s", insert_query)
        val=(sensor_sn, timestampStr, int(accumulationGenPowerInt), int(0), allSolarInfo)
        cur.execute(insert_query, val)
        con.commit()

        update_query = 'update bems_sensor_solar set val_date = \''+timestampStr+ '\', val= '+str(accumulationGenPowerInt)+', error_code=0 where sensor_sn =\'' +sensor_sn+'\''
        mylogger.info("update_query = %s", update_query)
        cur.execute(update_query)

        cur.close()
        con.close()

        return True

    def insertNovelSolarInfo(self, solarInfoBytesArray, sensor_sn):
        offset=0;
        pvVoltage = solarInfoBytesArray[offset:++offset]  # scale 0.1
        pvCurrent = solarInfoBytesArray[offset:++offset]  # scale 0.1
        pvPower = solarInfoBytesArray[offset:++offset]  # scale 0.1
        rVoltage = solarInfoBytesArray[offset:++offset]  # scale 0.1
        rCurrent = solarInfoBytesArray[offset:++offset]  # scale 0.1
        sVoltage = solarInfoBytesArray[offset:++offset]  # scale 0.1
        sCurrent = solarInfoBytesArray[offset:++offset]  # scale 0.1
        tVoltage = solarInfoBytesArray[offset:++offset]  # scale 0.1
        tCurrent = solarInfoBytesArray[offset:++offset]  # scale 0.1
        frequency = solarInfoBytesArray[offset:++offset]  # scale 0.1
        ++offset #reservered
        genPower = solarInfoBytesArray[offset:++offset]  # kw  # scale 0.1
        todayGenPowerLow = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0.1
        todayGenPowerHigh = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0.1
        todayGenPower = [todayGenPowerHigh[0], todayGenPowerLow[0]]
        monthGenPowerLow = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0.1
        monthGenPowerHigh = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0.1
        monthGenPower = [monthGenPowerHigh[0], monthGenPowerLow[0]]
        accumulationGenPowerLow = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0. 1
        accumulationGenPowerHigh = solarInfoBytesArray[offset:++offset]  # kwh  # scale 0.1
        accumulationGenPower = [accumulationGenPowerHigh[0], accumulationGenPowerLow[0]]
        ++offset #reservered
        ++offset #reservered
        ++offset #reservered
        ++offset #reservered
        inSolationAmount = solarInfoBytesArray[offset:++offset]  # W/m2  # inclined # scale 1
        wlSolationAmount = solarInfoBytesArray[offset:++offset]  # W/m2  # horizontal # scale 1

        moduleTemp = solarInfoBytesArray[offset:++offset]  # moduleTemp  # scale 0.1
        outerTemp = solarInfoBytesArray[offset:++offset]  # outerTemp  # scale 0.1

        util = Util()

        pvVoltageInt = util.bytes_to_int(pvVoltage)
        pvCurrentInt = util.bytes_to_int(pvCurrent)
        pvPowerInt = util.bytes_to_int(pvPower) * 100
        rVoltageInt = util.bytes_to_int(rVoltage)
        sVoltageInt = util.bytes_to_int(sVoltage)
        tVoltageInt = util.bytes_to_int(tVoltage)
        rCurrentInt = util.bytes_to_int(rCurrent)
        sCurrentInt = util.bytes_to_int(sCurrent)
        tCurrentInt = util.bytes_to_int(tCurrent)
        frequencyInt= util.bytes_to_int(frequency)
        genPowerInt = util.bytes_to_int(genPower) * 100
        accumulationGenPowerInt = util.bytes_to_int(accumulationGenPower) * 100
        todayGenPowerInt = util.bytes_to_int(todayGenPower) * 100
        monthGenPowerInt = util.bytes_to_int(monthGenPower) * 100
        wlSolationAmountInt = util.bytes_to_int(wlSolationAmount)
        inSolationAmountInt = util.bytes_to_int(inSolationAmount)
        outerTempInt = util.bytes_to_int(outerTemp)
        moduleTempInt = util.bytes_to_int(moduleTemp)

        pvVoltageInt = float(pvVoltageInt) / 10
        pvCurrentInt = float(pvCurrentInt) / 10
        rVoltageInt = float(rVoltageInt) / 10
        sVoltageInt = float(sVoltageInt) / 10
        tVoltageInt = float(tVoltageInt) / 10
        rCurrentInt = float(rCurrentInt) / 10
        sCurrentInt = float(sCurrentInt) / 10
        tCurrentInt = float(tCurrentInt) / 10
        frequencyInt = float(frequencyInt) / 10
        outerTempInt = float(outerTempInt) / 10
        moduleTempInt = float(moduleTempInt) / 10

        # allSolarInfo = "pvVol:" + str(pvVoltageInt) + " pvCur:" + str(pvCurrentInt) + " pvPow:" + str(
        #     pvPowerInt) + " rVol:" + str(rVoltageInt) + " sVol:" + str(sVoltageInt) + " tVol:" + str(
        #     tVoltageInt) + " rCur:" + str(rCurrentInt) + \
        #                " sCur:" + str(sCurrentInt) + " tCur:" + str(tCurrentInt) + " genPow:" + str(
        #     genPowerInt) + " accumGenPow:" + str(accumulationGenPowerInt) + " todayGenPow:" + str(todayGenPowerInt) + \
        #                " monthGenPow:" + str(monthGenPowerInt) + " wlSolAmt:" + str(
        #     wlSolationAmountInt) + " inSolAmt:" + str(inSolationAmountInt) + " outTemp:" + str(
        #     outerTempInt) + " modTemp:" + str(moduleTempInt) + " frequency:" + str(frequencyInt)

        allSolarInfoDict = {}
        allSolarInfoDict['pvVol'] = str(pvVoltageInt)
        allSolarInfoDict['pvCur'] = str(pvCurrentInt)
        allSolarInfoDict['pvPow'] = str(pvPowerInt)
        allSolarInfoDict['rVol'] = str(rVoltageInt)
        allSolarInfoDict['sVol'] = str(sVoltageInt)
        allSolarInfoDict['tVol'] = str(tVoltageInt)
        allSolarInfoDict['rCur'] = str(rCurrentInt)
        allSolarInfoDict['sCur'] = str(sCurrentInt)
        allSolarInfoDict['tCur'] = str(tCurrentInt)
        allSolarInfoDict['genPow'] = str(genPowerInt)
        allSolarInfoDict['accumGenPow'] = str(accumulationGenPowerInt)
        allSolarInfoDict['todayGenPow'] = str(todayGenPowerInt)
        allSolarInfoDict['monthGenPow'] = str(monthGenPowerInt)
        allSolarInfoDict['wlSolAmt'] = str(wlSolationAmountInt)
        allSolarInfoDict['inSolAmt'] = str(inSolationAmountInt)
        allSolarInfoDict['outTemp'] = str(outerTempInt)
        allSolarInfoDict['modTemp'] = str(moduleTempInt)
        allSolarInfoDict['frequency'] = str(frequencyInt)

        allSolarInfo = json.dumps(allSolarInfoDict)

        # allSolarInfo="test"
        mylogger.info("all solar info = %s", allSolarInfo)
        # print("all solar info = %s", allSolarInfo)

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y%m%d%H%M%S")
        mylogger.info("timestampStr = %s", timestampStr)
        # print("timestampStr = %s", timestampStr)

        con = pymysql.connect(host=self.ip_addr_decoded, port=self.port_decoded, user=self.db_user,
                              passwd=self.pass_decoded, db=self.db_name_decoded, charset='utf8', autocommit=True)
        cur = con.cursor()

        insert_query = """insert into energy_meter_three_phase(sensor_sn, val_date, total_wh, error_code, info) values (%s, %s, %s, %s, %s)"""
        mylogger.info("insert_query = %s", insert_query)
        val = (sensor_sn, timestampStr, int(accumulationGenPowerInt), int(0), allSolarInfo)
        cur.execute(insert_query, val)
        con.commit()

        update_query = 'update energy_sensor_inverter set val_date = \'' + timestampStr + '\', val= ' + str(
            accumulationGenPowerInt) + ', error_code=0 where sensor_sn =\'' + sensor_sn + '\''
        mylogger.info("update_query = %s", update_query)
        cur.execute(update_query)

        cur.close()
        con.close()

        return True


    def bytes_to_int(self, bytes):
        result = 0
        for b in bytes:
            result = result * 65536 + int(b)
        return result


class UnitTest(unittest.TestCase):
#    def testGetAcpInfo(self):
#        util = Util()
#        acpInfoList=util.getAcpInfo('2001')
#
#        for acpInfo in acpInfoList:
#            complex_code = acpInfo[0];
#            siteIp = acpInfo[2];
#            sitePort = acpInfo[3];
#            siteUnitId = acpInfo[4];
#
#            print("acp complex_code = %s", complex_code)
#            print("acp siteIp = %s", siteIp)
#            print("acp sitePort = %s", sitePort)
#            print("acp siteUnitId = %s", siteUnitId)

#    def testGetSamChuckSolarInfo(self):
#        util = Util()
#       samChuckInfoList=util.getSamChuckConnInfo('2003')
#
#       for scInfo in samChuckInfoList:
#           complex_code = scInfo[0];
#           siteIp = scInfo[2];
#           sitePort = scInfo[3];
#
#           print("samchuck complex_code = %s", complex_code)
#           print("samchuck siteIp       = %s", siteIp)
#           print("samchuck sitePort     = %s", sitePort)

    # def testInsertSamChuckSolarInfo(self):
    #     util = Util()
    #     solarInfoBytesArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]
    #     util.insertSamChuckSolarInfo(solarInfoBytesArray,'2003_1')
    #
    #     testBytes = b'\x01\x01'
    #     testResult = struct.unpack('>H', testBytes) # big endian , H : 16bit(?)
    #
    #     print testResult
    #
    #     testBytes2 = b'\x01\x01\x00\x02'
    #     testResult = struct.unpack('>HH', testBytes2)
    #
    #     print testResult
    #
    #     elements = [1, 2, 3, 4]
    #     values = bytearray(elements)
    #     result = util.bytes_to_int(values)
    #     print result
    #     #util.bytes_to_int(testBytes2)

   def testByteToInt(self):
        util = Util()

        solarInfoBytesArray = [1,2,2011,1]
        #values = bytearray(elements)
        accumulationGenPowerLow = solarInfoBytesArray[2:3]  # kwh  # scale 1
        accumulationGenPowerHigh = solarInfoBytesArray[3:4]  # kwh  # scale 1

        accumulationGenPower = [accumulationGenPowerHigh[0], accumulationGenPowerLow[0]]

        result = util.bytes_to_int(accumulationGenPower)
        print result
        #util.bytes_to_int(testBytes2)

if __name__ == '__main__':
    unittest.main()

