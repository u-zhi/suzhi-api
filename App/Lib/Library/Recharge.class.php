<?php
/*
 * 充值记录
 */
class Recharge{
    protected $_recharge_model=null;
    protected $_balance_model=null;//求职者和猎头余额表
    //用户类型
    public static $_user_type=array(
        1=>"企业",
        2=>"求职者|猎头",
    );
    //状态
    public static $_status=array(
        1=>"新建",
        2=>"充值成功",
        3=>"充值失败",
    );
    //充值类型
    public static $_pay_type=array(
        1=>"支付宝",
        2=>"erp充值",
    );
    public function __construct(){
        $this->_recharge_model=new RechargeModel();
        $this->_balance_model=new UserBalanceModel();//求职者和猎头余额表
    }
    //增加充值记录
    public function add($user_id,$money,$user_type=1,$pay_type=1){
        return $this->_recharge_model->add(array(
            "user_id"=>$user_id,
            "money"=>$money,
            "user_type"=>$user_type,
            "pay_type"=>$pay_type,
            "create_time"=>date("Y-m-d H:i:s"),
            "order_number"=>"R".time().rand(100,999),
        ));
    }
    //获取充值信息
    public function get_row($recharge_id){
        return $this->_recharge_model->find($recharge_id);
    }
    //获取充值信息
    public function get_row_by_number($order_number){
        return $this->_recharge_model->where(array(
            "order_number"=>$order_number
        ))->find();
    }
    //充值成功
    public function success($order_number){
        $order_row=$this->_recharge_model->where(array(
            "order_number"=>$order_number,
            "status"=>1,
        ))->find();
        if(!$order_row){return false;}
        $this->_recharge_model->where(array("id"=>$order_row["id"]))->save(array(
            "notice_time"=>date("Y-m-d H:i:s"),
            "status"=>2,
        ));
        //给用户增加金额
        static $company=null;
        if($order_row["user_type"]==1){
            if($company==null){
                $company=new Company();
            }
            $company->add_money($order_row["user_id"],$order_row["money"]);
        }
    }
    // 账户余额所有
    public function balance_row($user_id){
        $money_row=$this->_balance_model->where(array("user_id"=>$user_id))->limit(1)->find();
        if(!$money_row){
            $insert_id=$this->_balance_model->add(array(
                "user_id"=>$user_id,
                "balance"=>0,
                "create_time"=>date("Y-m-d H:i:s"),
                "update_time"=>date("Y-m-d H:i:s"),
            ));
            $money_row=$this->_balance_model->where(array("id"=>$insert_id))->find();
        }
        return $money_row;
    }
    // 账户余额
    public function amount($user_id){
        $amount=$this->_balance_model->field("amount")->where(array("user_id"=>$user_id))->limit(1)->find();
        if($amount){
            $money=$amount['amount'];
        }else{
            $money=0;
        }
        return $money;
    }
}
