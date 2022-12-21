<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class MudeungMountainWonhyoSensor 무등산 원효분소 메뉴별 센서 정의
 */
class MudeungMountainWonhyoSensor implements SensorInterface
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
            ],
            'usage' => [
            ],
            'facility' => [
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
        ];
    }

    /**
     * 장애알람 에너지원 조회
     * @return mixed
     */
    public function getHindranceAlarmSensor() : array
    {
        return [
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
     * @return mixed
     */
    public function getSolarSensor() : array
    {
        return [
            'in' => '',
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
            ],
            'distribution' => [
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