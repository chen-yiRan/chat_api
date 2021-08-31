<?php
namespace App\Service\User;

use App\Model\User\UserBlacklistModel;
use App\Service\BaseService;
use EasySwoole\Component\Singleton;

class UserBlacklistService extends BaseService
{
    use Singleton;


    function checkExitstsBlack(int $userId, int $friendId)
    {
        $isPullBlack = UserBlacklistModel::create()->where(['userId' => $userId,'toUserId' => $friendId])->get();
        if($isPullBlack){
            return true;
        }
        return false;
    }
}