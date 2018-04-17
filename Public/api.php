<?php
/*
 * API接口 　访问方式　abc.com/v1/abc/def
 */
// phpinfo();exit();
header("Content-type: text/html; charset=utf-8");//设置字符编码
/*
 * 允许跨域
*/
header('Access-Control-Allow-Origin:*');//允许域名
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE,PUT');//允许操作
header('Access-Control-Allow-Headers:TOKEN');//允许头部参数

//定义根目录
define('ROOT', realpath(dirname(__FILE__))."/");
//定义项目路径
define('APP_PATH', dirname(__FILE__).'/../App/');
//定义总管理分组名
define('APP_MASTER','Api');
//定义项目名称
$php_arr=explode("/",$_SERVER['PHP_SELF']);//根据路径定义不同的模块
define('APP_NAME', ucfirst(strtolower($php_arr[2])));
//定义特殊配置
define('SPECIAL_CONFIG',APP_MASTER .'/'.strtolower(APP_NAME));
//是否使用restful风格
define('MODE_NAME', 'rest'); // 采用rest模式运行
//环境变量
define('TP_ENV',isset($_SERVER["TP_ENV"])?$_SERVER["TP_ENV"]:'production');
//根据开发环境设置是否开启调试模式
switch(TP_ENV){
	case 'production':
		define('APP_DEBUG', FALSE);
	break;
	default:
		define('APP_DEBUG', TRUE);
	break;
}
//定义缓存目录
define('RUNTIME_PATH',APP_PATH .'Runtime/'.APP_MASTER.'/'.APP_NAME .'/');
//加载框架入口文件
require dirname(__FILE__).'/../ThinkPHP/ThinkPHP.php';