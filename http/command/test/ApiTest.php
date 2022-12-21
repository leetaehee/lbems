<?php
namespace Http\Command;

use EMS_Module\Utility;

/**
 * Class ApiTest api 테스트
 */
class ApiTest extends Command
{
    /**
     * AnalysisEnergy constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * AnalysisEnergy destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 메인 실행 함수
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $url = '';

        $method = 'POST';

        $httpHeader = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $postData = [
            'id' => '',
            'pw' => '',
        ];

        // 함수에서 url 인코딩 하도록 수정하기..
        Utility::getInstance()->curlProcess($url, $method, $httpHeader, $postData);

        $this->data = $data;
        return true;
    }
}