<?php
//订单类--包括订单流程类
class OrderAction extends BaseAction {
    //拒绝面试
    public function refuse_interview(){
        $task_id=$this->_post("task_id");
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        if($order->refuse_interview($task_id,$user_id)){
            $this->success(array("content"=>true));
        }else{
            $this->error("非法操作");
        }
    }
    //接受面试
    public function accept_interview(){
        $task_id=$this->_post("task_id");
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        if($order->accept_interview($task_id,$user_id)){
            $this->success(array("content"=>true));
        }else{
            $this->error("非法操作");
        }
    }
    //取消面试
    public function cancel_interview(){
        $task_id=$this->_post("task_id");
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        if($order->cancel_interview($task_id,$user_id)){
            $this->success(array("content"=>true));
        }else{
            $this->error("非法操作");
        }
    }
    //拒绝就职
    public function refuse_position(){
        $task_id=$this->_post("task_id");
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        if($order->refuse_position($task_id,$user_id)){
            $this->success(array("content"=>true));
        }else{
            $this->error("非法操作");
        }
    }
    //确认就职
    public function accept_position(){
        $task_id=$this->_post("task_id");
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        if($order->accept_position($task_id,$user_id)){
            $this->success(array("content"=>true));
        }else{
            $this->error("非法操作");
        }
    }
}
