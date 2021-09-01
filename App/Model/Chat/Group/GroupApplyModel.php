<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * GroupApplyModel
 * Class GroupApplyModel
 * Create With ClassGeneration
 * @property int    $applyId //
 * @property int    $groupId //
 * @property int    $fromUserId //
 * @property string $applyMsg //
 * @property string $refuseMsg //
 * @property int    $applyTime //
 * @property int    $status //  0 未审核  1 通过  -1拒绝
 */
class GroupApplyModel extends BaseModel
{

    const STATUS_TO_BE_VERIFY = 0;//待审核
    const STATUS_VERIFY_ED = 1;//已审核
    const STATUS_REFUSE = -1;//已拒绝
    protected $tableName = 'chat_group_apply_list';


    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    /**
     * 判断是否已经申请该群
     * checkApplyGroup
     * @param int $userId
     * @param int $groupId
     * @return GroupApplyModel|array|bool|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author Tioncico
     * Time: 17:24
     */
    function checkApplyGroup(int $userId, int $groupId)
    {
        $this->where('fromUserId', $userId);
        $this->where('groupId', $groupId);
        $this->where('status', self::STATUS_TO_BE_VERIFY);
        return $this->get();
    }

    function addApply(int $groupId, int $userId, string $applyMsg, int $status = self::STATUS_TO_BE_VERIFY)
    {
        $groupApplyModel = new GroupApplyModel();
        $groupApplyModel->groupId = $groupId;
        $groupApplyModel->fromUserId = $userId;
        $groupApplyModel->applyMsg = $applyMsg;
        $groupApplyModel->refuseMsg = null;
        $groupApplyModel->applyTime = time();
        $groupApplyModel->status = $status;
        $groupApplyModel->save();
        return $groupApplyModel;
    }

    function groupInfo()
    {
        return $this->hasOne(UserGroupModel::class, function (QueryBuilder $builder) {
            $fieldArr = ['groupHash', 'groupNum', 'groupName', 'belongToUserId', 'groupId'];
            return $builder->fields(implode(',', $fieldArr));
        }, 'groupId', 'groupId');
    }

    function fromUserInfo()
    {
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder) {
            $builder->fields('userId,account,username,avatar');
            return $builder;
        }, 'fromUserId', 'userId');
    }
}

