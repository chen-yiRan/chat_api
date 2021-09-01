<?php


namespace App\Service\Chat\Group;


use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Utility\Assert\AssertException;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Singleton;

class GroupService extends GroupBaseService
{
    use Singleton;

    public function addGroup($groupName)
    {
        $groupModel = new GroupModel();
        $groupInfo = $groupModel::transaction(function () use ($groupModel, $groupName) {
            $groupInfo = $groupModel->addGroup($groupName, $this->who->userId);
            //新增群员
            $groupUserModel = new GroupUserModel();
            $groupUserModel->addGroupUser($groupInfo, $this->who, $groupUserModel::MANAGER);
            return $groupInfo;
        });

        return $groupInfo;
    }

    public function getUserGroupList(?int $weddingId=0)
    {
        $groupModel = new GroupModel();
        if ($weddingId>0){
            $groupModel->where('weddingGroupRelation.weddingId = '.$weddingId);
        }

        $groupList = $groupModel
            ->field("`group`.*,weddingGroupRelation.weddingId as weddingId")
            ->with([])
            ->alias("group")
            ->join("wedding_group_relation_list as weddingGroupRelation","weddingGroupRelation.groupId = `group`.groupId","LEFT")
            ->join("chat_group_user_list as groupUser","groupUser.groupId = `group`.groupId")
            ->where("groupUser.userId = {$this->who->userId}")
            ->all();
        return $groupList;
    }

    public function getGroupInfo($groupHash): ?GroupModel
    {
        $model = new GroupModel();
        $info = $model->get(['groupHash' => $groupHash]);
        return $info;
    }

    public function updateGroup($groupHash, $param)
    {
        $model = new GroupModel();
        $info = $model->get(['groupHash' => $groupHash]);
        if (empty($info)) {
            throw new BusinessException("该数据不存在");
        }
        $updateData = [];

        $updateData['groupName'] = $param['groupName'] ?? $info->groupName;
        $updateData['belongToUserId'] = $param['belongToUserId'] ?? $info->belongToUserId;
        $updateData['groupNotice'] = $param['groupNotice'] ?? $info->groupNotice;
        $updateData['groupSpeakState'] = $param['groupSpeakState'] ?? $info->groupSpeakState;
        $info->update($updateData);
        return $info;
    }

    public function dismissGroup($groupHash)
    {
        $model = new GroupModel();
        $info = $model->get(['groupHash' => $groupHash]);
        if (!$info) {
            throw new BusinessException("该数据不存在");
        }
        $model->dismiss($info);
        return $info;
    }

    public function quitGroup($groupHash)
    {
        $model = new GroupModel();
        $groupInfo = $model->get(['groupHash' => $groupHash]);
        if (!$groupInfo) {
            throw new BusinessException("群数据不存在");
        }
        if ($groupInfo->belongToUserId == $this->who->userId) {
            throw new BusinessException("你是群主不能退出群");
        }
        //获取群用户数据
        $groupUserInfo = GroupUserModel::create()->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
        if (empty($groupUserInfo) || $groupUserInfo->state == $groupUserInfo::STATE_DELETE) {
            throw new BusinessException("你不是群成员");
        }
        $groupUserInfo->delGroupUser($groupUserInfo);
        return $groupUserInfo;
    }

}
