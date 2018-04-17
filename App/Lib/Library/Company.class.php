<?php
/*
 * 公司类
 */
class Company extends Tool_User{
    public $_staff_model=null;//企业员工
    public $_jobhunter_order_model=null;//企业员工推荐
    public $_innerpush_model=null;//企业内推
    public $_company_job_model=null;//企业招聘会参加记录
    public $_task_model=null;//职位表
    public $_ocppye_model=null;//行业类别
    public $_server_model=null;//服务
    public function __construct(){
        $this->_user_model=new CompanyModel();
        $this->_staff_model=new StaffModel();
        $this->_innerpush_model=new InnerpushModel();
        $this->_jobhunter_order_model=new JobhunterOrderModel();
        $this->_company_job_model=new CompanyJobFairModel();
        $this->_task_model=new TaskModel();
        $this->_ocppye_model=new OcppyeModel();
        $this->_server_model=new CompanyServerModel();
    }
    //手机号码是否已经被注册
    public function mobile_register($mobile){
        $check_row=$this->_user_model->where(array("phone_number"=>$mobile))->find();
        if($check_row){return true;}
        return false;
    }
    //账号是否已经被注册
    public function account_register($account){
        $check_row=$this->_user_model->where(array("user_name"=>$account))->find();
        if($check_row){return true;}
        return false;
    }
    //添加信息
    public function add_row(array $data=array()){
        return $this->_user_model->add($data);
    }
    //减少金额
    public function plus_money($company_id,$money){
        if(is_array($company_id)){
            $company_id=$company_id["id"];
        }
        $this->_user_model->where(array("id"=>$company_id))->setDec("money",$money);
    }
    //增加金额
    public function add_money($company_id,$money){
        if(is_array($company_id)){
            $company_id=$company_id["id"];
        }
        $this->_user_model->where(array("id"=>$company_id))->setInc("money",$money);
    }
    //增加冻结金额
    public function add_frozen($company_id,$money){
        if(is_array($company_id)){
            $company_id=$company_id["id"];
        }
        $this->_user_model->where(array("id"=>$company_id))->setInc("forzen_money",$money);
    }
    //减少冻结金额
    public function plus_frozen($company_id,$money){
        if(is_array($company_id)){
            $company_id=$company_id["id"];
        }
        $this->_user_model->where(array("id"=>$company_id))->setDec("forzen_money",$money);
    }
    //企业员工
    public function staff_row($company_id){
        return $this->_staff_model->where(array(
            "company_id"=>$company_id,
            "is_delete"=>1,
        ))->select();
    }    
    //内推员工所在的企业
    public function staff_user_row($user_id){
        return $this->_staff_model->where(array(
            "user_id"=>$user_id,
            "is_delete"=>1,
        ))->field("company_id")->find();
    }
    //添加企业员工
    public function add_staff($company_id,$department,$name,$mobile){
        //能否添加企业员工
        $server_row=$this->_allow_add($company_id);
        if(!$server_row){return false;}
        //是否已经超过人员限制
        if($server_row["number"]<=$this->staff_number($company_id)){return false;}
        //是否已经存在该员工
        $check_row=$this->_staff_model->where(array(
            "mobile"=>$mobile,
            "is_delete"=>1,
        ))->find();
        if($check_row && !empty($check_row)){return false;}
        //添加员工
        $current_row=$this->_staff_model->where(array(
            "company_id"=>$company_id,
            "mobile"=>$mobile,
            "is_delete"=>2,
        ))->find();
        //发送消息
        $message=new Message();
        $message->add_staff($name,$mobile,$company_id);
        if(is_array($current_row) && !empty($current_row)){
            $this->_staff_model->where(array("id"=>$current_row["id"]))->save(array(
                "is_delete"=>1,
                "begin_time"=>$server_row["begin_time"],
                "end_time"=>$server_row["end_time"],
                "department"=>$department,
                "name"=>$name,
            ));
            return $current_row["id"];
        }else{
            return $this->_staff_model->add(array(
                "company_id"=>$company_id,
                "mobile"=>$mobile,
                "department"=>$department,
                "name"=>$name,
                "add_time"=>date("Y-m-d H:i:s"),
                "begin_time"=>$server_row["begin_time"],
                "end_time"=>$server_row["end_time"],
                "user_id"=>$this->_mobile_user_id($mobile),
            ));
        }
    }
    //能否添加企业员工(根据企业内推)
    private function _allow_add($company_id){
        static $allow_add=array();
        if(isset($allow_add[$company_id])){
            return $allow_add[$company_id];
        }
        $server=new CompanyServerModel();
        $server_row=$server->where(array(
            "company_id"=>$company_id,
            "type"=>1,
            "status"=>1,
            "end_time"=>array("egt",date("Y-m-d")." 23:59:59"),
        ))->find();
        if(!is_array($server_row)){
            $allow_add[$company_id]=false;return false;
        }
        $allow_add[$company_id]=$server_row;
        return $server_row;
    }
    //企业目前的员工数
    public function staff_number($company_id){
        $row=$this->_staff_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "is_delete"=>1,
            "end_time"=>array("gt",date("Y-m-d")." 00:00:00"),
        ))->find();
        return $row?$row["total"]:0;
    }
    //获取公司行业信息
    public function type_classify(){
        static $type_classify=null;
        if($type_classify){
            return $type_classify;
        }
        $ocppye_row=$this->_ocppye_model->order("parent_id")->select();
        $result=array();
        foreach($ocppye_row as $value){
            if($value["parent_id"]==0){
                $result[$value["id"]]=array(
                    "name"=>$value["item"],
                    "children"=>array(),
                );
            }
        }
        foreach($ocppye_row as $value){
            if(isset($result[$value["parent_id"]])){
                $result[$value["parent_id"]]["children"][$value["id"]]=$value["item"];
            }
        }
        $type_classify=$result;
        return $type_classify;
    }
    //获取公司融资情况
    public static function financing(){
        return array(
            0=>"不需要融资",
            1=>"种子轮",
            2=>"天使轮",
            3=>"pre-A",
            4=>"A轮",
            5=>"B轮",
            6=>"C轮",
            7=>"D轮",
            8=>"pre—ipo",
            9=>"上市",
        );
    }
    //获取公司规模
    public static function scale_type(){
        return array(
            0=>"20人以内",
            1=>"20-50人",
            2=>"51-100人",
            3=>"101-200人",
            4=>"200人以上",
        );
    }
    // 获取订单来源
    public function order_add_type(){
        static $type=null;
        if($type){return $type;}
        return array(
            "1"=>"主动投递简历",
            "2"=>"企业内推",
            "3"=>"全民猎头悬赏招聘",
            "4"=>"速职人才库",
            "5"=>"招聘会",
            );
    }

    // 获取求职订单任务状态-此状态由公司和求职者共同知道
    public function current_status_name($current_status){
        static $type=array();
        if(isset($type[$current_status])){return $type[$current_status];}
        switch ($current_status) {
            case '0':$current_name="求职者报名";break;
            case '1':$current_name="企业发送面试邀请";break;
            case '2':$current_name="企业已录取";break;
            case '3':$current_name="完成-佣金解冻";break;
            case '4':$current_name="3-10天企业辞退75%的佣金";break;
            case '5':$current_name="3-10天求职者辞职25%的佣金";break;
            case '6':$current_name="企业查看不通过";break;
            case '7':$current_name="企业面试不通过";break;
            case '8':$current_name="已完工";break;
            case '9':$current_name="未面试";break;
            default:
                "亲你操作有错误哦，请重新操作";
                break;
        }
        $type[$current_status]=$current_name;
        return $current_name;
    }
    //根据手机号码获取用户ｉｄ
    private function _mobile_user_id($mobile){
        $user=new UserModel();
        $user_row=$user->field("id")->where(array("phone_number"=>$mobile))->find();
        return $user_row?$user_row["id"]:0;
    }
    //根据手机号码获取企业用户ｉｄ
    public function _staff_user_id($mobile){
        $staff_row=$this->_staff_model->field("id")->where(array("mobile"=>$mobile,"is_delete"=>1))->find();
        return $staff_row?$staff_row["id"]:0;
    }
    // 企业参加招聘会总数
    public function _company_job_sum($company_id){
        $company_job_sum=$this->_company_job_model->where(array("company_id"=>$company_id))->count();
        return $company_job_sum;
    }
    // 邀面反馈总数
    public function jobhunter_order_list($firm_id){
        $task=new Task();
        $tasklist=$task->_task_model->where(array("firm_id"=>$firm_id,"is_deleted"=>0))->field('id')->select();
        $order_count=0;
        foreach ($tasklist as $key => $value) {
            $order_count+=$this->_jobhunter_order_model->where(array("task_id"=>$value['id'],"current_status"=>array("neq",0)))->count();
        }
        return $order_count;

    }
    //收到的简历
    public function receive_resume($company_id){
        $row=$this->_jobhunter_order_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "current_status"=>1
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //待面试
    public function wait_interview($company_id){
        $order=new HunterOrder();
        $row=$order->_interview_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "status"=>5,
            "interview_time"=>array("gt",date("Y-m-d H:i:s"))
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //面试后处理
    public function interview_handle($company_id){
        $order=new HunterOrder();
        $row=$order->_interview_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "status"=>5,
            "interview_time"=>array("elt",date("Y-m-d H:i:s"))
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //面试结果通知
    public function notice_number($company_id){
        $row=$this->_jobhunter_order_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "current_status"=>6
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //邀面反馈
    public function invitation_callback($company_id){
        $order=new HunterOrder();
        $row=$order->_interview_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "status"=>array("egt",4)
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //入职确定
    public function entry_sure($company_id){
        $row=$this->_jobhunter_order_model->field("count(id) as total")->where(array(
            "company_id"=>$company_id,
            "current_status"=>10
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //企业内推职位数
    public function innerpush_task_number($company_id){
        $task=new Task();
        $row=$task->_task_model->field("count(*) as total")->where(array(
            "firm_id"=>$company_id,
            "status"=>2,
            "recruit_type_inner"=>2,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //企业员工获取的累计内推佣金数
    public function staff_money($user_id){
        //订单
        $order_row=$this->_jobhunter_order_model->where(array(
            "inner_user_id"=>$user_id,
            "add_type"=>1,
        ))->select();
        if(empty($order_row)){return 0;}
        //职位
        $task_id=array();
        foreach($order_row as $value){
            $task_id[]=$value["task_id"];
        }
        $task_row=$this->_task_model->where(array("id"=>array("in",$task_id)))->select();
        $task_arr=array();
        foreach($task_row as $value){
            $task_arr[$value["id"]]=$value;
        }
        //总佣金
        $total=0;
        foreach($order_row as $value){
            $task=$task_arr[$value["task_id"]];
            if($task["recruit_need"]==1){ //简历
                $has_status=array(2,4,5,6,7,8,9,10,11,12,13,14);
            }elseif($task["recruit_need"]==2){  //就职
                $has_status=array(10,13,14);
            }elseif($task["recruit_need"]==3){  //入职
                $has_status=array(13);
            }
            if(in_array($value["current_status"],$has_status)){
                $total+=$task["commission"];
            }
        }
        return $total;
    }
    //是否已经开通内推职位
    public function has_open_innerpush($company_id){
        return $this->_server_model->where(array(
            "company_id"=>$company_id,
            "type"=>1
        ))->find();
    }
    //最近一次开通的内推服务
    public function last_innerpush_server($company_id){
        $row=$this->_server_model->where(array(
            "company_id"=>$company_id,
            "type"=>1,
        ))->order("id desc")->find();
        return $row;
    }
}
