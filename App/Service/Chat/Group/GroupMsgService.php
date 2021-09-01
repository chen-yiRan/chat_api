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
use App\WebSocket\Command;
use App\WebSocket\MsgPushEvent;
use EasySwoole\Component\Singleton;
use UnitTest\User\Chat\Group\GroupTest;

class GroupMsgService extends GroupBaseService
{
    use Singleton;


    public function sendMsg(string $groupHash, string $msg, int $type): GroupMsgModel
    {
        $groupModel = new GroupModel();
        $groupInfo = $groupModel->getOneByGroupHash($groupHash);
        if (empty($groupInfo)) {
            throw new BusinessException("群组数据不存在");
        }
        $groupUserModel = new GroupUserModel();
        $groupUserInfo = $groupUserModel->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
        if (empty($groupUserInfo)) {
            throw new BusinessException("你不是该群成员");
        }
        if ($groupUserInfo->speakState != $groupUserInfo::SPEAK_STATE_NORMAL) {
            throw new BusinessException("你已被禁言");
        }

        if ($groupUserInfo->isManager != $groupUserInfo::MANAGER && $groupInfo->groupSpeakState == $groupInfo::SPEAK_STATE_FORBIDDEN) {
            throw new BusinessException("该群已开启全群禁言");
        }
        $msgInfo = $this->addGroupMsg($groupInfo, $msg, $type);
        return $msgInfo;
    }

    public function addGroupMsg(GroupModel $groupInfo, string $msg, int $type)
    {
        $msgInfo = BaseModel::transaction(function () use ($groupInfo, $msg, $type) {
            $groupMsgModel = new GroupMsgModel();
            $msgInfo = $groupMsgModel->addMsg($groupInfo->groupId, $this->who->userId, $msg, $type);
            //给其他所有群员更新lastMsgId
            $groupUserModel = new GroupUserModel();
            $groupUserModel->updateMsgId($groupInfo->groupId, null, null, $msgInfo->msgId);
            //todo 聊天文件资源
            $this->pushGroupMsg($msgInfo);
            return $msgInfo;
        });
        return $msgInfo;
    }

    public function pushGroupMsg(GroupMsgModel $groupMsgInfo)
    {
        //获取所有群成员id
        $fields = [
            'userId',
            'groupUserId'
        ];
        $groupUserModel = new GroupUserModel();
        $groupUserList = $groupUserModel->where('groupId', $groupMsgInfo->groupId)->field(implode(',', $fields))->all();
        $groupUserIds = array_column($groupUserList, 'userId');
        foreach ($groupUserIds as $userId) {
            if ($userId == $this->who->userId) {
                continue;
            }
            //推送给别人
            MsgPushEvent::getInstance()->msgPush(Command::USER_RECEIVES_MSG_PUSH, $groupMsgInfo, $userId, null);
        }
    }

    public function getMsgInfo($msgId)
    {
        $model = new GroupMsgModel();
        $msgInfo = $model->with(['groupInfo', 'fromUserInfo'], false)->get($msgId);
        if (empty($msgInfo)) {
            throw new BusinessException("消息不存在");
        }
        //判断是否为该群成员
        $groupUserModel = new GroupUserModel();
        $groupUserInfo = $groupUserModel->getOneByGroupIdAndUserId($msgInfo->groupId, $this->who->userId);
        if (empty($groupUserInfo)) {
            throw new BusinessException("消息不存在");
        }
        return $msgInfo;
    }

    public function getMsgList(?string $groupHash = null, string $operate, ?int $msgId = null)
    {
        $groupIds = [];
        if (!empty($groupHash)) {
            $groupModel = new GroupModel();
            $groupInfo = $groupModel->getOneByGroupHash($groupHash);
            if (empty($groupInfo)) {
                throw new BusinessException("群组数据不存在");
            }
            $groupUserModel = new GroupUserModel();
            $groupUserInfo = $groupUserModel->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
            if (empty($groupUserInfo)) {
                throw new BusinessException("你不是该群成员");
            }
            $groupIds[] = $groupInfo->groupId;
        }

        $list = $this->getUserGroupMsgList($groupIds, $operate, $msgId);
        return $list;
    }

    public function getUserGroupMsgList(array $groupIds = [], string $operate = 'before', ?int $msgId = null)
    {
        $groupMsgModel = new GroupMsgModel();
        $limit = 100;
        if ($msgId !== null) {
            if ($operate == 'after') {
                $groupMsgModel->where('groupMsg.msgId', $msgId, '>');
            } else {
                $groupMsgModel->where('groupMsg.msgId', $msgId, '<');
            }
        }
        if (!empty($groupIds)) {
            $groupMsgModel->where('groupMsg.groupId', $groupIds, 'in');
        }

        $list = $groupMsgModel
            ->alias("groupMsg")
            ->with(['groupInfo', 'fromUserInfo'], false)
            ->join("chat_group_user_list as groupUser", 'groupUser.groupId = groupMsg.groupId and groupUser.userId = ' . $this->who->userId)
            ->order("groupMsg.msgId", 'DESC')
            ->field("*")
            ->limit($limit)
            ->all();
        return $list;
    }
}
