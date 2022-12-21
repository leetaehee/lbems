#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=monitor_alarm_off" http://localhost/http/index.php -L
