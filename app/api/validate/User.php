<?php
declare (strict_types = 1);

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
    	'name'=> 'require',
    	'id_card_number'=> 'require',
    	'id_card_image_front'=> 'require',
    	'id_card_image_verso'=> 'require',

		'origin_phone'=> 'require',
		'new_phone'=> 'require',
		'origin_code'=> 'require',
		'new_code'=> 'require',

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
    	'name.require'=> '姓名不能为空',
    	'id_card_number.require'=> '身份证号不能为空',
    	'id_card_image_front.require'=> '身份证正面图片不能为空',
    	'id_card_image_verso.require'=> '身份证反面图片不能为空',

		'origin_phone.require'=> '原号码不能为空',
		'new_phone.require'=> '新号码不能为空',
		'origin_code.require'=> '原号码验证码不能为空',
		'new_code.require'=> '新号码验证码不能为空',

		'phone.require'=> '手机号不能为空',
		'code.require'=> '验证码不能为空',
		'password.require'=> '密码不能为空',
		'confirm_password.require'=> '确认密码不能为空',
	];

    protected $scene = [
    	'auth'=> ['name', 'id_card_number', 'id_card_image_front', 'id_card_image_verso'],
		'replace_phone'=> ['origin_phone', 'new_phone', 'origin_code', 'new_code'],
		'replace_password'=> ['phone', 'code', 'password', 'confirm_password'],
		'forget_password'=> ['phone', 'code', 'password', 'confirm_password'],
		'bind_phone'=> ['phone', 'code'],
	];
}
