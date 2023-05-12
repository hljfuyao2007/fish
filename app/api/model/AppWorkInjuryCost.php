<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class AppWorkInjuryCost extends TimeModel
{
	protected $deleteTime = 'delete_time';

}
