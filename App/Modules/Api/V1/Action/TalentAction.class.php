<?php
//人才库信息
class TalentAction extends BaseAction {
    /*猎头人才库列表
    *
    *   @token 
        @page
        @page_sum
    *
    */
    public function talentlist(){
        $user_id=$this->_user_id;
        $talent=new Outsourcing();
        // 我的人才库总数
        $talentsum=$talent->talentsum($user_id);
        $select=$talent->_talent_model->where(array("headhunter_user_id"=>$user_id));
        //分页
        $page=intval($this->_get("page"));
        $page_sum=intval($this->_get("page_sum")) ? intval($this->_get("page_sum")) : 10 ;
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,$page_sum);
        //列表
        $content=$pagenitor->content();
        $user=new User();
        foreach ($content as $key => $value) {
            $content[$key]=$user->get_row($value['jobhunter_user_id']);
            if($content[$key]['avatar_url']){
                $content[$key]['avatar_url']=thumb_image($content[$key]['avatar_url'],65,65);
            }else{
                $content[$key]['avatar_url']="/images/1497267078.jpg";
            }
        }
        $pageinfo=$pagenitor->pageinfo();
        $this->success(array(
            "pageinfo"=>$pageinfo,
            "content"=>$content,
            "talentsum"=>$talentsum,
            ));
    }

    
    /*
    *邀请加入人才库
    *token 
    */
    public function join_mytalent(){
        $user_id=$this->_post("user_id");
        if(empty($user_id)){$this->error("请正确操作！");}
        // 获取顾问信息
        $user=new User();
        $user_info=$user->get_row($user_id);
        // 顾问目前的人才库人数
        $talent=new Outsourcing();
        $talentsum=$talent->talentsum($user_id);
        //邀请二维码
        $text=str_replace("amp;","",strval($this->_post("text")));
        $text.=$user_id."&k=".$user_info["nickname"];
        $qrcode_url=base64_image(ROOT .qrcode($text));
        $this->success(array("user_info"=>$user_info,"talentsum"=>$talentsum,"qrcode_url"=>$qrcode_url));
    }

    /*加入我的人才库*/
    public function join_hunter_talent(){
        $user_id=$this->_user_id;
        $hunter_id=intval($this->_post('hunter_id'));

        $talent=new Outsourcing();
        // 判断是否已经加入了
        $join_yes=$talent->join_mytalent_row($hunter_id,$user_id);
        if($join_yes){$this->error("你已经在此顾问人才库了，请不要重复操作");}
        // 加入顾问的人才库
        $data['headhunter_user_id']=$hunter_id;
        $data['jobhunter_user_id']=$user_id;
        $data['create_time']=date("Y-m-d H:i:s");
        $talentsum=$talent->join_hunter_talent($data);
        if($talentsum){
            $this->success(array("content"=>true));
        }else{
            $this->error("加入失败请重新尝试！");
        }

    }

    /*
    *投递职位-推荐人才
    *token 
    */
    public function hunter_join_mytalent(){
        $user_id=$this->_user_id;
        $task_id=intval($this->_post("task_id"));
        if(empty($user_id)){$this->error("请正确操作！");}
        //职位信息
        $task=new Task();
        $task_row=$task->get_row($task_id);
        //薪资范围
        $buffer=explode(",",$task_row["salary_range"]);
        $task_row["salary_range"]=$buffer[0]."K-".$buffer[1]."K";
        //任职要求
        $task_row["skill_need"]=array_filter(json_decode($task_row["skill_need"],true));
         // 公司信息
        $company=new Company();
        $company_obj=$company->user_row($task_row["company_id"]);
        $task_row['logo_image']=thumb_image($company_obj['logo_url'],56,56);
        $task_row['company_name']=$company_obj['name'];
        // 职位名称 name

        // 获取顾问信息
        $user=new User();
        $user_info=$user->get_row($user_id);
        // 顾问目前的人才库人数
        $talent=new Outsourcing();
        $talentsum=$talent->talentsum($user_id);
        //邀请二维码 目前放空 之加
        $text=str_replace("amp;","",strval($this->_post("text")));
        $text.=$user_id;
        $qrcode_url=base64_image(ROOT .qrcode($text));
        $this->success(array("user_info"=>$user_info,"talentsum"=>$talentsum,"task_row"=>$task_row,"qrcode_url"=>$qrcode_url));
    }
    public function hunter_join_mytalent1(){
        $user_id=$this->_post("user_id");
        $task_id=intval($this->_post("task_id"));
        if(empty($user_id)){$this->error("请正确操作！");}
        //职位信息
        $task=new Task();
        $task_row=$task->get_row($task_id);
        //薪资范围
        $buffer=explode(",",$task_row["salary_range"]);
        $task_row["salary_range"]=$buffer[0]."K-".$buffer[1]."K";
        //任职要求
        $task_row["skill_need"]=array_filter(json_decode($task_row["skill_need"],true));
        // 公司信息
        $company=new Company();
        $company_obj=$company->user_row($task_row["company_id"]);
        $task_row['logo_image']=thumb_image($company_obj['logo_url'],56,56);
        $task_row['company_name']=$company_obj['name'];
        // 职位名称 name

        // 获取顾问信息
        $user=new User();
        $user_info=$user->get_row($user_id);
        // 顾问目前的人才库人数
        $talent=new Outsourcing();
        $talentsum=$talent->talentsum($user_id);
        //邀请二维码 目前放空 之加
        $text=str_replace("amp;","",strval($this->_post("text")));
        $text.=$user_id;
        $qrcode_url=base64_image(ROOT .qrcode($text));
        $this->success(array("user_info"=>$user_info,"talentsum"=>$talentsum,"task_row"=>$task_row,"qrcode_url"=>$qrcode_url));
    }
    
}
