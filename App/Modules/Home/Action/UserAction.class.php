<?php
// 公司
class UserAction extends Action {

    //整体概况
    public function index(){

        $this->display();
    }
    //企业资料
    public function info(){
        $company=new Company();
        $company_row=$company->user_row(Company::login_user_id());
        $this->assign("company_row",$company_row);

        $this->assign("type_classify",$company->type_classify());//公司行业
        $this->assign("financing",Company::financing());//融资情况
        $this->assign("scale_type",Company::scale_type());//人数规模
        $this->display();
    }
    //编辑企业资料
    public function edit_info(){
        if($this->isPost()){
            $data=$this->_post();
            $data["telephone"]=$data["telephone_code"]."-".$data["telephone"];
            $data["type_classify"]=$data["type_classify_main"].",".$data["type_classify_sub"];
            $data["address_pro_city_coun"]="#".$data["province_id"]."#".$data["city_id"]."#".$data["country_id"]."#";
            $company=new Company();
            $company->update_user(Company::login_user_id(),$data);
            $this->success("修改成功",U('user/info'));exit;
        }else{
            $company=new Company();
            $company_row=$company->user_row(Company::login_user_id());
            $this->assign("company_row",$company_row);

            $this->assign("type_classify",$company->type_classify());//公司行业
            $this->assign("financing",Company::financing());//融资情况
            $this->assign("scale_type",Company::scale_type());//人数规模
            $this->display();
        }
    }
    //账号安全
    public function safe(){
        $company=new Company();
        $company_row=$company->user_row(Company::login_user_id());
        $this->assign("company_row",$company_row);

        $this->display();
    }
    //账户充值
    public function recharge(){
        if($this->isPost()){
            $money=floatval($this->_post("money"));
            if(!$money){
                $this->error("充值金额错误",U('user/safe'));
            }
            $recharge=new Recharge();
            $recharge_id=$recharge->add(Company::login_user_id(),$money*100);
            if($recharge_id>0){
                $recharge_row=$recharge->get_row($recharge_id);
                //跳转支付宝进行支付
                $alipay=new Tool_Alipay();
                $alipay->pay($recharge_row["order_number"],"账号充值",$money);
            }
        }
    }
    //支付宝充值回调
    public function recharge_back(){
        $this->success("充值即时可到账,请稍后查看余额",U("user/safe"));exit;
    }
    //账号提现
    public function withdraw(){
        if($this->isPost()){
            $money=floatval($this->_post("money"));
            if(!$money){
                $this->error("充值金额错误",U('user/safe'));
            }
            //验证码
            $company=new Company();
            $company_row=$company->user_row(Company::login_user_id());
            $verify_code=trim(strval($this->_post("code")));
            $code=new Code();
            if(!$code->check_code($company_row["phone_number"],$verify_code,3)){
                $this->error("手机验证码错误",U('user/safe'));
            }
            $account=array(
                "type"=>intval($this->_post("type")),
                "account"=>trim(strval($this->_post("account"))),
                "name"=>trim(strval($this->_post("name"))),
                "extra_info"=>trim(strval($this->_post("extra_info"))),
                "comment"=>'',
            );
            $withdraw=new Withdraw();
            if($withdraw->promote($company_row,$money*100,$account)){
                $this->success("提现申请成功",U('user/safe'));
            }else{
                $this->error("提现申请失败",U('user/safe'));
            }
        }
    }
    //修改登录密码
    public function password(){
        if($this->isPost()){
            $password=trim(strval($this->_post("password")));
            $new_password=trim(strval($this->_post("new_password")));
            $sure_password=trim(strval($this->_post("sure_password")));
            if(!$password || !$new_password || !$sure_password){
                $this->error("修改密码失败",U('user/safe'));
            }
            if($sure_password!=$new_password){
                $this->error("修改密码失败",U('user/safe'));
            }
            $company=new Company();
            $company_row=$company->user_row(Company::login_user_id());
            if($company_row["password"]!=encrypt_password($password)){
                $this->error("旧密码错误",U('user/safe'));
            }
            $company->update_user(Company::login_user_id(),array("password"=>encrypt_password($new_password)));
            $this->success("密码修改成功",U('user/safe'));
        }
    }
    //修改手机号码
    public function mobile(){
        //手机号码
        $mobile=trim(strval($this->_post("mobile")));
        $company=new Company();
        if(!$mobile || $company->mobile_register($mobile)){
            $this->error("手机已经被注册",U('user/safe'));
        }
        //验证码
        $verify_code=trim(strval($this->_post("code")));
        $code=new Code();
        if(!$code->check_code($mobile,$verify_code,4)){
            $this->error("手机验证码错误",U('user/safe'));
        }
        //密码校验
        $company_row=$company->user_row(Company::login_user_id());
        $password=trim(strval($this->_post("password")));
        if(!$password || $company_row["password"]!=encrypt_password($password)){
            $this->error("密码错误",U('user/safe'));
        }

        $company->update_user(Company::login_user_id(),array("phone_number"=>$mobile));
        $this->success("手机号码修改成功",U('user/safe'));
    }
    //获取登录用户信息
    public function get_info(){
        $company=new Company();
        $company_row=$company->user_row(Company::login_user_id());
        $company_row["money"]=$company_row["money"]/100;
        $company_row["forzen_money"]=$company_row["forzen_money"]/100;
        unset($company_row["password"]);
        echo json_encode($company_row);
    }
}
