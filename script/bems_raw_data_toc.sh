#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_meter_toc" http://localhost/http/index.php -L