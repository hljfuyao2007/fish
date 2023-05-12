<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-15
 * Time: 14:55
 * Description:
 */

namespace app\common\service;


use EasyWeChat\Factory;
use EasyWeChat\Kernel\Http\StreamResponse;
use think\facade\Request;

class EasyWechat
{
	private static $instance;
	public $config;

	public function __construct(string $type, string $extra = '')
	{
		$this->config = config('wechat');
		$m = ["JSAPI" => "miniProgram", 'APP' => 'officialAccount'];
		$method = $m[$type] ?? $type;

		self::$instance = Factory::$method($extra ? array_merge($this->config[$type], $this->config[$extra]) : $this->config[$type]);
	}

	/**
	 * 接收通知成功后应答输出XML数据
	 * @param string $xml
	 */
	public function replyNotify(){
		$data['return_code'] = 'SUCCESS';
		$data['return_msg'] = 'OK';
		$xml = $this->data_to_xml( $data );
		echo $xml;
		die();
	}
	/**
	 * 输出xml字符
	 * @param   $params     参数名称
	 * return   string      返回组装的xml
	 **/
	public function data_to_xml( $params ){
		if(!is_array($params)|| count($params) <= 0)
		{
			return false;
		}
		$xml = "<xml>";
		foreach ($params as $key=>$val)
		{
			if (is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">";
			}else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml.="</xml>";
		return $xml;
	}

	/**
	 *
	 * 获取支付结果通知数据
	 * return array
	 */
	public function getNotifyData()
	{
		//获取通知的数据
		$xml = file_get_contents('php://input');
		$data = array();
		if (empty($xml)) {
			return false;
		}
		$data = $this->xml_to_data($xml);
		if (!empty($data['return_code'])) {
			if ($data['return_code'] == 'FAIL') {
				return false;
			}
		}
		return $data;
	}

	/**
	 * 将xml转为array
	 * @param string $xml
	 * return array
	 */
	public function xml_to_data($xml){
		if(!$xml){
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	}

	/**
	 * app微信获取资料
	 * @return array
	 */
	public function app_info(): array
	{
		$user = self::$instance->oauth->user();
		if (!$user) {
			abort(-1, '微信资料获取失败');
		}
		return [
			'union_id' => $user->original['unionid'],
			'open_id' => $user->id,
			'nickname' => $user->getNickname(),
			'avatar' => $user->getAvatar(),
		];
	}

	/**
	 * 小程序微信获取资料
	 * @param string $code
	 * @return array
	 */
	public function applet_info(string $code): array
	{
		$s = self::$instance->auth->session($code);
//		c_log(json_encode($s), 'wechat_login');
		if (!isset($s['session_key'])) {
//			abort(-1, '微信密钥获取失败');
			abort(-1, $s['errmsg']);
		}

		return [
			'union_id' => $s['unionid'] ?? '',
			'open_id' => $s['openid'],
			'session_key' => $s['session_key']
		];
	}

	/**
	 * 小程序获取微信授权手机号
	 * @param string $session_key
	 * @param string $encryptedData
	 * @param string $iv
	 * @return mixed|string
	 */
	public function applet_get_phone(string $session_key, string $encryptedData, string $iv)
	{
		$user = self::$instance->encryptor->decryptData($session_key, $iv, $encryptedData);

		return $user['purePhoneNumber'] ?? '';
	}

	/**
	 * 预下单
	 * @param array $param
	 * @param $order_info
	 * @return mixed|array
	 */
	public function pre_order(array $param, $order_info)
	{
		$this->config['cent'] && $param['total_fee'] = '1';
		$res = self::$instance->order->unify($param);
		c_log(json_encode($res), 'wechat_app_pay');
		if ($res['return_code'] === 'SUCCESS' && $res['result_code'] === 'SUCCESS') {
			$order_info->prepayment_info = $res['prepay_id'];
			if ($order_info->trade_type == 'JSAPI') {   // 小程序
				return self::$instance->jssdk->bridgeConfig($order_info->prepayment_info, false);
			} else { // app
				return self::$instance->jssdk->appConfig($order_info->prepayment_info);
			}
		}

		if ($res['return_code'] === 'SUCCESS' && $res['result_code'] === 'FAIL') {
			abort(-1, $res['err_code_des'] == 'JSAPI支付必须传openid' ? '请使用微信登录' : $res['err_code_des']);
		}
		return [];
	}

	/**
	 * 支付回调
	 * @param \Closure $c
	 */
	public function notify(\Closure $c): void
	{
		self::$instance->handlePaidNotify($c)->send();
	}

	/**
	 * 退款
	 * @param string $trade_no
	 * @param string $refund_order_number
	 * @param $total_fee
	 * @param $refund_fee
	 * @param array $refund_options
	 * @param int $abort
	 * @return mixed|string
	 */
	public function refund(string $trade_no, string $refund_order_number, $total_fee, $refund_fee,
						   array $refund_options, int $abort = 1)
	{
		$this->config['cent'] && $total_fee = 1 && $refund_fee = 1;
		$refund_res = self::$instance->refund->byOutTradeNumber($trade_no, $refund_order_number, $total_fee, $refund_fee, $refund_options);
		if ($refund_res['return_code'] !== 'SUCCESS' || $refund_res['result_code'] !== 'SUCCESS') {
			if ($abort) {
				abort(-1, '微信订单退款失败');
			} else {
				abort(-1, $refund_res['err_code_des'] ?? '微信订单退款失败');
			}
		}
		return '';
	}

	/**
	 * 生成小程序太阳码
	 * @param string $scene
	 * @param array $options
	 * @param int $regenerate
	 * @return string
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
	 * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
	 */
	public function generateAppletCode(string $scene, array $options, int $regenerate = 0): string
	{
		$filepath = "/static/qrcode/{$options['dir']}/{$options['filename']}.png";

		if (file_exists(public_path() . $filepath)) {
			if ($regenerate) {
				@unlink($filepath);
			} else {
				return Request::domain() . $filepath;
			}
		}

		$res = self::$instance->app_code->getUnlimit($scene, [
			'check_path'=> false,
			'width' => $options['width'],
			'page' => $options['page']
		]);
		is_array($res) && ($res['errcode'] ?? 0) && abort(-1, $res['errmsg']);
		if ($res instanceof StreamResponse) {
			$res->saveAs("./static/qrcode/{$options['dir']}", "{$options['filename']}.png");
		}

		return Request::domain() . $filepath;
	}
}
