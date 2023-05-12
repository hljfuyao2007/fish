<?php
declare(strict_types=1);

namespace app\common\service\sms;

use think\facade\Config;
use think\facade\Env;

/**
 * 腾讯云短信
 * Class QCloud
 * @package app\common\sms
 */
class QCloud
{
    /**
     * appId
     * @var
     */
    private $appId;
    /**
     * appKey
     * @var
     */
    private $appKey;
    /**
     * 发送短信的号码
     * @var
     */
    private $phone;
    /**
     * 拨号字冠
     * @var
     */
    private $nationcode;
    /**
     * 签名内容
     * @var
     */
    private $sign = '';
    /**
     * 随机数
     * @var
     */
    private $random;
    /**
     * 模板ID
     * @var
     */
    private $tempId;
    /**
     * 请求发起时间
     * @var
     */
    private $time;
    /**
     * 多元参数
     * @var
     */
    private $param;

    /**
     * 初始化
     * QCloud constructor.
     * @param string $phone 发送短信号码
     * @param $param mixed 多元参数
     * @param int $tempId 模板ID
     */
    public function __construct($phone = '', $param = '', $tempId = 1)
    {
        // 加载sms配置
        Env::load(Env::get('APP_PATH') . 'common/ini/.sms');
        $this->phone = $phone;
        $this->nationcode = '86';
        $this->appId = Env::get('QCLOUD_APPID');
        $this->appKey = Env::get('QCLOUD_APPKEY');
        $this->sign = Env::get('QCLOUD_SIGN');
        $this->config = Config::get('sms.qCloud');
        $this->tempId = $tempId;
        $this->time = time();
        $this->random = mt_rand(1000000000, 9999999999);
        $this->param = $param;
    }

    /**
     * 单发或群发短信
     * @return mixed|string
     */
    public function sendSms()
    {
        $phone = $this->phone;
        if (is_array($this->phone)) {
            $phone = [];
            foreach ($this->phone as $value) {
                $phone[] = [
                    'phone'      => $value,
                    'nationcode' => $this->nationcode,
                ];
            }
        }
        // 拼装参数
        $param = [
            // 用户的 session 内容，腾讯 server 回包中会原样返回，可选字段，不需要就填空
            'ext'    => '',
            // 短信码号扩展号，格式为纯数字串，其他格式无效。
            'extend' => '',
            // App 凭证
            'sig'    => self::makeSig(),
            // 短信签名，如果使用默认签名，该字段可缺省
            'sign'   => $this->sign,
            // 单发号码或群发号码数组
            'tel'    => $phone,
            // 请求发起时间，unix 时间戳（单位：秒），如果和系统时间相差超过 10 分钟则会返回失败
            'time'   => $this->time,
            // 短信消息
            'msg'    => $this->param['msg'],
            // 短信类型，Enum{0: 普通短信, 1: 营销短信}（注意：要按需填值，不然会影响到业务的正常使用）
            'type'   => $this->param['type'],
        ];
        $url = $this->config['url']['sendOne'] . '?sdkappid=' . $this->appId . '&random=' . $this->random;
        return curl(2, $url, $param);
    }

    /**
     * 添加或修改模板
     * @param int $type 1 添加 0修改
     * @return mixed|string
     */
    public function addTemp($type = 1)
    {
        // 拼装参数
        $extra = [
            'sig'  => self::makeSig(),
            'time' => $this->time,
        ];
        $param = array_merge($this->param, $extra);
        $addUrl = $this->config['url']['addTemp'] . '?sdkappid=' . $this->appId . '&random=' . $this->random;
        $modUrl = $this->config['url']['modTemp'] . '?sdkappid=' . $this->appId . '&random=' . $this->random;
        return curl(2, $type ? $addUrl : $modUrl, $param);
    }


    /**
     * 删除模板
     * @return mixed|string
     */
    public function destroyTemp()
    {
        $param = [
            'sig'    => self::makeSig(),
            'time'   => $this->time,
            'tpl_id' => $this->param,
        ];
        $delUrl = $this->config['url']['delTemp'] . '?sdkappid=' . $this->appId . '&random=' . $this->random;
        return curl(2, $delUrl, $param);
    }

    /**
     * 模板状态查询
     * @return mixed|string
     */
    public function queryTempState()
    {
        $param = [
            'sig'  => self::makeSig(),
            'time' => $this->time,
        ];
        $param = array_merge($this->param, $param);
        $queryUrl = $this->config['url']['delTemp'] . '?sdkappid=' . $this->appId . '&random=' . $this->random;
        return curl(2, $queryUrl, $param);
    }


    /**
     * 生成凭证签名
     * @return string
     */
    private function makeSig()
    {
        try {
            $str = 'appkey=' . $this->appKey .
                '&random=' . $this->random .
                '&time=' . $this->time .
                '&mobile=' . $this->phone;
            $sig = hash('sha256', $str);
            return $sig;
        } catch (\Exception $e) {
            return '';
        }
    }


}