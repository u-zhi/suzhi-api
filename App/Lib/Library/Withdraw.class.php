<?php
/*
 * 提现类
 */
class Withdraw{
    public $_order_model=null;//提现订单
    protected $_account_model=null;//提现账号
    //用户类型
    public static $_user_type=array(
        1=>"企业",
        2=>"求职者|猎头",
    );
    //状态
    public static $_status=array(
        0=>"未反馈",
        1=>"已批准",
        2=>"拒绝",
    );    
    //提现方式
    public static $_issuer=array(
        0=>"支付宝",
        1=>"微信支付",
        2=>"银行卡",
    );    
    //支出收入状态
    public static $_direction=array(
        0=>"收入",
        1=>"支出",
    );
    public function __construct(){
        $this->_order_model=new WithdrawModel();
        $this->_account_model=new WithdrawProfileModel();
    }
    //企业提交提现申请
    public function promote($user_row,$money,array $account,$user_type=1){
        $money=floatval($money);
        if($money<=0){return false;}
        //企业账号
        if($user_type==1){
            $company=new Company();
            if(is_numeric($user_row)){
                $user_row=$company->user_row($user_row);
            }
            if($user_row["money"]<$money){
                return false;
            }
            //减少金额
            $company->plus_money($user_row["id"],$money);
            //增加冻结金额
            $company->add_frozen($user_row["id"],$money);
            //记录提现记录
            $profile_id=$this->_account_model->add(array(
                "user_id"=>$user_row["id"],
                "user_type"=>1,
                "issuer"=>$account["type"],
                "account"=>$account["account"],
                "name"=>$account["name"],
                "extra_info"=>$account["extra_info"],
                "comment"=>$account["comment"],
                "create_time"=>date("Y-m-d H:i:s"),
            ));
            $this->_order_model->add(array(
                "method_id"=>$profile_id,
                "amount"=>$money,
                "create_time"=>date("Y-m-d H:i:s"),
                "user_id"=>$user_row["id"],
                "user_type"=>1,
            ));
            return true;
        }
        return false;
    }
    // 提现申请个人信息
    public function account($user_id){
        return $this->_account_model->find(array("user_id"=>$user_id,"user_type"=>2));
    }
    // 个人提现方式提现方式是一条条加的
    public function user_withdraw_profile($user_id,$data){
        return $this->_account_model->add(array(
                "user_id"=>$data['user_id'],
                "account"=>$data['account'],
                "issuer"=>$data['issuer'],
                "name"=>$data['name'],
                "extra_info"=>$data['extra_info'],
                "comment"=>$data['comment'],
                "user_type"=>$data['user_type'],
                "create_time"=>date("Y-m-d H:i:s"),
            )); 
    }
    // 个人提现申请
    public function user_balance($user_id,$method_id,$amount){
        return $this->_order_model->add(array(
            "user_id"=>$user_id,
            "method_id"=>$method_id,
            "amount"=>$amount,
            "user_type"=>2,
            "create_time"=>date("Y-m-d H:i:s"),
            ));        

    }

}
