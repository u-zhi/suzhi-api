<?php
//招聘会
class FairAction extends BaseAction {
    /*
        招聘会列表-参加招聘会显示--erp记得加哦city_id
        @token
    */
    public function fair_lists(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}
        $jobfair=new JobFair();
        $select=$jobfair->_jobfair_model->where(array("status"=>1))->order("begin_time asc");
        //分页
        $page=intval($this->_get("page")) ? intval($this->_get("page")) : 1 ;
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $city=new Tool_Region();
        foreach ($content as $key => $value) {
            $content[$key]["company_number"]=$jobfair->join_company_number($value['id']);
            $content[$key]["user_number"]=$jobfair->join_user_number($value['id']);
            $content[$key]["city_name"]=$city->country_name($value['city_id']);
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }


    /*参加招聘会
        @token
        @job_fair_id 招聘会id
    */
    public function join_fair(){
         $user_id=$this->_user_id;
         $job_fair_id=$this->_post("job_fair_id");
        if(empty($user_id)||empty($job_fair_id)){$this->error("亲请正确操作！");}
        // 获取用户基本信息
        $user=new User();
        $user_row=$user->get_row($user_id);
        $user_row_info=$user->get_extra_info($user_id);
        $user_job_row=$user->cv_job_row($user_id);
        $jobfair=new JobFair();
        $jobfair_row=$jobfair->jobfair_row($user_id,$job_fair_id);
        if($jobfair_row){
            $this->success(array());
        }else{
            $salary=$user_job_row['wage_lower']."-".$user_job_row['wage_upper']."K";
            $data=array(
                "add_type"=>1,
                "add_time"=>date("Y-m-d H:i:s"),
                "resume"=>'',
                "name"=>$user_row['nickname'],
                "city"=>$user_job_row['city'],
                "year"=>$user_row_info['work_year'],
                "position"=>$user_job_row['occupation'],
                "salary"=>$salary,
            );
            $jobfair_add=$jobfair->join_fair_add($job_fair_id,$user_id,$data);
            if($jobfair_add){
                $this->success(array("content"=>"true"));
            }else{
                $this->error("参加招聘会失败，请重新参加");
            }
        }
    }


    /*
        招聘会页面---招聘会职位
        @token
        @job_fair_id
    */
    public function jobfair_task_profile(){
         $user_id=$this->_user_id;
         $job_fair_id=$this->_get("job_fair_id");
        if(empty($user_id) || empty($job_fair_id)){$this->error("亲请正确操作！");}
         //排序的方式  on_time发布时间  salary工资 scale企业规模 
        $order_way=strval($this->_get("order_way")) ? strval($this->_get("order_way")) : "" ;
        $task=new Task();
        if($order_way){
            $task->_task_model->order("$order_way desc");
        }else{  //默认按照摊位金额排序
            $task->_task_model->order("job_fair_money desc,create_time");
        }
        $task->_task_model->where(array(
            "status"=>2,
            "is_deleted"=>0,
            "job_fair_id"=>$job_fair_id,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($task->_task_model,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $type_classify=$company->type_classify();
        foreach($content as $key=>$value){
            $company_obj=$company->user_row($value["company_id"]);
            $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[0]]["name"];
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }

    //招聘会基本信息
    public function base_info(){
        $jobfair_id=intval($this->_get("jobfair_id"));
        $jobfair=new JobFair();
        $row=$jobfair->base_info($jobfair_id);
        if(!$row){
            $this->error("信息错误");
        }
        $this->success($row);
    }
    
}
