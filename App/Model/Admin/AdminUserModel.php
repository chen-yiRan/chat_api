<?php
namespace App\Model\Admin;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * Class AdminUserModel
 * @package App\Model\Admin
 * @property int    $adminId // id
 * @property string $adminName // 昵称
 * @property string $adminAccount // 账号
 * @property string $adminPassword // 密码
 * @property int    $addTime // 创建时间
 * @property int    $lastLoginTime // 上次登陆的时间
 * @property string $lastLoginIp // 上次登陆的Ip
 * @property string $adminSession //
 */
class AdminUserModel extends BaseModel
{
    protected $tableName = 'admin_list';

    public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(),'DESC')
            ->field($field)
            ->getPageList($page, $pageSize);
        return $listBean;
    }

    static function hashPassword($password)
    {
        return md5($password);
    }
    function logout()
    {
        return $this->update(['adminSession' => '']);
    }
    function addAdmin(string $adminAccount, string $adminPassword, string $adminName)
    {
        $model = new AdminUserModel();
        $model->adminAccount = $adminAccount;
        $model->adminPassword = $adminPassword;
        $model->adminName = $adminName;
        $model->addTime = time();
        $model->adminSession = '';
        $model->lastLoginIp = '';
        $model->lastLoginTime = 0;

        $model->save();
        return $model;
    }
}