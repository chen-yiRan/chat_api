<?php

namespace App\HttpController\Api\User\Chat\Group;

use App\HttpController\Api\User\Base;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Utility\Exception\BusinessException;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccessParam;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

/**
 * GroupUser
 * Class GroupUser
 * Create With ClassGeneration
 * @ApiGroup(groupName="群成员管理")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("")
 */
class GroupUser extends Base
{
    /**
     * @Api(name="更新自己的群成员备注",path="/Api/User/Chat/Group/GroupUser/update")
     * @ApiDescription("更新自己的群成员备注")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":{"groupUserId":3,"groupId":4,"groupUserHash":"93d1ac5d7a9f3b75","userId":125817,"isManager":0,"lastMsgId":0,"lastReadMsgId":0,"unreadCount":0,"state":1,"remark":"津津自喜","lastMsgTime":0,"speakState":1,"speakForbiddenTime":null,"receiveMsgType":1},"msg":"更新数据成功"})
     * @ApiFail({"code":400,"result":[],"msg":"更新失败"})
     * @Param(name="groupHash",required="")
     * @Param(name="remark",alias="群成员备注",description="群成员备注",lengthMax="32",required="")
     */
    public function updateRemark()
    {
        $param = ContextManager::getInstance()->get('param');
        $groupInfo = GroupModel::create()->getOneByGroupHash($param['groupHash']);
        if (empty($groupInfo) || $groupInfo->isDelete == $groupInfo::DELETE_DELETE) {
            throw new BusinessException("群组数据不存在!");
        }

        $info = GroupUserModel::create()->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
        if (empty($info)) {
            throw new BusinessException("你不在该群组!");
        }

        $updateData = [];

        $updateData['remark'] = $param['remark'] ?? $info->remark;
        $info->update($updateData);
        $this->writeJson(Status::CODE_OK, $info, "更新数据成功");
    }


    /**
     * @Api(name="获取一条群成员信息",path="/Api/User/Chat/Group/GroupUser/getOne")
     * @ApiDescription("获取一条群成员信息")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":{"groupUserId":4,"groupId":5,"groupUserHash":"64a5e0b5db58118b","userId":125817,"isManager":0,"lastMsgId":0,"lastReadMsgId":0,"unreadCount":0,"state":1,"remark":null,"lastMsgTime":0,"speakState":1,"speakForbiddenTime":null,"receiveMsgType":1},"msg":"获取数据成功."})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="groupUserHash",required="")
     * @ApiSuccessParam(name="result.groupUserId",description="")
     * @ApiSuccessParam(name="result.groupId",description="")
     * @ApiSuccessParam(name="result.groupUserHash",description="")
     * @ApiSuccessParam(name="result.userId",description="")
     * @ApiSuccessParam(name="result.isManager",description="")
     * @ApiSuccessParam(name="result.lastMsgId",description="")
     * @ApiSuccessParam(name="result.lastReadMsgId",description="")
     * @ApiSuccessParam(name="result.unreadCount",description="")
     * @ApiSuccessParam(name="result.state",description="群员状态1正常,0已被删除")
     * @ApiSuccessParam(name="result.remark",description="群成员备注")
     * @ApiSuccessParam(name="result.lastMsgTime",description="最后消息发送时间")
     * @ApiSuccessParam(name="result.speakState",description="发言状态,1正常,2禁言")
     * @ApiSuccessParam(name="result.speakForbiddenTime",description="禁言时间,只有当speakState=0时才有效")
     * @ApiSuccessParam(name="result.receiveMsgType",description="接收消息状态")
     */
    public function getOne()
    {
        $param = ContextManager::getInstance()->get('param');
        $model = new GroupUserModel();
        $info = $model->getOneByGroupUserHash($param['groupUserHash']);
        if (empty($info)) {
            throw new BusinessException("群成员不存在");
        }
        //获取自己在群里的数据
        $myGroupUserInfo = $model->getOneByGroupIdAndUserId($info->groupId, $info->userId);
        if (empty($myGroupUserInfo)) {
            throw new BusinessException("群成员不存在");
        }
        $this->writeJson(Status::CODE_OK, $info, "获取数据成功.");
    }


    /**
     * @Api(name="获取群成员列表",path="/Api/User/Chat/Group/GroupUser/getList")
     * @ApiDescription("获取群成员列表")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":{"page":1,"pageSize":9999,"list":[{"groupUserId":12,"groupId":13,"groupUserHash":"22c6de6ff0bd9d73","userId":125817,"isManager":0,"lastMsgId":0,"lastReadMsgId":0,"unreadCount":0,"state":1,"remark":null,"lastMsgTime":0,"speakState":1,"speakForbiddenTime":null,"receiveMsgType":1,"userInfo":{"userId":125817,"account":"lh","username":"lh","avatar":"https:\/\/fs-zuola.oss-cn-beijing.aliyuncs.com\/userAvatar\/20210412\/15\/11\/6073f2895e21235628.jpg"}}],"total":1,"pageCount":1},"msg":"获取列表成功"})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="groupHash",required="")
     * @ApiSuccessParam(name="result[].groupUserId",description="")
     * @ApiSuccessParam(name="result[].groupId",description="")
     * @ApiSuccessParam(name="result[].groupUserHash",description="")
     * @ApiSuccessParam(name="result[].userId",description="")
     * @ApiSuccessParam(name="result[].isManager",description="")
     * @ApiSuccessParam(name="result[].lastMsgId",description="")
     * @ApiSuccessParam(name="result[].lastReadMsgId",description="")
     * @ApiSuccessParam(name="result[].unreadCount",description="")
     * @ApiSuccessParam(name="result[].state",description="群员状态1正常,0已被删除")
     * @ApiSuccessParam(name="result[].remark",description="群成员备注")
     * @ApiSuccessParam(name="result[].lastMsgTime",description="最后消息发送时间")
     * @ApiSuccessParam(name="result[].speakState",description="发言状态,1正常,2禁言")
     * @ApiSuccessParam(name="result[].speakForbiddenTime",description="禁言时间,只有当speakState=0时才有效")
     * @ApiSuccessParam(name="result[].receiveMsgType",description="接收消息状态")
     */
    public function getList()
    {
        $param = ContextManager::getInstance()->get('param');
        $groupInfo = GroupModel::create()->getOneByGroupHash($param['groupHash']);
        if (empty($groupInfo) || $groupInfo->isDelete == $groupInfo::DELETE_DELETE) {
            throw new BusinessException("群组数据不存在!");
        }

        $info = GroupUserModel::create()->getOneByGroupIdAndUserId($groupInfo->groupId, $this->who->userId);
        if (empty($info)) {
            throw new BusinessException("你不在该群组!");
        }

        $model = new GroupUserModel();
        $data = $model
            ->with(['userInfo'],false)
            ->where('groupId',$groupInfo->groupId)
            ->getList(1, 9999);
        $this->writeJson(Status::CODE_OK, $data, '获取列表成功');
    }


    /**
     * @Api(name="将群员移出群",path="/Api/User/Chat/Group/GroupUser/delete")
     * @ApiDescription("将群员移出群")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="groupHash",required="")
     * @Param(name="userId",required="")
     */
//	public function delete()
//	{
//		$param = ContextManager::getInstance()->get('param');
//        $groupInfo  = GroupModel::create()->getOneByGroupHash($param['groupHash']);
//        if (empty($groupInfo)||$groupInfo->isDelete==$groupInfo::DELETE_DELETE){
//            throw new BusinessException("群组数据不存在!");
//        }
//        $info  = GroupUserModel::create()->getOneByGroupIdAndUserId($groupInfo->groupId,$this->who->userId);
//        if (empty($info)||$info->isManager==$info::NORMAL){
//            throw new BusinessException("你不是管理员.");
//        }
//
//        $info  = GroupUserModel::create()->getOneByGroupIdAndUserId($groupInfo->groupId,$param['userId']);
//        if (empty($info)){
//            throw new BusinessException("该群员不存在.");
//        }
//       if ($info->isManager==$info::MANAGER&&$groupInfo->belongToUserId==$this->who->userId){
//            throw new BusinessException("该群员是管理,不能移出.");
//        }
//        $info->delGroupUser($info);
//
//		$this->writeJson(Status::CODE_OK, [], "移出成功.");
//	}
}

