<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class SystemAdmin extends TimeModel
{
	public function getTagIdsAttr($v)
	{
		return $v;
    }


}
