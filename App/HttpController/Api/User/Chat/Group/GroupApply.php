<?php

namespace App\HttpController\Api\User\Chat\Group;

use App\HttpController\Api\User\Base;
use App\Model\BaseModel;
use App\Model\Chat\Group\GroupApplyModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Service\Chat\Group\GroupApplyService;
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
 * GroupApply
 * Class GroupApply
 * Create With ClassGeneration
 * @ApiGroup(groupName="申请入群管理")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("")
 */
class GroupApply extends Base
{
    /**
     * @Api(name="新增申请加入群数据",path="/Api/User/Chat/Group/GroupApply/add")
     * @ApiDescription("新增申请加入群数据")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="groupHash",required="")
     * @Param(name="applyMsg",lengthMax="255",required="")
     */
//    public function add()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $groupInfo = GroupApplyService::getInstance($this->who)->addApply($param['groupHash'], $param['applyMsg']);
//        $this->writeJson(Status::CODE_OK, $groupInfo->toArray(), "新增成功");
//    }

    /**
     * @Api(name="审核群申请",path="/Api/User/Chat/Group/GroupApply/verify")
     * @ApiDescription("审核群申请")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"更新成功"})
     * @ApiFail({"code":400,"result":[],"msg":"更新失败"})
     * @Param(name="applyId",required="")
     * @Param(name="refuseMsg",lengthMax="255",optional="")
     * @Param(name="status",alias="审核状态",description="1通过,-1拒绝",required="",inArray={-1,1})
     */
//    public function verify()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $groupApplyModel = new GroupApplyModel();
//        $applyInfo = $groupApplyModel->get($param['applyId']);
//        if (empty($applyInfo)) {
//            throw  new BusinessException("群申请记录不存在");
//        }
//        //判断是否是管理员
//        $groupUserModel = new GroupUserModel();
//        $groupUserInfo = $groupUserModel->getOneByGroupIdAndUserId($applyInfo->groupId, $this->who->userId);
//        if (empty($groupUserInfo) || $groupUserInfo->isManager != $groupUserInfo::MANAGER) {
//            throw  new BusinessException("你不是该群管理员");
//        }
//        BaseModel::transaction(function () use ($param, $applyInfo) {
//            $applyInfo->refuseMsg = $param['refuseMsg'] ?? '';
//            $applyInfo->status = $param['status'] == 1 ? $applyInfo::STATUS_VERIFY_ED : $applyInfo::STATUS_REFUSE;
//            $applyInfo->update();
//            if ($param['status'] == 1) {
//                GroupApplyService::getInstance($this->who)->agree($applyInfo);
//            } else {
//                GroupApplyService::getInstance($this->who)->refuse($applyInfo);
//            }
//        });
//        $this->writeJson(Status::CODE_OK, $applyInfo, "操作成功");
//    }


    /**
     * @Api(name="获取一条群申请信息",path="/Api/User/Chat/Group/GroupApply/getOne")
     * @ApiDescription("获取一条群申请信息")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"获取成功"})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="applyId",required="")
     * @ApiSuccessParam(name="result.applyId",description="")
     * @ApiSuccessParam(name="result.groupId",description="")
     * @ApiSuccessParam(name="result.fromUserId",description="")
     * @ApiSuccessParam(name="result.applyMsg",description="")
     * @ApiSuccessParam(name="result.refuseMsg",description="")
     * @ApiSuccessParam(name="result.applyTime",description="")
     * @ApiSuccessParam(name="result.status",description=" 0 未审核  1 通过  -1拒绝")
     */
//    public function getOne()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $applyInfo = GroupApplyService::getInstance($this->who)->applyInfo($param['applyId']);
//        $this->writeJson(Status::CODE_OK, $applyInfo, "获取数据成功");
//    }


    /**
     * @Api(name="获取我申请加群的数据列表",path="/Api/User/Chat/Group/GroupApply/getList")
     * @ApiDescription("获取我申请加群的数据列表")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"获取成功"})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="page", from={GET,POST}, alias="页数", optional="")
     * @Param(name="pageSize", from={GET,POST}, alias="每页总数", optional="")
     * @ApiSuccessParam(name="result[].applyId",description="")
     * @ApiSuccessParam(name="result[].groupId",description="")
     * @ApiSuccessParam(name="result[].fromUserId",description="")
     * @ApiSuccessParam(name="result[].applyMsg",description="")
     * @ApiSuccessParam(name="result[].refuseMsg",description="")
     * @ApiSuccessParam(name="result[].applyTime",description="")
     * @ApiSuccessParam(name="result[].status",description=" 0 未审核  1 通过  -1拒绝")
     */
//    public function getList()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $page = (int)($param['page'] ?? 1);
//        $pageSize = (int)($param['pageSize'] ?? 20);
//        $model = new GroupApplyModel();
//        $data = $model->where('fromUserId',$this->who->userId)->getList($page, $pageSize);
//        $this->writeJson(Status::CODE_OK, $data, '获取列表成功');
//    }

}

