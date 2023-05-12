<?php

return [
    'SUCCESS'=> [
        'code'=> 0,
        'msg'=> '操作成功',
    ],
    'MISSING_PARAM'=> [
        'code'=> -1,
        'msg'=> '缺少参数',
    ],
    'ERROR_PARAM'=> [
        'code'=> -2,
        'msg'=> '参数错误',
    ],
    'DATA_EXIST'=> [
        'code'=> -3,
        'msg'=> '数据已存在'
    ],
    'DATA_NOT_EXIST'=> [
        'code'=> -4,
        'msg'=> '数据不存在'
    ],

    'NOT_PAY'=> [
        'code'=> -5,
        'msg'=> '请支付后查看'
    ],

    'NO_BIND_COMPANY'=> [
        'code'=> -6,
        'msg'=> '暂未绑定公司'
    ],
    'REPEAT_OPERATE'=> [
        'code'=> -7,
        'msg'=> '重复操作'
    ],
    'INVALID_OPERATE'=> [
        'code'=> -8,
        'msg'=> '无效操作'
    ],
    'UN_KNOW_ERROR'=> [
        'code'=> -9,
        'msg'=> '位置错误'
    ],

    'UNSUPPORTED_FILE_TYPE'=> [
        'code'=> -10,
        'msg'=> '不支持的文件类型'
    ],

    'INVALID_SHARE'=> [
        'code'=> -11,
        'msg'=> '无效的分享链接'
    ],

	'PASSWORD_UNCONFORMITY'=> [
		'code'=> -12,
		'msg'=> '两次输入密码不一致'
	],
	'BALANCE_LACK'=> [
		'code'=> -13,
		'msg'=> '余额不足'
	],

    'AUTHORIZATION_EXPIRE'=> [
        'code'=> 401,
        'msg'=> '登录过期, 请重新登录'
    ],

    'UN_AUTH'=> [
        'code'=> -601,
        'msg'=> '未实名认证'
    ],

    'CODE_EXCEPTION'=> [
        'code'=> -100,
        'msg'=> '代码异常',
    ],
    'DB_EXCEPTION'=> [
        'code'=> -101,
        'msg'=> '数据库异常',
    ],


	'FREQUENT_OPERATION'=> [
		'code'=> -700,
		'msg'=> '操作频繁'
	],
	'CODE_INCORRECT'=> [
		'code'=> -701,
		'msg'=> '验证码错误'
	],

    'OS_ERROR'=> [
        'code'=> -666,
        'msg'=> '验证码错误'
    ],


];