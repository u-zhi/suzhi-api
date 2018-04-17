<?php
/*
 * 用户登录判断
*/
class UserLoginBehavior extends Behavior {
    /**
     * 钩子对外统一接口
     */
    public function run(&$params){
        $module=strtolower(MODULE_NAME);
        $action=strtolower(ACTION_NAME);
        //白名单设置
        if(in_array($module,array("login","image","code","reback"))){
            return;
        }
        //是否登录
        if(!$user_id=Company::login_user_id()){
            redirect(U("login/index"));
        }
    }

}