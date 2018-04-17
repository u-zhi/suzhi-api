<?php
/*
 * 求职顾问订单
 */
class HeadHunterOrder{
    protected $_headhunter_order_model=null;//猎头订单
    public function __construct(){
        $this->_headhunter_order_model=new HeadhunterOrderModel();
    }
    public function __get($name){
        if(substr($name,-6)=="_model"){
            return $this->$name;
        }
        return false;
    }

    //获取顾问是否有此订单，有返回错误，无加入此条订单
    public function head_receive($user_id,$task_id){
        $order_list=$this->_headhunter_order_model->where(array("user_id"=>$user_id,"task_id"=>$task_id,"is_cancled"=>1))->find();
        if($order_list){
            return false;
        }else{
            // 若原来领取的任务呗被取消则激活
            $order_list_have=$this->_headhunter_order_model->where(array("user_id"=>$user_id,"task_id"=>$task_id,"is_cancled"=>0))->find();
            if($order_list_have){
                $data["is_cancled"]=1;
                $data["update_time"]=date("Y-m-d H:i:s");
                return $this->_headhunter_order_model->where(array("id"=>$order_list_have['id']))->save($data);
            }
            // 若任务重来未被领取则新增一条任务
            $data=array(
                "user_id"=>$user_id,
                "task_id"=>$task_id,
                "is_cancled"=>1,
                "qrcode_url"=>0,//目前还未写后面补上
                "create_time"=>date("Y-m-d H:i:s"),
                );
            return $this->_headhunter_order_model->add($data);
        }
    }
     //取消顾问领取的订单
    public function head_cancle($user_id,$task_id){
        $order_list=$this->_headhunter_order_model->where(array("user_id"=>$user_id,"task_id"=>$task_id,"is_cancled"=>1))->find();
        if($order_list){
            $data['is_cancled']=0;
            $data['cancel_time']=date("Y-m-d H:i:s");
            return $this->_headhunter_order_model->where(array("id"=>$order_list['id']))->save($data);
        }else{
            return false;
        }
    }
    //领取任务订单记录
    public function get_task_row($user_id,$task_id){
        $order_list=$this->_headhunter_order_model->where(array("user_id"=>$user_id,"task_id"=>$task_id))->find();
        return $order_list;
    }

}
