#!/bin/sh

# 열량계 raw-data 추가
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_meter_cnc" http://localhost/http/index.php -L