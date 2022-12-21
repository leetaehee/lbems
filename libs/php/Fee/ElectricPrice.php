<?php
/**
 * 전력요금 계산
 */
class ElectricPrice {

    private $dbController;

    //------------------------------------
    //Constructor
    //------------------------------------
    function __construct(){
        $this->dbController = new ElectricMariaDB();
    }

    //------------------------------------
    //Destructor
    //------------------------------------
    function __destruct() {
        if(!isset($this->dbController))
            return;
    }

    /**
     * 주택용 전기 사용료
     * 주택용 / 일반용 구분값으로 기본 요금가격 책정
     * electicType -> H:주택용, N:일반용, S:산업용
     */
    function priceInfo($params) {
        $electicType = $params['electricType'];

        $params['startDate'] = date("Y-m-d", strtotime($params['startDate']));
        if($params['endDate'] == "") {
            $params['endDate'] = date("Y-m-d", strtotime("+1 month"));
        }

        $price = $this->getPriceValue($params);//요금 계산

        $price = $this->cutWonVal($price);//원 미만 절사
        return $price;
    }

    /**
     * 역률 계산. 역률은 계약전력이 20kW 이상인 일반용/산업용 일 경우만 계산
     * 저압일 경우 진상 역률은 계산하지 않음
     */
    function getPowerFector($params) {
        $contractValue = $params['contractUseVal'];// 계약전력
        $laggingPF = $params['laggingPowerFactor'];//지상역률
        $leadingPF = $params['leadingPowerFactor'];//진상역률
        $voltType = $params['voltType'];//전력 구분
        $defaultPrice = $params['defaultPrice'];//기본요금

        $resultPrice1 = 0;//지상 역률 조정 값
        $resultPrice2 = 0;//진상 역률 조정 값

        if($laggingPF != "" && $contractValue >= 20) {//지상역률이 있으며, 계약전력이 20Kw이상일 경우만 역률 계산
            if($laggingPF < 90) {//지상 역률이 90%미만일 경우 0.2%적용
                if($laggingPF < 60) {
                    $laggingPF = 60;
                }
                $resultPrice1 = (90 - $laggingPF) * 0.2 * $defaultPrice;
            }
            if($leadingPF < 95 && $voltType != "L") {//진상 역률이 95% 미만이고 저압이 아닐때 0.2% 적용
                if($leadingPF < 60) {
                    $leadingPF = 60;
                }
                $resultPrice2 = (95 - $leadingPF) * 0.2 * $defaultPrice;
            }

            return floor($resultPrice1 + $resultPrice2);
        } else {
            return 0;
        }
    }

    /**
     * 요금제 적용 계절 설정
     */
    function getSeason($electicType, $month) {
        if($electicType == "H") {
            $sArray = ['07','08'];
            $eArray = ['01','02','03','04','05','06','09','10','11', '12'];
            $wArray = [];
        } else {
            $sArray = ['06','07','08'];
            $eArray = ['03','04','05','09','10'];
            $wArray = ['01','02','11','12'];
        }

        if(in_array($month, $sArray)) {
            $season = "S";
        } else if (in_array($month, $wArray)) {
            $season = "W";
        } else {
            $season = "E";
        }

        return $season;
    }

    function convertParameter($params) {//과거버젼의 라이브러리를 변환

        $electicType = $params['electricType'];
        if($electicType == "H") {//가정용의 경우 전압 설정 필드가 다름
            $voltType = $params['typeGubun'];
        } else {
            $voltType = $params['typeGubun2'];
            $powerDiv = $params['typeGubun'];
        }

        if($voltType == 'low') {
            $voltType = "L";
        } else {
            $voltType = str_replace("high", "H", $voltType);
        }

        $result = [
            'electricType' => $params['electricType'],
            'voltType' => $voltType,
            'powerDiv' => $powerDiv
        ];

        if($params['typeSelect'] != "") {
            $selectType = $params['typeSelect'];
            $result['selectType'] = $selectType;
        }

        //기타 파라미터 유지
        foreach ($params as $key => $value) {
            if(!in_array($key,  ['electricType', 'typeGubun', 'typeGubun2', 'typeSelect'])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * 요금제에 따른 요금 데이터 가져오기
     */
    function getCostInfo($params) {
        /**
         * $result = [
         *  ['month'=>월, 'defaultPrice' => 기본요금, 'unitCost' => 단가, 'season' => 적용계절, 'section' => 구간(누진, 부하), 'days' => 적용일수],
         * ];
         */
        $electricType = $params['electricType'];

        if($electricType == "H") {//가정용의 경우 전압 설정 필드가 다름
            $voltType = $params['voltType'];
        } else {
            $voltType = $params['voltType'];
            $powerDiv = $params['powerDiv'];
            if($voltType != "L") {
                $selectType = $params['typeSelect'];
            }
        }

        $startDate = date("Y-m-d", strtotime($params['startDate']));
        $endDate = $params['endDate'];

        $sDate = new DateTime($startDate);
        $eDate = new DateTime($endDate);

        $totalDays = date_diff($sDate, $eDate);

        if($endDate == '') {//종료일이 설정 되어 있지 않으면 한달 요금
            $endDate = date("Y-m-d", strtotime($startDate . " +1month -1day"));
        }

        /**
         * 시작월과 종료월이 다르다면 월별 분할 계산이 필요함.
         * 반환 되는 내용에 월 및 일수를 포함하여 계산이 되록 함.
         */
        $startMonth = date("m",strtotime($startDate));
        $endMonth = date("m", strtotime($endDate));

        if($startMonth != $endMonth) {
            $sMonthEndDay = date("Y-m-t", strtotime($startDate));//시작월의 말 일
            $eMonthStartDay = date("Y-m-01", strtotime($endDate));//종료일의 첫 일

            $ssDate = new DateTime($startDate);
            $seDate = new DateTime($sMonthEndDay);

            $sDateDiff = date_diff($ssDate, $seDate);

            $esDate = new DateTime($eMonthStartDay);
            $eeDate = new DateTime($endDate);

            $eDateDiff = date_diff($esDate, $eeDate);

            $endQuery[] = ['month' => $startMonth , 'query' => " AND startDate <= '$startDate' AND endDate >= '$sMonthEndDay' ", 'days' => $sDateDiff, 'startDate' => $startDate, 'endDate' => $sMonthEndDay];
            $endQuery[] = ['month' => $endMonth , 'query' => " AND startDate <= '$eMonthStartDay' AND endDate >= '$endDate' ", 'days' => $eDateDiff, 'startDate' => $eMonthStartDay, 'endDate' => $endDate];
        } else {
            $ssDate = new DateTime($startDate);
            $eeDate = new DateTime($endDate);

            $dateDiff = date_diff($ssDate, $eeDate);
            $endQuery[] = ['month' => $startMonth , 'query' => " AND startDate <= '$startDate' AND endDate >= '$endDate'", 'days' => $dateDiff, 'startDate' => $startDate, 'endDate' => $endDate];
        }

        $query  = "SELECT * FROM kepco_price WHERE electricType = '$electricType' AND voltType = '$voltType' ";
        if($powerDiv != "") {
            $query .= " AND powerDiv = '$powerDiv' ";
        }
        if($selectType != "") {//전압 선택이 있을 경우 (저압이 아닐경우)
            $query .= " AND selectType = '$selectType' ";
        }

        foreach($endQuery as $addQuery) {
            $season = $this->getSeason($electricType, $addQuery['month']);
            $seasonQuery = " AND season = '$season'";
            $data = $this->select($query . $addQuery['query'] . $seasonQuery);

            //filelog($query . $addQuery['query'] . $seasonQuery);

            if(is_array($data) && count($data) > 0) {
                foreach($data as $row) {
                    $result[$addQuery['month']][] = [
                        'defaultPrice' => $this->getDefaultPrice($params['electricType'], $row['defaultPrice'], $params['elecDefaultType']),
                        'unitCost' => $row['unitCost'],
                        'season' => $season,
                        'section' => $row['section'],
                        'level' => $row['level'],
                        'days' => $addQuery['days']->days +1,
                        'totaldays' => $totalDays->days +1,
                        'startDate' => $addQuery['startDate'],//계산 시작일
                        'endDate' => $addQuery['endDate']//계산 종료일
                    ];
                }
            }
        }

        //filelog($query . $addQuery['query'] . $seasonQuery);

        return $result;
    }

    /**
     * 기본요금 + 사용요금을 계산 (기타 요금 추가 전)
     * 1. 월이 나뉘는 경우 일할 주의
     * 2.
     */
    function getPriceValue($params) {
        if($params['typeGubun'] != "") {
            $params = $this->convertParameter($params);
        }

        $costInfo = $this->getCostInfo($params);

        $totalDays = 0;
        $days = 0;

        if($params['electricType'] == 'H') {//주택용 요금은 계산이 다름
            $useVal = $params['useValue'];
            foreach($costInfo as $month => $info) {//월별 분할 루프
                $usedInfo = [];
                foreach($info as $cost) {//해당월의 요금 정보 row별
                    $usedInfo[$cost['level']] = [
                        'unitCost' => $cost['unitCost'],
                        'section' => $cost['section'],
                        'defaultPrice' => $cost['defaultPrice']
                    ];
                    if($cost['totaldays'] != $totalDays) {
                        $totalDays = $cost['totaldays'];
                    }
                    if($cost['days'] != $days) {
                        $days = $cost['days'];
                    }
                }

                if($totalDays > 0) {
                    $monthRate = $days / $totalDays;//해당 월의 일 수 : 기본요금, 누진구간, 기후요금 등
                } else {//설정 데이터 오류
                    return false;
                }

                $thisUse = $useVal * $monthRate;//해당월의 사용량

                if($thisUse <= ($usedInfo[1]['section'] * $monthRate)) { //사용량이 1단계에 들어왔을 경우.
                    $dPrice = $usedInfo[1]['defaultPrice'] * $monthRate;
                    $usedPrice = $usedInfo[1]['unitCost'] * $thisUse;
                } else if ($thisUse <= ($usedInfo[2]['section'] * $monthRate) && $thisUse > ($usedInfo[1]['section'] * $monthRate)) {//2단계일 경우
                    $dPrice = $usedInfo[2]['defaultPrice'] * $monthRate;
                    $usedPrice  = $usedInfo[1]['unitCost'] * $usedInfo[1]['section'] * $monthRate;
                    $usedPrice += $usedInfo[2]['unitCost'] * ($thisUse - ($usedInfo[1]['section'] * $monthRate));
                } else {//3단계일 경우
                    $dPrice = $usedInfo[3]['defaultPrice'] * $monthRate;
                    $usedPrice  = $usedInfo[1]['unitCost'] * $usedInfo[1]['section'] * $monthRate;
                    $usedPrice += $usedInfo[2]['unitCost'] * ($usedInfo[2]['section']-$usedInfo[1]['section']) * $monthRate;

                    //슈퍼사용자 - 경우가 많지 않으므로 하드코딩 -> 추후 변동될 경우 체계화
                    if(
                        in_Array($month, ['01', '02', '07', '08', '12']) &&
                        $thisUse > (1000 * $monthRate)
                    ) {
                        $usedPrice += $usedInfo[3]['unitCost'] * (1000 - $usedInfo[2]['section']) * $monthRate;//3단계 요금
                        $usedPrice += ($params['voltType'] == 'H')?569.6:704.5 * ($thisUse - (1000 * $monthRate));//4단계 요금
                    } else {
                        $usedPrice += $usedInfo[3]['unitCost'] * ($thisUse - ($usedInfo[2]['section'] * $monthRate));//3단계 요금
                    }
                }

                $defaultPrice[$month] = round($dPrice);
                $usePrice[$month] = round($usedPrice);
                $etcParams = [
                    'startDate' => $cost['startDate'],
                    'endDate' => $cost['endDate'],
                    'useValue' => $thisUse,
                    'defaultPrice' => round($dPrice),
                    'usedPrice' => round($usedPrice)
                ];
                $etcPrice[$month] = round($this->getEtcPrice($etcParams));
            }
        } else {//일반, 산업용
            $contractValue = (int)$params['contractUseVal'];//계약 전력
            foreach($costInfo as $month => $info) {//월별 분할
                $season = $this->getSeason($params['electricType'], $month);
                $usePrice[$month] = 0;
                $usedPrice = 0;
                $thisUse = 0;
                $addUse = 0;
                foreach($info as $cost) {//시계별 분할
                    if($season == $cost['season']) {
                        if($cost['totaldays'] != $totalDays) {
                            $totalDays = $cost['totaldays'];
                        }
                        if($cost['days'] != $days) {
                            $days = $cost['days'];
                        }

                        $dPrice = $cost['defaultPrice'] * (int)$contractValue * $days / $totalDays;

                        //filelog($cost['defaultPrice'] . " * (int)$contractValue * $days / $totalDays");

                        switch($cost['level']) {//전력량 요금
                            case 0 : //갑1 의 경우 level이 0
                            default :
                                $useVal = "useValue";
                                break;
                            case 1 : // 그 외의 경우 경부하
                                $useVal = "useMinValue";
                                break;
                            case 2 : // 중부하
                                $useVal = "useMidValue";
                                break;
                            case 3 : // 최대부하
                                $useVal = "useMaxValue";
                                break;
                        }
                        $thisUse = round($params[$useVal] * $days / $totalDays);
                        $usedPrice += $cost['unitCost'] * $thisUse;
                        $addUse += $thisUse;
                        //filelog($month . "월($useVal) / thisUse : " . $thisUse . " * " . $cost['unitCost'] . "(" . ($cost['unitCost'] * $thisUse) . ") = " . $usedPrice);
                    }
                }

                $defaultPrice[$month] = floor($dPrice);
                $usePrice[$month] = floor($usedPrice);
                $etcParams = [
                    'startDate' => $cost['startDate'],
                    'endDate' => $cost['endDate'],
                    'useValue' => round($addUse),
                    'defaultPrice' => round($dPrice),
                    'usedPrice' => round($usedPrice)
                ];
                //filelog("etcParams : " . json_encode($etcParams));
                $etcPrice[$month] = floor($this->getEtcPrice($etcParams));
            }
        }

        //filelog(json_encode($params) . "\n" . json_encode($costInfo) . "\n" . json_encode($etcPrice));
        //filelog(json_encode($params)  . " : " . json_encode($defaultPrice) . " / " . json_encode($usePrice). " / " . json_encode($etcPrice));

        //$etcPrice = $this->getEtcPrice($params);

        //filelog("etcPrice : " . json_encode($etcPrice));

        //var_dump($defaultPrice);

        $price = array_sum($defaultPrice);
        $price += array_sum($usePrice);
        $price += array_sum($etcPrice);


        if(date("t", strtotime($params['startDate'])) <= $totalDays) {//한달 요금 계산 시
            //최저 사용량 미달시 기본 요금 적용
            switch($params['electricType']) {
                case "H" :
                    if($price < 1130) {
                        $price = 1130;
                    }
                    break;
                case "N" :
                    if($price < 6160) {
                        $price = 6160;
                    }
                    break;
                case "S" :
                    if($price < 6160) {
                        $price = 6160;
                    }
                    break;
            }//최저 사용량 기본요금 끝
        }
        return $price;
    }

    /**
     * 기타 요금 : 기후환경요금, 연료비 조정액, 부가가치세, 전력산업기반요금 -> 시작월 무시 종료월 기준으로 계산
     */
    function getEtcPrice($params) {
        $startDate = $params['startDate'];
        $endDate = $params['endDate'];
        $useValue = $params['useValue'];
        $defaultPrice = $params['defaultPrice'];
        $usedPrice = $params['usedPrice'];

        $price = 0;
        $uPrice = 0;
        $tPrice = 0;
        $dPrice = 0;

        $query = "SELECT * FROM kepco_etc_price WHERE endDate >= '$endDate' AND startDate <= '$startDate' ORDER BY `priority` ASC";
        $etcPriceInfo = $this->select($query);// or filelog($query);
        //filelog(json_encode($params));
        //filelog($query);

        $usedPriceSum = $usedPrice;
        $defaultPriceSum = $defaultPrice;

        if(is_array($etcPriceInfo) && count($etcPriceInfo) > 0) {
            foreach($etcPriceInfo as $etcPrice) {
                switch($etcPrice['applyTo']){
                    case "usedValue" :
                        $uPrice += round($useValue * $etcPrice['rate']);
                        $lprice = $uPrice;
                        break;
                    case "totalFee" :
                        $tPrice += round(($usedPriceSum + $defaultPriceSum + $uPrice) * $etcPrice['rate']);
                        $lprice = $tPrice;
                        break;
                    case "defaultPrice" :
                        $dPrice += round($defaultPriceSum * $etcPrice['rate']);
                        $lprice = $tPrice;
                        break;
                }
                //filelog("etcCal : " . $etcPrice['name'] . "/" . $etcPrice['applyTo'] . "/" . $etcPrice['rate'] . "/ $useValue * " . "($tPrice/$uPrice/$dPrice)" . $lprice);
            }
        }

        $price = $uPrice + $tPrice + $dPrice;

        return $price;
    }

    function select($query) {
        $result = [];

        try {
            $result = $this->dbController->querys($query);
        }
        catch(Exception $e) { }

        return $result;
    }

    /** 원 미만 절사 */
    function cutWonVal($val) {
        if($val > 0) {
            $cutVal = floor($val/10)*10;
        } else {
            $cutVal = 0;
        }
        return $cutVal;
    }

    function getDailyElectPrice($params) {//간략히 일 사용 요금을 가져오는 함수
        $date = $params['startDate'];//YYYYMMDD
        $useVal = $params['useVal'];
        $elecType = $params['electricType'];
        //$closingDay = $params['closingDay'];
        $addUseVal = $params['addUseVal'];//누적 사용량
        $typeGubun = $params['typeGubun'];//

        $eDate = date("Y-m-d", strtotime($date . " +1 day"));

        $req = [
            'electricType' => $elecType,
            'typeGubun' => $typeGubun,
            'startDate' => $date,
            'endDate' => $eDate,
            'useValue' => $useVal + $addUseVal
        ];

        $price1 = $this->getPriceValue($req);

        $req['useValue'] = $addUseVal;
        $price2 = $this->getPriceValue($req);

        return (int)$price1 - $price2;

        /* 구버전
        $req = [
            'electricType' => $elecType,
            'typeGubun' => $typeGubun,
            'startDate' => $date
        ];

        $costUnit = $this->getCostInfoPrice($req);

        $price1 = $this->getUsedanPrice($params,$costUnit[0],$costUnit[1]);//구간요금

        $params['useValue'] += $addUseVal;
        $price2 = $this->getDefaultPrice($params,$costUnit[0],$costUnit[1]);//기본요금

        //기본요금을 그 달의 날 수 만큼 나눈다

        $days = date('t',strtotime($date));

        $price = $price1 + ($price2/$days);

        return $this->cutWonVal($price);// */
    }

    /**
     * 기본요금 제외 여부에 따른  기본요금 조정  (일반용,산업용)
     *
     * @param string $electricType
     * @param int $defaultPrice
     * @param string $isDefaultPrice
     *
     * @return int
     */
    private function getDefaultPrice($electricType, $defaultPrice, $isDefaultPrice)
    {
        $fcPrice = 0;

        if (in_array($electricType, ['N', 'S']) === false) {
            return $defaultPrice; // 일반용 산업용이아닌 경우는 해당되지 않음.
        }

        if ($isDefaultPrice === 'N') {
            return $fcPrice; // 기본요금을 사용하지 않은 경우 0으로 처리
        }

        if (empty($defaultPrice) === true || $defaultPrice < 0) {
            return $fcPrice; // 요금이 마이너스인 경우 0으로 처리
        }

        $fcPrice = $defaultPrice; // 기본요금 반영

        return $fcPrice;
    }
}