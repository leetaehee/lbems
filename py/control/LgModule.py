from pyModbusTCP.client import ModbusClient
import unittest
import json
import logging
import UtilModule
from logging.handlers import RotatingFileHandler
from MyString import log_path

print("Content-Type: application/json\n")

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class LgAcp:
    siteIp     = None
    sitePort   = None
    siteUnitId = None
    mbc        = None

    def set_acp_info(self, complex_code):
        util = UtilModule.Util()
        acpInfoList = util.getAcpInfo(complex_code)
        for acpInfo in acpInfoList:
            self.siteIp = acpInfo[2];
            self.sitePort = int(acpInfo[3]);
            self.siteUnitId = int(acpInfo[4]);

        mylogger.info("siteIp = %s, sitePort= %s, siteUnitId = %s" , self.siteIp, self.sitePort, self.siteUnitId)
        self.mbc = ModbusClient(host=self.siteIp, port=self.sitePort, unit_id=self.siteUnitId, auto_open=True)
   
    def get_function_code_1(self, id):
        mylogger.info("[get_function_code_1] id = %s" , id)

        # TCP auto connect on first modbus request
        # mbc = ModbusClient(host=self.siteIp, port=self.sitePort, unit_id=self.siteUnitId, auto_open=True)
        response = self.mbc.read_coils(id*16, 7)

        mylogger.info("response = %s" , response)
        return str(response)

    def get_function_code_3(self, id):
        mylogger.info("[get_function_code_3] id = %s" , id)

        # TCP auto connect on first modbus request
        response = self.mbc.read_holding_registers(id*16, 11)

        mylogger.info("response = %s" , response)
        return str(response)

    def get_function_code_5(self, id, operation, cmd):
        mylogger.info("[get_function_code_5] id = %s, operation = %s, cmd = %s" , id, operation, cmd)

        response = self.mbc.write_single_coil((id*16)+operation, cmd)
        mylogger.info("response = %s" , response)
        return str(response)

    def get_function_code_6(self, id, operation, cmd):
        mylogger.info("[get_function_code_6] id = %s, operation = %s, cmd = %s" , id, operation, cmd)
        response = self.mbc.write_single_register((id*16)+operation, cmd)

        mylogger.info("response = %s" , response)
        return str(response)

class UnitTest(unittest.TestCase):
    def test_get_fc1(self):
        lgAcp = LgAcp()
        lgAcp.set_acp_info('2002')
        #response= lgAcp.get_function_code_1(0)
        #print("get_function_code_1 response = ", response)

        response= lgAcp.get_function_code_3(11)
        print("get_function_code_3 response = ", response)

        #response= lgAcp.get_function_code_6(1,2,22)
        #rint("get_function_code_6 response = ", response)

        #response= lgAcp.get_function_code_6(9,3,30)
        #print("get_function_code_6 response = ", response)

        #response= lgAcp.get_function_code_6(10,4,16)
        #print("get_function_code_6 response = ", response)
if __name__ == '__main__':
    unittest.main()

