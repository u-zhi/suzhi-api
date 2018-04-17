<?php
/*
 * 定义自动加载规则,App/Lib/Library 里面的类库,自动加载 ,如 类Abcd,对应文件App/Lib/Library/Abcd.class.php自动加载
 * 类 Tool_Abcd,对应 App/Lib/Library/Tool/Abcd.class.php自动加载
 * 多层目录
 * @.Library/ 里面的类库也适用,优先 @.Library/
 */
spl_autoload_register(function($class){
    $file       =   str_replace("_","/",$class.'.class.php');
	if(require_array(array(
		APP_PATH . C('APP_GROUP_PATH') ."/".APP_NAME.'/Library/'.$file,
		LIB_PATH.'Library/'.$file,
	),true)) {
		return ;
	}
});
//加载分组模块下的自定义函数文件
load('common',APP_PATH .C('APP_GROUP_PATH')."/".APP_NAME.'/Common/');
//加载拓展目录下的函数文件
load("extend");
//获取助手类方法
function helper($help_class_name=''){
    if(!$help_class_name){$help_class_name='Common';}
    static $class_container=array();
    if(isset($class_container[$help_class_name])){
        return $class_container[$help_class_name];
    }
    $file=$help_class_name.'.class.php';
    require_array(array(
        APP_PATH .C('APP_GROUP_PATH')."/".APP_NAME.'/Widget/'.$file,
    ),true);
    $help_class_name.="Helper";
    $class=new $help_class_name;
    $class_container[$help_class_name]=$class;
    return $class;
}
/**
 * 对密码进行加密
 * $password 待加密字符串
 */
function encrypt_password($password){
    return md5(md5($password."ranmiaozai".$password)."mutouren");
}
/*
 * 加密字符串(可解密)
*/
function encrypt_str($str){
    import("ORG.Crypt.Crypt");
	$crypt=new Crypt();
    return $crypt->encrypt($str,"ranmiaozai_mutouren");
}
/*
 * 解密字符串
*/
function decrypt_str($str){
    import("ORG.Crypt.Crypt");
	$crypt=new Crypt();
    return $crypt->decrypt($str,"ranmiaozai_mutouren");
}
/*
 * 生成缩略图地址
*/
function thumb_image($image_url,$width,$height){
    static $image_object=null;
    $image_object==null && $image_object=new Tool_Image();
    return $image_object->thumb_url($image_url,$width,$height);
}
/*
 * 将图片生成base64编码输出
*/
function base64_image($image_file){
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
/*
 * 生成二维码
*/
/**
 * 调用phpqrcode生成二维码
 * @param string $text 二维码解析的地址或者内容
 * @param int $level 二维码容错级别
 * @param int $size 需要生成的图片大小
 * @param string $save_file 保存文件
 */
function qrcode($text,$level = 3, $size = 4){
    Vendor('Qrcode.phpqrcode');
    //容错级别
    $errorCorrectionLevel = intval($level);
    //生成图片大小
    $matrixPointSize = intval($size);
    //生成二维码图片
    $path=C("image_path")."qrcode";
    if(!file_exists($path)){
        mkdir($path);
    }
    $file_name=md5(urlencode($text)).".png";
    $save_file=$path ."/".$file_name;
    if(file_exists($save_file)){
        return "/uploads/qrcode/".$file_name;
    }
    QRcode::png($text, $save_file, $errorCorrectionLevel, $matrixPointSize, 2);
    return "/uploads/qrcode/".$file_name;
}
//生成token
function create_token($user_id){
    import("ORG.Crypt.Crypt");
    $crypt=new Crypt();
    $str=json_encode(array(
        "user_id"=>$user_id,
        "time"=>time(),
    ));
    return base64_encode($crypt->encrypt($str,"ranmiaozai_mutouren"));
}
//解析token
function decode_token($token){
    import("ORG.Crypt.Crypt");
    $crypt=new Crypt();
    $str=$crypt->decrypt(base64_decode($token),"ranmiaozai_mutouren");
    if($str && $result=json_decode($str,true)){
        if($result["time"]>time()-30*86400){
            return $result["user_id"];
        }
    }
    return false;
}
function cut_str($str,$len,$suffix="..."){
    if(function_exists('mb_substr')){
        if(strlen($str) > $len){
            $str= mb_substr($str,0,$len,"utf-8").$suffix;
        }
        return $str;
    }else{
        if(strlen($str) > $len){
            $str= substr($str,0,$len).$suffix;
        }
        return $str;
    }
}
function get_apply($isflag,$item) {
    if(!$isflag)
        return "邀请面试后显示";
    else
        return $item;
}
