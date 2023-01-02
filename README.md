# LBEMS 개발 가이드 라인

[PHP 정보]
1. PHP 7.4 
2. PHP PSR 준수
3. 비트나미 또는 리눅스에서 실행 필요
4. 객체지향프로그래밍 지식 필요 

[구글드라이브]
1. 설정파일과 필요한 라이브러리들은 구글 드라이브에 있음
2. 추후 composer 를 이용해서 연동 할 수 있도록 할 예정 
3. 처음 사용할 경우 권한을 요청 바랍니다.

[계정 정보]
1. 소스에서 api 비밀번호, db 계정정보는 .env 파일에서 관리 할 것
2. env파일이 없는 경우 .env.example 파일을 복사해서 .env로 만들 것

[숨김파일]
1. 아래 파일들은 구글 드라이브에서 다운 받을 것 

        libs/php
            /PHPExcel
            /PHPMailer
            /Fee

        libs/js
            /sheetjs
            /magnific-popup
            /jquery
            /filesaver
            /chartjs
            /billboard
            /datatables
            /devextreme
            /swiper-4.5.0
            /validation

[네임스페이스] 

        EMS_Module 에너지 관리 시스템과 관련된 공통 소스 
        Http
            /Parser 요청 그룹 
            /Command 요청 처리 
            /Sensor 업체 정보 요청 
        Module 개발과 관련된 공통 소스

[업체별 분기처리 시 방법]

[외부 API]
1. 날씨 (openWeatherMap)
2. 온,습도 (openWeatherMap)
3. 미세먼지(공공데이터)
4. 공휴일 (공공데이터)
5. SMS (가비아/유료)
6. SMTP (구글)

[데이터 수신]

[설정파일]
1. .env 을 추가 한다.
2. 항목 정리

        IS_ENABLED_WATCHDOG=0
        IS_DEV=0

        DOMAIN=''

        DB_HOST=''
        DB_PORT=''
        DB_ID=''
        DB_PASSWORD=''
        DB_SID=''

        FEE_DB_TYPE=''
        FEE_DB_HOST=''
        FEE_DB_PORT=''
        FEE_DB_ID=''
        FEE_DB_PASSWORD=''
        FEE_DB_SID=''

        SMS_ID=''
        SMS_KEY=''
        SMS_SENDER_NUMBER=''

        AIR_KOREA_SERVICE_KEY=''
        API_HOLIDAY_SERVICE_KEY=''
        OPEN_WEATHER_APP_ID=''

        CONTROL_API_URL=''
        CONTROL_SAMSUNG_API_URI=''
        MQTT_FINEDUST_API_URL=''
        TOC_URL=''
        YJ_RND_API_URL=''

        SITE_TYPE=''
        LOG_PATH=''
        CACHE_FILE_PATH=''

        SMTP_SECURE=''
        MAIL_HOST=''
        MAIL_PORT=''
        MAIL_USERNAME=''
        MAIL_PASSWORD=''
        MAIL_FROM_NAME=''

        SECRET_KEY=''
        IV_KEY=''
        TOC_KEY=''

        DEFAULT_PASSWORD=''

        KEVIN_EMAIL=''
