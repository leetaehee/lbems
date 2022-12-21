#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_mdmt" http://localhost/http/index.php -L