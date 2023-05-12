<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class AppVipRecord extends TimeModel
{
	protected $deleteTime = 'delete_time';


	public function vip()
	{
		return $this->belongsTo(AppVip::class, 'vip_id', 'id');
	}

	public function getExpireTimeAttr($v)
	{
		return $v;
		return date('Y-m-d H:i:s', $v);
	}
}
