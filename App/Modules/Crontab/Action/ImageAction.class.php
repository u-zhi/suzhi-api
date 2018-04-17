<?php
// 图片
class ImageAction extends Action {

    //删除未关联的图片
    public function clean(){
        $image=new Tool_Image();
        $image->clean_free();
    }

}
