<?php
/*
 * 手机验证码类
 */
class Code{
    //短信类型
    public static $_type=array(
        0=>"登录",
        1=>"注册",
        2=>"企业注册",
        3=>"企业提现",
        4=>"企业修改手机号码",
        5=>"忘记密码",
    );
    //普通短信通知模板类型
    public static $_msg_type=array(
        205546=>array(
            "desc"=>'猎头-新反馈',
            "template"=>'{1},您推荐的人才有新的动态啦,详情查看{2}.',
        ),
        205545=>array(
            "desc"=>"求职者-新反馈",
            "template"=>"{1},您投递的职位有新反馈啦,详情查看{2}"
        ),
        205544=>array(
            "desc"=>"内推系统-发布岗位",
            "template"=>"{1},您的公司{2}有新的内推岗位需求,详情链接查看{3},首页-我的公司"
        ),
        205543=>array(
            "desc"=>"内推系统-添加成员",
            "template"=>"{1},您已加入公司{2}的内推系统,推荐人才有赏金,最高有30000元,详情链接查看{3},首页-我的公司"
        ),
        205029=>array(
            "desc"=>"报价反馈",
            "template"=>"您发起的{1},速职已经报价反馈,请注意查看"
        ),
        205028=>array(
            "desc"=>"邀面次数过少",
            "template"=>"您开通的邀请面试次数已经少于{1},请注意续费"
        ),
        205026=>array(
            "desc"=>"内推过期",
            "template"=>"您开通的{1}内推系统已经到期,请注意续费"
        ),
        205025=>array(
            "desc"=>"提现成功",
            "template"=>"您于{1}申请的{2}提现,已打入制定账号"
        ),
    );
    protected $_expire_time=5;//短信的有效分钟数
    protected $_sms_log=null;//短信发送队列表
    public function __construct(){
        $this->_code_model=new CodeModel();
        $this->_sms_log=new SmsLogModel();
    }

    //发送短信
    public function company_register_send($mobile,$type=2){
        $code=rand_string(4,1);
        $check_row=$this->_code_model->where(array(
            "phone"=>$mobile,
            "is_used"=>0,
            "type"=>$type
        ))->find();
        if($check_row){
            $this->_code_model->where(array("id"=>$check_row["id"]))->save(array(
                "code"=>$code,
                "create_time"=>date("Y-m-d H:i:s"),
            ));
        }else{
            $this->_code_model->add(array(
                "phone"=>$mobile,
                "type"=>$type,
                "code"=>$code,
                "create_time"=>date("Y-m-d H:i:s"),
            ));
        }
        //调用短信api进行发送
        $msg="您的验证码为：".$code.",请及时验证,".$this->_expire_time."分钟有效";
        $this->_api_send($mobile,$msg,$code);

        return $code;
    }
    //短信校验
    public function check_code($mobile,$code,$type=0){
        $check_row=$this->_code_model->where(array("phone"=>$mobile,"is_used"=>0,"type"=>$type))->find();
        if(!is_array($check_row) || !$check_row){return false;}
        $effective=date("Y-m-d H:i:s",time()-$this->_expire_time*60);//有效时间
        if($check_row["code"]!=$code || $check_row["create_time"]<$effective){return false;}
        $this->_code_model->where(array("id"=>$check_row["id"]))->save(array(
            "is_used"=>1,
            "use_time"=>date("Y-m-d H:i:s"),
        ));
        return true;
    }
    //调用短信api进行短信发送
    private function _api_send($mobile,$msg,$code){
        $sms=new Tool_Sms();
        $sms->sendMsg($mobile,$msg,$code);
    }

    //发送短信通知  手机号 参数(按照模板中的参数顺序) 模板id $now_send是否立即发送
    public function send_msg($mobile,array $data,$template_id,$now_send=false){
        if($now_send){
            $sms=new Tool_Sms();
            $sms->send_msg($mobile,$data,$template_id);
        }else{
            //加入队列等待发送
            $this->_sms_log->add(array(
                "mobile"=>$mobile,
                "template_id"=>$template_id,
                "params"=>json_encode($data),
                "create_time"=>date("Y-m-d H:i:s")
            ));
        }
    }
}
