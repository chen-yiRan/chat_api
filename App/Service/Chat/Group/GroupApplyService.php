<?php


namespace App\Service\Chat\Group;


use App\Model\BaseModel;
use App\Model\Chat\SystemMsgModel;
use App\Model\Chat\Group\GroupApplyModel;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Model\User\UserModel;
use App\Service\Chat\SystemMsgService;
use App\Utility\Assert\AssertException;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Singleton;
use EasySwoole\ORM\AbstractModel;

class GroupApplyService extends GroupBaseService
{
    use Singleton;

    public function addApply(string $groupHash, ?string $applyMsg = null)
    {
        $groupModel = new GroupModel();
        $groupInfo = $groupModel->getOneByGroupHash($groupHash);
        if (empty($groupInfo)) {
            throw  new BusinessException("群组数据不存在");
        }
        $groupUserModel = new GroupUserModel();
        //判断是否为本群成员
        $userInfo = $groupUserModel->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
        if ($userInfo) {
            throw  new BusinessException("你已经是本群成员");
        }
        //判断是否已经存在未审核的申请记录
        $groupApplyModel = new GroupApplyModel();
        $applyInfo = $groupApplyModel->checkApplyGroup($this->who->userId, $groupInfo->groupId);
        if ($applyInfo) {
            throw  new BusinessException("你已经发起过申请");
        }
        $groupApplyInfo = BaseModel::transaction(function () use ($groupInfo, $applyMsg) {
            $groupApplyModel = new GroupApplyModel();
            $groupApplyInfo = $groupApplyModel->addApply($groupInfo->groupId, $this->who->userId, $applyMsg ?? '');
            // 新增群申请系统消息
            SystemMsgService::getInstance($this->who)->sendGrupManagerSystemMsg($groupInfo, $this->who, "{$this->who->username} 申请加入群 {$groupInfo->groupName} ", SystemMsgModel::TYPE_GROUP_APPLY, $groupApplyInfo->applyId);
            return $groupApplyInfo;
        });
        return $groupApplyInfo;
    }

    /**
     * 同意入群
     * agree
     * @param GroupApplyModel $applyInfo
     * @return GroupUserModel
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author tioncico
     * Time: 上午11:28
     */
    public function agree(GroupApplyModel $applyInfo)
    {
        //更新为群员
        $groupInfo = GroupModel::create()->get($applyInfo->groupId);
        $userInfo = UserModel::create()->get($applyInfo->fromUserId);
        //增加群成员进群
        $groupUserInfo = GroupUserModel::create()->addGroupUser($groupInfo, $userInfo);
        SystemMsgService::getInstance($this->who)->sendSystemMsg($userInfo->userId, $this->who->userId, $groupInfo->groupId, "{$this->who->username} 已同意你的加群请求", SystemMsgModel::TYPE_APPLY_AGREE, $applyInfo->applyId);
        return $groupUserInfo;
    }

    public function refuse(GroupApplyModel $applyInfo)
    {
        SystemMsgService::getInstance($this->who)->sendSystemMsg($applyInfo->fromUserId, $this->who->userId, $applyInfo->groupId, "{$this->who->username} 拒绝了你的加群请求", SystemMsgModel::TYPE_APPLY_REFUSE, $applyInfo->applyId);
        return true;
    }

    /**
     * 申请数据
     * applyInfo
     * @param int $applyId
     * @return GroupApplyModel|array|bool|\EasySwoole\ORM\AbstractModel|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|null
     * @throws BusinessException
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author tioncico
     * Time: 上午11:34
     */
    public function applyInfo(int $applyId)
    {
        $model = new GroupApplyModel();
        $applyInfo = $model->get(['applyId' => $applyId]);
        //不是自己申请的,则判断是不是管理员
        if ($applyInfo->fromUserId != $this->who->userId) {
            //判断是否是管理员
            $groupUserModel = new GroupUserModel();
            $groupUserInfo = $groupUserModel->getOneByGroupIdAndUserId($applyInfo->groupId, $this->who->userId);
            if (empty($groupUserInfo) || $groupUserInfo->isManager != $groupUserInfo::MANAGER) {
                throw  new BusinessException("申请数据不存在");
            }
        }
        return $applyInfo;
    }

}
