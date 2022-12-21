from flask import Flask, jsonify
from flask import request
import logging
import LgModule
import SolarGeneration
from samsung.aircondition import TcpClient
from light.panasonic import ControllerModule
from solar.novel import NovelControllerModule
from logging.handlers import RotatingFileHandler
from MyString import log_path
import unittest

app = Flask(__name__)

mylogger = logging.getLogger()
mylogger.setLevel(logging.INFO)
rotatingHandler = logging.handlers.RotatingFileHandler(log_path, mode='a', maxBytes=1000000, backupCount=7)
mylogger.addHandler(rotatingHandler)

@app.route("/lg/fc1", methods=['GET'], endpoint='e1')
@app.route("/lg/fc3", methods=['GET'], endpoint='e2')
@app.route("/lg/fc5", methods=['GET'], endpoint='e3')
@app.route("/lg/fc6", methods=['GET'], endpoint='e4')
def lg_acp():
    id = int(request.args.get('id'))
    complexCode = request.args.get('complex_code')
    operation = None;
    cmd = None;

    if request.args.get('operation') != None:
        operation = int(request.args.get('operation'))

    if request.args.get('cmd') != None:
        cmd = int(request.args.get('cmd'))

    mylogger.info('### complex = %s, id = %s, operation = %s, cmd = %s', complexCode, id, operation, cmd)

    lgAcp = LgModule.LgAcp()
    lgAcp.set_acp_info(complexCode)

    if request.endpoint == 'e1' : 
        answer = lgAcp.get_function_code_1(id)                  
    elif request.endpoint == 'e2' : 
        answer = lgAcp.get_function_code_3(id)                
    elif request.endpoint == 'e3' : 
        answer = lgAcp.get_function_code_5(id, operation, cmd) 
    elif request.endpoint == 'e4' : 
        answer = lgAcp.get_function_code_6(id, operation, cmd)

    return answer

@app.route("/solar/samchuck/fc3", methods=['GET'], endpoint='e5')
def solar_power_generation():
    complexCode = request.args.get('complex_code')
    mylogger.info('### complex_code = %s', complexCode)
    #print('### complex_code = %s', complexCode)

    if request.endpoint == 'e5' :
        samChuckSolarGen = SolarGeneration.SamChuckSolarGeneration()
        samChuckSolarGen.set_solar_samchuck_info(complexCode)
        answer = samChuckSolarGen.get_function_code_3()                  

    return answer

@app.route("/api/dms/get/0", methods=['GET'], endpoint='e6')
def samsung_aircondition_monitoring():

    complexCode = request.args.get('complex_code')
    mylogger.info('### complex_code = %s'+ complexCode)
    #print('### complex_code =%s', complexCode)

    if request.endpoint == 'e6':
        tcpClient = TcpClient.TcpClient(complexCode)
        answer = tcpClient.processGetMonitoring()

        #answer = 'OK'

    return answer

@app.route("/api/dms/set/0/<address>/<cmd>", methods=['GET'], endpoint='e7')
def samsung_aircondition_control(address=None, cmd=None):

    complexCode = request.args.get('complex_code')
    mylogger.info('### complex_code =%s', complexCode)
    #print('### complex_code =%s', complexCode)

    if request.endpoint == 'e7':
        tcpClient = TcpClient.TcpClient(complexCode)
        answer = tcpClient.processControlAircondition(address,cmd)

        #answer = 'OK'

    return answer

@app.route("/light/panasonic/get", methods=['GET'], endpoint='e8')
def get_light_status():

    complexCode = request.args.get('complex_code')
    id = int(request.args.get('id'))

    mylogger.info('### complex_code = '+ complexCode)
    mylogger.info('### id = %d', id)

    response = 'None'
    if request.endpoint == 'e8':
        control = ControllerModule.LightControl(complexCode)
        response = control.set_modbus()
        if response != 'None':
            response = control.get_function_code_1(id)

    mylogger.info('### response = %s', str(response))

    return str(response[0])

@app.route("/light/panasonic/set", methods=['GET'], endpoint='e9')
def set_light_status():

    complexCode = request.args.get('complex_code')
    id = int(request.args.get('id'))
    cmd = request.args.get('cmd')

    mylogger.info('### complex_code = '+ complexCode)
    mylogger.info('### id  = %d', id)
    mylogger.info('### cmd = %s', cmd)

    cmd_id = 0x0000
    if cmd == 'on' :
        cmd_id = 0xff00
    elif cmd == 'off':
        cmd_id = 0x0000
    else:
        return 'None'

    mylogger.info('### cmd_id = %d', cmd_id)

    response = 'None'
    if request.endpoint == 'e9':
        control = ControllerModule.LightControl(complexCode)
        response = control.set_modbus()
        if response != 'None':
            response = control.get_function_code_5(id, cmd_id)

    mylogger.info('### response = %s', str(response))

    return str(response)


@app.route("/solar/novel/get_solar_status", methods=['GET'], endpoint='e10')
def set_light_status():

    complexCode = request.args.get('complex_code')
    id = int(request.args.get('id'))

    mylogger.info('### complex_code = '+ complexCode)
    mylogger.info('### id  = %d', id)

    response = 'None'

    control = NovelControllerModule.SolarControl(complexCode)
    response = control.set_modbus()
    if response != 'None':
        response = control.get_function_code_3(id)

    mylogger.info('### response = %s', str(response))

    return str(response)


@app.route("/")
def hello_world():
    #complexCode = request.args.get('complex_code')
    #tcpClient = TcpClient.TcpClient()
    #return tcpClient.processGetMonitoring(complexCode)
    return 'Hello World MainController'


class UnitTest(unittest.TestCase):
    def test_get_light_control(self):
        control = ControllerModule.LightControl('0004')
        control.get_function_code_1(1)

        response = control.set_modbus()
        if response != None:
            response = control.get_function_code_1(id)
            print('### response = %s', response)


if __name__ == '__main__':
    app.run()
