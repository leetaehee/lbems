from pyModbusTCP.client import ModbusClient
import unittest
import logging
import UtilModule
from logging.handlers import RotatingFileHandler
from MyString import log_path

print("Content-Type: application/json\n")

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class SolarControl:
    siteIp     = None
    sitePort   = None
    mbc        = None

    def __init__(self, complexCode):
        util = UtilModule.Util()
        dmsList = util.getDeviceConnInfo(complexCode, 'solar')

        for scInfo in dmsList:
            self.siteIp = scInfo[2]
            self.sitePort = scInfo[3]

    def set_modbus(self):
        if self.siteIp == None:
            return 'None'

        mylogger.info("siteIp = %s, sitePort= %d" , self.siteIp, self.sitePort)
        self.mbc = ModbusClient(host=self.siteIp, port=self.sitePort, auto_open=True)
   
    def get_function_code_3(self, id):
        mylogger.info("[get_function_code_3] id = %s" , id)

        # TCP auto connect on first modbus request
        response = self.mbc.read_holding_registers(id*0, 26)

        mylogger.info("response = %s", response)

        if response != None:
            util = UtilModule.Util()
            util.insertNovelSolarInfo(response, "-")

        return str(response)

class UnitTest(unittest.TestCase):
    def test_get_fc1(self):
        control = SolarControl()
        control.set_acp_info('2002')

        response= control.get_function_code_3(11)
        print("get_function_code_3 response = ", response)

if __name__ == '__main__':
    unittest.main()

