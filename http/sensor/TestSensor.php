<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class TestSensor 테스트 메뉴별 센서 정의
 */
class TestSensor implements SensorInterface
{
    /**
     * 전열 미터 데이터 생성을 위한 센서 정의
     *
     * @return array|mixed
     */
    public function getElectricElechotSensor() : array
    {
        return [
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
                'electric' => [
                    'option' => 0,
                    'label' => '전기'
                ],
            ],
            'usage' => [
                'electric_light' => [
                    'option' => 3,
                    'label' => '전등'
                ],
                'electric_elechot' => [
                    'option' => 5,
                    'label' => '전열'
                ],
                'electric_hotwater' => [
                    'option' => 7,
                    'label' => '급탕'
                ],
                'electric_cold' => [
                    'option' => 4,
                    'label' => '냉/난방'
                ],
                'electric_vent' => [
                    'option' => 10,
                    'label' => '환기'
                ],
                'electric_elevator' => [
                    'option' => 6,
                    'label' => '운송(승강기)'
                ],
                'power_train' => [
                    'option' => 0,
                    'label' => '동력'
                ],
            ],
            'facility' => [
                'electric_water_heater' => [
                    'option' => 7,
                    'label' => '전기온수기'
                ],
                'feed_pump' => [
                    'option' => 12,
                    'label' => '급수펌프'
                ],
                'sump_pump' => [
                    'option' => 12,
                    'label' => '배수펌프'
                ],
                'circulating_pump' => [
                    'option' => 7,
                    'label' => '순환펌프'
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