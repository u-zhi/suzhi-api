<?php
/*
 * 省市县地区
 */
class Tool_Region{

    protected $_db=null;//数据库对象

    public function __construct(){
        $this->_db=new RegionModel();//城市总表
        $this->_open_city_db=new OpenCityModel();//开通城市
    }

    public function __destruct(){
        $this->_db=null;
    }
    // 获取所有开通城市
    public function open_city(){
        $city_list=$this->_open_city_db
                        ->order("id desc")
                        ->select();
        return $city_list;
    }
    //获取所有省份
    public function province(){
        $province=$this->_db
                    ->field("region_name,region_id")
                    ->where(array("parent_id"=>1))
                    ->order("region_id")
                    ->select();
        return $province;
    }

    //获取省下面的市
    public function city($province_id){
        $city=$this->_db->field("region_name,region_id")
                    ->order("region_id")
                    ->where(array("parent_id"=>$province_id))
                    ->select();
        return $city;
    }

    //获取省下面的市id
    public function city_id($province_id){
        $row=$this->city($province_id);
        $arr=array();
        foreach($row as $value){
            $arr[]=$value["region_id"];
        }
        return $arr;
    }

    //获取市下面的县
    public function country($city_id){
        $city=$this->_db
            ->field("region_name,region_id")
            ->order("region_id")
            ->where(array("parent_id"=>$city_id))
            ->select();
        return $city;
    }

    //根据省市县id获取名称
    public function region($province_id,$city_id,$country_id){
        $row=$this->_db->field("region_name")->order("region_id")
                        ->where(array("region_id"=>array("in",array(
                            $province_id,$city_id,$country_id
                        ))))->select();
        $buffer=array();
        foreach($row as $value){
            $buffer[]=$value["region_name"];
        }
        return implode("",$buffer);
    }
    // 获取城市名称
    public function country_name($city_id){
        $row=$this->_db->field("region_name")->where(array("region_id"=>$city_id))->find();
        return $row['region_name'];
    }

    // 根据城市定位获取city_id
    public function get_city_id($city_name) {
        $row = $this->_db->field('region_id')->where(array('region_name'=>$city_name))->find();
        return intval($row['region_id']);
    }

}
