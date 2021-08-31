<?php
namespace App\HttpController\Api\User;
use App\Model\User\UserModel;
use App\Service\User\UserService;
use App\Utility\ApiErrorCode\UserError;
use App\Utility\Assert\Assert;
use App\Utility\Cache\UserCache;
use App\Utility\CLSY;
use App\Utility\Exception\BusinessException;
use App\Utility\VerifyCode;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;


/**
 * Class Auth
 * @package App\HttpController\Api\User
 * @ApiGroup(groupName="用户登录授权、注销登录、注册用户")
 * @ApiGroupDescription ("用户登录授权、注销登录、注册用")
 * @ApiGroupAuth(name="user_token",from={POST,GET,COOKIE}),required="",description="访问这里的接口需要用户登录后，服务端返回的TOKEN",ignoreAction={"onRequest","loginByPassword","loginBySms","loginByPhone","restorePassword"})
 *
 */
class Auth extends UserBase
{
    protected $noneAuthAction = ['loginByPassword', 'loginBySms', 'loginByPhone', 'restorePassword', 'register', 'restorePhone', 'bindingPhone'];

    /**
     * @Api (name="用户密码登录",path="/Api/User/Auth/loginByPassword")
     * @ApiDescription ("用户密码登录")
     * @Param (name="account",required="",description="账号")
     * @Param (name="password",required="",description="客户端对密码做md5提交")
     */
    function loginByPassword($account, $password)
    {
       $user = UserModel::create()->where('account', $account)->where('password', $password)->get();
       if(!empty($user)) {
           $this->doLogin($user);
           return true;
       }

       $phoneUser = UserModel::create()->where('phone', $account)->where('password', $password)->get();
       if(!empty($phoneUser)){
           $this->doLogin($phoneUser);
           return true;
       }

       $this->writeJson(Status::CODE_BAD_REQUEST,null,'账户不存在或者密码错误');
    }

    /**
     * @Api (name="短信验证吗登录接口,通过短信验证码接口，获取到time,phone,hash参数，保存，然后提交到此接口",path="Api/User/Auth/loginBySms")
     * @param (name="phone",required="")
     * @param (name="code",required="")
     * @param (name="time",required="")
     * @param (name="hash",required="")
     * @throws \App\Utility\Assert\AssertException
     * @throws \App\Utility\Exception\BusinessException
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    function loginBySms($phone,$code,$time,$hash)
    {
        //验证手机号
        UserService::getInstance()->phoneVerify($phone);
        //验证短信验证码
        try{
            VerifyCode::verifySms($code,$phone,$time,$hash);
        } catch (\Throwable $throwable){
            Assert::assert(false,'短信验证吗验证失败');
        }
        //获取user信息
        $user = UserModel::create()->where('phone',$phone)->get();
        //注册新用户
        if(empty($user)){
            $user = UserService::getInstance()->addUser($phone);
        }
        Assert::assert(!!$user,'登录异常');
        $this->doLogin($user);
    }

    /**
     * @Api (name="创蓝闪验,手机号码免登录接口",path="/Api/User/Auth/loginByPhone")
     * @Param(name="token", alias="手机号码", required="")
     * @Param(name="type", alias="设备类型",inArray={"ios","android"},required="")
     * @throws BusinessException
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    function loginByPhone($token,$type){
        //验证token，获取手机号码
        $client = $type == 'ios' ? (CLSY::getIosInstance()) : CLSY::getAndroidInstance();
        $phone = $client->getMobile($token);

        if(empty($phone)){
            throw new BusinessException("登录失败");
        }
        //获取user信息
        $user = UserModel::create()->where('phone', $phone)->get();
        //注册新用户
        if(empty($user)){
            $user = UserService::getInstance()->addUser($phone);
        }
        $this->doLogin($user);
    }
    protected function doLogin(?UserModel $user){
        if($user){
            if($user->isDelete == $user::DELETE_DELETE || $user->isForbid == $user::FORBID_FORBID){
                $this->writeJson(Status::CODE_BAD_REQUEST,null,'账户不存在或者已被禁用');
                return false;
            }
            //更新缓存
            UserCache::getInstance()->set($user);
            $session = UserCache::makeSession($user);
            $info = $user->toArray();
            unset($info['password']);
            $info[static::USER_TOKEN_NAME] = $session;
            $this->response()->setCookie(static::USER_TOKEN_NAME,$session,time()+60*60*24*180);
            $this->writeJson(Status::CODE_OK, $info,'登录成功');
        }else{
            $this->writeJson(Status::CODE_BAD_REQUEST,null,'账号不存在或者密码错误');
        }
    }

    /**
     * @Api (name="register",path="/Api/User/Auth/register")
     * @param (name="account",required="",lengthMin="6",lengthMax="18")
     * @param (name="password",required="",lengthMin="8",lengthMax="30")
     * @param (name="verifyCode",required="",length="4")
     */
    function register(){
        Assert::assert($this->checkVerify(),'验证码错误',UserError::ERROR_VERIFY_CODE_ERROR);
        $param = $this->request()->getRequestParam();
        $model = new UserModel([
            'username' => $param['username'],
            'account' => $param['account'],
            'password' => md5($param['password']),
            'createTime' => time()
        ]);
        $userInfo =$model->get(['account' => $param['account']]);
        Assert::assert(!$userInfo,'账号已存在',UserError::ERROR_ACCOUNT_EXIST);
        try{
            $model->save();
            $this->writeJson(Status::CODE_OK,null,'注册成功');
        }catch (\Throwable $throwable){
            Trigger::getInstance()->throwable($throwable);
            $this->writeJson(UserError::ERROR_SYSTEM_REGISTER_ERROR,'系统原因，注册失败');
        }
    }

    /**
     * @Api (name="checkVerify",path="/Api/User/Auth/checkVerify")
     * @param (name="verifyCode",required="",length="4")
     * @param (name="verifyCodeHash",description="验证码验证hash,cookie中保存",from={COOKIE,GET,POST},required="")
     * @param (name="verifyCodeTime",description="验证码生成时间,cookie中保存",from={COOKIE,GET,POST},required="")
     */
    protected function checkVerify(){
        $verifyCodeHash = $this->request()->getCookieParams(VerifyCode::COOKIE_CODE_HASH);
        $verifyCodeTime = $this->request()->getCookieParams(VerifyCode::COOKIE_CODE_TIME);
        $verifyCodeHash = $verifyCodeHash ?? $this->request()->getRequestParam(VerifyCode::COOKIE_CODE_HASH);
        $verifyCodeTime = $verifyCodeTime ?? $this->request()->getRequestParam(VerifyCode::COOKIE_CODE_TIME);

        $param = $this->request()->getRequestParam();
        $verifyCode = $param['verifyCode'];
        $ttl = 5 * 60;

        //调用后，cookie失效
        $this->response()->setCookie(VerifyCode::COOKIE_CODE_HASH,null,-1);
        $this->response()->setCookie(VerifyCode::COOKIE_CODE_TIME,null,-1);
        //判断是否过期
        if($verifyCodeTime + $ttl < time()){
            return false;
        }
        $code = strtolower($verifyCode);
        return md5($code . $verifyCodeTime) == $verifyCodeHash;
    }
}
