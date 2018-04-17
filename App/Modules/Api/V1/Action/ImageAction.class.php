<?php
// 图片处理相关
class ImageAction extends BaseAction {

    //图片上传
    public function upload(){
        $file_key=trim(strval($this->_get("file_key")));
        if(!$file_key){$file_key="image";}
        $file=$_FILES[$file_key];
        empty($file) && exit("fail");
        if(empty($file)){
            $this->error("上传失败");
        }
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
	//获取图片base64地址
    public function base64_url(){
        $url=trim(strval($this->_get("url")));
        if(!$url){
            $this->error("获取失败");
        }
        $file=rtrim(C("image_path"),"/uploads/").$url;
        if(!file_exists($file)){
            $this->error("获取失败");
        }
        $base64_url=base64_image($file);
        $this->success(array("url"=>$base64_url));
    }

    //生成缩略图
    public function create_thumb(){
        $image=new Tool_Image();
        $image->create_thumb($_SERVER["REQUEST_URI"]);
    }

}
