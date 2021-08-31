<?php
namespace App\Model\Chat\User;

use App\Model\BaseModel;
use App\Utility\Bean\ListBean;

/**
 * Class UserMsgModel
 * @package App\Model\Chat\User
 * @property int $msgId
 * @property int $fromUserId
 * @property int $toUserId
 * @property string $msg
 * @property int $createTime
 * @property int $type // 1文本 2图片 3文件 4语音 5站内链接
 */
class UserMsgModel extends BaseModel
{
    protected $tableName = 'chat_user_msg_list';

    const TYPE_TEXT = 1;
    const TYPE_IMG = 2;
    const TYPE_FILE = 3;
    const TYPE_VOICE = 4;
    const TYPE_URL_PATH = 5;
    const TYPE_SPEAK_FORBIDDEN = 6;
    const TYPE_DELETE = 7;

    public function getList(int $page = 1, int $pageSize =10, string $field = '*' ): ListBean
    {
        $listBean = $this
            ->order($this->schemaInfo()->getPkFiledName(),'DESC')
            ->field($field)
            ->getPageList($page,$pageSize);
        return $listBean;
    }
}