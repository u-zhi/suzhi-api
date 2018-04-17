<?php
// 短信相关
class SmsAction extends Action {

    //面试提示 每天8点执行一次
    public function interview_tips(){
        $interview=new JobhunterInterviewModel();
        $message=new Message();
        $begin_id=0;
        BEGIN_TAG:
        $interview_row=$interview->where(array(
            "id"=>array("gt",$begin_id),
            "status"=>5,
            "interview_time"=>array(array("egt",date("Y-m-d")." 00:00:00"),array("elt",date("Y-m-d")." 23:59:59")),
        ))->order("id")->limit(1)->find();
        if($interview_row){
            $message->interview_tips($interview_row);
            $begin_id=$interview_row["id"];
            goto BEGIN_TAG;
        }
    }

    //面试后处理 每分钟执行一次
    public function interview_handle(){
        $interview=new JobhunterInterviewModel();
        $message=new Message();
        $begin_id=0;
        $begin_time=date("Y-m-d H:i",time()-60).":00";
        $end_time=date("Y-m-d H:i",time()-60).":59";
        BEGIN_TAG:
        $interview_row=$interview->where(array(
            "id"=>array("gt",$begin_id),
            "status"=>5,
            "interview_time"=>array(array("egt",$begin_time),array("elt",$end_time)),
        ))->order("id")->limit(1)->find();
        if($interview_row){
            $message->interview_handle($interview_row);
            $begin_id=$interview_row["id"];
            goto BEGIN_TAG;
        }
    }

    //从队列里面进行发送 每分钟执行一次
    public function log_send(){
        $log=new SmsLogModel();
        $code=new Code();
        $begin_id=0;
        BEGIN_TAG:
        $log_row=$log->where(array(
            "id"=>array("gt",$begin_id),
            "is_send"=>1,
        ))->order("id")->limit(1)->find();
        if($log_row){
            $data=json_decode($log_row["params"],true);
            $code->send_msg($log_row["mobile"],$data,$log_row["template_id"],true);
            $log->where(array("id"=>$log_row["id"]))->save(array("is_send"=>2));
            $begin_id=$log_row["id"];
            goto BEGIN_TAG;
        }
    }

    //内推职位通知员工 每5分钟执行一次
    public function task_notice(){
        $notice=new TaskNoticeModel();
        $staff=new StaffModel();
        $task=new TaskModel();
        $company=new CompanyModel();
        $sms=new SmsLogModel();
        $begin_id=0;
        BEGIN_TAG:
        $notice_row=$notice->where(array(
            "id"=>array("gt",$begin_id),
            "is_notice"=>1
        ))->order("id")->limit(1)->find();
        if($notice_row){
            //职位信息
            $task_row=$task->where(array("id"=>$notice_row["task_id"]))->find();
            //企业信息
            $company_row=$company->where(array("id"=>$task_row["firm_id"]))->find();
            //查找员工进行发送消息
            $staff_row=$staff->where(array(
                "company_id"=>$task_row["firm_id"],
                "end_time"=>array("elt",date("Y-m-d H:i:s"))
            ))->field("user_id,name,mobile")->select();
            $sms_arr=array();
            foreach($staff_row as $value){
                $sms_arr[]=array(
                    "mobile"=>$value["mobile"],
                    "template_id"=>205544,
                    "params"=>json_encode(array(
                        $value["name"],$company_row["name"],"http://hunter.91suzhi.com"
                    )),
                );
            }
            if($sms_arr){
                $sms->addAll($sms_arr);
            }
            $notice->where(array(
                "id"=>$notice_row["id"]
            ))->save(array("is_notice"=>2));
            $begin_id=$notice_row["id"];
            goto BEGIN_TAG;
        }
    }

    //内推到期通知 每天0点跑一次
    public function innerpush_expire(){
        $server=new CompanyServerModel();
        $message=new Message();
        $begin_id=0;
        BEGIN_TAG:
        $server_row=$server->where(array(
            "id"=>array("gt",$begin_id),
            "type"=>1,
            "status"=>1,
            "end_time"=>array("lt",date("Y-m-d H:i:s"))
        ))->order("id")->limit(1)->find();
        if($server_row){
            $server->where(array("id"=>$server_row["id"]))->save(array("status"=>2));
            $message->innerpush_expire($server_row["company_id"],$server_row);
            $begin_id=$server_row["id"];
            goto BEGIN_TAG;
        }
    }

}
