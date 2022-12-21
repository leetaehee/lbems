<?php
namespace Http\Command;

/**
 * Class MenuAuthority 메뉴 권한 정보 조회
 */
class MenuAuthority extends Command
{
    /**
     * MenuLocation constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * MenuLocation destructor.
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
     * @return bool|null
     *
     * @throws \Exception
     */
    public function execute(array $params) :? bool
    {
        $data = [];

        $groupId = isset($params[0]['value']) === true ? $params[0]['value'] : '';

        if (is_int($groupId) === false || $groupId < 1) {
            $data = [
                'error' => 'data-error',
            ];
        }

        // 권한 조회
        $rMenuAuthorityQ = $this->emsQuery->getMenuGroupInfo($groupId);
        $rAuthority = $this->query($rMenuAuthorityQ);

        $data = [
            'group_name' => $rAuthority[0]['name'],
            'authority' => $rAuthority[0]['authority']
        ];

        $this->data = $data;
        return true;
    }
}