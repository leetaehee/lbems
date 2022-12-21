import unittest
import json
import logging
import datetime
from io import BytesIO

from logging.handlers import RotatingFileHandler
from MyString import log_path
from xml.etree.ElementTree import Element, SubElement, ElementTree, dump
#-*-coding:utf-8 -*-
from xml.etree import ElementTree as etree

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class InstalledInfo:

    def requestInstalledInfoXml(self):
        mylogger.info("requestInstalledInfoXml")
        #print("requestInstalledInfoXml")

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa=\"mfc\" da=\"dms\" messageType=\"command\" dateTime=\""+timestampStr+"\" dvmControlMode=\"individual\""
        headerNode = Element(header)

        root.append(headerNode)
        rangeNode = Element("treeInfoEx range=\"all\"")
        root.append(rangeNode)
        tree=ElementTree(root)

        f=BytesIO()
        tree.write(f, encoding='utf-8', xml_declaration=True)
        xmlString = f.getvalue()

        #print(xmlString)
        mylogger.info("password checking = %s", xmlString)

        return xmlString

    def decodingInstalledInfoXml(self, respXml):
        mylogger.info("decodingInstalledInfoXml")
        #print("decodingInstalledInfoXml")

        root=etree.fromstring(respXml.encode('utf-8'))
        ack=root.find('treeInfoEx')
        dmsVersion=ack.get('dmsVersion')

        mylogger.info("dmsVersion = %s"+ dmsVersion)
        #print("dmsVersion = "+ dmsVersion)

        indoors=[]
        indoor = {}
        indoor['dmsVersion']=dmsVersion
        indoors.append(indoor)

        iter_element = root.iter(tag="indoor")

        for element in iter_element:
            indoor = {}
            indoor['addr'] = element.get('addr')
            indoor['type'] = element.get('indoorType')
            indoors.append(indoor)

        mylogger.info(indoors)

        return indoors;

class UnitTest(unittest.TestCase):
    def testRequestInstalledInfoXml(self):
        installedInfo = InstalledInfo()
        installedInfo.requestInstalledInfoXml()

    def testDecodingInstalledInfoXml(self):
        installedInfo = InstalledInfo()
        respXml='''
            <root>
            <header sa='dms' da='mfc' messageType='response' dateTime='2011-06-07 15:41:13' dvmControlMode='individual'/>
            <treeInfoEx range='all' updateDate='2014-09-02 14:31:27' DDCUpdateDate=' ' dmsVersion='2.6.2.0' temperatureScale='Celsius'>
            <indoorList>
            <indoor addr='11.00.03' rmcAddr='03' modelCode='03 00 51 FF 15 07 A6 0E FF' indoorType='indoor' version='141212' name='HRconsole'/>
            <indoor addr='11.00.02' rmcAddr='02' modelCode='02 00 34 FF 15 07 A6 0E FF' indoorType='indoor' version='141212' name='HRmini4Way'/>
            </indoorList>
            </treeInfoEx>
            </root>
            '''
        installedInfo.decodingInstalledInfoXml(respXml)

if __name__ == '__main__':
    unittest.main()
