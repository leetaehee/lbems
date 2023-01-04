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

[버전관리]
1. 작업은 main(master) 와 branch로 나누어서 한다.
2. 추후 필요에 의해 branch를 여러개 이상 나눌 수 있다.

         main (master)
               develop (branch)

[라이브러리]
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

[오픈 API]
1. 날씨 (openWeatherMap)
2. 온,습도 (openWeatherMap)
3. 미세먼지(공공데이터)
4. 공휴일 (공공데이터)
5. SMS (가비아/유료)
6. SMTP (구글)

[데이터 수신]
1. crontab을 통해서 5분마다 데이터 받는 방식
   * 엔텍 (/script/bems_raw_data_ntek.sh)
   * 레티그리드 (/script/bems_raw_data_mdmt.sh)
   * 펄스카운트 (/script/bems_raw_data_cnc.sh)
   * MQTT (/script/bems_raw_data_finedust.sh)
2. 업체에서 5분마다 데이터 쌓는 방식 
3. "계산식" 을 이용하는 방식
   * 데이터를 직접 받지 않고, 기존에 받은 데이터를 계산을 통해 얻는 방식 
   * 사례 - 전기, 전열 

[데이터 연계]
1. 데이터 연계는 외부 업체에서 우리 데이터를 제공하는 것을 의미한다.
2. 연계 방식은 API와 POST 방식이 있다.
3. API 방식을 사용 시 JWT(JSON Web Token) 방식을 이용한다.
4. JSON 토큰은 주기는 아래와 같다.
   * ACCESS TOKEN : 7일
   * REFRESH TOKEN : 14일 
5. POST 데이터 연계 방식은 5분마다 전달한다. 
6. 데이터 연계 시 제공되는 항목은 아래와 같다. 
   * 에너지원 : 전기, 가스, 태양, 환경정보(미세먼지, co2,  온습도)
   * 제어 : EHP 상태 조회 및 제어 
   * 엣지서버 데이터 동기화

[통계 데이터 관리]
1. 통계 데이터는 시간, 일, 월에 대해 생성한다.
2. 생성시간
   * 시간 : 매 시 10분마다 "이전시간" 생성
   * 일 : 다음 날 새벽 1시에 생성
   * 월 : 한달 기준으로 생성하며 "마감일" 에 따라 진행 
3. 생성항목 
   * 에너지 데이터 (전기,가스,수도 등)
   * 환경정보 (미세먼지, 초미세먼지, 온습도 등)
   * 역률 데이터 (냉난방, 급탕)
4. 생성방법 
   * 에너지 데이터 : 측정 시작일과 측정 종료일을 가지고 "MAX - MIN" 계산
   * 환경 정보 : 측정 시작일과 측정 종료일을 가지고 "평균값" 계산 

[EHP 제어]
1. 단지 내에 EHP 상태 조회 및 제어 
2. EHP에 대한 정보는 API를 통해 접근 할 수 있다. 
3. 현재까지 EHP 개발 진척 상황
   * LG
   * Samsung

[메뉴]

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

        # 오픈 API 키 설정
        AIR_KOREA_SERVICE_KEY=공공데이터 미세먼지 
        API_HOLIDAY_SERVICE_KEY=공공데이터 공휴일 
        OPEN_WEATHER_APP_ID=OpenWeaterMap 

        # 데이터 연동 URL
        CONTROL_API_URL=LG 에어컨 API
        CONTROL_SAMSUNG_API_URI=삼성 에어컨 API
        MQTT_FINEDUST_API_URL=MQTT 미세먼지 API
        TOC_URL=TOC API
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
