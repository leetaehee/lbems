<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class TaebaekMountainSensor 태백산 메뉴별 센서 정의
 */
class TaebaekMountainSensor implements SensorInterface
{
    /**
     * 전열 미터 데이터 생성을 위한 센서 정의
     *
     * @return array|mixed
     */
    public function getElectricElechotSensor() : array
    {
        return [
            '1층 홀' => [
                0 => [
                    '1층 전체 전력1' => '2002_1_1',
                    '2층 전체 전력1' => '2002_3_1',
                    '2층 전체 전력3' => '2002_5_1',
                    '3층 전체 전력1' => '2002_6_1',
                    '동력 시설1' => '2002_2_1',
                ],
                4 => [
                    '냉난방1' => '2002_1_2',
                    '냉난방2' => '2002_1_4',
                    '냉난방3' => '2002_1_11',
                ],
                6 => [
                    '운송1' => '2002_1_5',
                ],
                10 => [
                    '환기1' => '2002_1_8',
                ],
                11 => [
                    '신재생 소비량' => '2002_1_3',
                ],
                3 => [
                    '전등1' => '2002_1_6',
                    '전등2' => '2002_1_7',
                    '전등3' => '2002_1_9',
                    '전등4' => '2002_1_10',
                ],
            ],
            '1층 물탱크실'=> [
                0 => [
                    '동력 시설1' => '2002_2_1',
                ],
                12 => [
                    '급수 시설1' => '2002_2_2',
                    '배수 시설1' => '2002_2_5',
                ],
                7 => [
                    '급탕 시설1' => '2002_2_3',
                    '급탕 시설2' => '2002_2_4',
                    '급탕 에너지1' => '2002_2_6'
                ],
            ],
            '2층 홀' => [
                0 => [
                    '2층 전체 전력1' => '2002_3_1'
                ],
                3 => [
                    '전등1' => '2002_3_4',
                ],
                7 => [
                    '급탕 에너지1' => '2002_3_2',
                ],
                10 => [
                    '환기1' => '2002_3_3',
                    '환기2' => '2002_3_5',
                ],
            ],
            '2층 농산물 판매장' => [
                0 => [
                    '2층 전체 전력2' => '2002_4_1',
                ],
                3 => [
                    '전등1' => '2002_4_4',
                ],
                7 => [
                    '급탕1' => '2002_4_2',
                ],
                10 => [
                    '환기1' => '2002_4_3',
                    '환기2' => '2002_4_5',
                ],
                4 => [
                    '냉난방1' => '2002_4_6',
                ]
            ],
            '2층 사무실' => [
                0 => [
                    '2층 전체 전력3' => '2002_5_1',
                ],
                3 => [
                    '전등1' => '2002_5_3',
                ],
                10 => [
                    '환기1' => '2002_5_4',
                ]
            ],
            '3층 홀' => [
                0 => [
                    '3층 전체 전력1' => '2002_6_1',
                ],
                3 => [
                    '전등1' => '2002_6_3',
                ],
                10 => [
                    '환기1' => '2002_6_2',
                    '환기2' => '2002_6_4',
                ],
            ],
            '3층 공관숙소1' => [
                0 => [
                    '3층 전체 전력2' => '2002_7_1',
                ],
                3 => [
                    '전등1' => '2002_7_5',
                ],
                7 => [
                    '급탕1' => '2002_7_3',
                ],
                10 => [
                    '환기1' => '2002_7_4',
                    '환기2' => '2002_7_6',
                ],
                4 => [
                    '냉난방1' => '2002_7_2',
                ],
            ],
            '3층 공관숙소2' => [
                0 => [
                    '3층 전체 전력3' => '2002_8_1',
                ],
                3 => [
                    '전등1' => '2002_8_4',
                ],
                7 => [
                    '급탕1' => '2002_8_2',
                ],
                10 => [
                    '환기1' => '2002_8_3',
                ],
                4 => [
                    '냉난방1' => '2002_8_5',
                ],
            ],
        ];
    }

    /**
     * 에너지원, 용도별, 설비별 분류
     *
     * @return array
     */
    public function getEnergyPartData() : array
    {
        return [
            'energy' => [
                'electric' => ['option' => 0, 'label' => '전기'],
            ],
            'usage' => [
                'electric_light' => ['option' => 3, 'label' => '전등'],
                'electric_elechot' => ['option' => 5, 'label' => '전열'],
                'electric_hotwater' => ['option' => 7, 'label' => '급탕'],
                'electric_cold' => ['option' => 4, 'label' => '냉/난방'],
                'electric_vent' => ['option' => 10, 'label' => '환기'],
                'electric_elevator' => ['option' => 6, 'label' => '운송(승강기)'],
                'power_train' => ['option' => 0, 'label' => '동력'],
            ],
            'facility' => [
                'electric_water_heater' => ['option' => 7, 'label' => '전기온수기'],
                'feed_pump' => ['option' => 12, 'label' => '급수펌프'],
                'sump_pump' => ['option' => 12, 'label' => '배수펌프'],
                'circulating_pump' => ['option' => 7, 'label' => '순환펌프'],
            ],
        ];
    }

    /**
     * 전기 층, 룸별 센서 조회
     *
     * @return array|mixed
     */
    public function getElectricSensor() : array
    {
        return [
            '전체 전력' => [
                0 => [
                    '1층 전체 전력' => '2002_1F',
                    '2층 전체 전력' => '2002_2F',
                    '3층 전체 전력' => '2002_3F',
                ],
            ],
            '한전 전체 전력' => [
                0 => [
                    '전체 전력' => '2002_1_2_3_F'
                ],
                11 => [
                    '신재생1' => '2002_1_3',
                ],
            ],
            '1층 전체 전력' => [
                0 => [
                    '1층 전체 전력1' => '2002_1_1',
                    '2층 전체 전력1' => '2002_3_1',
                    '2층 전체 전력3' => '2002_5_1',
                    '3층 전체 전력1' => '2002_6_1',
                    //'동력 시설1' => '2002_2_1',
                ],
                11 => [
                    '신재생 소비량' => '2002_1_3',
                ],
            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 전체 전력1' => '2002_3_1',
                    '2층 전체 전력2' => '2002_4_1',
                    '2층 전체 전력3' => '2002_5_1',
                ],
            ],
            '3층 전체 전력' => [
                0 => [
                    '3층 전체 전력1' => '2002_6_1',
                    '3층 전체 전력2' => '2002_7_1',
                    '3층 전체 전력3' => '2002_8_1',
                ]
            ],
        ];
    }

    /**
     * 전기 동별 센서 조회
     *
     * @return array
     */
    public function getElectricDongSensor() : array
    {
        return [
        ];
    }

    /**
     * 전기 층별 센서 조회
     *
     * @return array|mixed
     */
    public function getElectricFloorSensor() : array
    {
        return [
            'all' => [
                'all' => '2002_ALL',
            ],
            '1F' => [
                'all' => '2002_1F',
            ],
            '2F' => [
                'all' => '2002_2F',
            ],
            '3F' => [
                'all' => '2002_3F',
            ],
        ];
    }

    /**
     * 장애알람 에너지원 조회
     *
     * @return mixed
     */
    public function getHindranceAlarmSensor() : array
    {
        return [
            'electric' => [
                'name' => '전기',
            ],
            'electric_light' => [
                'name' => '전등',
            ],
            'electric_elechot' => [
                'name' => '전열',
            ],
            'electric_hotwater' => [
                'name' => '급탕',
            ],
            'electric_cold' => [
                'name' => '냉/난방',
            ],
            'electric_vent' => [
                'name' => '환기',
            ],
            'electric_elevator' => [
                'name' => '승강',
            ],
            'electric_water' => [
                'name' => '급수배수',
            ],
            'solar' => [
                'name' => '태양광',
            ],
        ];
    }

    /**
     * 업체별 커스텀 설비 목록
     *
     * @return array
     */
    public function getSpecialSensorKeyName() : array
    {
        return [
            'power_train' => [
                '1F' => ['2002_2_1'],
            ],
            'electric_water_heater' => [
                '1F' => [
                    '2002_2_6'
                ],
                '2F' => [
                    '2002_3_2',
                    '2002_4_2'
                ],
                '3F' => [
                    '2002_7_3',
                    '2002_8_2'
                ],
            ],
            'feed_pump' => [
                '1F' => [
                    '2002_2_2'
                ],
            ],
            'sump_pump' => [
                '1F' => [
                    '2002_2_5'
                ],
            ],
            'circulating_pump' => [
                '1F' => [
                    '2002_2_3',
                    '2002_2_4'
                ]
            ],
        ];
    }

    /**
     * 태양광 센서 정보
     *
     * @return mixed
     */
    public function getSolarSensor() : array
    {
        return [
            'in' => '2002_1',
            'out' => ''
        ];
    }

    /**
     * 층 정보
     *
     * @return mixed
     */
    public function getFloorInfo() : array
    {
        return [
            '1F',
            '2F',
            '3F'
        ];
    }

    /**
     * 보고서 정보
     *
     * @return mixed
     */
    public function getPaperInfo() : array
    {
        return [
        ];
    }

    /**
     * 모바일 메뉴 조회
     *
     * @return mixed
     */
    public function getMobileMenuInfo() : array
    {
        return [
            'group' => [
                'home' => [
                    'key' => 'home',
                    'name' => '홈',
                ],
                'diagram' => [
                    'key' => 'diagram',
                    'name' => '계통도',
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
                            'name' => '태양광 발전',
                        ]
                    ],
                ],
                'control' => [
                    'key' => 'control',
                    'name' => '제어',
                ],
            ],
            'menu' => [
                'home' => './home/home.html', //홈
                'diagram' => './diagram/diagram_tbmt.html', // 계통도
                'prediction_energy' => './prediction/energy.html', // 예측-에너지원별
                'prediction_solar' => './prediction/solar.html', // 예측-태양광발전
                'control' => './control/control.html', // 제어
            ],
        ];
    }

    /**
     * 제어 정보
     *
     * @return mixed
     */
    public function getControlDeviceInfo() : array
    {
        return [
            '1F' => [
                '화장실(남)' => 0,
                '화장실(여)' => 1,
            ],
            '2F' => [
                '농산물판매장' => 3,
                '탈의실(남)' => 5,
                '탈의실(여)' => 4,
                '사무실(1번)' => 6,
                '사무실(2번)' => 7,
                '분소장실' => 8,
                '전산실' => 2,
            ],
            '3F' => [
                '주방/식당' => 9,
                '공관(숙소)-1' => 11,
                '공관(숙소)-2' => 10,
            ],
        ];
    }

    /**
     * 계통도 키 정보
     *
     * @return array
     */
    public function getDiagramKeyInfo() : array
    {
        return [
            'used' => [
                'solar',
                'electric',
                'electric_light',
                'electric_elechot',
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
                'electric_elevator',
                'power_train',
                'electric_water_heater',
                'circulating_pump',
                'feed_pump',
                'sump_pump',
            ],
            'distribution' => [
                'electric_light',
                'electric_elechot',
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
                'electric_elevator',
                'power_train',
                'electric_water_heater',
                'circulating_pump',
                'feed_pump',
                'sump_pump',
            ],
        ];
    }

    /**
     * 센서 별칭 정의
     *
     * @return array
     */
    public function getSensorAliasInfo() : array
    {
        return [
        ];
    }

    /**
     * 에너지 단위 커스텀 하기
     *
     * @return mixed
     */
    public function getCustomUnit() : array
    {
        return [
        ];
    }
}