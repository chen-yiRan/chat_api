<?php
namespace Test\User\User;
use UnitTest\User\UserBaseTestCase;

class UserInfoTest extends UserBaseTestCase
{
    protected $modelName = 'User/UserInfo';
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }
    public function testGetDetail()
    {
        $data = [];
        $response = $this->request('getDetail', $data,$this->modelName);
        var_dump($response);
    }
}