<?php
/**
 * Created by PhpStorm.
 * User: xdd
 * Date: 2020/8/21
 * Time: 13:50
 */

namespace App\HttpController\Api\Admin\User;

use App\HttpController\Api\Admin\AdminBase;
use App\Model\User\UserModel;
use App\Model\User\UserDetailModel;
use App\Service\Common\AreaService;
use App\Service\Common\OssService;
use App\Utility\Assert\Assert;
use App\Utility\Bean\ListBean;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * Class User
 * @package App\HttpController\Api\Admin\User
 * @ApiGroup(groupName="后台用户管理接口")
 * @ApiGroupDescription("后台用户管理接口")
 * @ApiGroupAuth(name="adminSession",from={POST,GET,COOKIE},required="",description="访问这里的接口需要用户登录后，服务端返回的TOKEN")
 */
class User extends AdminBase
{
    /**
     * @Api(name="用户列表及搜索",path="/Api/Admin/User/User/getList")
     * @ApiDescription("用户列表及搜索")
     * @Param(name="page",optional="",description="page从1开始",min="1")
     * @Param(name="limit",max="50",optional="",description="每页总数")
     * @Param(name="keyword",optional="",description="用户账号/昵称关键字")
     * @Param(name="phone",optional="",description="手机号码搜索条件")
     * @Param(name="isForbid",optional="",inArray={0,1},description="用户状态搜索条件,是否被禁,0:未被禁,1:被禁")
     * @Param(name="isDelete",optional="",inArray={0,1},description="用户状态搜索条件,是否被删除,0:未,1:被删除")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/User/User/getList?keyword=s&phone=8&isForbid=1")
     * @ApiSuccess({"code":200,"result":{"page":1,"pageSize":10,"list":[{"userId":52,"account":"UserAddTest","username":"UserAddTest","password":null,"phone":"15885215685","avatar":"http://fs-laboom.oss-cn-beijing.aliyuncs.com/https://imgsa.baidu.com/forum/w=580/sign=2092b2c7e8fe9925cb0c695804a95ee4/bd4c162fb9389b5055794a508d35e5dde6116e48.jpg","createTime":1598862591,"isDelete":1,"deleteTime":1598863518,"isForbid":1,"accountTime":0,"fansCount":0,"followCount":0,"favoriteCount":0}],"total":1,"pageCount":1},"msg":"用户列表"})
     * @author xdd
     * Time: 14:02
     */
    function getList($page, $limit, $keyword, $phone, $isForbid, $isDelete)
    {
        $model = new UserModel();
        if (!empty($keyword)) {
            $model->where("(`account` like ? or `username` like ?)", ["%$keyword%", "%$keyword%"]);
        }
        if (!empty($phone)) {
            $model->where('phone', "%" . $phone . "%", 'like');
        }
        if (!is_null($isForbid)) {
            $model->where(['isForbid' => $isForbid]);
        }
        if (!is_null($isDelete)) {
            $model->where(['isDelete' => $isDelete]);
        }

        /**
         * @var $listBean ListBean
         */
        $listBean = $model->where('isDelete', $model::DELETE_NORMAL)->getList($page ?? 1, $limit ?? 10);
        //屏蔽字段数据
        $listBean->listChunk(function (UserModel $value) {
            $value->password = null;
            return $value;
        });
        $this->writeJson(Status::CODE_OK, $listBean, "用户列表");
    }

    /**
     * @Api(name="获得用户详情信息",path="/Api/Admin/User/User/getDetailInfo")
     * @ApiDescription("获得用户详情信息")
     * @Param(name="userId",required="",description="用户id")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/User/User/getDetailInfo?userId=3")
     * @ApiSuccess({"code":200,"result":{"id":1,"userId":3,"introduction":"ss","birthday":123123,"school":null,"sex":null,"areaId":null,"idNum":null,"realName":null,"isDelete":0,"deleteTime":null},"msg":"success"})
     * @author xdd
     * Time: 11:51
     */
    function getDetailInfo($userId)
    {
        $model = new UserModel();

        UserDetailModel::create()->getUserDetail($userId);
        $fields = [
            'user_list.*',
            'user_detail_list.introduction',
            'user_detail_list.birthday',
            'user_detail_list.sex',
            'user_detail_list.areaCode',

        ];
        $result = $model->join('user_detail_list', 'user_detail_list.userId=' . $userId,'left')
            ->where(['user_list.userId'=>$userId])
            ->field($fields)
            ->get();
        Assert::assert(!!$result, '未查询到信息');
        $areaService = AreaService::getInstance();
        if (!empty($result->areaCode)){
            $areaInfo = $areaService->getNameByCode($result->areaCode);
            //地名拼接成一条
            $areaName = $areaService->spliceAreaName($areaInfo);
            $result['areaName'] = $areaName;
        }
        $this->writeJson(Status::CODE_OK, $result, "success");
    }


    /**
     * @Api(name="用户软删除",path="/Api/Admin/User/User/delete")
     * @ApiDescription("用户软删除")
     * @Param(name="userId",required="",description="用户id")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/User/User/delete?userId=8")
     * @ApiSuccess({"code":200,"result":null,"msg":"success"})
     * @author xdd
     * Time: 14:02
     */
    function delete($userId)
    {
        $model = new UserModel();
        $detailModel = new UserDetailModel();
        $user = $model->get(['userId' => $userId]);
        Assert::assert(!!$user, '用户数据不存在');
        $data = [
            'isDelete'   => UserModel::DELETE_DELETE,
            'deleteTime' => time()
        ];
        $user->update($data);

        $userDetail = $detailModel->get(['userId' => $userId]);
        if ($userDetail) {
            $userDetail->update($data);
        }
        $this->writeJson(Status::CODE_OK, null, "success");
    }

    /**
     * @Api(name="更新用户信息",path="/Api/Admin/User/User/update")
     * @ApiDescription("更新用户信息")
     * @Param(name="userId",required="",description="用户id")
     * @Param(name="username",optional="",description="昵称")
     * @Param(name="phone",optional="",description="手机号")
     * @Param(name="avatar",optional="",description="头像")
     * @Param(name="isForbid",optional="",inArray={0,1},description="是否被禁,0:未被禁,1:被禁")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/User/User/update?userId=6&account=zxc&password=123456&username=zxc&phone=180561581258&avatar=img&isForbid=0")
     * @ApiSuccess({"code":200,"result":true,"msg":"success"})
     * @ApiFail({"code":400,"result":null,"msg":"数据不存在"})
     * @author xdd
     * Time: 14:02
     */
    function update($userId, $username, $phone, $avatar, $isForbid)
    {
        $user = UserModel::create()->get(['userId' => $userId]);
        Assert::assert(!!$user, '数据不存在');
        $img = OssService::moveFilePath($avatar, OssService::FILE_TYPE_USER_AVATAR);
        $oldAvatar = $user->getOriginData()['avatar'] ?? '';
        $data = [
            'username' => $username ?? $user->username,
            'phone'    => $phone ?? $user->phone,
            'isForbid' => $isForbid ?? $user->isForbid,
        ];
        if ($img) {
            $data['avatar'] = $img;
        }
        $result = $user->update($data);
        if ($img) {
            OssService::delTempFile($avatar);
            OssService::delFile($oldAvatar);
        }
        $this->writeJson(Status::CODE_OK, $result, 'success');
    }

    /**
     * @Api(name="更新用户密码",path="/Api/Admin/User/User/updatePassword")
     * @ApiDescription("更新用户密码")
     * @Param(name="userId",required="",description="用户id")
     * @Param(name="password",required="",description="密码")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/User/User/updatePassword")
     * @ApiSuccess({"code":200,"result":true,"msg":"success"})
     * @author xdd
     * Time: 16:29
     */
    function updatePassword($userId, $password)
    {
        $bean = UserModel::create()->get(['userId' => $userId]);
        Assert::assert(!!$bean, '数据不存在');
        $data = [
            'password' => !empty($password) ? md5($password) : $bean->password
        ];
        $result = $bean->update($data);
        $this->writeJson(Status::CODE_OK, $result, 'success');
    }

//    /**
//     * @Api(name="增加用户",path="/Api/Admin/User/User/add")
//     * @ApiDescription("增加用户")
//     * @Param(name="username",optional="",description="昵称")
//     * @Param (name="password",description="密码")
//     * @Param(name="phone",optional="",description="手机号")
//     * @Param(name="avatar",optional="",description="头像地址")
//     * @Param(name="isForbid",optional="",inArray={0,1},description="是否被禁,0:未被禁,1:被禁")
//     * @InjectParamsContext (key = "param")
//     * @ApiSuccess({"code":200,"result":true,"msg":"success"})
//     * @ApiFail({"code":400,"result":null,"msg":"数据不存在"})
//     * @author chen
//     * Time: 14:02
//     */
//    public function add(){
//        $param = ContextManager::getInstance()->get('param');
//        $avatar = OssService::moveFilePath($param['avatar'],OssService::FILE_TYPE_USER_AVATAR);
//        $data = [
//            'username' => $param['username'],
//            'password' => md5($param['password']),
//            'phone' => $param['phone'],
//            'avatar' => $avatar,
//            'createTime' => time(),
//            'isForbid' => UserModel::FORBID_NORMAL
//        ];
//        OssService::delTempFile($param[$avatar]);
//        $model = new UserModel($data);
//        $model->save();
//        $this->writeJson(Status::CODE_OK, $model->toArray(), 'success');
//    }

}