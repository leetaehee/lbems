#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_day" http://localhost/http/index.php -L

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_month" http://localhost/http/index.php -L

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_finedust_day" http://localhost/http/index.php -L

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_co2_day" http://localhost/http/index.php -L