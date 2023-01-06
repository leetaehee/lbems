<?php
namespace EMS_Module;

/**
 * Class Config
 */
class Config
{
    /**
     * 한 테이블에 여러개의 에너지원으로 쓰이는 항목
     *
     * electric_hotwater : 전기급탕, 전기온수기
     * electric_heating : 전기난방, 바닥난방
     * electric_cold : 냉방, EHP, GHP
     * electric_water : 급수, 배수
     */

    /**
     * 설비 키
     *
     * communication_power 통신전원
     * electric_water_heater 전기온수기
     * electric_ehp EHP
     * electric_floor_heating 바닥난방
     * power_train 동력
     * feed_pump 급수펌프
     * sump_pump 배수펌프
     * circulating_pump 순환펌프
     * electric_cold_gas 냉난방가스
     * electric_ghp : GHP
     * electric_car : 전기차
     * electric_egs : 전기 + GHP + 태양광 발전량 합
     */

    /**
     * 역률 테이블
     *
     * 전기급탕(전기온수기), 전기냉방(EHP) 우선 적용
     * pf 컬럼 참조
     */

    /**
     * 설비 테이블
     *
     * bems_sensor_electric_equipment
     *
     * type 컬럼으로 구분할 것
     * freeze = 냉장냉동설비
     * geothermal = 지열(계측기 설치 시에만 적용)
     *
     */

    /**
     * 요금 정보
     *
     * 용도 - 일반용:N, 산업용:S
     * 타입1 - 갑1 : type1, 갑2 :type2, 을 : type3
     * 타입2 - 저압 : low, 고압 : high, 고압A : high1, 고압B : high2, 고압C : high3
     * 선택 - 선택1 : select1, 선택2 : select2, 선택3 : select3
     * 계약전력 - 사용자 입력
     */

    /** @var string[] COLUMN_NAMES 에너지원별 단위 */
    const COLUMN_NAMES = [
        'total_wh',
        'val',
        'val',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'total_wh',
        'flow_oil',
        'total_wh',
    ];

    /** @var string[] SENSOR_COLUMN_NAMES 선서테이블에서 에너지원별 컬럼명 */
    const SENSOR_COLUMN_NAMES = [
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'val',
        'flow_oil',
        'val',
    ];

    /** @var string[] SENSOR_TABLES 센서테이블 */
    const SENSOR_TABLES = [
        'bems_sensor_electric',
        'bems_sensor_gas',
        'bems_sensor_water',
        'bems_sensor_electric_light',
        'bems_sensor_electric_cold',
        'bems_sensor_electric_elechot',
        'bems_sensor_electric_elevator',
        'bems_sensor_electric_hotwater',
        'bems_sensor_electric_heating',
        'bems_sensor_electric_boiler',
        'bems_sensor_electric_vent',
        'bems_sensor_solar',
        'bems_sensor_electric_water',
        'bems_sensor_heating',
        'bems_sensor_electric_equipment',
    ];

    /** @var string[] SENSOR_TYPES 센서타입 */
    const SENSOR_TYPES = [
        'electric',
        'gas',
        'water',
        'electric_light',
        'electric_cold',
        'electric_elechot',
        'electric_elevator',
        'electric_hotwater',
        'electric_heating',
        'electric_boiler',
        'electric_vent',
        'solar',
        'electric_water',
        'oil_dyu',
        'electric_equipment',
    ];

    /** @var string[] SENSOR_TYPE_NAMES 센서 타입에 대한 이름  */
    const SENSOR_TYPE_NAMES = [
        'electric' => '전기',
        'gas' => '가스',
        'water' => '수도',
        'electric_light' => '조명',
        'electric_cold' => '냉방',
        'electric_elechot' => '전열',
        'electric_elevator' => '운송',
        'electric_hotwater' => '전기급탕',
        'electric_heating' => '전기난방',
        'electric_boiler' => '전기보일러',
        'electric_vent' => '환기',
        'solar' => '태양광',
        'electric_water' => '급수배수',
        'oil_dyu' => '등유',
        'electric_equipment' => '설비', /* 개별 항목에 대해 조회 필요 */
    ];

    /** @var int[]  SENSOR_TYPE_NO 센서 인덱스 정보 */
    const SENSOR_TYPE_NO = [
        'electric' => 0,
        'gas' => 1,
        'water' => 2,
        'electric_light' => 3,
        'electric_cold' => 4,
        'electric_elechot' => 5,
        'electric_elevator' => 6,
        'electric_hotwater' => 7,
        'electric_heating' => 8,
        'electric_boiler' => 9,
        'electric_vent' => 10,
        'solar' => 11,
        'electric_water' => 12,
        'oil_dyu' => 13,
        'electric_equipment' => 14,
    ];

    /** @var string[] MONTH_TABLES 월 통계 테이블 */
    const MONTH_TABLES = [
        'bems_stat_month_electric',
        'bems_stat_month_gas',
        'bems_stat_month_water',
        'bems_stat_month_electric_light',
        'bems_stat_month_electric_cold',
        'bems_stat_month_electric_elechot',
        'bems_stat_month_electric_elevator',
        'bems_stat_month_electric_hotwater',
        'bems_stat_month_electric_heating',
        'bems_stat_month_electric_boiler',
        'bems_stat_month_electric_vent',
        'bems_stat_month_solar',
        'bems_stat_month_electric_water',
        'bems_stat_month_oil_dyu',
        'bems_stat_month_electric_equipment',
    ];

    /** @var string[] RAW_TABLES 미터 테이블 */
    const RAW_TABLES = [
        'bems_meter_electric',
        'bems_meter_gas',
        'bems_meter_water',
        'bems_meter_electric_light',
        'bems_meter_electric_cold',
        'bems_meter_electric_elechot',
        'bems_meter_electric_elevator',
        'bems_meter_electric_hotwater',
        'bems_meter_electric_heating',
        'bems_meter_electric_boiler',
        'bems_meter_electric_vent',
        'bems_meter_solar',
        'bems_meter_electric_water',
        'bems_meter_heating',
        'bems_meter_electric_equipment',
    ];

    /** @var string[] DAILY_TABLES 일통계 테이블 */
    const DAILY_TABLES = [
        'bems_stat_daily_electric',
        'bems_stat_daily_gas',
        'bems_stat_daily_water',
        'bems_stat_daily_electric_light',
        'bems_stat_daily_electric_cold',
        'bems_stat_daily_electric_elechot',
        'bems_stat_daily_electric_elevator',
        'bems_stat_daily_electric_hotwater',
        'bems_stat_daily_electric_heating',
        'bems_stat_daily_electric_boiler',
        'bems_stat_daily_electric_vent',
        'bems_stat_daily_solar',
        'bems_stat_daily_electric_water',
        'bems_stat_daily_oil_dyu',
        'bems_stat_daily_electric_equipment',
    ];

    /** @var string[] LIMIT_COLUMN_NAMES 기준값 컬럼 */
    const LIMIT_COLUMN_NAMES = [
        'limit_val_electric', // 전기
        'limit_val_gas', // 가스
        'limit_val_water', // 수도
        'limit_val_electric_light', // 전등
        'limit_val_electric_cold', // 냉방
        'limit_val_electric_elechot', // 전열
        'limit_val_electric_elevator', // 승강
        'limit_val_electric_hotwater', // 전기급탕
        'limit_val_electric_heating', // 전기난방
        'limit_val_electric_boiler', // 보일러
        'limit_val_electric_vent', // 환기
        'limit_val_solar', // 태양광
        'limit_val_electric_water', // 급수배수펌프
        'limit_val_heating', // 등유
        'limit_val_electric_equipment', // 설비
    ];

    /** @var string[] CLOSING_DAY_COLUMN_NAMES 마감일 컬럼 */
    const CLOSING_DAY_COLUMN_NAMES = [
        'closing_day_electric',
        'closing_day_gas',
        'closing_day_water',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
        'closing_day_electric',
    ];

    /** @var int[] DIVISOR_VALUES 에너지원별 데이터 계산 값 */
    const DIVISOR_VALUES = [
        1000,
        1,
        1,
        1000,
        1000,
        1000,
        1000,
        1000,
        1000,
        1000,
        1000,
        1000,
        1000,
        1,
        1000,
    ];

    /** @var string[] EFFICIENCY_TABLES 효율 적용 가능 테이블 */
    const EFFICIENCY_TABLES = [
        '', // 전기
        '', // 가스
        '', // 수도
        '', // 전등
        'electric_cold', // 냉방
        '', // 전열
        '', // 승강
        'electric_hotwater', // 전기급탕
        '', // 전기난방
        '', // 보일러
        '', // 환기
        '', // 태양광
        '', // 급수배수펌프
        '', // 등유
        '', // 설비
    ];

    /** @var string[] ANOMALY_TABLES 이상증후 적용 가능 테이블 */
    const ANOMALY_TABLES = [
        'electric', // 전기
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        ''
    ];

    /** @var string[] NTEC_TABLES 엔텍 데이터 테이블 정보 */
    const NTEK_TABLES = [
        'sensor_table' => 'ntek_sensor_electric',
        'meter_table' => 'ntek_meter_electric',
    ];

    /** @var string[] CNC_TABLESCNC 테이블 정보 */
    const CNC_TABLES = [
        'sensor_table' => 'cnc_sensor_pulse',
        'meter_table' => 'cnc_meter_pulse',
    ];

    /** @var array FINEDUST_TABLE_INFO 미세먼지 정보 */
    const FINEDUST_TABLE_INFO = [
        'sensor_types' => 'finedust',
        'sensor_table' => 'bems_sensor_finedust',
        'raw_table' => 'bems_meter_finedust',
        'daily_table' => [
            'pm10' => 'bems_stat_daily_finedust',
            'pm25' => 'bems_stat_daily_finedust_ultra',
            'pm1_0' => 'bems_stat_daily_finedust_ultra1',
        ],
        'month_table' => [
            'pm10' => 'bems_stat_month_finedust',
            'pm25' => 'bems_stat_month_finedust_ultra',
            'pm1_0' => 'bems_stat_month_finedust_ultra1',
        ],
        'limit_column' => 'limit_val_finedust',
    ];

    /** @var array CO2_TABLE_INFO */
    const CO2_TABLE_INFO = [
        'data' => [
            'bems_stat_month_co2',
            'bems_stat_daily_co2',
            'bems_meter_finedust'
        ],
        'sensor' => [
            'table' => 'bems_sensor_finedust',
            'column' => 'device_eui',
        ],
        'column' => ['ym', 'val_date', 'co2'],
        'limit_column' => 'limit_val_co2',
    ];

    /** @var string[] PERIODS 주기 */
    const PERIODS = ['year', 'month', 'daily', 'hour', 'today' , 'weekly'];

    /** @var string[] PREDICT_COLUMN_NAMES 예측컬럼 */
    const PREDICT_COLUMN_NAMES = ['', 'predict_month', 'predict_day', '', '', 'predict_week'];

    /** @var array DEFAULT_MENUS 메뉴 기본값 */
    const DEFAULT_MENUS = [
        '대시보드' => [
            '전체' => 'dashboard/dashboard.html',
            '세부' => 'dashboard/floor.html'
        ],
        '계통도' => 'diagram/diagram_mdmt.html',
        '정보감시' => [
            '에너지원별 부하감시' => 'info/energy.html',
            '용도별 부하감시' => 'info/usage.html',
            '설비별 부하감시' => 'info/facilities.html',
            '실내 미세먼지' => 'info/finedust.html'
        ],
        '조회' => [
            '에너지원별 사용현황' => 'report/energy.html',
            '용도별 사용현황' => 'report/usage.html',
            '설비별 사용현황' => 'report/facilities.html',
            '층별 사용현황' => 'report/floor.html',
            '태양광 발전현황' => 'report/solar.html'
        ],
        '분석' => [
            '종합분석' => 'analysis/total.html',
            '단위면적당 사용분석(에너지원별)' => 'analysis/energy.html',
            '단위면적당 사용분석(용도별)' => 'analysis/usage.html',
            '단위면적당 사용분석(설비별)' => 'analysis/facilities.html',
            '일별 비교분석' => 'analysis/period_day.html',
            '월별 비교분석' => 'analysis/period_month.html',
            '년별 비교분석' => 'analysis/period_year.html',
            '층별 비교분석' => 'analysis/floor.html',
            '제로에너지 등급' => 'analysis/zeroenergy.html',
        ],
        '태양광' => 'solar/solar.html',
        '설비' => 'facility/facilities.html',
        '예측' => [
            '에너지원별 사용예측' => 'prediction/energy_prediction.html',
            '태양광 발전예측' => 'prediction/solar_prediction.html'
        ],
        /*
            '제어' => [
                '1층' => 'control/control_1f_mdmt.html',
                '2층' => 'control/control_2f_mdmt.html',
                '3층' => 'control/control_3f_mdmt.html',
            ],`
        */
        /*
            '보고서' => 'paper/paper_mdmt.html',
        */
        '설정' => [
            '기본 정보 관리' => 'set/info.html',
            '목표량/기준값 설정' => 'set/standard.html',
            '에너지 단가관리' => 'set/unit_price.html'
        ],
        '관리자설정' => [
            '건물 관리' => 'manager/building.html',
            '권한 관리' => 'manager/authority.html',
            /*
                '동/세대 관리' => 'manager/dong.html',
                '에너지 단가관리' => 'manager/unit_price.html',
                '장비관리 -에너지원별' => 'manager/energy.html',
                '장비관리 -용도별' => 'manager/usage.html',
                '장비관리 -설비별' => 'manager/facilities.html',
            */
            '로그인 로그' => 'manager/login.html'
        ],
    ];

    /** @var string[] DEFAULT_MENU_ICONS 디폴트 메뉴 이미지 */
    const DEFAULT_MENU_ICONS = [
        '대시보드' => 'ico_gnb1.png',
        '계통도' => 'ico_gnb2.png',
        '정보감시' => 'ico_gnb3.png',
        '조회' => 'ico_gnb4.png',
        '분석' => 'ico_gnb5.png',
        '태양광' => 'ico_gnb9.png',
        '설비' => 'ico_gnb6.png',
        '예측' => 'ico_gnb7.png',
        '제어' => 'ico_gnb8.png',
        '보고서' => 'ico_gnb4.png', // 아이콘 디자인 필요
        '설정' => 'ico_gnb10.png',
        '관리자설정' => 'icon_gnb9.png',
    ];

    /** @var int[] DEFAULT_MENU_AUTHORITY 디폴트 메뉴 권한 */
    const DEFAULT_MENU_AUTHORITY = [
        '설정' => 70,
        '관리자설정' => 100,
    ];

    /** @var array DEFAULT_MENU_GROUPS 모바일 메뉴 그룹 */
    const DEFAULT_MENU_GROUPS = [
        'home' => [
            'key' => 'home',
            'name' => '홈'
        ],
        'diagram' => [
            'key' => 'diagram',
            'name' => '계통도'
        ],
        'prediction' => [
            'key' => 'prediction',
            'name' => '예측',
            'sub_menu' => [
                'prediction_energy' => [
                    'key' => 'prediction_energy',
                    'name' => '에너지원별'
                ],
                'prediction_solar' => [
                    'key' => 'prediction_solar',
                    'name' => '태양광 발전'
                ]
            ],
        ],
        'control' => [
            'key' => 'control',
            'name' => '제어'
        ],
        /*
            'alarm' => [
                'key' => 'alarm',
                'name' => '알람'
            ],
        */
    ];

    /** @var string[] DEFAULT_MOBILE_MENUS 모바일에서 보여지는 디폴트 메뉴 */
    const DEFAULT_MOBILE_MENUS = [
        'home' => './home/home.html',
        'diagram' => './diagram/diagram.html',
        'prediction_energy' => './prediction/energy.html',
        'prediction_solar' => './prediction/solar.html',
        'control' => './control/control.html',
        /*
            'alarm' => './alarm/alarm.html', // 알람
         */
    ];

    /** @var string[][] MENU_USER_AUTHORITY 유저별 메뉴 권한 관리 (추후 DB화) */
    const MENU_USER_AUTHORITY = [
        'twin_bems' => [
            '정보 감시' => [
                '에너지원별 부하 감시',
                '용도별 부하 감시',
                '설비별 부하 감시',
            ],
            '조회' => [
                '에너지원별 사용 현황',
                '용도별 사용 현황',
                '설비별 사용 현황'
            ],
        ],
        'public_bems' => [
            '분석' => [
                '단위면적당 사용 분석(에너지원별)',
                '단위면적당 사용 분석(용도별)',
                '단위면적당 사용 분석(설비별)',
            ]
        ],
        'light_bems' => [
        ],
    ];

    /** @var string[] COMPLEX_MENU_DASHBOARD_TYPES 단지 기준 대시보드 메뉴 정의  */
    const COMPLEX_MENU_DASHBOARD_TYPES = [
        '3001' => '조회',
        '2011' => '정보 감시',
    ];

    /** @var string[] USER_MENU_DASHBOARD_TYPES 유저 기준 대시보드 메뉴 정의 */
    const USER_MENU_DASHBOARD_TYPES = [
        'twin_bems' => '정보 감시',
        'public_bems' => '분석',
        'light_bems' => '',
    ];

    /** @var string[][] SKIN_DIRECTORY 스킨 상태에 따라 경로 분기 처리 */
    const SKIN_DIRECTORY = [
        'default' => [
            'menu_icon' => '../res/images/icon/',
        ],
        'dark' => [
            'menu_icon' => '../res/images/template_2/',
        ],
    ];

    /** @var int LOGIN_FAIL_LIMIT_COUNT 로그인 실패 횟수 제한  */
    const LOGIN_FAIL_LIMIT_COUNT = 5;

    /** @var string[] SOLAR_TYPES 태양광 in, out 타입 정의  */
    const SOLAR_TYPES = [
        'I' => 'in',
        'O' => 'out',
    ];

    /** @var int MAX_GENERATION_TIME 발전 최대 시간  EMS_Modules/php/Utility.php 에서 사용  */
    const MAX_GENERATION_TIME = 5;

    /** @var string WEB_VERSION 시스템 버전 */
    const WEB_VERSION = '건물에너지관리시스템 L-BEMS 1.0.0';

    /** @var string FEE_METHOD 요금 계산방식  1) Fee:  Fee 클래스 2) Library: library 방식 */
    const FEE_METHOD = 'Fee';

    /** @var string SECRET_KEY_RULE 양방향 암호화/복호화 사용 시 키 생성 규칙 */
    const SECRET_KEY_RULE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var string ENCRYPTION_METHOD 양방항 암호화/복호화 시 알고리즘 */
    const ENCRYPTION_ALGORITHM = 'aes-256-cbc';
    
    /** @var string[] DOWNLOAD_ALLOW_EXTENSIONS 다운로드 시 허용되는 확장자 목록 */
    const DOWNLOAD_ALLOW_EXTENSIONS = ['docx', 'hwp', 'png', 'jpg', 'jpeg', 'pdf'];

    /** @var string[] ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO  전기 사용량에 태양광을 제외하는 대상 */
    const ELECTRIC_BUILDING_EXCEPT_SOLAR_INFO = ['2004', '2007', '2012', '2013'];

    /** @var string[] FINEDUST_SENSOR_USE_GROUP 미세먼지 센서를 사용하는 경우 */
    const FINEDUST_SENSOR_USE_GROUP = ['2001', '2002', '2005', '2014', '2017'];

    /** @var string[] FACTORY_USE_GROUPFACTORY_USE_GROUP 공장 사용 그룹 정보  */
    const FACTORY_USE_GROUP = ['3001', '3003', '3004'];

    /** @var string SYSTEM_TYPE 시스템 정보 */
    const SYSTEM_TYPE = 'lbems';

    /** @var int[] RAW_DATA_RECEIVE_PERIODS 건물별 데이터 수신 주기 - '분' 기준, (에너지원마다 다를 경우 아래 옵션 검토) */
    const RAW_DATA_RECEIVE_PERIODS = [
        '2006' => 10,
        '2008' => 10,
    ];

    /** @var int[] RAW_DATA_RECEIVE_START_MINUTE 건물별 데이터 수신시 시작 분  */
    const RAW_DATA_RECEIVE_START_MINUTE = [
        '2011' => 2,
    ];

    /** @var string[] SPECIAL_ENERGY_DATA 에너지원에서 별도로 보여주어야 하는 항목 */
    const SPECIAL_ENERGY_DATA = [
        'electric',
        'power_train',
        'electric_cold_gas',
        'communication_power',
        'electric_car',
        'geothermal'
    ];

    /** @var string[] EQUIPMENT_TYPE_ITEMS bems_sensor_ 에서 타입항목 */
    const EQUIPMENT_TYPE_ITEMS = [
        'freeze',
        'geothermal',
    ];

    /** @var int[] TOC_ENERGY_TYPE_DATA TOC 적용되는 에너지원 항목 */
    const TOC_ENERGY_TYPE_DATA = [0, 11];

    /** @var array TOC_ENERGY_TYPE_ERROR_ITEMS TOC 로 보내는 장애 항목 */
    const TOC_ENERGY_TYPE_ERROR_ITEMS = [0, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14];

    /** @var string[] TOC_CALCULATE_INFO TOC 센서를  모두 더해야 하는 정보  */
    const TOC_CALCULATE_INFO = [
        'lbems' => [
            '2006',
            '2008',
            '2009',
            '2011'
        ],
        'fems' => [
            '3001',
            '3003',
            '3004'
        ],
    ];

    /** @var string[]  UNIT_OVERRIDE_INFO 단위 재정의 하기 */
    const UNIT_OVERRIDE_INFO = [];

    /** @var array CONTROL_AIR_CONDITION_COMMAND 에어컨 제어 명령어  정보 */
    const CONTROL_AIR_CONDITION_COMMAND = [
        'lg' => [
            'operation' => [
                'fc5' => [
                    'power' => 0, // 전원
                ],
                'fc6' => [
                    'mode' => 0, // 모드
                    'fan_speed' => 1, // 풍량
                    'set_temperature' => 2, //  희망온도 설정
                    'upper_temperature' => 3, // 온도 상승
                    'lower_temperature' => 4, // 온도 하락
                ]
            ],
            'fan' => [
                1 => 'Low', // 낮음
                2 => 'Med', // 중간
                3 => 'High', // 높음
                4 => 'Auto' // 자동
            ],
            'mode' => [
                1 => 'Cool', // 냉방
                2 => 'Dry', // 제습
                3 => 'Fan', // 풍량
                4 => 'Auto', // 자동
                5 => 'Heat', // 난방
            ],
        ],
        'samsung' => [
            'operation' => [
                'power' => 'power', // 전원
                'mode' => 'operationMode', // 모드
                'fan_speed' => 'fanSpeed', // 풍량
                'upper_temperature' => 'setTemp', // 온도 상승
                'lower_temperature' => 'setTemp', // 온도 하락
            ],
            'fan' => [
                1 => 'low', // 낮음
                2 => 'mid', // 중간
                3 => 'high', // 높음
                4 => 'auto' // 자동
            ],
            'mode_v' => [
                'cool' => 1, // 냉방
                'dry' => 2, // 제습
                'fan' => 3, // 풍량
                'auto' => 4, // 자동
                'heat' => 5, // 난방
            ],
            'mode_k' => [
                1 => 'cool', // 냉방
                2 => 'dry', // 제습
                3 => 'fan', // 풍량
                4 => 'auto', // 자동
                5 => 'heat', // 난방
            ],
        ],
    ];

    /** @var string[]  MONTH_SEASON_TYPES 월별 계절별 분류  */
    const MONTH_SEASON_TYPES = [
        1 => 'winter',
        2 => 'winter',
        3 => 'spring',
        4 => 'spring',
        5 => 'spring',
        6 => 'summer',
        7 => 'summer',
        8 => 'summer',
        9 => 'spring',
        10 => 'spring',
        11 => 'winter',
        12 => 'winter',
    ];


    /** @var \string[][] SEASON_STATUS_TIME_INFO 계절별 부하 시간 구분  */
    const SEASON_STATUS_TIME_INFO = [
        'winter' => [
            0 => 'low', 1 => 'low', 2 => 'low', 3 => 'low', 4 => 'low', 5 => 'low', 6 => 'low', 7 => 'low', 8 => 'low',
            9 => 'mid', 10 => 'max', 11 => 'max', 12 => 'mid', 13 => 'mid', 14 => 'mid', 15 => 'mid', 16 => 'mid',
            17 => 'max', 18 => 'max', 19 => 'max', 20 => 'mid', 21 => 'mid', 22 => 'max', 23 => 'low'
        ],
        'summer' => [
            0 => 'low', 1 => 'low', 2 => 'low', 3 => 'low', 4 => 'low', 5 => 'low', 6 => 'low', 7 => 'low', 8 => 'low',
            9 => 'mid', 10 => 'max', 11 => 'max', 12 => 'mid', 13 => 'max', 14 => 'max', 15 => 'max', 16 => 'max',
            17 => 'mid', 18 => 'mid', 19 => 'mid', 20 => 'mid', 21 => 'mid', 22 => 'mid', 23 => 'low',
        ],
        'spring' => [
            0 => 'low', 1 => 'low', 2 => 'low', 3 => 'low', 4 => 'low', 5 => 'low', 6 => 'low', 7 => 'low', 8 => 'low',
            9 => 'mid', 10 => 'max', 11 => 'max', 12 => 'mid', 13 => 'max', 14 => 'max', 15 => 'max', 16 => 'max',
            17 => 'mid', 18 => 'mid', 19 => 'mid', 20 => 'mid', 21 => 'mid', 22 => 'mid', 23 => 'low',
        ],
    ];

    /** @var \array[][] SEASON_STATUS_TIME_RANGES 계절별 부하 시간 간격  */
    const SEASON_STATUS_TIME_RANGES = [
        'winter' => [
            'low' => [
                ['start' => '00', 'end' => '08'],
                ['start' => '23', 'end' => '23'],
            ],
            'mid' => [
                ['start' => '09', 'end' => '09'],
                ['start' => '12', 'end' => '16'],
                ['start' => '20', 'end' => '21'],
            ],
            'max' => [
                ['start' => '10', 'end' => '11'],
                ['start' => '17', 'end' => '19'],
                ['start' => '22', 'end' => '22'],
            ],
        ],
        'summer' => [
            'low' => [
                ['start' => '00', 'end' => '08'],
                ['start' => '23', 'end' => '23'],
            ],
            'mid' => [
                ['start' => '09', 'end' => '09'],
                ['start' => '12', 'end' => '12'],
                ['start' => '17', 'end' => '22'],
            ],
            'max' => [
                ['start' => '10', 'end' => '11'],
                ['start' => '13', 'end' => '16'],
            ],
        ],
        'spring' => [
            'low' => [
                ['start' => '00', 'end' => '08'],
                ['start' => '23', 'end' => '23'],
            ],
            'mid' => [
                ['start' => '09', 'end' => '09'],
                ['start' => '12', 'end' => '12'],
                ['start' => '17', 'end' => '22'],
            ],
            'max' => [
                ['start' => '10', 'end' => '11'],
                ['start' => '13', 'end' => '16'],
            ],
        ],
    ];

    /** @var string[] STATUS_COLUMNS 경부하,중부하,최대부하 컬럼명 */
    const STATUS_COLUMNS = [
        'low_status',
        'mid_status',
        'max_status',
    ];

    /** @var int[] STATUS_ENERGY_TYPES 경부하,중부하,최대부하가 적용되는 에너지원 (전기에만 적용)  */
    const STATUS_ENERGY_TYPES = [
        0, 3, 4, 5, 6, 7, 8, 10, 12, 14
    ];

    /** @var string[] 요금 라이브러리 타입2에 대한 키 값  */
    const FEE_LIBRARY_VOLT_TYPE_KEYS = [
        'low' => 'L',
        'high' => 'H',
        'high1' => 'H1',
        'high2' => 'H2',
        'high3' => 'H3',
    ];

    /** @var string[] FLOOR_INFO 층 정보 */
    const FLOOR_INFO = [
        'B1' => '지하1층',
        '1F' => '1층',
        '2F' => '2층',
        '3F' => '3층',
        '4F' => '4층',
        '5F' => '5층',
        '6F' => '6층',
        '7F' => '7층',
        '8F' => '8층',
        '9F' => '9층',
        '10F' => '10층',
        'PH' => '옥상',
    ];

    /** @var int TOKEN_DATE_TYPE 토큰 만료 주기 (단위: 일)*/
    const TOKEN_DATE_TYPE = 14;

    /** @var int ACCESS_TOKEN_DATE_TYPE access_token 만료 주기  (단위: 일) */
    const ACCESS_TOKEN_DATE_TYPE = 7;

    /** @var int REFRESH_TOKEN_DATE_TYPE refresh_token 만료 주기 (단위: 일) */
    const REFRESH_TOKEN_DATE_TYPE = 14;

    /** @var string JWT_MAKE_ALGORITHM  jwt 생성시 사용하는 알고리즘 */
    const JWT_MAKE_ALGORITHM = 'sha256';

    /** @var \string[][] COOKIE_SETTINGS  쿠키 설정값 모음 */
    const COOKIE_SETTINGS = [
        'KBET' => [
            'path' => '/kbet',
            'login_cookie_name' => 'API_COOKIE'
        ],
    ];

    /** @var string[] ALL_DATA_JSON_VALUES 센서테이블에 all_data 값 */
    const ALL_DATA_JSON_VALUES = [
        'type',
        'frequency',
        'v_l1',
        'v_l2',
        'v_l3',
        'v_ub',
        'vpp_l1',
        'vpp_l2',
        'vpp_l3',
        'vpp_ub',
        'i_l1',
        'i_l2',
        'i_l3',
        'watt',
        'var',
        'va',
        'pf',
        'kwh_imp',
        'kwh_exp',
        'kvarh_imp',
        'kvarh_exp'
    ];

    /** @var int[] CNC_REFERENCE_TABLES 참조 테이블  */
    const CNC_REFERENCE_TABLES = [
        1,
        4,
        13
    ];

    /** @var string[] CNC_TWO_CHANNEL_COMPLEX_INFO 펄스카운트 채널 2개 사용하는 단지 정보 */
    const CNC_TWO_CHANNEL_COMPLEX_INFO = [
        '2019'
    ];

    /** @var string DEFAULT_PASSWORD 패스워드 기본값 */
    const DEFAULT_PASSWORD = 'password1234';

    /** @var int NORMAL_PASSWORD_LENGTH 슈퍼관리자가 아닌 계정에 대한  비밀번호 자릿수  */
    const NORMAL_PASSWORD_LENGTH = 14;

    /** @var array LOGIN_LEVEL_DATA 로그인 레벨 정보  */
    const LOGIN_LEVEL_DATA = [
        'key' => [
            '게스트' => 70,
            '단지관리자'=> 80,
            '업체관리자'=> 90,
            '최고관리자'=> 100
        ],
        'value' => [
            '70' => '게스트',
            '80'=> '단지관리자',
            '90'=> '업체관리자',
            '100'=> '최고관리자'
        ]
    ];

    /** @var float GAS_TO_ELECTRIC_TRANS_VALUE 가스를 전기로 환산하는 값 */
    const GAS_TO_ELECTRIC_TRANS_VALUE = 10.55;

    /** @var string[] SEPARATED_ENERGY_TYPES 에너지원으로 등록된 센서 중 독립적으로 조회 하는 경우 */
    const SEPARATED_ENERGY_TYPES = [
        'communication_power ',
        'electric_water_heater',
        'electric_ehp',
        'electric_floor_heating',
        'power_train',
        'circulating_pump',
        'electric_cold_gas',
        'electric_ghp',
        'electric_car'
    ];

    /** @var string[] COLLECT_TYPES 수집데이터 종류 */
    const COLLECT_TYPES = [
        'edge' => 'edge'
    ];

    /** @var string[] COMPLEX_AIR_CONDITIONER_INFO 단지별 에어컨 제조사 정보 */
    const COMPLEX_AIR_CONDITIONER_INFO = [
        '2001' => 'lg',
        '2002' => 'lg',
        '2005' => 'samsung',
        '2014' => 'lg',
        '2017' => 'lg',
    ];

    /** @var string[] NTEK_SOLAR_OUT_COMPLEX_INFO  엔텍 데이터 중에서 태양광 소비량을 전기에 포함시키기 위한 설정 */
    const NTEK_SOLAR_OUT_COMPLEX_INFO = [
        '2019'
    ];

    /** @var string[] AIR_CONDITIONER_STATUS_TYPE API 전달 시 에어컨 status 타입 목록  */
    const AIR_CONDITIONER_STATUS_TYPE = ['power_etc', 'operation_etc'];

    /** @var \string[][] API_INTEGRATION_SEND_OPTIONS 데이터 연계 송신 옵션 */
    const API_INTEGRATION_SEND_OPTIONS = [
        'YTEH' => [
            'API_URL' => 'YJ_RND_API_URL',
            'COMPLEX_ADDR' => [
                'URL' => '/bemsComplexAddr',
                'METHOD' => 'POST',
            ],
            'ELECTRIC_CONSUME' => [
                'URL' => '/bemsElectricConsume',
                'METHOD' => 'POST',
            ],
            'GAS_CONSUME' => [
                'URL' => '/bemsGasConsume',
                'METHOD' => 'POST',
            ],
            'SOLAR_CONSUME' => [
                'URL' => '/bemsSolarProduct',
                'METHOD' => 'POST',
            ],
            'ENVIRONMENT_SENSOR' => [
                'URL' => '/bemsEnvSensor',
                'METHOD' => 'POST'
            ],
        ],
    ];

    /** @var string COMMUNICATION_METHOD 통신 방식 - API, DATABASE, SAMPLE */
    const CONTROL_COMMUNICATION_METHOD = 'SAMPLE';
}