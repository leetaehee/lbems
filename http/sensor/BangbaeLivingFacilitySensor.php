<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class BangbaeLivingFacilitySensor 방배동 근린생활시설 메뉴별 센서 정의
 */
class BangbaeLivingFacilitySensor implements SensorInterface
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
                    '1층 EPS실' => '2007_3_1',
                ],
            ],
            '한전 전체 전력' => [
                0 => [
                    '1층 EPS실' => '2007_3_1',
                ],
                11 => [
                    '신재생 소비량' => '2007_3_2',
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
                'all' => '2007_ALL',
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
            'electric_cold' => [
                'name' => '냉/난방'
            ],
            'electric_hotwater' => [
                'name' => '급탕'
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
            'in' => '2007_1',
            'out' => '2007_3_2'
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
            '4F',
            '5F',
            '6F',
            '7F'
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
                'diagram' => './diagram/diagram_bangbae.html', // 계통도
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
                'electric_hotwater',
                'electric_cold',
                'electric_vent',
            ],
            'distribution' => [
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
            '2007_ALL' => '2007_3_1'
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