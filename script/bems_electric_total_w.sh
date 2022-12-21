#!/bin/sh

sleep 2m

# 무등산
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_mdmt" http://localhost/http/index.php -L

# 태백산
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_tbmt" http://localhost/http/index.php -L

# 대전네드사옥
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_nedOb" http://localhost/http/index.php -L

# 빛사랑어린이집
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_scnr" http://localhost/http/index.php -L

# 방배동근린생활시설
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_bangbae" http://localhost/http/index.php -L

# 다도해 국립공원
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_dado" http://localhost/http/index.php -L

# 김해 행정복지센터
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_khc" http://localhost/http/index.php -L

# 김해 소상공인 물류센터
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_ksbc" http://localhost/http/index.php -L

# 장애인 내일키움 직업교육센터
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_hjecc" http://localhost/http/index.php -L

# 새마을중앙연수원
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_sct" http://localhost/http/index.php -L

# 북한산국립공원
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_bhmt" http://localhost/http/index.php -L

# 한국식품연구원
curl --data-urlencode "requester=watchdog" --data-urlencode "request=add_electric_all_kfl" http://localhost/http/index.php -L