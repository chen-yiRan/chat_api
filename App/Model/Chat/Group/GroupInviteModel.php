<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Model\User\UserModel;
use App\Utility\Bean\ListBean;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * GroupInviteModel
 * Class GroupInviteModel
 * Create With ClassGeneration
 * @property int $inviteId //
 * @property int $groupId // 群id
 * @property int $fromUserId // 邀请人id
 * @property int $toUserId // 被邀请人id
 * @property string $inviteMsg // 邀请人附带信息
 * @property int $addTime // 邀请时间
 * @property int $updateTime // 最后更新时间
 * @property int $status // -2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群
 */
class GroupInviteModel extends BaseModel
{
	protected $tableName = 'chat_group_invite_list';
    const STATUS_REFUSE=-1;
    const STATUS_MANAGER_REFUSE=-2;
    const STATUS_INVITE_ING=0;
    const STATUS_AGREE=1;
    const STATUS_MANAGER_AGREE=2;

	public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
	{
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
	}


    function getInfoByFromUserIdAndToUserIdAndGroupId($fromUserId,$toUserId,$groupId){
        return $this->order($this->schemaInfo()->getPkFiledName(), 'DESC')->get(['fromUserId'=>$fromUserId,'toUserId'=>$toUserId,'groupId'=>$groupId]);
    }


    function fromUserInfo()
    {
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder)  {
            $builder->fields('userId,account,username,avatar');
            return $builder;
        }, 'fromUserId', 'userId');
    }

    function toUserInfo()
    {
        $friendFieldArr = [
            'userId',
            'userHash',
            'account',
            'nickName',
            'thumb',
        ];
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder) use ($friendFieldArr) {
            $builder->fields(implode(',', $friendFieldArr));
            return $builder;
        }, 'toUserId', 'userId');
    }

    function groupInfo()
    {
        return $this->hasOne(UserGroupModel::class, function (QueryBuilder $builder) {
            $fieldArr = ['groupHash', 'groupNum', 'groupName', 'belongToUserId', 'groupId'];
            return $builder->fields(implode(',', $fieldArr));
        }, 'groupId', 'groupId');
    }
}

