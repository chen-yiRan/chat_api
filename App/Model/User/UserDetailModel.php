<?php
namespace App\Model\User;
use App\Model\BaseModel;
use EasySwoole\ORM\AbstractModel;

/**
 * @property int    $id
 * @property int    $userId  用户id
 * @property string $introduction  简介
 * @property string $birthday  年龄
 * @property int    $sex  不显示:0,男:1,女:2
 * @property int    $areaCode  地区code
 * @property int    $isDelete  0:未删除1:删除
 * @property int    $deleteTime  删除时间
 * @property string $background  背景图
 *
 * Class UserDetailModel
 * @package App\Model\User
 */
class UserDetailModel extends BaseModel
{
    protected $tableName = 'user_detail_list';

    const DELETE_DELETE = 1;
    const DELETE_NORMAL = 0;
    public function getUserDetail($userId)
    {
        $userInfo = UserDetailModel::create()->get(['userId' => $userId]);
        if ($userInfo) {
            return $userInfo;
        } else {
            $info = $this->addUserDetail($userId);
            return $info;
        }
    }


    function addUserDetail($userId, ?array $data = [])
    {
        $model = new UserDetailModel();
        $model->userId = $userId;
        $model->introduction = $data['introduction'] ?? '';
        $model->birthday = $data['birthday'] ?? null;
        $model->sex = $data['sex'] ?? 0;
        $model->areaCode = $data['areaCode'] ?? '';
        $model->isDelete = self::DELETE_NORMAL;
        $model->deleteTime = 0;
        $model->save();

        return $model;
    }
}