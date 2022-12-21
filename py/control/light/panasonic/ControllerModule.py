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

class LightControl:
    siteIp     = None
    sitePort   = None
    mbc        = None

    def __init__(self, complexCode):
        util = UtilModule.Util()
        dmsList = util.getDeviceConnInfo(complexCode, 'light')

        for scInfo in dmsList:
            self.siteIp = scInfo[2]
            self.sitePort = scInfo[3]

    def set_modbus(self):
        if self.siteIp == None:
            return 'None'

        mylogger.info("siteIp = %s, sitePort= %d" , self.siteIp, self.sitePort)
        self.mbc = ModbusClient(host=self.siteIp, port=self.sitePort, auto_open=True)
   
    def get_function_code_1(self, id):
        mylogger.info("[get_function_code_1] id = %s" , id)

        # TCP auto connect on first modbus request
        response = self.mbc.read_coils(id, 2)

        if response == None:
            return 'None'

        mylogger.info("response = %s" + str(response))
        return response

    def get_function_code_5(self, id, cmd):
        mylogger.info("[get_function_code_5] id = %s, cmd = %s" , id, cmd)

        response = self.mbc.write_single_coil(id, cmd)

        if response == None:
            return 'None'

        mylogger.info("response = %s" , str(response))

        return response

class UnitTest(unittest.TestCase):
    def test_get_fc1(self):
        lightControl = LightControl()
        lightControl.set_acp_info('2002')
        #response= lightControl.get_function_code_1(0)
        #print("get_function_code_1 response = ", response)

        response= lightControl.get_function_code_3(11)
        print("get_function_code_3 response = ", response)

        #response= lightControl.get_function_code_6(1,2,22)
        #rint("get_function_code_6 response = ", response)

        #response= lightControl.get_function_code_6(9,3,30)
        #print("get_function_code_6 response = ", response)

        #response= lightControl.get_function_code_6(10,4,16)
        #print("get_function_code_6 response = ", response)
if __name__ == '__main__':
    unittest.main()

