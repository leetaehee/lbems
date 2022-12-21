#!/bin/sh

curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_complex_data_toc" http://localhost/http/index.php -L