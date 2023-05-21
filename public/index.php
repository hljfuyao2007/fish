<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
header('Access-Control-Allow-Origin:*');
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
$allow_origin = array();
if(true || in_array($origin, $allow_origin)){
    header('Access-Control-Allow-Origin:'.$origin);
}
header("Access-Control-Allow-Methods:GET,POST,OPTIONS,DELETE");
header("Access-Control-Allow-Credentials:true");
header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding,Authorization,language_id,Cookies,Cookie");

require __DIR__ . '/../vendor/autoload.php';

// 声明全局变量
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS . '..' . DS);

// 判断是否安装程序
if (!is_file(ROOT_PATH . 'config' . DS . 'install' . DS . 'lock' . DS . 'install.lock')) {
    exit(header("location:/install.php"));
}

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
