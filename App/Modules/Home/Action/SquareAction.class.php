<?php
// 人才广场
class SquareAction extends Action {

    //整体概览
    public function index(){
        $user=new Tool_User();
        $staff=new Company();
        $user_sum=$user->user_sum();// 求职者总数
        $company_job_sum=$staff->_company_job_sum(Company::login_user_id());// 线上招聘会总数
        $order_count=$staff->invitation_callback(Company::login_user_id());//邀面反馈

        $this->assign("user_sum",$user_sum);
        $this->assign("order_count",$order_count);
        $this->assign("company_job_sum",$company_job_sum);
        $this->display();
    }

    // 邀请面试 状态1.判断有没有开通邀请的服务，无则返回失败，请先开通企业服务,2.有扣除原先拥有的服务次数
    public function ajax_current_jober(){

    }

    //邀面反馈
    public function interview_reback(){
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
        $this->display();
    }

    //线上招聘会
    public function job_fair(){
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
        $this->display();
    }

    //购买招聘会摊位
    public function buy_position(){
        if($this->isPost()){
            $position_id=intval($this->_post("position_id"));
            $jobfair=new JobFair();
            if($jobfair->buy_position(Company::login_user_id(),$position_id)){
                $this->success("参与成功",U('square/job_fair'));exit;
            }else{
                $this->error("参与失败",U('square/job_fair'));
            }
        }
    }

    //获取招聘会摊位
    public function get_position(){
        $jobfair_id=intval($this->_get("jobfair_id"));
        $jobfair=new JobFair();
        echo json_encode($jobfair->get_position($jobfair_id));
    }

    //会场求职人才
    public function jobfair_user(){
        $jobfair_id=intval($this->_get("jobfair_id"));
        $jobfair=new JobFair();
        $select=$jobfair->_user_model->where(array("job_fair_id"=>$jobfair_id))->order("add_time desc");
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        $content=$pagenitor->content();

        $this->assign("content",$content);
        $this->assign("jobfair_id",$jobfair_id);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->display();
    }

    //人才市场--求职人才库能否进行邀请面试
    public function allow_apply_interview(){
        $task_id=intval($this->_get("task_id"));
        $user_id=intval($this->_get("user_id"));
        if(!$task_id || !$user_id){
            echo json_encode(array("status"=>"fail","msg"=>"系统错误"));exit;
        }
        $task=new Task();
        $result=$task->allow_apply_interview(Company::login_user_id(),$task_id,$user_id);
        if($result){
            echo json_encode(array("status"=>"success","msg"=>"允许邀请面试"));exit;
        }
        if($result===0){
            echo json_encode(array("status"=>"fail","msg"=>"您的邀面次数不足,请购买后操作"));exit;
        }else{
            echo json_encode(array("status"=>"fail","msg"=>"系统出错,请重试"));exit;
        }
    }

    //人才市场--求职人才库进行邀请面试
    public function apply_interview(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $user_id=intval($this->_post("user_id"));
            $refer=$this->_post("refer");
            if(!$task_id || !$user_id){
                $this->error("非法操作");
            }
            $task=new Task();
            $result=$task->allow_apply_interview(Company::login_user_id(),$task_id,$user_id);
            if($result){
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
            $this->error("系统出错,请重试");
        }
    }

    // 解锁简历功能
    public function apply_interview_unlock(){
        if($this->isPost()){
            $currentPage = intval($this->_post('page'));
            $task_id=intval($this->_post("task_id"));
            $user_id=intval($this->_post("user_id"));
            $refer=$this->_post("refer");
            if(!$task_id || !$user_id){
                header('Location: /index.php/square/lists?page='.$currentPage);die;
                // $this->error("非法操作");
            }
            $task=new Task();
            $result=$task->allow_apply_interview(Company::login_user_id(),$task_id,$user_id);
            if($result){
                $interview_time=trim(strval($this->_post("interview_time")));
                $interview_row=$task->apply_interview($task_id,$user_id,$interview_time);
                //记录消息
                $message=new Message();
                $message->apply_interview2($interview_row);
                if($refer)
                {
                    header('Location: /index.php/square/lists?page='.$currentPage);die;
                    // $this->success("解锁简历成功",U("square/lists",array("user_id"=>$user_id)));exit;
                }else{
                    header('Location: /index.php/square/lists?page='.$currentPage);die;
                    // $this->success("解锁简历成功");exit;
                }

            }
            header('Location: /index.php/square/lists?page='.$currentPage);die;
            // $this->error("简历已解锁");
        }
    }

    //招聘会--人才库能否进行邀请面试
    public function jobfair_allow_apply_interview(){
        $task_id=intval($this->_get("task_id"));
        $user_id=intval($this->_get("user_id"));
        if(!$task_id || !$user_id){
            echo json_encode(array("status"=>"fail","msg"=>"系统错误"));exit;
        }
        $task=new Task();
        $result=$task->jobfair_allow_apply_interview(Company::login_user_id(),$task_id,$user_id);
        if($result){
            echo json_encode(array("status"=>"success","msg"=>"允许邀请面试"));exit;
        }
        if($result===0){
            echo json_encode(array("status"=>"fail","msg"=>"您的邀面次数已用完"));exit;
        }else{
            echo json_encode(array("status"=>"fail","msg"=>"系统出错,请重试"));exit;
        }
    }

    //招聘会－－进行邀面申请
    public function jobfair_apply_interview(){
        if($this->isPost()){
            $task_id=intval($this->_post("task_id"));
            $user_id=intval($this->_post("user_id"));
            if(!$task_id || !$user_id){
                $this->error("非法操作");
            }
            $task=new Task();
            $result=$task->jobfair_allow_apply_interview(Company::login_user_id(),$task_id,$user_id);
            if($result){
                $interview_time=trim(strval($this->_post("interview_time")));
                $interview_row=$task->jobfair_apply_interview($task_id,$user_id,$interview_time);
                //记录消息
                $message=new Message();
                $message->apply_interview($interview_row);
                $this->success("邀面成功",U("square/lists"));exit;
            }
            $this->error("系统出错,请重试");
        }
    }

    // 时间转换
    public function format_date($time){
        $t=time()-$time;

        if($t/86400>30){
            return '30天前';
        }
        $f=array(
            '31536000'=>'年',
            '2592000'=>'个月',
            '604800'=>'星期',
            '86400'=>'天',
            '3600'=>'小时',
            '60'=>'分钟',
            '1'=>'秒'
        );
        if($t<=86400)
        {
            return "今天";

        }else{
            foreach ($f as $k=>$v)    {
                if (0 !=$c=floor($t/(int)$k)) {
                    return $c.$v.'前';
                }
            }
        }

    }

    /*
     * 速职人才库功能修改
     * @liangbo
     * 2017-11-6
     */
    public function next_lists(){
        $user_one=new User();
        $user=new Tool_User();
        $search=new Tool_Search();
//        $search->search();exit();
        $userid=$this->_get("user_id");
        $occupation= new OccupationModel();
        $occupation_lists=$occupation->where(array('parent_id'=>array('eq',0)))->select();
        foreach ($occupation_lists as $key => $value) {
            $occupation_child=$occupation->where(array('parent_id'=>array('eq',$value['id'])))->select();
            $occupation_lists[$key]['child_lists']=$occupation_child;
        }
        // 获取所有职位和行业
        $all_user=$user_one->_extra_info_model->field('occupation,industries')->select();
        $all_user_occupations=$all_user_industries=array();
        foreach ($all_user as $key => $value) {
            $all_user_industries[]=$value['industries'];
        }
        $this->assign('all_user_occupations',$occupation_lists);
        // echo '<pre>';
        // print_r($occupation_lists);exit;
        $this->assign('all_user_industries',array_unique(array_filter($all_user_industries)));
        //其他搜索
        $city_id=intval($this->_get("city_id"));
        $occupation=trim(strval($this->_get("occupation")));
        $highest_degree=intval($this->_get("highest_degree"));
        $degree_level=intval($this->_get("degree_level"));
        $work_year=intval($this->_get("work_year"));
        $work_year_level=intval($this->_get("work_year_level"));
        $wage=intval($this->_get("wage"));
        $wage_level=intval($this->_get("wage_level"));
        $olds=intval($this->_get("olds"));
        $olds_level=intval($this->_get("olds_level"));
        $sex=intval($this->_get("sex"));
        $industries=$this->_get("industries");
        $experience=split(' ', $this->_get("experience"));
        $keywords=split(' ', $this->_get("keyword"));

        $search_flag=false;
        //TODO城市
        if($city_id){
            $user_one->_extra_info_model->where(array(
                "city_id"=>$city_id
            ));

            $search_flag=true;
        }
        //TODO职位
        if($occupation){
            $user_one->_extra_info_model->where(array(
                "occupation"=>array("like","%".$occupation."%")
            ));
            $search_flag=true;
        }
        //TODO学历
        if($highest_degree){
            if($highest_degree==1){
                switch ($degree_level) {
                    case 0:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2,3,4,5,6))
                        ));
                        break;
                    case 1:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(2,3,4,5,6))
                        ));
                        break;
                    case 2:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(0))
                        ));
                        break;
                    case 3:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1))
                        ));
                        break;
                    default:
                        break;
                }

            }elseif($highest_degree==2){
                switch ($degree_level) {
                    case 0:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(2,3,4,5,6))
                        ));
                        break;
                    case 1:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(3,4,5,6))
                        ));
                        break;
                    case 2:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1))
                        ));
                        break;
                    case 3:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2))
                        ));
                        break;
                    default:
                        break;
                }
            }elseif($highest_degree==3){
                switch ($degree_level) {
                    case 0:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(3,4,5,6))
                        ));
                        break;
                    case 1:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(4,5,6))
                        ));
                        break;
                    case 2:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2))
                        ));
                        break;
                    case 3:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2,3))
                        ));
                        break;
                    default:
                        break;
                }
            }elseif($highest_degree==4){
                switch ($degree_level) {
                    case 0:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(4,5,6))
                        ));
                        break;
                    case 1:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(5,6))
                        ));
                        break;
                    case 2:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2,3))
                        ));
                        break;
                    case 3:
                        $user_one->_extra_info_model->where(array(
                            "highest_degree"=>array("in",array(1,2,3,4))
                        ));
                        break;
                    default:
                        break;
                }
            }
            $search_flag=true;
        }
        // 工作年限
        if($work_year){
            switch ($work_year_level) {
                case 0:
                    $user_one->_extra_info_model->where(array(
                        "work_year"=>array(array("egt",$work_year))
                    ));
                    break;
                case 1:
                    $user_one->_extra_info_model->where(array(
                        "work_year"=>array(array("gt",$work_year))
                    ));
                    break;
                case 2:
                    $user_one->_extra_info_model->where(array(
                        "work_year"=>array(array("lt",$work_year))
                    ));
                    break;
                case 3:
                    $user_one->_extra_info_model->where(array(
                        "work_year"=>array(array("elt",$work_year))
                    ));
                    break;
                default:
                    break;
            }
            $search_flag=true;
        }
        // 薪资要求
        if($wage){
            switch ($wage_level) {
                case 0:
                    $user_one->_extra_info_model->where(array(
                        "wage"=>array("egt",$wage)
                    ));
                    break;
                case 1:
                    $user_one->_extra_info_model->where(array(
                        "wage"=>array("gt",$wage)
                    ));
                    break;
                case 2:
                    $user_one->_extra_info_model->where(array(
                        "wage"=>array("lt",$wage)
                    ));
                    break;
                case 3:
                    $user_one->_extra_info_model->where(array(
                        "wage"=>array("elt",$wage)
                    ));
                    break;
                default:
                    break;
            }
            $search_flag=true;
        }
        // 年龄
        if($olds){
            $now_day=date('Y-01-01',strtotime('-'.$olds.' years'));
            switch ($olds_level) {
                case 0:
                    $user_one->_user_model->where(array(
                        "birthday"=>array("egt",$now_day)
                    ));
                    break;
                case 1:
                    $user_one->_user_model->where(array(
                        "birthday"=>array("gt",$now_day)
                    ));
                    break;
                case 2:
                    $user_one->_user_model->where(array(
                        "birthday"=>array("lt",$now_day)
                    ));
                    break;
                case 3:
                    $user_one->_user_model->where(array(
                        "birthday"=>array("elt",$now_day)
                    ));
                    break;
                default:
                    break;
            }
            $search_flag=true;
        }

        // 性别
        if($sex){
            $user_one->_user_model->where(array(
                "gender"=>array("EQ",$sex)
            ));
            $search_flag=true;
        }
        // 所属行业
        if($industries){
            $user_one->_extra_info_model->where(array(
                "industries"=>array("EQ",$industries)
            ));
            $search_flag=true;
        }
        // 工作经历
        // if($experience){
        //     foreach ($experience as $key => $value) {
        //         if($value){
        //             $user_one->_cv_work_experience_model->where(array(
        //                     "content"=>array("LIKE",$value)
        //                 ));
        //         }
        //     }
        //     $search_flag=true;
        // }
        // 关键词
        // if($keyword){
        //     foreach ($experience as $key => $value) {
        //         if($value):
        //             $user_one->_cv_work_experience_model->where(array(
        //                     "content"=>array("LIKE",$value)
        //                 ));
        //     }
        //     $search_flag=true;
        // }

        if($search_flag){
            $id_row=$user_one->_extra_info_model->field("id")->select();
            $id_arr=array(0);
            foreach($id_row as $value){
                $id_arr[]=$value["id"];
            }
            if($id_arr){
                $user->_jobhunter_user_model->where(array("id"=>array("in",$id_arr)));
            }
        }

        $city=new Tool_Region();
        $select=$user->_jobhunter_user_model->order("update_time desc")->where(array("is_seeker"=>2,"is_deleted"=>0));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $jober_list=$pagenitor->content();
//         var_dump($jober_list);exit();

        foreach ($jober_list as $key => &$value) {
            $cv_job_row=$user_one->cv_job_row($value['user_id']);
            //$jober_list[$key]["occupation"]=$cv_job_row['occupation'];
            //$jober_list[$key]["lower"]=$cv_job_row['wage_lower']."-".$cv_job_row['wage_upper'];
            $jober_list[$key]["lower"]=$value['wage_lower']."-".$value['wage_upper'];
            $user_row=$user->user_row($value['user_id']);
            $jober_list[$key]["name"]=$user_row['nickname'];
            if($value['highest_degree'] == 0){
                $value['highest_degree_name']='小学';
            }elseif ($value['highest_degree'] == 1){
                $value['highest_degree_name']='初中';
            }elseif ($value['highest_degree'] == 2){
                $value['highest_degree_name']='高中';
            }elseif ($value['highest_degree'] == 3){
                $value['highest_degree_name']='大专';
            }elseif ($value['highest_degree'] == 4){
                $value['highest_degree_name']='本科学士';
            }elseif ($value['highest_degree'] == 5){
                $value['highest_degree_name']='硕士';
            }elseif ($value['highest_degree'] == 6){
                $value['highest_degree_name']='博士';
            }else{
                $value['highest_degree_name']='博士后';
            }
            if($value['city_id']!=0){
                $jober_list[$key]["city_name"]=$city->country_name($value['city_id']);
            }else{
                $jober_list[$key]["city_name"]="未填写";
            }
            if($value['update_time']){
                $value['update_time']=$this->format_date(strtotime($value['update_time']));
            }
        }

        $this->assign("pageinfo",$pagenitor->pageinfo());
        $this->assign("jober_list",$jober_list);
        $this->assign("userid",$userid);
        $task=new Task();
        // echo '<pre>';
        // print_r($jober_list);exit;
        $this->assign("education_type",$task->education_type());//学历要求
        $this->display();
    }

    /*
     * 速职人才库功能修改
     * @liangbo
     * 2017-11-6
     */
    public function lists(){
        $user_one=new User();
        $user=new Tool_User();
        $search=new Tool_Search();
        $city=new Tool_Region();
        $userid=$this->_get("user_id");
        $occupation= new OccupationModel();
        $occupation_lists=$occupation->where(array('parent_id'=>array('eq',0)))->select();
        foreach ($occupation_lists as $key => $value) {
            $occupation_child=$occupation->where(array('parent_id'=>array('eq',$value['id'])))->select();
            $occupation_lists[$key]['child_lists']=$occupation_child;
        }
        // 获取所有职位和行业
        $all_user=$user_one->_extra_info_model->field('occupation,industries')->select();
        $all_industry=M("base_industry")->select();
        $all_user_occupations=$all_user_industries=array();
        foreach ($all_industry as $key => $value) {
            $all_user_industries[]=$value['item'];
        }
        $this->assign('all_user_occupations',$occupation_lists);
        // echo '<pre>';
        // print_r($occupation_lists);exit;
        $this->assign('all_user_industries',array_unique(array_filter($all_user_industries)));
        //其他搜索
        $city_id=intval($this->_get("city_id"));

        $occupation_first=trim(strval($this->_get("occupation_first")));
        $occupation=trim(strval($this->_get("occupation")));
        $keywords=$this->_get("keyword");
        $experience=$this->_get("experience");
        $college = $this->_get('college');

        $highest_degree=intval($this->_get("highest_degree"));
        $degree_level=intval($this->_get("degree_level"));
        $work_year=intval($this->_get("work_year"));
        $work_year_level=intval($this->_get("work_year_level"));
        $wage=intval($this->_get("wage"));
        $wage_level=intval($this->_get("wage_level"));
        $olds=intval($this->_get("olds"));
        $olds_level=intval($this->_get("olds_level"));
        $sex=intval($this->_get("sex"));
        $industries=$this->_get("industries");
        $this->assign('filters', $this->_get());
        $search_flag=false;

        if($city_id){
            $search->setCityId('city_id="'.$city_id.'"');
            $search_flag=true;
        } else {
            $city_name = session('location_city');
            $city_id = $city->get_city_id($city_name);
        }
        // 职位
        $other="";
        $occ="";
        if($occupation_first)
            $occ.=$occupation_first." ";
        if($occupation)
            $occ.=$occupation;
        if($occ){
            $other.=$occ." ";
            $search->setOccupation("occupation:'".$occ."'");
            $search_flag=true;
        }
        //TODO学历
        if($highest_degree){
            if($highest_degree==1){
                switch ($degree_level) {
                    case 0:
                        $search->setHighestDegree("highest_degree_num>=1");
                        $search_flag=true;
                        break;
                    case 1:
                        $search->setHighestDegree("highest_degree_num>=2");
                        $search_flag=true;
                        break;
                    case 2:
                        $search->setHighestDegree("highest_degree_num>=0");
                        $search_flag=true;
                        break;
                    case 3:
                        $search->setHighestDegree("highest_degree_num=1");
                        $search_flag=true;
                        break;
                    default:
                        break;
                }

            }elseif($highest_degree==2){
                switch ($degree_level) {
                    case 0:
                        $search->setHighestDegree("highest_degree_num>=2");
                        $search_flag=true;
                        break;
                    case 1:
                        $search->setHighestDegree("highest_degree_num>=3");
                        $search_flag=true;
                        break;
                    case 2:
                        $search->setHighestDegree("highest_degree_num=1");
                        $search_flag=true;
                        break;
                    case 3:
                        $search->setHighestDegree("highest_degree_num>=1");
                        $search_flag=true;
                        break;
                    default:
                        break;
                }
            }elseif($highest_degree==3){
                switch ($degree_level) {
                    case 0:
                        $search->setHighestDegree("highest_degree_num>=3");
                        $search_flag=true;
                        break;
                    case 1:
                        $search->setHighestDegree("highest_degree_num>=4");
                        $search_flag=true;
                        break;
                    case 2:
                        $search->setHighestDegree("highest_degree_num=1");
                        $search_flag=true;
                        break;
                    case 3:
                        $search->setHighestDegree("highest_degree_num>=1");
                        $search_flag=true;
                        break;
                    default:
                        break;
                }
            }elseif($highest_degree==4){
                switch ($degree_level) {
                    case 0:
                        $search->setHighestDegree("highest_degree_num>=4");
                        $search_flag=true;
                        break;
                    case 1:
                        $search->setHighestDegree("highest_degree_num>=5");
                        $search_flag=true;
                        break;
                    case 2:
                        $search->setHighestDegree("highest_degree_num>=1");
                        $search_flag=true;
                        break;
                    case 3:
                        $search->setHighestDegree("highest_degree_num>=1");
                        $search_flag=true;
                        break;
                    default:
                        break;
                }
            }
        }
        // 工作年限
        if($work_year){
            switch ($work_year_level) {
                case 0:
                    $search->setWorkYear("work_year>=".$work_year);
                    $search_flag=true;
                    break;
                case 1:
                    $search->setWorkYear("work_year>".$work_year);
                    $search_flag=true;
                    break;
                case 2:
                    $search->setWorkYear("work_year<".$work_year);
                    $search_flag=true;
                    break;
                case 3:
                    $search->setWorkYear("work_year<=".$work_year);
                    $search_flag=true;
                    break;
                default:
                    break;
            }
        }
        // 薪资要求
        if($wage){
            switch ($wage_level) {
                case 0:
                    $search->setWage("expected_salary_lower>=".$wage);
                    $search_flag=true;
                    break;
                case 1:
                    $search->setWage("expected_salary_lower>".$wage);
                    $search_flag=true;
                    break;
                case 2:
                    $search->setWage("expected_salary_lower<".$wage);
                    $search_flag=true;
                    break;
                case 3:
                    $search->setWage("expected_salary_lower<=".$wage);
                    $search_flag=true;
                    break;
                default:
                    break;
            }
        }
        // 年龄
        if($olds){
            // $now_day=date('Y-01-01',strtotime('-'.$olds.' years'));
            $now_day = $olds;
            switch ($olds_level) {
                case 0:
                    $search->setOlds("age>=".$now_day);
                    break;
                case 1:
                    $search->setOlds("age>".$now_day);
                    break;
                case 2:
                    $search->setOlds("age<".$now_day);
                    break;
                case 3:
                    $search->setOlds("age<=".$now_day);
                    break;
                default:
                    break;
            }
        }

        // 性别
        if($sex){
            if($sex==1)
                $sex="男";
            else
                $sex="女";
            $search->setSex('gender="'.$sex.'"');
        }
        // 毕业院校
        if($college) {
            $search->setCollege('college='.$college);
            $search_flag=true;
        }
        // 所属行业
        if($industries)
            $other.=$industries." ";
        //关键词
        if($keywords) {
            $other.=$keywords." ";
            $search_flag=true;
        }
        // 工作经历（新版表示所在公司）
        if($experience){
            $search->setExperience("experience:'".$experience."'");
            $search_flag=true;
        }
        if($other){
            $search->setKeyword("other:'".$other."'");
        }
        if($search_flag){
            $res=$search->search();
            $id_arr=array(0);
            if($res["result"]["num"]>0)
            {
                foreach($res["result"]["items"] as $value){
                    $id_arr[]=$value["fields"]["user_id"];
                }
            }
            if($id_arr){
                $user->_jobhunter_user_model->where(array("user_id"=>array("in",$id_arr)));
            }
        }

        if($city_id) {
            $where = array("is_seeker"=>2,"is_deleted"=>0, 'city_id'=>$city_id);
        } else {
            $where = array("is_seeker"=>2,"is_deleted"=>0);
        }
        $select=$user->_jobhunter_user_model->order("id desc")->where($where);
        // print_r($select->limit(0, 10)->select());die;

        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,20);
        $jober_list=$pagenitor->content();
        // print_r($jober_list);exit();

        foreach ($jober_list as $key => &$value) {
            $cv_job_row=$user_one->cv_job_row($value['user_id']);
            //$jober_list[$key]["occupation"]=$cv_job_row['occupation'];
            //$jober_list[$key]["lower"]=$cv_job_row['wage_lower']."-".$cv_job_row['wage_upper'];
            $jober_list[$key]["lower"]=$value['wage_lower']."-".$value['wage_upper'];
            $user_row=$user->user_row($value['user_id']);
            $jober_list[$key]["name"]=$user_row['nickname'];
            $jober_list[$key]["gender"] = $user_row['gender'];
            $jober_list[$key]['birthday']=getAgeByBirthday($user_row['birthday']);
            $jober_list[$key]['phone_number']= $user_row['phone_number'];
            $jober_list[$key]['isInvited']= !!$user_one->has_apply_interview($value["user_id"], Company::login_user_id()); // 是否已经邀请过
            if($value['highest_degree'] == 0){
                $value['highest_degree_name']='小学';
            }elseif ($value['highest_degree'] == 1){
                $value['highest_degree_name']='初中';
            }elseif ($value['highest_degree'] == 2){
                $value['highest_degree_name']='高中';
            }elseif ($value['highest_degree'] == 3){
                $value['highest_degree_name']='大专';
            }elseif ($value['highest_degree'] == 4){
                $value['highest_degree_name']='本科学士';
            }elseif ($value['highest_degree'] == 5){
                $value['highest_degree_name']='硕士';
            }elseif ($value['highest_degree'] == 6){
                $value['highest_degree_name']='博士';
            }else{
                $value['highest_degree_name']='博士后';
            }
            if($value['city_id']!=0){
                $jober_list[$key]["city_name"]=$city->country_name($value['city_id']);
            }else{
                $jober_list[$key]["city_name"]="未填写";
            }
            if($value['update_time']){
                $value['update_time']=$this->format_date(strtotime($value['update_time']));
            }
        }

        $pageinfo = $pagenitor->pageinfo2();
        $this->assign("pageinfo",$pageinfo);
        $this->assign("jober_list",$jober_list);
        $this->assign("userid",$userid);
        $task=new Task();
        // echo '<pre>';
        // print_r($jober_list);exit;
        $this->assign("education_type",$task->education_type());//学历要求
        $this->display();
    }
}
