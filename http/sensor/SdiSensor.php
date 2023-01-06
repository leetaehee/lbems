<?php
namespace Http\Sensor;

use EMS_Module\SensorInterface;

/**
 * Class SdiSensor 에스디아이 메뉴별 센서 정의
 */
class SdiSensor implements SensorInterface
{
    /**
     * 에너지원, 용도별, 설비별 분류
     *
     * @return array
     */
    public function getEnergyPartData(): array
    {
        return [
        ];
    }

    /**
     * 전기 층, 룸별 센서 조회
     *
     * @return array
     */
    public function getElectricSensor(): array
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
    public function getElectricFloorSensor(): array
    {
        return [
        ];
    }

    /**
     * 공장 센서에 대한 키 정의
     *
     * @return string[]
     */
    public function getFactorySensorAboutStatusName() : array
    {
        return [
            '3001_1_1' => 'hybrid_main',
            '3001_1_2' => 'hybrid_heater',
            '3001_2_1' => 'hydraulic_heater',
            '3001_2_2' => 'hydraulic_electric_motor1',
            '3001_2_3' => 'hydraulic_electric_motor2',
            '3001_2_4' => 'hydraulic_electric_motor3',
        ];
    }

    /**
     * 장애알람 에너지원 조회
     *
     * @return array
     */
    public function getHindranceAlarmSensor(): array
    {
        return [
            'hybrid_main' => [
                'name' => '하이브리드-메인',
                'sensor_sn' => '3001_1_1',
            ],
            'hybrid_heater' => [
                'name' => '하이브리드-히터',
                'sensor_sn' => '3001_1_2',
            ],
            'hydraulic_heater' => [
                'name' => '유압식-히터',
                'sensor_sn' => '3001_2_1',
            ],
            'hydraulic_electric_motor1' => [
                'name' => '유압식-전동기1',
                'sensor_sn' => '3001_2_2',
            ],
            'hydraulic_electric_motor2' => [
                'name' => '유압식-전동기2',
                'sensor_sn' => '3001_2_3',
            ],
            'hydraulic_electric_motor3' => [
                'name' => '유압식-전동기3',
                'sensor_sn' => '3001_2_4',
            ],
        ];
    }

    /**
     * 업체별 커스텀 설비 목록
     *
     * @return array
     */
    public function getSpecialSensorKeyName(): array
    {
        return [
            'hybrid_main' => [
                '1F' => ['3001_1_1'],
            ],
            'hybrid_heater' => [
                '1F' => ['3001_1_2'],
            ],
            'hydraulic_heater' => [
                '1F' => ['3001_2_1'],
            ],
            'hydraulic_electric_motor1' => [
                '1F' => ['3001_2_2'],
            ],
            'hydraulic_electric_motor2' => [
                '1F' => ['3001_2_3'],
            ],
            'hydraulic_electric_motor3' => [
                '1F' => ['3001_2_4'],
            ],
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
            'in' => '',
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
        return [];
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