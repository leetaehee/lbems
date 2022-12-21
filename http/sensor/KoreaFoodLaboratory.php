<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class KoreaFoodLaboratory 한국식품연구원 메뉴별 센서 정의
 */
class KoreaFoodLaboratory implements SensorInterface
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
                'gas' => ['option' => 1, 'label' => '가스'],
            ],
            'usage' => [
                'electric_light' => ['option' => 3, 'label' => '조명'],
                'electric_cold' => ['option' => 4, 'label' => '냉/난방'],
                'electric_hotwater' => ['option' => 7, 'label' => '급탕'],
                'electric_heating' => ['option' => 8, 'label' => '난방'],
                'electric_vent' => ['option' => 10, 'label' => '환기'],
            ]
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
            '한전 전체 전력' => [
                0 => [
                    '판넬 메인' => '2019_2_1'
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
                'all' => '2019_ALL',
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
            'gas' => [
                'name' => '가스',
            ],
            'electric_light' => [
                'name' => '조명',
            ],
            'electric_cold' => [
                'name' => '냉/난방',
            ],
            'electric_hotwater' => [
                'name' => '급탕',
            ],
            'electric_heating' => [
                'name' => '난방',
            ],
            'electric_vent' => [
                'name' => '환기',
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
            'in' => '2019_1',
            'out' => '2019_3_4'
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
            'B1', '1F', '2F', 'PH'
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
     * 계통도 키 정보
     *
     * @return array
     */
    public function getDiagramKeyInfo() : array
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
        return [];
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