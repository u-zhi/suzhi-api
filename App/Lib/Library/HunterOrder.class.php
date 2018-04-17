<?php
/*
 * 求职者订单
 */
class HunterOrder{
    protected $_order_model=null;//求职订单
    protected $_task_model=null;//职位表
    protected $_interview_model=null;//求职者面试表
    protected $_work_model=null;//求职者offer表
    protected $_headorder_model=null;//顾问订单
    //求职订单状态
    public static $_order_status=array(
        1=>"投递待处理",
        2=>"邀请面试",
        3=>"不合适",
        4=>"拒绝面试",
        5=>"待面试",
        6=>"已经面试待反馈",
        7=>"未参加面试",
        8=>"面试不合适",
        9=>"发offer",
        10=>"确认就职",
        11=>"取消面试",
        12=>"拒绝就职",
        13=>"确认入职",
        14=>"未入职",
    );
    // 求职订单状态的进行中--已经结束
    public static $_order_progress_type=array(
        1=>array(1,2,5,6,9,10),
        2=>array(3,4,7,8,11,12,13,14),
        3=>array(99),
        );
    //投递职位来源类型
    public static $_post_type=array(
        1=>"内推",
        2=>"全民猎头悬赏",
        3=>"自主投递",
        4=>"招聘会",
        5=>"企业邀面",
    );
    public function __construct(){
        $this->_order_model=new JobhunterOrderModel();
        $this->_task_model=new TaskModel();
        $this->_interview_model=new JobhunterInterviewModel();
        $this->_work_model=new JobhunterWorkModel();
        $this->_headorder_model=new HeadhunterOrderModel();
    }
    public function __get($name){
        if(substr($name,-6)=="_model"){
            return $this->$name;
        }
        return false;
    }
    //投递职位
    public function post_task($user_id,$task_row,$add_type,$ext=array()){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        if($task_row["status"]!=2){return false;}
        $data=array(
            "user_id"=>$user_id,
            "task_id"=>$task_row["id"],
            "trade_no"=>md5("P".time().rand(0,999)),
            "current_status"=>1,
            "company_id"=>$task_row['firm_id'],
            "create_time"=>date("Y-m-d H:i:s"),
            "add_type"=>$add_type,
            "headhunter_task_id"=>isset($ext["headhunter_task_id"])?$ext["headhunter_task_id"]:0,
            "inner_user_id"=>isset($ext["inner_user_id"])?$ext["inner_user_id"]:0,
            "header_id"=>isset($ext["header_id"])?$ext["header_id"]:0,
            "hunter_tip_status"=>1,
        );
        return $this->_order_model->add($data);
    }
    //获取订单信息
    public function get_row($order_id,$company_id=0){
        $order_row=$this->_order_model->find($order_id);
        if(!$order_row){return false;}
        if($company_id && $order_row["company_id"]!=$company_id){
            return false;
        }
        return $order_row;
    }
    //标记成不合适
    public function deny_interview($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=1){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>3,
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ));
        return true;
    }
    //邀请面试
    public function pass_interview($order_row,$interview_time){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=1){
            return false;
        }
        $task_row=$this->_task_model->find($order_row["task_id"]);
        //支付佣金
        if($task_row["commission_type"]==2 && $task_row["recruit_need"]==1 && in_array($order_row["add_type"],array(1,2))){   //支付押金且简历需求
            if(!$this->pay_commission($order_row,$task_row)){
                return false;
            }
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>2,
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ));
        //记录面试记录
        return $this->_interview_model->add(array(
            "jobhunter_order_id"=>$order_row["id"],
            "status"=>2,
            "contact"=>$task_row["link_name"]?$task_row["link_name"]:'',
            "contact_tel"=>$task_row["mobile"]?$task_row["mobile"]:'',
            "interview_time"=>$interview_time,
            "interview_address"=>$task_row["interview_address"]?json_decode($task_row["interview_address"],true)["address"]:"",
            "create_time"=>date("Y-m-d H:i:s"),
            "company_id"=>$order_row["company_id"],
        ));
    }
    //确认已面试
    public function has_interview($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=5){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>6,
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ));
        //同时修改面试记录
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>6));
        //记录消息
        $message=new Message();
        $message->has_interview($order_row);
        return true;
    }
    //确认没来面试
    public function no_interview($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=5){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>7,
        ));
        //同时修改面试记录
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ))->save(array("status"=>7));
        return true;
    }
    //面试结果通知
    public function notice_interview($order_row,array $result_data){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=6){
            return false;
        }
        //不合适
        if($result_data["result_type"]==1){
            $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
                "current_status"=>8,
                "hunter_tip_status"=>1,
                "seeker_tip_status"=>1,
            ));
            //同时修改面试记录
            $this->_interview_model->where(array(
                "jobhunter_order_id"=>$order_row["id"],
            ))->save(array("status"=>8,"result_reason"=>$result_data["content"]));
            //记录消息
            $message=new Message();
            $message->nopass_interview($order_row);
        }else{ //发offer
            $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
                "current_status"=>9,
                "hunter_tip_status"=>1,
                "seeker_tip_status"=>1,
            ));
            //同时修改面试记录
            $this->_interview_model->where(array(
                "jobhunter_order_id"=>$order_row["id"],
            ))->save(array("status"=>9));
            //生成开工记录
            $this->_work_model->add(array(
                "jobhunter_order_id"=>$order_row["id"],
                "status"=>9,
                "contact"=>$result_data["contact"],
                "contact_tel"=>$result_data["contact_tel"],
                "work_time"=>$result_data["work_time"],
                "salary"=>$result_data["salary"],
                "create_time"=>date("Y-m-d H:i:s"),
                "work_address"=>"",
            ));
            //记录消息
            $message=new Message();
            $message->offer_interview($order_row);
        }
        return true;
    }
    //由面试信息获取订单信息
    public function interview_to_order(&$content){
        $order_id=array();
        foreach($content as $value){
            $order_id[]=$value["jobhunter_order_id"];
        }
        if($order_id){
            $order_arr=array();
            $order_row=$this->_order_model->where(array("id"=>array("in",$order_id)))->select();
            foreach($order_row as $value){
                $order_arr[$value["id"]]=$value;
            }
            foreach($content as $key=>$value){
                $content[$key]["interview_id"]=$value["id"];
                $content[$key]["task_id"]=$order_arr[$value["jobhunter_order_id"]]["task_id"];
                $content[$key]["trade_no"]=$order_arr[$value["jobhunter_order_id"]]["trade_no"];
                $content[$key]["headhunter_task_id"]=$order_arr[$value["jobhunter_order_id"]]["headhunter_task_id"];
                $content[$key]["add_type"]=$order_arr[$value["jobhunter_order_id"]]["add_type"];
                $content[$key]["company_id"]=$order_arr[$value["jobhunter_order_id"]]["company_id"];
                $content[$key]["id"]=$order_arr[$value["jobhunter_order_id"]]["id"];
                $content[$key]["user_id"]=$order_arr[$value["jobhunter_order_id"]]["user_id"];
            }
        }
    }

    // 求职者被邀请面试反馈
    public function jobhunter_status($status,$user_id){
        return $this->_interview_model->where(array("jobhunter_order_id"=>$user_id))->save(array(
            "status"=>$status,
            ));
    }

    //求职者是否参与某职位
    public function has_join_task($user_id,$task_id){
        return $this->_order_model->where(array(
            "user_id"=>$user_id,
            "task_id"=>$task_id,
        ))->find();
    }
    //猎头是否领取某职位 0 未领取 1 已领取
    public function has_join_hunter_task($user_id,$task_id){
        return $this->_headorder_model->where(array(
            "user_id"=>$user_id,
            "task_id"=>$task_id,
            "is_cancled"=>1,
        ))->find();
    }

    //订单面试信息
    public function interview_row($order_id){
        return $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_id,
        ))->find();
    }

    //订单开工信息
    public function work_row($order_id){
        return $this->_work_model->where(array(
            "jobhunter_order_id"=>$order_id,
        ))->find();
    }

    //支付佣金
    public function pay_commission($order_row,$task_row=false){
        if(!$task_row){
            $task_row=$this->_task_model->find($order_row["task_id"]);
        }
        $money=$task_row["commission"];
        $company=new Company();
        //佣金足够支付
        $task=new Task();
        if($task->commission_enough_pay($order_row)){
            $company->plus_frozen($task_row["firm_id"],$money);
        }else{  //佣金不够支付
            $company_row=$company->user_row($task_row["firm_id"]);
            if($company_row["money"]<$money){
                return false;
            }
            $company->plus_money($task_row["firm_id"],$money);
        }
        //给顾问或者内推者加钱
        if($order_row["inner_user_id"]){  //内推用户
            $user_id=$order_row["inner_user_id"];
        }elseif($order_row["headhunter_task_id"]){  //顾问
            $headhunter_row=$this->_headorder_model->find($order_row["headhunter_task_id"]);
            $user_id=$headhunter_row["user_id"];
        }
        $user=new User();
        $user->add_money($user_id,$money,$order_row["id"],1);
        return true;
    }

    //订单流程  拒绝面试
    public function refuse_interview($task_id,$user_id){
        $order_row=$this->has_join_task($user_id,$task_id);
        if(!$order_row){return false;}
        if($order_row["current_status"]!=2){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>4,
            "hunter_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>4));
        //发送消息
        $message=new Message();
        $message->refuse_interview($order_row);
        return true;
    }
    //订单流程  接受面试
    public function accept_interview($task_id,$user_id){
        $order_row=$this->has_join_task($user_id,$task_id);
        if(!$order_row){return false;}
        if($order_row["current_status"]!=2){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>5,
            "hunter_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>5));
        //发送消息
        $message=new Message();
        $message->accept_interview($order_row);
        return true;
    }
    //订单流程  取消面试
    public function cancel_interview($task_id,$user_id){
        $order_row=$this->has_join_task($user_id,$task_id);
        if(!$order_row){return false;}
        if($order_row["current_status"]!=5){
            return false;
        }
        //已过面试时间不能取消面试
        $interview_row=$this->interview_row($order_row["id"]);
        if($interview_row["interview_time"]<=date("Y-m-d H:i:s")){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>11,
            "hunter_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>11));
        //发送消息
        $message=new Message();
        $message->cancel_interview($order_row);
        return true;
    }
    //订单流程  拒绝就职
    public function refuse_position($task_id,$user_id){
        $order_row=$this->has_join_task($user_id,$task_id);
        if(!$order_row){return false;}
        if($order_row["current_status"]!=9){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>12,
            "hunter_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>12));
        //同时更改开工状态
        $this->_work_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>12));
        return true;
    }
    //订单流程  确认就职
    public function accept_position($task_id,$user_id){
        $order_row=$this->has_join_task($user_id,$task_id);
        if(!$order_row){return false;}
        if($order_row["current_status"]!=9){
            return false;
        }
        $task_row=$this->_task_model->find($order_row["task_id"]);
        //支付佣金
        if($task_row["commission_type"]==2 && $task_row["recruit_need"]==2 && in_array($order_row["add_type"],array(1,2))){   //支付押金且就职需求
            if(!$this->pay_commission($order_row,$task_row)){
                //return false;  //无法支付佣金也可以标记确认就职(具体如何提示和后续操作,后续再定)
            }
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>10,
            "hunter_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>10));
        //同时更改开工状态
        $this->_work_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>10));
        return true;
    }
    //订单流程  未入职
    public function no_work($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=10){
            return false;
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>14,
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>14));
        //同时更改开工状态
        $this->_work_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>14));
        return true;
    }
    //订单流程 已入职
    public function has_work($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_order_model->find($order_row);
        }
        if($order_row["current_status"]!=10){
            return false;
        }
        $task_row=$this->_task_model->find($order_row["task_id"]);
        //支付佣金
        if($task_row["commission_type"]==2 && $task_row["recruit_need"]==3 && in_array($order_row["add_type"],array(1,2))){   //支付押金且入职需求
            if(!$this->pay_commission($order_row,$task_row)){
                return false;
            }
        }
        $this->_order_model->where(array("id"=>$order_row["id"]))->save(array(
            "current_status"=>13,
            "hunter_tip_status"=>1,
            "seeker_tip_status"=>1,
        ));
        $this->_interview_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>13));
        //同时更改开工状态
        $this->_work_model->where(array(
            "jobhunter_order_id"=>$order_row["id"],
        ))->save(array("status"=>13));
        //发送消息
        $message=new Message();
        $message->has_work($order_row);
        return true;
    }
}
