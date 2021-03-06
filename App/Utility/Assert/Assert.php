<?php
namespace App\Utility\Assert;

class Assert
{
    static function assert($condition, $msg, ?int $errorCode = null): bool
    {
        if($condition !== true){
            throw (new AssertException($msg))->setErrorCode($errorCode);
        }
        return  true;
    }
}
