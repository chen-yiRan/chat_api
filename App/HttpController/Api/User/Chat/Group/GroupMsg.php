<?php

namespace App\HttpController\Api\User\Chat\Group;

use App\HttpController\Api\User\Base;
use App\Model\Chat\Group\GroupModel;
use App\Model\Chat\Group\GroupMsgModel;
use App\Service\Chat\Group\GroupMsgService;
use App\Service\Chat\Group\GroupService;
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
 * GroupMsg
 * Class GroupMsg
 * Create With ClassGeneration
 * @ApiGroup(groupName="群消息管理")
 * @ApiGroupAuth(name="")
 * @ApiGroupDescription("")
 */
class GroupMsg extends Base
{
    /**
     * @Api(name="发送群消息",path="/Api/User/Chat/Group/GroupMsg/send")
     * @ApiDescription("发送群消息")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":{"groupId":1140,"fromUserId":125817,"msg":"测试文本8YDKnE","type":1,"time":1618471071,"msgId":1604},"msg":"发送成功"})
     * @ApiFail({"code":400,"result":[],"msg":"新增失败"})
     * @Param(name="msg",lengthMax="1024",required="")
     * @Param(name="type",alias="1文本 2图片 3文件 4语音",description="1文本 2图片 3文件 4语音",lengthMax="1",required="",defaultValue="1")
     * @Param(name="groupHash",required="")
     */
    public function send()
    {
        $param = ContextManager::getInstance()->get('param');
        $info = GroupMsgService::getInstance($this->who)->sendMsg($param['groupHash'], $param['msg'], $param['type']);
        $this->writeJson(Status::CODE_OK, $info->toArray(), "发送成功");
    }

    /**
     * @Api(name="获取一条群消息数据",path="/Api/User/Chat/Group/GroupMsg/getOne")
     * @ApiDescription("获取一条群消息数据")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":{"msgId":1605,"msg":"test123","time":1618471139,"type":1,"groupId":1141,"fromUserId":125817,"groupInfo":{"msgId":1605,"msg":"test123","time":1618471139,"type":1,"groupId":1141,"fromUserId":125817},"fromUserInfo":{"userId":125817,"account":"lh","username":"lh","avatar":"fs-zuola.oss-cn-beijing.aliyuncs.com\/userAvatar\/20210412\/15\/11\/6073f2895e21235628.jpg"}},"msg":"获取数据成功."})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="msgId",required="")
     * @ApiSuccessParam(name="result.msgId",description="")
     * @ApiSuccessParam(name="result.msg",description="")
     * @ApiSuccessParam(name="result.time",description="")
     * @ApiSuccessParam(name="result.type",description="1文本 2图片 3文件 4语音")
     * @ApiSuccessParam(name="result.groupId",description="")
     * @ApiSuccessParam(name="result.fromUserId",description="")
     * @ApiSuccessParam(name="result.state",description="")
     */
    public function getOne()
    {
        $param = ContextManager::getInstance()->get('param');
        $info = GroupMsgService::getInstance($this->who)->getMsgInfo($param['msgId']);
        $this->writeJson(Status::CODE_OK, $info, "获取数据成功.");
    }


    /**
     * @Api(name="获取一条群消息数据",path="/Api/User/Chat/Group/GroupMsg/getList")
     * @ApiDescription("获取一条群消息数据")
     * @Method(allow={GET,POST})
     * @InjectParamsContext(key="param")
     * @ApiSuccessParam(name="code",description="状态码")
     * @ApiSuccessParam(name="result",description="api请求结果")
     * @ApiSuccessParam(name="msg",description="api提示信息")
     * @ApiSuccess({"code":200,"result":[{"msgId":1607,"msg":"大萨达啥多所大奥","time":1618474732,"type":1,"groupId":1150,"fromUserId":125817,"groupUserId":1326,"groupUserHash":"e5244f99ae1b12cd","userId":125817,"isManager":1,"lastMsgId":0,"lastReadMsgId":0,"unreadCount":0,"state":1,"remark":null,"lastMsgTime":0,"speakState":1,"speakForbiddenTime":null,"receiveMsgType":1,"groupInfo":{"msgId":1607,"msg":"大萨达啥多所大奥","time":1618474732,"type":1,"groupId":1150,"fromUserId":125817},"fromUserInfo":{"userId":125817,"account":"lh","username":"lh","avatar":"fs-zuola.oss-cn-beijing.aliyuncs.com\/userAvatar\/20210412\/15\/11\/6073f2895e21235628.jpg"}}],"msg":"获取列表成功"})
     * @ApiFail({"code":400,"result":[],"msg":"获取失败"})
     * @Param(name="msgId", alias="消息id",description="起始/最后消息id", optional="")
     * @Param(name="operate",defaultValue="after",inArray={"after","before"})
     * @Param(name="groupHash", alias="群标识",description="群标识,不传则获取会员加入的所有群", optional="")
     * @ApiSuccessParam(name="result[].msgId",description="")
     * @ApiSuccessParam(name="result[].msg",description="")
     * @ApiSuccessParam(name="result[].time",description="")
     * @ApiSuccessParam(name="result[].type",description="1文本 2图片 3文件 4语音")
     * @ApiSuccessParam(name="result[].groupId",description="")
     * @ApiSuccessParam(name="result[].fromUserId",description="")
     * @ApiSuccessParam(name="result[].state",description="")
     */
    public function getList()
    {
        $param = ContextManager::getInstance()->get('param');

        $data = GroupMsgService::getInstance($this->who)->getMsgList($param['groupHash'],$param['operate'],$param['msgId']);

        $this->writeJson(Status::CODE_OK, $data, '获取列表成功');
    }
}

