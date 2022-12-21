#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=arrange_status_type_month" http://localhost/http/index.php -L