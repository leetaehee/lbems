<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * DadoseaMountainSensor 다도해 메뉴별 센서 정의
 */
class DadoseaMountainSensor implements SensorInterface
{
    /**
     * 에너지원, 용도별, 설비별 분류
     *
     * @return array
     */
    public function getEnergyPartData() : array
    {
        return [
            'energy' => [
                'electric' => [
                    'option' => 0,
                    'label' => '전기'
                ],
            ],
            'usage' => [
                'electric_light' => [
                    'option' => 3,
                    'label' => '조명'
                ],
                'electric_cold' => [
                    'option' => 4,
                    'label' => '냉/난방'
                ],
                'electric_hotwater' => [
                    'option' => 7,
                    'label' => '급탕'
                ],
                'electric_elevator' => [
                    'option' => 6,
                    'label' => '운송'
                ],
                'electric_vent' => [
                    'option' => 10,
                    'label' => '환기'
                ],
            ],
            'facility' => [
                'electric_water_heater' => [
                    'option' => 7,
                    'label' => '전기온수기'
                ],
                'electric_ehp' => [
                    'option' => 4,
                    'label' => 'EHP'
                ],
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
                    '1층 전체 전력1' => '2005_3_12',
                ]
            ],
            '한전 전체 전력' => [
                0 => [
                    '1층 전체 전력1' => '2005_3_12',
                ],
                11 => [
                  '신재생1' => '2005_3_1',
                  '신재생2' => '2005_3_4',
                ],
            ],
            '지하 1층 전체 전력' => [
                0 => [
                    '지하 1층 전체 전력1' => '2005_1_1',
                    '지하 1층 전체 전력2' => '2005_3_3',
                ],
            ],
            '1층 전체 전력' => [
                0 => [
                    '전기1' => '2005_4_1',
                ],
                3 => [
                    '전등1' => '2005_3_5',
                    '전등2' => '2005_3_6',
                    '전등3' => '2005_3_8',
                    '전등4' => '2005_3_9',
                ],
                4 => [
                    '냉방1' => '2005_3_11',
                ],
                7 => [
                    '급탕1' => '2005_3_7',
                ],
                10 => [
                    '환기1' => '2005_3_13',
                    '환기2' => '2005_3_10',
                ],
                /*
                0 => [
                    '지하 1층 전체 전력1' => '2005_1_1',
                    '지하 1층 전체 전력2' => '2005_3_3',
                    '1층 전체 전력1' => '2005_3_12',
                    '2층 전체 전력1' => '2005_5_1',
                    '옥탑 전체 전력1' => '2005_6_1',
                ],
                6 => [
                    '운송' => '2005_3_2',
                ],
                */
            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 전체 전력1' => '2005_5_1',
                ]
            ],
            '옥탑 전체 전력' => [
                0 => [
                    '옥탑 전체 전력1' => '2005_6_1',
                ],
                6 => [
                    '옥탑 전체 전력2' => '2005_3_2',
                ]
            ]
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
                'all' => '2005_ALL',
            ],
            'B1' => [
                'all' => '2005_B1',
            ],
            '1F' => [
                'all' => '2005_1F',
            ],
            '2F' => [
                'all' => '2005_2F',
            ],
            'PH' => [
                'all' => '2005_PH',
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
                'name' => '전기'
            ],
            'electric_light' => [
                'name' => '조명'
            ],
            'electric_cold' => [
                'name' => '냉/난방'
            ],
            'electric_hotwater' => [
                'name' => '급탕'
            ],
            'electric_elevator' => [
                'name' => '운송'
            ],
            'electric_vent' => [
                'name' => '환기'
            ],
            'solar' => [
                'name' => '태양광'
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
            'electric_water_heater' => [
                '1F' => ['2005_3_7','2005_4_2', '2005_4_3', '2005_4_4', '2005_4_5'],
                '2F' => ['2005_5_7'],
            ],
            'electric_ehp' => [
                'PH' => ['2005_6_2', '2005_6_3'],
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
            'in' => '2005_1',
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
            'B1',
            '1F',
            '2F',
            'PH'
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
                'diagram' => './diagram/diagram_ddmt.html', // 계통도
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
    public function getControlDeviceInfo() :array
    {
        return [
            '1F' => [
                '사무실' => '12.01.01',
                '수유실' => '12.01.02',
                '휴게공간1' => '12.01.04',
                '휴게공간2' => '12.01.03',
                '남자화장실' => '12.03.02',
                '여자화장실' => '12.03.01',
            ],
            '2F' => [
                '탐방안내소1' => '12.02.03',
                '탐방안내소2' => '12.02.02',
                '탐방안내소3' => '12.02.01',
                '시청각실' => '12.02.04',
                '준비실' => '12.02.05',
            ],
        ];
    }

    /**
     * 계통도 키 정보
     *
     * @return array
     */
    public function getDiagramKeyInfo() :array
    {
        return [
            'used' => [
                'solar',
                'electric',
                'electric_light',
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
                'electric_elevator',
            ],
            'distribution' => [
                'electric_light',
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
                'electric_elevator',
                'power_train'
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