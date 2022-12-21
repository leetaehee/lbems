const DEFAULT_BUILDING_NAME = '무등산국립공원';
const LAYOUT_CSS_PATH = '../res/css/layout';

/**
 * default : lbems 처음 개발 될 때 적용된 화이트 배경색 버전
 * dark : lbems 흑백(다크) 버전
 */

/** 공통사항 */
/* 차트 범주에서 미설정에 해당하는 색상 */
const DEFAULT_COLORS = {
    default : '221,221,221',
    dark : '22,22,22',
};

/* 용도별 차트 색상 */
const USAGE_COLORS = {
    default : {
        electric_light : '50,119,100', // 전등(조명)
        electric_elechot : '105,144,178', // 전열
        electric_hotwater : '108,170,184', // 급탕
        electric_cold : '154,221,229', // 냉/난방
        electric_vent : '203,216,97', // 환기
        electric_elevator : '147,201,132', // 운송(승강)
        electric_heating : '255,167,167', // 난방
        power_train : '255,178,245', // 동력
        electric_car : '255,178,245', // 전기차
        freeze : '255,178,245', // 냉장/냉동설비
        geothermal : '255,178,245', // 지열
    },
    dark : {
        electric_light : '230,200,148', // 전등(조명)
        electric_elechot : '239,180,158', // 전열
        electric_hotwater : '236,204,192', // 급탕
        electric_cold : '188,185,169', // 냉/난방
        electric_vent : '177,96,63', // 환기
        electric_elevator : '146,147,105', // 운송(승강)
        electric_heating : '255,167,167', // 난방
        power_train : '255,217,236', // 동력
        electric_car : '255,217,236', // 전기차
        freeze: '255,217,236', // 냉장/냉동설비
        geothermal : '255,217,236', // 지열
    },
};

/* 설비별 차트 색상 */
const FACILITY_COLORS = {
    default : {
        electric_water_heater : '158,151,195', // 전기온수기
        feed_pump : '165,193,238', // 급수펌프
        sump_pump : '181,227,246', // 배수펌프
        circulating_pump : '108,170,184', // 순환펌프
        electric_ehp : '143,177,234', // EHP
        electric_floor_heating : '163,220,244', // 바닥난방
    },
    dark : {
        electric_water_heater : '158,151,195', // 전기온수기
        feed_pump : '165,193,238', // 급수펌프
        sump_pump : '181,227,246', // 배수펌프
        circulating_pump : '108,170,184', // 순환펌프
        electric_ehp : '143,177,234', // EHP
        electric_floor_heating : '163,220,244', // 바닥난방
    },
};

/* 층별 차트 색상 */
const FLOOR_CHART_COLOR = {
    default : {
        'B1' : '213,213,213', // 색상 디자인 검토 필요
        '1F' : '197,181,194',
        '2F' : '99,73,112',
        '3F' : '162,170,206',
        '4F' : '162,170,206', // 색상 디자인 검토 필요
        '5F' : '162,170,206', // 색상 디자인 검토 필요
        '6F' : '162,170,206', // 색상 디자인 검토 필요
        '7F' : '162,170,206', // 색상 디자인 검토 필요
        'PH' : '42,49,130',
    },
    dark : {
        'B1' : '213,213,213', // 색상 디자인 검토 필요
        '1F' : '197,181,194',
        '2F' : '99,73,112',
        '3F' : '162,170,206',
        '4F' : '162,170,206', // 색상 디자인 검토 필요
        '5F' : '162,170,206', // 색상 디자인 검토 필요
        '6F' : '162,170,206', // 색상 디자인 검토 필요
        '7F' : '162,170,206', // 색상 디자인 검토 필요
        'PH' : '42,49,130',
    },
};

/** 대시보드 */
/* 대시보드 용도별/설비별 차트 색상은 공통영역 참조 */

/* 대시보드 > 전체, 세부에서 에너지 차트 색상 */
const DH_ENERGY_GRAPH_COLORS = {
    default : {
        current_line : '208,167,37', // 현재 사용량
        previous_line : '108,117,125', // 이전 사용량
        standard_line : '0,0,0', // 기준값
    },
    dark : {
        current_line : '229,193,149', // 현재 사용량
        previous_line : '108,117,125', // 이전 사용량
        standard_line : '0,0,0', // 기준값
    },
};

/* 대시보드 > 전체 제로 에너지 등급 차트 색상 */
const DH_ZERO_ENERGY_GRAPH_COLORS = {
    default : '66,110,90',
    dark : '229,193,149',
};

/* 대시보드 > 세부 예측 사용량 그래프 */
const DH_DETAIL_PREDICT_GRAPH_COLORS = {
    default : {
        current : '157,173,192', // 현재사용량
        predict : '125,167,217', // 예측사용량
    },
    dark : {
        current : '157,173,192', // 현재사용량
        predict : '108,117,125', // 예측사용량
    },
};

/* 대시보드 > 세부 층별 정보 보여주는 영역 배경색 */
const DH_DETAIL_FLOOR_BACKGROUND_COLOR = {
    default: '255,255,255',
    dark: '37,37,38',
};

/* 대시보드 > 전체에서  용도별 사용현황 차트에서 테두리 width 조정 */
const DH_USAGE_CHART_CUTOUT_PERCENTAGE = {
    default : 83,
    dark : 85,
};

/* 대시보드 > 세부 페이지 건물 이미지 */
const DH_DETAIL_BUILDING_IMAGE = {
    mdmt : 'dashboard_mudeng_pic.jpg', // 무등산
    tbmt : 'dashboard_taebaek_pic.jpg', // 태백산
    nedOffice : 'dashboard_nedoffice.jpg', // 대전네드사옥 (삼원빌딩)
    samcheock : 'dashboard_samcheok.jpg', // 빛사랑어린이집
    dado : 'dashboard_dado.jpg', // 다도해
    kc_hc : 'dashboard_kc_hc.jpg', // 김해 행정복지센터
    default : 'dashboard_default.jpg', // 기본 이미지
};

/** 정보감시 */
/* 에너지,용도별,설비별 그리고 미세먼지, co2 차트 색상 */
/**
 status: 부하현황, 부하비율 색상
 standard_average: 기준값 대비 평균 사용량
 co2_color: 실내환경정보 메뉴
 finedust_color: 실내 미세먼지 메뉴(무등산 메뉴)
 */
const INFO_CHART_COLORS = {
    default : {
        status : {
            max : '158,151,195', // 최대부하
            mid : '165,193,238', // 중부하
            min : '181,227,246', // 경부하
            normal : '167,210,191', // 정상
            standard : '108,117,125', // 기준값
        },
        standard_average : {
            used : '231,139,85', // 0보다 큰 경우..
            zero : '167,210,191', // 디폴트값
        },
        finedust_color : {
            bar : '243,169,120', // 실내미세먼지 - bar
            line : '108,117,125', // 실내미세먼지 - line
        },
        co2_color : {
            bar : '243,169,120', // 실내 환경정보 - bar
            line : '108,117,125', // 실내 환경정보 - line
        },
    },
    dark : {
        status : {
            max : '158,151,195', // 최대부하
            mid : '165,193,238', // 중부하
            min : '181,227,246', // 경부하
            normal : '167,210,191', // 정상
            standard : '108,117,125', // 기준값
        },
        standard_average : {
            used : '231,139,85', // 0보다 큰 경우..
            zero : '22,22,22', // 디폴트값
        },
        finedust_color : {
            bar : '243,169,120', // 실내미세먼지 - bar
            line : '108,117,125', // 실내미세먼지 - line
        },
        co2_color : {
            bar : '243,169,120', // 실내 환경정보 - bar
            line : '108,117,125', // 실내 환경정보 - line
        },
    },
};

/** 조회 */
/* 에너지,용도별,설비별 사용현황 차트 색상 */
/**
 bar: 그래프에서 사용량을 보여주는 막대
 line: 그래프에서 사용요금을 보여주는 막대
 click_color: 그래프 클릭했을 때 색상
 */
const REPORT_BASIC_CHART_COLORS = {
    default : {
        bar : {
            year : '188,243,241', // 년도
            month : '236,175,169', // 월
            day : '255,227,176', // 시
            raw : '186,178,223', // 분
        },
        line : {
            year : '105,183,181', // 년도
            month : '241,123,113', // 월
            day : '253,180,48', // 시
            raw : '135,125,189', // 분
        },
        click_color : {
            year : '105,182,100', // 년도
            month : '245,209,98', // 월
            day : '253,180,48', // 시
            raw : '135,125,189', // 분
        }
    },
    dark : {
        bar : {
            year : '188,243,241', // 년도
            month : '236,175,169', // 월
            day : '255,227,176', // 시
            raw : '186,178,223', // 분
        },
        line : {
            year : '105,183,181', // 년도
            month : '241,123,113', // 월
            day : '253,180,48', // 시
            raw : '135,125,189', // 분
        },
        click_color : {
            year : '105,182,100', // 년도
            month : '245,209,98', // 월
            day : '253,180,48', // 시
            raw : '135,125,189', // 분
        }
    },
};

/* 층별 사용현황 차트 색상 */
/*
const REPORT_FLOOR_CHART_COLOR = {
    default : {
        'B1' : '213,213,213', // 색상 디자인 검토 필요
        '1F' : '197,181,194',
        '2F' : '99,73,112',
        '3F' : '162,170,206',
        '4F' : '162,170,206', // 색상 디자인 검토 필요
        '5F' : '162,170,206', // 색상 디자인 검토 필요
        '6F' : '162,170,206', // 색상 디자인 검토 필요
        '7F' : '162,170,206', // 색상 디자인 검토 필요
        'PH' : '42,49,130',
    },
    dark : {
        'B1' : '213,213,213', // 색상 디자인 검토 필요
        '1F' : '197,181,194',
        '2F' : '99,73,112',
        '3F' : '162,170,206',
        '4F' : '162,170,206', // 색상 디자인 검토 필요
        '5F' : '162,170,206', // 색상 디자인 검토 필요
        '6F' : '162,170,206', // 색상 디자인 검토 필요
        '7F' : '162,170,206', // 색상 디자인 검토 필요
        'PH' : '42,49,130',
    },
};
*/

/** 분석 */
/* 종합분석 용도별/설비별 차트 색상은 공통영역 참조 */

/* 종합분석 > 실내 온/습도 추이 차트 색상 */
const ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS = {
    default : {
        humidity : '157,173,192', // 습도
        temperature : '241,95,95', // 온도
    },
    dark : {
        humidity : '157,173,192', // 습도
        temperature : '241,95,95', // 온도
    },
};

/* 단위면적당 사용분석 차트 색상 */
const ANALYSIS_AREA_USED_COLORS = {
    default : {
        used : {
            previous : '157,173,192', // 이전기간 사용량
            current : '125,167,217', // 선택기간 사용량
        },
        price : {
            previous : '165,186,176', // 이전기간 사용요금
            current : '125,167,217', // 선택기간 사용요금
        },
    },
    dark : {
        used : {
            previous : '157,173,192', // 이전기간 사용량
            current : '125,167,217', // 선택기간 사용량
        },
        price : {
            previous : '165,186,176', // 이전기간 사용요금
            current : '125,167,217', // 선택기간 사용요금
        },
    },
}

/* 비교분석 차트 색상 */
/**
 * energy : 에너지원별
 * usage: 용도별
 * facility: 설비별
 */
const ANALYSIS_PERIOD_USED_COLORS = {
    default : {
        energy : {
            previous : '197,181,194', // 그제, 전월, 전년
            current : '99,73,112', // 어제, 금월, 금년
        },
        usage : {
            previous : '162,170,206',
            current : '42,49,130',
        },
        facility : {
            previous : '106,192,207',
            current : '18,97,127',
        },
    },
    dark : {
        energy : {
            previous : '197,181,194', // 그제, 전월, 전년
            current : '99,73,112', // 어제, 금월, 금년
        },
        usage : {
            previous : '162,170,206',
            current : '42,49,130',
        },
        facility : {
            previous : '106,192,207',
            current : '18,97,127',
        },
    },
};

/* 층별 비교 분석 차트 색상*/
/*
const ANALYSIS_FLOOR_COLORS = {
    default : {
        'B1' : '135,125,189', // 색상 디자인 검토 필요
        '1F' : '135,125,189',
        '2F' : '253,180,48',
        '3F' : '241,123,113',
        '4F' : '135,125,189', // 색상 디자인 검토 필요
        '5F' : '135,125,189', // 색상 디자인 검토 필요
        '6F' : '135,125,189', // 색상 디자인 검토 필요
        '7F' : '135,125,189', // 색상 디자인 검토 필요
        'PH' : '103,183,180',
    },
    dark : {
        'B1' : '135,125,189' , // 색상 디자인 검토 필요
        '1F' : '135,125,189',
        '2F' : '253,180,48',
        '3F' : '241,123,113',
        '4F' : '241,123,113', // 색상 디자인 검토 필요
        '5F' : '241,123,113', // 색상 디자인 검토 필요
        '6F' : '241,123,113', // 색상 디자인 검토 필요
        '7F' : '241,123,113', // 색상 디자인 검토 필요
        'PH' : '103,183,180',
    },
};
*/

/** 태양광 */
/* 태양광 메뉴 차트 색상 */
const SOLAR_GRAPH_COLORS = {
    default : {
        solar_compare_color : {
            production : '197,210,91', // 생산량
            consumption : '120,199,234', // 소비량
        },
        efficiency_color : '197,210,91', // 효율
    },
    dark : {
        solar_compare_color : {
            production : '197,210,91', // 생산량
            consumption : '120,199,234', // 소비량
        },
        efficiency_color : '197,210,91', // 효율
    },
};

/** 설비 */
/* 설비 효율 차트 색상 */
const FACILITY_CHART_COLORS = {
    default : {
        electric_water_heater : '1,0,255', // 전기온수기
        feed_pump : '255,94,0', // 급수펌프
        sump_pump : '0,216,255', // 배수펌프
        electric_ehp : '1,0,255', // EHP
    },
    dark : {
        electric_water_heater : '1,0,255', // 전기온수기
        feed_pump : '255,94,0', // 급수펌프
        sump_pump : '229,216,92', // 배수펌프
        electric_ehp : '1,0,255', // EHP
    },
};

/** 예측 */
/* 에너지원별 사용 예측, 태양광 발전 예측 차트 색상 */
const PREDICT_CHART_COLORS = {
    default : {
        floor_type : {
            current : '157,173,192', // 층별 예측 사용량 - 금일 현재 사용량
            predict : '125,167,217',
        },
        total_type : {
            current : '208,209,246', // 사무소 예측 사용량 - 금일 현재 사용량
            predict : '158,159,234',
        }
    },
    dark : {
        floor_type : {
            current : '157,173,192', // 층별 예측 사용량 - 금일 현재 사용량
            predict : '125,167,217',
        },
        total_type : {
            current : '208,209,246', // 사무소 예측 사용량 - 금일 현재 사용량
            predict : '158,159,234',
        }
    },
};

/** 제어 */
/* 제어 프로그레스바 차트 색상 */
const CONTROL_PROGRESS_CHART_COLOR = {
    default : '41,112,184',
    dark: '229,193,149',
};

/** 설정 */
/* 목표값/기준값 설정 */
const SET_STANDATD_TABLE_COLOR = {
    default : {
        colspan : '248,248,248',
    },
    dark : {
        colspan : '37,37,38',
    },
}

/** 모바일 */
/* '홈' 화면에서 용도별 사용현황 그래프 색상 */
const MOBILE_USAGE_CHART_COLORS = {
    electric_light : '212,168,37', // 조명(전등)
    electric_elechot : '210,86,58', // 전열
    electric_hotwater : '190,206,58', // 급f탕
    electric_cold : '33,150,243', // 냉방
    electric_vent : '120,187,101', // 환기
    electric_elevator : '96,125,139', // 운송(승강)
    electric_heating : '255,167,167', // 난방
    power_train: '255,187,0', // 동력
    electric_car: '255,187,0', // 전기차
    freeze: '255,187,0', // 냉동,냉장설비
    geothermal: '255,187,0', // 지열
};

/** [기타] */
/* 레이아웃 파일 리스트 */
const LAYOUT_CSS_FILES = {
    mdmt : 'layout_mdmt', // 무등산
    default : 'layout', // 기본
    dark : 'layout_dark', // 다크
};

/** (개발자용) 공통사항  */
/* 에너지원/용도별/설비별 키 네임 */
const KEYS_NAMES = {
    ELECTRIC : 'electric', // 전기
    GAS: 'gas', // 가스
    ELECTRIC_LIGHT : 'electric_light', // 조명(전등)
    ELECTRIC_ELECHOT : 'electric_elechot', // 전열
    ELECTRIC_HOTWATER : 'electric_hotwater', // 급탕
    ELECTRIC_COLD : 'electric_cold', // 냉방
    ELECTRIC_HEATING : 'electric_heating', // 난방
    ELECTRIC_VENT : 'electric_vent', // 환기
    ELECTRIC_ELEVATOR : 'electric_elevator', // 승강(운송)
    FREEZE : 'freeze', // 냉장/냉동설비
    COMMUNICATION_POWER : 'communication_power', // 통신전원
    POWER_TRAIN : 'power_train', // 동력
    ELECTRIC_CAR : 'electric_car', // 전기차
    ELECTRIC_WATER_HEATER : 'electric_water_heater', // 전기온수기
    FEED_PUMP : 'feed_pump', // 급수펌프
    SUMP_PUMP : 'sump_pump', // 배수펌프
    CIRCULATING_PUMP : 'circulating_pump', // 순환펌프
    ELECTRIC_EHP : 'electric_ehp', // EHP
    ELECTRIC_FLOOR_HEATING : 'electric_floor_heating', // 바닥난방
    OIL_DYU: 'oil_dyu', // 등유
    HEATING : 'heating', // 난방
    SOLAR : 'solar', // 태양광
    ELECTRIC_GHP : 'electric_ghp', // GHP
    CO2 : 'finedust_co2', // CO2
    FM25 : 'finedust_fm25', // FM25
    FM10: 'finedust_fm10', // FM10
    GEOTHERMAL: 'geothermal', // 지열
};

/* 설정 > 목표/기준값 설정 정보 */
const SET_INPUT_BOXES_TYPES = {
    'time' : ['한 시간 목표 사용량', '하루 목표 사용량', '한 달 목표 사용량', '일 년 목표 사용량'],
    'time_solar' : ['한 시간 목표 발전량', '하루 목표 발전량', '한 달 목표 발전량', '일 년 목표 발전량'],
    'finedust' : ['미세먼지 농도', '초미세먼지 농도'],
    'co2_type' : ['CO2 농도', '초미세먼지 농도'],
};

/* 개발도메인 체크 리스트 */
const DEVELOP_DOMAINS = [
    'lbems.work',
    'lbems.study',
    'lbems.4st.co.kr',
];

/* 버튼 눌렀을 때 색상정보(skinType)에 대해서 메뉴 파일 정의: 무등산 활용 금지 */
const PAGE_LOCATION_BUTTONS = {
    default: {
        대시보드_전체 : 'dashboard',
        대시보드_세부 : 'floor_facility',
    },
    dark: {
        대시보드_전체 : 'dashboard_dark',
        대시보드_세부 : 'floor_facility_dark'
    },
};

/* 비밀번호 정규식 */
const PASSWORD_RULES = {
    rule1: /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,15}$/, // 영문대소, 숫자
    rule2: /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[#?!@$%^&*-]).{8,15}$/, //  영문대소, 특수문자
    rule3: /^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,15}$/, // 영문대, 숫자, 특수문자
    rule4: /^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,15}$/, // 영문소, 숫자, 특수문자
};

module.config = function()
{
    let control = {
        selectedSkinType: '',
        setConfig: function(buildingFeCode, buildingName)
        {
            let self = control;
            let skinType = self.selectedSkinType;

            return {
                'B_2001' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층',
                        '2F' : '2층',
                        '3F' : '3층',
                        'PH' : '옥탑',
                        '0M' : '외부',
                        'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        190: '1층',
                        191: '2층',
                        192: '3층',
                        193: '옥탑'
                    },
                    facility_item : {
                        electric_water_heater : '전기온수기',
                        electric_ehp : 'EHP',
                        electric_floor_heating : '바닥난방'
                    },
                    usage_labels : ['데이터없음', '전등', '전열', '급탕', '난방', '냉/난방', '환기', '운송(승강기)'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_elechot'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_ELECHOT'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                        KEYS_NAMES['ELECTRIC_ELEVATOR']
                    ],
                    facility_labels : [
                        '데이터없음',
                        '전기온수기',
                        'EHP',
                        '바닥난방'
                    ],
                    facility_colors : [
                        DEFAULT_COLORS[skinType],
                        FACILITY_COLORS[skinType]['electric_water_heater'],
                        FACILITY_COLORS[skinType]['electric_ehp'],
                        FACILITY_COLORS[skinType]['electric_floor_heating']
                    ],
                    facility_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_WATER_HEATER'],
                        KEYS_NAMES['ELECTRIC_EHP'],
                        KEYS_NAMES['ELECTRIC_FLOOR_HEATING']
                    ],
                    floor : ['1F', '2F', '3F', 'PH'],
                    floor_name : ['1층', '2층', '3층', '옥탑'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    facility_start_index : 7,
                    facility_start_key : 'electric_water_heater',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: true,
                    is_use_manual: false,
                    control : {
                        file_type : 'mdmt',
                        command: 'control',
                        on_off_display: false,
                        is_ready: true, // 제어 준비여부 -  사용가능 true, 사용불가능 false
                        default_floor : { '1F' : '로비(대회의실앞)', '2F' : '체력단련실', '3F' : '영양사실' },
                        chart_color : CONTROL_PROGRESS_CHART_COLOR[skinType],
                        air_con_id: {
                          '1F' : [
                              'lobby_conference_front', 'lobby_disaster_side', 'ready_room_side', 'entrance_side',
                              'control_side', 'stage_side', 'disaster_room_side', 'service_office', 'photo_printing_room',
                              'center_entrance', 'service_office_front', 'commentary_main', 'commentary_center',
                              'commentary_inner', 'yeongseonsil', 'communication_room', 'watch_room'
                          ],
                          '2F' : [
                              'fitness_room', 'reference_room', 'elevator_front', 'fitting_man_room', 'all_fitting_man_room',
                              'all_fitting_woman_room', 'entrance_2f_side', 'pantry_2f_side', 'office_11_back', 'office_12_back'
                          ],
                          '3F' : [
                              'nutrition_room', 'serving_table_front', 'restaurant_entrance', 'hallway_restaurant_front',
                              'hallway_elevator_front', 'hallway_meeting_room_front', 'meeting_room_front', 'meeting_room_back',
                              'archive_room', 'all_office_leader_room', 'office_leader_room'
                          ],
                        },
                    },
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option : 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH']
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['mdmt'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : 'floor',
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage: DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    facility : {
                        graph_labels : ['EHP'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_ehp']
                        ],
                        graph_data : ['electric_ehp'],
                        is_auto_search : true,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info: {
                            energy: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time'],
                                group: '에너지원별',
                                items: { 전기: KEYS_NAMES['ELECTRIC'] },
                            },
                            usage: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time'],
                                group: '용도별',
                                items: {
                                    '조명': KEYS_NAMES['ELECTRIC_LIGHT'],
                                    전열: KEYS_NAMES['ELECTRIC_ELECHOT'],
                                    급탕: KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방: KEYS_NAMES['ELECTRIC_HEATING'],
                                    '냉/난방': KEYS_NAMES['ELECTRIC_COLD'],
                                    환기: KEYS_NAMES['ELECTRIC_VENT'],
                                    '운송(승강기)': KEYS_NAMES['ELECTRIC_ELEVATOR']
                                },
                            },
                            facility: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time'],
                                group: '설비별',
                                items: {
                                    EHP: KEYS_NAMES['ELECTRIC_EHP'],
                                    전기온수기: KEYS_NAMES['ELECTRIC_WATER_HEATER'],
                                    바닥난방: KEYS_NAMES['ELECTRIC_FLOOR_HEATING'],
                                },
                            },
                            solar: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time_solar'],
                                group: '태양광',
                                items: { 태양광: KEYS_NAMES['SOLAR'] },
                            },
                            device: {
                                input_boxes: SET_INPUT_BOXES_TYPES['finedust'],
                                group: '미세먼지',
                                items: {
                                    미세먼지: [
                                        KEYS_NAMES['FM10'], KEYS_NAMES['FM25']
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {
                        control : {
                            'command' : 'm_control',
                        },
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_ELECHOT'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['ELECTRIC_ELEVATOR']
                            ],
                            label : [
                                '데이터없음',
                                '전등',
                                '전열',
                                '급탕',
                                '난방',
                                '냉/난방',
                                '환기',
                                '운송'
                            ],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_elechot'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_heating'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                                MOBILE_USAGE_CHART_COLORS['electric_elevator']
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_ELECHOT'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_HEATING'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR']
                        ],
                    }
                },
                'B_2002' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층', '2F' : '2층', '3F' : '3층', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191: '1층', 192: '2층', 193: '3층'
                    },
                    facility_item : {
                        electric_water_heater : '전기온수기', feed_pump : '급수펌프', sump_pump : '배수펌프', circulating_pump : '순환펌프',
                    },
                    usage_labels : ['데이터없음', '전등', '전열', '급탕', '냉/난방', '환기', '승강', '동력(펌프)'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType], USAGE_COLORS[skinType]['electric_light'], USAGE_COLORS[skinType]['electric_elechot'],
                        USAGE_COLORS[skinType]['electric_hotwater'], USAGE_COLORS[skinType]['electric_cold'], USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator'], USAGE_COLORS[skinType]['power_train'],
                    ],
                    usage_key : [
                        '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN']
                    ],
                    facility_labels : ['데이터없음', '전기온수기', '급수펌프', '배수펌프', '순환펌프'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType], FACILITY_COLORS[skinType]['electric_water_heater'], FACILITY_COLORS[skinType]['feed_pump'],
                        FACILITY_COLORS[skinType]['sump_pump'], FACILITY_COLORS[skinType]['circulating_pump']
                    ],
                    facility_key : [
                        '-', KEYS_NAMES['ELECTRIC_WATER_HEATER'], KEYS_NAMES['FEED_PUMP'], KEYS_NAMES['SUMP_PUMP'], KEYS_NAMES['CIRCULATING_PUMP']
                    ],
                    floor : ['1F', '2F', '3F'],
                    floor_name : ['1층', '2층', '3층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    facility_start_index : 7,
                    facility_start_key : 'electric_water_heater',
                    auto_loading : true,
                    is_use_environment : true,
                    is_use_finedust_sensor: true,
                    is_use_manual: true,
                    power_factor: false,
                    control : {
                        file_type : 'tbmt',
                        command: 'control',
                        on_off_display: true,
                        is_ready: true, // 제어 준비여부 -  사용가능 true, 사용불가능 false
                        default_floor : { '1F' : '화장실(남)', '2F' : '농산물판매장', '3F' : '주방/식당' },
                        chart_color : CONTROL_PROGRESS_CHART_COLOR[skinType],
                        air_con_id: {
                            '1F' : ['toilet_man_status', 'toilet_woman_status'],
                            '2F' : ['sell_status', 'office_2_status', 'office_3_status', 'office_4_status', 'computer_status', 'fitting_room_woman_status', 'fitting_room_man_status'],
                            '3F' : ['restaurant_status', 'lodging_1_status', 'lodging_2_status'],
                        }
                    },
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'], '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'], '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['tbmt'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram_facility',
                        is_show_detail: false,
                    },
                    facility : {
                        graph_labels : ['전기온수기', '급수펌프', '배수펌프', '순환펌프'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_water_heater'], FACILITY_CHART_COLORS[skinType]['feed_pump'],
                            FACILITY_CHART_COLORS[skinType]['sump_pump'], FACILITY_COLORS[skinType]['circulating_pump']
                        ],
                        graph_data : ['electric_water_heater', 'feed_pump', 'sump_pump', 'circulating_pump'],
                        is_auto_search : true,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    전열 : KEYS_NAMES['ELECTRIC_ELECHOT'], 전등 : KEYS_NAMES['ELECTRIC_LIGHT'], 급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'], 환기 : KEYS_NAMES['ELECTRIC_VENT'], 동력 : KEYS_NAMES['POWER_TRAIN'],
                                    승강 : KEYS_NAMES['ELECTRIC_ELEVATOR']
                                },
                            },
                            facility : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '설비별',
                                items: {
                                    전기온수기: KEYS_NAMES['ELECTRIC_WATER_HEATER'], 배수펌프: KEYS_NAMES['SUMP_PUMP'], 순환펌프: KEYS_NAMES['CIRCULATING_PUMP'],
                                    급수펌프: KEYS_NAMES['FEED_PUMP'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                            device : {
                                input_boxes : SET_INPUT_BOXES_TYPES['co2_type'],
                                group : '실내 환경정보',
                                items: {
                                    '실내 환경정보': [
                                        KEYS_NAMES['CO2'], KEYS_NAMES['FM25'],
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {
                        control : {
                            'command' : 'm_control',
                        },
                        usage : {
                            key : [
                                '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN'],
                            ],
                            label : ['데이터없음', '전등', '전열', '급탕', '냉/난방', '환기', '운송', '동력'],
                            color : [
                                DEFAULT_COLORS['default'], MOBILE_USAGE_CHART_COLORS['electric_light'], MOBILE_USAGE_CHART_COLORS['electric_elechot'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'], MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'], MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                                MOBILE_USAGE_CHART_COLORS['power_train']
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'], KEYS_NAMES['ELECTRIC'], KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'], KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN']
                        ],
                    }
                },
                'B_2003' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층', '2F' : '2층', '3F' : '3층', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191: '1층', 192: '2층', 193: '3층'
                    },
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉방', '급탕', '난방', '환기', '운송'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                        KEYS_NAMES['ELECTRIC_ELEVATOR']
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F', '2F', '3F'],
                    floor_name : ['1층', '2층', '3층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: true,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['samcheock'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    냉방 : KEYS_NAMES['ELECTRIC_COLD'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                    운송 : KEYS_NAMES['ELECTRIC_ELEVATOR']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['ELECTRIC_ELEVATOR']
                            ],
                            label : ['데이터없음', '조명', '냉방', '급탕', '난방', '환기', '운송'],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_heating'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                                MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_HEATING'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR'],
                        ],
                    }
                },
                'B_2004' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층', '2F' : '2층', '3F' : '3층', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191: '1층', 192: '2층', 193: '3층'
                    },
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉/난방', '급탕', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_vent']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_VENT']
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F', '2F', '3F'],
                    floor_name : ['1층', '2층', '3층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['nedOffice'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: true,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_VENT']
                            ],
                            label : ['데이터없음', '조명', '냉/난방', '급탕', '환기'],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent']
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                        ],
                    }
                },
                'B_2005' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        'B1' : '지하1층', '1F' : '1층', '2F' : '2층', 'PH' : '옥상', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        194: '지하1층', 191: '1층', 192: '2층', 193: '옥상'
                    },
                    facility_item : {
                        electric_water_heater : '전기온수기', electric_ehp : 'EHP'
                    },
                    usage_labels : ['데이터없음', '조명', '냉/난방', '급탕', '운송', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType], USAGE_COLORS[skinType]['electric_light'], USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'], USAGE_COLORS[skinType]['electric_elevator'], USAGE_COLORS[skinType]['electric_vent']
                    ],
                    usage_key : [
                        '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['ELECTRIC_VENT']
                    ],
                    facility_labels : ['데이터없음', '전기온수기', 'EHP'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType], FACILITY_COLORS[skinType]['electric_water_heater'], FACILITY_COLORS[skinType]['electric_ehp']
                    ],
                    facility_key : [
                        '-', KEYS_NAMES['ELECTRIC_WATER_HEATER'], KEYS_NAMES['ELECTRIC_EHP']
                    ],
                    floor : ['B1', '1F', '2F', 'PH'],
                    floor_name : ['지하1층', '1층', '2층', '옥상'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    facility_start_index : 7,
                    facility_start_key : 'electric_water_heater',
                    auto_loading : true,
                    is_use_environment : true,
                    is_use_finedust_sensor: true,
                    is_use_manual: false,
                    power_factor: false,
                    control : {
                        file_type : 'ddmt',
                        command: 'samsung_control',
                        on_off_display: true,
                        is_ready: true, // 제어 준비여부 -  사용가능 true, 사용불가능 false
                        default_floor_key: {'1F' : 'lounge_room_1', '2F' : 'display_room_1'},
                        default_floor : {'1F' : '휴게공간1', '2F' : '탐방안내소1'},
                        chart_color : CONTROL_PROGRESS_CHART_COLOR[skinType],
                        air_con_id: {
                            '1F' : ['lounge_room_1', 'lounge_room_2', 'toilet_man', 'toilet_woman', 'office_room', 'nursing_room'],
                            '2F' : ['display_room_1', 'display_room_2', 'display_room_3', 'experience_room', 'ready_room'],
                        },
                        air_con_info : {
                            '1F' : {
                                'lounge_room_1' : '휴게공간1', 'lounge_room_2' : '휴게공간2', 'toilet_man' : '남자화장실',
                                'toilet_woman' : '여자화장실', 'office_room' : '사무실', 'nursing_room' : '수유실',
                            },
                            '2F' : {
                                'display_room_1' : '탐방안내소1', 'display_room_2' : '탐방안내소2', 'display_room_3' : '탐방안내소3',
                                'experience_room' : '시청각실', 'ready_room' : '준비실'
                            }
                        },
                    },
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'], '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'], 'PH' : FLOOR_CHART_COLOR[skinType]['PH'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'], '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'], 'PH' : FLOOR_CHART_COLOR[skinType]['PH']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['dado'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram_facility',
                        is_show_detail: false,
                    },
                    facility : {
                        graph_labels : ['전기온수기', 'EHP'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_water_heater'],
                            FACILITY_CHART_COLORS[skinType]['electric_ehp'],
                        ],
                        graph_data : ['electric_water_heater', 'electric_ehp'],
                        is_auto_search : false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'], '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'], 급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    운송 : KEYS_NAMES['ELECTRIC_ELEVATOR'], 환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                },
                            },
                            facility : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '설비별',
                                items: {
                                    전기온수기: KEYS_NAMES['ELECTRIC_WATER_HEATER'],
                                    EHP : KEYS_NAMES['ELECTRIC_EHP'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                            device : {
                                input_boxes : SET_INPUT_BOXES_TYPES['co2_type'],
                                group : '실내 환경정보',
                                items: {
                                    '실내 환경정보': [
                                        KEYS_NAMES['CO2'], KEYS_NAMES['FM25'],
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {
                        control : {
                            'command' : 'm_samsung_control',
                        },
                        usage : {
                            key : [
                                '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['POWER_TRAIN'],
                            ],
                            label : ['데이터없음', '조명', '냉/난방', '급탕', '운송', '환기'],
                            color : [
                                DEFAULT_COLORS['default'], MOBILE_USAGE_CHART_COLORS['electric_light'], MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'], MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'], KEYS_NAMES['ELECTRIC'], KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['ELECTRIC_VENT']
                        ],
                    }
                },
                'B_2006' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {'1F' : '1층'},
                    electric_floor_key_data : {},
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉방', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_vent'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                    ],
                    facility_labels : [
                        '데이터없음'
                    ],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F'],
                    floor_name : ['1층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: true,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                    },
                    analysis : {
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                    가스: KEYS_NAMES['GAS']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {}
                },
                'B_2007' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        'B1' : '지하1층', '1F' : '1층', '2F' : '2층', '3F' : '3층', '4F': '4층', '5F': '5층',
                        '6F' : '6층', '7F' : '7층', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {'ALL' : '전체'},
                    facility_item : {},
                    usage_labels : ['데이터없음', '냉/난방', '급탕', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_vent'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['B1', '1F', '2F', '3F', '4F', '5F', '6F', '7F'],
                    floor_name : ['지하1층', '1층', '2층', '3층', '4층', '5층', '6층', '7층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 4,
                    usage_start_key : 'electric_cold',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                    },
                    analysis : {
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    "냉/난방" : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                            ],
                            label : ['데이터없음', '냉방', '급탕', '환기'],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                        ],
                    }
                },
                'B_2008' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {'1F' : '1층'},
                    electric_floor_key_data : {},
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉/난방', '온수'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER']
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F'],
                    floor_name : ['1층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: true,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        }
                    },
                    analysis : {
                        total_menu : {
                            is_display_energy_percent: true,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                    가스: KEYS_NAMES['GAS'],
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    온수 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {}
                },
                'B_2010' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        'B1' : '지하1층',
                        '1F' : '1층',
                        '2F' : '2층',
                        '3F' : '3층',
                        'PH' : '옥상',
                        '0M' : '외부',
                        'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191 : '지하1층',
                        192 : '1층',
                        193 : '2층',
                        194 : '3층',
                    },
                    facility_item : {},
                    usage_labels : [
                        '데이터없음',
                        '조명',
                        '냉/난방',
                        '급탕',
                        '난방',
                        '환기',
                        '운송'
                    ],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                        KEYS_NAMES['ELECTRIC_ELEVATOR']
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : [
                        'B1',
                        '1F',
                        '2F',
                        '3F',
                        'PH'
                    ],
                    floor_name : [
                        '지하1층',
                        '1층',
                        '2층',
                        '3층',
                        '옥상'
                    ],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'],
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'],
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH'],
                            },
                        },
                        total_menu : {
                            is_display_energy_percent: true,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['kc_hc'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                    가스: KEYS_NAMES['ELECTRIC_GHP']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                    운송 : KEYS_NAMES['ELECTRIC_ELEVATOR']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['ELECTRIC_ELEVATOR']
                            ],
                            label : ['데이터없음', '조명', '냉/난방', '급탕', '난방', '환기', '운송'],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_heating'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                                MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_HEATING'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR'],
                        ],
                    }
                },
                'B_2011' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '2F' : '2층',
                        '3F' : '3층',
                        '4F' : '4층',
                        '5F' : '5층',
                        '6F' : '6층',
                        '7F' : '7층',
                        '8F' : '8층',
                        '9F' : '9층',
                        '10F' : '10층',
                    },
                    electric_floor_key_data : {},
                    facility_item : {},
                    usage_labels : ['데이터없음'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                    ],
                    usage_key : [
                        '-',
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['2F', '3F', '4F', '5F', '6F', '7F', '8F', '9F', '10F'],
                    floor_name : ['2층', '3층', '4층', '5층', '6층', '7층', '8층', '9층', '10층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info: {
                            energy: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time'],
                                group: '에너지원별',
                                items: {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                        }
                    }
                },
                'B_2012' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층'
                    },
                    electric_floor_key_data : {},
                    facility_item : {},
                    usage_labels : [
                        '데이터없음',
                        '조명',
                        '냉/난방',
                        '급탕',
                        '난방',
                        '환기',
                        '냉장/냉동',
                    ],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['freeze'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                        KEYS_NAMES['FREEZE'],
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : [
                        '1F',
                    ],
                    floor_name : [
                        '1층'
                    ],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F']
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F']
                            },
                        },
                        total_menu : {
                            is_display_energy_percent: false,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                    '냉장/냉동' : KEYS_NAMES['FREEZE'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['FREEZE'],
                            ],
                            label : ['데이터없음', '조명', '냉/난방', '급탕', '난방', '환기', '냉장/냉동'],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_heating'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                                MOBILE_USAGE_CHART_COLORS['freeze']
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_HEATING'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['FREEZE'],
                        ],
                    }
                },
                'B_2013' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {'1F' : '1층', 'PH' : '옥상'},
                    electric_floor_key_data : {},
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉/난방', '급탕', '난방', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F'],
                    floor_name : ['1층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        }
                    },
                    analysis : {
                        total_menu : {
                            is_display_energy_percent: true,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                    가스: KEYS_NAMES['GAS'],
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {}
                },
                'B_2014' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {'1F' : '1층', '2F': '2층'},
                    electric_floor_key_data : {'ALL' : '전체'},
                    facility_item : {
                        electric_ehp : 'EHP'
                    },
                    usage_labels : ['데이터없음', '조명', '냉/난방', '난방', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent']
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT']
                    ],
                    facility_labels : ['데이터없음', 'EHP'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType],
                        FACILITY_COLORS[skinType]['electric_ehp']
                    ],
                    facility_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_EHP']
                    ],
                    floor : ['1F', '2F'],
                    floor_name : ['1층', '2층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : true,
                    is_use_finedust_sensor: true,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        }
                    },
                    analysis : {
                        total_menu : {
                            is_display_energy_percent: false,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    facility : {
                        graph_labels : ['EHP'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_ehp'],
                        ],
                        graph_data : ['electric_ehp'],
                        is_auto_search : false,
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                },
                            },
                            facility: {
                                input_boxes: SET_INPUT_BOXES_TYPES['time'],
                                group: '설비별',
                                items: {
                                    EHP: KEYS_NAMES['ELECTRIC_EHP']
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                            device : {
                                input_boxes : SET_INPUT_BOXES_TYPES['co2_type'],
                                group : '실내 환경정보',
                                items: {
                                    '실내 환경정보': [
                                        KEYS_NAMES['CO2'], KEYS_NAMES['FM25'],
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {}
                },
                'B_2017' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        'B1' : '지하1층',
                        '1F' : '1층',
                        '2F' : '2층',
                        'PH' : '옥상',
                        '0M' : '외부',
                        'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191: '지하1층',
                        192: '1층',
                        193: '2층',
                        194: '옥상'
                    },
                    facility_item : {
                        electric_water_heater : '전기온수기',
                        electric_floor_heating : '바닥난방',
                        circulating_pump : '순환펌프',
                        electric_ehp : 'EHP'
                    },
                    usage_labels : ['데이터없음', '조명', '냉/난방', '급탕', '난방', '환기', '운송'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                        KEYS_NAMES['ELECTRIC_ELEVATOR'],
                    ],
                    facility_labels : ['데이터없음', '전기온수기', '바닥난방', '순환펌프', 'EHP'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType],
                        FACILITY_COLORS[skinType]['electric_water_heater'],
                        FACILITY_COLORS[skinType]['electric_floor_heating'],
                        FACILITY_COLORS[skinType]['circulating_pump'],
                        FACILITY_COLORS[skinType]['electric_ehp']
                    ],
                    facility_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_WATER_HEATER'],
                        KEYS_NAMES['ELECTRIC_FLOOR_HEATING'],
                        KEYS_NAMES['CIRCULATING_PUMP'],
                        KEYS_NAMES['ELECTRIC_EHP']
                    ],
                    floor : ['B1', '1F', '2F', 'PH'],
                    floor_name : ['지하1층', '1층', '2층', '옥상'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    facility_start_index : 7,
                    facility_start_key : 'electric_water_heater',
                    auto_loading : true,
                    is_use_environment : true,
                    is_use_finedust_sensor: true,
                    is_use_manual: false,
                    power_factor: false,
                    control : {
                        file_type : 'bhmt',
                        command: 'control',
                        on_off_display: false,
                        is_ready: true, // 제어 준비여부 -  사용가능 true, 사용불가능 false
                        default_floor : {'B1' : '시험장비창고', '1F' : '영선실', '2F' : '탐방시설(좌)'},
                        chart_color : CONTROL_PROGRESS_CHART_COLOR[skinType],
                        air_con_id: {
                            'B1' : [
                                'test_equipment_room', 'waiting_room_1', 'small_meeting_room_2', 'promotion_room',
                                'library_left', 'library_right', 'lobby', 'meeting_room_left', 'meeting_room_right',
                                'fitting_woman_room', 'fitting_man_room', 'fitness_man_room', 'fitness_woman_room'
                            ],
                            '1F' : [
                                'yeongseonsil', 'photo_printing_room', 'lounge_man_room', 'lounge_woman_room',
                                'waiting_room_1', 'commentary_room', 'lounge', 'hallway', 'pocket_space', 'office_2_left',
                                'office_2_right', 'watch_room', 'food_warehouse', 'lounge_room', 'staff_restaurant_left',
                                'staff_restaurant_right'
                            ],
                            '2F' : [
                                'exploration_facilities_left', 'exploration_facilities_right', 'resource_treasury_left',
                                'resource_treasury_right', 'administration_left', 'administration_right', 'pocket_space',
                                'hally_left', 'hally_right', 'small_meeting_room_1', 'office_leader_room', 'archive_room_left',
                                'archive_room_right', 'disaster_prevention_dept', 'disaster_room'
                            ],
                        }
                    },
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'],
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                'B1' : FLOOR_CHART_COLOR[skinType]['B1'],
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'],
                                '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                'PH' : FLOOR_CHART_COLOR[skinType]['PH']
                            },
                        },
                        total_menu : {
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram',
                        is_show_detail: false,
                    },
                    facility : {
                        graph_labels : ['전기온수기', 'EHP'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_water_heater'],
                            FACILITY_CHART_COLORS[skinType]['electric_ehp'],
                        ],
                        graph_data : ['electric_water_heater', 'electric_ehp'],
                        is_auto_search : false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC']
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                    운송 : KEYS_NAMES['ELECTRIC_ELEVATOR'],
                                    지열 : KEYS_NAMES['GEOTHERMAL'],
                                },
                            },
                            facility : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '설비별',
                                items: {
                                    전기온수기: KEYS_NAMES['ELECTRIC_WATER_HEATER'],
                                    바닥난방 : KEYS_NAMES['ELECTRIC_FLOOR_HEATING'],
                                    순환펌프 : KEYS_NAMES['CIRCULATING_PUMP'],
                                    EHP : KEYS_NAMES['ELECTRIC_EHP'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                            device : {
                                input_boxes : SET_INPUT_BOXES_TYPES['co2_type'],
                                group : '실내 환경정보',
                                items: {
                                    '실내 환경정보': [
                                        KEYS_NAMES['CO2'], KEYS_NAMES['FM25'],
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {
                        control : {
                            'command' : 'm_control',
                        },
                        usage : {
                            key : [
                                '-',
                                KEYS_NAMES['ELECTRIC_LIGHT'],
                                KEYS_NAMES['ELECTRIC_COLD'],
                                KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_HEATING'],
                                KEYS_NAMES['ELECTRIC_VENT'],
                                KEYS_NAMES['ELECTRIC_ELEVATOR']
                            ],
                            label : [
                                '데이터없음', '조명', '냉/난방', '급탕', '난방', '환기', '운송'
                            ],
                            color : [
                                DEFAULT_COLORS['default'],
                                MOBILE_USAGE_CHART_COLORS['electric_light'],
                                MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'],
                                MOBILE_USAGE_CHART_COLORS['electric_heating'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'],
                                MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'],
                            KEYS_NAMES['ELECTRIC'],
                            KEYS_NAMES['ELECTRIC_LIGHT'],
                            KEYS_NAMES['ELECTRIC_COLD'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'],
                            KEYS_NAMES['ELECTRIC_HEATING'],
                            KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR'],
                        ],
                    }
                },
                'B_2019' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {'B1' : '지하1층', '1F' : '1층', '2F' : '2층', 'PH' : '옥상'},
                    electric_floor_key_data : {'ALL' : '전체'},
                    facility_item : {},
                    usage_labels : ['데이터없음', '조명', '냉/난방', '급탕', '난방', '환기'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType],
                        USAGE_COLORS[skinType]['electric_light'],
                        USAGE_COLORS[skinType]['electric_cold'],
                        USAGE_COLORS[skinType]['electric_hotwater'],
                        USAGE_COLORS[skinType]['electric_heating'],
                        USAGE_COLORS[skinType]['electric_vent'],
                    ],
                    usage_key : [
                        '-',
                        KEYS_NAMES['ELECTRIC_LIGHT'],
                        KEYS_NAMES['ELECTRIC_COLD'],
                        KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_HEATING'],
                        KEYS_NAMES['ELECTRIC_VENT'],
                    ],
                    facility_labels : ['데이터없음'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType]
                    ],
                    facility_key : [
                        '-'
                    ],
                    floor : ['1F'],
                    floor_name : ['1층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        }
                    },
                    analysis : {
                        total_menu : {
                            is_display_energy_percent: true,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                    가스: KEYS_NAMES['GAS'],
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    조명 : KEYS_NAMES['ELECTRIC_LIGHT'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'],
                                    급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    난방 : KEYS_NAMES['ELECTRIC_HEATING'],
                                    환기 : KEYS_NAMES['ELECTRIC_VENT'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                        },
                    },
                    mobile : {}
                },
                'B_3001' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {},
                    electric_floor_key_data : {'ALL' : '전체'},
                    floor : [],
                    floor_name : [],
                    energy_start_index : 0,
                    energy_start_key : 'hybrid_main',
                    auto_loading : true,
                    factory_mode: true,
                    report : {
                        is_display_price: false,
                        usage_menu: {
                            chart_color: REPORT_BASIC_CHART_COLORS[skinType],
                        },
                    },
                },
                'B_9999' : {
                    building_name : buildingName,
                    default_color : DEFAULT_COLORS[skinType],
                    floor_key_data : {
                        '1F' : '1층', '2F' : '2층', '3F' : '3층', '0M' : '외부', 'ALL' : '전체'
                    },
                    electric_floor_key_data : {
                        191: '1층', 192: '2층', 193: '3층'
                    },
                    facility_item : {
                        electric_water_heater : '전기온수기', feed_pump : '급수펌프', sump_pump : '배수펌프', circulating_pump : '순환펌프'
                    },
                    usage_labels : ['데이터없음', '전등', '전열', '급탕', '냉/난방', '환기', '승강', '동력(펌프)'],
                    usage_colors : [
                        DEFAULT_COLORS[skinType], USAGE_COLORS[skinType]['electric_light'], USAGE_COLORS[skinType]['electric_elechot'],
                        USAGE_COLORS[skinType]['electric_hotwater'], USAGE_COLORS[skinType]['electric_cold'], USAGE_COLORS[skinType]['electric_vent'],
                        USAGE_COLORS[skinType]['electric_elevator'], USAGE_COLORS[skinType]['power_train']
                    ],
                    usage_key : [
                        '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                        KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN']
                    ],
                    facility_labels : ['데이터없음', '전기온수기', '급수펌프', '배수펌프', '순환펌프'],
                    facility_colors : [
                        DEFAULT_COLORS[skinType], FACILITY_COLORS[skinType]['electric_water_heater'], FACILITY_COLORS[skinType]['feed_pump'],
                        FACILITY_COLORS[skinType]['sump_pump'], FACILITY_COLORS[skinType]['circulating_pump']
                    ],
                    facility_key : [
                        '-', KEYS_NAMES['ELECTRIC_WATER_HEATER'], KEYS_NAMES['FEED_PUMP'], KEYS_NAMES['SUMP_PUMP'],
                        KEYS_NAMES['CIRCULATING_PUMP']
                    ],
                    floor : ['1F', '2F', '3F'],
                    floor_name : ['1층', '2층', '3층'],
                    energy_start_index : 0,
                    energy_start_key : 'electric',
                    usage_start_index : 3,
                    usage_start_key : 'electric_light',
                    facility_start_index : 7,
                    facility_start_key : 'electric_water_heater',
                    auto_loading : true,
                    is_use_environment : false,
                    is_use_finedust_sensor: false,
                    is_use_manual: false,
                    control : {
                        file_type : 'tbmt',
                        command: 'control',
                        is_ready: false, // 제어 준비여부 -  사용가능 true, 사용불가능 false
                        default_floor :  {'1F' : '화장실(남)', '2F' : '농산물판매장', '3F' : '주방/식당' },
                        chart_color : CONTROL_PROGRESS_CHART_COLOR[skinType],
                    },
                    info : {
                        chart_color : INFO_CHART_COLORS[skinType],
                    },
                    report : {
                        usage_menu : {
                            chart_color : REPORT_BASIC_CHART_COLORS[skinType],
                        },
                        floor_menu : {
                            option: 0,
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'], '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F'],
                            },
                        },
                    },
                    analysis : {
                        floor_menu : {
                            floor_color : {
                                '1F' : FLOOR_CHART_COLOR[skinType]['1F'], '2F' : FLOOR_CHART_COLOR[skinType]['2F'],
                                '3F' : FLOOR_CHART_COLOR[skinType]['3F']
                            },
                        },
                        total_menu : {
                            is_display_energy_percent: false,
                            chart_color : ANALYSIS_TEMPERATURE_AND_HUMIDITY_COLORS[skinType],
                        },
                        area_menu : {
                            chart_color : ANALYSIS_AREA_USED_COLORS[skinType],
                        },
                        period_menu : {
                            chart_color : ANALYSIS_PERIOD_USED_COLORS[skinType],
                        }
                    },
                    solar : {
                        chart_color : SOLAR_GRAPH_COLORS[skinType],
                    },
                    dashboard : {
                        option : 0,
                        date_type : 2,
                        predict_date_type : 2,
                        building_image : DH_DETAIL_BUILDING_IMAGE['default'],
                        facility_title : '설비별 사용량', // 효율이 나온다면 설비별 사용량 및 효율로 변경
                        floor_page : PAGE_LOCATION_BUTTONS[skinType]['대시보드_세부'],
                        energy_graph_color : DH_ENERGY_GRAPH_COLORS[skinType],
                        prediction_graph_color : DH_DETAIL_PREDICT_GRAPH_COLORS[skinType],
                        zero_graph_color : DH_ZERO_ENERGY_GRAPH_COLORS[skinType],
                        floor_background_color : DH_DETAIL_FLOOR_BACKGROUND_COLOR[skinType],
                        cutout_percentage : DH_USAGE_CHART_CUTOUT_PERCENTAGE[skinType],
                    },
                    diagram : {
                        command : 'diagram_facility',
                        is_show_detail: false,
                    },
                    facility : {
                        graph_labels : ['전기온수기', '급수펌프', '배수펌프', '순환펌프'],
                        graph_colors : [
                            FACILITY_CHART_COLORS[skinType]['electric_water_heater'], FACILITY_CHART_COLORS[skinType]['feed_pump'],
                            FACILITY_CHART_COLORS[skinType]['sump_pump'], FACILITY_COLORS[skinType]['circulating_pump']
                        ],
                        graph_data : ['electric_water_heater', 'feed_pump', 'sump_pump', 'circulating_pump'],
                        is_auto_search : false,
                    },
                    predict : {
                        chart_color : PREDICT_CHART_COLORS[skinType],
                    },
                    set : {
                        colspan_color: SET_STANDATD_TABLE_COLOR[skinType]['colspan'],
                        info : {
                            energy : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '에너지원별',
                                items : {
                                    전기: KEYS_NAMES['ELECTRIC'],
                                },
                            },
                            usage : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '용도별',
                                items : {
                                    전열 : KEYS_NAMES['ELECTRIC_ELECHOT'], 전등 : KEYS_NAMES['ELECTRIC_LIGHT'], 급탕 : KEYS_NAMES['ELECTRIC_HOTWATER'],
                                    '냉/난방' : KEYS_NAMES['ELECTRIC_COLD'], 환기 : KEYS_NAMES['ELECTRIC_VENT'], 동력 : KEYS_NAMES['POWER_TRAIN'],
                                    승강 : KEYS_NAMES['ELECTRIC_ELEVATOR']
                                },
                            },
                            facility : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time'],
                                group : '설비별',
                                items: {
                                    전기온수기: KEYS_NAMES['ELECTRIC_WATER_HEATER'], 배수펌프: KEYS_NAMES['SUMP_PUMP'], 순환펌프: KEYS_NAMES['CIRCULATING_PUMP'],
                                    급수펌프: KEYS_NAMES['FEED_PUMP'],
                                },
                            },
                            solar : {
                                input_boxes : SET_INPUT_BOXES_TYPES['time_solar'],
                                group : '태양광',
                                items: {
                                    태양광: KEYS_NAMES['SOLAR'],
                                },
                            },
                            device: {
                                input_boxes: SET_INPUT_BOXES_TYPES['finedust'],
                                group: '미세먼지',
                                items: {
                                    미세먼지: [
                                        KEYS_NAMES['FM10'], KEYS_NAMES['FM25']
                                    ],
                                }
                            },
                        },
                    },
                    mobile : {
                        usage : {
                            key : [
                                '-', KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'], KEYS_NAMES['ELECTRIC_HOTWATER'],
                                KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'], KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN'],
                            ],
                            label : ['데이터없음', '전등', '전열', '급탕', '냉/난방', '환기', '운송', '동력'],
                            color : [
                                DEFAULT_COLORS['default'], MOBILE_USAGE_CHART_COLORS['electric_light'], MOBILE_USAGE_CHART_COLORS['electric_elechot'],
                                MOBILE_USAGE_CHART_COLORS['electric_hotwater'], MOBILE_USAGE_CHART_COLORS['electric_cold'],
                                MOBILE_USAGE_CHART_COLORS['electric_vent'], MOBILE_USAGE_CHART_COLORS['electric_elevator'],
                                MOBILE_USAGE_CHART_COLORS['power_train']
                            ],
                        },
                        diagram : [
                            KEYS_NAMES['SOLAR'], KEYS_NAMES['ELECTRIC'], KEYS_NAMES['ELECTRIC_LIGHT'], KEYS_NAMES['ELECTRIC_ELECHOT'],
                            KEYS_NAMES['ELECTRIC_HOTWATER'], KEYS_NAMES['ELECTRIC_COLD'], KEYS_NAMES['ELECTRIC_VENT'],
                            KEYS_NAMES['ELECTRIC_ELEVATOR'], KEYS_NAMES['POWER_TRAIN']
                        ],
                    }
                },
            }
        },
        getConfig: function(complexCodePk, buildingFeCode, buildingName, skinType, isDevMode = 0)
        {
            let self = control;
            self.selectedSkinType = skinType;

            let configData = self.setConfig(buildingFeCode, buildingName);
            let domain = window.location.host;

            let buildingConfigData = configData[buildingFeCode];
            if (buildingConfigData === undefined) {
                // 설정된 정보가 없는 경우 무등산으로 할 것
                buildingConfigData = configData[DEFAULT_BUILDING_NAME];
            }

            /*
                if (isDevMode === 1  && jQuery.inArray(domain, DEVELOP_DOMAINS) === 0) {
                    // 개발모드 일경우 설정 파일 정보 일부를 변경한다.
                    buildingConfigData['layout_css_path'] = `${LAYOUT_CSS_PATH}/${LAYOUT_CSS_FILES['dark']}.css`;
                }
             */

            buildingConfigData['password_rule'] = PASSWORD_RULES;

            return buildingConfigData;
        },
    };

    return control;
}