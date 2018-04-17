<?php
/*
 * 速职人力外包
 */
class Outsourcing{
    protected $_sourcing_model=null;//速职人力外包
    protected $_talent_model=null;//速职顾问人才库
    //状态
    public static $_status=array(
        1=>"待处理",
        2=>"已删除",
        3=>"已报价",
        4=>"合作中",
        5=>"拒绝报价",
        6=>"终止",
    );
    public function __construct()
    {
        $this->_sourcing_model=new OutsourcingModel();
        $this->_talent_model=new TalentPoolModel();
    }
    public function __get($name){
        if(substr($name,-6)=="_model"){
            return $this->$name;
        }
        return false;
    }
    //发布人才需求
    public function add($company_id,array $data){
        $data["company_id"]=$company_id;
        $data["create_time"]=date("Y-m-d H:i:s");
        $this->_sourcing_model->add($data);
    }
    //终止合作项目
    public function stop($project_row,$reason='',$company_id=0){
        if(is_numeric($project_row)){
            $project_row=$this->_sourcing_model->find($project_row);
        }
        if($company_id && $project_row["company_id"]!=$company_id){
            return false;
        }
        if($project_row["status"]!=4){
            return false;
        }
        $this->_sourcing_model->where(array("id"=>$project_row["id"]))->save(array(
            "status"=>6,
            "stop_reason"=>$reason,
            "stop_time"=>date("Y-m-d H:i:s"),
        ));
        return true;
    }
    //接受报价
    public function pass($project_row,$company_id=0){
        if(is_numeric($project_row)){
            $project_row=$this->_sourcing_model->find($project_row);
        }
        if($company_id && $project_row["company_id"]!=$company_id){
            return false;
        }
        if($project_row["status"]!=3){
            return false;
        }
        //金额是否足够
        $total_money=$project_row["person_demand"]*$project_row["offer_money"];
        $company=new Company();
        $company_row=$company->user_row($company_id);
        if($company_row["money"]<$total_money){
            return false;
        }
        $company->plus_money($company_id,$total_money);
        $company->add_frozen($company_id,$total_money);
        $this->_sourcing_model->where(array("id"=>$project_row["id"]))->save(array(
            "status"=>4,
        ));
        return true;
    }
    //拒绝报价
    public function deny($project_row,$company_id=0){
        if(is_numeric($project_row)){
            $project_row=$this->_sourcing_model->find($project_row);
        }
        if($company_id && $project_row["company_id"]!=$company_id){
            return false;
        }
        if($project_row["status"]!=3){
            return false;
        }
        $this->_sourcing_model->where(array("id"=>$project_row["id"]))->save(array(
            "status"=>5,
        ));
        return true;
    }
    //获取职位详情
    public function get_row($project_id,$company_id=0){
        $project_row=$this->_sourcing_model->find(array("id"=>$project_id));
        if(!$project_row){return false;}
        if($company_id && $project_row["company_id"]!=$company_id){
            return false;
        }
        return $project_row;
    }
    // 获取单个职位详情
    public function get_row_info($project_id){
        $project_row=$this->_sourcing_model->find(array("id"=>$project_id));
        if(!$project_row){return false;}
        return $project_row;
    }
    // 获取顾问人才库总数
    public function talentsum($user_id){
        return $this->_talent_model->where(array("headhunter_user_id"=>$user_id))->count();
    }
    // 存入人才库
    public function join_hunter_talent($data){
        return $this->_talent_model->add($data);
    }
    // 人才库单条
    public function join_mytalent_row($headhunter_user_id,$jobhunter_user_id){
        return $this->_talent_model->where(array('headhunter_user_id'=>$headhunter_user_id,'jobhunter_user_id'=>$jobhunter_user_id))->find();
    }

}