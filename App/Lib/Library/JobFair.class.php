<?php
/*
 * 招聘会
 */
class JobFair{
    public $_jobfair_model=null;//招聘会
    public $_position_model=null;//招聘会摊位
    public $_user_model=null;//参会求职者
    public $_join_company_model=null;//企业参与招聘会记录
    public function __construct(){
        $this->_jobfair_model=new JobFairModel();
        $this->_position_model=new JobFairPositionModel();
        $this->_user_model=new JobFairUserModel();
        $this->_join_company_model=new CompanyJobFairModel();
    }
    //招聘会状态
    public static $_status=array(
        1=>"未开始",
        2=>"进行中",
        3=>"已结束"
    );
    //目前进行招聘会个数
    public function current_number(){
        $row=$this->_jobfair_model->field("count(*) as total")->where(array(
            "status"=>array("in",array(1,2)),
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //参会求职者
    public function join_user_number($jobfair_id){
        $row=$this->_user_model->field("count(*) as total")->where(array(
            "job_fair_id"=>$jobfair_id
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //参与招聘会企业
    public function join_company_number($jobfair_id){
        $row=$this->_join_company_model->field("count(*) as total")->where(array(
            "job_fair_id"=>$jobfair_id,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //招聘会基础价位
    public function base_money($jobfair_id){
        $row=$this->_position_model->field("min(money) as min_money")->where(array(
            "job_fair_id"=>$jobfair_id
        ))->find();
        return $row?$row["min_money"]:0;
    }
    //招聘会回报(邀面次数)
    public function interview_number($jobfair_id){
        $row=$this->_position_model->field("money,number")->where(array(
            "job_fair_id"=>$jobfair_id
        ))->order("money")->find();
        return $row?$row["number"]:0;
    }
    //是否已经参加
    public function has_join($jobfair_id,$company_id){
        $row=$this->_join_company_model->field("count(*) as total")->where(array(
            "company_id"=>$company_id,
            "job_fair_id"=>$jobfair_id,
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //招聘会摊位
    public function get_position($jobfair_id){
        $row=$this->_position_model->where(array(
            "job_fair_id"=>$jobfair_id,
        ))->select();
        foreach($row as $key=>$value){
            $row[$key]["left_number"]=$this->left_number($value);
        }
        return $row;
    }
    //摊位剩余数量
    public function left_number($position_row){
        if(is_numeric($position_row)){
            $position_row=$this->_position_model->find($position_row);
        }
        //参与总数量
        $has_row=$this->_join_company_model->where(array(
            "job_fair_position_id"=>$position_row["id"],
        ))->field("count(*) as total")->find();
        $total=$has_row["total"]*2;
        //剩余数量
        $left_number=$position_row["number"]-$total;
        return $left_number>=0?$left_number:0;
    }
    //购买摊位
    public function buy_position($company_id,$position_id){
        //摊位信息
        $position_row=$this->_position_model->find($position_id);
        if(!is_array($position_row)){return false;}
        //摊位是否足够
        if($this->left_number($position_row)<=0){
            return false;
        }
        //招聘会信息
        $jobfair_row=$this->_jobfair_model->find($position_row["job_fair_id"]);
        if($jobfair_row["status"]==3){
            return false;
        }
        //资金是否充足
        $company=new Company();
        $server=new Server();
        $company_row=$company->user_row($company_id);
        if($server->suzhicoin_total($company_id)<$position_row["money"]){
            return false;
        }
        //购买
        $this->_join_company_model->add(array(
            "company_id"=>$company_id,
            "job_fair_id"=>$position_row['job_fair_id'],
            "job_fair_position_id"=>$position_id,
            "money"=>$position_row["money"],
            "create_time"=>date("Y-m-d H:i:s"),
            "interview_number"=>$position_row["interview_number"],
            "interview_number_left"=>$position_row["interview_number"],
        ));
        //速职币操作
        $server->suzhicoin_plus_number($company_id,$position_row["money"]);
//         $company->plus_money($company_id,$position_row["money"]);
        return true;
    }

    // 求职者参会信息
    public function jobfair_row($user_id,$job_fair_id){
        return $this->_user_model->where(array("user_id"=>$user_id,'job_fair_id'=>$job_fair_id))->find();
    }

    // 求职者主动加入招聘会
    public function join_fair_add($job_fair_id,$user_id,array $data){
        $data['job_fair_id']=$job_fair_id;
        $data['user_id']=$user_id;
        return $this->_user_model->add($data);
    }

    //获取基本信息
    public function base_info($jobfair_id){
        $row=$this->_jobfair_model->find($jobfair_id);
        if(!$row){return false;}
        $row["company_number"]=$this->join_company_number($jobfair_id);
        $row["user_number"]=$this->join_user_number($jobfair_id);
        return $row;
    }
    //企业招聘会剩余邀面次数
    public function interview_left_number($company_id,$jobfair_id){
        $row=$this->_join_company_model->where(array(
            "job_fair_id"=>$jobfair_id,
            "company_id"=>$company_id
        ))->find();
        if(!$row){return false;}
        return $row["interview_number_left"];
    }
    //企业招聘减少剩余邀请次数
    public function interview_plus_number($company_id,$jobfair_id){
        $this->_join_company_model->where(array(
            "job_fair_id"=>$jobfair_id,
            "company_id"=>$company_id
        ))->setDec("interview_number_left",1);
    }
    //招聘会能否职位发布
    public function jobfair_post_task($company_id,$jobfair_id){
        if(!$this->has_join($jobfair_id,$company_id)){
            return false;
        }
        //是否能够发职位
        $task=new Task();
        $task_row=$task->_task_model->field("count(id) as total")->where(array(
            "firm_id"=>$company_id,
            "job_fair_id"=>$jobfair_id,
        ))->find();
        if($task_row["total"]>=2){
            return false;
        }
        return true;
    }
    //企业招聘会摊位信息
    public function company_position($company_id,$jobfair_id){
        $row=$this->_join_company_model->where(array(
            "company_id"=>$company_id,
            "job_fair_id"=>$jobfair_id,
        ))->find();
        if(!$row){return false;}
        return $row;
    }
}
