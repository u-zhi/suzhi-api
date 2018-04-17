<?php
// 夺宝活动
class IndianaAction extends Action {

    //活动自动开始
    public function auto_start_activity(){
        $indiana=new Indiana();
        $indiana->auto_start_activity();
    }

    //订单超时未付款自动取消
    public function auto_cancel_order(){
        $indiana=new Indiana();
        $indiana->auto_cancel_order();
    }

    //超时未夺宝完成的自动标记失败并等待退款给用户
    public function auto_fail_period(){
        $indiana=new Indiana();
        $indiana->auto_fail_period();
    }

    //已支付且参与夺宝失败的订单自动退款给用户(每5分钟执行一次)
    public function auto_withdraw(){
        $indiana=new Indiana();
        $indiana->auto_withdraw();
    }

    //自动开奖
    public function auto_period_lottery(){
        $indiana=new Indiana();
        $indiana->auto_period_lottery();
    }

}
