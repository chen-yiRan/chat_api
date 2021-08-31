<?php


namespace App\Utility\ApiErrorCode;


class UserError extends ApiErrorCode
{
    //错误码定义为 A BB CCC
    //A 错误级别 4 服务级错误 5系统级错误
    //BB 模块码 00 通用模块 例如参数错误
    //CCC 具体错误编号 根据模块不同而不同
    protected $moduleCode = '03';
    //用户详情已存在
    const ERROR_USER_DETAIL_EXIST = 403001;
    //用户详情不存在
    const ERROR_USER_DETAIL_NOT_EXIST = 403002;
    //account存在
    const ERROR_ACCOUNT_EXIST = 403003;
    //离上次修改没有超过1年
    const ERROR_CHANGE_NO_A_YEAR = 403004;
    //用户被删除
    const ERROR_USER_DELETE = 403005;
    //不存在用户隐私信息
    const ERROR_USER_PRIVACY_NOT_EXIST = 403006;
    //验证码错误
    const ERROR_VERIFY_CODE_ERROR = 403007;
    const ERROR_SYSTEM_REGISTER_ERROR = 403008;


}