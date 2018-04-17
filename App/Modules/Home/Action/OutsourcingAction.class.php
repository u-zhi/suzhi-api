<?php
// 企业外包
class OutsourcingAction extends Action {

    //整体概况
    public function index(){

        $this->display();
    }
    //合作项目
    public function lists(){
        $outsourcing=new Outsourcing();
        $select=$outsourcing->_sourcing_model->where(array(
            "company_id"=>Company::login_user_id(),
            "status"=>4,
        ))->order("create_time desc");
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        $task=new Task();
        $education_type=$task->education_type();
        $work_year=$task->work_year();
        $recruit_need=$task->recruit_need();
        foreach($content as $key=>$value){
            $content[$key]["education_type"]=$education_type[$value["education_type"]];
            $content[$key]["work_year"]=$work_year[$value["work_year"]];
            $content[$key]["recruit_need"]=$recruit_need[$value["recruit_need"]];
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //项目反馈
    public function callback(){
        $outsourcing=new Outsourcing();
        $select=$outsourcing->_sourcing_model->where(array(
            "company_id"=>Company::login_user_id(),
            "status"=>3,
        ))->order("create_time desc");
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        $task=new Task();
        $education_type=$task->education_type();
        $work_year=$task->work_year();
        $recruit_need=$task->recruit_need();
        foreach($content as $key=>$value){
            $content[$key]["education_type"]=$education_type[$value["education_type"]];
            $content[$key]["work_year"]=$work_year[$value["work_year"]];
            $content[$key]["recruit_need"]=$recruit_need[$value["recruit_need"]];
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //添加项目
    public function add(){
        if($this->isPost()){
            $data=$this->_post();
            $data['work_address_pro_cit_cou']="#".$data["work_address_province_id"]."#".$data["work_address_city_id"]."#".$data["work_address_country_id"]."#";
            $data["salary_range"]=$data["salary_min"].",".$data["salary_max"];
            $data["skill_need"]=json_encode($data["skill_need"]);
            $data["commission"]=$data["commission"]?$data["commission"]:0;
            $data["person_demand"]=$data["person_demand"]?$data["person_demand"]:1;

            $outsourcing=new Outsourcing();
            $outsourcing->add(Company::login_user_id(),$data);
            $this->success("发布成功,等待平台进行处理",U('outsourcing/lists'));
        }else{
            $task=new Task();
            $this->assign("occupation_type",$task->occupation_type());//职位类别
            $this->assign("education_type",$task->education_type());//学历要求
            $this->assign("work_year",$task->work_year());//工作年限要求
            $this->assign("notify_time",$task->notify_time());//面试结果通知时间
            $this->assign("recruit_need",$task->recruit_need());//招聘需求
            $this->display();
        }
    }
    //拒绝报价
    public function deny(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $outsourcing=new Outsourcing();
            if($outsourcing->deny($task_id,Company::login_user_id())){
                $this->success("拒绝报价成功",U('outsourcing/callback'));
            }else{
                $this->success("拒绝报价失败",U('outsourcing/callback'));
            }
        }
    }
    //接受报价
    public function pass_money(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $outsourcing=new Outsourcing();
            if($outsourcing->pass($task_id,Company::login_user_id())){
                $this->success("接受报价成功",U('outsourcing/callback'));
            }else{
                $this->success("接受报价失败",U('outsourcing/callback'));
            }
        }
    }
    //终止项目
    public function stop(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $outsourcing=new Outsourcing();
            if($outsourcing->stop($task_id,'',Company::login_user_id())){
                $this->success("终止项目成功",U('outsourcing/lists'));
            }else{
                $this->success("终止项目失败",U('outsourcing/lists'));
            }
        }
    }
    //职位详情
    public function info(){
        $task_id=intval($this->_get("task_id"));
        $task=new Task();
        $project=new Outsourcing();
        $task_row=$project->get_row($task_id,Company::login_user_id());
        if(!$task_row){
            $this->error("系统错误",U('user/index'));
        }
        //职位类别
        $occupation_type=$task->occupation_type();
        $task_row["occupation_type"]=$occupation_type[$task_row["occupation_id"]]["name"].",".$occupation_type[$task_row["occupation_id"]]["children"][$task_row["occupation_two_id"]];
        //工作地点
        $task_row["work_address"]=$this->_get_address_name($task_row["work_address_pro_cit_cou"],$task_row["work_address"]);
        //学历要求
        $education_type=$task->education_type();
        $task_row["education_type"]=$education_type[$task_row["education_type"]];
        //工作年限要求
        $work_year=$task->work_year();
        $task_row["work_year"]=$work_year[$task_row["work_year"]];
        //薪资范围
        $buffer=explode(",",$task_row["salary_range"]);
        $task_row["salary_range"]=$buffer[0]."-".$buffer[1]."K";
        //面试地点
        $interview_address=json_decode($task_row["interview_address"],true);
        $interview_area="#".$interview_address["province_id"]."#".$interview_address["city_id"]."#".$interview_address["country_id"]."#";
        $task_row["interview_address"]=$this->_get_address_name($interview_area,$interview_address["address"]);
        //面试结果通知时间
        $notify_time=$task->notify_time();
        $task_row["notify_time"]=$notify_time[$task_row["notify_time"]];
        //招聘方式
        $recruit_need=$task->recruit_need();
        $task_row["recruit_need"]=$recruit_need[$task_row["recruit_need"]];
        //任职要求
        $task_row["skill_need"]=array_filter(json_decode($task_row["skill_need"],true));
        layout(false);
        $this->assign("task_row",$task_row);
        $this->display();
    }

    //获取工作地点名称
    private function _get_address_name(&$province_city_country,&$address){
        $region=new Tool_Region();
        $area=array_filter(explode("#",$province_city_country));
        $area_info=$region->region($area[0],$area[1],$area[2]);
        return $area_info." ".$address;
    }
}
