<?php
namespace App\WebSocket\Cache;

use App\Utility\RedisClient;
use EasySwoole\Component\Singleton;

class UserFdMap{
    use Singleton;

    const TTL = 86400 * 1;

    protected $userFdHash;
    protected $fdUserHash;

    /**
     * @param $fd
     * @param $userId
     */
    public function bind($fd, $userId){
        $this->setFdUser($fd,$userId);
        $this->setUserFd($userId, $fd);
    }

    /**
     * @param int $userId
     * @param int $fd
     * @return false|mixed|null
     * @throws \Throwable
     */
    public function setUserFd(int $userId, int $fd){
        //一个userId,可能有多个fd，所以使用hash存储方案
        return  RedisClient::invoke(function (RedisClient $redisClient) use($userId,$fd){
            $result = $redisClient->hSet($this->getUserFdKey($userId),$fd,1);
            //设置一个过期时间
            $redisClient->expire($this->getUserFdKey($userId), time()+self::TTL);
            return $result;
        } );
    }
    protected function getUserFdKey($userId){
        return "ws_user_fd_{$userId}";
    }

    /**
     * @param int $fd
     * @param int $userId
     * @return false|mixed|null
     * @throws \Throwable
     */
    public function setFdUser(int $fd, int $userId){
        //一个fd，只有一个userId
        return RedisClient::invoke(function (RedisClient $redisClient) use($userId,$fd){
           $result = $redisClient->hSet($this->getFdUserKey(),$fd, $userId);
           //设置一个过期时间
            $redisClient->expire($this->getFdUserKey(),time()+self::TTL);
            return $result;
        });
    }
    protected function getFdUserKey()
    {
        return "ws_fd_user";
    }

    /**
     * @param int $fd
     * @return false|mixed|null
     * @throws \Throwable
     */
    public function getFdUserId(int $fd){
        return RedisClient::invoke(function (RedisClient $redisClient) use($fd){
            $result = $redisClient->hGet($this->getFdUserKey(), $fd);
            //设置一个过期时间
            $redisClient->expire($this->getFdUserKey(),time()+self::TTL);
            return $result;
        });
    }

    /**
     * @param int $userId
     * @return false|mixed|null
     * @throws \Throwable
     */
    public function getUserFdList(int $userId)
    {
        return RedisClient::invoke(function (RedisClient $redisClient) use ($userId) {
            $result = $redisClient->hGetAll($this->getUserFdKey($userId));
            //设置一个过期时间
            $redisClient->expire($this->getUserFdKey($userId),time()+self::TTL);
        });
    }

    /**
     * @param int $fd
     * @throws \Throwable
     */
    public function fdClose(int $fd){
        $userId = $this->getFdUserId($fd);
        $this->delFdUser($fd);
        if(!empty($userId)){
            $this->delUserFd($userId, $fd);
        }
    }

    public function delFdUser($fd){
        return RedisClient::invoke(function (RedisClient $redisClient) use($fd){
            return $redisClient->hDel($this->getFdUserKey(), $fd);
        });
    }

    public function delUserFd(int $userId, int $fd){
        return RedisClient::invoke(function (RedisClient $redisClient) use($userId,$fd){
            return $redisClient->hDel($this->getUserFdKey($userId), $fd);
        });

    }


}
