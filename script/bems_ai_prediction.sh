#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_ai_prediction" http://localhost/http/index.php -L