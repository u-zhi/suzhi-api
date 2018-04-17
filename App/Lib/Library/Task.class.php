<?php
/*
 * 职位表
 */
class Task{
    protected $_task_model=null;//职位表
    protected $_occupation_model=null;//职位名称
    protected $_hunter_order_model=null;//求职订单
    protected $_ocppy_model=null;//职位类别
    //状态
    public static $_status=array(
        1=>"待发布",
        2=>"上架中",
        3=>"已关闭",
    ); 
    //
    public static $recruit_need=array(
        1=>"简历",
        2=>"面试",
        3=>"入职",
    );
    //资料是否齐全
    public static $_data_complete=array(
        1=>"资料不全",
        2=>"资料齐全",
    );
    //投递职位来源类型
    public static $_post_type=array(
        1=>"内推",
        2=>"顾问",
        3=>"自主投递",
        4=>"招聘会",
        5=>"企业邀面",
    );
    public function __construct(){
        $this->_task_model=new TaskModel();
        $this->_occupation_model=new OccupationModel();
        $this->_hunter_order_model=new JobhunterOrderModel();
        $this->_ocppy_model=new OcppyModel();
    }
    public function __get($name){
        if(substr($name,-6)=="_model"){
            return $this->$name;
        }
        return false;
    }
    //职位类别
    public function occupation_type(){
        static $type=null;
        if($type){return $type;}
        $ocppy_row=$this->_ocppy_model->order("parent_id")->select();
        $result=array();
        foreach($ocppy_row as $value){
            if($value["parent_id"]==0){
                $result[$value["id"]]=array(
                    "name"=>$value["item"],
                    "children"=>array(),
                );
            }
        }
        foreach($ocppy_row as $value){
            if(isset($result[$value["parent_id"]])){
                $result[$value["parent_id"]]["children"][$value["id"]]=$value["item"];
            }
        }
        $type=$result;
        return $type;
    }
    //学历要求
    public function education_type(){
        static $education_type=null;
        if($education_type){return $education_type;}
        $education_type=array(
            1=>"高中以下",
            2=>"高中",
            3=>"专科",
            4=>"本科",
            5=>"硕士",
            6=>"硕士以上"
        );
        return $education_type;
    }
    //工作年限要求
    public function work_year(){
        static $work_year=null;
        if($work_year){return $work_year;}
        $work_year=array(
            1=>"不限",
            2=>"1年以内",
            3=>"1-3年",
            4=>"3-5年",
            5=>"5-10年",
            6=>"10年以上",
        );
        return $work_year;
    }
    //面试结果通知时间
    public function notify_time(){
        static $notify_time=null;
        if($notify_time){return $notify_time;}
        $notify_time=array(
            1=>"1-3天",
            2=>"3-5天",
            3=>"5-10天",
        );
        return $notify_time;
    }
    //招聘需求
    public function recruit_need(){
        static $recruit_need=null;
        if($recruit_need){return $recruit_need;}
        $recruit_need=array(
            1=>"简历",
            2=>"面试",
            3=>"入职",
        );
        return $recruit_need;
    }
    //发布职位
    public function add($company_id,array $data){
        $company=new Company();
        $company_row=$company->user_row($company_id);
        $data["scale_type"]=$company_row['scale_type'];
        $data["firm_id"]=$company_id;
        $data["county_id"]=explode('#',$data['work_address_pro_cit_cou'])[2];
        $data["task_type"]=1;
        $data["work_schedule"]='';
        $data["benefits"]='';
        $data["image_url"]='';
        $data["data_complete"]=2;
        $data["create_time"]=date("Y-m-d H:i:s");
        $data["commission"]=isset($data["commission"])?100*$data["commission"]:0;
        return $this->_task_model->add($data);
    }
    //任务支付押金(都需要走这个方法)
    public function pay_deposit($task_row,$company_id){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        if(!$task_row || $task_row["firm_id"]!=$company_id){
            return false;
        }
        if($task_row["status"]!=1){
            return false;
        }
        //支付押金
        if($task_row["recruit_type_hunter"]==2 || $task_row["recruit_type_inner"]==2){
            if($task_row["commission_type"]==2){  //支付押金
                $total_money=$task_row["commission"]*$task_row["person_demand"];
                //用户金额支付
                $company=new Company();
                $company_row=$company->user_row($company_id);
                if($company_row["money"]<$total_money){
                    return false;
                }
                $company->plus_money($company_id,$total_money);
                $company->add_frozen($company_id,$total_money);
            }
        }
        $this->_task_model->where(array("id"=>$task_row["id"]))->save(array("status"=>2));
        //如果是内推职位发送消息
        if($task_row["recruit_type_inner"]==2){
            $notice=new TaskNoticeModel();
            $notice->add(array(
                "task_id"=>$task_row["id"]
            ));
        }
        return true;
    }
    //修改职位
    public function edit($task_id,array $data){
        if(isset($data['work_address_pro_cit_cou'])){
            $data['county_id']=explode('#',$data['work_address_pro_cit_cou'])[2];
        }
        $data["commission"]=isset($data["commission"])?100*$data["commission"]:0;
        $this->_task_model->where(array("id"=>$task_id))->save($data);
    }
    //获取职位信息
    public function get_row($task_id,$company_id=0){
        $task_row=$this->_task_model->find($task_id);
        if(!$task_row || empty($task_row)){return false;}
        if($company_id && $task_row["firm_id"]!=$company_id){
            return false;
        }
        return $task_row;
    }
    // 获取职位类别名称二的名称
    public function get_occupation_name($occupation_two_id){
        $occupation_row=$this->_occupation_model->find($occupation_two_id);
        if(!$occupation_row || empty($occupation_row)){return false;}
        return $occupation_row;
    }
    //根据ｉｄ获取人物
    public function get_by_ids($task_ids,$field="*"){
        if(is_numeric($task_ids)){
            $task_ids=array($task_ids);
        }
        if(empty($task_ids)){return array();}
        $row=$this->_task_model->field($field)->where(array("id"=>array("in",$task_ids)))->select();
        return $row;
    }
    //职位收到的简历
    public function task_order_number($task_id){
        $row=$this->_hunter_order_model->field("count(*) as total")->where(array("task_id"=>$task_id))->find();
        return $row["total"]?$row["total"]:0;
    }
    // 求职者订单row    
    public function task_order_row($user_id){
        return  $this->_hunter_order_model->where(array("user_id"=>$user_id))->find();
    }
    //职位面试人数
    public function task_interview_number($task_id){
        $row=$this->_hunter_order_model->field("count(*) as total")->where(array(
            "task_id"=>$task_id,
            "current_status"=>array("in",array(6,8,9,10,12,13,14)),
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //关闭职位
    public function close_task($task_row){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        if($task_row["status"]==3){return false;}
        $this->_task_model->where(array("id"=>$task_row["id"]))->save(array("status"=>3));
        return true;
    }
    //发布的职位
    public function publish_number($company_id){
        $row=$this->_task_model->field("count(*) as total")->where(array(
            "firm_id"=>$company_id,
            "status"=>2,
            "is_deleted"=>0,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //关闭的职位
    public function close_number($company_id){
        $row=$this->_task_model->field("count(*) as total")->where(array(
            "firm_id"=>$company_id,
            "status"=>3,
            "is_deleted"=>0,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //未成功发布的职位
    public function no_success_number($company_id){
        $row=$this->_task_model->field("count(*) as total")->where(array(
            "firm_id"=>$company_id,
            "status"=>1,
            "is_deleted"=>0,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //可退佣金  关闭的职位
    public function allow_reback_money($task_row){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        if($task_row["status"]!=3){return 0;}
        //如果有冻结金额就解冻金额
        if($task_row["commission_type"]==2){
            //是否已经退佣金
            if($task_row["is_back_commission"]==2){
                return 0;
            }
            if($task_row["recruit_need"]==1){  //简历
                $wait_status=array(1);
                $has_status=array(2,4,5,6,7,8,9,10,11,12,13,14);
            }elseif($task_row["recruit_need"]==2){  //到岗
                $wait_status=array(1,2,5,6,9);
                $has_status=array(10,13,14);
            }elseif($task_row["recruit_need"]==3){  //入职
                $wait_status=array(1,2,5,6,9,10);
                $has_status=array(13);
            }
            //未处理订单（内推和顾问）
            $wait_row=$this->_hunter_order_model->field("count(*) as total")->where(array(
                "task_id"=>$task_row["id"],
                "current_status"=>array("in",$wait_status),
                "add_type"=>array("in",array(1,2))
            ))->find();
            if($wait_row["total"]<=0){
                $has_row=$this->_hunter_order_model->field("count(*) as total")->where(array(
                    "task_id"=>$task_row["id"],
                    "current_status"=>array("in",$has_status),
                    "add_type"=>array("in",array(1,2))
                ))->find();
                //剩余用金额直接退
                $number=$task_row["person_demand"]-$has_row["total"];
                if($number>0){
                    $money=$task_row["commission"]*$number;
                    return $money;
                }
            }
        }
        return 0;
    }
    //退佣操作
    public function reback_money($task_row){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        $money=$this->allow_reback_money($task_row);
        if($money>0){
            $company=new Company();
            $company->add_money($task_row['firm_id'],$money);
            $company->plus_frozen($task_row['firm_id'],$money);
            $this->_task_model->where(array(
                "id"=>$task_row["id"],
            ))->save(array("is_back_commission"=>2));
            return true;
        }
    }
    //佣金是否足够支付
    public function commission_enough_pay($order_row){
        if(is_numeric($order_row)){
            $order_row=$this->_hunter_order_model->find($order_row);
        }
        if(!in_array($order_row["add_type"],array(1,2))){
            return true;
        }
        $task_row=$this->_task_model->find($order_row["task_id"]);
        if($task_row["commission_type"]!=2){
            return true;
        }
        if($task_row["recruit_need"]==1){  //简历
            $has_status=array(2,4,5,6,7,8,9,10,11,12,13,14);
        }elseif($task_row["recruit_need"]==2){  //到岗
            $has_status=array(10,13,14);
        }elseif($task_row["recruit_need"]==3){  //入职
            $has_status=array(13);
        }
        $has_row=$this->_hunter_order_model->field("count(*) as total")->where(array(
            "task_id"=>$task_row["id"],
            "current_status"=>array("in",$has_status),
            "add_type"=>array("in",array(1,2))
        ))->find();
        //剩余名额
        $number=$task_row["person_demand"]-$has_row["total"];
        return $number>0?true:false;
    }
    //速职人才库能否直接发起邀面
    public function allow_apply_interview($company_id,$task_id,$user_id){
        $task_row=$this->_task_model->find($task_id);
        if(!$task_row){return false;}
        if($task_row["status"]!=2){return false;}
        if($task_row["firm_id"]!=$company_id){return false;}
        //企业邀请面试剩余名额
        $server=new Server();
        $number=$server->interview_total($company_id);
        if($number<1){
            return 0;
        }
        //是否已经投递过改该职位
        $order=new HunterOrder();
        if($order->has_join_task($user_id,$task_id)){
            return false;
        }
        return true;
    }
    //速职人才库直接发起邀面
    public function apply_interview($task_row,$user_id,$interview_time){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        $order=new HunterOrder();
        //模拟投递简历
        $order_id=$order->post_task($user_id,$task_row,5);
        //自动发起邀请面试
        $interview_row=$order->pass_interview($order_id,$interview_time);
        //更改剩余邀请数量
        $server=new Server();
        $server->interview_plus_number($task_row["firm_id"]);
        //记录消息
        $code=new Message();
        $code->apply_interview($task_row["firm_id"]);
        return $interview_row;
    }
    //招聘会人才能否直接发起邀面
    public function jobfair_allow_apply_interview($company_id,$task_id,$user_id){
        $task_row=$this->_task_model->find($task_id);
        if(!$task_row){return false;}
        if($task_row["status"]!=2){return false;}
        if($task_row["firm_id"]!=$company_id){return false;}
        //企业邀请面试剩余名额
        $jobfair=new JobFair();
        $number=$jobfair->interview_left_number($company_id,$task_row["job_fair_id"]);
        if($number<1){
            return 0;
        }
        //是否已经投递过改该职位
        $order=new HunterOrder();
        if($order->has_join_task($user_id,$task_id)){
            return false;
        }
        return true;
    }
    //招聘会直接发起邀面
    public function jobfair_apply_interview($task_row,$user_id,$interview_time){
        if(is_numeric($task_row)){
            $task_row=$this->_task_model->find($task_row);
        }
        $order=new HunterOrder();
        //模拟投递简历
        $order_id=$order->post_task($user_id,$task_row,4);
        //自动发起邀请面试
        $interview_row=$order->pass_interview($order_id,$interview_time);
        //更改剩余邀请数量
        $jobfair=new JobFair();
        $jobfair->interview_plus_number($task_row["firm_id"],$task_row["job_fair_id"]);
        return $interview_row;
    }
}
