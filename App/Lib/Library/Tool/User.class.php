<?php
/*
 * 用户类(登录、退出等)
 */
class Tool_User{

    protected $_auto_login_expire=7;//自动登录过期天数
    public $_user_model=null;
    public $_jobhunter_user_model=null;//用户的其他信息
    public $_cv_job_model=null;//用户的简历-求职意向表

    public function __construct(){
        $this->_user_model=new UserModel();
        $this->_jobhunter_user_model=new JobhunterExtraInfoModel();
        $this->_cv_job_model=new CvJobIntentionModel();
    }

    //获取当前用户登录id
    public static function login_user_id(){
        return session("login_user_id")?decrypt_str(session("login_user_id")):0;
    }

    //获取当前登录用户名
    public static function login_name(){
        return session("login_name")?session("login_name"):'';
    }

    //记录登录信息
    public function login($user_row){
        if(is_numeric($user_row)){
            $user_row=$this->_user_model->field("id,user_name,name")->find($user_row);
        }
        session("login_user_id",encrypt_str($user_row["id"]));
        session("login_user_name",$user_row["user_name"]);
        session("login_name",$user_row["name"]);
    }

    //退出登录
    public function logout(){
        session(null);
        cookie("auto_login") && cookie("auto_login",null);
    }

    //设置自动登录
    public function auto_login($user_row){
        if(is_numeric($user_row)){
            $user_row=$this->_user_model->find($user_row);
        }
        $data=json_encode(array_merge($user_row,array(
            "auto_login_time"=>time()
        )));
        cookie("auto_login",encrypt_str($data));
    }

    //判断是否自动登录
    public function is_auto_login(){
        $user_data=cookie("auto_login")?json_decode(decrypt_str(cookie("auto_login")),true):false;
        if($user_data){
            $gone_day=round((time()-$user_data["auto_login_time"])/86400);
            if($gone_day>$this->_auto_login_expire){return false;}
            return $user_data;
        }
        return false;
    }

    //设置单一登录信息(后面登录踢出前面登录)
    public function single_login($user_id){
        $single_login_value=date("Y-m-d H:i:s")."_".rand(100,999);
        $single_login_key="single_login_key_".$user_id;
        session($single_login_key,$single_login_value);
        S($single_login_key,$single_login_value);
    }

    //是否登录被踢出
    public function is_push_logout($user_id){
        $single_login_key="single_login_key_".$user_id;
        if(!S($single_login_key)){return false;}
        if(session($single_login_key)==S($single_login_key)){return false;}
        return true;
    }

    //获取用户信息
    public function user_row($user_id){
        return $this->_user_model->find($user_id);
    }    
    //获取用户具体信息
    public function user_info_row($user_id){
        return $this->_jobhunter_user_model->where(array("user_id"=>$user_id))->find();
    }    

    //用户名是否已经存在
    public function user_name_exists($user_name){
        return $this->_user_model->where(array("user_name"=>$user_name))->count();
    }

    //修改用户信息
    public function update_user($user_id,array $data=array()){
        if(!$data){return false;}
        return $this->_user_model->where(array("id"=>$user_id))->save($data);
    }    
    //获取所有总数
    public function user_sum(){
        return $this->_user_model->where(array("is_seeker"=>2,"is_deleted"=>0))->count();
    }

}
