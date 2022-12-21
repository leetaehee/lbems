#!/bin/sh

# mqtt 서버에서 미세먼지 수신하기 
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_finedust" http://localhost/http/index.php -L
