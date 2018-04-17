<?php
/**
 * des:CrontabAction.php
 * User: liangbo
 * Date: 2018/3/11
 * Time: 下午11:44
 */

class CrontabAction extends Action
{
    /**
     * 定时任务，定时推送简历给企业
    */
    public function autopush()
    {
        $task=new Task();
        $all=$task->_task_model->select();
        var_dump($all);exit();
        $task_id=intval($this->_post("task_id"));
        $user_id=intval($this->_post("user_id"));
        $refer=$this->_post("refer");
        if(!$task_id || !$user_id){
            $this->error("非法操作");
        }
            $interview_time=trim(strval($this->_post("interview_time")));
            $interview_row=$task->apply_interview($task_id,$user_id,$interview_time);
            //记录消息
            $message=new Message();
            $message->apply_interview2($interview_row);
            if($refer)
            {
                $this->success("邀面成功",U("square/lists",array("user_id"=>$user_id)));exit;
            }else{

                $this->success("邀面成功");exit;
            }
    }

}