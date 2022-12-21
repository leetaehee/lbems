<?php
namespace Http\Command;

use EMS_Module\Usage;
use EMS_Module\Config;
use Module\FileCache;

/**
 * Class CacheRawDataTest 캐시로 미터 데이터 조회 테스트
 */
class CacheRawDataTest extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        /*
         대상
         - getEnergyData
         - getEnergyDataByRange
         - getUsageSumData
         입력
         for (주기 - 0,1,2) {
           for (단지코드 - 2001, 2002, 2003) {
              for (에너지원 - 전기, 전등 ...) {
                ...
              }
           }
         }
         출력
         [
            0 => [
                '2001' => [
                    'electric' => [
                    ],
                    'electric_light' => [
                    ]
                ],
                '2002' => [
                    'electric' => [
                    ],
                    'electric_light' => [
                    ],
                    'electric_cold' => [
                    ],
                ],
            ],
            1 => [
                ..
            ],
            2 = [
                ..
            ]
         ];
       */

        $saveData = [];

        $usage = new Usage();

        $date = date('Ymd');
        $dateTypes = [0, 1, 2];

        $sensorTypes = Config::SENSOR_TYPES;

        /*
        // 단지조회
        $rComplexQ = $this->emsQuery->getQuerySelectComplex();
        $complexData = $this->query($rComplexQ);

        for ($i = 0; $i < count($dateTypes); $i++) {

            $dateType = $dateTypes[$i];

            if ($dateType !== 1) {
                // 기간별 검색은 월만 할 것
                //continue;
            }

            $newDate = $usage->getDateByOption($date, $dateType);

            // 과거
            //$newDate = $usage->getLastDate($newDate, $dateType);

            for ($j = 0; $j < count($complexData); $j++) {

                $complexCodePk = $complexData[$j]['complex_code_pk'];

                if (in_array($complexCodePk, ['2009', '2015', '2016', '2018', '2020', '2021', '2023', '2024', '3003', '3004']) === true) {
                    continue;
                }

                for ($k = 0; $k < count($sensorTypes); $k++) {
                    $sensorType = $sensorTypes[$k];
                    $option = $k;

                    if ($option !== 0) {
                        // fems
                        //continue;
                    }

                    $addOptions = [
                        'energy_name' => $sensorType,
                        'is_cache' => false,
                    ];

                    $d = $usage->getEnergyData($this, $complexCodePk, $option, $dateType, $newDate, $addOptions);
                    //$d = $usage->getEnergyDataByRange($this, $complexCodePk, $option, $dateType, '20221101', '20221130', $addOptions);
                    //$d = $usage->getUsageSumData($this, $complexCodePk, $option, $dateType, $newDate, $addOptions);

                    $saveData[$dateType][$complexCodePk][$sensorType] = $d;
                }
            }
        }

        print_r($saveData);
        */

        // 캐시추가 - curren : 현재, last : 과거
        //$cache = new FileCache('getEnergyData', 'usage/current');
        //$cache = new FileCache('getEnergyDataByRange', 'usage/current');
        //$cache = new FileCache('getUsageSumData', 'usage/last');

        //$cache->cacheFileWrite($saveData);

        // 캐시 조회 (객체 방식)
        /*
        $dateType = 0;

        $newDate = $usage->getDateByOption($date, $dateType);

        $addOptions = [
            'energy_name' => 'electric_hotwater',
        ];

        $d = $usage->getEnergyData($this, '2002', 7, $dateType, $newDate, $addOptions);
        */

        //print_r($d);

        $this->data = [];
        return true;

    }
}