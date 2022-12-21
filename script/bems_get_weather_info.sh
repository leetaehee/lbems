#!/bin/sh

curl --data-urlencode "requester=weather" --data-urlencode "request=weather_open_api" http://localhost/http/index.php -L
