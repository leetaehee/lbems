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

class AuthProcessing:

    def requestPasswordCheckingXml(self):
        mylogger.info("requestPasswordCheckingXml")
        #print("requestPasswordCheckingXml")

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa=\"guest\" da=\"dms\" messageType=\"command\" dateTime=\""+timestampStr+"\" dvmControlMode=\"individual\""
        headerNode = Element(header)

        root.append(headerNode)
        passwordNode = Element("passwordAuth password=\"1234\"")
        root.append(passwordNode)
        tree=ElementTree(root)

        f=BytesIO()
        tree.write(f, encoding='utf-8', xml_declaration=True)
        xmlString = f.getvalue()

        #print("### password checking:"+xmlString)
        mylogger.info("### password checking = %s", xmlString)

        return xmlString

    def requestSerialNumberXml(self):
        mylogger.info("requestSerialNumberXml")
        #print("requestSerialNumberXml")

        dateTimeObj = datetime.datetime.now();
        timestampStr = dateTimeObj.strftime("%Y-%m-%d %H:%M:%S")
        mylogger.info("timestampStr = %s", timestampStr)

        root = Element("root")
        header="header sa=\"mfc\" da=\"dms\" messageType=\"command\" dateTime=\""+timestampStr+"\" dvmControlMode=\"individual\""
        headerNode = Element(header)

        root.append(headerNode)
        serialNode = Element("shakeSerialNo serialNo=\"SNET20041209094600000\"")
        root.append(serialNode)
        tree=ElementTree(root)

        f=BytesIO()
        tree.write(f, encoding='utf-8', xml_declaration=True)
        xmlString = f.getvalue()

        #print("serial Number:"+xmlString)
        mylogger.info("serial Number = %s", xmlString)

        return xmlString

    def decodingPasswordCheckingStatusXml(self, respXml):
        mylogger.info("decodingPasswordCheckingXml")
        #print("decodingPasswordCheckingXml")

        root=etree.fromstring(respXml)
        ack=root.find('ack')
        status=ack.get('status')

        mylogger.info("status = %s"+ status)
        #print("status = "+ status)

        return status;

    def decodingSerialNumberXml(self, respXml):
        mylogger.info("decodingSerialNumberXml")
        #print("decodingSerialNumberXml")

        root=etree.fromstring(respXml)
        ack=root.find('shakeSerialNo')
        serialNo=ack.get('serialNo')

        mylogger.info("serialNo = %s"+ serialNo)
        #print("serialNo = "+ serialNo)

        return serialNo;

class UnitTest(unittest.TestCase):
    def testRequestPasswordChecking(self):
        authProcessing = AuthProcessing()
        authProcessing.requestPasswordCheckingXml()
        authProcessing.requestSerialNumberXml()

    def testDecodingPasswordCheckingXml(self):
        authProcessing = AuthProcessing()
        respXml='''
        <root>
            <header sa="dms" da="guest" messageType="ack" dateTime="2014-09-12 14:04:48" dvmControlMode="individual"/>
            <ack methodName="passwordAuth" status="true" description=""/>
        </root>'''

        authProcessing.decodingPasswordCheckingStatusXml(respXml)

    def testDecodingSerialNumberXml(self):
        authProcessing = AuthProcessing()
        respXml='''
        <root>
            <header sa='dms' da='guest' messageType='ack' dateTime='2014-09-12 14:08:39' dvmControlMode='individual'/>
            <shakeSerialNo serialNo='DMS20140912140839898'/>
        </root>
        '''

        authProcessing.decodingSerialNumberXml(respXml)
if __name__ == '__main__':
    unittest.main()
