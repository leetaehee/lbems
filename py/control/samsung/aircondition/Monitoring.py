import unittest
import json
import logging
import datetime
import xmltodict
from io import BytesIO

from logging.handlers import RotatingFileHandler
from MyString import log_path
from xml.etree.ElementTree import Element, SubElement, ElementTree, dump
#-*-coding:utf-8 -*-

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class Monitoring:

    def requestGetMonitoringXml(self):
        mylogger.info("requestGetMonitoringXml")
        #print("requestGetMonitoringXml")

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa=\"mfc\" da=\"dms\" messageType=\"command\" dateTime=\""+timestampStr+"\" dvmControlMode=\"individual\""
        headerNode = Element(header)

        root.append(headerNode)
        secondNode = Element("getMonitoring")
        SubElement(secondNode,"all")

        root.append(secondNode)
        tree=ElementTree(root)

        f=BytesIO()
        tree.write(f, encoding='utf-8', xml_declaration=True)
        xmlString = f.getvalue()

        #print(xmlString)
        mylogger.info("getMonitoring = %s", xmlString)

        return xmlString

    def requestGetStatusUploadXml(self):
        mylogger.info("requestGetStatusUploadXml")
        #print("requestGetStatusUploadXml")

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa=\"mfc\" da=\"dms\" messageType=\"command\" dateTime=\""+timestampStr+"\" dvmControlMode=\"individual\""
        headerNode = Element(header)

        root.append(headerNode)
        secondNode = Element("getStatusUpload")

        root.append(secondNode)
        tree=ElementTree(root)

        f=BytesIO()
        tree.write(f, encoding='utf-8', xml_declaration=True)
        xmlString = f.getvalue()

        #print(xmlString)
        mylogger.info("getStatusUpload = %s", xmlString)

        return xmlString

    def decodingGetMonitoringXml(self, respXml):
        mylogger.info("decodingGetMonitoringXml")
        #print("decodingGetMonitoringXml")
        jsonString = json.dumps(xmltodict.parse(respXml), indent=4)
        jsonString=jsonString.replace('@','');
        mylogger.info(jsonString)

        return self.getAllAirCondInfo(jsonString)

    def getAllAirCondInfo(self, jsonStr):
        mylogger.info("getAllAirCondInfo")
        #print("getAllAirCondInfo")

        jsonObject = json.loads(jsonStr)
        jsonArray=jsonObject.get("root").get("getMonitoring").get("all").get("indoor")

        indoors = []
        indoor = {}
        for list in jsonArray:
            addrValue=list.get("addr");
            indoorDict=list.get("indoorDetail")
            indoorDict['addr']=addrValue;

            indoors.append(indoorDict)

        indoorsJsonType = json.dumps(indoors);
        mylogger.info('all data='+indoorsJsonType)
        #print('all data='+indoorsJsonType)

        return indoorsJsonType


class UnitTest(unittest.TestCase):
    def testRequestGetMonitoringXml(self):
        monitoring = Monitoring()
        monitoring.requestGetMonitoringXml()
        monitoring.requestGetStatusUploadXml()

    def testDecodingGetMonitoringXml(self):
        monitoring = Monitoring()
        respXml='''
            <root>
            <header sa='dms' da='mfc' messageType='response' dateTime='2014-09-18 15:15:54' dvmControlMode='individual'/>
            <getMonitoring>
            <all>
            <indoor addr='11.01.07'><indoorDetail power='off' setTemp='24.0' roomTemp='22.2' tempInterval='0.1' opMode='auto' airSwing_UD='false' airSwing_LR='false' fanSpeed='auto' dischargeCoolSetTemp='-1000' dischargeHeatSetTemp='-1000' dischargeCurrentTemp='-1000' spi='false' humanSensor='false' useHumanSensor='false' useAutoClean='false' useSpi='false' useDischargeSetTemp='false' useOaIntake='false' useOutdoorCool='false' useHumidification='false' useSetHumidity='false' useLRSwing='false' dischargeTempControl='false' useEHSPowerMode='false' useVacancyControl='true' useOpModeLimit='true' useHeatMode='true' useCoolMode='true' error='false' peakStatus='false' evapInTemp='23.1' evapOutTemp='23.0' capaCode='0' modelCode='0' remoconEnable='true' filterWarning='false' defrostOn='false' eev='2000' temperatureScale='Celsius' upperTemperatureLimit='false' upperTemperature='30.0' lowerTemperatureLimit='false' lowerTemperature='18.0' useMode='none' controlMode='none' isTempLimited='false' isScheduled='false' vacancyStatus='false' /></indoor>
            <indoor addr='11.01.06'><indoorDetail power='off' setTemp='24.0' roomTemp='23.4' tempInterval='0.1' opMode='auto' airSwing_UD='false' airSwing_LR='false' fanSpeed='auto' dischargeCoolSetTemp='-1000' dischargeHeatSetTemp='-1000' dischargeCurrentTemp='-1000' spi='false' humanSensor='false' useHumanSensor='false' useAutoClean='true' useSpi='false' useDischargeSetTemp='false' useOaIntake='false' useOutdoorCool='false' useHumidification='false' useSetHumidity='false' useLRSwing='false' dischargeTempControl='false' useEHSPowerMode='false' useVacancyControl='true' useOpModeLimit='true' useHeatMode='true' useCoolMode='true' error='false' peakStatus='false' evapInTemp='23.5' evapOutTemp='23.0' capaCode='0' modelCode='0' remoconEnable='true' filterWarning='false' defrostOn='false' eev='2000' temperatureScale='Celsius' upperTemperatureLimit='false' upperTemperature='30.0' lowerTemperatureLimit='false' lowerTemperature='16.0' useMode='none' controlMode='none' isTempLimited='false' isScheduled='false' vacancyStatus='false' /></indoor>
            </all>
            </getMonitoring>
            </root>
            '''
        monitoring.decodingGetMonitoringXml(respXml)


if __name__ == '__main__':
    unittest.main()
