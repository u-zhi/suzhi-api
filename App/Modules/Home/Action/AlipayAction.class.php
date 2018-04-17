<?php
// 支付宝异步通知
class AlipayAction extends Action {

    //通知接口
    public function notify(){
        file_put_contents(RUNTIME_PATH."log.txt",json_encode($this->_post()));
        if($this->isPost()){
            $alipay=new Tool_Alipay();
            $notify_row=$alipay->notify_check();
            file_put_contents(RUNTIME_PATH."log.txt",json_encode($this->_post()));
            if($notify_row && isset($notify_row["order_number"])){
                $recharge=new Recharge();
                $recharge_row=$recharge->get_row_by_number($notify_row["order_number"]);
                if($recharge_row && $recharge_row["status"]==1 && $recharge_row["pay_type"]==1 || $recharge_row["money"]==$notify_row["money"]*100){
                    $recharge->success($notify_row["order_number"]);
                }
            }
            echo "success";
        }
    }

}
