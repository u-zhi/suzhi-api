<?php
/*
 * 消息
 */
class Message{
    protected $_message_model=null;
    public function __construct(){
        $this->_message_model=new MessageModel();
    }
    //邀请面试发送消息
    public function apply_interview($interview_row){
        //邀面信息
        if(is_numeric($interview_row)){
            $interview=new JobhunterInterviewModel();
            $interview_row=$interview->where(array("id"=>$interview_row))->find();
        }
        //求职订单
        $order=new JobhunterOrderModel();
        $order_row=$order->where(array("id"=>$interview_row["jobhunter_order_id"]))->find();
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给求职者
        $content=$company_row["name"]."邀请您面试".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,面试时间{$interview_row["interview_time"]},联系人{$interview_row["contact"]},联系电话{$interview_row["contact_tel"]},请确认是否参加面试";
        $this->add_row($order_row["user_id"],$content,2);
        //发送短信
        $code=new Code();
        $code->send_msg($user_row["phone_number"],array($user_row["nickname"],"http://seeker.91suzhi.com"),205545);
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,企业反馈邀请该人才面试。";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //简历不合适
    public function deny_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //发送给求职者
        $content="您投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,企业反馈您当前不适合该岗位";
        $this->add_row($order_row["user_id"],$content,2);
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送短信
        $code=new Code();
        $code->send_msg($user_row["phone_number"],array($user_row["nickname"],"http://seeker.91suzhi.com"),205545);
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,企业反馈该人才暂时不适合该企业。";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //接受企业面试邀请
    public function accept_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,已接受企业面试邀请。";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
        //发送给企业
        $content="您邀请的{$user_row["nickname"]} 面试 ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K岗位,该人才接受面试邀请。";
        $this->add_row($order_row["company_id"],$content,1);
    }
    //拒绝面试邀请
    public function refuse_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给企业
        $content="您邀请的{$user_row["nickname"]} 面试 ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K岗位,该人才拒绝面试邀请。";
        $this->add_row($order_row["company_id"],$content,1);
    }
    //面试完成
    public function has_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,该人才已完成面试。";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //面试通过发offer
    public function offer_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送短信
        $code=new Code();
        $code->send_msg($user_row["phone_number"],array($user_row["nickname"],"http://seeker.91suzhi.com"),205545);
        //发送给求职者
        $content="您参加的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K的面试，企业给您发offer啦。";
        $this->add_row($order_row["user_id"],$content,2);
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,该人才已完成面试,企业已经给该人才发offer";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //确认入职
    public function has_work($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,该人才已确认入职";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //面试不通过
    public function nopass_interview($order_row){
        //求职订单
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给求职者
        $content="您参加的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K的面试，企业反馈您当前不适合该岗位。";
        $this->add_row($order_row["user_id"],$content,2);
        //发送短信
        $code=new Code();
        $code->send_msg($user_row["phone_number"],array($user_row["nickname"],"http://seeker.91suzhi.com"),205545);
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 投递的{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K,该人才已完成面试，企业面试反馈该人才不适合该企业";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
    }
    //投递简历
    public function post_task($order_row){
        //订单信息
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给顾问
        $recommend_user=$order_row["header_id"]?$order_row["header_id"]:$order_row["inner_user_id"];
        if($recommend_user>0){
            $content="您推荐的{$user_row["nickname"]} 已投递{$company_row["name"]} ".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K 岗位。";
            $this->add_row($recommend_user,$content,0);
            //顾问信息
            $recommend_row=$user->where(array("id"=>$recommend_user))->find();
            //发送短信
            $code=new Code();
            $code->send_msg($recommend_row["phone_number"],array($recommend_row["nickname"],"http://hunter.91suzhi.com"),205546);
        }
        //发送给企业
        $content="{$user_row["nickname"]} 投递了".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K 岗位,请处理。";
        $this->add_row($order_row["company_id"],$content,1);
    }
    //取消面试
    public function cancel_interview($order_row){
        //订单信息
        if(is_numeric($order_row)){
            $order=new JobhunterOrderModel();
            $order_row=$order->where(array("id"=>$order_row))->find();
        }
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给企业
        $content="{$user_row["nickname"]} 面试".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K 岗位,求职者取消面试。";
        $this->add_row($order_row["company_id"],$content,1);
    }
    //面试提示
    public function interview_tips($interview_row){
        //邀面信息
        if(is_numeric($interview_row)){
            $interview=new JobhunterInterviewModel();
            $interview_row=$interview->where(array("id"=>$interview_row))->find();
        }
        //求职订单
        $order=new JobhunterOrderModel();
        $order_row=$order->where(array("id"=>$interview_row["jobhunter_order_id"]))->find();
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给求职者
        $content=$user_row["nickname"]."您有今天的 ".$company_row["name"].$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K面试,请准时到场.如取消面试请提前处理";
        $this->add_row($order_row["user_id"],$content,2);
        //发送短信
        $code=new Code();
        $code->send_msg($user_row["phone_number"],array($user_row["nickname"],"http://seeker.91suzhi.com"),205545);
    }
    //面试后处理
    public function interview_handle($interview_row){
        //邀面信息
        if(is_numeric($interview_row)){
            $interview=new JobhunterInterviewModel();
            $interview_row=$interview->where(array("id"=>$interview_row))->find();
        }
        //求职订单
        $order=new JobhunterOrderModel();
        $order_row=$order->where(array("id"=>$interview_row["jobhunter_order_id"]))->find();
        //职位信息
        $task=new TaskModel();
        $task_row=$task->where(array("id"=>$order_row["task_id"]))->find();
        $salary_range=explode(",",$task_row["salary_range"]);
        //公司信息
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$order_row["company_id"]))->find();
        //求职者信息
        $user=new UserModel();
        $user_row=$user->where(array("id"=>$order_row["user_id"]))->find();
        //发送给企业
        $content="{$user_row["nickname"]} 面试".$task_row["name"].",月薪{$salary_range[0]}-{$salary_range[1]}K 岗位,请进行面试确认。";
        $this->add_row($order_row["company_id"],$content,1);
    }
    //添加企业成员
    public function add_staff($name,$mobile,$company_id){
        $company=new CompanyModel();
        $company_row=$company->where(array("id"=>$company_id))->find();
        $code=new Code();
        $code->send_msg($mobile,array($name,$company_row["name"],"http://hunter.91suzhi.com"),205543);
    }
    //内推到期
    public function innerpush_expire($company_row,$server_row){
        if(is_numeric($company_row)){
            $company=new CompanyModel();
            $company_row=$company->where(array("id"=>$company_row))->find();
        }
        $content="您开通的“".$server_row["number"]."人”内推系统，已经到期，请注意续费";
        $this->add_row($company_row["id"],$content,1);
        //发送短信
        $code=new Code();
        $code->send_msg($company_row["phone_number"],array($server_row["number"]."人"),205026);
    }
    //邀请面试
    public function apply_interview2($company_id){
        $company=new Company();
        $company_row=$company->_user_model->where(array("id"=>$company_id))->find();
        $number=$company->invitation_callback($company_id);
        if($number==50){
            $content="您开通的邀请面试次数已经少于“50次”，请注意续费";
            $this->add_row($company_id,$content,1);
            //发送短信
            $code=new Code();
            $code->send_msg($company_row["phone_number"],array(50),205028);
        }
    }
    //增加消息
    public function add_row($user_id,$content,$user_type=1){
        return $this->_message_model->add(array(
            "user_id"=>$user_id,
            "user_type"=>$user_type,
            "create_time"=>date("Y-m-d H:i:s"),
            "content"=>$content,
        ));
    }
    //标记消息为已读
    public function read_message($id){
        $this->_message_model->where(array("id"=>$id))->save(array(
            "is_read"=>1
        ));
    }
}