#!/bin/sh

# 전기 전송
curl --data-urlencode "requester=integration" --data-urlencode "request=integration_electric_send" http://localhost/http/index.php -L

# 가스 전송
curl --data-urlencode "requester=integration" --data-urlencode "request=integration_gas_send" http://localhost/http/index.php -L

# 태양광 전송
curl --data-urlencode "requester=integration" --data-urlencode "request=integration_solar_send" http://localhost/http/index.php -L

# 환경센서 전송
curl --data-urlencode "requester=integration" --data-urlencode "request=integration_finedust_send" http://localhost/http/index.php -L