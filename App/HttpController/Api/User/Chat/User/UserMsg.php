<?php
namespace App\HttpController\Api\User\Chat\User;

use App\HttpController\Api\User\UserBase;
use App\Service\Chat\User\UserMsgService;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\InjectParamsContext;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * Class UserMsg
 * @package App\HttpController\Api\User\Chat\User
 * @ApiGroup(groupName="用户私聊")
 * @ApiGroupAuth (name="")
 * @ApiGroupDescription ("")
 */
class UserMsg extends UserBase
{
    /**
     * @Api(name="给用户发送消息",path="/Api/User/Chat/User/UserMsg/send")
     * @ApiDescription ("给用户发送消息")
     * @Method (allow={GET,POST})
     * @InjectParamsContext (key="param")
     * @Param (name="toUserId",required="")
     * @Param (name="msg",lengthMax="1024",required="")
     * @Param (name="type",description="1文本 2图片 3文件 4语音 5站内链接")
     */
    public function send(){
        $param = ContextManager::getInstance()->get('param');
        $msgInfo = UserMsgService::getInstance($this->who)->sendMsg($param['toUserId'], $param['msg'], $param['type']);
        $this->writeJson(Status::CODE_OK,$msgInfo->toArray(),"发送成功");
    }

    /**
     * @Api (name="获取一条私聊信息",path="/Api/User/Chat/User/UserMsg/getOne")
     * @ApiDescription ("获取一条私聊信息")
     * @Method (allow={GET,POST})
     * @InjectParamsContext (key="param")
     * @Param(name="msgId",required="")
     */
    public function getOne(){
        $param = ContextManager::getInstance()->get("param");
        $info = UserMsgService::getInstance()->getMsgInfo($param['msgId']);
        $this->writeJson(Status::CODE_OK,$info,"获取数据成功");
    }

    /**
     * @Api(name="获取私聊信息列表",path="/Api/User/Chat/User/UserMsg/getList")
     * @ApiDescription ("获取私聊信息列表")
     * @Method (allow={GET,POST})
     * @InjectParamsContext (key="param")
     * @Param (name="msgId",description="起始/最后消息id",optional="")
     * @Param (name="msgEndId,description="范围搜索最后id",optional="")
     * @Param (name="operate",defaultValue="after",inArray={"after","before"}
     * @Param (name="userId",description="聊天的用户id",optional="")
     */
    public function getList()
    {
        $param = ContextManager::getInstance()->get("param");
        $data = UserMsgService::getInstance($this->who)->getMsgList($param['userId'] ?? 0,$param['operate'], $param['msgId'], $param["msgEndId"],100);
        $this->writeJson(Status::CODE_OK,$data,'获取列表成功');
    }

}