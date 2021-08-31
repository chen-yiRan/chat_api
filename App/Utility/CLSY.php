<?php
namespace App\Utility;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\HttpClient\HttpClient;

class CLSY
{
    protected $appId;
    protected $appKey;
    protected $url;

    public function __construct($appId, $appKey, $url)
    {
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->url = $url;
    }
    public static function getIosInstance()
    {
        $smsConfig = Config::getInstance()->getConf('CLSY.ios');
        $client = new self($smsConfig['appId'], $smsConfig['appKey'], $smsConfig['url']);
        return $client;
    }
    public static function getAndroidInstance()
    {
        $smsConfig = Config::getInstance()->getConf('CLSY.android');
        $client = new self($smsConfig['appId'], $smsConfig['appKey'], $smsConfig['url']);
        return $client;
    }

    public function getMobile(string $token)
    {
        $appId = $this->appId;
        $appKey = $this->appKey;
        $url = $this->url;

        $data = [
            'appId'       => $appId,
            'token'       => $token,
            'encryptType' => 0
        ];
        $str = '';
        ksort($data);
        foreach ($data as $key => $datum){
            $str .= "{$key}{$datum}";
        }
        $sig = hash_hmac('sha256', $str, $appKey);
        $data['sign'] = strtoupper($sig);
        $client = new HttpClient($url);
        $ret = $client->post($data);
        $json = json_decode($ret->getBody(),true);
        Logger::getInstance()->log($ret->getBody());
        if(isset($json['data']['mobileName'])){
            $mobile = $json['data']['mobileName'];
            $key = md5($appKey);
            $mobile = openssl_decrypt(hex2bin($mobile), 'AES-128-CBC', substr($key, 0, 16), OPENSSL_RAW_DATA, substr($key, 16));
            return $mobile;
        } else {
            return null;
        }
    }
}