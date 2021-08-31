<?php

namespace App\WebSocket;

use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Task\TaskManager;

class MsgPushEvent
{
    use Singleton;

    function msgPush($commandType, $content, $userId, $extraData)
    {
        $command = new Command();
        $command->setMsg(json_encode($content, JSON_UNESCAPED_UNICODE));
        $command->setOp($commandType);
        $command->setArgs($extraData);
        TaskManager::getInstance()->async(new Push($userId,$command));
    }
}