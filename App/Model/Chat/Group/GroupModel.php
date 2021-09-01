<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * GroupModel
 * Class GroupModel
 * Create With ClassGeneration
 * @property int    $groupId //
 * @property string $groupHash //
 * @property int    $groupNum //
 * @property string $groupName //
 * @property int    $belongToUserId // 群主
 * @property string $groupThumb //
 * @property string $groupNotice // 群公告
 * @property int    $groupSpeakState // 是否全群禁言
 * @property int    $isDelete // 是否删除
 * @property int    $deleteTime // 删除时间
 */
class GroupModel extends BaseModel
{
    protected $tableName = 'chat_group_list';

    const SPEAK_STATE_NORMAL = 1;
    const SPEAK_STATE_FORBIDDEN = 0;
    const DELETE_NORMAL = 0;
    const DELETE_DELETE = 1;

    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    function getOneByGroupHash($groupHash)
    {
        return $this->get(['groupHash' => $groupHash]);
    }



    function addGroup(string $groupName, int $userId)
    {
        $model = new GroupModel();
        $model->groupHash = substr(md5(uniqid()), 8, 16);
        $model->groupNum = 0;
        $model->groupName = $groupName;
        $model->belongToUserId = $userId;
        $model->groupSpeakState = self::SPEAK_STATE_NORMAL;
        $model->isDelete = self::DELETE_NORMAL;
        $model->deleteTime = 0;
        $model->save();
        return $model;
    }

    function getManagerGroupList($manageUserId)
    {
        $this->where('groupUser.userId', $manageUserId);
        $this->where('groupUser.isManager', GroupUserModel::MANAGER);
        $this->where('groupUser.state', GroupUserModel::STATE_NORMAL);
        $list = $this
            ->withTotalCount()
            ->alias('group')
            ->join('group_user_list as groupUser', 'groupUser.groupId= group.groupId')
            ->all();
        return $list;
    }

    /**
     * 解散群聊
     * dismiss
     * @author tioncico
     * Time: 上午10:03
     */
    public function dismiss(GroupModel $groupInfo)
    {
        $update = [
            'isDelete'   => self::DELETE_DELETE,
            'deleteTime' => time()
        ];
        $groupInfo->update($update);
        return $groupInfo;
    }

    protected function getGroupThumbAttr($value, $data)
    {
//        $systemResourceModel = new SystemResourceModel();
//        $systemInfo = $systemResourceModel->get($value);
        return $systemInfo->path ?? null;
    }

}

