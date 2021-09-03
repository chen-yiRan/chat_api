<?php

namespace App\HttpController\Api\User\User;


use App\HttpController\Api\User\UserBase;
use App\Model\User\UserDetailModel;
use App\Service\User\UserService;
use App\Utility\QRCode\QRCodeRule;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;

/**
 * Class UserInfo
 * @package App\HttpController\Api\User\User
 * @ApiGroup (groupName="用户详细信息添加、添加、修改")
 * @ApiGroupDescription ("用户详细信息添加、查询、修改")
 * @ApiGroupAuth (name="user_token",from={POST,GET,COOKIE},required="",description="访问这里的接口需要用户登录后，服务端返回的TOKEN")
 */
class UserInfo extends UserBase
{
    /**
     * @Api(name="获得用户自己的详细信息",path="/Api/User/User/UserInfo/getDetail")
     */
    public function getDetail()
    {
        $userId = $this->who()->userId;
        //如果不存在用户详情，就新增
        $userDetailInfo = UserDetailModel::create()->where(['userId' => $userId])->get();
        if(empty($userDetailInfo)){
            UserService::getInstance()->checkDetail($this->who()->userId);
        }
        $detailInfo = UserService::getInstance()->getUserBaseInfo($userId);
        $this->writeJson(Status::CODE_OK,$detailInfo,'success');
    }

    public function myQRCodeStr()
    {
        $data = [
            'userId' => $this->who()->userId
        ];
        $myQRCode = new QRCodeRule($data);
        $str = $myQRCode->str(QRCodeRule::MY_QR_CODE);
        $this->writeJson(Status::CODE_OK, [
            'str' => $str
        ], 'success');
    }
}