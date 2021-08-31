<?php
namespace App\WebSocket;
use App\Service\User\UserService;
use App\WebSocket\Cache\UserFdMap;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use Swoole\Server;
use Swoole\WebSocket\Frame;

class WebSocketEvent {
    static  function websocketInit(EventRegister $register){

        //webSocket初始化
        $register->set($register::onMessage,function (Server $server, Frame $frame){
            $server->push($frame->fd,'ok');
        });
        $register->set($register::onOpen, function (Server $server,\Swoole\Http\Request $request){
            self::onOpen($server,$request);
        });
        $register->set($register::onClose,function (Server $server,int $fd,int $reactorId){
            self::onClose($server,$fd,$reactorId);
        });
    }


    /**
     * @param Server $server
     * @param \Swoole\Http\Request $request
     */
    public static function onOpen(Server $server,\Swoole\Http\Request $request){
        $session = $request->get['user_token'] ?? null;

        $userId = UserService::getInstance()->userId($session);
        if(!empty($userId)){
            UserFdMap::getInstance()->bind($request->fd, $userId);
        } else{
            self::pushSessionError($request->fd);
            ServerManager::getInstance()->getSwooleServer()->close($request->fd);
        }
    }

    static function pushSessionError($fd){
        $command = new Command([
            'op' => Command::SERVER_SESSION_ERROR,
            'msg' => '登陆状态失败',
            'args' => []
        ]);
        ServerManager::getInstance()->getSwooleServer()->push($fd, json_encode($command->toArray()));
    }
    /**
     * @param Server $server
     * @param int $fd
     * @param int $reactorId
     */
    static function onClose(Server $server, int $fd, int $reactorId){
        $clientInfo = $server->getClientInfo($fd);
        //只有握手成功才处理关闭事件
        if(!empty($clientInfo['websocket_status']) and $clientInfo['websocket_status'] == 3){
                UserFdMap::getInstance()->fdClose($fd);
        } else {
            return ;
        }
    }


}