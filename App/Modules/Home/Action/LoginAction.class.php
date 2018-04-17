<?php
// 登录页
class LoginAction extends Action {
    //登录页面
    public function index(){
        layout(false);
        session("login_first","login_first");
        if($this->isPost()){
            //验证码
            $code=trim($this->_post("code"));
            $tel=trim($this->_post("tel"));
            if(!$tel)
            {
                $session_code=session("verify")?session("verify"):null;
                if(!$code || !$session_code){
                    $error = "请填写验证码";
                }
                if(md5($code)!=$session_code){
                    $error = "验证码错误";
                }
                //用户名密码
                $user_name=trim(strval($this->_post("user_name")));
                $password=trim(strval($this->_post("password")));
                if(!$user_name || !$password){
                    $error = "用户名或者密码错误";
                }
                $company=new Company();
                $user_row=$company->_user_model->where(array("user_name|phone_number"=>$user_name))->find();
                if(!$user_row){
                    $error = "用户名不存在,请先注册";
                }
                if($user_row["password"]!=encrypt_password($password)){
                    $error = "登录密码错误";
                }
                $company->login($user_row);
                $this->assign('user_name',$user_name);
                $this->assign('code',$code);
                $error_type = 'error_account';
            }else{
                $yzm=trim($this->_post("yzm"));
                $codeny=new Code();
                if(!$yzm){
                    $error = "请填写动态码";
                }
                if(!$codeny->check_code($tel,$yzm,5)){
                    $error = "动态码错误";
                }
                $company=new Company();
                $user_row=$company->_user_model->where(array("phone_number"=>$tel))->find();
                if(!$user_row){
                    $error = "手机号不存在,请先注册";
                }
                $company->login($user_row);
                $this->assign('tel',$tel);
                $error_type = 'error_phone';
            }
            if($error){
                $this->assign($error_type,$error);
                $this->display();
                exit;
            } else {
                $this->redirect("index/index");
            }
        }
        $this->display();
    }
    //注册
    public function register(){
        layout(false);
        if($this->isPost()){
            if(!session("register_first")){
                $this->error("系统出错");
            }
            $user_name=trim(strval($this->_post("user_name")));
            $password=trim(strval($this->_post("password")));
            $mobile=trim(strval($this->_post("mobile")));
            $verify_code=trim(strval($this->_post("code")));
            if(!$user_name || !$password || !$mobile || !$verify_code){
                $this->error("信息填写不完整");
            }
            $company=new Company();
            if($company->mobile_register($mobile) || $company->user_name_exists($user_name) || $company->mobile_register($user_name)){
                $this->error("账号或手机号码已经被注册");
            }
            //验证短信验证码
            $code=new Code();
            if(!$code->check_code($mobile,$verify_code,2)){
                $this->error("手机验证码错误");
            }
            //生成注册信息
            $data=session("register_first_data");
            $data["user_name"]=$user_name;
            $data["password"]=encrypt_password($password);
            $data["phone_number"]=$mobile;
            $data["create_time"]=date("Y-m-d H:i:s");
            $data["telephone"]=$data["telephone_code"]."-".$data["telephone"];
            $data["type_classify"]=$data["type_classify_main"].",".$data["type_classify_sub"];
            $data["address_pro_city_coun"]="#".$data["province_id"]."#".$data["city_id"]."#".$data["country_id"]."#";
            $user_id=$company->add_row($data);
            //自动登录,并前往选购产品页面
            $data["id"]=$user_id;
            $company->login($data);
            //清除session
            session("register_first",null);
            session("register_first_data",null);
            $this->redirect("index/buy_server");
        }else{
            $this->assign("second_step",session("register_first")?1:0);//是否第二步
            $this->assign("register_first_data",session("register_first_data")?session("register_first_data"):array());
            $company=new Company();
            $this->assign("type_classify",$company->type_classify());//公司行业
            $this->assign("financing",Company::financing());//融资情况
            $this->assign("scale_type",Company::scale_type());//人数规模
            $this->assign("mobile",session("?register_first_data")?session("register_first_data")["phone_number"]:'');
            $this->display();
        }
    }
    //上一步(重新注册)
    public function previous_step(){
        session("register_first",null);
        //session("register_first_data",null);
        $this->redirect('login/register');
    }
    //发送注册短信
    public function send_sms(){
        if($this->isAjax()){
//            if(!session("register_first") && !session("forget_password") && !$company_id=Company::login_user_id() && !session("login_first")){
//                echo json_encode(array("status"=>"fail"));exit;
//            }
            $type=intval($this->_post("type"));
            $type=$type?$type:2;
            $mobile=trim(strval($this->_post("mobile")));
            //手机号码是否被注册
            if($type==2 || $type==4){
                $company=new Company();
                if($company->mobile_register($mobile)){
                    echo json_encode(array("status"=>"has_register"));exit;
                }
            }
            //调用接口进行发送
            $code=new Code();
            $rand_code=$code->company_register_send($mobile,$type);
            echo json_encode(array(
                "status"=>"success",
                //"code"=>$rand_code
            ));exit;
        }
    }
    //验证注册第一步
    public function first_step(){
        if($this->isAjax()){
            $data=$this->_post();
            session("register_first",1);
            session("register_first_data",$data);
        }
    }
    //验证手机号码是否被注册
    public function check_mobile(){
        if($this->isAjax()){
            $mobile=trim(strval($this->_post("mobile")));
            $company=new Company();
            if($company->mobile_register($mobile)){
                echo json_encode(array("status"=>"has_register"));
            }else{
                echo json_encode(array("status"=>"success"));
            }
        }
    }
    //验证账号是否已被注册
    public function check_account(){
        if($this->isAjax()){
            $account=trim(strval($this->_post("account")));
            $company=new Company();
            if($company->account_register($account)){
                echo json_encode(array("status"=>"has_register"));
            }else{
                echo json_encode(array("status"=>"success"));
            }
        }
    }
    //退出登录
    public function logout(){
        $company=new Company();
        $company->logout();
        $this->redirect("login/index");
    }
    //忘记密码
    public function forget(){
        layout(false);
        session("forget_password","forget_password");
        if($this->isPost()){
            $mobile=trim(strval($this->_post("mobile")));
            $verify_code=trim(strval($this->_post("code")));
            $password=trim(strval($this->_post("password")));
            if(!$mobile || !$verify_code || !$password){
                $this->error("信息填写不完整");
            }
            $company=new Company();
            if(!$company->mobile_register($mobile)){
                $this->error("该手机号未注册,请先注册");
            }
            //验证短信验证码
            $code=new Code();
            if(!$code->check_code($mobile,$verify_code,5)){
                $this->error("手机验证码错误");
            }

            $company=new Company();
            $company->_user_model->where(array(
                "phone_number"=>$mobile
            ))->save(array("password"=>encrypt_password($password)));
            $this->success("修改成功",U("login/index"));exit;
        }
        $this->display();
    }
}
