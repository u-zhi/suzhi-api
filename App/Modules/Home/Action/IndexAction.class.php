<?php
// 控制台
class IndexAction extends Action {

    //工作台
    public function index(){
//         phpinfo();exit();
        location();
        $company=new Company();
        $company_id=Company::login_user_id();
        $this->assign("receive_resume",$company->receive_resume($company_id));//收到的简历
        $this->assign("wait_interview",$company->wait_interview($company_id));//待面试
        $this->assign("interview_handle",$company->interview_handle($company_id));//面试后处理
        $this->assign("notice_number",$company->notice_number($company_id));//面试结果通知
        $this->assign("invitation_callback",$company->invitation_callback($company_id));//邀面反馈
        $this->assign("entry_sure",$company->entry_sure($company_id));//入职确定
        $user = new User();
        $message_num=$user->_message_model->where(array("user_id"=>Company::login_user_id(),"user_type"=>1,"is_read"=>0))->count();
        $this->assign("message_num",$message_num);//通知
        $this->job_fair();//线上招聘会数据挂件
        $this->getUser();//用户账户预览数据挂件
        $this->getServer();//获取服务
        $this->display();
    }
    //收到的简历
    public function resume(){
        $order=new HunterOrder();
        $this->interview_reback();
        $select=$order->_order_model->where(array(
            "company_id"=>Company::login_user_id(),
            "add_type"=>array("lt",5),
            "current_status"=>1,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
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
                    "real_name"=>$value["nickname"],
                    "gender"=>$value["gender"]==1?"男":"女",
                    "birthday"=>$value["birthday"],
                    "edu"=>$user->education_list($user_id)

                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["gender"]=$user_arr[$value["user_id"]]["gender"];
            $content[$key]["birthday"]=$user_arr[$value["user_id"]]["birthday"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
            $content[$key]["degree"]=empty($user_arr[$value["user_id"]]["edu"])?$user_arr[$value["user_id"]]["edu"][0]["degree"]:"未知";
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //收到的简历
    public function resume1(){
        $order=new HunterOrder();
        $this->interview_reback();
        $select=$order->_order_model->where(array(
            "company_id"=>Company::login_user_id(),
            "add_type"=>array("lt",5),
            "current_status"=>1,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
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
                    "real_name"=>$value["nickname"],
                    "gender"=>$value["gender"]==1?"男":"女",
                    "birthday"=>$value["birthday"],
                    "edu"=>$user->education_list($user_id)

                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["gender"]=$user_arr[$value["user_id"]]["gender"];
            $content[$key]["birthday"]=$user_arr[$value["user_id"]]["birthday"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
            $content[$key]["degree"]=empty($user_arr[$value["user_id"]]["edu"])?$user_arr[$value["user_id"]]["edu"][0]["degree"]:"未知";
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //解锁简历
    public function resume2(){
        $order=new HunterOrder();
        $this->interview_reback();
        $select=$order->_order_model->where(array(
            "company_id"=>Company::login_user_id(),
            "add_type"=>array("lt",5),
            "current_status"=>1,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
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
                    "real_name"=>$value["nickname"],
                    "gender"=>$value["gender"]==1?"男":"女",
                    "birthday"=>$value["birthday"],
                    "edu"=>$user->education_list($user_id)

                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["gender"]=$user_arr[$value["user_id"]]["gender"];
            $content[$key]["birthday"]=$user_arr[$value["user_id"]]["birthday"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
            $content[$key]["degree"]=empty($user_arr[$value["user_id"]]["edu"])?$user_arr[$value["user_id"]]["edu"][0]["degree"]:"未知";
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //面试邀请反馈
    public function resume3(){
        $order=new HunterOrder();
        $this->interview_reback();
        $select=$order->_order_model->where(array(
            "company_id"=>Company::login_user_id(),
            "add_type"=>array("lt",5),
            "current_status"=>1,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
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
                    "real_name"=>$value["nickname"],
                    "gender"=>$value["gender"]==1?"男":"女",
                    "birthday"=>$value["birthday"],
                    "edu"=>$user->education_list($user_id)

                );
            }
        }
        //组合信息
        foreach($content as $key=>$value){
            $content[$key]["task_name"]=$task_arr[$value["task_id"]];
            $content[$key]["mobile"]=$user_arr[$value["user_id"]]["mobile"];
            $content[$key]["real_name"]=$user_arr[$value["user_id"]]["real_name"];
            $content[$key]["gender"]=$user_arr[$value["user_id"]]["gender"];
            $content[$key]["birthday"]=$user_arr[$value["user_id"]]["birthday"];
            $content[$key]["add_resource"]=$post_type[$value["add_type"]];
            $content[$key]["degree"]=empty($user_arr[$value["user_id"]]["edu"])?$user_arr[$value["user_id"]]["edu"][0]["degree"]:"未知";
        }
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //待面试
    public function wait_interview(){
        $order=new HunterOrder();
        $select=$order->_interview_model->where(array(
            "company_id"=>Company::login_user_id(),
            "status"=>5,
            "interview_time"=>array("gt",date("Y-m-d H:i:s"))
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
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //面试后处理
    public function interview_handle(){
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
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //面试结果通知
    public function interview_notice(){
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
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //入职确定
    public function interview_entry(){
        $order=new HunterOrder();
        $select=$order->_order_model->where(array(
            "company_id"=>Company::login_user_id(),
            "current_status"=>10,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $content=$pagenitor->content();
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
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //购买企业服务
    public function buy_server(){
        layout(false);
        $company_id=Company::login_user_id();
        $server=new Server();
        $this->assign("innerpush_list",$server->innerpush_list($company_id));
        $this->assign("interview_list",$server->interview_list($company_id));
        $this->assign("coin_list",$server->suzhicoin_list($company_id));
        $this->assign("package_list",$server->package_list($company_id));
        $this->display();
    }
    //获取投递用户简历并做简单操作
    public function userinfo(){
        $isDown = intval($this->_get('isDown')); // 1 直接下载
        $user_id=intval($this->_get("user_id"));
        if($user_id){
            $order_row=array("user_id"=>$user_id);
        }else{
            //订单信息
            $order_id=intval($this->_get("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
        }
        $this->assign("order_row",$order_row);
        //用户简历
        $user=new User();
        $user_row=$user->get_row($order_row["user_id"]);
        if($user_row['avatar_url']==Null){
            if($user_row['gender']==1){
                $user_row['avatar_url']="/images/1497267078.jpg";
            }else{
                $user_row['avatar_url']="/images/zxcvasds.png";
            }
        }
        // echo '<pre>';
        // $certificates=$user->get_extra_info($order_row["user_id"])['certificates'];
        $extra_info = $user->get_extra_info($order_row["user_id"]);
        //是否已经邀请过
        $apply_interview = $user->has_apply_interview($order_row["user_id"],Company::login_user_id());
        $this->assign('isDown', $isDown);
        $this->assign("user_row",$user_row);
        $this->assign("verfication",$user->get_verfication($order_row["user_id"]));
        $this->assign("degree",User::$_degree);
        $this->assign("extra_info", $extra_info);
        $this->assign("hightest_education",$user->hightest_education($order_row["user_id"]));
        $this->assign("education_list",$user->education_list($order_row["user_id"]));
        $this->assign("work_list",$user->work_list($order_row["user_id"]));
        $this->assign("cv_job_row", $user->cv_job_row($order_row["user_id"]));
        $this->assign("project_experience",$user->project_experience($order_row["user_id"]));
        $this->assign("skill_tag",$user->skill_tag($order_row["user_id"]));
        $this->assign("self_intro",$user->self_intro($order_row["user_id"]));
        $user_resume=$user->resume_user_info($order_row["user_id"]);
        $user_resume["work_list"]=json_decode($user_resume["work_list"],true);
        // print_r($user->cv_job_row($order_row["user_id"]));die;
        $this->assign("user_resume",$user_resume);
        $this->assign("apply_interview", $apply_interview);
        $this->assign('isInvited', !!$apply_interview);
        layout(false);
        $this->display();
    }
    //标记成不合适(不邀请来面试)
    public function deny_interview(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //标记成不合适
            $order=new HunterOrder();
            if($order->deny_interview($order_row)){
                //记录消息
                $message=new Message();
                $message->apply_interview($order_row);
                $this->success("标记成功",U("index/resume"));exit;
            }else{
                $this->error("系统错误",U("index/resume"));
            }
        }
    }
    //邀请面试
    public function pass_interview(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $interview_time=trim($this->_post("interview_time"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //邀请面试
            if($interview_row=$order->pass_interview($order_row,$interview_time)){
                //记录消息
                $message=new Message();
                $message->apply_interview($interview_row);

                $this->success("已邀请参加面试",U("index/resume"));exit;
            }else{
                $this->error("系统错误",U("index/resume"));
            }
        }
    }
    //佣金是否足够支付
    public function commission_enough_pay(){
        $order_id=intval($this->_get("order_id"));
        $task=new Task();
        $result=$task->commission_enough_pay($order_id);
        echo $result?1:0;
    }
    //确认已面试
    public function has_interview(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //标记成已面试
            if($order->has_interview($order_row)){
                $this->success("已参加面试",U("index/interview_handle"));exit;
            }else{
                $this->error("系统错误",U("index/interview_handle"));
            }
        }
    }
    //没来面试
    public function no_interview(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //标记成未参加面试
            if($order->no_interview($order_row)){
                $this->success("处理成功",U("index/interview_handle"));exit;
            }else{
                $this->error("系统错误",U("index/interview_handle"));
            }
        }
    }
    //面试结果通知
    public function notice_interview(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //面试结果他通知
            $data=$this->_post();
            if($order->notice_interview($order_row,$data)){
                $this->success("处理成功",U("index/interview_notice"));exit;
            }else{
                $this->error("系统错误",U("index/interview_notice"));
            }
        }
    }
    //未入职
    public function no_work(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //标记成未入职
            if($order->no_work($order_row)){
                $this->success("处理成功",U("index/interview_handle"));exit;
            }else{
                $this->error("系统错误",U("index/interview_handle"));
            }
        }
    }
    //已入职
    public function has_work(){
        if($this->isPost()){
            $order_id=intval($this->_post("order_id"));
            $order=new HunterOrder();
            $order_row=$order->get_row($order_id,Company::login_user_id());
            if(!$order_row){
                $this->error("系统错误",U("index/index"));
            }
            //已入职
            if($order->has_work($order_row)){
                $this->success("处理成功",U("index/resume"));exit;
            }else{
                $this->error("系统错误",U("index/resume"));
            }
        }
    }

    //直接发起-----邀请面试
    public function apply_interview(){
        $user_id=intval($this->_get("user_id"));
        $refer=$this->_get("refer");
        $user=new User();
        $user_row=$user->get_row($user_id);
        if(!$user_row){
            $this->error("系统错误");exit;
        }
        //目前在线职位列表
        $task=new Task();
        $select=$task->_task_model->order("create_time desc")->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>2,
            "is_deleted"=>0,
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,100);
        //列表
        $content=$pagenitor->content();
        $hunterOrder=new HunterOrder();
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
            //能否邀请面试
            if($hunterOrder->has_join_task($user_id,$value["id"])){
                $content[$key]["allow_apply_interview"]=false;
            }else{
                $content[$key]["allow_apply_interview"]=true;
            }
        }

        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->assign("nofirst",intval($this->_get("nofirst")));
        $this->assign("user_row",$user_row);
        $this->assign("refer",$refer);
        layout(false);
        $this->display();

    }
    //消息
    public function message(){
        $user = new User();
        $select=$user->_message_model->order("id desc")->where(array("user_id"=>Company::login_user_id(),"user_type"=>1));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }
    //标记成已经读取
    public function message_read(){
        if($this->isPost()){
            $id=intval($this->_post("id"));
            $message=new Message();
            $message->read_message($id);
        }
    }
    //招聘会直接发起-----邀请面试
    public function jobfair_apply_interview(){
        $user_id=intval($this->_get("user_id"));
        $user=new User();
        $user_row=$user->get_row($user_id);
        if(!$user_row){
            $this->error("系统错误");exit;
        }
        //目前在线职位列表
        $job_fair_id=intval($this->_get("job_fair_id"));
        $task=new Task();
        $select=$task->_task_model->order("create_time desc")->where(array(
            "firm_id"=>Company::login_user_id(),
            "status"=>2,
            "is_deleted"=>0,
            "job_fair_id"=>$job_fair_id
        ));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        //列表
        $content=$pagenitor->content();
        $hunterOrder=new HunterOrder();
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
            //能否邀请面试
            if($hunterOrder->has_join_task($user_id,$value["id"])){
                $content[$key]["allow_apply_interview"]=false;
            }else{
                $content[$key]["allow_apply_interview"]=true;
            }
        }

        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->assign("nofirst",intval($this->_get("nofirst")));
        $this->assign("user_row",$user_row);
        layout(false);
        $this->display();
    }
    public function exnew()
    {
        session("old_layout",null);
        $this->redirect("/index/index");

    }
    public function exold()
    {
        session("old_layout","old_layout");
        $this->redirect("/index/index");

    }
    //线上招聘会
    protected function job_fair(){
        $jobfair=new JobFair();
        $select=$jobfair->_jobfair_model->order("begin_time desc");
        //搜索
        $province_id=intval($this->_get("province_id"));
        $this->assign("province_id",$province_id);
        $city_id=intval($this->_get("city_id"));
        $this->assign("city_id",$city_id);
        if($city_id){
            $select->where(array("city_id"=>$city_id));
        }elseif($province_id){
            $region=new Tool_Region();
            $city_id_arr=$region->city_id($province_id);
            $select->where(array("city_id"=>array("in",$city_id_arr)));
        }
        $name=trim(strval($this->_get("name")));
        $this->assign("name",$name);
        if($name){
            $select->where(array("name"=>array("like","%{$name}%")));
        }
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        $content=$pagenitor->content();
        foreach ($content as $key=>$value){
            //参会求职者
            $content[$key]["join_user"]=$jobfair->join_user_number($value["id"]);
            //参会企业
            $content[$key]["join_company"]=$jobfair->join_company_number($value["id"]);
            //基础摊位费
            $content[$key]["base_money"]=$jobfair->base_money($value["id"]);
            //基础摊位邀面次数
            $content[$key]["interview_number"]=$jobfair->interview_number($value["id"]);
            //是否已经参加
            $content[$key]["has_join"]=$jobfair->has_join($value["id"],Company::login_user_id());
            //能否发布职位
            $content[$key]["allow_post"]=$jobfair->jobfair_post_task(Company::login_user_id(),$value["id"]);
        }
        $company_id=Company::login_user_id();
        $server=new Server();
        $this->assign("suzhicoin",$server->suzhicoin_total($company_id));
        $suzhicoin_list=$server->suzhicoin_list($company_id);
        $this->assign("suzhicoin_list",$suzhicoin_list);
        $this->assign("content",$content);
        $this->assign("pageinfo",$pagenitor->pageinfo());
    }

    /**
     *获得当前用户的基础信息
     */
    protected function getUser()
    {
        $company=new Company();
        $company_row=$company->user_row(Company::login_user_id());
        $this->assign("company_row",$company_row);

    }

    /**
     *获取服务状态
     */
    protected function getServer()
    {
        $server=new Server();
        $company_id=Company::login_user_id();
        $this->assign("innerpush",$server->innerpush_total($company_id));
        $this->assign("interview",$server->interview_total($company_id));
        $this->assign("suzhicoin",$server->suzhicoin_total($company_id));
        $innerpush_list=$server->innerpush_list($company_id);
        $this->assign("innerpush_list",$innerpush_list);
        //内推升级可抵扣费用
        $discount=0;
        foreach($innerpush_list as $value){
            if($value["discount_money"]>0){
                $discount=$value["discount_money"];break;
            }
        }
        $this->assign("discount",$discount);
        $this->assign("interview_list",$server->interview_list($company_id));
        $this->assign("suzhicoin_list",$server->suzhicoin_list($company_id));

    }
    //邀面反馈
    private function interview_reback(){
        $order=new HunterOrder();
        $select=$order->_interview_model->where(array(
                "company_id"=>Company::login_user_id(),
                "status"=>array("egt",4))
        );
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        $interview_list=$pagenitor->content();
        $interview=new Company();
        $user=new Tool_User();
        $user_one=new User();
        $city=new Tool_Region();
        $order_add_type=HunterOrder::$_post_type;
        foreach ($interview_list as $key => $value) {
            $order_row=$order->get_row($value["jobhunter_order_id"]);
            $user_row=$user->user_row($order_row['user_id']);
            $cv_job_row=$user_one->cv_job_row($order_row['user_id']);
            $interview_list[$key]["occupation"]=$cv_job_row['occupation'];
            $interview_list[$key]["lower"]=$cv_job_row['wage_lower']."-".$cv_job_row['wage_upper'];
            $interview_list[$key]["nickname"]=$user_row['nickname'];
            $user_info_row=$user->user_info_row($order_row['user_id']);
            $interview_list[$key]["work_year"]=$user_info_row['work_year'];
            if($user_info_row['city_id']!=0){
                $interview_list[$key]["city_name"]=$city->country_name($user_info_row['city_id']);
            }else{
                $interview_list[$key]["city_name"]="未填写";
            }
            $interview_list[$key]['current_status_name']=$interview->current_status_name($value['status']);

            $interview_list[$key]["add_type_name"]=$order_add_type[$order_row['add_type']];
            $interview_list[$key]["id"]=$order_row["id"];
        }
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->assign("interview_list",$interview_list);
    }



}
