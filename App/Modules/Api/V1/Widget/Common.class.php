<?php
/*
 * 视图助手通用类
 */
class CommonHelper extends Widget{
    //当前页面地址
    public function current_url(){
        $link=strtolower(MODULE_NAME)."/".strtolower(ACTION_NAME);
        $param=I('get.');
        $param_result=array();
        foreach($param as $key=>$value){
            if(!in_array($key,array("_URL_"))){
                $param_result[$key]=$value;
            }
        }
        $param_str=$param_result?'?'.http_build_query($param_result):'';
        return U($link).$param_str;
    }
    //分页
    public function page($data){
        layout(false);
        $link=strtolower(MODULE_NAME)."/".strtolower(ACTION_NAME);
        $param=I('get.');
        $param_result=array();
        foreach($param as $key=>$value){
            if(!in_array($key,array("page","_URL_"))){
                $param_result[$key]=$value;
            }
        }
        $buf=empty($param_result)?"page=":"&page=";
        $result=array(
            "link"=>U($link).'?'.http_build_query($param_result).$buf,
            "pageinfo"=>$data,
        );
        return $this->renderFile("page",$result);
    }

    public function render($data){}
}