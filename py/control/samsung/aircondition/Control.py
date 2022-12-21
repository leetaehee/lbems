import unittest
import json
import logging
import datetime
import xmltodict
from io import BytesIO

from logging.handlers import RotatingFileHandler
from MyString import log_path
from xml.etree.ElementTree import Element, SubElement, ElementTree, dump
from xml.etree import ElementTree as etree
#-*-coding:utf-8 -*-

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class Control:

    def requestSetDeviceControlXml(self, address, cmds):
        mylogger.info("requestSetDeviceControlXml")
        #print("requestSetDeviceControlXml")

        controlDict={}
        cmdList=cmds.split("__");
        for cmdStr in cmdList:
            cmdStrList = cmdStr.split("_")
            cmd=cmdStrList[0]
            operation=cmdStrList[1]
            controlDict[cmd]=operation

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa='mfc' da='dms' messageType='request' dateTime='"+timestampStr+"' dvmControlMode='individual'"
        SubElement(root, header)

        eleName1 = 'setDeviceControl'
        eleName2 = 'controlList'
        eleName3 = 'control'
        eleName3_1 = 'controlValue'
        eleName3_2 = 'addressList'
        eleName3_2_1 = 'address'

        name1=SubElement(root, eleName1)                 # setDeviceControl
        name2=SubElement(name1,eleName2)                 # controlList
        name3=SubElement(name2, eleName3)                # control
        name3_1=SubElement(name3, eleName3_1)            # controlValue

        for key in controlDict:
            val = controlDict[key]
            SubElement(name3_1, key).text = val

        name3_2=SubElement(name3, eleName3_2)            # addressList
        SubElement(name3_2, eleName3_2_1).text=address   # address

        reqControlXml= etree.tostring(root, encoding='utf-8', method='xml' )
        mylogger.info(reqControlXml)

        return reqControlXml

    def decodingSetDeviceControlXml(self, respXml):
        mylogger.info("decodingSetDeviceControlXml")
        #print("decodingSetDeviceControlXml")
        jsonString = json.dumps(xmltodict.parse(respXml), indent=4)
        jsonString=jsonString.replace('@','');
        #print(jsonString)

        return jsonString


class UnitTest(unittest.TestCase):
    def testRequestSetDeviceControlXml(self):
        control = Control()
        control.requestSetDeviceControlXml('00.11.22', "setTemp_25.0__opMode_cool")

    def testDecodingSetDeviceControlXml(self):
        control = Control()
        respXml='''
            <root>
            <header sa="mfc" da="dms" messageType="response" dateTime="2014-09-18T19:37:02:115" dvmControlMode="individual" />
            <setDeviceControl> 
                <controlList> 
                    <control>
                        <controlValue> 
                            <power>on</power> 
                            <operationMode>cool</operationMode> 
                        </controlValue>
                            <addressList>
                            <address>11.00.02</address> 
                        </addressList> 
                    </control>
                </controlList>
            </setDeviceControl>
            </root>
        '''
        control.decodingSetDeviceControlXml(respXml)

if __name__ == '__main__':
    unittest.main()
