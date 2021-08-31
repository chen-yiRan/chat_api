<?php
namespace App\Utility\Cache;

use App\Model\User\UserModel;
use EasySwoole\Component\Singleton;
use Swoole\Table;

class UserCache{
    use Singleton;

    /**
     * @var Table $userTable
     */
    private $userTable;

    /**
     * NormalUserTable constructor.
     * @param int $maxOnline
     */
    function __construct(int $maxOnline = 1024 * 256){
        $this->userTable = new Table($maxOnline);
        $this->userTable->column('userId', Table::TYPE_INT, 16);
        $this->userTable->column('account', Table::TYPE_STRING, 64);
        $this->userTable->column('username', Table::TYPE_STRING, 128);
        $this->userTable->column('password', Table::TYPE_STRING, 32);
        $this->userTable->column('phone', Table::TYPE_STRING, 11);
        $this->userTable->column('avatar', Table::TYPE_STRING, 255);
        $this->userTable->column('createTime', Table::TYPE_INT, 8);
        $this->userTable->column('isDelete', Table::TYPE_INT, 1);
        $this->userTable->column('deleteTime', Table::TYPE_INT, 8);
        $this->userTable->column('isForbid', Table::TYPE_INT, 1);
        $this->userTable->create();
    }

    function set(UserModel $userModel){
        $userData = $userModel->getOriginData();
        return $this->userTable->set($userModel->userId,[
            'userId'             => $userData['userId'],
            'account'            => $userData['account'],
            'username'           => $userData['username'],
            'password'           => $userData['password'],
            'phone'              => $userData['phone'],
            'avatar'             => $userData['avatar'],
            'createTime'         => $userData['createTime'],
            'isDelete'           => $userData['isDelete'],
            'deleteTime'         => $userData['deleteTime'],
            'isForbid'           => $userData['isForbid'],
        ]);
    }
    function get(int $userId): ?UserModel
    {
        $info = $this->userTable->get($userId);
        if ($info) {
            return UserModel::create()->data($info);
        }else{///todo 后面确认看看要不要删除
            $userInfo =  UserModel::create()->get($userId);
            if($userInfo){
                self::set($userInfo);
            }
            return  $userInfo;
        }
        return null;
    }
    public static function makeSession(UserModel $user)
    {
        $time = time();
        $token = substr(md5($time . $user->password), 8, 16);
        return "{$user->userId}-{$token}-{$time}";
    }

    function getBySession(?string $session, ?int $ttl = null): ?UserModel
    {
        if ($session) {
            $temp = explode('-', $session);
            $userId = array_shift($temp);
            $token = array_shift($temp);
            $loginTime = array_shift($temp);
            $userId = (int)$userId;
            if (empty($userId)){
                return null;
            }
            $user = $this->get($userId);
            if ($user) {
                if (($ttl !== null) && (time() - $loginTime > $ttl)) {
                    return null;
                }
                if ($token === substr(md5($loginTime . $user->password), 8, 16)) {
                    return $user;
                }
            }
        }
        return null;
    }
    function destroy($userId)
    {
        $this->userTable->del($userId);
    }
}