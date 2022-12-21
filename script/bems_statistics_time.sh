#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_time" http://localhost/http/index.php -L