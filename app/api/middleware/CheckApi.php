<?php
declare (strict_types = 1);

namespace app\api\middleware;

use app\api\model\AppUser;
use app\api\service\Vip as SVip;
use think\cache\driver\Redis as CRedis;

use Firebase\JWT\JWT;
use think\Request;

class CheckApi
{
    /**
     * 处理请求
     * @param $request
     * @param \Closure $next
     * @return mixed|\think\response\Json
     */
    public function handle(Request $request, \Closure $next)
    {   
//        $request->user_id=$request->param('user_id');


        //关闭登录验证
        return $next($request);
        //return $next($request);
        try {
            $api_config = config('api');

			$current_path_info = explode('/', strtolower($request->pathinfo()));

            if(!isset($current_path_info[2]) && empty($current_path_info[2])) {
                $current_controller = $current_path_info[0];
            }else{
                $current_controller = $current_path_info[1];
            }
            if (in_array($current_controller, $api_config['no_login_controller'])) { // 判断是否需要验证token
                return $next($request);
            }

            if(!isset($current_path_info[2]) && empty($current_path_info[2])) {
                $current_method = $current_path_info[0] . '/' . $current_path_info[1];
            }else{
                $current_method = $current_path_info[0]. '/' .$current_path_info[1] . '/' . $current_path_info[2];
            }
            if (in_array($current_method, $api_config['no_login_node'])) { // 判断当前接口是否需要验证token
				return $next($request);
			}

//          $token = $request->header('Authorization');
            $token = $request->header('token');
//            echo $token;exit;
            if (!$token) {
                return json(['code'=> 401, 'message'=> '登录过期, 请重新登录']);
            }
            $decode = JWT::decode($token, config('jwt.jwt_key'), config('jwt.algs'));
			$c_redis = new CRedis(config('cache.stores.redis'));


            if (!isset($decode->data->user_id)) {
                return json(['code'=> 401, 'message'=> '登录过期, 请重新登录2']);
            }

//			$new_token = $c_redis->get('token_' . $decode->data->user_id);
//			if ($new_token != $token) {
//				return json(['code'=> 401, 'message'=> '您的账号已在其他设备登录']);
//			}

            $request->user_id = $decode->data->user_id;

            $m_user = new AppUser();
            $d_user = $m_user->find($request->user_id);

            if (!$d_user) {
                return json(['code'=> 401, 'message'=> '用户不存在']);
            }

            $request->session_key = $decode->data->session_key;
            $request->openid = $decode->data->openid;

// 检查vip, 先放这里, 有需求再改消息队列
// 			$s_vip = new SVip();
// 			$s_vip->vip_expire_check($request->user_id);
            return $next($request);

        }catch (\Exception $e) {
            return json(['code'=> 401, 'message'=> $e->getMessage()]);
        }
    }
}
