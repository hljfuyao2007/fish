<?php
declare (strict_types = 1);

namespace app\api\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
    	'phone'=> 'require',
		'code'=> 'require',
		'password'=> 'require',
		'confirm_password'=> 'require',
	];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
    	'phone.require'=> '手机号不能为空',
		'code.require'=> '验证码不能为空',
		'password'=> '密码不能为空',
		'confirm_password.require'=> '确认密码不能为空',
	];


    protected $scene = [
    	'phone'=> ['phone', 'code'],
		'password'=> ['phone', 'password'],

		'reg'=> ['phone', 'code', 'password', 'confirm_password'],
	];

}
