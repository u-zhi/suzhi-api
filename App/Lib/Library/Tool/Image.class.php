<?php
/*
 * 图片类
 */
class Tool_Image{

    public $_image_model=null;//图片表
    protected $_save_path="";//图片保存路径
    protected $_thumb_pass="";//缩略图加密串
    protected $_thumb_expire=5;//生成缩略图超时等待秒数
    protected $_image_thumb_adjust=false;//缩略图是否遵循原图比例
    //图片类型
    public static $type=array(
        1=>"夺宝图片",
    );
    public function __construct(){
        $this->_image_model=new LinkImageModel();
        $this->_save_path=C("image_path");
        $this->_thumb_pass=C("image_thumb_pass");
        $this->_thumb_expire=C("image_thumb_timeout");
        $this->_image_thumb_adjust=C("image_thumb_adjust");
    }
    public function __destruct(){
        $this->_image_model=null;
    }

    //添加图片
    public function add_image($type,$link_id,array $files){
        $data=array();
        foreach($files as $value){
            $data[]=array(
                "link_id"=>$link_id,
                "image_url"=>$value,
                "type"=>$type
            );
        }
        return $this->_image_model->addAll($data);
    }

    //编辑图片
    public function edit_image($type,$link_id,array $files){
        //删除不关联的图片,并排除已经关联的图片(不再添加)
        $delete_id=array();
        $has_exist=$this->_image_model->field("image_url,id")->where(array(
            "link_id"=>$link_id,
            "type"=>$type
        ))->select();
        foreach($has_exist as $value){
            if(in_array($value["image_url"],$files)){
                unset($files[array_search($value["image_url"],$files)]);
            }else{
                $delete_id[]=$value["id"];
            }
        }
        !empty($delete_id) && $this->_image_model->where(array("id"=>array("in",$delete_id)))->save(array("is_delete"=>2));
        //添加新增关联的图片
        if(!empty($files)){
            $data=array();
            foreach($files as $value){
                $data[]=array(
                    "link_id"=>$link_id,
                    "image_url"=>$value,
                    "type"=>$type
                );
            }
            $this->_image_model->addAll($data);
        }
    }

    //某类型id对应的图片
    public function image_row($type,$link_id){
        return $this->_image_model->where(array(
            "link_id"=>$link_id,
            "type"=>$type,
            "is_delete"=>1,
        ))->select();
    }

    //删除已经不存在关联的图片,减少空间图片的冗余
    public function clean_free(){
        $delete_row=$this->_image_model->where(array("is_delete"=>2))->limit(400)->select();
        $image_url=array();
        $delete_id=array();
        foreach($delete_row as $value){
            $delete_id[]=$value["id"];
            $image_url[]=$value["image_url"];
        }
        $image_url=array_unique($image_url);
        //删除关联
        !empty($delete_id) && $this->_image_model->where(array("id"=>array("in",$delete_id)))->delete();
        //删除图片和缩略图
        if(!empty($image_url)){
            $link_arr=array();
            $link_row=$this->_image_model->field("image_url")->where(array(
                "image_url"=>array("in",$image_url),
                "is_delete"=>1
            ))->select();
            if($link_row && !empty($link_row)){
                foreach($link_row as $value){
                    $link_arr[]=$value["image_url"];
                }
            }
            foreach($image_url as $value){
                if(in_array($value,$link_arr)){continue;}
                $this->_delete_image($value);
            }
        }
    }

    //删除图片和缩略图
    private function _delete_image($image_url){
        $file=str_replace("//","/",ROOT .$image_url);
        if(file_exists($file)){
            unlink($file);
            //删除所有相关缩略图
            $ext=pathinfo($file, PATHINFO_EXTENSION);
            $dir=str_replace(".".$ext,"_".$ext,$file);
            is_dir($dir) && $this->delFileUnderDir($dir);
        }
    }

    //删除目录中的所有文件
    private function delFileUnderDir($dirName){
        if ( $handle = opendir( "$dirName" ) ) {
            while ( false !== ( $item = readdir( $handle ) ) ) {
                if ( $item != "." && $item != ".." ) {
                    if ( is_dir( "$dirName/$item" ) ) {
                        $this->delFileUnderDir( "$dirName/$item" );
                    } else {
                        unlink( "$dirName/$item");
                    }
                }
            }
            closedir( $handle );
            rmdir($dirName);
        }
    }

    //生成缩略图
    public function create_thumb($request_uri){
        //解析图片地址并进行判断
        $thumb_image=$request_uri;
        $path_info=pathinfo($thumb_image);
        $ext=$path_info["extension"];
        $dirname=$path_info["dirname"];
        $basename=$path_info["basename"];
        $image_url=str_replace("_".$ext,"",$dirname).".".$ext;
        $buffer=explode("_",$basename);
        count($buffer) != 2 && exit;
        $buffer2=explode("x",$buffer[1]);
        count($buffer2) !=2 && exit;
        $buffer2[1]=str_replace(".".$ext,"",$buffer2[1]);
        $sign=substr(strtolower(md5($this->_thumb_pass.$image_url.$buffer2[0].$buffer2[1])),0,8);
        $buffer[0] != $sign && exit;
        $ori_image=str_replace("//","/",ROOT .$image_url);
        !file_exists($ori_image) && exit;
        $thumb_image=str_replace("//","/",ROOT .$thumb_image);
        //===========不存在缩略图就进行生成==========
        //高并发处理
        $is_wait=0;
        $wait_timeout=$this->_thumb_expire;
        $starttime=time();
        if(!F(md5($thumb_image))){
            F(md5($thumb_image),"processing_flag");
        }else{
            $is_wait = 1;
        }
        if($is_wait){ // 需要等待生成
            while(F(md5($thumb_image))){
                if(time()-$starttime>$wait_timeout){ // 超时
                    exit;
                }
                usleep(300); // sleep 300 ms
            }
            if(file_exists($thumb_image)){ // 图片生成成功
                ob_clean();
                header('content-type:'.mime_content_type($thumb_image));
                exit(file_get_contents($thumb_image));
            }else{
                exit(); // 生成失败退出
            }
        }
        // 创建缩略图
        import('ORG.Util.Image');
        $dirname=pathinfo($thumb_image,PATHINFO_DIRNAME);
        !is_dir($dirname) && mkdir($dirname,0777,TRUE);
        if($this->_image_thumb_adjust){
            Image::thumb($ori_image,$thumb_image,'',$buffer2[0],$buffer2[1],true);
        }else{
            Image::thumb2($ori_image,$thumb_image,'',$buffer2[0],$buffer2[1],true);
        }
        F(md5($thumb_image),null);// 删除处理中标记文件
        ob_clean();
        header('content-type:'.mime_content_type($thumb_image));
        exit(file_get_contents($thumb_image));
    }

    //图片上传
    public function upload($file){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();
        $upload->maxSize  = 2000000 ;
        $upload->allowExts  = array('gif','jpg','jpeg','bmp','png');
        $upload->savePath =  $this->_save_path;
        $upload->autoSub = TRUE;
        $upload->subType = "date";
        $info=$upload->uploadOne($file);
        if(!$info) {
            return false;
        }else{
            return "/".str_replace(ROOT,"",$this->_save_path).$info[0]["savename"];
        }
    }

    //根据原图和尺寸生成缩略图地址
    public function thumb_url($image_url,$width,$height){
        $ext=pathinfo($image_url, PATHINFO_EXTENSION);
        $dir=str_replace(".".$ext,"_".$ext,$image_url);
        $new_image=$dir."/".substr(strtolower(md5($this->_thumb_pass.$image_url.$width.$height)),0,8)."_".$width."x".$height.".".$ext;
        return $new_image;
    }

}
