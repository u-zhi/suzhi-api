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
    // 新分页
    public function page2($data){
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
        return $this->renderFile("page2",$result);
    }
    //登录用户信息
    public function user_name(){
        return Company::login_user_name();
    }
    //登录用户名称
    public function name(){
        return Company::login_name();
    }
    // 企业未读通知数
    public function message_num(){
        $user = new User();
        $message_num=$user->_message_model->where(array("user_id"=>Company::login_user_id(),"user_type"=>1,"is_read"=>0))->count();
        return $message_num;
    }
    public function render($data){}
}