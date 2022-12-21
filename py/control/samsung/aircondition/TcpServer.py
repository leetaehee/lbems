import unittest
import json
import logging
import datetime
import socket

from logging.handlers import RotatingFileHandler
from MyString import log_path

#-*-coding:utf-8 -*-

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class TcpServer:

    def processClientMsg(self):


        host = "127.0.0.1"
        port = 11000

        serverSocket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        serverSocket.bind((host, port))
        serverSocket.listen(1)

        connectionSocket, addr = serverSocket.accept()

        print(str(addr), "connected.")

        data = connectionSocket.recv(1024)
        print("rcv message :", data.decode("utf-8"))

        respXml=self.getRespMsg(4);

        connectionSocket.send(respXml.encode("utf-8"))
        print("send msg")

        serverSocket.close()

    def getRespMsg(self, selected):

        respXml=''

        if(selected == 1):
            # PasswordAuth
            respXml = '''
             <root>
                 <header sa="dms" da="guest" messageType="ack" dateTime="2014-09-12 14:04:48" dvmControlMode="individual"/>
                 <ack methodName="passwordAuth" status="true" description=""/>
             </root>'''
        elif(selected == 2):
            #SerialNumber
            respXml = '''
              <root>
                  <header sa='dms' da='guest' messageType='ack' dateTime='2014-09-12 14:08:39' dvmControlMode='individual'/>
                  <shakeSerialNo serialNo='DMS20140912140839898'/>
              </root>
              '''
        elif(selected == 3):
            #installed
            respXml = '''
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
        elif(selected==4):
            #Monitoring
            respXml = '''
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
        elif(selected==5):
            #control
            respXml = '''
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

        return respXml

if __name__ == '__main__':
    tcpServer = TcpServer()
    tcpServer.processClientMsg()