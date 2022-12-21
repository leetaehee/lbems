#!/bin/sh

curl --data-urlencode "requester=weather" --data-urlencode "request=weather_ministry_finedust" http://localhost/http/index.php -L
