<?php
namespace App\HttpController\Api\User;

use App\HttpController\Api\ApiBase;
use App\Model\User\UserModel;
use App\Utility\Assert\Assert;
use App\Utility\Cache\UserCache;
use EasySwoole\Http\Message\Status;


class UserBase extends ApiBase
{
    const  USER_TOKEN_NAME = 'user_token';

    public $who;

    protected $noneAuthAction = [];

    function onRequest(?string $action): ?bool
    {
        if(parent::onRequest($action) === false){
            return  false;
        }
        //控制器池，重置
        $this->who = null;
        if(in_array($action, $this->noneAuthAction)){
            return true;
        } else{
            if($this->who()){
                Assert::assert($this->who->isDelete != UserModel::DELETE_DELETE,'该用户被删除');
                return true;
            } else {
                $this->writeJson(Status::CODE_UNAUTHORIZED,null,'请登录');
                return false;
            }
        }
    }

    protected function who(): ?UserModel
    {
        if($this->who){
            return $this->who;
        }
        $token = $this->request()->getCookieParams(static::USER_TOKEN_NAME);
        if(empty($token)){
            $token = $this->request()->getRequestParam($token);
        }
        $this->who = UserCache::getInstance()->getBySession($token);
        return $this->who;
    }
}