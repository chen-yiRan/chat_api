<?php


namespace App\Service\Chat\Group;


use App\Model\BaseModel;
use App\Model\Chat\Group\GroupApplyModel;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupMsgModel;
use App\Model\Chat\Group\GroupSettingModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Model\User\UserModel;
use App\Utility\Assert\AssertException;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Singleton;
use UnitTest\User\Chat\Group\GroupTest;

class GroupUserService extends GroupBaseService
{
    use Singleton;

    public function getGroupUserList()
    {

    }


    public function joinGroup(GroupModel $groupInfo)
    {
        $groupUserInfo = GroupUserModel::create()->addGroupUser($groupInfo, $this->who);
        //发送一条消息
        GroupMsgService::getInstance($this->who)->addGroupMsg($groupInfo, "{$this->who->username}已加入本群", GroupMsgModel::TYPE_TEXT);
        return $groupUserInfo;
    }
}
