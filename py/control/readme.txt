FLASK_APP=/usr/local/flask/lbems_control_aircondtion/MainController.py flask run --port 5001 --host=0.0.0.0 &

http://211.43.14.10:5001/lg/fc1?id=1&complex_code=2001
http://211.43.14.10:5001/lg/fc3?id=1&complex_code=2001

#http://211.43.14.10:5001/lg/fc5?id=1&complex_code=2001?operation=0?cmd=1
#http://211.43.14.10:5001/lg/fc6?id=1&complex_code=2001?operation=0?cmd=1

unit id:  PC  화면 > ACP 시스템 설정> 백랩모드버스>  슬레이브 아이디-10 가 unit_id

----------------------------------------------------------------
SamCheck 어린이집

http://169.56.179.12:5001/solar/samchuck/fc3?complex_code=2003

---------------------------------------------------------------
다도해 에어컨
local test
http://localhost:5000/api/dms/get/0?complex_code=2005

서버
http://www.lbems.com:5001/api/dms/get/0?complex_code=2005
http://169.56.179.12:5001/api/dms/set/0/<address>/power_on?complex_code=2005
http://169.56.179.12:5001/api/dms/set/0/<address>/power_off?complex_code=2005

example)
http://169.56.179.12:5001/api/dms/set/0/12.03.02/power_off?complex_code=2005

