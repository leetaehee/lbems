#!/bin/sh

# 단지정보 전송
curl --data-urlencode "requester=integration" --data-urlencode "request=integration_complex_send" http://localhost/http/index.php -L