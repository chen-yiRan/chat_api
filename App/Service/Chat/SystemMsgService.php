<?php


namespace App\Service\Chat;


use App\Model\Chat\SystemMsgModel;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Model\User\UserModel;
use EasySwoole\Component\Singleton;

class SystemMsgService extends ChatBaseService
{
    use Singleton;

    public function getSystemMsgList(int $msgId, string $operate)
    {
        $model = SystemMsgModel::create();
        if ($msgId !== null) {
            if ($operate == 'after') {
                $model->where('msgId', $msgId, '>');
            } else {
                $model->where('msgId', $msgId, '<');
            }
        }
        $limit = 100;
        $fields = [
            '*',
            'if(find_in_set(type,"GROUP_APPLY,APPLY_AGREE,APPLY_REFUSE"),extraData,0) as byApplyId'
        ];
        $list = $model
            ->where(['userId'=>$this->who->userId])
            ->field($fields)
            ->with(['applyInfo', 'byUserInfo', 'byGroupInfo'], false)
            ->order($model->schemaInfo()->getPkFiledName(), 'DESC')
            ->limit($limit)
            ->all();
        return $list;
    }

    public function getSystemMsgInfo(int $msgId)
    {
        $model = SystemMsgModel::create();
        $fields = [
            '*',
            'if(find_in_set(type,"GROUP_APPLY,APPLY_AGREE,APPLY_REFUSE"),extraData,0) as byApplyId',
        ];
        $list = $model
            ->where(['userId'=>$this->who->userId])
            ->field($fields)
            ->with(['applyInfo', 'byUserInfo', 'byGroupInfo'], false)
            ->get($msgId);
        return $list;
    }

    public function sendSystemMsg(int $userId, int $byUserId, int $byGroupId, string $content, string $type, int $extraData)
    {
        $systemMsgInfo = SystemMsgModel::create()->addSystemMsg($userId, $byUserId, $byGroupId, $content, $type, $extraData);
        //todo: websocket推送
        return $systemMsgInfo;
    }

    public function sendGrupManagerSystemMsg(GroupModel $groupInfo, UserModel $byUserInfo, string $content, string $type, int $extraData)
    {
        //获取所有群管理列表
        $groupUserManagerList = GroupUserModel::create()->getGroupUserList($groupInfo->groupId, GroupUserModel::MANAGER, GroupUserModel::STATE_NORMAL, 1, 999);
        /**
         * @var $groupUser GroupUserModel
         */
        foreach ($groupUserManagerList as $groupUser) {
            $this->sendSystemMsg($groupUser->userId, $byUserInfo->userId, $groupInfo->groupId, $content, $type, $extraData);
        }
        return true;
    }


}
