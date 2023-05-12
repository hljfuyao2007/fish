<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class AppCity extends TimeModel
{

	protected $deleteTime = 'delete_time';
	public function province()
	{
		return $this->belongsTo(AppProvince::class, 'province_id');
	}

}
