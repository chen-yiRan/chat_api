<?php
namespace App\WebSocket;

use EasySwoole\Spl\SplBean;

class Command extends SplBean
{
    /*
    * mainOpCode,CLIENT_SEND(0)|SERVER_REPLY(1),SUB_TYPE(DEFAULT = 01)
    */

    //系统消息
    const SERVER_SESSION_ERROR = -1001; //强制退出,用户下线

    const USER_SEND_MSG_PUSH = 1001;  //用户发送信息
    const USER_RECEIVES_MSG_PUSH = 1002; //用户接收信息

    const USER_DESIRE_HELP_MSG_PUSH = 1003; //用户心愿单

    const USER_AT_MSG_PUSH = 1005; //用户时刻评论  @用户消息
    const USER_COMMENT_MSG_PUSH = 1006; //用户评论
    const USER_SUB_COMMENT_MSG_PUSH = 1007; //用户回复评论
    const USER_FOLLOW_MSG_PUSH= 1008; //用户关注


    const USER_FRIEND_REQUEST_PUSH = 2001; // 用户系统消息
    const USER_SYSTEM_MSG_PUSH = 2002; //系统消息推送


    const EVENT_SYSTEM_MSG_PUSH = 3001; //用户申请加入事件亲友团消息
    const EVENT_USER_MOMENT_FAVORITE = 3002; // 事件时刻点赞
    const EVENT_USER_MOMENT_COLLECTION = 3003; // 事件时刻收藏
    const EVENT_APPLY_EVENT_JOIN = 3004; //用户申请加入事件亲友团消息
    const EVENT_APPLY_EVENT_JOIN_VERIFY_ED = 3005; //用户申请加入事件亲友团消息
    const EVENT_APPLY_EVENT_JOIN_REFUSE = 3006; //用户申请加入事件亲友团消息
    const EVENT_USER_EVENT_DELETE = 3007; //用户申请加入事件亲友团消息
    const EVENT_KICK_OUT_EVENT_JOIN = 3008; //事件把亲友移除
    const EVENT_SIGN_OUT_EVENT_JOIN = 300; //事件亲友主动退出


    const MALL_SYSTEM_MSG_PUSH = 4001; //系统消息推送


    protected $op;
    protected $args;
    protected $msg;

    /**
     * @return mixed
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @param mixed $op
     */
    public function setOp($op): void
    {
        $this->op = $op;
    }

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args): void
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg): void
    {
        $this->msg = $msg;
    }
}