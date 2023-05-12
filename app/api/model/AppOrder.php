<?php
declare (strict_types = 1);

namespace app\api\model;

use app\common\model\TimeModel;
use think\Model;

/**
 * @mixin \think\Model
 */
class AppOrder extends TimeModel
{
	protected $deleteTime = 'delete_time';
	public function getList($user_id, $type)
	{
		$where = [
			'user_id' => $user_id,
			'status'=> 1,
			'is_invoice' => $type,
		];
		$list = $this
			->where($where)
			->append(['out_detail'])
			->order('id desc')
			->paginate();
		return $list;
	}

	public function getOutDetailAttr($v, $data)
	{
		if ($data['type'] == 1) {
			// identify detail
			$m_identify = new AppIdentify();
			$d_detail = $m_identify->where('id', $data['out_id'])->find();
			$d_detail['title'] = '伤情鉴定';
		} else {
			// vip record detail
			$m_vip = new AppVip();
			$d_detail = $m_vip->where('id', $data['out_id'])->find();
			$d_detail['title'] = '开通会员';
			$d_detail['report'] = $d_detail['icon'];
		}
		$d_detail['pay_price'] = $data['amount'];

		return $d_detail;
	}
}
