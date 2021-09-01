<?php

namespace App\Model\Chat;

use App\Model\Chat\Group\GroupApplyModel;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupMsgModel;
use App\Model\User\UserModel;
use App\Utility\Bean\ListBean;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

/**
 * SystemMsgModel
 * Class SystemMsgModel
 * Create With ClassGeneration
 * @property int    $systemId //
 * @property int    $userId //
 * @property int    $byUserId //
 * @property int    $byGroupId //
 * @property string $type //
 * @property string $content //
 * @property int    $addTime //
 * @property int    $isRead //
 * @property string $extraData //
 */
class SystemMsgModel extends AbstractModel
{
    protected $tableName = 'chat_system_msg_list';
    const TYPE_GROUP_APPLY = "GROUP_APPLY";
    const TYPE_APPLY_AGREE = "APPLY_AGREE";
    const TYPE_APPLY_REFUSE = "APPLY_REFUSE";

    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    public function addSystemMsg(int $userId, int $byUserId, int $byGroupId, string $content, string $type, int $extraData)
    {
        $model = new SystemMsgModel();
        $model->userId = $userId;
        $model->byUserId = $byUserId;
        $model->byGroupId = $byGroupId;
        $model->type = $type;
        $model->content = $content;
        $model->addTime = time();
        $model->isRead = self::READ_UN_READ;
        $model->extraData = $extraData;
        $model->save();
        return $model;
    }

    public function applyInfo()
    {
        return $this->hasOne(GroupApplyModel::class, function (QueryBuilder $builder) {
            return $builder;
        }, 'byApplyId', 'applyId');
    }

    public function byUserInfo()
    {
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder) {
            $builder->fields('userId,account,username,avatar');
            return $builder;
        }, 'byUserId', 'userId');
    }

    public function byGroupInfo()
    {
        return $this->hasOne(GroupModel::class, function (QueryBuilder $builder) {
            return $builder;
        }, 'byGroupId', 'groupId');
    }
}

