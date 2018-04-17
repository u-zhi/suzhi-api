<?php
//通用配置
return array(
	'HTTP_TYPE'=>'http',//站点http类型 http || https
    'URL_CASE_INSENSITIVE' =>true,//url访问不区分大小写
	'LOAD_EXT_CONFIG'=>defined('SPECIAL_CONFIG')?TP_ENV .",".SPECIAL_CONFIG:TP_ENV,//自动加载配置文件
	//"LOAD_EXT_FILE"=>"",//自动加载函数库文件
	'APP_AUTOLOAD_PATH' =>'@.Library',//自动加载类库
    'APP_GROUP_LIST' => APP_NAME, //项目分组设定
    'APP_GROUP_MODE' => 1, //独立分组
    'DEFAULT_GROUP' => defined('APP_MASTER')?'Home':APP_NAME ,//默认分组
    'APP_GROUP_PATH'=>'Modules'.(defined('APP_MASTER')?'/'.APP_MASTER:''),//分组再进行分组
    'URL_MODEL'=>defined('APP_MASTER')?2:1,//分组再进行分组
    'SESSION_PREFIX' => defined('APP_MASTER')?APP_MASTER."_".APP_NAME:APP_NAME,//默认session前缀,防止多模块冲突
    'COOKIE_PREFIX' => defined('APP_MASTER')?APP_MASTER."_".APP_NAME:APP_NAME,//默认cookie前缀,防止多模块冲突
    'COOKIE_EXPIRE' => time()+86400*365*100,//cookie的过期时间默认为2年
    'DATA_CACHE_SUBDIR' => true,//文件缓存开启子目录存储
    'DATA_PATH_LEVEL' => 3,//文件缓存子目录层次
    'DATA_CACHE_PREFIX' => defined('APP_MASTER')?APP_MASTER."_".APP_NAME:APP_NAME,//默认缓存前缀,防止多模块冲突
    'LAYOUT_ON' => TRUE,//开启layout
	//默认错误跳转对应的模板文件
	'TMPL_ACTION_ERROR' => '../dispatch_jump',
	//默认成功跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' =>'../dispatch_jump',
	//开启报错
	'SHOW_ERROR_MSG' => true,
	//伪静态后缀
	'URL_HTML_SUFFIX'=>'',
	//视图主题设定  $this->view->theme('test');
	'DEFAULT_THEME' => 'default',
	'THEME_LIST' => 'default',//主题列表
	'TMPL_DETECT_THEME' => true,//是否自动检测主题 host?t=default
    //数据库配置
    'DB_TYPE'			 => 'Mysql',
	'DB_HOST'			 => '127.0.0.1',
	'DB_USER'			 => 'root',
	'DB_PWD'			 => '123456',
	'DB_PORT'			 => '3306',
	'DB_NAME'			 => 'suzhi_erp',
	'DB_PREFIX'			 => 'sz_',
	'DB_CHARSET'		 => 'utf8',
	'DB_FIELDTYPE_CHECK' => false,
    //图片设置相关
    'image_path' => ROOT ."uploads/",//图片保存路径
    'image_thumb_pass' => 'ranmiaozai_mutouren',//缩略图加密串
    'image_thumb_timeout' => 5,//缩略图生成超时时间
    'image_thumb_adjust' => true ,//缩略图是否遵循原来比例
    //erp站点
    'erp_url'=>'http://erp.91suzhi.com',
    //支付宝回调地址
    'alipay_return_url'=>'http://enterprise.91suzhi.com/user/recharge_back', //同步通知地址
    'alipay_notify_url'=>'http://enterprise.91suzhi.com/apipay/notify', //异步通知地址
);