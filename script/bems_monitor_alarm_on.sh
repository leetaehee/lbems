#!/bin/sh

i=1

while [ $i -lt 2 ]
do
sleep 30

curl --data-urlencode "requester=watchdog" --data-urlencode "request=monitor_alarm_on" http://localhost/http/index.php -L

i=$(($i+1))
done
