<?php
// phpinfo();exit();
header("Content-type: text/html; charset=utf-8");//设置字符编码
session_start();//session默认开启,防止Behivior读取不到session
//定义根目录
define('ROOT', realpath(dirname(__FILE__))."/");
//定义项目名称
define('APP_NAME', 'Home');
//定义项目路径
define('APP_PATH', dirname(__FILE__).'/../App/');
//环境变量
define('TP_ENV',isset($_SERVER["TP_ENV"])?$_SERVER["TP_ENV"]:'production');
//根据开发环境设置是否开启调试模式
switch(TP_ENV){
	case 'ccc':
		define('APP_DEBUG', TRUE);
	break;
	case 'hyz':
		define('APP_DEBUG', TRUE);
	break;
	default:
		define('APP_DEBUG', TRUE);
	break;
}
//定义缓存目录
define('RUNTIME_PATH',APP_PATH .'Runtime/'.APP_NAME .'/');
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
//加载框架入口文件、
require dirname(__FILE__).'/../ThinkPHP/ThinkPHP.php';