<?php
namespace UnitTest\Utility\DataTrait;

use App\Model\User\UserModel;
use App\Utility\Cache\UserCache;

trait UserTraint
{
    protected $data;

    //用户
    public function addUser($account = 'unit-test-name', string $password = '123456', string $phone = null): UserModel
    {
        $userInfo = UserModel::create()->get(['account' => $account]);
        if($userInfo){
            $userInfo->update(['isForbid' => 0, 'isDelete' => 0]);
            $this->data[] = $userInfo;
            return  $userInfo;
        }
        $phone = $phone ?? time() . mt_rand(10000, 99999);
        $password = md5($password);
        $userInfo = UserModel::create()->addUser($account,$password,$phone,'我的测试');
        UserCache::getInstance()->set($userInfo);
        $this->data[] = $userInfo;
        return $userInfo;
    }
}