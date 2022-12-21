<?php
namespace Http\Command;

/**
 * Class TmpSession
 */
class TmpSession extends Command
{
    /**
     * TmpSession constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * TmpSession destructor.
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

        $ss_complex_pk = $_SESSION['ss_complex_pk'];

        if (isset($_SESSION['tmp'][$ss_complex_pk]) === true) {
            $data = $_SESSION['tmp'][$ss_complex_pk];
        }

        $this->data = $data;
        return true;
    }
}