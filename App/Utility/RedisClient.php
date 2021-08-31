<?php
namespace App\Utility;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

class RedisClient extends Redis{
    const REDIS_POOL_NAME = 'mychat';

    public static function invoke(callable $call){
        try {
            return RedisPool::invoke($call,static::REDIS_POOL_NAME);
        }catch (\Throwable $throwable){
            throw $throwable;
        }
    }
}
