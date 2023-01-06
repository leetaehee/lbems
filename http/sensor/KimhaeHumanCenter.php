<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * KimhaeHumanCenter 김해시 행정복지센터 메뉴별 센서 정의
 */
class KimhaeHumanCenter implements SensorInterface
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
                'electric' => ['option' => 0, 'label' => '전기'],
                'electric_ghp' => ['option' => 4, 'label' => '가스'],
            ],
            'usage' => [
                'electric_light' => ['option' => 3, 'label' => '조명'],
                'electric_cold' => ['option' => 4, 'label' => '냉/난방'],
                'electric_hotwater' => ['option' => 7, 'label' => '급탕'],
                'electric_heating' => ['option' => 8, 'label' => '난방'],
                'electric_vent' => ['option' => 10, 'label' => '환기'],
                'electric_elevator' => ['option' => 6, 'label' => '운송'],
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
            '한전 전체 전력' => [
                11 => [
                    '신재생1' => '2010_1_1',
                ],
                6 => [
                    '운송1' => '2010_1_2',
                ],
                0 => [
                    '전기차 충전1' => '2010_1_3',
                    '지하1층 전체1' => '2010_2_1',
                    '지하1층 전체3' => '2010_3_1',
                    '1층 전체1' => '2010_4_1',
                    '1층 전체2' => '2010_5_1',
                    '1층 전체3' => '2010_6_1',
                    '2층 전체1' => '2010_7_1',
                    '3층 전체1' => '2010_10_1',
                    '옥상 전체1' => '2010_13_1',
                ],
            ],
            '지하1층 전체전력' => [
                0 => [
                    '지하1층 전체1' => '2010_2_1',
                    '지하1층 전체3' => '2010_3_1',
                ],
            ],
            '1층 전체전력' => [
                0 => [
                    '1층 전체1' => '2010_4_1',
                    '1층 전체2' => '2010_5_1',
                    '1층 전체3' => '2010_6_1',
                 ],
            ],
            '2층 전체전력' => [
                0 => [
                    '2층 전체1' => '2010_7_1',
                ],
            ],
            '3층 전체전력' => [
                0 => [
                    '3층 전체1' => '2010_10_1'
                ],
            ],
            '전기 가스 태양광' => [
                4 => [
                    'GHP' => '8121120924',
                ],
                11 => [
                    '태양광 발전량' => '2010_1',
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
                'all' => '2010_ALL',
            ],
            'B1' => [
                'all' => '2010_B1',
            ],
            '1F' => [
                'all' => '2010_1F',
            ],
            '2F' => [
                'all' => '2010_2F',
            ],
            '3F' => [
                'all' => '2010_3F',
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
            'electric_heating' => [
                'name' => '난방'
            ],
            'electric_vent' => [
                'name' => '환기'
            ],
            'electric_elevator' => [
                'name' => '운송'
            ],
            'solar' => [
                'name' => '태양광'
            ]
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
            'electric_ghp' => [
                'PH' => ['8121120924']
            ],
            'electric_egs' => [
                '1F' => ['2010_EGS']
            ]
        ];
    }

    /**
     * 태양광 센서 정보
     *
     * @return array
     */
    public function getSolarSensor() : array
    {
        return [
            'in' => '2010_1',
            'out' => '2010_1_1',
        ];
    }

    /**
     * 층 정보
     *
     * @return array
     */
    public function getFloorInfo() : array
    {
        return [
            'B1',
            '1F',
            '2F',
            '3F',
            'PH'
        ];
    }

    /**
     * 모바일 메뉴 조회
     *
     * @return array
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
            ],
            'menu' => [
                'home' => './home/home.html', //홈
                'diagram' => './diagram/diagram_kimhae_hc.html', // 계통도
                'prediction_energy' => './prediction/energy.html', // 예측-에너지원별
                'prediction_solar' => './prediction/solar.html', // 예측-태양광발전
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
                'electric_ghp',
                'electric_light',
                'electric_cold',
                'electric_hotwater',
                'electric_heating',
                'electric_vent',
                'electric_elevator',
            ],
            'distribution' => [
                'electric_light',
                'electric_cold',
                'electric_hotwater',
                'electric_heating',
                'electric_vent',
                'electric_elevator',
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