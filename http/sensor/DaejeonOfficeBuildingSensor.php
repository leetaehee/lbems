<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class DaejeonOfficeBuildingSensor 삼척 어린이집 메뉴별 센서 정의
 */
class DaejeonOfficeBuildingSensor implements SensorInterface
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
                'electric_vent' => [
                    'option' => 10,
                    'label' => '환기'
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
                    '1층 계단실' => '2004_1_4',
                ],
                11 => [
                    '신재생 소비량' => '2004_1_2',
                ]
            ],
            '한전 전체 전력' => [
                0 => [
                    '1층 계단실' => '2004_1_1',
                ],
            ],
            '1층 전체 전력' => [
                0 => [
                    '1층 계단실' => '2004_1_4',
                    '1층 사무실' => '2004_1_7',
                ],
            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 계단실' => '2004_2_1',
                ],
            ],
            '3층 전체 전력' => [
                0 => [
                    '3층 계단실' => '2004_3_1'
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
     * @return array
     */
    public function getElectricFloorSensor() : array
    {
        return [
            'all' => [
                'all' => '2004_ALL',
            ],
            '1F' => [
                'all' => '2004_1F',
            ],
            '2F' => [
                'all' => '2004_2F',
            ],
            '3F' => [
                'all' => '2004_3F',
            ],
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
                'name' => '전기'
            ],
            'electric_light' => [
                'name' => '전등'
            ],
            'electric_hotwater' => [
                'name' => '급탕'
            ],
            'electric_cold' => [
                'name' => '냉/난방'
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
            'in' => '2004_1',
            'out' => '2004_1_2'
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
     * 보고서 정보
     *
     * @return array
     */
    public function getPaperInfo() : array
    {
        return [
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
                'diagram' => './diagram/diagram_nedob.html', // 계통도
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
                'electric_cold',
                'electric_vent',
                'electric_water_heater'
            ],
            'distribution' => [
                'electric_light',
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
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