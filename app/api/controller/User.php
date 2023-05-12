<?php
declare (strict_types=1);

namespace app\api\controller;

use app\api\model\AppUser as MAppUser;
use app\common\controller\ApiController;
use app\api\validate\User as VUser;
use app\api\model\AppUserAuth as MAppUserAuth;
use app\api\model\AppBalanceLog as MAppBalanceLog;

use think\Request;
use think\Response;
use think\response\Json;
use think\facade\Db;

class User extends ApiController
{

	/**
	 * 绑定手机号
	 * @param MAppUser $m_user
	 * @param VUser $v_user
	 * @return Json
	 */
	public function bind_phone(MAppUser $m_user, VUser $v_user)
	{
		try {
			$user_id = $this->request->user_id;
			$param = $this->request->post();

			if (!$v_user->scene('bind_phone')->check($param)) {
				return $this->api_data('ERROR_PARAM', '', $v_user->getError());
			}

			/*
			 * 保存之前需要验证两个验证码
			 */
			$c_sms = app('app\\api\\controller\\v1\\Sms');

			if (!$c_sms->getCache($param['phone'], '6', $param['code'])) {
				return $this->api_data('CODE_INCORRECT');
			}
			$d_user = $m_user->login_phone($param['phone']);
			if ($d_user) {
				return $this->api_data('DATA_EXIST', '', '手机号已存在, 请更换手机号');
			}

			$m_user->where([
				'id' => $user_id,
			])->update([
				'phone' => $param['phone']
			]);

			return $this->api_data('SUCCESS');

		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 余额明细
	 * @param MAppBalanceLog $m_balance_log
	 * @return Json
	 */
	public function balance_detail(MAppBalanceLog $m_balance_log)
	{
		try {
			$user_id = $this->request->user_id;
			$param = $this->request->get();

			$d_record = $m_balance_log
				->where([
					'user_id'=> $user_id,
				])
				->paginate();

			return $this->api_data('SUCCESS', $d_record);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 修改手机号
	 * @param MAppUser $m_user
	 * @param VUser $v_user
	 */
	public function replace_phone(MAppUser $m_user, VUser $v_user)
	{
		try {
			$user_id = $this->request->user_id;
			$param = $this->request->post();

			if (!$v_user->scene('replace_phone')->check($param)) {
				return $this->api_data('ERROR_PARAM', '', $v_user->getError());
			}

			/*
			 * 保存之前需要验证两个验证码
			 */
			$c_sms = app('app\\api\\controller\\v1\\Sms');

			if (!$c_sms->getCache($param['origin_phone'], '6', $param['origin_code'])) {
				return $this->api_data('CODE_INCORRECT');
			}
			if (!$c_sms->getCache($param['new_phone'], '6', $param['new_code'])) {
				return $this->api_data('CODE_INCORRECT');
			}

			if (!$m_user->check_user($user_id, $param['origin_phone'])) {
				return $this->api_data('DATA_NOT_EXIST', '', '该手机号不存在');
			}
			$m_user->where([
				'id' => $user_id,
			])->update([
				'phone' => $param['new_phone']
			]);

			return $this->api_data('SUCCESS');

		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 修改密码
	 * @param MAppUser $m_user
	 * @param VUser $v_user
	 */
	public function replace_password(MAppUser $m_user, VUser $v_user)
	{
		try {
			$user_id = $this->request->user_id;
			$param = $this->request->post();

			// 参数合法性校验
			if (!$v_user->scene('replace_password')->check($param)) {
				return $this->api_data('ERROR_PARAM', '', $v_user->getError());
			}

			// 验证两次密码是否输入一致
			if ($param['password'] != $param['confirm_password']) {
				return $this->api_data('PASSWORD_UNCONFORMITY');
			}

			// 手机号校验
			if (!$m_user->check_user($user_id, $param['phone'])) {
				return $this->api_data('DATA_NOT_EXIST', '', '该手机号不存在');
			}

			// 验证验证码
			$c_sms = app('app\\api\\controller\\v1\\Sms');
			if (!$c_sms->getCache($param['phone'], '4', $param['code'])) {
				return $this->api_data('CODE_INCORRECT');
			}

			$m_user->where([
				'id' => $user_id,
			])->update([
				'password' => password($param['password'])
			]);

			return $this->api_data('SUCCESS');

		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 实名认证
	 * @param MAppUser $m_user
	 * @param VUser $v_user
	 * @param MAppUserAuth $m_user_auth
	 * @return Json
	 */
	public function auth(MAppUser $m_user, VUser $v_user, MAppUserAuth $m_user_auth)
	{
		try {
			$user_id = $this->request->user_id;
			$param = $this->request->post();

			if (!$v_user->scene('auth')->check($param)) {
				return $this->api_data('ERROR_PARAM', '', $v_user->getError());
			}

			$param['user_id'] = $user_id;
			$m_user_auth->save($param);

			return $this->api_data('SUCCESS');
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 获取实名认证状态
	 * @param MAppUserAuth $m_user_auth
	 * @return Json
	 */
	public function auth_status(MAppUserAuth $m_user_auth)
	{
		try {
			$user_id = $this->request->user_id;

			$d_auth = $m_user_auth->where([
				'user_id'=> $user_id,
			])->order('id', 'desc')->find();

			return $this->api_data('SUCCESS', $d_auth);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	/**
	 * 获取用户信息
	 *
	 * @param MAppUser $m_user
	 * @return Response
	 */
	public function info(MAppUser $m_user)
	{
		try {
			$user_id = $this->request->user_id;
			$d_user = $m_user->info($user_id);

			return $this->api_data('SUCCESS', $d_user);
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}



	/**
	 * 编辑用户资料
	 * @param MAppUser $m_user
	 * @return Json
	 */
	public function edit(MAppUser $m_user)
	{
		try {
			$param = $this->request->param();
// 			print_r($param);exit;
			$user_id = $this->request->user_id;
            $up=[];
            if(isset($param["nickname"]))$up["nickname"]=$param["nickname"];          //昵称
            if(isset($param["avatar"]))$up["avatar"]=$param["avatar"];                //头像
            if(isset($param["phone"]))$up["phone"]=$param["phone"];                    //手机
            if(isset($param["sex"]))$up["sex"]=$param["sex"];                         //性别
            if(isset($param["province"]))$up["province"]=$param["province"];        //省
            if(isset($param["city"]))$up{"city"}=$param["city"];                    //市
            if(isset($param["area"]))$up["area"]=$param["area"];                    //区
            if(isset($param["about"]))$up["about"]=$param["about"];
            if(isset($param["images"]))$up["images"]=$param["images"];
            if(isset($param["birth_address"])){
                $up["birth_address"]=$param["birth_address"];
                if(!isset($param["birth_code"])){
                    $up["birth_code"]=Db::name("system_area")->where("area_name",$up["birth_address"])->value("area_id");
                }
            }
            if(isset($param["birth_code"])){
                $up["birth_code"]=$param["birth_code"];
                if(!isset($param["birth_address"])){
                    $up["birth_address"]=Db::name("system_area")->where("area_id",$up["birth_code"])->value("area_name");
                }
            }
            if(isset($param["live_address"]))$up["live_address"]=$param["live_address"];
            if(isset($param["live_code"]))$up["live_code"]=$param["live_code"];
            if(isset($param["height"]))$up["height"]=$param["height"];
            if(isset($param["weight"]))$up["weight"]=$param["weight"];
            if(isset($param["work_unit"]))$up["work_unit"]=$param["work_unit"];
            if(isset($param["work"]))$up["work"]=$param["work"];
            if(isset($param["school"]))$up["school"]=$param["school"];
            if(isset($param["education"]))$up["education"]=$param["education"];
            if(isset($param["income"]))$up["income"]=$param["income"];
            if(isset($param["marry"]))$up["marry"]=$param["marry"];
            if(isset($param["has_child"]))$up["has_child"]=$param["has_child"];
            if(isset($param["birthday"])){
                $up["birthday"]=$param["birthday"];
                //不传星座自动由生日换算
                if(!isset($param["constellation"]))$param["constellation"]=$this->birthday2constellation($param["birthday"]);
                //不传属相自动由生日换算
                if(!isset($param["animal"]))$param["animal"]=$this->birthday2animal($param["birthday"]);
            }
            if(isset($param["constellation"]))$up["constellation"]=$param["constellation"];
            if(isset($param["animal"]))$up["animal"]=$param["animal"];
            
			$m_user->where('id',$user_id)->update($up);

			return $this->api_data('SUCCESS');
		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}
	
	
	public function birthday2constellation($birthday=''){
	    $month = substr($birthday,5,2);//截取月
    	$day = substr($birthday,8,2);//截取日
    	$day = intval($day);
    	$month = intval($month);

    	if ($month<1 || $month>12 || $day<1 || $day>31) return '';
    	$signs=[
    		'120'=>'水瓶座',
    		'219'=>'双鱼座',
    		'321'=>'白羊座',
    		'420'=>'金牛座',
    		'521'=>'双子座',
    		'622'=>'巨蟹座',
    		'723'=>'狮子座',
    		'823'=>'处女座',
    		'923'=>'天秤座',
    		'1024'=>'天蝎座',
    		'1122'=>'射手座',
    		'1222'=>'摩羯座',
    	];
    	$n=intval($month.($day<10?"0".$day:$day));
       
    	if($n>1222){
    	    $name='水瓶座';
    	}else{
    	    foreach ($signs as $key=>$value) {
        	    if($n<$key) break;
        	    $name=$value;
        	}
    	}
    	return $name?:'';
    }
    
    public function birthday2animal($birthday=''){
    	$year=substr($birthday,0,4);
    	$animals=['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
    	$key=($year-1900)%12;
    	return $animals[$key]?:'';
    }


}
