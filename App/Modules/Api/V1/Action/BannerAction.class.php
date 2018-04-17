<?php
//banner信息
class BannerAction extends BaseAction {
    // banner_list
    public function banner_list(){

        $user=new User();
        $banner_list=$user->_banner_model->where(array("is_deleted"=>0))->order("sort asc")->select();
        foreach ($banner_list as $key => $value) {
            $banner_list[$key]['banner_url']=thumb_image($value['img_url'],750,300);
        }
        $this->success($banner_list);
    }
    
}
