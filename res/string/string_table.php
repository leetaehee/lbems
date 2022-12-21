<?php
//----------------------------------------------------------------------------------------------------------
// System
//----------------------------------------------------------------------------------------------------------
define('ErrNoResponder', '해당 요청을 처리 할 수 있는 모듈이 존재하지 않습니다.');
define('ErrWrongRequest', '올바르지 않은 요청입니다.');
define('ErrWrongIdPasswd', '아이디 또는 비밀번호가 올바르지 않습니다.');
define('ErrWrongAdminInfo', '해당 계정의 사용자 정보가 올바르지 않습니다.');
define('ErrWrongComplexInfo', '건물 설정 정보가 없습니다.');
define('ErrSetFail', '시스템 오류가 발생하여 설정에 실패하였습니다.');
define('ErrAccess', '접근 할 수 없습니다.');
define('ManualNoAccess', '현재 매뉴얼 준비중입니다.');
define('ErrPassword', '비밀번호가 틀렸습니다.');
define('ErrAccount', '해당 계정은 존재하지 않습니다. KevinLAB에 문의하세요.');
define('ErrPasswordOverCount', '비밀번호가 5회 이상 초과되어 계정이 잠겼습니다. KevinLab에 문의하세요.');
define('ErrMakeToken', '토큰 생성 에러! KevinLab에 문의하세요.');
define('ErrTokenValidTime', '토큰의 유효시간이 초과되었습니다.');
define('ErrTokenInfo', '토큰 정보가 유효하지 않습니다.');
define('ErrLogin', '로그인을 하세요.');
define('ErrAuthorizationToken', 'Authorization 에 인증타입과 토큰을 확인 하세요.');
define('ErrTokenAuthorization', '정상적인 토큰이 필요합니다.(401)');
define('ErrLoginNotInfo', '로그인 정보가 변동되었습니다. 다시 로그인 하세요.');
define('ErrAPIStandard', 'API 규격서를 확인하세요.');
define('ErrContentTypePostRule', 'POST 전송 시 x-www-form-urlencoded로 하세요.');
define('ErrComplexCodePk', '단지 코드를 입력하세요');
define('ErrNoData', '데이터가 존재하지 않습니다.');
define('ErrApiFunctionOpen', 'API 준비 중입니다. KevinLAB 에 문의하세요.');
define('ErrApiSyncData', '전송 데이터 타입을 확인하세요.');
define('ErrApiSyncDataType', '전송 데이터가 존재하지 않습니다.');
define('ErrAirConditionerCompany', '에어컨 제조사 정보를 확인하세요.');
define('ErrApiControlStatusType', 'status_type 정보를 확인하세요.');
define('ErrApiAirConditionalId', 'EHP ID 정보를 확인하세요.');

define('loginSuccessMessage', '로그인에 성공하였습니다.');

//----------------------------------------------------------------------------------------------------------
// Database
//----------------------------------------------------------------------------------------------------------
define('ErrDbType', '올바르지 않는 데이터베이스 타입입니다.');
define('ErrReadConfig', '데이터베이스 설정 파일을 읽을 수 없습니다.');
define('ErrReadSmsConfig', 'SMS 설정 파일을 읽을 수 없습니다.');
define('ErrConnection', '데이터베이스 연결에 실패하였습니다.');

//----------------------------------------------------------------------------------------------------------
// .ENV
//----------------------------------------------------------------------------------------------------------
define('ErrEnvFilePathExist', '환경설정 파일이 존재 하지 않습니다. KevinLAB에 문의하세요.');
define('ErrEnvFileItemEmpty', '실행파일에 항목이 존재하지 않습니다. KevinLAB에 문의하세요.');
define('ErrEnvFileItemDefine', '설정파일에 정의되어 있지 않습니다. KevinLab에 문의하세요.');