<?php


namespace App\Utility\ApiErrorCode;


class ApiErrorCode
{
    //错误码定义为 A BB CCC
    //A 错误级别 4 服务级错误 5系统级错误
    //BB 模块码 00 通用模块 例如参数错误
    //CCC 具体错误编号 根据模块不同而不同

    protected $moduleCode = '00';
    const ERROR_PARAMS_ERROR = 400001;//全局参数错误
    const ERROR_REPEAT_OPERATION = 400002;//重复操作/提交
    const CODE_INTERNAL_SERVER_ERROR = 500001;//服务端出错
}
