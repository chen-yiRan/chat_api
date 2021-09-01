<?php

namespace App\Model\Chat\Group;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * GroupSettingModel
 * Class GroupSettingModel
 * Create With ClassGeneration
 * @property int $settingId //
 * @property int $groupId //
 * @property string $key //
 * @property string $value //
 */
class GroupSettingModel extends BaseModel
{
	protected $tableName = 'chat_group_setting';


    const DEFAULT_SETTING = [
        'USER_INVITE'       => 0,//成员邀请功能
        'USER_INVITE_CHECK' => 1,//成员邀请是否需要验证
        'USER_ADD_FRIEND'   => 0,//会员添加好友功能
        'GROUP_URL_SHARE'   => 0,//群分享功能
    ];


	public function getList(int $page = 1, int $pageSize = 10, string $field = '*'):  ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
	}

    function getGroupData($groupId)
    {
        $data = $this->where('groupId', $groupId)->limit(100)->all();
        $list = [];
        /**
         * @var $data GroupSettingModel[]
         */
        foreach ($data as $value) {
            $list[$value->key] = $value->value;
        }
        foreach (self::DEFAULT_SETTING as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
            }
        }
        return $list;
    }

    function addGroupSetting($groupId, $key, $value)
    {
        $model = new GroupSettingModel();
        $model->groupId = $groupId;
        $model->key = $key;
        $model->value = $value;
        $model->save();
        return $model;
    }

    function saveGroupSetting($groupId, $key, $value)
    {
        if (!isset(self::DEFAULT_SETTING[$key])){
            return false;
        }
        $info = $this->get(['groupId' => $groupId, 'key' => $key]);
        if (empty($info)) {
            $info = $this->addGroupSetting($groupId, $key, $value);
        } else {
            $info->update(['value' => $value]);
        }
        return $info;
    }

}

