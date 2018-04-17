<?php
// 智能推荐
class PushAction extends Action {

    /**
     * 定时任务，定时推送简历给企业
     */
    public function autopush()
    {
        $task=new Task();
        //#todo 先模拟一个沙盒
        $all=$task->_task_model->select();
        foreach ($all as $k=>$v){
            $interview_time=time();
            $user_id=rand(1,1000000);
            $interview_row=$task->apply_interview($v["id"],$user_id,$interview_time);
            //记录消息
            $message=new Message();
            $message->apply_interview2($interview_row);
        }

    }

}
