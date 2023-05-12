<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-22
 * Time: 11:02
 * Description:
 */

namespace app\common\service;


use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;

class EasyAliPay
{
    private static $instance = null;

    /**
     * @var mixed 配置
     */
    private $config;

    /**
     * 实例化入口
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->config = config('ali');
        Factory::setOptions($this->getOption());
    }

    private function __clone()
    {
    }

    /**
     * 设置参数
     * @return Config
     */
    private function getOption(): Config
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        // appid
        $options->appId = $this->config['appid'];
        // 商户私钥
        $options->merchantPrivateKey = $this->config['merchant_private_key'];
        // 支付宝公钥
        $options->alipayPublicKey = $this->config['alipay_public_key'];

        // 证书模式证书路径
         $options->alipayCertPath = '';
         $options->alipayRootCertPath = '';
         $options->merchantCertPath = '';

        // 可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $this->config['notify_url'];
        // 可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey = $this->config['encrypt_key'];

        return $options;
    }

    /**
     * 支付能力
     * @return \Alipay\EasySDK\Kernel\Payment
     */
    public function payment(): \Alipay\EasySDK\Kernel\Payment
    {
        return Factory::payment();
    }

    /**
     * 基础能力
     * @return \Alipay\EasySDK\Kernel\Base
     */
    public function base(): \Alipay\EasySDK\Kernel\Base
    {
        return Factory::base();
    }

    /**
     * 会员能力
     * @return \Alipay\EasySDK\Kernel\Member
     */
    public function member(): \Alipay\EasySDK\Kernel\Member
    {
        return Factory::member();
    }

    /**
     * 安全能力
     * @return \Alipay\EasySDK\Kernel\Security
     */
    public function security(): \Alipay\EasySDK\Kernel\Security
    {
        return Factory::security();
    }

    /**
     * 营销能力
     * @return \Alipay\EasySDK\Kernel\Marketing
     */
    public function marketing(): \Alipay\EasySDK\Kernel\Marketing
    {
        return Factory::marketing();
    }

    /**
     * 辅助工具
     * @return \Alipay\EasySDK\Kernel\Util
     */
    public function util(): \Alipay\EasySDK\Kernel\Util
    {
        return Factory::util();
    }

    /**
     * 预下单
     * @param array $param
     * @return \Alipay\EasySDK\Payment\App\Models\AlipayTradeAppPayResponse
     */
    public function pre_order(array $param)
    {
        $this->config['cent'] && $param['total_fee'] = '0.01';
        $request = self::payment()
            ->app()
            ->batchOptional($param['extend'])
            ->pay($param['subject'], $param['order_number'], $param['total_fee']);

        if (!(new ResponseChecker())->success($request)) {
            abort(-1, $request->msg);
        }
        return $request;

    }

    /**
     * 退款
     * @param $param
     * @return bool
     * @throws \Exception
     */
    public function refund($param): bool
    {
        $this->config['cent'] && $param['total_fee'] = '0.01';
        $request = self::payment()
            ->common()
            ->refund($param['out_trade_no'], $param['total_fee']);
        
        if (!(new ResponseChecker())->success($request)) {
            abort(-1, $request->msg);
        }
        return true;
    }
}