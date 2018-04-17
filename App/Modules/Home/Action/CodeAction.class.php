<?php
/*
 * 验证码
*/
class CodeAction extends Action {

    public function index($verify_name=''){
        //验证码长度
        $length=intval($this->_get("length"));
        $length=$length?$length:4;
        //验证码类型 0 字母 1 数字 2 大写字母 3 小写字母 4中文 5混合
        $mode=$this->_get("mode");
        $mode=is_numeric($mode)?$mode:1;
        //验证码图片类型
        $type="png";
        //验证码宽度、高度
        $width=intval($this->_get("width"));
        $height=intval($this->_get("height"));
        if($mode==4){
            $width=$width?$width:180;
            $height=$height?$height:50;
        }else{
            $width=$width?$width:48;
            $height=$height?$height:22;
        }
        //中文字体库
        $fontface="simhei.ttf";
        //验证码 verifyName
        $verifyName=trim(strval($this->_get("name")));
        $verifyName=$verifyName?$verifyName:($verify_name?$verify_name:"verify");

        import('ORG.Util.Image');
        if($mode==4){
            Image::GBVerify($length,$type,$width,$height,$fontface,$verifyName);
        }else{
            Image::buildImageVerify($length,$mode,$type,$width,$height,$verifyName);
        }
    }
    //空操作 code/verify_name
    public function _empty($verify_name){
        $this->index($verify_name);
    }
}
