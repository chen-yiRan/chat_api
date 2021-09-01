<?php


namespace App\Service\Chat\Group;


use App\Model\BaseModel;
use App\Model\Chat\Group\GroupApplyModel;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupSettingModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Model\User\UserModel;
use App\Utility\Assert\AssertException;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Singleton;
use UnitTest\User\Chat\Group\GroupTest;

class GroupSettingService extends GroupBaseService
{
    use Singleton;

    public function getGroupSetting($groupHash)
    {
        $groupInfo = GroupModel::create()->getOneByGroupHash($groupHash);
        if (empty($groupInfo)) {
            throw new BusinessException("群组数据不存在");
        }
        return GroupSettingModel::create()->getGroupData($groupInfo->groupId);
    }

    public function updateSetting($groupHash, $key, $value)
    {
        $groupInfo = GroupModel::create()->getOneByGroupHash($groupHash);
        if (empty($groupInfo)) {
            throw new BusinessException("群组数据不存在!");
        }
        if (!isset(GroupSettingModel::DEFAULT_SETTING[$key])) {
            throw new BusinessException("配置项不存在!");
        }
        return GroupSettingModel::create()->saveGroupSetting($groupInfo->groupId, $key, $value);
    }


}
