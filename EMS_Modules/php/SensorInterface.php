<?php
namespace EMS_Module;

/**
 * Interface SensorInterface
 */
interface SensorInterface
{
    /**
     * 에너지원, 용도별, 설비별 분류
     *
     * @return array
     */
    public function getEnergyPartData() : array;

    /**
     * 전기 층, 룸별 센서 조회
     *
     * @return array
     */
    public function getElectricSensor() : array;

    /**
     * 전기 동별 센서 조회
     *
     * @return array
     */
    public function getElectricDongSensor() : array;

    /**
     * 전기 층별 센서 조회
     *
     * @return array
     */
    public function getElectricFloorSensor() : array;

    /**
     * 장애알람 에너지원 조회
     *
     * @return array
     */
    public function getHindranceAlarmSensor() : array;

    /**
     * 업체별 커스텀 설비 목록
     *
     * @return array
     */
    public function getSpecialSensorKeyName() : array;

    /**
     * 태양광 센서 정보
     *
     * @return array
     */
    public function getSolarSensor() : array;

    /**
     * 층 정보
     *
     * @return array
     */
    public function getFloorInfo() : array;

    /**
     * 모바일 메뉴 조회
     *
     * @return array
     */
    public function getMobileMenuInfo() : array;

    /**
     * 제어 정보
     *
     * @return mixed
     */
    public function getControlDeviceInfo() : array;

    /**
     * 계통도 키 정보
     *
     * @return array
     */
    public function getDiagramKeyInfo() : array;

    /**
     * 센서 별칭 정의
     *
     * @return array
     */
    public function getSensorAliasInfo() : array;

    /**
     * 에너지 단위 커스텀 하기
     *
     * @return mixed
     */
    public function getCustomUnit() : array;
}