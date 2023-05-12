<?php
/**
 * 微信移动应用配置信息
 */
return [
	'cent'    => true,  // 一分钱支付
	'JSAPI'   => [
		'app_id'        => 'wx23d51efaf096afdb',
		'secret'        => 'a15e929583fe1e427775fd91542ec2d1',
		'response_type' => 'array',
	],
	'APP'     => [
		'app_id'        => 'wx23d51efaf096afdb',
		'secret'        => 'a15e929583fe1e427775fd91542ec2d1',
		'response_type' => 'array',
	],
    'APPLET'     => [
        'app_id'        => 'wx23d51efaf096afdb',
        'secret'        => 'a15e929583fe1e427775fd91542ec2d1',
        'response_type' => 'array',
    ],
	'payment' => [
		'mch_id'    => '1623036425',
		'key'       => '',
		'cert_path' => root_path() . 'cert/wechat/apiclient_cert.pem',
		'key_path'  => root_path() . 'cert/wechat/apiclient_key.pem',

		'notify_url'=> request()->domain() .'/api/v1/callback/wechat_pay',
	]
];
