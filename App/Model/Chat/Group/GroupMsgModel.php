<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Model\User\UserModel;
use App\Utility\Bean\ListBean;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * GroupMsgModel
 * Class GroupMsgModel
 * Create With ClassGeneration
 * @property int    $msgId //
 * @property string $msg //
 * @property int    $time //
 * @property int    $type // 1文本 2图片 3文件 4语音
 * @property int    $groupId //
 * @property int    $fromUserId //
 */
class GroupMsgModel extends BaseModel
{
    protected $tableName = 'chat_group_msg_list';

    const TYPE_TEXT = 1;
    const TYPE_IMG = 2;
    const TYPE_FILE = 3;
    const TYPE_VOICE = 4;
    const TYPE_URL_PATH = 5;
    const TYPE_SPEAK_FORBIDDEN = 6;
    const TYPE_DELETE = 7;


    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }


    function addMsg($groupId, $userId, $msg, $type)
    {
        $model = new GroupMsgModel();
        $model->groupId = $groupId;
        $model->fromUserId = $userId;
        $model->msg = $msg;
        $model->type = $type;
        $model->time = time();
        $model->save();
        return $model;
    }


    function getMsgListByMsgId($groupId, $operate, $msgId = null, $limit = 10)
    {
        if ($msgId !== null) {
            if ($operate == 'after') {
                $this->where('msgId', $msgId, '>');
            } else {
                $this->where('msgId', $msgId, '<');
            }
        }
        $this->where('groupId', $groupId, 'in');

        $list = $this
            ->with(['groupInfo', 'fromUserInfo'])
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field("*")
            ->limit($limit)
            ->all();
        return $list;
    }


    function groupInfo()
    {
        return $this->hasOne(GroupMsgModel::class, function (QueryBuilder $builder) {
            return $builder;
        }, 'groupId', 'groupId');
    }

    function fromUserInfo()
    {
        return $this->hasOne(UserModel::class, function (QueryBuilder $builder) {
            $fieldArr = ['userId', 'account', 'username', 'avatar'];
            return $builder->fields(implode(',', $fieldArr));
        }, 'fromUserId', 'userId');
    }

}

