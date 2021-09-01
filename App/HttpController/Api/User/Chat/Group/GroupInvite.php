<?php

namespace App\HttpController\Api\User\Chat\Group;

use App\HttpController\Api\User\Base;
use App\Model\Chat\Group\GroupInviteModel;
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
 * 暂时作废
 * GroupInvite
 * Class GroupInvite
 * Create With ClassGeneration
 * @ApiGroup(groupName="/Api/User/Chat/Group.GroupInvite")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("暂时作废")
 */
class GroupInvite extends Base
{
//	/**
//	 * @Api(name="add",path="/Api/User/Chat/Group/GroupInvite/add")
//	 * @ApiDescription("新增数据")
//	 * @Method(allow={GET,POST})
//	 * @InjectParamsContext(key="param")
//	 * @ApiSuccessParam(name="code",description="状态码")
//	 * @ApiSuccessParam(name="result",description="api请求结果")
//	 * @ApiSuccessParam(name="msg",description="api提示信息")
//	 * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
//	 * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
//	 * @Param(name="inviteId",required="")
//	 * @Param(name="groupId",alias="群id",description="群id",required="")
//	 * @Param(name="fromUserId",alias="邀请人id",description="邀请人id",required="")
//	 * @Param(name="toUserId",alias="被邀请人id",description="被邀请人id",required="")
//	 * @Param(name="inviteMsg",alias="邀请人附带信息",description="邀请人附带信息",lengthMax="255",required="")
//	 * @Param(name="addTime",alias="邀请时间",description="邀请时间",required="")
//	 * @Param(name="updateTime",alias="最后更新时间",description="最后更新时间",required="")
//	 * @Param(name="status",alias="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群",description="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群",lengthMax="1",required="")
//	 */
//	public function add()
//	{
//		$param = ContextManager::getInstance()->get('param');
//		$data = [
//		    'inviteId'=>$param['inviteId'],
//		    'groupId'=>$param['groupId'],
//		    'fromUserId'=>$param['fromUserId'],
//		    'toUserId'=>$param['toUserId'],
//		    'inviteMsg'=>$param['inviteMsg'],
//		    'addTime'=>$param['addTime'],
//		    'updateTime'=>$param['updateTime'],
//		    'status'=>$param['status'],
//		];
//		$model = new GroupInviteModel($data);
//		$model->save();
//		$this->writeJson(Status::CODE_OK, $model->toArray(), "新增成功");
//	}
//
//
//	/**
//	 * @Api(name="update",path="/Api/User/Chat/Group/GroupInvite/update")
//	 * @ApiDescription("更新数据")
//	 * @Method(allow={GET,POST})
//	 * @InjectParamsContext(key="param")
//	 * @ApiSuccessParam(name="code",description="状态码")
//	 * @ApiSuccessParam(name="result",description="api请求结果")
//	 * @ApiSuccessParam(name="msg",description="api提示信息")
//	 * @ApiSuccess({"code":200,"result":[],"msg":"更新成功"})
//	 * @ApiFail({"code":400,"result":[],"msg":"更新失败"})
//	 * @Param(name="inviteId",required="")
//	 * @Param(name="groupId",alias="群id",description="群id",optional="")
//	 * @Param(name="fromUserId",alias="邀请人id",description="邀请人id",optional="")
//	 * @Param(name="toUserId",alias="被邀请人id",description="被邀请人id",optional="")
//	 * @Param(name="inviteMsg",alias="邀请人附带信息",description="邀请人附带信息",lengthMax="255",optional="")
//	 * @Param(name="addTime",alias="邀请时间",description="邀请时间",optional="")
//	 * @Param(name="updateTime",alias="最后更新时间",description="最后更新时间",optional="")
//	 * @Param(name="status",alias="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群",description="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群",lengthMax="1",optional="")
//	 */
//	public function update()
//	{
//		$param = ContextManager::getInstance()->get('param');
//		$model = new GroupInviteModel();
//		$info = $model->get(['inviteId' => $param['inviteId']]);
//		if (empty($info)) {
//		    $this->writeJson(Status::CODE_BAD_REQUEST, [], '该数据不存在');
//		    return false;
//		}
//		$updateData = [];
//
//		$updateData['groupId']=$param['groupId'] ?? $info->groupId;
//		$updateData['fromUserId']=$param['fromUserId'] ?? $info->fromUserId;
//		$updateData['toUserId']=$param['toUserId'] ?? $info->toUserId;
//		$updateData['inviteMsg']=$param['inviteMsg'] ?? $info->inviteMsg;
//		$updateData['addTime']=$param['addTime'] ?? $info->addTime;
//		$updateData['updateTime']=$param['updateTime'] ?? $info->updateTime;
//		$updateData['status']=$param['status'] ?? $info->status;
//		$info->update($updateData);
//		$this->writeJson(Status::CODE_OK, $info, "更新数据成功");
//	}
//
//
//	/**
//	 * @Api(name="getOne",path="/Api/User/Chat/Group/GroupInvite/getOne")
//	 * @ApiDescription("获取一条数据")
//	 * @Method(allow={GET,POST})
//	 * @InjectParamsContext(key="param")
//	 * @ApiSuccessParam(name="code",description="状态码")
//	 * @ApiSuccessParam(name="result",description="api请求结果")
//	 * @ApiSuccessParam(name="msg",description="api提示信息")
//	 * @ApiSuccess({"code":200,"result":[],"msg":"获取成功"})
//	 * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
//	 * @Param(name="inviteId",required="")
//	 * @ApiSuccessParam(name="result.inviteId",description="")
//	 * @ApiSuccessParam(name="result.groupId",description="群id")
//	 * @ApiSuccessParam(name="result.fromUserId",description="邀请人id")
//	 * @ApiSuccessParam(name="result.toUserId",description="被邀请人id")
//	 * @ApiSuccessParam(name="result.inviteMsg",description="邀请人附带信息")
//	 * @ApiSuccessParam(name="result.addTime",description="邀请时间")
//	 * @ApiSuccessParam(name="result.updateTime",description="最后更新时间")
//	 * @ApiSuccessParam(name="result.status",description="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群")
//	 */
//	public function getOne()
//	{
//		$param = ContextManager::getInstance()->get('param');
//		$model = new GroupInviteModel();
//		$info = $model->get(['inviteId' => $param['inviteId']]);
//		if ($info) {
//		    $this->writeJson(Status::CODE_OK, $info, "获取数据成功.");
//		} else {
//		    $this->writeJson(Status::CODE_BAD_REQUEST, [], '数据不存在');
//		}
//	}
//
//
//	/**
//	 * @Api(name="getList",path="/Api/User/Chat/Group/GroupInvite/getList")
//	 * @ApiDescription("获取数据列表")
//	 * @Method(allow={GET,POST})
//	 * @InjectParamsContext(key="param")
//	 * @ApiSuccessParam(name="code",description="状态码")
//	 * @ApiSuccessParam(name="result",description="api请求结果")
//	 * @ApiSuccessParam(name="msg",description="api提示信息")
//	 * @ApiSuccess({"code":200,"result":[],"msg":"获取成功"})
//	 * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
//	 * @Param(name="page", from={GET,POST}, alias="页数", optional="")
//	 * @Param(name="pageSize", from={GET,POST}, alias="每页总数", optional="")
//	 * @ApiSuccessParam(name="result[].inviteId",description="")
//	 * @ApiSuccessParam(name="result[].groupId",description="群id")
//	 * @ApiSuccessParam(name="result[].fromUserId",description="邀请人id")
//	 * @ApiSuccessParam(name="result[].toUserId",description="被邀请人id")
//	 * @ApiSuccessParam(name="result[].inviteMsg",description="邀请人附带信息")
//	 * @ApiSuccessParam(name="result[].addTime",description="邀请时间")
//	 * @ApiSuccessParam(name="result[].updateTime",description="最后更新时间")
//	 * @ApiSuccessParam(name="result[].status",description="-2管理员已拒绝 -1被邀请人拒绝 0 待被邀请人同意 1被邀请人已同意待管理员同意,2管理员同意已进群")
//	 */
//	public function getList()
//	{
//		$param = ContextManager::getInstance()->get('param');
//		$page = (int)($param['page'] ?? 1);
//		$pageSize = (int)($param['pageSize'] ?? 20);
//		$model = new GroupInviteModel();
//		$data = $model->getList($page, $pageSize);
//		$this->writeJson(Status::CODE_OK, $data, '获取列表成功');
//	}

}

