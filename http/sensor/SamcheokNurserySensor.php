<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class SamcheokNurserySensor 삼척 어린이집 메뉴별 센서 정의
 */
class SamcheokNurserySensor implements SensorInterface
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
                    'label' => '냉방'
                ],
                'electric_hotwater' => [
                    'option' => 7,
                    'label' => '급탕'
                ],
                'electric_heating' => [
                    'option' => 8,
                    'label' => '난방'
                ],
                'electric_vent' => [
                    'option' => 10,
                    'label' => '환기'
                ],
                'electric_elevator' => [
                    'option' => 6,
                    'label' => '운송'
                ],
            ],
        ];
    }

    /**
     * 전기 층, 룸별 센서 조회
     *
     * @return array
     */
    public function getElectricSensor() : array
    {
        return [
            '전체 전력' => [
                0 => [
                    '1층 외부 기사대기실 옆' => '2003_1_1',
                ],
                11 => [
                    '신재생 소비량' => '2003_1_3',
                ]
            ],
            '한전 전체 전력' => [
                0 => [
                    '1층 외부 기사대기실 옆' => '2003_1_1',
                ],
            ],
            '1층 전체 전력' => [
                0 => [
                    '1세반 앞 복도' => '2003_2_1',
                ],
                6 => [
                    '1층 외부 기사대기실 옆' => '2003_1_2',
                ],
            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 엘레베이터 홀' => '2003_3_1',
                ]
            ],
            '3층 전체 전력' => [
                0 => [
                    '3층 계단실' => '2003_4_1'
                ],
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
     * @return array
     */
    public function getElectricFloorSensor() : array
    {
        return [
            'all' => [
                'all' => '2003_ALL',
            ],
            '1F' => [
                'all' => '2003_1F',
            ],
            '2F' => [
                'all' => '2003_2F',
            ],
            '3F' => [
                'all' => '2003_3F',
            ]
        ];
    }

    /**
     * 장애알람 에너지원 조회
     *
     * @return array
     */
    public function getHindranceAlarmSensor() : array
    {
        return [
            'electric' => [
                'name' => '전기',
            ],
            'electric_cold' => [
                'name' => '냉/난방',
            ],
            'electric_light' => [
                'name' => '전등',
            ],
            'electric_hotwater' => [
                'name' => '급탕'
            ],
            'electric_heating' => [
                'name' => '난방',
            ],
            'electric_vent' => [
                'name' => '환기',
            ],
            'electric_elevator' => [
                'name' => '운송',
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
            'in' => '2003_1',
            'out' => '2003_1_3'
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
            '1F',
            '2F',
            '3F'
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
                'diagram' => './diagram/diagram_scnr.html', // 계통도
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
                'electric_light',
                'electric_hotwater',
                'electric_heating',
                'electric_cold',
                'electric_vent',
                'electric_elevator',
            ],
            'distribution' => [
                'electric_light',
                'electric_hotwater',
                'electric_cold',
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