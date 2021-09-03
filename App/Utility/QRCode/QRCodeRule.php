<?php
namespace App\Utility\QRCode;

use EasySwoole\Http\Response;
use Endroid\QrCode\QrCode;

class QRCodeRule
{
    //扫码
    //001为个人名片
    const MY_QR_CODE = '001';

    const INVITE_RELATIVES_MEMBER_QR_CODE = '002';

    protected $paramData;

    public function __construct($data)
    {
        $this->paramData = $data;
    }

    public function str($QRCodeRule)
    {
        $str = $QRCodeRule."@" . base64_encode(http_build_query($this->paramData)) . "@";
        return $str;
    }

    public function ResponseQRCode($str,Response $response)
    {
        $qrCode = new QrCode($str);

        $response->withAddedHeader('Content-Type', $qrCode->getContentType());
        $response->write($qrCode->writeString());
    }
}