<?php
// 服务相关
class ServerAction extends Action {
    //整体概况
    public function index(){
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
        $this->display();
    }
    //开通新的服务
    public function add(){
        $company_id=Company::login_user_id();
        $server=new Server();
        $this->assign("innerpush_list",$server->innerpush_list($company_id));
        $this->assign("interview_list",$server->interview_list($company_id));
        $this->assign("coin_list",$server->suzhicoin_list($company_id));
        $this->assign("package_list",$server->package_list($company_id));
        $this->assign("force",$this->_get("force"));
        $this->display();
    }
    //购买服务
    public function buy(){
        if($this->isPost()){
            $mix_id=trim(strval($this->_post("mix_id")));
            $interview_id=intval($this->_post("interview_id"));
            $suzhicoin_id=intval($this->_post("suzhicoin_id"));
            $coin_id=intval($this->_post("coin_id"));
            if(!empty($coin_id))
                $suzhicoin_id=$coin_id;
            if(!$mix_id && !$interview_id && !$suzhicoin_id){
                $this->error("请选择服务",U("index/buy_server"));
            }
            $server=new Server();
            $company_id=Company::login_user_id();
            //先购买套餐或内推
            if($mix_id){
                if(strpos($mix_id,"innerpush_")!==false){
                    $server_id=intval(ltrim($mix_id,"innerpush_"));
                    $server->buy_server($company_id,$server_id,1);
                }elseif(strpos($mix_id,"package_")!==false){
                    $server_id=intval(ltrim($mix_id,"package_"));
                    $server->buy_server($company_id,$server_id,3);
                }
            }
            //后购买邀面
            if($interview_id){
                $server->buy_server($company_id,$interview_id,2);
            }
            // 购买速职币
            if($suzhicoin_id){
                $server->buy_server($company_id,$suzhicoin_id,4);
            }

            $this->success("购买成功",U("server/index"));
        }
    }
}
