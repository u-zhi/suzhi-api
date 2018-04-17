<?php
//猎头、求职者---个人操作类
class UserAction extends BaseAction {
    /*头像,姓名,电话,余额--我的
    * 
    *   @token
    *
    */
    public function info(){
        $user=new User();
        $recharge=new Recharge();
        $info=$user->get_row($this->_user_id);
        $info['amount']=$recharge->amount($this->_user_id);
        if($info){
            $this->success(array("content"=>$info));
        }else{
            $this->error("亲请正确操作哦");
        }

    }
    /*猎头-我的公司内推职位
    *
    *   @token
        @page 页数
        @page_sum 分页数
    *
    */
    public function company_staff(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $staff_user_row=$company->staff_user_row($user_id);
        $type_classify=$company->type_classify();
        if($staff_user_row){
            // 公司基本信息
            $company_info=$company->user_row($staff_user_row["company_id"]);
            $company_info['img_url']=thumb_image($company_info['logo_url'],65,65);
            $company_info['financing']=$financing[$company_info['financing']];
            $company_info['scale_type']=$scale_type[$company_info['scale_type']];
            $buffer=explode(",",$company_info['type_classify']);
            $company_info['type_classify']=$type_classify[$buffer[0]]["name"];
            // 获取公司的内推职位
            $task=new Task();
            $select=$task->_task_model->where(array("firm_id"=>$staff_user_row['company_id'],"is_deleted"=>0,"recruit_type_inner"=>2));
            //分页
            $page=intval($this->_get("page"));
            $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
            $page=$page?$page:1;
            $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
            //列表
            $task=new Task();
            $city=new Tool_Region();
            $status_type=Task::$recruit_need;
            $content=$pagenitor->content();
            $company_obj=$company->user_row($staff_user_row['company_id']);
            foreach ($content as $key => $value) {
                //状态 
                $content[$key]['status_name']=$status_type[$value['status']];
                // 职位名称
                $occupation_name=$task->get_occupation_name($value['occupation_two_id']);
                $content[$key]['occupation_two_id']=$occupation_name['item'];
                // 城市
                $content[$key]['county_id']=$city->country_name($value['county_id']);
                $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);

            }
            $pageinfo=$pagenitor->pageinfo();
            $this->success(array("content"=>$content,"pageinfo"=>$pageinfo,"page_sum"=>$page_sum,"company_info"=>$company_info));
        }else{
            $this->error("你不属于任何公司的内推员工");
        }

    }
    /*猎头消息中心--求职者消息中心--get
    *   @token
        @user_type  用户类型（0:''Headhunter''猎头,1:''JobHunter''求职者）
    */
    public function message(){
        $user_id=$this->_user_id;
        $user_type=intval($this->_get("user_type"));
        if(empty($user_id)){$this->error("请正确操作！");}
        // 列表
        $user=new User();
        $select=$user->_message_model->order("is_read")->where(array("user_id"=>$user_id,"user_type"=>$user_type))->field("content,id,create_time");
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        //标记城已读
        $id_arr=array();
        foreach($content as $value){
            $id_arr[]=$value["id"];
        }
        $id_arr=array_unique(array_filter($id_arr));
        if($id_arr){
            $user->_message_model->where(array(
                "id"=>array("in",$id_arr)
            ))->save(array("is_read"=>1));
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));

    }

    /*求职者查看简历---
    * user_id :查看的用户id
    */
    public function job_user_info(){
        // $user_id=$this->_user_id;
        $user_type=intval($this->_get("user_id"));
        if($user_type){
            $user_id=$user_type;
        }else{
            $user_id=$this->_user_id;
        }

        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $user_row=$user->get_row($user_id);
        if($user_row['avatar_url']){
            $user_row['avatar_url_thumb']=thumb_image($user_row['avatar_url'],50,50);
        }
        $verfication=$user->get_verfication($user_id);

        $degree=User::$_degree;

        $extra_info=$user->get_extra_info($user_id);

        $hightest_education=$user->hightest_education($user_id);

        $education_list=$user->education_list($user_id);

        $work_list=$user->work_list($user_id);
        foreach($work_list as $key=>$value){
            $work_list[$key]["start_time1"]=date("Y.m",strtotime($value["start_time"]));
            $work_list[$key]["end_time1"]=date("Y.m",strtotime($value["end_time"]));
        }

        $cv_job_row=$user->cv_job_row($user_id);

        $project_experience=$user->project_experience($user_id);

        $skill_tag=$user->skill_tag($user_id);

        $self_intro=$user->self_intro($user_id);

        //未读取消息数
        $is_hunter=intval($this->_get("is_hunter"));
        $wait_message=$user->_message_model->where(array(
            "user_id"=>$user_id,
            "user_type"=>$is_hunter?0:2,
            "is_read"=>0
        ))->field("count(*) as total")->find();

        $order_model=new JobhunterOrderModel();
        //我的求职消息
        $order_row=$order_model->where(array(
            "user_id"=>$user_id,
            "seeker_tip_status"=>1
        ))->field("count(*) as total")->find();
        //我的任务订单消息
        $task_row=$order_model->where(array(
            "header_id"=>$user_id,
            "hunter_tip_status"=>1,
        ))->field("count(*) as total")->find();
        $this->success(array(
            "user_row"=>$user_row,
            "verfication"=>$verfication,
            "degree"=>$degree,
            "extra_info"=>$extra_info,
            "hightest_education"=>$hightest_education,
            "education_list"=>$education_list,
            "work_list"=>$work_list,
            "cv_job_row"=>$cv_job_row,
            "project_experience"=>$project_experience,
            "skill_tag"=>$skill_tag,
            "self_intro"=>$self_intro,
            "wait_message"=>$wait_message["total"]?$wait_message["total"]:0,
            "wait_order"=>$order_row["total"]?$order_row["total"]:0,
            "wait_task"=>$task_row["total"]?$task_row["total"]:0,
            ));

    }
    /*
        求职者编辑简历---post
        头像-基本信息-
        @token
        @nickname  昵称
        @phone_number
        @avatar_url
        @gender
        @birthday
        @city_id
        @highest_degree
        @work_email
        @work_year
        @college
        @major

    */
    public function job_user_info_edit(){

         $user_id=$this->_user_id;
         $nickname=trim(strval($this->_post("nickname")));
         $phone_number=trim(intval($this->_post("phone_number")));
         $avatar_url=trim(strval($this->_post("avatar_url")));
         $gender=trim(intval($this->_post("gender")));
         $birthday=trim(strval($this->_post("birthday")));

        /*编辑自我描述*/
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        /*编辑user_profile信息表*/
        // /*jobhunter_extra_info信息编辑*/
         $city_id=trim(strval($this->_post("city_id")));
         $highest_degree=trim(strval($this->_post("highest_degree")));
         $work_email=trim(strval($this->_post("work_email")));
         $work_year=trim(strval($this->_post("work_year")));
         $college=trim(strval($this->_post("college")));
         $major=trim(strval($this->_post("major")));
        $user_row=$user->edit_row($user_id,$nickname,$phone_number,$avatar_url,$gender,$birthday);
        /*jobhunter_extra_info信息编辑*/
        $user_extra_info=$user->get_extra_info_edit($user_id,$city_id,$highest_degree,$work_email,$work_year,$college,$major);        
        if($user_row && $user_extra_info){
            $this->success(array("content"=>true));
        }else{
            $this->error("编辑基本信息失败，请重新编辑");
        }
    }
    //修改用户头像
    public function user_avatar_url(){
        $user_id=$this->_user_id;
        $image=trim(strval($this->_post("avatar_url")));
        if(!$image){
            $this->error("头像修改失败");
        }
        $user=new User();
        $user->edit_avatar_url($user_id,$image);
        $this->success(array("content"=>true));
    }
    /*
        @token
            自我描述
        @content  昵称

    */
    public function self_intro_edit(){
         $user_id=$this->_user_id;
        /*编辑自我描述*/
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        /*编辑自我描述*/
        $content=trim(strval($this->_post("content")));
        $user_self_intro=$user->self_intro_edit($user_id,$content);
        if($user_self_intro){
            $this->success(array("content"=>true));
        }else{
            $this->error("编辑自我描述失败，请重新编辑");
        }
    }
            
    /*
        期望工作-post
        @token
        @city 期望城市-----城市存##类型还是就一个城市id 待确认
        @job_type 工作类型
        @occupation 期望职位
        @wage_lower 期望工资下限（0表示无下限）
        @wage_upper 期望工资上限（0表示无上限）

    */
    public function job_row(){
         $user_id=$this->_user_id;
         $city=trim(strval($this->_post("city")));/*是要传递一个id还是多个id得后面商榷*/
         $city_id=intval($this->_post("city_id"));
         $job_type=trim(intval($this->_post("job_type")));
         $occupation=trim(strval($this->_post("occupation")));
         $wage_lower=trim(intval($this->_post("wage_lower")));
         $wage_upper=trim(intval($this->_post("wage_upper")));
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $job_row=$user->cv_job_row_edit($user_id,$city,$city_id,$job_type,$occupation,$wage_lower,$wage_upper);
        if($job_row){
            $this->success(array("content"=>true));
        }else{
            $this->error("编辑期望工作失败，请重新编辑");
        }
    }

    /*
        求职者技能标签--添加-post
        @token
        @tag
    */
    public function tag_add(){
         $user_id=$this->_user_id;
         $tag=trim(strval($this->_post("tag")));
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $tag_list=$user->skill_tag_add($user_id,$tag);
        if($tag_list){
            $this->success(array("tag_id"=>$tag_list));
        }else{
            $this->error("请重新操作！");
        }
    }
    /*
        求职者技能标签列表--列表
        @token
    */
    public function tag_list(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $tag_list=$user->skill_tag($user_id);
        $tag_list=$tag_list?$tag_list:array();
        $this->success($tag_list);
    }

     /*
        求职者技能标签删除
        @token
    */
    public function tag_del(){
        $user_id=$this->_user_id;
        $tag_id=intval($this->_post("tag_id"));
        if(!$tag_id){
            $this->error("操作有误请重新操作！");
        }
        $user=new User();
        $tag_list=$user->tag_del($tag_id,$user_id);
        if($tag_list){
            $this->success(array("content"=>true));
        }else{
            $this->error("操作有误请重新操作！");
        }
    }


    /*
        求职者工作简历添加和编辑-post
        @token
        @work_id 工作简历id
        @firm_name 公司名称
        @occupation 职位
        @start_time 开始日期
        @end_time 结束日期
        @content 工作内容介绍
    */
    public function work_experience(){
         $user_id=$this->_user_id;
         $work_id=trim(intval($this->_post("work_id")));
        $work_id=$work_id?$work_id:0;
         $firm_name=trim(strval($this->_post("firm_name")));
         $occupation=trim(strval($this->_post("occupation")));
         $start_time=trim($this->_post("start_time"));
         $end_time=trim($this->_post("end_time"));
         $content=trim(strval($this->_post("content")));
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $work_row=$user->work_list_edit($work_id,$user_id,$firm_name,$occupation,$start_time,$end_time,$content);
        if($work_row){
            $this->success(array("content"=>true));
        }else{
            $this->error("填写格式有误,请重新填写");
        }
    }
    //获取求职者工作经历
    public function work_experience_row(){
        $work_id=intval($this->_get("work_id"));
        $user_id=$this->_user_id;
        $user=new User();
        $work_row=$user->work_row($work_id,$user_id);
        if($work_row){
            $this->success($work_row);
        }else{
            $this->error("获取失败,请重新获取");
        }
    }


    /*
        求职者工作简历-删除 
        @token
        @work_id
    */
    public function work_list_del(){
         $user_id=$this->_user_id;
         $work_id=trim(intval($this->_post("work_id")));
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $work_row=$user->work_list_del($work_id,$user_id);
        if($work_row){
            $this->success(array("content"=>true));
        }else{
            $this->error("请重新操作");
        }

    }


    //发送短信
    public function sms(){
        $mobile=trim(strval($this->_post("mobile")));
        if(!$mobile){
            $this->error("缺少手机号码参数");
        }
        $type=intval($this->_post("type"));
        $user=new User();
        if($type==0){ //登录
            if(!$user->mobile_exist($mobile)){
                $this->error("手机未注册,请先注册");
            }
        }elseif($type==1){ //注册
            if($user->mobile_exist($mobile)){
                $this->error("手机已被注册");
            }
        }
        $code=new Code();
        $code->company_register_send($mobile,$type);
        $this->success(array("content"=>"短信已发送"));
    }
    //登录
    public function login(){
        $mobile=trim(strval($this->_post("mobile")));
        $code_str=trim(strval($this->_post("code")));
        if(!$mobile || !$code_str){
            $this->error("参数错误");
        }
        $code=new Code();
        $result=$code->check_code($mobile,$code_str,0);
        if($result){
            $user=new User();
            $user_row=$user->mobile_user_row($mobile);
            $user->add_id($user_row,$this->_post("user_type"));
            $token=create_token($user_row["id"]);
            $this->success(array(
                "token"=>$token,
                "user_id"=>$user_row["id"],
                "expire_time"=>time()+30*86400,
            ));
        }else{
            $this->error("验证码失效或过期");
        }
    }
    
    /*余额中心
    *
    * @token
    *
    */
    //当前余额--提现申请展示--记得余额前端不用除100哦
    public function user_balance(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}
        $balance=new Recharge();
        $money_one=$balance->balance_row($user_id);
        $money['balance']=$money_one['balance']/100;//可转出余额
        $money['frozen_amount']=$money_one['frozen_amount']/100;//冻结余额
        $money['sum_money']=$money['balance']+$money['frozen_amount'];
        $this->success(array("money"=>$money));
    }
    // 收入明细
    public function statements(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}
        $user=new User();
        $Withdraw=new Withdraw();
        $task=new Task();
        $company=new Company();
        $direction=Withdraw::$_direction;
        $select=$user->_amount_model->where(array("user_id"=>$user_id,"user_type"=>2));
        //分页
        $page=intval($this->_get("page")) ? intval($this->_get("page")) : 1 ;
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        foreach ($content as $key => $value) {
            $content[$key]['direction']=$direction[$value['direction']];
            //时间转换
            $create_time_change=strtotime($value['create_time']);
            $content[$key]['create_time']=date("Y/m/d H:i",$create_time_change);
            // // 获取是什么订单下的什么职位获得的收入

            // // 玩家的订单
            $jober_order_row=$company->_jobhunter_order_model->where(array(
            "id"=>$value['link_id'],
            ))->find();
            $task_row=$task->get_row($jober_order_row['task_id']);
            //职位类别
            $occupation_type=$task->occupation_type();
            $content[$key]["occupation_type"]=$occupation_type[$task_row["occupation_id"]]["name"].",".$occupation_type[$task_row["occupation_id"]]["children"][$task_row["occupation_two_id"]];
            //公司名称
            $company_obj=$company->user_row($task_row["firm_id"]);
            $content[$key]['logo_image']=$company_obj['logo_url'];
            $content[$key]['name']=$company_obj['name'];            

        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }   
    /* 提现记录
    * @token 
    */
    public function withdraw_order(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}
        $withdraw=new Withdraw();
        $is_approved=Withdraw::$_status;
        $select=$withdraw->_order_model->where(array("user_id"=>$user_id,"user_type"=>2));
        //分页
        $page=intval($this->_get("page")) ? intval($this->_get("page")) : 1 ;
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        foreach ($content as $key => $value) {
            $content[$key]['is_approved']=$is_approved[$value['is_approved']];
            $content[$key]['amount']=$value['amount']/100;/*金额已转换*/
            $content[$key]['user_money']=$value['user_money']/100;/*余额已转换*/
            //时间转换
            $create_time_change=strtotime($value['create_time']);
            $content[$key]['create_time']=date("Y/m/d H:i",$create_time_change);
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }


    /*提现 post--及提现的方式
      
        @token
        @amount 提现的金额
        @method_id 提现的方式
    
    */
    public function user_withdraw_order(){
        $user_id=$this->_user_id;
        $account=trim(strval($this->_post("account"))) ? trim(strval($this->_post("account"))) :"";
        $issuer=trim(intval($this->_post("issuer"))) ? trim(intval($this->_post("issuer"))) : "";
        $name=trim(strval($this->_post("name"))) ? trim(strval($this->_post("name"))) :"";
        //所属银行
        $extra_info=trim(strval($this->_post("extra_info"))) ? trim(strval($this->_post("extra_info"))) :"NULL";
        $comment=trim(strval($this->_post("comment"))) ? trim(strval($this->_post("comment"))) :"NULL";
        // 1商家  2是用户
        $user_type=trim(intval($this->_post("user_type"))) ? trim(intval($this->_post("user_type"))) :2;
        if(empty($user_id) ||empty($account)||empty($name)){
            $this->error("参数错误,请填写正确的提现方式！");
        }
        $withdraw=new Withdraw();
        $user=new User();
        $data_withdraw=array(
                "user_id"=>$user_id,
                "account"=>$account,
                "issuer"=>$issuer,
                "name"=>$name,
                "extra_info"=>$extra_info,
                "comment"=>$comment,
                "user_type"=>$user_type,
            );
        $user_withdraw_profile=$withdraw->user_withdraw_profile($user_id,$data_withdraw);
        if(!$user_withdraw_profile){
            $this->error("提现的方式出错了!");
        }
        // 提现
        $user_id=$this->_user_id;
        $method_id=$user_withdraw_profile;
        $amount=intval($this->_post("amount"))*100;
        if(($amount<5000) || empty($method_id) ||empty($user_id)){
            $this->error("参数错误");
        }
        // 用户本身的余额
        $user_money=$user->_balance_model->where(array("user_id"=>$user_id,))->field('balance,frozen_amount')->find();
        if($amount > $user_money['balance']){ $this->error("亲不要违规操作，提现金额不能大于余额!");}
        $user_balance=$withdraw->user_balance($user_id,$method_id,$amount);
        // 提现申请预先扣除用户的余额增加用户的冻结余额
        $reduce_balance=$user_money['balance']-$amount;
        $frozen_amount=$user_money['frozen_amount']+$amount;
        $user_money=$user->_balance_model->where(array("user_id"=>$user_id))->save(array('balance'=>$reduce_balance,'frozen_amount'=>$frozen_amount));
        if($user_balance){
            $this->success(array("content"=>"提现申请成功"));
        }else{
            $this->success(array("content"=>"提现申请失败"));
        }
    }
    /*
        猎头资料存入 post
        @avatar_url 用户头像URL
        @nickname 昵称
        @gender 性别（0：女，1：男）
        @birthday 生日
        @occupation_id 职位ID
        @school_id 学校ID
        @major_id 专业ID
        @highest_degree 学历
        @work_year 工作年限---缺少字段
        @city_id 所在城市---缺少字段
        @phone_number 联系电话
        @email 联系邮箱---缺少字段

    */
    public function headhunter_extra_add(){
        $user_id=$this->_user_id;
        $nickname=trim(strval($this->_post("nickname")));
        $phone_number=trim(intval($this->_post("phone_number")));
        $avatar_url=trim(strval($this->_post("avatar_url")));
        $gender=trim(intval($this->_post("gender")));
        $birthday=trim(strval($this->_post("birthday")));

        /*编辑自我描述*/
        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        /*编辑user_profile信息表*/
        // /*jobhunter_extra_info信息编辑*/
         $city_id=trim(strval($this->_post("city_id")));
         $highest_degree=trim(strval($this->_post("highest_degree")));
         $work_email=trim(strval($this->_post("work_email")));
         $work_year=trim(strval($this->_post("work_year")));
         $college=trim(strval($this->_post("college")));
         $major=trim(strval($this->_post("major")));
        $user_row=$user->edit_row($user_id,$nickname,$phone_number,$avatar_url,$gender,$birthday);
        /*jobhunter_extra_info信息编辑*/
        $user_extra_info=$user->user_extra_edit($user_id,$city_id,$highest_degree,$work_email,$work_year,$college,$major);        
        if($user_row && $user_extra_info){
            $this->success(array("content"=>true));
        }else{
            $this->error("编辑基本信息失败，请重新编辑");
        }
    }
    /*
        猎头资料获取 ---缺少一些条件等，后面补齐
        @token 

    */
    public function headhunter_extra_info(){
        // $user=new User();
        // $headhunter_info=$user->_headhunter_info_model->where(array("user_id"=>$user_id))->select();
        $user_type=intval($this->_get("user_id"));
        if($user_type){
            $user_id=$user_type;
        }else{
            $user_id=$this->_user_id;
        }

        if(empty($user_id)){$this->error("请正确操作！");}
        $user=new User();
        $user_row=$user->get_row($user_id);
        if($user_row['avatar_url']){
            $user_row['avatar_url_thumb']=thumb_image($user_row['avatar_url'],50,50);
        }
        $verfication=$user->get_verfication($user_id);

        $degree=User::$_degree;

        $extra_info=$user->get_hunterextra_info($user_id);

        $hightest_education=$user->hightest_education($user_id);

        $education_list=$user->education_list($user_id);

        $work_list=$user->work_list($user_id);

        $cv_job_row=$user->cv_job_row($user_id);

        $project_experience=$user->project_experience($user_id);

        $skill_tag=$user->skill_tag($user_id);

        $self_intro=$user->self_intro($user_id);

        $this->success(array(
            "user_row"=>$user_row,
            "verfication"=>$verfication,
            "degree"=>$degree,
            "extra_info"=>$extra_info,
            "hightest_education"=>$hightest_education,
            "education_list"=>$education_list,
            "work_list"=>$work_list,
            "cv_job_row"=>$cv_job_row,
            "project_experience"=>$project_experience,
            "skill_tag"=>$skill_tag,
            "self_intro"=>$self_intro,
            ));
    }

    /*
        求职者面试邀请页面
        $token
    */
    public function jobhunter_interview_list(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("亲请正确操作！");}

        $order=new HunterOrder();
        $select=$order->_interview_model->where(array("jobhunter_order_id"=>$user_id,"status"=>1))->order("create_time desc");
        //分页
        $page=intval($this->_get("page")) ? intval($this->_get("page")) : 1 ;
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $company=new Company();
        $content=$pagenitor->content();
        foreach ($content as $key => $value) {
            $content[$key]['company_list_info']=$company->user_row($value["company_id"]);
        }
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));

    }
    /*
        求职者被邀请面试的操作状态--post
        @token
        @id
        @status
        
    */
    public function jobhunter_status(){
        $user_id=$this->_user_id;
        $id=$this->_get("id");
        $status=$this->_get("status");
        if(empty($user_id)||empty($id)||empty($status)){$this->error("亲请正确操作！");}
        $order=new HunterOrder();
        $order_row=$order->jobhunter_status($status,$user_id);
        if($order_row){
            $this->success(array("content"=>true));
        }else{
            $this->error("反馈失败");
        }
    }
    /*
        投递简历-及判断是否有投递简历的状态
        @token
        @task_row 职位id
        @add_type 订单来源，内推，猎头，普通等 默认1
        @headhunter_task_id 猎头任务ID
        @inner_user_id 内推用户ｉｄ
    */
    public function post_task_add(){
        $user_id=$this->_user_id;
        $task_id=intval($this->_post("task_id"));
        $headhunter_task_id=intval($this->_post("headhunter_task_id"));
        $inner_user_id=intval($this->_post("inner_user_id"));
        $add_type=intval($this->_post("add_type"));
        $share_user=intval($this->_post("share_user"));
        $header_id=0;
        if(empty($user_id) || !$task_id){$this->error("亲请正确操作！");}
        //职位是否存在
        $task=new Task();
        $task_row=$task->get_row($task_id);
        if(!$task_row){
            $this->error("职位不存在");
        }
        if($task_row['status']==3){
            $this->error("该职位已经下架");
        }
        //是否已经投递过简历
        $hunterorder=new HunterOrder();
        if($hunterorder->has_join_task($user_id,$task_id)){
            $this->error("已经投递过该职位");
        }
        //根据分享id更改投递来源
        if($share_user){
            //该分享用户　对于该职位是内推还是猎头
            $company=new Company();
            $share_row=$company->staff_user_row($share_user);
            if($share_row && $share_row["company_id"]==$task_row["firm_id"]){
                $inner_user_id=$share_user;
                $headhunter_task_id=0;
                $add_type=1;
            }else{
                //该猎头领取的任务订单
                $header_order=new HeadHunterOrder();
                $order_row=$header_order->get_task_row($share_user,$task_id);
                if($order_row){
                    $headhunter_task_id=$order_row["id"];
                    $inner_user_id=0;
                    $add_type=2;
                    $header_id=$share_user;
                }
            }
        }
        $ext=array(
            "headhunter_task_id"=>$headhunter_task_id,
            "inner_user_id"=>$inner_user_id,
            "header_id"=>$header_id,
        );
        $hunter_add=$hunterorder->post_task($user_id,$task_row,$add_type,$ext);
        if($hunter_add){
            //发送消息
            $message=new Message();
            $message->post_task($hunter_add);
            $this->success(array("content"=>true));
        }else{
            $this->error("投递失败，请检查参数！");
        }
    }
    //注册
    public function register(){
        $mobile=trim(strval($this->_post("mobile")));
        $code_str=trim(strval($this->_post("code")));
        if(!$mobile || !$code_str){
            $this->error("非法访问");
        }
        $user=new User();
        if($user->mobile_exist($mobile)){
            $this->error("手机已被注册");
        }
        $code=new Code();
        $result=$code->check_code($mobile,$code_str,1);
        if($result){
            //新增用户
            $user_type=intval($this->_post("user_type"));
            $user_type=$user_type?$user_type:1;
            $user_id=$user->add_row($mobile,$user_type);
            // 判断成员是否在企业的内推
            $company=new Company();
            $company_staff=$company->_staff_model->where(array("mobile"=>$mobile))->save(array("user_id"=>$user_id));
            $token=create_token($user_id);
            $this->success(array(
                "token"=>$token,
                "user_id"=>$user_id,
                "expire_time"=>time()+30*86400,
            ));
        }else{
            $this->error("验证码失效或过期");
        }
    }

    //求职者我的求职进程
    /*
        @progress_type 1进行中 2已经结束 3问题单
        @page
        @page_sum

    */
    public function jober_progress(){
        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("请正确操作！");}
        $progress_type=intval($this->_get("progress_type"));
        $hunterorder=new HunterOrder();
        $order_progress_type=HunterOrder::$_order_progress_type;
        $order_status=HunterOrder::$_order_status;
        $task=new Task();
        $select=$hunterorder->_order_model->where(array(
            "user_id"=>$user_id,
            "current_status"=>array("in",$order_progress_type[$progress_type]),
            ));
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $user=new User();
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $type_classify=$company->type_classify();
        foreach ($content as $key => $value) {
            //进程对应订单信息
            $content[$key]["task_row"]=$task->get_row($value['task_id']);
            $company_obj=$company->user_row($value["company_id"]);
            $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[0]]["name"];
            // 状态，反馈信息
            $content[$key]["current_status"]=$order_status[$value['current_status']];
            //时间转换
            $create_time_change=strtotime($value['create_time']);
            $content[$key]['create_time']=date("Y/m/d H:i",$create_time_change);

        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }


    //顾问我的求职进程
    /*
        @progress_type 1进行中 2已经结束 3问题单
        @page
        @page_sum

    */
    public function hunter_progress(){

        $user_id=$this->_user_id;
        if(empty($user_id)){$this->error("请正确操作！");}
        $progress_type=intval($this->_get("progress_type"));
        $hunterorder=new HunterOrder();
        $order_progress_type=HunterOrder::$_order_progress_type;
        $order_status=HunterOrder::$_order_status;
        $task=new Task();
        $select=$hunterorder->_order_model->order("hunter_tip_status desc")->where(array(
            "inner_user_id|header_id"=>$user_id,
            "current_status"=>array("in",$order_progress_type[$progress_type]),
            ));
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $user=new User();
        $company=new Company();
        $financing=Company::financing();
        $scale_type=Company::scale_type();
        $type_classify=$company->type_classify();
        $status_type=Task::$recruit_need;
        $task_id=array();
        foreach ($content as $key => $value) {
            //进程对应订单信息
            $content[$key]["task_row"]=$task->get_row($value['task_id']);
            $company_obj=$company->user_row($value["company_id"]);
            $content[$key]['img_url']=thumb_image($company_obj['logo_url'],65,65);
            $content[$key]['financing']=$financing[$company_obj['financing']];
            $content[$key]['scale_type']=$scale_type[$company_obj['scale_type']];
            $buffer=explode(",",$company_obj['type_classify']);
            $content[$key]['type_classify']=$type_classify[$buffer[0]]["name"];
            // 状态，反馈信息
            $content[$key]["current_status"]=$order_status[$value['current_status']];
            $content[$key]['status_name']=$status_type[$content[$key]["task_row"]['status']];//简历邀请面试等
            //时间转换
            $create_time_change=strtotime($value['create_time']);
            $content[$key]['create_time']=date("Y/m/d H:i",$create_time_change);
            // 进程对应求职者的头像
            $user_row=$user->get_row($value['user_id']);
            $content[$key]["user_url"]=thumb_image($user_row['avatar_url'],65,65);
            $content[$key]["user_name"]=$user_row['nickname'];
            $task_id[]=$value["id"];
        }
        $task_id=array_filter(array_unique($task_id));
        if($task_id){
            $hunterorder->_order_model->where(array(
                "id"=>array("in",$task_id)
            ))->save(array(
                "hunter_tip_status"=>0
            ));
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array("content"=>$content,"pageinfo"=>$pageinfo));
    }
}