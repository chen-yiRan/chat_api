<?php
namespace App\Utility;

use App\Utility\Exception\BusinessException;
use EasySwoole\Utility\Random;


class VerifyCode
{
    const COOKIE_CODE_HASH = 'verifyCodeHash';
    const COOKIE_CODE_TIME = 'verifyCodeTime';
    const VERIFY_CODE_LENGTH = 4;
    const TTL = 5 * 60;
    static function checkCode($verifyCodeHash, $verifyCodeTime, $verifyCode)
    {
        //判断是否过期
        if($verifyCodeTime + self::TTL < time()){
            return false;
        }
        $code = strtolower($verifyCode);
        return md5($code . $verifyCodeTime) == $verifyCodeHash;
    }

    public static function sendSmsVerify($phone){
        $code = Random::character(6,'1234567890');
        $sms = "你的验证码是:{$code}";
        $ret = Sms::getInstance()->send($phone,$sms);
        if(empty($ret['successNum']) || $ret['successNum'] != 1){
            throw new BusinessException('短信网关异常:' . $ret['errorMsg']);
        }
        //注册验证码数据
        $data = [
            'time' => time(),
            'phone' => $phone,
        ];
        $token = self::hash($data['time'],$phone,$code);
        $data['hash'] = $token;
        return $data;
    }

    public static function verifySms($code, $phone, $verifyTime, $hash)
    {
        //判断验证吗是否已经验证成功过
        RedisClient::invoke(function (RedisClient $redisClient) use($code,$phone,$verifyTime,$hash){
            $redisPrefixKey = "sms_verify_code_";
            $time = $verifyTime;

            $code = strtolower($code);
            if(time() - self::TTL > $time){
                throw new BusinessException("验证失败");
            }

            $tokenData = $redisClient->get($redisPrefixKey . $hash);
            if(!empty($tokenData)){
                throw new BusinessException("验证失败");
            }
            $codeHash = self::hash($time, $phone, $code);
            if($codeHash != $hash){
                throw new BusinessException("验证失败");
            }

            $redisClient->set($redisPrefixKey . $hash, 1, self::TTL);
        });
        return $phone;
    }

    protected static function hash($time, $phone, $code)
    {
        $salt = 'api';
        return md5($code . $phone . $time . $salt);
    }
}