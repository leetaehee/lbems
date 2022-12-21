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

class SamChuckSolarGeneration:
    siteIp     = None
    sitePort   = None
    mbc        = None

    def set_solar_samchuck_info(self, complex_code):
        util = UtilModule.Util()
        samChuckInfoList = util.getDeviceConnInfo(complex_code,'solar')
        for scInfo in samChuckInfoList:
            self.siteIp = scInfo[2];
            self.sitePort = int(scInfo[3]);

        mylogger.info("complexCode = %s, siteIp = %s, sitePort= %s" , complex_code, self.siteIp, self.sitePort)
        self.mbc = ModbusClient(host=self.siteIp, port=self.sitePort, auto_open=True)
   
    def get_function_code_3(self):
        util = UtilModule.Util()
        mylogger.info("[get_function_code_3] id = %s" , id)

        response = self.mbc.read_holding_registers(0, 18)

        mylogger.info("response = %s" , response)

        if response != None:
            util.insertSamChuckSolarInfo(response, "2003_1")

        return str(response)


class UnitTest(unittest.TestCase):

    def test_get_fc1(self):
        samChunkGen = SamChuckSolarGeneration()
        samChunkGen.set_solar_samchuck_info('2003')

        response= samChunkGen.get_function_code_3()
        print("get_function_code_3 response = ", response)

if __name__ == '__main__':
    unittest.main()

