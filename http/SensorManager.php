<?php
namespace Http;

use EMS_Module\SensorInterface;

use Http\Sensor\MudeungMountainSensor;
use Http\Sensor\TaebaekMountainSensor;
use Http\Sensor\SamcheokNurserySensor;
use Http\Sensor\DaejeonOfficeBuildingSensor;
use Http\Sensor\UjeonPrimarySchool;
use Http\Sensor\DadoseaMountainSensor;
use Http\Sensor\BangbaeLivingFacilitySensor;
use Http\Sensor\TestSensor;
use Http\Sensor\SdiSensor;
use Http\Sensor\SeoilPrimarySchool;
use Http\Sensor\KimhaeHumanCenter;
use Http\Sensor\MotiSensor;
use Http\Sensor\KimhaeSmallBusinessCenterSensor;
use Http\Sensor\HandicapJobEducationCenterCenter;
use Http\Sensor\SaemaeulCenterTrainingInstitute;
use Http\Sensor\BukhanMountainSensor;
use Http\Sensor\MudeungMountainWonhyoSensor;
use Http\Sensor\KoreaFoodLaboratory;

/**
 * Class SensorManager
 */
class SensorManager
{
    /**
     * SensorManager constructor.
     */
    public function __construct()
    {
    }

    /**
     * SensorManager destructor.
     */
    public function __destruct()
    {
    }

    /**
     * 건물별 센서 객체 조회
     *
     * @param string $complexCodePk
     *
     * @return SensorInterface $obj
     */
    public function getSensorObject(string $complexCodePk) : SensorInterface
    {
        $obj = null;

        switch ($complexCodePk) {
            case '2001':
                // 무등산
                $obj = new MudeungMountainSensor();
                break;
            case '2002':
                // 태백산
                $obj = new TaebaekMountainSensor();
                break;
            case '2003':
                // 삼척어린이집 
                $obj = new SamcheokNurserySensor();
                break;
            case '2004':
                // 대전네드사옥
                $obj = new DaejeonOfficeBuildingSensor();
                break;
            case '2005':
                // 다도해해상국립공원
                $obj = new DadoseaMountainSensor();
                break;
            case '2006':
                // 전주 우전초등학교
                $obj = new UjeonPrimarySchool();
                break;
            case '2007':
                // 방배동근린생활시설
                $obj = new BangbaeLivingFacilitySensor();
                break;
            case '2008':
                // 전주 서일초등학교
                $obj = new SeoilPrimarySchool();
                break;
            case '2010' :
                // 김해시 행정복제선터
                $obj = new KimhaeHumanCenter();
                break;
            case '2011' :
                // 국방전직교육원
                $obj = new MotiSensor();
                break;
            case '2012' :
                // 김해시 소상공인 물류센터
                $obj = new KimhaeSmallBusinessCenterSensor();
                break;
            case '2013' :
                // 장애인 내일키움 직업교육센터
                $obj = new HandicapJobEducationCenterCenter();
                break;
            case '2014' :
                // 새마을 중앙 연수원
                $obj = new SaemaeulCenterTrainingInstitute();
                break;
            case '2017' :
                // 북한산 국립공원 청사
                $obj = new BukhanMountainSensor();
                break;
            case '2018' :
                // 무등산 국립공원 원효분소
                $obj = new MudeungMountainWonhyoSensor();
                break;
            case '2019' :
                // 한국식품연구원
                $obj = new KoreaFoodLaboratory();
                break;
            case '3001':
                // fems - 공장
                $obj = new SdiSensor();
                break;
            case '9999':
                // 테스트 건물
                $obj = new TestSensor();
                break;
            default:
                break;
        }

        return $obj;
    }
}