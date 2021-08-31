<?php
namespace App\Model\User;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;


/**
 * Class UserModel
 * @package App\Model\User
 *
 * @property int                  $userId //
 * @property string               $account // 账号
 * @property string               $username // 昵称
 * @property string               $password // 密码
 * @property string               $phone // 手机号
 * @property string               $avatar // 头像地址
 * @property int                  $createTime // 创建的时间
 * @property int                  $isDelete // 0:未删除1:已删除
 * @property int                  $deleteTime // 删除时间
 * @property int                  $isForbid // 是否被禁,0:未被禁,1:被禁
 * @property int                  $accountTime // 最后一次修改account时间
 */
class UserModel extends BaseModel{
    const DELETE_DELETE = 1;
    const DELETE_NORMAL = 0;
    const FORBID_FORBID = 1;
    const FORBID_NORMAL = 0;
    const SELLER_NO = 0;
    const SELLER_YES = 1;

    const IS_INNER_NO = 0;
    const IS_INNER_YES = 1;

    protected $tableName = 'user_list';

    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order("user_list.".$this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }
    function addUser(?string $account, string $password, ?string $phone = null, ?string $username = null)
    {
        $model = new UserModel();
        $model->account = $account;
        $model->password = $password;
        $model->phone = $phone;
        $model->username = $username;
        $model['createTime'] = time();
        $model->avatar = null;
        $model->isDelete = self::DELETE_NORMAL;
        $model->deleteTime = null;
        $model->isForbid = self::FORBID_NORMAL;
        $model->accountTime = 0;
        $model->save();
        return $model;
    }
    static function hashPassword($password)
    {
        return md5($password);
    }
}