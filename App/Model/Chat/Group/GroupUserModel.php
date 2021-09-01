<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Model\User\UserModel;
use App\Utility\Bean\ListBean;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * GroupUserModel
 * Class GroupUserModel
 * Create With ClassGeneration
 * @property int    $groupUserId //
 * @property int    $groupId //
 * @property string $groupUserHash //
 * @property int    $userId //
 * @property int    $isManager //
 * @property int    $lastMsgId //
 * @property int    $lastReadMsgId //
 * @property int    $unreadCount //
 * @property int    $state // 群员状态1正常,0已被删除
 * @property string $remark // 群成员备注
 * @property int    $lastMsgTime // 最后消息发送时间
 * @property int    $speakState // 发言状态,1正常,2禁言
 * @property int    $speakForbiddenTime // 禁言时间,只有当speakState=0时才有效
 * @property int    $receiveMsgType // 接收消息状态
 */
class GroupUserModel extends BaseModel
{
    protected $tableName = 'chat_group_user_list';

    const  MANAGER = 1;
    const  NORMAL = 0;
    const STATE_NORMAL = 1;
    const STATE_DELETE = 0;

    const SPEAK_STATE_NORMAL = 1;
    const SPEAK_STATE_FORBIDDEN = 0;

    const RECEIVE_MSG_NORMAL = 1;
    const RECEIVE_MSG_DO_NOT_DISTURB = 2;
    const RECEIVE_MSG_MASK = 0;

    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    function userInfo()
    {
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder) {
            $builder->fields('userId,account,username,avatar');
            return $builder;
        }, 'userId', 'userId');
    }

    function groupInfo()
    {
        return $this->hasOne(GroupModel::class, function (QueryBuilder $builder) {
            $field = [
                'groupId',
                'groupHash',
                'groupNum',
                'groupName',
                'groupThumb',
                'groupNotice',
                'belongToUserId',
                'groupSpeakState',
            ];
            return $builder->fields($field);
        }, 'groupId', 'groupId');
    }

    public function getGroupUserList(int $groupId, $isManager = null, $state = self::STATE_NORMAL, $page = 1, $pageSize = 20)
    {
        $this->where('groupId', $groupId);
        if ($isManager !== null) {
            $this->where('isManager', $isManager);
        }
        if ($state !== null) {
            $this->where('state', $state);
        }

        $data = $this
            ->with(['userInfo'])
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->getList($page, $pageSize);
        return $data;
    }

    function getOneByGroupIdAndUserId($groupId, $userId, $state = self::STATE_NORMAL): ?GroupUserModel
    {
        $this->where('groupId', $groupId);
        $this->where('userId', $userId);
        if ($state !== null) {
            $this->where('state', $state);
        }
        $info = $this->get();
        return $info;
    }

    function addGroupUser(GroupModel $groupInfo, UserModel $userInfo, $isManager = self::NORMAL)
    {
        $model = new GroupUserModel();
        $data = [
            'groupUserHash'  => substr(md5($groupInfo->groupHash . $userInfo->userId), 8, 16),
            'groupId'        => $groupInfo->groupId,
            'userId'         => $userInfo->userId,
            'isManager'      => $isManager,
            'lastMsgId'      => 0,
            'lastReadMsgId'  => 0,
            'unreadCount'    => 0,
            'lastMsgTime'    => 0,
            'state'          => self::STATE_NORMAL,
            'speakState'     => self::SPEAK_STATE_NORMAL,
            'receiveMsgType' => self::RECEIVE_MSG_NORMAL,
        ];

        $groupUserInfo = $model->getOneByGroupIdAndUserId($groupInfo->groupId, $userInfo->userId, null);

        if ($groupUserInfo) {
            $groupUserInfo->update($data);
            return $groupUserInfo;
        } else {
            $model->data($data);
            $model->save();
            return $model;
        }

    }

    function delGroupUser(GroupUserModel $groupUser)
    {
        $groupUser->update(['state' => self::STATE_DELETE]);
    }

    function updateGroupUserManager(GroupUserModel $groupUser, int $isManager)
    {
        $groupUser->update(['isManager' => $isManager]);
    }

    function getOneByGroupUserHash($groupUserHash, $state = self::STATE_NORMAL)
    {
        if ($state !== null) {
            $this->where('state', $state);
        }
        return $this->get(['groupUserHash' => $groupUserHash]);
    }

    function getUnreadGroup($userId, $state = self::STATE_NORMAL)
    {
        if ($state !== null) {
            $this->where('state', $state);
        }
        $list = $this
            ->where('lastMsgId>lastReadMsgId')
            ->where('userId', $userId)
            ->order('lastMsgTime', 'DESC')
            ->all();
        return $list;
    }

    function updateMsgId($groupId, $userId = null, int $readMsgId = null, int $unReadMsgId = null)
    {
        $model = new GroupUserModel();
        if ($userId !== null) {
            $model->where('userId', $userId);
        }
        $model->where('groupId', $groupId);

        $update = [];
        if ($readMsgId !== null) {
            $update['lastReadMsgId'] = $readMsgId;
        }
        if ($unReadMsgId !== null) {
            $update['lastMsgId'] = $unReadMsgId;
            $update['lastMsgTime'] = time();
        }
        if (empty($update)) {
            return true;
        }
        return $model->update($update);
    }

    function updateSpeakState()
    {
        $model = new GroupUserModel();
        $model->where(['speakState' => self::SPEAK_STATE_FORBIDDEN, 'speakForbiddenTime' => [time(), '<=']])->chunk(function (GroupUserModel $groupUserInfo) {
            $groupUserInfo->speakState = self::SPEAK_STATE_NORMAL;
            $groupUserInfo->speakForbiddenTime = null;
            $groupUserInfo->update();
        });
    }
}

