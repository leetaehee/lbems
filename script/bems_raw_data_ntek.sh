#!/bin/sh

# 엔텍 데이터 받아서 meter 넣기 (무등산 제외)
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_meter_ntek" http://localhost/http/index.php -L
