<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class SeoilPrimarySchool 전주 서일초등학교 메뉴별 센서 정의
 */
class SeoilPrimarySchool implements SensorInterface
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
                'gas' => [
                    'option' => 1,
                    'label' => '가스'
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
                    'label' => '온수'
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
            'gas' => [
                'name' => '가스',
            ],
            'electric_light' => [
                'name' => '조명',
            ],
            'electric_cold' => [
                'name' => '냉난방',
            ],
            'electric_hotwater' => [
                'name' => '온수',
            ],
            'solar' => [
                'name' => '태양광',
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
            'in' => '20081001',
            'out' => ''
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
                'gas',
                'electric_light',
                'electric_cold',
                'electric_hotwater'
            ],
            'distribution' => [
                'electric_light',
                'electric_cold',
                'electric_hotwater',
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