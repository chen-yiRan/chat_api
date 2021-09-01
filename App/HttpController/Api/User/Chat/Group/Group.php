<?php

namespace App\HttpController\Api\User\Chat\Group;


use App\HttpController\Base;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupUserModel;
use App\Service\Chat\Group\GroupService;
use App\Utility\Assert\AssertException;
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
 * Group
 * Class Group
 * Create With ClassGeneration
 * @ApiGroup(groupName="群聊管理")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("")
 */
class Group extends Base
{
    /**
     * @Api(name="新增群聊",path="/Api/User/Chat/Group/Group/add")
     * @ApiDescription("新增群聊")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="groupName",lengthMax="255",required="")
     */
//    public function add()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $groupInfo = GroupService::getInstance($this->who)->addGroup($param['groupName']);
//        $this->writeJson(Status::CODE_OK, $groupInfo, '群创建成功');
//    }


    /**
     * @Api(name="更新群组数据",path="/Api/User/Chat/Group/Group/update")
     * @ApiDescription("更新群组数据")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"更新成功"})
     * @ApiFail({"code":400,"result":[],"msg":"更新失败"})
     * @Param(name="groupHash", alias="群标识", required="",lengthMax="64")
     * @Param(name="groupName",lengthMax="255",optional="")
     * @Param(name="groupNotice",alias="群公告",description="群公告",lengthMax="255",optional="")
     * @Param(name="groupSpeakState",alias="是否全群禁言",description="是否全群禁言",lengthMax="1",optional="",defaultValue="1")
     */
    public function update()
    {
        $param = ContextManager::getInstance()->get('param');
        $info = GroupService::getInstance($this->who)->updateGroup($param['groupHash'], $param);
        $this->writeJson(Status::CODE_OK, $info, "更新数据成功");
    }


    /**
     * @Api(name="获取一条群组数据",path="/Api/User/Chat/Group/Group/getOne")
     * @ApiDescription("获取一条群组数据")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"获取成功"})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="groupHash",required="")
     * @ApiSuccessParam(name="result.groupId",description="")
     * @ApiSuccessParam(name="result.groupHash",description="")
     * @ApiSuccessParam(name="result.groupNum",description="")
     * @ApiSuccessParam(name="result.groupName",description="")
     * @ApiSuccessParam(name="result.belongToUserId",description="群主")
     * @ApiSuccessParam(name="result.groupThumb",description="")
     * @ApiSuccessParam(name="result.groupNotice",description="群公告")
     * @ApiSuccessParam(name="result.groupSpeakState",description="是否全群禁言")
     */
    public function getOne()
    {
        $param = ContextManager::getInstance()->get('param');
        $info = GroupService::getInstance($this->who)->getGroupInfo($param['groupHash']);
        if (!$info) {
            throw new BusinessException("该数据不存在");
        }
        $this->writeJson(Status::CODE_OK, $info, "获取数据成功.");
    }


//    /**
//     * @Api(name="获取群组列表",path="/Api/User/Chat/Group/Group/getList")
//     * @ApiDescription("获取群组列表")
//     * @Method(allow={GET,POST})
//     * @InjectParamsContext(key="param")
//     * @ApiSuccessParam(name="code",description="状态码")
//     * @ApiSuccessParam(name="result",description="api请求结果")
//     * @ApiSuccessParam(name="msg",description="api提示信息")
//     * @Param(name="weddingId",optional="")
//     * @ApiSuccess({"code":200,"result":[{"groupId":1,"groupHash":"33cb0257159f1bb3","groupNum":0,"groupName":"测试","belongToUserId":125817,"groupThumb":null,"groupNotice":null,"groupSpeakState":1,"isDelete":0,"deleteTime":0,"weddingId":null},{"groupId":12,"groupHash":"2ba65f57969c07ce","groupNum":0,"groupName":"测试婚礼群","belongToUserId":125817,"groupThumb":null,"groupNotice":null,"groupSpeakState":1,"isDelete":0,"deleteTime":0,"weddingId":null},{"groupId":13,"groupHash":"3d4dd002b6c56f26","groupNum":0,"groupName":"测试婚礼群","belongToUserId":125817,"groupThumb":null,"groupNotice":null,"groupSpeakState":1,"isDelete":0,"deleteTime":0,"weddingId":null},{"groupId":14,"groupHash":"9d1fd5d190593510","groupNum":0,"groupName":"测试","belongToUserId":125817,"groupThumb":null,"groupNotice":null,"groupSpeakState":1,"isDelete":0,"deleteTime":0,"weddingId":null}],"msg":"获取列表成功"})
//     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
//     * @ApiSuccessParam(name="result[].groupId",description="")
//     * @ApiSuccessParam(name="result[].groupHash",description="")
//     * @ApiSuccessParam(name="result[].groupNum",description="")
//     * @ApiSuccessParam(name="result[].groupName",description="")
//     * @ApiSuccessParam(name="result[].belongToUserId",description="群主")
//     * @ApiSuccessParam(name="result[].groupThumb",description="")
//     * @ApiSuccessParam(name="result[].groupNotice",description="群公告")
//     * @ApiSuccessParam(name="result[].groupSpeakState",description="是否全群禁言")
//     */
//    public function getList()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        $data = GroupService::getInstance($this->who)->getUserGroupList($param['weddingId']);
//        $this->writeJson(Status::CODE_OK, $data, '获取列表成功');
//    }


    /**
     * @Api(name="解散群聊",path="/Api/User/Chat/Group/Group/dismiss")
     * @ApiDescription("解散群聊")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="groupHash",required="")
     */
//    public function dismiss()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        GroupService::getInstance($this->who)->dismissGroup($param['groupHash']);
//        $this->writeJson(Status::CODE_OK, [], "删除成功.");
//    }

    /**
     * @Api(name="退出群聊",path="/Api/User/Chat/Group/Group/quit")
     * @ApiDescription("退出群聊")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[],"msg":"新增成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="groupHash",required="")
     */
//    public function quit()
//    {
//        $param = ContextManager::getInstance()->get('param');
//        GroupService::getInstance($this->who)->quitGroup($param['groupHash']);
//        $this->writeJson(Status::CODE_OK, [], "退出成功.");
//    }
}

