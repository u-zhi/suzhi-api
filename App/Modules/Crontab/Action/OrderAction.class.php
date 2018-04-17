<?php
// 订单相关
class OrderAction extends Action {

    //自动查询更新快递信息  半小时执行一次
    public function auto_deliver_info(){
        $order=new Tool_Order();
        $order->auto_deliver_info();
    }

}
