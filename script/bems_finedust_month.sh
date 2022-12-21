#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_finedust_month" http://localhost/http/index.php -L

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_co2_month" http://localhost/http/index.php -L

