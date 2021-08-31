<?php
namespace App\Service\Chat;

use App\Model\User\UserModel;

class ChatBaseService
{
    protected $who;

    public function __construct(UserModel $userModel)
    {
        $this->who = $userModel;
    }

}