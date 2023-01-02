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

[ API]
1. 날씨 (openWeatherMap)
2. 온,습도 (openWeatherMap)
3. 미세먼지(공공데이터)
4. 공휴일 (공공데이터)
5. SMS (가비아/유료)
6. SMTP (구글)

[데이터 수신]

[설정파일]
1. .env 을 추가 한다.
2. 설정 정보는 구글 드라이브를 참고한다.
3. 항목 설명

        IS_ENABLED_WATCHDOG=watchdog 수동 실행 (1 = 활성화 | 0 = 비활성화)
        IS_DEV=개발모드 (1 = 개발 | 0 = 운영)

        DOMAIN=도메인주소 
        SITE_TYPE=사이트 타입(lbems, fems, bems)

        # lbems_db 설정
        DB_HOST=데이터베이스 주소
        DB_PORT=데이터베이스 포트
        DB_ID=데이버베이스 계정명
        DB_PASSWORD=데이터베이스 패스워드
        DB_SID=데이터베이스 이름

        # 요금 데이터베이스 설정
        FEE_DB_TYPE=데이터베이스 타입
        FEE_DB_HOST=데이터베이스 주소
        FEE_DB_PORT=데이터베이스 포트
        FEE_DB_ID=데이터베이스 계정명
        FEE_DB_PASSWORD=데이터베이스 패스워드
        FEE_DB_SID=데이터베이스 이름

        # SMS 설정
        SMS_ID=아이디
        SMS_KEY=키
        SMS_SENDER_NUMBER=발신자 번호

        # Open API 키 설정
        AIR_KOREA_SERVICE_KEY=공공데이터 미세먼지 
        API_HOLIDAY_SERVICE_KEY=공공데이터 공휴일 
        OPEN_WEATHER_APP_ID=OpenWeaterMap 

        # 데이터 연동 URL
        CONTROL_API_URL=LG 에어컨 API
        CONTROL_SAMSUNG_API_URI=삼성 에어컨 API
        MQTT_FINEDUST_API_URL=MQTT 미세먼지 API
        TOC_URL=TOC APO
        YJ_RND_API_URL=데이터 연계 POST 방식 API
        
        # 경로 
        LOG_PATH=로그 저장 경로
        CACHE_FILE_PATH=캐시 저장 경로

        # SMTP 설정 
        SMTP_SECURE=SECURE타입
        MAIL_HOST=호스트 정보
        MAIL_PORT=포트
        MAIL_USERNAME=계정명
        MAIL_PASSWORD=비밀번호
        MAIL_FROM_NAME=발신자 이름
        KEVIN_EMAIL=발신자 이메일

        # 키 값
        SECRET_KEY=사이트 비밀키 
        IV_KEY=IV 키
        TOC_KEY=TOC API 키

        # 기타
        DEFAULT_PASSWORD=비밀번호 기본값
