<?php
declare (strict_types = 1);

namespace app\api\validate;

use think\Validate;

class Message extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
    	'title'=> 'require',
		'phone'=> 'require',
		'message_id'=> 'require',
	];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
    	'title.require'=> '标题不能为空',
    	'phone.require'=> '电话不能为空',
		'message_id.require'=> '留言ID不能为空'
	];


    protected $scene = [
		'publish'=> ['title', 'phone'],
		'info'=> ['message_id'],
	];
}
