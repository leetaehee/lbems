<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class MudeungMountainSensor 무등산 메뉴별 센서 정의
 */
class MudeungMountainSensor implements SensorInterface
{
    /**
     * 전열 미터 데이터 생성을 위한 센서 정의
     *
     * @return array|mixed
     */
    public function getElectricElechotSensor() : array
    {
        return [
            '1층 전기실' => [
                0 => [
                    '1층 전체 전력1' => '985DAD60E5ED_1',
                    '1층 전체 전력2' => '985DAD60D991_1',
                ],
                3 => [
                    '전등1' => '985DAD60E5ED_2',
                    '전등2' => '985DAD60E5ED_3',
                    '전등3' => '985DAD60E5ED_4',
                    '전등4' => '985DAD60E5ED_5',
                ],
            ],
            '1층 EPS실' => [
                0 => [
                    '1층 전체 전력2' => '985DAD60D991_1',
                ],
                3 => [
                    '전등1' => '985DAD60D991_2',
                    '전등2' => '985DAD60D991_3',
                    '전등3' => '985DAD60D991_4',
                    '전등4' => '985DAD60D991_5',
                    '전등5' => '985DAD60D991_6',
                    '전등6' => '985DAD60D991_7',
                    '전등7' => '985DAD60D991_8',
                ],
            ],
            '2층 EPS실' => [
                0 => [
                    '2층 전체 전력1' => '985DAD60D1B1_1',
                ],
                3 => [
                    '전등1' => '985DAD60D1B1_2',
                    '전등2' => '985DAD60D1B1_3',
                    '전등3' => '985DAD60D1B1_4',
                    '전등4' => '985DAD60D1B1_5',
                    '전등5' => '985DAD60D1B1_6',
                    '전등6' => '985DAD60D1B1_7',
                ],
                8 => [
                    '바닥판넬' => '985DAD60BBB6_5',
                ],
            ],
            '3층 EPS실' => [
                0 => [
                    '3층 전체 전력1' => '985DAD60BBB0_1',
                    '3층 전체 전력2' => '985DAD60C116_1',
                ],
                3 => [
                    '전등1' => '985DAD60BBB0_3',
                    '전등2' => '985DAD60BBB0_4',
                    '전등3' => '985DAD60BBB0_5',
                    '전등4' => '985DAD60BBB0_6',
                ],
                10 => [
                    '환기1' => '985DAD60BBB0_2',
                ]
            ],
            '3층 식당' => [
                0 => [
                    '3층 전체 전력2' => '985DAD60C116_1',
                ],
                3 => [
                    '전등1' => '985DAD60C116_5',
                ],
                8 => [
                    '난방1' => '985DAD60C116_4',
                ],
                10 => [
                    '환기1' => '985DAD60C116_2',
                    '환기2' => '985DAD60C116_3',
                ],
            ],
        ];
    }

    /**
     * 환기 미터 데이터 생성을 위한 센서 정의
     *
     * @return array|mixed
     */
    public function getElectricVentSensor() : array
    {
        return [
            '옥탑층' => [
                0 => [
                    '전기기기' => '985DAD60CBEC_1',
                ],
                4 => [
                    '냉난방1' => '985DAD60CBEC_2',
                    '냉난방2' => '985DAD60CBEC_3',
                    '냉난방3' => '985DAD60CBEC_4',
                    '냉난방4' => '985DAD60CBEC_5',
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
                'electric_heating' => ['option' => 8, 'label' => '난방'],
                'electric_cold' => ['option' => 4, 'label' => '냉난방'],
                'electric_vent' => ['option' => 10, 'label' => '환기'],
                'electric_elevator' => ['option' => 6, 'label' => '운송(승강기)'],
                'communication_power' => ['option' => 0, 'label' => '통신전원']
            ],
            'facility' => [
                'electric_water_heater' => ['option' => 7, 'label' => '전기온수기'],
                'electric_ehp' => ['option' => 4, 'label' => 'EHP'],
                'electric_floor_heating' => ['option' => 8, 'label' => '바닥난방'],
            ],
        ];
    }

    /**
     * 전기 층, 룸별 센서 조회
     *
     * @return array|mixed
     *
     */
    public function getElectricSensor() : array
    {
        return [
            '1층 전체 전력' => [
                /*
                0 => [
                    '건물 전체 전력' => '985DAD60BBB6_1',
                    '2층 전체 전력' => '2001_2F',
                    '3층 전체 전력' => '2001_3F',
                    '옥탑 전체 전력' => '2001_PH',
                ],
                11 => [
                    '신재생' => '985DAD60BBB6_2',
                ],
                */
                0 => [
                    '1층 전체 전력1' => '985DAD60E5ED_1',
                    '통신전원' => '985DAD60BBB6_6',
                ],
                7 => [
                    '전기온수기' => '985DAD60BBB6_3',
                ],

            ],
            '2층 전체 전력' => [
                0 => [
                    '2층 전체 전력1' => '985DAD60D1B1_1',
                ],
                8 => [
                    '바닥판넬' => '985DAD60BBB6_5',
                ],
            ],
            '3층 전체 전력' => [
                0 => [
                    '3층 전체 전력1' => '985DAD60BBB0_1',
                ],
                6 => [
                    '운송' => '985DAD60BBB6_4'
                ],

            ],
            '옥탑 전체 전력' => [
                0 => [
                    '전기기기' => '985DAD60CBEC_1',
                ],
            ],
            '1층 전기실' => [
                0 => [
                    '1층 전체 전력1' => '985DAD60E5ED_1',
                    '1층 전체 전력2' => '985DAD60D991_1',
                ],
            ],
            '3층 EPS실' => [
                0 => [
                    '3층 전체 전력1' => '985DAD60BBB0_1',
                    '3층 전체 전력2' => '985DAD60C116_1',
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
     * @return array|mixed
     */
    public function getElectricFloorSensor() : array
    {
        return [
            'all' => [
                'all' => '985DAD60BBB6_1',
            ],
            '1F' => [
                'all' => '2001_1F',
            ],
            '2F' => [
                'all' => '2001_2F',
            ],
            '3F' => [
                'all' => '2001_3F',
            ],
            'PH' => [
                'all' => '2001_PH',
            ],
        ];
    }

    /**
     * 통신전원 센서 조회
     *
     * @return mixed|void
     */
    public function getCommunicationSensor() : array
    {
        return [
            '통신전원' => [
                7 => [
                    '전기온수기' => '985DAD60BBB6_3',
                ],
                6 => [
                    '승강1' => '985DAD60BBB6_4',
                ],
                8 => [
                    '바닥판넬' => '985DAD60BBB6_5',
                ],
                /*
                    4 => [
                        '냉난방1' => '985DAD60CBEC_2'
                    ],
                */
                0 => [
                    '건물 전체 전력' => '985DAD60BBB6_1',
                    '1층 전체 전력1' => '985DAD60E5ED_1',
                    '2층 전체 전력1' => '985DAD60D1B1_1',
                    '3층 전체 전력1' => '985DAD60BBB0_1',
                    '옥탑 전체 전력' => '985DAD60CBEC_1'
                ],
                11 => [
                    '신재생' => '985DAD60BBB6_2',
                ]
            ],
        ];
    }

    /**
     * 장애알람 에너지원 조회
     * @return mixed
     */
    public function getHindranceAlarmSensor() : array
    {
        return [
            'electric' => [
                'name' => '전기'
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
            'electric_heating' => [
                'name' => '난방',
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
            'communication_power' => [
                '1F' => [
                    '985DAD60BBB6_6'
                ],
            ],
            'electric_water_heater' => [
            ],
            'electric_ehp' => [
            ],
            'electric_floor_heating' => [
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
            'in' => '2001_1',
            'out' => '985DAD60BBB6_2'
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
            '3F',
            'PH'
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
            'web_keys' => [
                'checkbox_electric' => ['option' => 0, 'key' => 'electric'], // 전체전기
                'checkbox_1f_electric_light' => ['option' => 3, 'key' => 'electric_light'], // 1층 조명
                'checkbox_1f_electric_elechot' => ['option' => 5, 'key' => 'electric_elechot'], // 1층 전열
                'checkbox_1f_electric_water_heater' => ['option' => 7, 'key' => 'electric_water_heater'], // 1층 전기온수기
                'checkbox_2f_electric_light' => ['option' => 3, 'key' => 'electric_light'], // 2층 조명
                'checkbox_2f_electric_elechot' => ['option' => 5, 'key' => 'electric_elechot'], // 2층 전열
                'checkbox_2f_electric_heating' => ['option' => 8, 'key' => 'electric_heating'], // 2층 바닥난방
                'checkbox_3f_electric_light' => ['option' => 3, 'key' => 'electric_light'], // 3층 조명
                'checkbox_3f_electric_elechot' => ['option' => 5, 'key' => 'electric_elechot'], // 3층 전열
                'checkbox_3f_electric_vent' => ['option' => 10, 'key' => 'electric_vent'], // 3층 환기
                'checkbox_3f_electric_heating' => ['option' => 8, 'key' => 'electric_heating'], // 3층 난방
                'checkbox_3f_electric_elavator' => ['option' => 6, 'key' => 'electric_elevator'], // 3층 운송(승강기)
                'checkbox_ph_electric_elechot' => ['option' => 5, 'key' => 'electric_elechot'], // 옥탑 전열
                'checkbox_ph_electric_cold' => ['option' => 4, 'key' => 'electric_cold'], // 옥탑 냉난방
                'checkbox_ph_electric_ehp' => ['option' => 4, 'key' => 'electric_ehp'], // 옥탑 EHP
                'checkbox_ph_electric_vent' => ['option' => 10, 'key' => 'electric_vent'], // 옥탑 환기
            ],
            'excel_keys' => [
                '1f_electric_light' => ['floor' => '1F', 'option' => '3', 'floor_group' => '1층', 'energy_group' => '전기', 'energy_name' => '조명', 'energy_type' => 'electric_light'], // 1f-조명
                '1f_electric_elechot' => ['floor' => '1F', 'option' => '5', 'floor_group' => '1층', 'energy_group' => '전기', 'energy_name' => '전열', 'energy_type' => 'electric_elechot'], // 1f-전열
                '1f_electric_water_heater' => ['floor' => '1F', 'option' => '7', 'floor_group' => '1층', 'energy_group' => '전기', 'energy_name' => '전기온수기', 'energy_type' => 'electric_water_heater'], // 1f-전기온수기
                '2f_electric_light' => ['floor' => '2F', 'option' => '3', 'floor_group' => '2층', 'energy_group' => '전기', 'energy_name' => '조명', 'energy_type' => 'electric_light'], // 2f-조명
                '2f_electric_elechot' => ['floor' => '2F', 'option' => '5', 'floor_group' => '2층', 'energy_group' => '전기', 'energy_name' => '전열', 'energy_type' => 'electric_elechot'], // 2f-전열
                '2f_electric_heating' => ['floor' => '2F', 'option' => '8', 'floor_group' => '2층', 'energy_group' => '전기', 'energy_name' => '난방', 'energy_type' => 'electric_heating'
                ], // 2f-바닥난방
                '3f_electric_light' => ['floor' => '3F', 'option' => '3', 'floor_group' => '3층', 'energy_group' => '전기', 'energy_name' => '조명', 'energy_type' => 'electric_light'], // 3f-조명
                '3f_electric_elechot' => ['floor' => '3F', 'option' => '5', 'floor_group' => '3층', 'energy_group' => '전기', 'energy_name' => '전열', 'energy_type' => 'electric_elechot'], // 3f-전열
                '3f_electric_vent' => ['floor' => '3F', 'option' => '10', 'floor_group' => '3층', 'energy_group' => '전기', 'energy_name' => '환기', 'energy_type' => 'electric_vent'], // 3f-환기
                '3f_electric_heating' => ['floor' => '3F', 'option' => '8', 'floor_group' => '3층', 'energy_group' => '전기', 'energy_name' => '난방', 'energy_type' => 'electric_heating'], // 3f-난방
                '3f_electric_eleavator' => ['floor' => '3F', 'option' => '6', 'floor_group' => '3층', 'energy_group' => '전기', 'energy_name' => '운송(승강기)', 'energy_type' => 'electric_eleavator'], // 3f-운송(승강기)
                'ph_electric_cold' => ['floor' => 'PH', 'option' => '4', 'floor_group' => '옥탑', 'energy_group' => '전기', 'energy_name' => '냉난방', 'energy_type' => 'electric_cold'], // 옥탑-냉난방
                'ph_electric_ehp' => ['floor' => 'PH', 'option' => '4', 'floor_group' => '옥탑', 'energy_group' => '전기', 'energy_name' => 'EHP', 'energy_type' => 'electric_ehp'], // 옥탑-EHP
                'ph_electric_vent' => ['floor' => 'PH', 'option' => '10', 'floor_group' => '옥탑', 'energy_group' => '전기', 'energy_name' => '환기', 'energy_type' => 'electric_vent'], // 옥탑-환기
            ],
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
                'diagram' => './diagram/diagram.html', // 계통도
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
                '로비(대회의실앞)' => 25,
                '로비(재난상황실옆)' => 26,
                '대회의실(준비실옆)' => 22,
                '대회의실(출입구옆)' => 24,
                '대회의실(조정실옆)' => 21,
                '대회의실(무대옆)' => 23,
                '재난상황실' => 27,
                '민원실' => 29,
                '실사출력실' => 31,
                '중앙출입구' => 28,
                '복도(민원실앞)' => 30,
                '해설사실(통신실앞)' => 36,
                '해설사실(중앙)' => 35,
                '해설사실(안쪽)' => 34,
                '영선실' => 32,
                '통신실' => 33,
                '당직실' => 37,
            ],
            '2F' => [
                '체력단련실' => 11,
                '자료실' => 13,
                '복도(승강기앞)' => 16,
                '남자탈의실' => 12,
                '전실(남자탈의실)' => 14,
                '전실(여자탈의실)' => 15,
                '사무실(출입구옆)' => 17,
                '사무실(탕비실옆)' => 18,
                '사무실(11번뒤)' => 19,
                '사무실(12번뒤)' => 20,
            ],
            '3F' => [
                '영양사실' => 0,
                '식당(배식대앞)' => 1,
                '식당(출입구)' => 2,
                '복도(식당앞)' => 3,
                '복도(승강기앞)' => 4,
                '복도(소회의실앞)' => 7,
                '소회의실(앞)' => 5,
                '소회의실(뒤)' => 6,
                '문서고' => 8,
                '전실(소장실)' => 10,
                '소장실' => 9
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
                'electric_elechot',
                'electric_hotwater',
                'electric_heating',
                'electric_cold',
                'electric_vent',
                'electric_elevator'
            ],
            'distribution' => [
                'electric_light',
                'electric_elechot',
                'electric_hotwater',
                'electric_heating',
                'electric_cold',
                'electric_vent',
                'electric_elevator'
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