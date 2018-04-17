<?php
header("Content-type: text/html; charset=utf-8");//设置字符编码
if(file_exists(__DIR__ ."/TP_ENV.php")){
    require __DIR__ ."/TP_ENV.php";
}else{
    exit("need ". __DIR__ ."/TP_ENV.php");
}
if(!defined("TP_ENV")){
    exit("need set TP_ENV");
}
//命令行才能运行
if(php_sapi_name()!="cli"){
    exit("需在命令行下运行");
}
//解决框架bug不支持cli的问题,因此重新定义模型和控制器参数  其他版本无此bug  crontab.php recharge/auto_cancel/abd/34/kk/8
//define('MODE_NAME', 'cli');  //命令行模式
$depr = '/';
$params=array();
$path   = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
if(!empty($path)) {
    $params = explode($depr,trim($path,$depr));
}
//!empty($params)?$_GET['g']=array_shift($params):"";//是否传递分组
!empty($params)?$_GET['m']=array_shift($params):"";
!empty($params)?$_GET['a']=array_shift($params):"";
if(count($params)>1) {
// 解析剩余参数 并采用GET方式获取
    @preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));
}
//定义根目录
define('ROOT', realpath(dirname(__FILE__))."/");
//定义项目名称
define('APP_NAME', 'Crontab');
//定义项目路径
define('APP_PATH', dirname(__FILE__).'/../App/');
//根据开发环境设置是否开启调试模式
switch(TP_ENV){
    case 'ccc':
        define('APP_DEBUG', TRUE);
        break;
    default:
        define('APP_DEBUG', FALSE);
        break;
}
//定义缓存目录
define('RUNTIME_PATH',APP_PATH .'Runtime/'.APP_NAME .'/');
//加载框架入口文件
require APP_PATH.'/../ThinkPHP/ThinkPHP.php';