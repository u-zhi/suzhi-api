<?php
//城市信息
class CityAction extends BaseAction {
    //开通城市列表
    public function open_city_list(){
        $city=new Tool_Region();
        $city_lists=$city->open_city();
        $this->success(array("content"=>$city_lists));
    }
    //获取所有省份
    public function province(){
        $city=new Tool_Region();
        $province=$city->province();
        $this->success(array("content"=>$province));

    }
    //获取省下面的市
    public function city(){
        $city=new Tool_Region();
        $city=$city->city();
        $this->success(array("content"=>$city));
    }    
    //获取市下面的县
    public function country(){
        $country=new Tool_Region();
        $country=$city->country();
        $this->success(array("content"=>$country));
    }
    
}
