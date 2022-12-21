import unittest
import json
import logging
import datetime
import socket
import sys
import AuthProcessing
import InstalledInfo
import Monitoring
import Control
import UtilModule

from logging.handlers import RotatingFileHandler
from MyString import log_path
from xml.etree.ElementTree import Element, SubElement, ElementTree, dump
#-*-coding:utf-8 -*-

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class TcpClient:
    siteIp = None
    sitePort = None

    def __init__(self, complexCode):
         util = UtilModule.Util()
         dmsList = util.getDeviceConnInfo(complexCode, 'dms')

         for scInfo in dmsList:
             self.siteIp = scInfo[2]
             self.sitePort = scInfo[3]

         #self.siteIp = '121.149.58.203'
         #self.sitePort = 11000

    def processGetMonitoring(self):
        decodedMonitoringXml=''
        if self.siteIp == None:
            return None

        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        server_address = (self.siteIp, self.sitePort)
        #print(server_address)
        mylogger.info("server_address = %s" , server_address)
        sock.connect(server_address)

        authProcessing = AuthProcessing.AuthProcessing()
        passCheckXml = authProcessing.requestPasswordCheckingXml()
        respXml = self.sendAndRecv(passCheckXml, sock)

        if(len(respXml)<=0):
            mylogger.info("authProcessing fail")
            #print("authProcessing fail")

            sock.close()
            return 'NOK'

        status = authProcessing.decodingPasswordCheckingStatusXml(respXml)
        mylogger.info("password checking : "+status)
        #print("password checking : "+status)

        if (status == 'true'):
            serialNumberXml = authProcessing.requestSerialNumberXml()
            respXml = self.sendAndRecv(serialNumberXml, sock)

            decodedSerialNo = authProcessing.decodingSerialNumberXml(respXml)
            mylogger.info("serialNo : " + decodedSerialNo)
            #print("serialNo : " + decodedSerialNo)

            installedInfo = InstalledInfo.InstalledInfo()
            installXml = installedInfo.requestInstalledInfoXml()

            respXml = self.sendAndRecv(installXml, sock)

            decodedInstalledInfo = installedInfo.decodingInstalledInfoXml(respXml)
            mylogger.info("all InstalledInfo : " + str(decodedInstalledInfo))
            #print("all InstalledInfo : " + str(decodedInstalledInfo))

            monitoring = Monitoring.Monitoring()
            monitoringXml = monitoring.requestGetMonitoringXml()

            respXml = self.sendAndRecv(monitoringXml, sock)
            #print("length=" + str(len(respXml)))
            mylogger.info("length=" + str(len(respXml)))

            decodedMonitoringXml= monitoring.decodingGetMonitoringXml(respXml)
            mylogger.info("decodedMonitoringXml : " + decodedMonitoringXml + "\n length="+str(len(respXml)))
            #print("decodedMonitoringXml : " + decodedMonitoringXml + "\n length="+str(len(respXml)))

        sock.close()

        mylogger.info("closing socket")
        #print("closing socket")

        return decodedMonitoringXml

    def processControlAircondition(self, address, cmds):
        mylogger.info("processControlAircondition......")

        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        server_address = (self.siteIp, self.sitePort)
        mylogger.info(server_address)
        sock.connect(server_address)

        #print("AuthProcessing......")

        authProcessing = AuthProcessing.AuthProcessing()
        passCheckXml = authProcessing.requestPasswordCheckingXml()
        respXml = self.sendAndRecv(passCheckXml, sock)

        if(len(respXml)<=0):
            mylogger.info("authProcessing fail")
            #print("authProcessing fail")

            sock.close()
            return 'NOK'

        status = authProcessing.decodingPasswordCheckingStatusXml(respXml)
        mylogger.info("password checking : "+status)
        #print("password checking : "+status)

        if (status == 'true'):
            serialNumberXml = authProcessing.requestSerialNumberXml()
            respXml = self.sendAndRecv(serialNumberXml, sock)

            decodedSerialNo = authProcessing.decodingSerialNumberXml(respXml)
            mylogger.info("serialNo : " + decodedSerialNo)
            #print("serialNo : " + decodedSerialNo)

            installedInfo = InstalledInfo.InstalledInfo()
            installXml = installedInfo.requestInstalledInfoXml()

            respXml = self.sendAndRecv(installXml, sock)

            decodedInstalledInfo = installedInfo.decodingInstalledInfoXml(respXml)
            mylogger.info("all InstalledInfo : " + str(decodedInstalledInfo))
            #print("all InstalledInfo : ", str(decodedInstalledInfo))

            control=Control.Control()

            controlXml=control.requestSetDeviceControlXml(address, cmds)
            respXml = self.sendAndRecv(controlXml, sock)

            decodedControlInfo = control.decodingSetDeviceControlXml(respXml)
            mylogger.info("control info result : " + decodedControlInfo+ "\n length="+str(len(respXml)))
            #print("control info result : " , decodedControlInfo)
            mylogger.info("length="+str(len(respXml)))

        sock.close()
        mylogger.info("closing socket")
        #print("closing socket")

        return 'ok'

    def sendAndRecv(self, message, sock):
        mylogger.info("call sendAndRecv")

        BUFF_SIZE = 50000  # 1 KiB
        #data = b''
        data=''
        part=''

        try:
            # Send data
            mylogger.info("sended message : \n" + message)
            #print("sended message : \n" + message)

            #sock.sendall(message.encode('utf-8'))
            #sock.send(message.encode('utf-8'))
            sock.send(message)

            loop=0
            # Look for the response
            while True:
                part = sock.recv(BUFF_SIZE)
                data += part.decode('utf-8')
                #print("part message", part)
                if len(part) < BUFF_SIZE:
                    # either 0 or end of data
                    #break
                    #print("part message" + part)
                    if part.find("</root>") > 0 :
                        break
                else:    
                    loop = loop+1
                    if(loop == 3):
                        break


        finally:
            #mylogger.info("received message" + data.decode('utf-8'))
            mylogger.info("received message " + data)
            #print("received message" + data)
            #print("received message")

        return data

if __name__ == '__main__':
    tcpClient = TcpClient('2005')
    tcpClient.processGetMonitoring()
    #tcpClient.processControlAircondition('00.01.11','setTemp_25.0__opMode_cool')
    #tcpClient.processControlAircondition('12.03.02','power_off')
