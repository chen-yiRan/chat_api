<?php
namespace App\Model\User;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * @property int    $id
 * @property int    $userId  //用户id
 * @property int    $toUserId //被拉黑用户id
 *
 * @property UserModel userInfo
 */
class UserBlacklistModel extends BaseModel
{
    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }
}