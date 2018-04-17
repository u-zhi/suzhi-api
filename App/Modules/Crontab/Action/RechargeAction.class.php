<?php
// 充值订单操作
class RechargeAction extends Action {

    //45分钟未支付订单自动标记成取消  每分钟执行一次
    public function auto_cancel(){
        $end_time=time()+60*1;//执行的终止时间,默认执行一分钟
        $recharge=new Tool_Recharge();
        $time=date("Y-m-d H:i:s",time()-45*60);
        START_TAG:
        if(time()>=$end_time){ //超时自动中断
            exit("超时终止");
        }
        $recharge_row=$recharge->_recharge_model->where(array(
            "create_time"=>array("elt",$time),
            "status"=>1,
        ))->order("create_time desc")->find();
        if(!$recharge_row){exit("无数据终止");}
        $recharge->_recharge_model->where(array("id"=>$recharge_row["id"]))->save(array("status"=>4));
        goto START_TAG;
    }

}
