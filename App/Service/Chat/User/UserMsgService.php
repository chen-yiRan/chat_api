<?php
namespace App\Service\Chat\User;


use App\Model\Chat\User\UserMsgModel;
use App\Service\User\UserBlacklistService;
use App\Utility\Exception\BusinessException;
use App\WebSocket\Command;
use App\WebSocket\MsgPushEvent;
use EasySwoole\Component\Singleton;

class UserMsgService extends UserBaseService
{
    use Singleton;
    public function sendMsg(int $toUserId, $msg, $type)
    {
        $this->sendMsgCheck($toUserId);
        $msgInfo = $this->addUserMsg($toUserId,$msg,$type);
        return $msgInfo;
    }

    public function addUserMsg(int $toUserId, $msg, $type)
    {
        $data = [
            'userId' => $this->who->userId,
            'toUserId' => $toUserId,
            'msg' => $msg,
            'type' => $type
        ];
        $msgInfo = new UserMsgModel($data);
        $msgInfo->save();
        //推送给别人
        MsgPushEvent::getInstance()->msgPush(Command::USER_RECEIVES_MSG_PUSH,$msgInfo,$toUserId,null);
        return $msgInfo;
    }

    public function sendMsgCheck(int $toUserId)
    {
        $data = [];
        //Blacklist
        $data['isExistUserBlack'] = UserBlacklistService::getInstance()->checkExitstsBlack($toUserId,$this->who->userId);
        $data['isExistMyBlack'] = UserBlacklistService::getInstance()->checkExitstsBlack($this->who->userId,$toUserId);

        if (!empty($data['isExistUserBlack'])){
            throw new BusinessException('你已被对方拉黑,不能私信!');

        }
        if (!empty($data['isExistMyBlack'])){
            throw new BusinessException('对方在您的黑名单中,不能私信!');

        }
        return $data;
    }

    public function getMsgInfo($msgId)
    {
        $model = new UserMsgModel();
        $msgInfo = $model->with(['toUserInfo','fromUserInfo'])->get($msgId);
        if(empty($msgInfo)){
            throw new BusinessException("消息不存在");
        }
//        //判断是否为聊天当事人
//        if($msgInfo->toUserId != $this->who->userId &&$msgInfo)
        return $msgInfo;
    }

    public function getMsgList(int $friendUserId, string $operate,?int $msgId = null, ?int $msgEndId = null, int $limit = 10): array{
        $model = UserMsgModel::create();
        if ($msgId !== null) {
            if ($operate == 'after') {
                $model->where('msgId', $msgId, '>');
                $model->where('msgId', $msgEndId, '<=');
                if ($msgEndId !== null){
                    $model->where('msgId', $msgEndId, '<=');
                }
            } else {
                $model->where('msgId', $msgId, '<');
                if ($msgEndId !== null){
                    $model->where('msgId', $msgEndId, '>');
                }
            }
        }
        if ($friendUserId > 0) {
            $model->where("((toUserId = ? and fromUserId = ?) or (fromUserId=? and toUserId = ?))", [$this->who->userId, $friendUserId, $this->who->userId, $friendUserId]);
        } else {
            $model->where("(toUserId = ?  or fromUserId = ? )", [$this->who->userId, $this->who->userId]);
        }

        $list = $model
            ->withTotalCount()
            ->with(['toUserInfo', 'fromUserInfo'],false)
            ->order($model->schemaInfo()->getPkFiledName(), 'DESC')
            ->field("*")
            ->limit($limit)
            ->all();
        return $list;
    }
}
