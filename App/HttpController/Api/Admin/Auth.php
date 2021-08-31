<?php


namespace App\HttpController\Api\Admin;


use App\Model\Admin\AdminUserModel;
use App\Utility\Assert\Assert;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Utility\Random;

/**
 * Class Auth
 * @package App\HttpController\Api\Admin
 * @ApiGroupAuth(name="adminSession",description="访问接口时需要带上登陆时管理端返回的TOKEN",from={GET,POST,COOKIE},required="",ignoreAction={"onRequest","login"})
 * @ApiGroup(groupName="管理员登陆")
 * @ApiGroupDescription("管理员登陆")
 */
class Auth extends AdminBase
{
    protected $noneAuthAction = ['login'];

    /**
     * @Api(name="管理员登录",path="/Api/Admin/Auth/login")
     * @ApiDescription("管理员登录")
     * @Param(name="adminAccount",required="",description="密码")
     * @Param(name="adminPassword",required="",description="账号")
     * @ApiRequestExample("curl http://127.0.0.1:9501/Api/Admin/Auth/login?account=123456&password=e10adc3949ba59abbe56e057f20f883e")
     * @ApiSuccess({"code":200,"result":{"adminId":1,"adminName":"zyx","adminAccount":"123456","addTime":0,"lastLoginTime":1596530015,"lastLoginIp":"192.168.0.122","adminSession":"b2187eb9f20fb327"},"msg":"登陆信息"})
     * @author xdd
     * Time: 16:03
     */
    function login($adminAccount, $adminPassword)
    {
        $admin = AdminUserModel::create()->where(['adminAccount' => $adminAccount, 'adminPassword' => AdminUserModel::hashPassword($adminPassword)])->get();
        Assert::assert(!!$admin, "账号或密码错误");
        $time = time();
        $session = Random::character(32);
        $admin->update([
            'lastLoginTime' => $time,
            'lastLoginIp' => $this->clientRealIP(),
            'adminSession' => $session
        ]);
        $this->response()->setCookie(self::ADMIN_TOKEN_NAME, $session, time() + 86400 * 7, '/');
        $this->writeJson(Status::CODE_OK, $admin, "登陆信息");
    }

    /**
     * @Api(name="管理员登出",path="/Api/Admin/Auth/logout")
     * @ApiDescription("管理员登出")
     * @ApiRequestExample("curl http://127.0.0.1:9501/Api/Admin/Auth/logout")
     * @ApiSuccess({"code":200,"result":true,"msg":"注销成功"})
     * @author xdd
     * Time: 16:03
     */
    function logout()
    {
        $admin = $this->who();
        $result = $admin->logout();
        $this->writeJson(Status::CODE_OK, $result, "注销成功");
    }

    /**
     * @Api(path="/Api/Admin/Auth/getInfo",name="管理员信息")
     */
    function getInfo()
    {
        $this->writeJson(Status::CODE_OK, $this->who(), '获取信息成功');
    }

}