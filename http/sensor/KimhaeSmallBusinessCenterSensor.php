<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * KimhaeHumanCenter 김해 소상공인 물류센터 메뉴별 센서 정의
 */
class KimhaeSmallBusinessCenterSensor implements SensorInterface
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
            ],
            'usage' => [
                'electric_light' => ['option' => 3, 'label' => '조명'],
                'electric_cold' => ['option' => 4, 'label' => '냉/난방'],
                'electric_hotwater' => ['option' => 7, 'label' => '급탕'],
                'electric_heating' => ['option' => 8, 'label' => '난방'],
                'electric_vent' => ['option' => 10, 'label' => '환기'],
                'freeze' => ['option' => 14, 'label' => '냉장/냉동'],
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
                0 => [
                    '한전 전체 전력' => '2012_1_3',
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
                'all' => '2012_ALL',
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
                'name' => '조명',
            ],
            'electric_cold' => [
                'name' => '냉/난방',
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
            'equipment' => [
                'name' => '냉장/냉동',
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
            'in' => '2012_1',
            'out' => '2012_1_2',
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
            '1F'
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
                'diagram' => './diagram/diagram_kimhae_ksbc.html', // 계통도
                'prediction_energy' => './prediction/energy.html', // 예측-에너지원별
                'prediction_solar' => './prediction/solar.html', // 예측-태양광발전
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
                'electric_cold',
                'electric_hotwater',
                'electric_heating',
                'electric_vent',
                'equipment',
            ],
            'distribution' => [
                'electric_light',
                'electric_cold',
                'electric_hotwater',
                'electric_heating',
                'electric_vent',
                'electric_elevator',
                'equipment',
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