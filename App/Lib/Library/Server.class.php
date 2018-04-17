<?php
/*
 * 服务类
 */
class Server extends Tool_User{
    protected $_interview_model=null;//邀面服务
    protected $_innerpush_model=null;//企业内推服务
    protected $_package_model=null;//套餐服务
    protected $_suzhicoin_model=null;//速职币
    protected $_company_server_model=null;//企业已经购买服务
    protected $_company_server_buy_model=null;//企业购买服务记录
    public static $server_type=array(
        1=>"企业内推",
        2=>"邀请面试",
        3=>"套餐",
    );
    public static $server_status=array(
        1=>"使用中",
        2=>"已失效",
    );
    public function __construct(){
        $this->_innerpush_model=new InnerpushModel();
        $this->_interview_model=new InterviewModel();
        $this->_package_model=new PackageModel();
        $this->_company_server_buy_model=new CompanyBuyServerModel();
        $this->_company_server_model=new CompanyServerModel();
        $this->_suzhicoin_model=new SuzhicoinModel();
    }
    //内推服务列表
    public function innerpush_list($company_id=0){
        $list=$this->_innerpush_model->where(array("is_delete"=>1))->order("money")->select();
        foreach($list as $key=>$value){
            //能够购买
            if($company_id){
                $list[$key]["allow_buy"]=$this->_allow_buy($company_id,$value,1);
                $list[$key]["discount_money"]=$this->_discount_money($company_id,$value,1);//抵扣费用
            }
        }
        return $list;
    }
    //邀面服务列表
    public function interview_list($company_id=0){
        $list=$this->_interview_model->where(array("is_delete"=>1))->order("money")->select();
        foreach($list as $key=>$value){
            //能够购买
            if($company_id){
                $list[$key]["allow_buy"]=$this->_allow_buy($company_id,$value,2);
                $list[$key]["discount_money"]=$this->_discount_money($company_id,$value,2);//抵扣费用
            }
        }
        return $list;
    }
    //套餐列表
    public function package_list($company_id=0){
        $package_list=$this->_package_model->where(array("is_delete"=>1))->order("money")->select();
        $interview_id=array();
        $innerpush_id=array();
        $suzhicoin_id=array();
        foreach($package_list as $key=>$value){
            $interview_id[]=$value["interview_id"];
            $innerpush_id[]=$value["innerpush_id"];
            $suzhicoin_id[]=$value["suzhicoin_id"];
            //能否进行购买
            if($company_id){
                $package_list[$key]["allow_buy"]=$this->_allow_buy($company_id,$value,3);
                $package_list[$key]["discount_money"]=$this->_discount_money($company_id,$value,3);//抵扣费用
            }
        }
        if(!empty($package_list)){
            $innerpush_row=$this->_innerpush_model->where(array("id"=>array("in",$innerpush_id)))->select();
            $innerpush_arr=array();
            $suzhicoin_arr=array();
            foreach($innerpush_row as $value){
                $innerpush_arr[$value["id"]]=$value;
            }
            $interview_row=$this->_interview_model->where(array("id"=>array("in",$interview_id)))->select();
            $interview_arr=array();
            $suzhicoin_row=$this->_suzhicoin_model->where(array("id"=>array("in",$suzhicoin_id)))->select();
            foreach ($interview_row as $value){
                $interview_arr[$value["id"]]=$value;
            }
            foreach ($suzhicoin_row as $v){
                $suzhicoin_arr[$v["id"]]=$v;
            }
            foreach ($package_list as $key=>$value){
                $package_list[$key]["interview_info"]=$interview_arr[$value["interview_id"]];
                $package_list[$key]["innerpush_info"]=$innerpush_arr[$value["innerpush_id"]];
                $package_list[$key]["suzhicoin_info"]=$suzhicoin_arr[$value["suzhicoin_id"]];
            }
        }
        return $package_list;
    }
    // 速职币列表
    public function suzhicoin_list($company_id=0){
        $list=$this->_suzhicoin_model->where(array("is_delete"=>1))->order("money")->select();
        // foreach($list as $key=>$value){
        //     //能够购买
        //     if($company_id){
        //         $list[$key]["allow_buy"]=$this->_allow_buy($company_id,$value,2);
        //         $list[$key]["discount_money"]=$this->_discount_money($company_id,$value,2);//抵扣费用
        //     }
        // }
        return $list;
    }
    //购买服务
    public function buy_server($company_id,$server_id,$type=1){
        if($type==1){
            $server_row=$this->_innerpush_model->find($server_id);
        }elseif($type==2){
            $server_row=$this->_interview_model->find($server_id);
        }elseif($type==3){
            $server_row=$this->_package_model->find($server_id);
        }elseif($type==4){
            $server_row=$this->_suzhicoin_model->find($server_id);
        }else{
            return false;
        }

        if(!$this->_allow_buy($company_id,$server_row,$type)){
            return false;
        }

        //抵扣金额
        $discount_money=$this->_discount_money($company_id,$server_row,$type);
        $money=$server_row["money"]-$discount_money;
        //用户金额扣费
        $company=new Company();
        $company_row=$company->user_row($company_id);

        if($company_row["money"]<$money){
            return false;
        }
        if($money>0){
            $company->plus_money($company_id,$money);
        }
        //购买记录
        $buy_id=$this->_company_server_buy_model->add(array(
            "company_id"=>$company_id,
            "server_id"=>$server_id,
            "server_type"=>$type,
            "buy_time"=>date("Y-m-d H:i:s"),
            "money"=>$server_row["money"],
            "discount"=>$discount_money,
            "content"=>json_encode($server_row),
        ));
        //增加服务
        if($type==1){
            $this->_company_server_model->where(array(
                "company_id"=>$company_id,
                "type"=>1,
                "status"=>1,
            ))->save(array("status"=>2));
            $this->_company_server_model->add(array(
                "company_id"=>$company_id,
                "buy_id"=>$buy_id,
                "type"=>1,
                "number"=>$server_row["number"],
                "begin_time"=>date("Y-m-d")." 00:00:00",
                "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$server_row["expire_year"]*86400*365),
                "add_time"=>date("Y-m-d H:i:s"),
                "money"=>$server_row["money"],
            ));
        }elseif($type==2){
            $this->_company_server_model->add(array(
                "company_id"=>$company_id,
                "buy_id"=>$buy_id,
                "type"=>2,
                "number"=>$server_row["number"],
                "begin_time"=>date("Y-m-d")." 00:00:00",
                "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$server_row["expire_year"]*86400*365),
                "add_time"=>date("Y-m-d H:i:s"),
                "money"=>$server_row["money"],
            ));
        }elseif($type==3){
            $innerpush_row=$this->_innerpush_model->find($server_row["innerpush_id"]);
            $this->_company_server_model->where(array(
                "company_id"=>$company_id,
                "type"=>1,
                "status"=>1,
            ))->save(array("status"=>2));
            $this->_company_server_model->add(array(
                "company_id"=>$company_id,
                "buy_id"=>$buy_id,
                "type"=>1,
                "number"=>$innerpush_row["number"],
                "begin_time"=>date("Y-m-d")." 00:00:00",
                "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$innerpush_row["expire_year"]*86400*365),
                "add_time"=>date("Y-m-d H:i:s"),
                "money"=>$innerpush_row["money"],
            ));
            $interview_row=$this->_interview_model->find($server_row["interview_id"]);
            $suzhicoin_row=$this->_suzhicoin_model->find($server_row["suzhicoin_id"]);
            $this->_company_server_model->add(array(
                "company_id"=>$company_id,
                "buy_id"=>$buy_id,
                "type"=>2,
                "number"=>$interview_row["number"],
                "begin_time"=>date("Y-m-d")." 00:00:00",
                "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$interview_row["expire_year"]*86400*365),
                "add_time"=>date("Y-m-d H:i:s"),
                "money"=>$interview_row["money"],
            ));
            if($suzhicoin_row)
            {
                $this->_company_server_model->add(array(
                    "company_id"=>$company_id,
                    "buy_id"=>$buy_id,
                    "type"=>4,
                    "number"=>$suzhicoin_row["number"],
                    "begin_time"=>date("Y-m-d")." 00:00:00",
                    "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$interview_row["expire_year"]*86400*365),
                    "add_time"=>date("Y-m-d H:i:s"),
                    "money"=>$suzhicoin_row["money"],
                ));
            }
        }elseif($type=4){
            $this->_company_server_model->add(array(
                "company_id"=>$company_id,
                "buy_id"=>$buy_id,
                "type"=>4,
                "number"=>$server_row["number"],
                "begin_time"=>date("Y-m-d")." 00:00:00",
                "end_time"=>date("Y-m-d H:i:s",strtotime(date("Y-m-d")." 00:00:00")+$server_row["expire_year"]*86400*365),
                "add_time"=>date("Y-m-d H:i:s"),
                "money"=>$server_row["money"],
            ));
        }
        return $buy_id;
    }
    //某种服务能否被购买 内推服务不能降级且不能叠加
    private function _allow_buy($company_id,$server_row,$type=1){
        if($type==2 || $type==4){return true;}
        static $innerpush_data=array();
        static $on_server_row=null;
        if(is_numeric($server_row)){
            switch($type){
                case 1:
                    $server_row=$this->_innerpush_model->find($server_row);
                    break;
                case 3:
                    $server_row=$this->_package_model->find($server_row);
                    break;
            }
        }
        if($type==3){
            if(isset($innerpush_data[$server_row["innerpush_id"]])){
                $server_row=$innerpush_data[$server_row["innerpush_id"]];
            }else{
                $server_row=$this->_innerpush_model->find($server_row["innerpush_id"]);
                $innerpush_data[$server_row["id"]]=$server_row;
            }
        }
        //企业正在使用的内推服务
        if(!$on_server_row){
            $on_server_row=$this->_company_server_model->where(array(
                "company_id"=>$company_id,
                "type"=>1,
                "status"=>1,
            ))->find();
        }
        if(!is_array($on_server_row) || empty($on_server_row)){return true;}
        if($on_server_row["number"]>$server_row["number"]){return false;}
        return true;
    }
    //购买服务折算金额 内推服务和套餐(因为内推服务)未到期可以进行抵扣
    private function _discount_money($company_id,$server_row,$type=1){
        if($type==2){return 0;}
        static $discount_money=null;
        //企业正在使用的内推服务
        if(!$discount_money){
            if($type==3){
                $server_row=$this->_innerpush_model->find($server_row["innerpush_id"]);
            }
            $on_server_row=$this->_company_server_model->where(array(
                "company_id"=>$company_id,
                "type"=>1,
                "status"=>1,
            ))->find();
            if(!is_array($on_server_row) || empty($on_server_row)){
                $discount_money=0;return 0;
            }
            if($on_server_row["number"]>$server_row["number"]){$discount_money=0;return 0;}
            //计算可以抵扣的金额
            $left_day=strtotime(date("Y-m-d",strtotime($on_server_row["end_time"]))." 00:00:00")-strtotime(date("Y-m-d"));
            $left_money=($left_day/86400)*$on_server_row["money"]/365;
            $discount_money=$left_money;
            return $left_money;
        }
        return $discount_money;
    }
    //邀面减少可用邀面数量
    public function interview_plus_number($company_id){
        $row=$this->_company_server_model->where(array(
            "company_id"=>$company_id,
            "type"=>2,
            "status"=>1,
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->order("end_time")->find();
        $data=array(
            "has_number"=>$row["has_number"]+1,
        );
        if($data["has_number"]>=$row["number"]){
            $data["status"]=2;
        }
        $this->_company_server_model->where(array("id"=>$row["id"]))->save($data);
    }
    /*
     * 统计相关
     */
    //内推系统
    public function innerpush_total($company_id){
        $result=$this->_company_server_model->where(array(
            "company_id"=>$company_id,
            "type"=>1,
            "status"=>1,
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->find();
        if(!$result){return array();}
        $gone_day=(strtotime($result["end_time"])-strtotime(date("Y-m-d")." 00:00:00"))/86400;
        $result["expire_day"]=$gone_day;
        return $result;
    }
    //邀请面试
    public function interview_total($company_id){
        $result=$this->_company_server_model->field("sum(number) as total,sum(has_number) as use_total")->where(array(
            "company_id"=>$company_id,
            "type"=>2,
            "status"=>1,
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->find();
        if(!$result){return 0;}
        return $result["total"]-$result["use_total"];
    }
    // 速职币系统
    public function suzhicoin_total($company_id){
        $result=$this->_company_server_model->field("sum(number) as total,sum(has_number) as use_total")->where(array(
            "company_id"=>$company_id,
            "type"=>4,
            "status"=>1,
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->find();
        if(!$result){return 0;}
        return $result["total"]-$result["use_total"];
    }
    //使用减少速职币数量
    public function suzhicoin_plus_number($company_id,$coin_num){
        $row=$this->_company_server_model->where(array(
            "company_id"=>$company_id,
            "type"=>4,
            "status"=>1,
            "end_time"=>array("gt",date("Y-m-d H:i:s")),
        ))->order("end_time")->find();
        $data=array(
            "has_number"=>$row["has_number"]+intval($coin_num),
        );
        if($data["has_number"]>=$row["number"]){
            $data["status"]=2;
        }
        $this->_company_server_model->where(array("id"=>$row["id"]))->save($data);
    }

}
