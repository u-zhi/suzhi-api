<?php
// 图片处理相关
class ImageAction extends Action {

    //图片上传
    public function upload(){
        if(!session("domain_file_upload")){
            $encrypt_key=trim($this->_get("encrypt_key"));
            if(encrypt_password("ranmiaozai_mutouren")!=$encrypt_key){
                exit("fail");
            }
        }
        $file_key=trim(strval($this->_get("file_key")));
        if(!$file_key){$file_key="image";}
        if($this->isPost()){
            $file=$_FILES[$file_key];
            empty($file) && exit("fail");
            $image=new Tool_Image();
            if($this->_get("thumb")){
                $image_url=$image->upload($file);
                echo json_encode(array(
                    "image"=>$image_url,
                    "thumb"=>thumb_image($image_url,$this->_get("width"),$this->_get("height")),
                ));
            }else{
                echo $image->upload($file);
            }
        }
	}

    //生成缩略图
    public function create_thumb(){
        $image=new Tool_Image();
        $image->create_thumb($_SERVER["REQUEST_URI"]);
    }

}
