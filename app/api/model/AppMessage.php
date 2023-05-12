<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class AppMessage extends TimeModel
{
	protected $deleteTime = 'delete_time';
	public function info($message_id)
	{
		return $this
			->where([
				'id'=> $message_id,
			])
			->find();
	}

}
