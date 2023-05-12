<?php
declare(strict_types=1);

namespace app\common\service\sms;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

/**
 * 阿里云短信
 * Class ALi
 * @package app\common\sms
 */
class ALi
{
    /**
     * 应用ID
     * @var
     */
    private $appId;
    /**
     * 应用Key
     * @var
     */
    private $appKey;
    /**
     * 发送短信的号码
     * @var
     */
    private $phone;
    /**
     * 短信签名
     * @var
     */
    private $sign = '';
    /**
     * 模板ID
     * @var
     */
    private $tempId;
    /**
     * 多元参数
     * @var
     */
    private $param;

    public function __construct($phone = '', $param = '', $tempId = 'SMS_137825175')
    {
        $this->phone = $phone;
        $this->appId = config('sms.sms_conf.appId');
        $this->appKey = config('sms.sms_conf.appKey');
        $this->sign = config('sms.sms_conf.sign');
        $this->tempId = $tempId;
        $this->param = $param;

        // 初始化阿里云
        AlibabaCloud::accessKeyClient($this->appId, $this->appKey)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
    }


    /**
     * 发送短信
     * @return array
     */
    public function sendSms()
    {
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId'      => "cn-hangzhou",
                        'PhoneNumbers'  => $this->phone,
                        'SignName'      => $this->sign,
                        'TemplateCode'  => $this->tempId,
                        'TemplateParam' => json_encode($this->param, JSON_UNESCAPED_UNICODE),
                    ]
                ])
                ->request();

            return $result->toArray();
        } catch (ClientException $e) {
            return ['code' => -1, 'message' => $e->getErrorMessage()];
        } catch (ServerException $e) {
            return ['code' => -999, 'message' => $e->getErrorMessage()];
        }
    }
}