import requests
import unittest
import logging
from logging.handlers import RotatingFileHandler
from MyString import log_path

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

class RequestControl:

    def request_solar_novel_info(self):
        response = requests.get("localhost:5001/solar/novel/get_solar_status", None)
        mylogger.info("response = %s" , response)

class UnitTest(unittest.TestCase):
    def test_request(self):
        requestControl = RequestControl();
        requestControl.request_solar_novel_info()

if __name__ == '__main__':
    # unittest.main()
    rc = RequestControl()
    rc.request_solar_novel_info()
