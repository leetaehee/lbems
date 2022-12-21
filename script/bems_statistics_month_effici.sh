#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_efficiency_day" http://localhost/http/index.php -L

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_efficiency_month" http://localhost/http/index.php -L