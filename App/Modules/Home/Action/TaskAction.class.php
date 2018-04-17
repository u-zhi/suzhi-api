<?php
// 招聘职位
class TaskAction extends Action {
    //整体概览
    public function overview(){
        $task=new Task();
        $company_id=Company::login_user_id();
        $this->assign("publish_number",$task->publish_number($company_id));
        $this->assign("close_number",$task->close_number($company_id));
        $this->assign("no_success_number",$task->no_success_number($company_id));
        $this->display();
    }
    //发布职位
    public function add(){
        $tab=intval($this->_get("tab"));
        if($tab>1 and $tab<5)
        {
            $this->task_list($tab);
        }
        if($tab==5)
        {
            $this->interview_handle();
            $this->interview_notice();
        }
        if($this->isPost()){
            $data=$this->_post();
            $data['work_address_pro_cit_cou']="#".$data["work_address_province_id"]."#".$data["work_address_city_id"]."#".$data["work_address_country_id"]."#";
            $data["salary_range"]=$data["salary_min"].",".$data["salary_max"];
            $data["salary"]=$data["salary_max"];
            $data["skill_need"]=json_encode($data["skill_need"]);
            $data["interview_address"]=json_encode(array(
                "province_id"=>$data["interview_address_province_id"],
                "city_id"=>$data["interview_address_city_id"],
                "country_id"=>$data["interview_address_country_id"],
                "address"=>$data["interview_address"],
            ));
            $data["interview_time"]=json_encode($data["interview_time"]);
            $data["commission"]=$data["commission"]?$data["commission"]:0;
            $data["person_demand"]=$data["person_demand"]?$data["person_demand"]:1;
            if(in_array(1,$data["recruit_type"])){ //求职顾问
                $data['recruit_type_hunter']=2;
            }
            if(in_array(2,$data["recruit_type"])){ //企业内推
                $data['recruit_type_inner']=2;
            }
            if(in_array(3,$data["recruit_type"])){ //速职平台
                $data['recruit_type_platform']=2;
            }
            $data["commission_type"]=1;
            //有企业内推或者求职顾问
            if(isset($data["recruit_type_inner"]) || isset($data["recruit_type_hunter"])){
                if(!isset($data["recruit_type_hunter"]) && $data["pay_type"]==1){ //企业内推而且现金支付
                    //不做处理
                    $data["commission_type"]=1;
                }else{
                    //需要扣除金额
                    $data["commission_type"]=2;
                }
            }
            $task=new Task();
            if($task_id=$task->add(Company::login_user_id(),$data)){
                if($data["commission_type"]==1){ //无需支付押金,直接处理成功
                    $task->pay_deposit($task_id,Company::login_user_id());
                }
                session("?readd_work") && session("readd_work",null);
                exit("<script>location.href='/index.php/task/add?tab=2'</script>");
            }else{
                exit("<script>location.href='/index.php/task/add?tab=1'</script>");
            }
        }else{
            $task=new Task();
            $this->assign("occupation_type",$task->occupation_type());//职位类别
            $this->assign("education_type",$task->education_type());//学历要求
            $this->assign("work_year",$task->work_year());//工作年限要求
            $this->assign("notify_time",$task->notify_time());//面试结果通知时间
            $this->assign("recruit_need",$task->recruit_need());//招聘需求
            //是否开通了内推职位
            $server=new Server();
            $this->assign("has_open_server",$server->innerpush_total(Company::login_user_id()));
            if(!$this->_get("temp")){
                session("?readd_work") && session("readd_work",null);
            }
            $this->assign('tabType', $tab);
            $this->assign("readd_work",session("readd_work")?session("readd_work"):array());
            $this->display();
        }
    }
    //发布职位支付押金
    public function pay_deposit(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            if($task->pay_deposit($task_id,Company::login_user_id())){
                $this->success("职位发布成功",U("task/lists"));exit;
            }else{
                $this->error("职位支付失败",U("task/lists"));
            }
        }
    }
    //修改职位
    public function edit(){
        $task_id=intval($this->_get("task_id"));
        $company_id=Company::login_user_id();
        $task=new Task();
        $task_row=$task->get_row($task_id,$company_id);
        if(!is_array($task_row)){
            $this->error("职位不存在");
        }
        if($this->isPost()){
            $data=$this->_post();
            $data['work_address_pro_cit_cou']="#".$data["work_address_province_id"]."#".$data["work_address_city_id"]."#".$data["work_address_country_id"]."#";
            $data["salary_range"]=$data["salary_min"].",".$data["salary_max"];
            $data["salary"]=$data["salary_max"];
            $data["skill_need"]=json_encode($data["skill_need"]);
            $data["interview_address"]=json_encode(array(
                "province_id"=>$data["interview_address_province_id"],
                "city_id"=>$data["interview_address_city_id"],
                "country_id"=>$data["interview_address_country_id"],
                "address"=>$data["interview_address"],
            ));
            $data["interview_time"]=json_encode($data["interview_time"]);
            $task->edit($task_id,$data);
            $this->success("修改成功",U('task/lists'));exit;
        }
        $this->assign("task_row",$task_row);
        $this->assign("occupation_type",$task->occupation_type());//职位类别
        $this->assign("education_type",$task->education_type());//学历要求
        $this->assign("work_year",$task->work_year());//工作年限要求
        $this->assign("notify_time",$task->notify_time());//面试结果通知时间
        $this->assign("recruit_need",$task->recruit_need());//招聘需求
        $this->display();
    }
    //重新发布职位
    public function readd(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $company_id=Company::login_user_id();
            $task=new Task();
            $task_row=$task->get_row($task_id,$company_id);
            if(!is_array($task_row)){
                $this->error("职位不存在");
            }
            session("readd_work",$task_row);
            $this->redirect("task/add",array("temp"=>1));
        }
    }
    //职位列表
    public function lists(){
        $task=new Task();
        $select=$task->_task_model->order('is_top desc,create_time desc')->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>2,
            "is_deleted"=>0,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        foreach($content as $key=>$value){
            //模式
            $recruit_type=array();
            if($value["recruit_type_platform"]==2){
                $recruit_type[]="速职平台";
            }
            if($value["recruit_type_inner"]==2){
                $recruit_type[]="内推";
            }
            if($value["recruit_type_hunter"]==2){
                $recruit_type[]="顾问";
            }
            $content[$key]["recruit_type"]=implode(",",$recruit_type);
            //收到的简历
            $content[$key]["order_number"]=$task->task_order_number($value["id"]);
            //面试人数
            $content[$key]["interview_number"]=$task->task_interview_number($value["id"]);
        }
        $company_id=Company::login_user_id();
        $server=new Server();
        $this->assign("suzhicoin",$server->suzhicoin_total($company_id));
        $suzhicoin_list=$server->suzhicoin_list($company_id);
        $this->assign("suzhicoin_list",$suzhicoin_list);
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //职位列表
    private function task_list($type){
        $task=new Task();
        $select=$task->_task_model->order('is_top desc,create_time desc')->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>2,
            "is_deleted"=>0,
        ));
        if($type==2)
        {
            $select=$select->where(array("recruit_type_platform"=>2));
        }
        if($type==4)
        {
            $select=$select->where(array("recruit_type_inner"=>2));
        }
        if($type==3)
        {
            $select=$select->where(array("recruit_type_hunter"=>2));
        }
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        foreach($content as $key=>$value){
            //模式
            $recruit_type=array();
            if($value["recruit_type_platform"]==2){
                $recruit_type[]="速职平台";//2
            }
            if($value["recruit_type_inner"]==2){
                $recruit_type[]="内推";//4
            }
            if($value["recruit_type_hunter"]==2){
                $recruit_type[]="悬赏招聘";//3
            }
            $content[$key]["recruit_type"]=implode(",",$recruit_type);
            //收到的简历
            $content[$key]["order_number"]=$task->task_order_number($value["id"]);
            //面试人数
            $content[$key]["interview_number"]=$task->task_interview_number($value["id"]);
        }
        $company_id=Company::login_user_id();
        $server=new Server();
        $this->assign("suzhicoin",$server->suzhicoin_total($company_id));
        $suzhicoin_list=$server->suzhicoin_list($company_id);
        $this->assign("suzhicoin_list",$suzhicoin_list);
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
    }
    //职位详情
    public function info(){
        $task_id=intval($this->_get("task_id"));
        $task=new Task();
        $task_row=$task->get_row($task_id,Company::login_user_id());
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
    //预览的职位详情
    public function test_info(){
        $data=$this->_post("data");
        parse_str(urldecode(str_replace("amp;","",$data)),$data);
        $task=new Task();
        $task_row=$data;
        layout(false);
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
    //关闭的职位
    public function close(){
        $task=new Task();
        $select=$task->_task_model->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>3,
            "is_deleted"=>0,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        foreach($content as $key=>$value){
            //模式
            $recruit_type=array();
            if($value["recruit_type_platform"]==2){
                $recruit_type[]="速职平台";
            }
            if($value["recruit_type_inner"]==2){
                $recruit_type[]="内推";
            }
            if($value["recruit_type_hunter"]==2){
                $recruit_type[]="顾问";
            }
            $content[$key]["recruit_type"]=implode(",",$recruit_type);
            //收到的简历
            $content[$key]["order_number"]=$task->task_order_number($value["id"]);
            //面试人数
            $content[$key]["interview_number"]=$task->task_interview_number($value["id"]);
            //可退佣金
            $content[$key]["reback_money"]=$task->allow_reback_money($value);
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //未成功发布的职位
    public function no_success(){
        $task=new Task();
        $select=$task->_task_model->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>1,
            "is_deleted"=>0,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        foreach($content as $key=>$value){
            //模式
            $recruit_type=array();
            if($value["recruit_type_platform"]==2){
                $recruit_type[]="速职平台";
            }
            if($value["recruit_type_inner"]==2){
                $recruit_type[]="内推";
            }
            if($value["recruit_type_hunter"]==2){
                $recruit_type[]="顾问";
            }
            $content[$key]["recruit_type"]=implode(",",$recruit_type);
            //收到的简历
            $content[$key]["order_number"]=$task->task_order_number($value["id"]);
            //面试人数
            $content[$key]["interview_number"]=$task->task_interview_number($value["id"]);
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //关闭职位
    public function close_task(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            $task_row=$task->get_row($task_id,Company::login_user_id());
            if(!$task_row){
                $this->error("系统错误",U('user/index'));
            }
            $task->close_task($task_row);
            if($backurl=$this->_get("backurl")){
                $this->success("关闭成功",U("innerpush/task"));
            }else{
                $this->success("关闭成功",U('task/lists'));
            }
        }
    }
    //关闭后退佣金
    public function reback_money(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            if($task->reback_money($task_id)){
                $this->success("退佣成功,佣金已进入余额",U("task/close"));
            }
        }
    }
    //删除职位
    public function delete(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            $task_row=$task->get_row($task_id,Company::login_user_id());
            if(!$task_row){
                $this->error("系统错误",U('user/index'));
            }
            $task->edit($task_id,array("is_deleted"=>1));
            $this->success("删除成功",U('task/no_success'));
        }
    }
    //开启职位
    public function open_task(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            $task_row=$task->get_row($task_id,Company::login_user_id());
            if(!$task_row){
                $this->error("系统错误",U('user/index'));
            }
            $task->edit($task_id,array("status"=>2));
            $this->success("开启成功",U('task/close'));
        }
    }

    //获取工作地点名称
    private function _get_address_name(&$province_city_country,&$address){
        $region=new Tool_Region();
        $area=array_filter(explode("#",$province_city_country));
        $area_info=$region->region($area[0],$area[1],$area[2]);
        return $area_info." ".$address;
    }

    //发布招聘会职位
    public function jobfair_add(){
        $jobfair_id=intval($this->_get("jobfair_id"));
        //能否进行职位发布
        $jobfair=new JobFair();
        if(!$jobfair->jobfair_post_task(Company::login_user_id(),$jobfair_id)){
            $this->error("系统错误",U('user/index'));
        }
        if($this->isPost()){
            $data=$this->_post();
            $data['work_address_pro_cit_cou']="#".$data["work_address_province_id"]."#".$data["work_address_city_id"]."#".$data["work_address_country_id"]."#";
            $data["salary_range"]=$data["salary_min"].",".$data["salary_max"];
            $data["salary"]=$data["salary_max"];
            $data["skill_need"]=json_encode($data["skill_need"]);
            $data["interview_address"]=json_encode(array(
                "province_id"=>$data["interview_address_province_id"],
                "city_id"=>$data["interview_address_city_id"],
                "country_id"=>$data["interview_address_country_id"],
                "address"=>$data["interview_address"],
            ));
            $data["interview_time"]=json_encode($data["interview_time"]);
            $data["commission"]=$data["commission"]?$data["commission"]:0;
            $data["person_demand"]=$data["person_demand"]?$data["person_demand"]:1;
            $data["job_fair_id"]=$jobfair_id;
            //存储招聘会相关信息
            $join_row=$jobfair->company_position(Company::login_user_id(),$jobfair_id);
            $data["job_fair_money"]=$join_row["money"];
            $data["job_fair_position"]=$join_row["job_fair_position_id"];
            $data["recruit_type_platform"]=2;
            $data["commission_type"]=1;
            $task=new Task();
            if($task_id=$task->add(Company::login_user_id(),$data)){
                $task->pay_deposit($task_id,Company::login_user_id());
                session("?readd_work") && session("readd_work",null);
                echo $task_id;exit;
            }else{
                echo 0;exit;
            }
        }else{
            $task=new Task();
            $this->assign("occupation_type",$task->occupation_type());//职位类别
            $this->assign("education_type",$task->education_type());//学历要求
            $this->assign("work_year",$task->work_year());//工作年限要求
            $this->assign("notify_time",$task->notify_time());//面试结果通知时间
            $this->assign("recruit_need",$task->recruit_need());//招聘需求
            $this->assign("readd_work",array());
            $this->display();
        }
    }
    public function top(){
        if($this->isPost()){
            $top_data=explode("@",$this->_post("top_data"));
            if(count($top_data)<2)
            {
                $this->error("置顶选项不能为空",U('user/index'));
            }
            $task_id=intval($this->_post("task_id"));
            $task=new Task();
            $task_row=$task->get_row($task_id,Company::login_user_id());
            if(!$task_row){
                $this->error("系统错误",U('user/index'));
            }
            $top_time=date('Y-m-d H:i:s',strtotime('+'.$top_data[0].' day'));
            $task->edit($task_id,array("is_top"=>1,"top_end"=>$top_time));
            // 置顶成功,扣除对应速职币
            $server=new Server();
            $server->suzhicoin_plus_number($task_row["firm_id"],$top_data[1]);
            $this->success("置顶成功",U('task/lists'));
        }
    }
    private function interview_handle(){
        $order=new HunterOrder();
        $select=$order->_interview_model->where(array(
            "company_id"=>Company::login_user_id(),
            "status"=>5,
            "interview_time"=>array("elt",date("Y-m-d H:i:s"))
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
        $order->interview_to_order($content);
        //任务信息
        $task_id=array();
        $task_arr=array();
        //用户信息
        $user_id=array();
        $user_arr=array();
        //投递来源
        $post_type=HunterOrder::$_post_type;
        foreach($content as $value){
            $task_id[]=$value["task_id"];
            $user_id[]=$value["user_id"];
        }
        if($task_id){
            $task_id=array_unique($task_id);
            $task=new Task();
            $task_row=$task->get_by_ids($task_id,"name,id");
            foreach($task_row as $value){
                $task_arr[$value["id"]]=$value["name"];
            }
        }
        if($user_id){
            $user_id=array_unique($user_id);
            $user=new User();
            $user_row=$user->get_by_ids($user_id,"id,nickname,phone_number");
            foreach($user_row as $value){
                $user_arr[$value["id"]]=array(
                    "mobile"=>$value["phone_number"],
                    "real_name"=>$value["nickname"]
                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
        }
        $this->assign("content_first",$content);
        $this->assign("pageinfo_first",$pagenitor->pageinfo());
    }

    private function interview_notice(){
        $order=new HunterOrder();
        $select=$order->_interview_model->where(array(
            "company_id"=>Company::login_user_id(),
            "status"=>6,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
        $order->interview_to_order($content);
        //任务信息
        $task_id=array();
        $task_arr=array();
        //用户信息
        $user_id=array();
        $user_arr=array();
        //投递来源
        $post_type=HunterOrder::$_post_type;
        foreach($content as $value){
            $task_id[]=$value["task_id"];
            $user_id[]=$value["user_id"];
        }
        if($task_id){
            $task_id=array_unique($task_id);
            $task=new Task();
            $task_row=$task->get_by_ids($task_id,"name,id");
            foreach($task_row as $value){
                $task_arr[$value["id"]]=$value["name"];
            }
        }
        if($user_id){
            $user_id=array_unique($user_id);
            $user=new User();
            $user_row=$user->get_by_ids($user_id,"id,nickname,phone_number");
            foreach($user_row as $value){
                $user_arr[$value["id"]]=array(
                    "mobile"=>$value["phone_number"],
                    "real_name"=>$value["nickname"]
                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
        }
        $this->assign("content_second",$content);
        $this->assign("pageinfo_second",$pagenitor->pageinfo());
    }
}
