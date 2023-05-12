<?php
namespace app\api\validate;

use think\Validate;

class Sms extends Validate
{
    protected $rule = [
        'phone'  =>  'require',
        'type'  =>  'require',
    ];

    protected $message  =   [
        'phone.require' => '手机号不能为空',
        'type.require' => '场景类型不能为空',
    ];

    protected $scene = [
        'send'  =>  ['phone', 'type'],
    ];

}