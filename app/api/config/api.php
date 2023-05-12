<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

return [

    // 不需要验证登录的控制器
    'no_login_controller' => [
        'login',
//		'test',
//      'index',
		'common',
//		'lawyer',
		'sms',
//		'knowledge',
		'callback',
//  'identify'

    ],

    // 不需要验证登录的节点
    'no_login_node'       => [
        'v1/message/index',
        'v1/identify/options_select',
        'v1/identify/get_pdf',
        'index/agreement_privacy',
        'index/agreement_user'
//        'index/get_phone'
        // 'v1/identify/detail',
        // '/v1/message/publish'
    ],

];