<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2020-08-14
 * Time: 9:03
 * Description:
 */

namespace app\common\service\sms;


use think\facade\Cache;
use think\Exception;

class sms
{
    /**
     * 短信类别 1阿里 2腾讯
     * @var
     */
    private $smsType;
    /**
     * 使用场景[注册,找回密码..]
     * @var string
     */
    private $type = 0;
    /**
     * 短信实例
     * @var ALi
     */
    private $instance;
    /**
     * 接收号码
     * @var
     */
    private $phone;

    public function __construct($smsType = 1, $phone = '', $type = 0, $param = [])
    {
        $this->smsType = $smsType;
        $this->type = $type;
        $this->phone = $phone;

        $info = self::getTempInfo();
        if (is_null($info)) {
            throw new Exception('短信模板缓存异常,请检查模板类型是否选择正确', -999);
        }
        c_log("短信模版: \t". json_encode($info), 'message_code');
        switch ($smsType) {
            case 1:
                $this->instance = new ALi($phone, $param, $info['temp_id']);
                break;
            case 2:
                $this->instance = new QCloud($phone, $param, $info['temp_id']);
                break;
            default:
                $this->instance = new ALi($phone, $param, $info['temp_id']);
        }
    }

    /**
     * 获取模板信息
     * @return mixed|null
     */
    private function getTempInfo()
    {
        $_temCache = Cache::get('sms_template_' . $this->smsType);
        if (!$_temCache) {
            $_temCache = json_encode(config('sms.sms_template'));
            // 存入缓存
            Cache::set('sms_template_' . $this->smsType, $_temCache);
        }

        return $_temCache ? json_decode($_temCache, true)[$this->type] : null;
    }

    /**
     * 发送短信
     * @return array
     */
    public function sendSms()
    {
        $ret = $this->instance->sendSms();
        c_log("短信发送结果: \t". json_encode($ret), 'message_code');

        $sucRes = $errRes = [];
        switch ($this->smsType) {
            case 1:
                $ret = json_decode(json_encode($ret));

                if ($ret->Code === 'OK') {
                    $sucRes = ['code' => 0, 'message' => '发送成功'];
                }

                // 分钟级流控
                if(!empty($ret->Code)){
                    if ($ret->Code == 'isv.BUSINESS_LIMIT_CONTROL') {
                        $ret->Message = (strstr($ret->Message, '分钟') ? '60秒' : '1小时') . '内请勿重复获取验证码';
                    }
                }

                $errRes = ['code' => -1, 'message' => $ret->Message];
                break;
            case 2:
                if ($ret['result'] === 0) $sucRes = ['code' => 0, 'message' => '发送成功'];
                $errRes = ['code' => -1, 'message' => $ret['errmsg']];
                break;
            default:
                if ($ret['result'] === 0) $sucRes = ['code' => 0, 'message' => '发送成功'];
                $errRes = ['code' => -1, 'message' => $ret['errmsg']];
        }

        return $sucRes ?: $errRes;
    }
}