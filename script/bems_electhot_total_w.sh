#!/bin/sh

sleep 2m

# 무등산
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_elechot_mdmt" http://localhost/http/index.php -L
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_elevent_mdmt" http://localhost/http/index.php -L

# 태백산
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_elechot_tbmt" http://localhost/http/index.php -L
