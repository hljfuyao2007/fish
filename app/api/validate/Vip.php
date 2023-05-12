<?php
namespace app\api\validate;

use think\Validate;

class Vip extends Validate
{
    protected $rule = [
        'vip_id'  =>  'require',
        'platform'  =>  'require',
    ];

    protected $message  =   [
        'vip_id.require' => '会员种类ID不能为空',
        'platform.require' => '支付渠道不能为空',
    ];

    protected $scene = [
        'activate'  =>  ['vip_id', 'platform'],
    ];

}