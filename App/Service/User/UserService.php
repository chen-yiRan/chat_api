<?php
namespace App\Service\User;

use App\Model\User\UserDetailModel;
use App\Model\User\UserModel;
use App\Service\BaseService;
use App\Utility\Cache\UserCache;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Singleton;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\Spl\SplString;

class UserService extends BaseService{
    const SEX_HIDE = 0;
    const SEX_MALE = 1;
    const SEX_FEMALE = 2;

    const DELETE_NORMAL = 0;
    const DELETE_DELETE = 1;
    use Singleton;

    /**
     * @param $session
     * @return int
     */
    public function userId($session){
        $userInfo = UserCache::getInstance()->getBySession($session);
        if($userInfo){
            return $userInfo->userId;
        }
    }

    public function phoneVerify($phone)
    {
        $string = new SplString($phone);
        $reg_tel = '/^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/';
        if($string->regex($reg_tel)){
            return $phone;
        } else {
            throw new BusinessException("请输入正确的手机号码");
        }
    }
    public function addUser($phone, $password = '')
    {
        $userInfo = UserModel::create()->addUser(null, $password, $phone, "用户" . mt_rand(1000000000, 9999999999));
        //将账号更新为id
        $userInfo->update(['account' => $userInfo->userId]);
        //UserPrivacyModel::create()->addUserPrivacy($userInfo->userId);
        UserDetailModel::create()->addUserDetail($userInfo->userId);
        return $userInfo;
    }

    //检查是否存在用户详情，不存在则创建
    public function checkDetail($userId)
    {
        $userDetailModel = UserDetailModel::create();
        $userInfo = $userDetailModel->where('userId', $userId)->get();

        if(!$userInfo){
            $data = [
                'userId' => $userId,
                'introduction' => '',
                'birthday' => null,
                'sex' => self::SEX_HIDE,
                'areaCode' => '',
                'isDelete' => self::DELETE_NORMAL,
                'deleteTime' => 0
            ];
            $userDetailModel->data($data,false)->save();
            return $userDetailModel;
        }
    }
    public function getUserBaseInfo($userId)
    {
        $model = new UserModel();
        $fields = [
            'userId',
            'account',
            'password',
            'phone',
            'username',
            'avatar',
        ];

        $userInfo = $model->where(['userId' => $userId])->field(implode(',', $fields))->get();

        $userDetailInfo = $this->getUserBaseDetailInfo($userId);
        $userDetailInfo = !empty($userDetailInfo) ? $userDetailInfo->toArray() : [];
        $userInfo = array_merge($userInfo->toArray(null, false), $userDetailInfo);

        return $userInfo;
    }
    function getUserBaseDetailInfo($userId)
    {
        $detailFields = [
            'introduction',
            'birthday',
            'sex',
            'areaCode',
        ];
        $userDetailInfo = UserDetailModel::create()->where(['userId' => $userId])->field(implode(',', $detailFields))->get();
        return $userDetailInfo;
    }

}