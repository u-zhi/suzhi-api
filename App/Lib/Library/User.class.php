<?php
/*
 * 用户类
 */
class User{
    public $_user_model=null;//用户表
    public $_amount_model=null;//用户金额变动
    public $_verfication_model=null;//实名认证表
    public $_extra_info_model=null;//求职其他信息
    public $_education_model=null;//求职教育经历
    public $_work_experience_model=null;//求职工作经历
    public $_cv_job_model=null;//求职意向表-期望工作
    public $_project_experience_model=null;//求职项目经历
    public $_skill_tag_model=null;//技能标签列表
    public $_skill_tag_rel_model=null;//求职拥有技能标签
    public $_self_intro_model=null;//求职自我描述
    public $_headhunter_info_model=null;//个人信息猎头补充信息
    public $_message_model=null;//消息中心
    public $_banner_model=null;//轮播
    public $_balance_model=null;//用户余额
    public $_flow_model=null;//资金流水
    public $_resume_model=null;//用户基础信息(导入简历用户)

    //学历
    public static $_degree=array(
        0=>"小学",
        1=>"初中",
        2=>"高中",
        3=>"大专",
        4=>"本科学士",
        5=>"硕士",
        6=>"博士",
        7=>"博士后",
    );    
    //学历
    public static $occupation_type=array(
        0=>"兼职",
        1=>"全职",
        2=>"实习",
    );
    //资金流水类型
    public static $_flow_type=array(
        1=>"求职订单",
    );

    public function __construct(){
        $this->_user_model=new UserModel();
        $this->_amount_model=new BalanceStatementsModel();
        $this->_verfication_model=new VerficationModel();
        $this->_extra_info_model=new JobhunterExtraInfoModel();
        $this->_education_model=new EducationModel();
        $this->_work_experience_model=new WorkExperienceModel();
        $this->_cv_job_model=new CvJobIntentionModel();
        $this->_project_experience_model=new ProjectExperienceModel();
        $this->_skill_tag_model=new SkillTagModel();
        $this->_skill_tag_rel_model=new SkillTagRelModel();
        $this->_self_intro_model=new SelfIntroModel();
        $this->_headhunter_info_model=new HeadhunterInfoModel();
        $this->_message_model=new MessageModel();
        $this->_banner_model=new FigureModel();
        $this->_balance_model=new UserBalanceModel();
        $this->_flow_model=new BalanceStatementsModel();
        $this->_resume_model=new UserResumeModel();
    }
    //获取用户信息
    public function get_row($user_id,$field=false){
        $this->_user_model->where(array("id"=>$user_id));
        if($field){
            $this->_user_model->field($field);
        }
        return $this->_user_model->find();
    }    
    //修改用户信息
    public function edit_row($user_id,$nickname,$phone_number,$avatar_url,$gender,$birthday){
        return $this->_user_model->where(array("id"=>$user_id))->save(array(
              'nickname'=>$nickname,
              //'phone_number'=>$phone_number,
              //'avatar_url'=>$avatar_url,
              'gender'=>$gender,
              'birthday'=>$birthday,
              'update_time'=>date("Y-m-d H:i:s"),
            ));
    }
    //修改用户头像
    public function edit_avatar_url($user_id,$image){
        $this->_user_model->where(array("id"=>$user_id))->save(array("avatar_url"=>$image));
    }
    //根据ｉｄ获取用户信息
    public function get_by_ids($user_ids,$field="*"){
        if(is_numeric($user_ids)){
            $user_ids=array($user_ids);
        }
        return $this->_user_model->field($field)->where(array("id"=>array("in",$user_ids)))->select();
    }
    //获取实名认证信息
    public function get_verfication($user_ids){
        if(is_numeric($user_ids)){
            $user_ids=array($user_ids);
        }
        return $this->_verfication_model->where(array(
            "user_id"=>array("in",$user_ids)
        ))->select();
    }
    //获取用户附属信息
    public function get_extra_info($user_id){
        return $this->_extra_info_model->where(array(
            "user_id"=>$user_id,
        ))->find();
    }  
    //获取猎头附属信息
    public function get_hunterextra_info($user_id){
        return $this->_headhunter_info_model->where(array(
            "user_id"=>$user_id,
        ))->find();
    }    
    //获取用户附属信息-编辑--存在编辑，不存在添加
    public function get_extra_info_edit($user_id,$city_id,$highest_degree,$work_email,$work_year,$college,$major){
        $edit_row=$this->_extra_info_model->where(array("user_id"=>$user_id))->find();
        if($edit_row){
            $edit_info=$this->_extra_info_model->where(array("user_id"=>$user_id))->save(array(
                'city_id'=>$city_id,
                'highest_degree'=>$highest_degree,
                'work_email'=>$work_email,
                'work_year'=>$work_year,
                'college'=>$college,
                'major'=>$major,
                'update_time'=>date("Y:m:d H:i:s"),
                ));
        }else{
            $edit_info=$this->_extra_info_model->add(array(
                'city_id'=>$city_id,
                'highest_degree'=>$highest_degree,
                'work_email'=>$work_email,
                'work_year'=>$work_year,
                'college'=>$college,
                'major'=>$major,
                'create_time'=>date("Y:m:d H:i:s"),
                'user_id'=>$user_id,
                ));        
        }
        return $edit_info;
    }
    //获取最高教育经历
    public function hightest_education($user_id){
        return $this->_education_model->where(array(
            "user_id"=>$user_id,
        ))->order("degree desc")->find();
    }
    //获取教育经历
    public function education_list($user_id){
        $education_list=$this->_education_model->where(array(
            "user_id"=>$user_id,
        ))->select();
        foreach ($education_list as $key => $value) {
            $education_list[$key]['degree']=self::$_degree[$value['degree']];
        }
        return $education_list;
    }
    //获取工作经历
    public function work_list($user_id){
        return $this->_work_experience_model->where(array("user_id"=>$user_id,"is_deleted"=>0))->order("end_time desc")->select();
    }    
    //编辑求职者工作经历
    public function work_list_edit($work_id,$user_id,$firm_name,$occupation,$start_time,$end_time,$content){
        $work_row=$this->_work_experience_model->find($work_id);
        if($work_row){
            $work_list_row = $this->_work_experience_model->where(array("id"=>$work_id,'user_id'=>$user_id))->save(array(
                    'firm_name'=>$firm_name,
                    'occupation'=>$occupation,
                    'start_time'=>$start_time,
                    'end_time'=>$end_time,
                    'content'=>$content,
                    'update_time'=>date("Y:m:d H:i:s"),
                ));
        }else{
            $work_list_row = $this->_work_experience_model->add(array(
                    'user_id'=>$user_id,
                    'firm_name'=>$firm_name,
                    'occupation'=>$occupation,
                    'start_time'=>$start_time,
                    'end_time'=>$end_time,
                    'content'=>$content,
                    'create_time'=>date("Y:m:d H:i:s"),
                ));
        }
        return $work_list_row;

    }
    //获取工作经历
    public function work_row($work_id,$user_id=0){
        $work_row=$this->_work_experience_model->find($work_id);
        if(!$work_row){return false;}
        if($user_id && $work_row['user_id']!=$user_id){return false;}
        return $work_row;
    }
    //删除求职者工作经历
    public function work_list_del($work_id,$user_id){
        $work_row=$this->_work_experience_model->find($work_id);
        if($work_row){
            $work_list_row = $this->_work_experience_model->where(array("user_id"=>$user_id,"id"=>$work_id))->save(array(
                'is_deleted'=>1,
                'delete_time'=>date("Y:m:d H:i:s")
                ));
        }else{
            $work_list_row=false;
        }
        return $work_list_row;

    }
    //获取期望工作
    public function cv_job_row($user_id){
        $cv_job_row=$this->_cv_job_model->where(array("user_id"=>$user_id,"is_deleted"=>0))->find();
        if(empty($cv_job_row)){return array();}
        $cv_job_row['job_type_ori']=$cv_job_row['job_type'];
        //$cv_job_row['job_type']=self::$occupation_type[$cv_job_row['job_type']];
        $company=new Company();
        $type_classifys=$company->type_classify();
        $cv_job_row['job_type']=$type_classifys[$cv_job_row['job_type']]["name"];
        return $cv_job_row;
    }   
    //编辑期望工作
    public function cv_job_row_edit($user_id,$city,$city_id,$job_type,$occupation,$wage_lower,$wage_upper){
        $cv_job_row=$this->_cv_job_model->where(array("user_id"=>$user_id))->find();
        if($cv_job_row){
            $job_row=$this->_cv_job_model->where(array("user_id"=>$user_id))->save(array(
                'city'=>$city,
                'job_type'=>$job_type,
                'occupation'=>$occupation,
                'wage_lower'=>$wage_lower,
                'wage_upper'=>$wage_upper,
                'city_id'=>$city_id,
                'update_time'=>date("Y:m:d H:i:s"),
                ));
        }else{
            $job_row=$this->_cv_job_model->add(array(
                'city'=>$city,
                'city_id'=>$city_id,
                'job_type'=>$job_type,
                'occupation'=>$occupation,
                'wage_lower'=>$wage_lower,
                'wage_upper'=>$wage_upper,
                'create_time'=>date("Y:m:d H:i:s"),
                'user_id'=>$user_id,
                ));        
        }
        //将期望职位　期望薪资　存入用户信息表做冗余设计
        $data=array(
            'occupation'=>$occupation,
            'wage_lower'=>$wage_lower,
            'wage_upper'=>$wage_upper,
        );
        $extra_row=$this->_extra_info_model->where(array("user_id"=>$user_id))->find();
        if($extra_row){
            $data["user_id"]=$user_id;
            $this->_extra_info_model->add($data);
        }else{
            $this->_extra_info_model->where(array("id"=>$extra_row["id"]))->save($data);
        }
        return $job_row;
    }
    // 获取项目经历
    public function project_experience($user_id){
        return $this->_project_experience_model->where(array("user_id"=>$user_id,"is_deleted"=>0))->select();
    }     
    // 获取技能标签
    public function skill_tag($user_id){
        $skill_tag_list=$this->_skill_tag_rel_model->where(array("user_id"=>$user_id,"is_deleted"=>0))->select();
        foreach ($skill_tag_list as $key => $value) {
            $tag=$this->_skill_tag_model->where(array("id"=>$value['tag_id'],"is_deleted"=>0))->find();   
            $skill_tag_list[$key]['tag']=$tag['tag'];
        }
        return $skill_tag_list;
    }    
    //技能标签添加
    public function skill_tag_add($user_id,$tag){

        // 1先添加sz_cv_skill_tag_list
        $tag_id=$this->_skill_tag_model->add(array(
            'tag'=>$tag,
            'create_time'=>date("Y:m:d H:i:s"),
            ));
        // 2.添加sz_cv_skill_tag_rel
        return $this->_skill_tag_rel_model->add(array(
            'user_id'=>$user_id,
            'tag_id'=>$tag_id,
            'create_time'=>date("Y:m:d H:i:s"),
            ));
    }
    // 技能标签删除
    public function tag_del($tag_id,$user_id=0){

        return $this->_skill_tag_rel_model->where(array("id"=>$tag_id,"user_id"=>$user_id,"is_deleted"=>0))->save(array(
            'is_deleted'=>1,
            'delete_time'=>date("Y:m:d H:i:s"),
            ));
    }     
    // 编辑自我描述
    public function self_intro_edit($user_id,$content){
        $self_intro=$this->_self_intro_model->where(array("user_id"=>$user_id))->find();
        if($self_intro){
            $intro_edit=$this->_self_intro_model->where(array("user_id"=>$user_id))->save(array(
                'content'=>$content,
                'update_time'=>date("Y:m:d H:i:s"),
                ));
        }else{
            $intro_edit=$this->_self_intro_model->add(array(
                'content'=>$content,
                'create_time'=>date("Y:m:d H:i:s"),
                'user_id'=>$user_id,
                ));        
        }
        return $intro_edit;
    }   
     // 自我描述
    public function self_intro($user_id){
        $self_intro=$this->_self_intro_model->where(array("user_id"=>$user_id))->find();
        return $self_intro;
    }   
    //手机号码是否注册
    public function mobile_exist($mobile){
        $row=$this->_user_model->field("count(id) total")->where(array(
            "phone_number"=>$mobile
        ))->find();
        return $row["total"]?$row["total"]:0;
    }
    //手机号码获取用户信息
    public function mobile_user_row($mobile){
        return $this->_user_model->where(array("phone_number"=>$mobile))->find();
    }
    //用不基础修改
    public function user_edit($user_id,$avatar_url,$nickname,$gender,$birthday){
        return $this->_user_model->where(array("id"=>$user_id))->save(array(
            'avatar_url'=>$avatar_url,
            'nickname'=>$nickname,
            'gender'=>$gender,
            'birthday'=>$birthday,
            //'phone_number'=>$phone_number,
            'update_time'=>date("Y-m-d H:i:s"),
            ));

    }
    // 猎头修改补充信息或添加补充信息
    public function user_extra_edit($user_id,$city_id,$highest_degree,$email,$work_year,$college,$major){
        $edit_row=$this->_headhunter_info_model->where(array("user_id"=>$user_id))->find();
        if($edit_row){
            $edit_info=$this->_headhunter_info_model->where(array("user_id"=>$user_id))->save(array(
                'city_id'=>$city_id,
                'highest_degree'=>$highest_degree,
                'email'=>$email,
                'work_year'=>$work_year,
                'college'=>$college,
                'major'=>$major,
                'update_time'=>date("Y:m:d H:i:s"),
                ));
        }else{
            $edit_info=$this->_headhunter_info_model->add(array(
                'city_id'=>$city_id,
                'highest_degree'=>$highest_degree,
                'email'=>$email,
                'work_year'=>$work_year,
                'college'=>$college,
                'major'=>$major,
                'create_time'=>date("Y:m:d H:i:s"),
                'user_id'=>$user_id,
                )); 
        }
        return $edit_info;
    }
    //添加用户
    public function add_row($mobile,$user_type){
        return $this->_user_model->add(array(
            "phone_number"=>$mobile,
            "create_time"=>date("Y-m-d H:i:s"),
            "is_seeker"=>$user_type==1?2:1,
            "is_hunter"=>$user_type==2?2:1,
        ));
    }
    //用户身份增加
    public function add_id($user_row,$user_type){
        if($user_type==1){
            if($user_row["is_seeker"]==1){
                $this->_user_model->where(array("id"=>$user_row["id"]))->save(array("is_seeker"=>2));
            }
        }elseif($user_type==2){
            if($user_row["is_hunter"]==1){
                $this->_user_model->where(array("id"=>$user_row["id"]))->save(array("is_hunter"=>2));
            }
        }
    }
    //用户增加资金
    public function add_money($user_id,$money,$link_id,$type){
        //增加资金
        $mone_row=$this->_balance_model->where(array(
            "user_id"=>$user_id,
        ))->find();
        if(!$mone_row){
            $insert_id=$this->_balance_model->add(array(
                "user_id"=>$user_id,
                "balance"=>0,
                "create_time"=>date("Y-m-d H:i:s"),
                "update_time"=>date("Y-m-d H:i:s"),
            ));
            $mone_row=$this->_balance_model->find($insert_id);
        }
        $this->_balance_model->where(array("id"=>$mone_row["id"]))->setInc("balance",$money);
        //记录资金流水
        $this->_flow_model->add(array(
            "user_id"=>$user_id,
            "direction"=>0,
            "amount"=>$money,
            "create_time"=>date("Y-m-d H:i:s"),
            "user_type"=>2,
            "link_id"=>$link_id,
            "type"=>$type
        ));
    }
    //获取导入简历用户信息
    public function resume_user_info($user_id){
        return $this->_resume_model->where(array(
            "user_id"=>$user_id
        ))->find();
    }
    //企业是否邀请过该用户
    public function has_apply_interview($user_id,$company_id){
        $task=new Task();
        return $task->_hunter_order_model->where(array(
            "user_id"=>$user_id,
            "company_id"=>$company_id,
            "current_status"=>array("in",array(2,4,5,6,7,8,9,10,11,12,13,14))
        ))->find();
    }
}