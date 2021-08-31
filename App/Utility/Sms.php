<?php
namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\HttpClient\HttpClient;

class Sms
{
    protected $account;
    protected $password;
    protected $url;

    use Singleton;

    public function __construct($account = null, $password = null, $url = null)
    {
        $smsConfig = Config::getInstance()->getConf('SMS');

        $this->account = $account ?? $smsConfig['account'];
        $this->password = $password ?? $smsConfig['password'];
        $this->url = $url ?? $smsConfig['url'];
    }

    public function send($phone, $sms)
    {
        $data = [
            'account' => $this->account,
            'password' => $this->password,
            'params' => "{$phone}, ",
            "msg" => "{$sms}" . '{$var}'
        ];
        $client = new HttpClient($this->url);
        $ret = $client->postJson(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))->getBody();
        return json_decode($ret,true);
    }
}