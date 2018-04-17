<?php
//职位信息--任务类-流程
class TaskAction extends BaseAction {
    //猎头求职列表  recruit_type_hunter=2
    public function hunter_lists(){
        //搜索条件 地区county_id 名称搜索name
        $country_id=intval($this->_get("country_id"));
        $recruit_need=intval($this->_get("recruit_need"));
        $occupation_id=intval($this->_get("occupation_id"));
        $education_type=intval($this->_get("education_type"));
        $work_year=intval($this->_get("work_year"));
        $name=strval($this->_get("name"));
        //更多筛选  on_time发布时间  salary工资 scale企业规模 
        $order_way=intval($this->_get("order_way")) ? intval($this->_get("order_way")) : "salary" ;
        $task=new Task();
        $task->_task_model->order("$order_way desc")->where(array(
            "status"=>2,
            "is_deleted"=>0,
            "recruit_type_hunter"=>2,
        ));
        if($country_id){
            $task->_task_model->where(array("county_id"=>$country_id));
        }
        if($education_type){
            $task->_task_model->where(array("education_type"=>$education_type));
        }
        if($work_year){
            $task->_task_model->where(array("work_year"=>$work_year));
        }
        // 到岗状态
        if($recruit_need){
            $task->_task_model->where(array("recruit_need"=>$recruit_need));
        }
        if($occupation_id){
            $task->_task_model->where(array("occupation_id"=>$occupation_id));
        }
        if($name){
            $task->_task_model->where(array("name"=>array("like","%{$name}%")));
        }
        //公司行业信息搜索
        $company=new Company();
        $type_classify=trim($this->_get("type_classify"));
        if($type_classify){
            $company_row=$company->_user_model->field("firm_id")->where(array("type_classify"=>$type_classify))->select();
            $firm_id=array(0);
            if($company_row){
                foreach($company_row as $value){
                    $firm_id[]=$value["id"];
                }
            }
            $task->_task_model->where(array("firm_id"=>array("in",$firm_id)));
        }
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($task->_task_model,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $status_type=Task::$recruit_need;
        $type_classify=$company->type_classify();
        foreach ($content as $key => $value) {
            //状态 
            $content[$key]['status_name']=$status_type[$value['recruit_need']];
            $company_obj=$company->user_row($value["firm_id"]);
            // $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['img_url']=$company_obj['logo_url'];
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[1]]["name"];
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }    
    //求职者求职列表
    public function jober_lists(){
        //搜索条件 地区county_id 名称搜索name
        $country_id=intval($this->_get("country_id"));
        $recruit_need=intval($this->_get("recruit_need"));
        $occupation_id=intval($this->_get("occupation_id"));
        $education_type=intval($this->_get("education_type"));
        $work_year=intval($this->_get("work_year"));
        $name=strval($this->_get("name"));
        //更多筛选  on_time发布时间  salary工资 scale企业规模 
        $order_way=intval($this->_get("order_way")) ? intval($this->_get("order_way")) : "salary" ;
        $topmodel=new Task();
        $topmodel->_task_model->order("create_time desc")->where(array(
            "is_top"=>1,
            "top_end"=>array("EGT",date("Y-m-d H:i:s")),
            "status"=>2,
            "is_deleted"=>0,
            "recruit_type_platform"=>2,
        ));
        $task=new Task();
        $task->_task_model->order("$order_way desc")->where(array(
            "status"=>2,
            "is_deleted"=>0,
            "recruit_type_platform"=>2,
        ));
        if($country_id){
            $task->_task_model->where(array("county_id"=>$country_id));
        }
        if($education_type){
            $task->_task_model->where(array("education_type"=>$education_type));
        }
        if($work_year){
            $task->_task_model->where(array("work_year"=>$work_year));
        }
        // 到岗状态
        if($recruit_need){
            $task->_task_model->where(array("recruit_need"=>$recruit_need));
        }
        if($occupation_id){
            $task->_task_model->where(array("occupation_id"=>$occupation_id));
        }
        if($name){
            $task->_task_model->where(array("name"=>array("like","%{$name}%")));
        }
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($task->_task_model,$page,$page_sum);
        $pagenitortop=new Tool_Pagenitor($topmodel->_task_model,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $contenttop=$pagenitortop->content();
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $type_classify=$company->type_classify();
        foreach ($content as $key => $value) {
            $company_obj=$company->user_row($value["firm_id"]);
            // $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['img_url']=$company_obj['logo_url'];
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[0]]["name"];
        }
        foreach ($contenttop as $k => $v) {
            $company_obj=$company->user_row($v["firm_id"]);
            // $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $contenttop[$k]['img_url']=$company_obj['logo_url'];
            $contenttop[$k]['financing']=$financing[$company_obj['financing']];
            $contenttop[$k]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $contenttop[$k]['type_classify']=$type_classify[$buffer[0]]["name"];
        }
        $pageinfo=$pagenitor->pageinfo();
        if(!empty($contenttop))
        {
            foreach ($contenttop as $k=>$v)
            {

                array_unshift($content,$v);
            }

        }
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }
    //获取职位类别
    public function occupation_type(){
        $task=new Task();
        $result=$task->occupation_type();
        $this->success($result);
    }
    //获取行业
    public function type_classify(){
        $company=new Company();
        $result=$company->type_classify();
        $this->success($result);
    }
    /*获取任务详情---推荐人才--领取任务--工作详情 都可复用此接口
    * 
    *@id 任务id
    *
    */
    public function task_one(){
        $task_id=intval($this->_get("id"));
        if(empty($task_id)){
            $this->error("无此任务，亲不要随意操作哦！");
        }
        $task=new Task();
        $task_row=$task->get_row($task_id);
        if(!$task_row){
            $this->error("亲请正确操作！");
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
        $task_row["salary_range"]=$buffer[0]."K-".$buffer[1]."K";
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
        // 职位所属公司信息
        $company=new Company();
        $company_obj=$company->user_row($task_row["firm_id"]);
        // $company_obj['logo_image']=thumb_image($company_obj['logo_url'],56,56);
        $company_obj['logo_image']=$company_obj['logo_url'];
        $task_row["company_info"]=$company_obj;
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $company=new Company();
        $type_classify=$company->type_classify();
        $task_row['financing']=$financing[$company_obj['financing']];
        $task_row['scale_type']=$scale_type[$company_obj['scale_type']];
        $buffer=explode(",",$company_obj['type_classify']);
        $task_row['type_classify']=$type_classify[$buffer[0]]["name"];
        $this->success($task_row);
    }
    //获取工作地点名称
    private function _get_address_name(&$province_city_country,&$address){
        $region=new Tool_Region();
        $area=array_filter(explode("#",$province_city_country));
        $area_info=$region->region($area[0],$area[1],$area[2]);
        return $area_info." ".$address;
    }



    /* 顾问领取的任务
    * token ：为了获取用户id
    * id ：领取的任务id
    */
    public function head_receive(){
        $task_id=intval($this->_post("id"));
        $user_id=$this->_user_id;
        $headhunter=new HeadHunterOrder();
        $receive=$headhunter->head_receive($user_id,$task_id);
        if($receive){
            $this->success(array("content"=>"领取任务成功"));
        }else{
            $this->error("已经有此任务了，操作有误");
        }  
    }


    /* 顾问取消任务
    * token ：为了获取用户id
    * id ：领取的任务id
    */
    public function head_cancle(){
        $task_id=intval($this->_post("id"));
        $user_id=$this->_user_id;
        $headhunter=new HeadHunterOrder();
        $cancle=$headhunter->head_cancle($user_id,$task_id);
        if($cancle){
            $this->success(array("content"=>"取消任务成功"));
        }else{
            $this->error("亲请正确操作哦");
        }
    }


    /*
        求职者--求职进程 -及操作
        @token
        @user_id
    */
    public function jober_task_type(){

        
    }

    /*二维码
     * @param string $text 二维码解析的地址或者内容
     * @param int $level 二维码容错级别
     * @param int $size 需要生成的图片大小
    */
    public function qrcode(){
        $text=strval($this->_get("text"));
        $level=intval($this->_get("level"));
        $size=intval($this->_get("size"));
        $qrcode_url=qrcode($text,$level,$size);
        if($qrcode_url){
            $this->success(array("qrcode_url"=>$qrcode_url));
        }else{
            $this->success(array("qrcode_url"=>array()));
        }

    }

    //求职者是否参加过某个职位
    public function has_join_task(){
        $task_id=intval($this->_get("task_id"));
        // $user_id=intval($this->_get("user_id"));
        $user_id=$this->_user_id;
        if(!$task_id){
            $this->error("系统出错");
        }
        $hunterOrder=new HunterOrder();
        if($hunterOrder->has_join_task($user_id,$task_id)){
            $this->success(array("has_join"=>1));
        }else{
            $this->success(array("has_join"=>0));
        }
    }
    //猎头是否参加过某个职位
    public function has_join_hunter_task(){
        $task_id=intval($this->_get("task_id"));
        $user_id=$this->_user_id;
        if(!$task_id){
            $this->error("系统出错");
        }
        $hunterOrder=new HunterOrder();
        if($hunterOrder->has_join_hunter_task($user_id,$task_id)){
            $this->success(array("has_join"=>1));
        }else{
            $this->success(array("has_join"=>0));
        }
    }

    //求职简历投递相关
    public function join_progress(){
        $user_id=$this->_user_id;
        $task_id=intval($this->_get("task_id"));
        if(!$task_id){
            $this->error("系统出错");
        }
        $hunterOrder=new HunterOrder();
        if(!$order_row=$hunterOrder->has_join_task($user_id,$task_id)){
            $this->error("您未参加该职位");
        }
        //标记成已读
        $hunterOrder->_order_model->where(array(
            "id"=>$order_row["id"]
        ))->save(array(
            "seeker_tip_status"=>0
        ));
        //订单面试信息
        $interview_row=$hunterOrder->interview_row($order_row["id"]);
        $interview_row["time"]=strtotime($interview_row["interview_time"]);
        //订单开工信息
        $work_row=$hunterOrder->work_row($order_row["id"]);
        //职位信息
        $task=new Task();
        $task_row=$task->get_row($task_id);
        $task_row["interview_address"]=json_decode($task_row["interview_address"],true);

        $this->success(array(
            "order_row"=>$order_row,
            "interview_row"=>$interview_row,
            "work_row"=>$work_row,
            "task_row"=>$task_row,
        ));
    }

    //企业主动发起邀面列表
    public function company_apply_interview(){
        $user_id=$this->_user_id;
        $order=new HunterOrder();
        $select=$order->_order_model->where(array(
            "user_id"=>$user_id,
            "current_status"=>2,
            "add_type"=>array("in",array(4,5))
        ));
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $type_classify=$company->type_classify();
        $task=new Task();
        foreach($content as $key=>$value){
            $company_obj=$company->user_row($value["company_id"]);
            $content[$key]['company_name']=$company_obj["name"];
            $content[$key]['img_url']=$company_obj['logo_url'];
            // $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[1]]["name"];
            $content[$key]["task_row"]=$task->get_row($value["task_id"]);
            $content[$key]["interview_row"]=$order->interview_row($value["id"]);
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }

}
