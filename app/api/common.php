<?php
// api通用函数


use Firebase\JWT\JWT;
use think\cache\driver\Redis as CRedis;

use Mpdf\Mpdf;

function _n_to_br($content) {
	$content = str_replace("\n", "<br>", $content);
	return $content;
}


/**
 * imagick pdf转图片
 * @param $pdf
 * @return string
 */
function pdf2image($pdf)
{
	try {
		$lib_imagick = new Imagick();
		$lib_imagick->setResolution(500, 500);
		$lib_imagick->readImage($pdf);
		$lib_imagick->setImageFormat('jpg');

		$path = 'upload/image/' . date('Y-m') . '/' . date('d') . '/';

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$file_name = md5(time()) . '.jpg';


		$lib_imagick->writeImage($path . $file_name);

		$lib_imagick->clear();
		$lib_imagick->destroy();

		return $path . $file_name;
	} catch (\Exception $exception) {
		exit($exception->getMessage());
	}

}

if (!function_exists('curl')) {
	/**
	 * curl提交
	 * @param $type integer 1 get 2 post
	 * @param $url  string  访问的地址
	 * @param $param  array 请求的数据
	 * @param int $is_ignore
	 * @return mixed|string    返回的数据
	 */
	function curl(int $type, string $url, $param = [], $is_ignore = 0, $headers = [])
	{
		$_param = '';
		if (array_key_exists('sig', $param))
			$param['sig'] = urlencode($param['sig']);
		if ($type == 1) {
			foreach ($param as $key => $item) {
				$_param .= $key . "=" . $item . "&";
			}
			$url .= "?" . rtrim($_param, '&');
		} else {
			$_param = json_encode($param);
		}
		//初始化
		$curl = curl_init();
		if ($is_ignore) {
			ignore_user_abort();
		}
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, 0);
//        //设置header
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $url);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if ($type == 2) {
			//设置post方式提交
			curl_setopt($curl, CURLOPT_POST, 1);
			//设置post数据
			curl_setopt($curl, CURLOPT_POSTFIELDS, $_param);
		}


		//执行命令
		$data = curl_exec($curl);
		//关闭URL请求
		curl_close($curl);
//		exit($data);
		return json_decode($data, true);
	}

}


function bad_word_filter($str = '')
{

	$words = file_get_contents('bannedwords/pub_banned_words.out.txt');

	$format_words = explode('|', $words);

	$bad_words = array_combine($format_words, array_fill(0, count($format_words), '**'));

	$res = strtr($str, $bad_words);

	return $res;
}


function gen_dates_by_range($start, $end)
{
	$dt_start = strtotime($start);
	$dt_end = strtotime($end);
	$res = [];
	while ($dt_start <= $dt_end) {
		$res[] = date('Y-m-d', $dt_start);
		$dt_start = strtotime('+1 day', $dt_start);
	}
	return $res;
}


if (!function_exists('listToTree')) {
	//数组转成树形
	function listToTree($list, $pk = 'id', $pid = 'parent_id', $child = 'children', $root = 0)
	{
		if (!is_array($list)) {
			return [];
		}

		// 创建基于主键的数组引用
		$aRefer = [];
		foreach ($list as $key => $data) {
			$aRefer[$data[$pk]] = &$list[$key];
		}

		$tree = [];
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = $data[$pid];
			if ($root === $parentId) {

				$tree[] = &$list[$key];

			} else {
				if (isset($aRefer[$parentId])) {
					$parent = &$aRefer[$parentId];
					$parent[$child][] = &$list[$key];
				}
			}
		}

		return $tree;
	}
}


if (!function_exists('encode_phone')) {
	/**
	 * 遮挡手机号中间四位
	 * @param $phone
	 * @return string|string[]
	 */
	function encode_phone($phone)
	{
		return substr_replace($phone, '****', 3, 4);
	}

}


if (!function_exists('contextualTime')) {
	/**
	 * friendly time
	 * @param $small_ts
	 * @param false $large_ts
	 * @return false|string
	 */
	function contextualTime($small_ts, $large_ts = false)
	{
		if (!$large_ts) $large_ts = time();
		$n = $large_ts - $small_ts;
		if ($n <= 1) return 'less than 1 second ago';
		if ($n < (60)) return $n . ' seconds ago';
		if ($n < (60 * 60)) {
			$minutes = round($n / 60);
			return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
		}
		if ($n < (60 * 60 * 16)) {
			$hours = round($n / (60 * 60));
			return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
		}
		if ($n < (time() - strtotime('yesterday'))) return 'yesterday';
		if ($n < (60 * 60 * 24)) {
			$hours = round($n / (60 * 60));
			return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
		}
		if ($n < (60 * 60 * 24 * 6.5)) return 'about ' . round($n / (60 * 60 * 24)) . ' days ago';
		if ($n < (time() - strtotime('last week'))) return 'last week';
		if (round($n / (60 * 60 * 24 * 7)) == 1) return 'about a week ago';
		if ($n < (60 * 60 * 24 * 7 * 3.5)) return 'about ' . round($n / (60 * 60 * 24 * 7)) . ' weeks ago';
		if ($n < (time() - strtotime('last month'))) return 'last month';
		if (round($n / (60 * 60 * 24 * 7 * 4)) == 1) return 'about a month ago';
		if ($n < (60 * 60 * 24 * 7 * 4 * 11.5)) return 'about ' . round($n / (60 * 60 * 24 * 7 * 4)) . ' months ago';
		if ($n < (time() - strtotime('last year'))) return 'last year';
		if (round($n / (60 * 60 * 24 * 7 * 52)) == 1) return 'about a year ago';
		if ($n >= (60 * 60 * 24 * 7 * 4 * 12)) return 'about ' . round($n / (60 * 60 * 24 * 7 * 52)) . ' years ago';
		return false;
	}

}


if (!function_exists('gen_jwt_token')) {
	/**
	 * 生成token
	 * @param array $param
	 * @return mixed
	 */
	function gen_jwt_token($param = [])
	{
		$time = time();
		$en_data = [
			'iat' => $time,
			'exp' => $time + config('jwt.expire_time'),
			'data' => $param
		];

		$token = JWT::encode($en_data, config('jwt.jwt_key'),"HS256","0000");

		$c_redis = new CRedis(config('cache.stores.redis'));
		$c_redis->set('token_'. $param['user_id'], $token);

		return $token;
	}

}

if (!function_exists('build_order_no')) {
	//生成唯一订单号
	function build_order_no()
	{
		return date('Ymdhis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}
}

if (!function_exists('random_nickname')) {
	/**
	 * 生成随机默认用户名
	 * @param int $length
	 * @return mixed
	 */
	function random_nickname($length = 6)
	{
		$validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ1234567890";
		$validCharNumber = strlen($validCharacters);
		$result = "";
		for ($i = 0; $i < $length; $i++) {
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}
		return '用户_' . $result;
	}

}



