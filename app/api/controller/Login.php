<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\validate\User as VUser;
use app\common\controller\ApiController;
use app\api\validate\Login as VLogin;
use app\admin\model\LetUser as MAppUser;
use think\facade\Cache;
use think\response\Json;
use app\common\service\EasyWechat as SEasyWechat;
use think\facade\Db;
use app\admin\model\AppSale;

class Login extends ApiController {

    
    public function ceshi_login( MAppUser $m_user)
	{
		$param = $this->request->param();
		$d_user =$m_user->where([
				'id'=> 1,
			])->find();
        $d_user['token']=gen_jwt_token(['user_id'=> $d_user['id'], 'openid'=> $d_user["open_id"], 'session_key'=> ""]);
		return $this->api_data('SUCCESS', $d_user);

	}

	/**
	 * 微信授权登录
	 * @param VLogin $v_login
	 * @param MAppUser $m_user
	 * @return Json
	 */
	public function wechat(VLogin $v_login, MAppUser $m_user)
	{
		try {
			$param = $this->request->post();
			$param['invite_user_id'] = $param['invite_user_id'] ?? 0;
			$code = $param['code'] ?? '';
			$platform = $code ? 1 : 2; // 1小程序 2app
			if ($code) { // 有code是小程序登录
				$s_east_wechat = new SEasyWechat('JSAPI');
				$auth_info = $s_east_wechat->applet_info($param['code']);
				$open_id = $auth_info['open_id'];
				$union_id= $auth_info['union_id']??"";
				$session_key = $auth_info['session_key'];
			}else { // 没有code是app登录
				$open_id = $param['open_id'];
				$union_id= $param['union_id']??"";
				$session_key = '';
			}
			$d_user = $m_user->login_wechat($open_id);
//			$d_user =$m_user->where([
//				'open_id'=> $open_id,
//			])->find();
			//->field($this->common_field)
            //$open_id
            $local_url=$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']."/";
			if (!$d_user) {
				$i_data = [
					'unionid'=> $union_id,
					'parent_id'=> $param['invite_user_id'],
					'nickname'=> $param['nickname'] ?? random_nickname(),
					'sex'=> $param['sex'] ?? 1,
					'avatar'=> $param['avatar'] ?? $local_url.'/static/common/images/head.png',
					'openid'=>$open_id,
                    'sale_id'=>0
//					'id_num'=>$this->rand_num()
				];
				// $i_data[$platform == 1 ? 'openid' : 'openid_app'] = $open_id;
				// 创建用户
				$m_user->save($i_data);

				if ($param['invite_user_id']) {
					// TODO 绑定上下级
				}
//				$d_user =$m_user->where([
//    				'openid'=> $open_id,
//    			])->find();
                $d_user = $m_user->login_wechat($open_id);
				//$d_user = $m_user->login_union_id($union_id);
			} else {
                if( $d_user["status"] == 0 ){
                    return $this->api_data('CODE_EXCEPTION', '', "账户已停用");
                }
				$u_data = [
					'nickname'=> $param['nickname'] ?? $d_user['nickname'],
					'sex'=> $param['sex'] ?? $d_user['sex'],
					'avatar'=> $param['avatar'] ?? $d_user['avatar'],
				];
				//$u_data[$platform == 1 ? 'openid' : 'openid_app'] = $open_id;
				$m_user->where(['id'=> $d_user['id']])->update($u_data);
			}

            $sale=AppSale::where("user_id",$d_user["id"])->find();
            if(!$sale ){
                $sale_type=0;
            }else {
                $sale["phone"]=$d_user["phone"];
                $sale_type=$sale["identity"];
            }

			$response = [
				'user_info'=> $d_user,
                'sale_type'=> $sale_type,
                'sale_info'=> $sale,
				'token'=> gen_jwt_token(['user_id'=> $d_user['id'], 'openid'=> $open_id, 'session_key'=> $session_key]),
			];
			return $this->api_data('SUCCESS', $response);
		} catch (\Exception $exception) {
			c_log($exception->getMessage(), 'code_exception');
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

	 /**
	 * 忘记密码
	 * @param MAppUser $m_user
	 * @param VUser $v_user
	 */
	public function forget_password(MAppUser $m_user, VUser $v_user)
	{
		try {
			$param = $this->request->post();
			// 参数合法性校验
			if (!$v_user->scene('forget_password')->check($param)) {
				return $this->api_data('ERROR_PARAM', '', $v_user->getError());
			}
			// 验证两次密码是否输入一致
			if ($param['password'] != $param['confirm_password']) {
				return $this->api_data('PASSWORD_UNCONFORMITY');
			}

			// 手机号校验
			if (!$m_user->login_phone($param['phone'])) {
				return $this->api_data('DATA_NOT_EXIST', '', '该手机号不存在');
			}

			// 验证验证码
			$c_sms = app('app\\api\\controller\\v1\\Sms');
			if (!$c_sms->getCache($param['phone'], '2', $param['code'])) {
				return $this->api_data('CODE_INCORRECT');
			}

			$m_user->where([
				'phone' => $param['phone'],
			])->update([
				'password' => password($param['password'])
			]);

			return $this->api_data('SUCCESS');

		} catch (\Exception $exception) {
			return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
		}
	}

    /**
     * 手机号登录
     * @param MAppUser $m_user
     * @param VUser $v_user
     */
    public function phone(MAppUser $m_user, VUser $v_user)
    {

        try {
            $param = $this->request->param();

            // 手机号校验
            $d_user=$m_user->login_phone($param['phone']);


            if (empty($d_user)) {
                return $this->api_data('DATA_NOT_EXIST', '', '该手机号不存在');
            }

            // 验证验证码
            $c_sms = app('app\\api\\controller\\v1\\Sms');
            if (false && !$c_sms->getCache($param['phone'], '2', $param['code'])) {
                return $this->api_data('CODE_INCORRECT');
            }

//            $d_user=$m_user->where([
//                'phone' => $param['phone'],
//            ])->find();
            $sale=AppSale::where("user_id",$d_user["id"])->find();
            if(!$sale ){
                $sale_type=0;
                return $this->api_data('DATA_NOT_EXIST', '', '您不是员工，请使用微信登录');

            }else {
                $sale["phone"]=$d_user["phone"];
                $sale_type=$sale["identity"];
            }

            $session_key = 'session_key';
            $response = [
                'user_info'=> $d_user,
                'sale_type'=> $sale_type,
                'sale_info'=> $sale,
                'token'=> gen_jwt_token([
                    'user_id'=> $d_user['id'],
                    'openid'=> $d_user["openid"],
                    'session_key'=> $session_key
                ]),
            ];
            return $this->api_data('SUCCESS', $response);

        } catch (\Exception $exception) {
            return $this->api_data('CODE_EXCEPTION', '', $exception->getMessage());
        }
    }

	public function getOS()
	{	
		$param = $this->request->param();
		return $param["os"];

	}
	public function checkOS($user_id,$os){

			//$os=$this->getOS();

			$res=Db::name("login_os")->where("user_id",$user_id)->where("value",$os)->find();
			if(!$res){
				return false;//$this->api_data('OS_ERROR', '', '本次登陆需要身份验证');
			}else{
				$last_login_time=strtotime($res["update_time"]);
				if(($last_login_time+60*60*24*180)<time()){
					return false;
				}
				Db::name("login_os")->where("id",$res["id"])->update(["update_time"=>date("Y-m-d H:i:s")]);
				return true;
			}

	}
	
	
	public function rand_num(MAppUser $u){
	    $num=rand(9999999,99999999);
	    if($u->where("id_num",$num)->find()){
	        $num=$this->rand_num();
	    }
	    return $num;
	    
	}


}