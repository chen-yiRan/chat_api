<?php
namespace App\HttpController\Api\Admin;

use App\Model\Admin\AdminUserModel;
use App\Utility\Assert\Assert;
use App\Utility\Bean\ListBean;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\ORM\AbstractModel;

/**
 * Class AdminUser
 * @package App\HttpController\Api\Admin
 * @ApiGroup(groupName="后台管理员管理接口")
 * @ApiGroupDescription("后台管理员管理接口")
 * @ApiGroupAuth(name="adminSession",from={POST,GET,COOKIE},required="",description="访问这里的接口需要用户登录后，服务端返回的TOKEN")
 */
class AdminUser extends AdminBase
{
    /**
     * @Api(name="后台管理员列表及搜索",path="/Api/Admin/AdminUser/getList")
     * @ApiDescription ("后台管理员列表及搜索")
     * @param (name="page",description="页数")
     * @param (name="limit",description="每页总数")
     * @param (name="keyword",description="用户账号关键字")
     */
    function getList($page,$limit,$keyword)
    {
        $model = new AdminUserModel();
        if(!empty($keyword)){
            $model->where('adminAccount',"%" . $keyword . "%", 'like');
        }
        /**
         * @var $listBean ListBean
         */
        $listBean = $model->getList($page ?? 1, $limit ?? 10);
        //屏蔽字段数据
        $listBean->listChunk(function (AdminUserModel $value){
            $value->adminPassword = null;
            return $value;
        });
        $this->writeJson(Status::CODE_OK,$listBean,"后台管理员列表");
    }

    /**
     * @Api(name="添加后台管理人",path="/api/admin/adminUser/add")
     * @ApiDescription("添加后台管理人")
     * @Param(name="adminName",required="",description="昵称")
     * @Param(name="adminAccount",required="",description="账号")
     * @Param(name="adminPassword",required="",description="密码")
     * @ApiSuccess({"code":200,"result":3,"msg":"success"})
     * @author xdd
     * Time: 16:43.
     */
    function add($adminName, $adminAccount, $adminPassword)
    {
        $model = new AdminUserModel();
        $admin = $model->where(['adminAccount' => $adminAccount])->get();
        Assert::assert(!$admin,'账号已存在');
        $model->addAdmin($adminAccount,$adminPassword,$adminName);
        $this->writeJson(Status::CODE_OK,$model,'success');
    }

    /**
     * @Api(name="删除后台管理员",path="/api/admin/adminUser/delete")
     * @ApiDescription("删除后台管理员")
     * @Param(name="adminId",required="",description="管理员id")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/AdminUser/delete?adminId=3")
     * @ApiSuccess({"code":200,"result":1,"msg":"success"})
     * @author xdd
     * Time: 16:43
     */
    function delete($adminId)
    {
        $model = new AdminUserModel();
        $admin = $model->get(['adminId' => $adminId]);
        Assert::assert(!!$admin, '数据不存在');
        $admin->destroy();
        $this->writeJson(Status::CODE_OK, null, "success");
    }

    /**
     * @Api(name="管理员信息修改",path="/api/admin/adminUser/update")
     * @ApiDescription("管理员信息修改")
     * @Param(name="adminId",required="",description="账号")
     * @Param(name="adminName",optional="",description="昵称")
     * @Param(name="adminPassword",optional="",description="密码")
     * @ApiRequestExample("curl http://192.168.0.206:9501/Api/Admin/AdminUser/update?name=xcz&password=666666&adminId=3")
     * @ApiSuccess({"code":200,"result":true,"msg":"success"})
     * @author xdd
     * Time: 16:43
     */
    function update($adminId, $adminName, $adminPassword)
    {
        $bean = AdminUserModel::create()->get(['adminId' => $adminId]);
        Assert::assert(!!$bean, '数据不存在');
        $data = [
            'adminName'     => $adminName ?? $bean->adminName,
            'adminPassword' => !empty($adminPassword) ? AdminUserModel::hashPassword($adminPassword) : $bean->adminPassword,
        ];
        $bean->update($data);
        $this->writeJson(Status::CODE_OK, $bean, 'success');
    }
}