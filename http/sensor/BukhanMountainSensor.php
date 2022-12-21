<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class BukhanMountainSensor 북한산 메뉴별 센서 정의
 */
class BukhanMountainSensor implements SensorInterface
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
                'electric_elevator' => ['option' => 6, 'label' => '운송'],
                'electric_car' => ['option' => 0, 'label' => '전기차 충전소'],
                'geothermal' => ['option' => 14, 'label' => '지열'],
            ],
            'facility' => [
                'electric_water_heater' => ['option' => 7, 'label' => '전기온수기'],
                'electric_floor_heating' => ['option' => 8, 'label' => '바닥난방'],
                'circulating_pump' => ['option' => 7, 'label' => '순환펌프'],
                'electric_ehp' => ['option' => 4, 'label' => 'EHP'],
            ],
        ];
    }

    /**
     * 전기 층, 룸별 센서 조회 (북한산 예외 - 태양광 소비량 계산 포함)
     *
     * @return array|mixed
     */
    public function getElectricSensor() : array
    {
        return [
            '한전 전체 전력' => [
                0 => [
                    '지하1층 전기실' => '2017_1_1',
                ]
            ],
            '지하1층 전체 전력' => [
                0 => [
                    '지하1층 화장실복도1' => '2017_3_1',
                    '지하1층 화장실복도2' => '2017_4_1',
                ]
            ],
            '1층 전체 전력' => [
                0 => [
                    '1층 화장실복도' => '2017_5_1',
                    '1층 EPS실' => '2017_6_1',
                    '1층 주방휴게실' => '2017_7_1',
                ],
            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 복도' => '2017_8_1',
                    '2층 EPS실' => '2017_9_1',
                ],
            ],
            '옥상 전체 전력' => [
                0 => [
                    '지붕층 옥외' => '2017_10_1',
                ],
            ],
            '태양광 소비량' => [
                11 => [
                    '신재생1' => '2017_1_3',
                    '신재생2' => '2017_1_6',
                ],
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
                'all' => '2017_ALL',
            ],
            'B1' => [
                'all' => '2017_B1',
            ],
            '1F' => [
                'all' => '2017_1F',
            ],
            '2F' => [
                'all' => '2017_2F',
            ],
            'PH' => [
                'all' => '2017_PH',
            ]
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
            'geothermal' => [
                'name' => '지열'
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
            'electric_car' => [
                'B1' => ['2017_1_4']
            ],
            'electric_water_heater' => [
                'B1' => ['2017_1_5']
            ],
            'circulating_pump' => [
                'B1' => [
                    '2017_2_9',
                    '2017_2_10'
                ]
            ],
            'electric_floor_heating' => [
                'B1' => [
                    '2017_4_4',
                    '2017_4_7'
                ],
                '1F' => [
                    '2017_5_2',
                    '2017_5_4',
                    '2017_6_3',
                    '2017_7_4'
                ],
                '2F' => ['2017_9_4']
            ],
            'electric_ehp' => [
                'PH' => [
                    '2017_10_2',
                    '2017_10_3',
                    '2017_10_4'
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
            'in' => '2017_1',
            'out' => '2017_OUT_1'
        ];
    }

    /**
     * 층 정보
     *
     * @return mixed
     */
    public function getFloorInfo() : array
    {
        return ['B1', '1F', '2F', 'PH'];
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
                'diagram' => './diagram/diagram_bhmt.html', // 계통도
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
            'B1' => [
                '시험장비창고' => 1,
                '대기실-2' => 2,
                '소회의실-2' => 3,
                '홍보/전시' => 4,
                '숲속도서관(좌)' => 5,
                '숲속도서관(우)' => 6,
                '로비' => 7,
                '대회의실(좌)' => 8,
                '대회의실(우)' => 9,
                '탈의실(여)' => 'A',
                '탈의실(남)' => 'B',
                '체력단련실(남)' => 'C',
                '체력단련실(여)' => 'D',
            ],
            '1F' => [
                '영선실' => 'E',
                '실사출력실' => 'F',
                '직원휴게실(남)' => 10,
                '직원휴게실(여)' => 11,
                '대기실-1' => 12,
                '해설사실' => 13,
                '휴게' => 14,
                '복도' => 15,
                '포켓쉼터' => 16,
                '사무실-2(좌)' => 17,
                '사무실-2(우)' => 18,
                '숙직실' => 19,
                '식자재창고' => '1A',
                '휴게실' => '1B',
                '직원식당(좌)' => '1C',
                '직원식당(우)' => '1D',
            ],
            '2F' => [
                '탐방시설(좌)' => 20,
                '탐방시설(우)' => 21,
                '자원보전과(좌)' => 22,
                '자원보전과(우)' => 23,
                '행정과(좌)' => 24,
                '행정과(우)' => 25,
                '포켓쉼터' => 26,
                '복도(좌)' => 27,
                '복도(우)' => 28,
                '소회의실-1' => 29,
                '소장실' => '2A',
                '문서고(좌)' => '2B',
                '문서고(우)' => '2C',
                '방재과' => '1F',
                '재난상황실' => '1E',
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