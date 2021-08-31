<?php


namespace EasySwoole\EasySwoole;


use App\Utility\RedisClient;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Config;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use App\WebSocket\WebSocketEvent;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        self::mysqlInit();
        self::redisInit();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        //注册全局onrequest事件
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST,function (Request $request, Response $response) {
            if(self::crossDomainResponse($request,$response) === false){
                return false;
            }
        });
        //注册全局afterRequest事件
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST,function (Request $request, Response $response){
            // var_dump($this);
            //请求日志记录
            self::requestLog($request, $response);
        });

        WebSocketEvent::websocketInit($register);
    }


    /**
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public static function mysqlInit(){
        //注册mysql
        $config = new Config(GlobalConfig::getInstance()->getConf('MYSQL'));
        //连接池配置
        $config->setGetObjectTimeout(3.0);//设置获取连接池对象超时时间
        $config->setIntervalCheckTime(15 * 1000);//设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(10);//连接池对象最大闲置时间（秒）
        $config->setMaxObjectNum(20);//设置最大连接池存在连接对象数量
        $config->setMinObjectNum(5);//设置最小连接池存在连接对象数量
        $config->setAutoPing(5);//设置自动ping客户端连接的间隔
        DbManager::getInstance()->addConnection(new Connection($config));
    }
    public static function redisInit(){
        //注册redis
        $config = new RedisConfig(GlobalConfig::getInstance()->getConf('REDIS'));
        $redisPoolConfig = RedisPool::getInstance()->register($config, RedisClient::REDIS_POOL_NAME, RedisClient::class);
        $redisPoolConfig->setMaxObjectNum(40);
    }
    /**
     * 请求日志
     * @param Request $request
     * @param Response $response
     */
    protected static function requestLog(Request $request, Response $response){
        $str = "request:{$request->getMethod()}:{$request->getUri()->__toString()} post:" . json_encode($request->getRequestParam());
        $str = "response:{$response->getBody()->__toString()}";
    }
    /**
     * 跨域头
     * crossDomainResponse
     * @param Request  $request
     * @param Response $response
     * @return bool
     * @author tioncico
     * Time: 10:37 上午
     */
    protected static function crossDomainResponse(Request $request, Response $response)
    {
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            return false;
        }
        return true;
    }


}