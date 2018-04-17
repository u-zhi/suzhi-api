<?php
// 内推系统
class InnerpushAction extends Action {

/*  可全局引用的方法   
    Public function _initialize(){       
        $innerpush=new Company();
        }*/
    //整体概览
    public function index(){
        $innerpush=new Company();
        // 企业成员
        $company_staff_sum=$innerpush->staff_number(Company::login_user_id());
        // 内推职位
        $company_innerpush_sum=$innerpush->innerpush_task_number(Company::login_user_id());
        $this->assign("company_staff_sum",$company_staff_sum);
        $this->assign("company_innerpush_sum",$company_innerpush_sum);
        $this->display();
    }
    //企业成员
    public function staff(){
        //是否已经开通内推职位
        $staff=new Company();
        if(!$staff->has_open_innerpush(Company::login_user_id())){
            $this->redirect("server/add",array("force"=>1));
        }

        $select=$staff->_staff_model->where(array("company_id"=>Company::login_user_id(),"is_delete"=>1,"end_time"=>array("gt",date("Y-m-d")." 00:00:00")));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        $company_staff_list=$pagenitor->content();
        $company=new Company();
        foreach ($company_staff_list as $key => $value) {
            //内推的人数
            $jobhunter_order_sum=$staff->_jobhunter_order_model->where(array("inner_user_id"=>$value['user_id']))->count();
            $company_staff_list[$key]['jobhunter_order_sum']=$jobhunter_order_sum;
            //累计获取的奖金(内推)
            $company_staff_list[$key]['commission']=$company->staff_money($value["user_id"]);
        }
        $this->assign('company_staff_list',$company_staff_list);
        $this->assign("pageinfo",$pagenitor->pageinfo());
        //最近一次开通的内推服务
        $this->assign("last_innerpush_server",$staff->last_innerpush_server(Company::login_user_id()));
        $this->display();
    }
    // 删除企业成员
    public function staffdel(){
        $data=$this->_post();
        $postid=$data['id'];
        $staff=new Company();
        $datastaff['is_delete']=2;
        $delete=$staff->_staff_model->where(array("company_id"=>Company::login_user_id(),"id"=>$postid))->save($datastaff);
        if($delete){
            $this->success("删除成功",U('innerpush/staff'));
        }else{
            $this->error("删除失败请重新操作");
        }

    }
    // 添加企业成员
    public function staffadd(){
        if($this->isPost()){
            $data=$this->_post();
            $staff=new Company();
            $add=$staff->add_staff(Company::login_user_id(),$data['department'],$data['name'],$data['mobile']);
            if($add){
                $this->success("添加企业成员成功",U('innerpush/staff'));
            }else{
                $this->error("添加企业成员失败");
            }
        }else{
            $this->error("添加企业成员失败");
        }

    }
    // 导入企业成员
    public function staffexcle(){
        if($this->isPost()){
            $tmp_file = $_FILES ['file'] ['tmp_name']; //临时文件
            $name = $_FILES['file']['name'];   //上传文件名
            $buffer=explode(".",$name);
            $file_type=strtolower(end($buffer));
            //根据上传类型做不同处理
            new Tool_Excel();
            if ($file_type == 'xls') {
                $reader = PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)
            }elseif ($file_type == 'xlsx') {
                $reader = new PHPExcel_Reader_Excel2007();
            }else{
                $this->error("上传文件格式错误",U("innerpush/staff"));
            }
            //读excel文件
            $PHPExcel = $reader->load($tmp_file, 'utf-8'); // 载入excel文件
            $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数
            //把Excel数据保存数组中
            $data = array();
            for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                $data[$rowIndex]=array();
                for ($colIndex = 'A'; $colIndex <= $highestColumm; $colIndex++) {
                    $addr = $colIndex . $rowIndex;
                    $cell = $sheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) { //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $data[$rowIndex][] = trim($cell);
                }
            }
            //写入数据库
            $company=new Company();
            foreach($data as $value){
                if(isset($value[2]) && preg_match("/^\d{11}$/",$value[2])){
                    $company->add_staff(Company::login_user_id(),$value[0],$value[1],$value[2]);
                }
            }
            $this->success("添加成功",U("innerpush/staff"));exit;
        }
    }
    // ajax 判断手机号内推成员手机号是够存在
    public function ajax_staff_mobile(){
        $mobile=trim(strval($this->_get("mobile")));
        $staff=new Company();
        $check_row=$staff->_staff_user_id($mobile);
        if($check_row){
            echo 1;
        }else{
            echo 0;
        }
    }
    //内推职位
    public function task(){
        //是否已经开通内推职位
        $staff=new Company();
        if(!$staff->has_open_innerpush(Company::login_user_id())){
            $this->redirect("server/add",array("force"=>1));
        }
        $task=new Task();
        $select=$task->_task_model->where(array("firm_id"=>Company::login_user_id(),"status"=>array("in",array(1,2)),"recruit_type_inner"=>2));
        //分页
        $page=intval($this->_get("page"));
        $page=$page?$page:1;
        $pagenitor=new Tool_Pagenitor($select,$page,10);
        $task_list=$pagenitor->content();
        foreach ($task_list as $key => $value) {
            //收到的简历
            $task_list[$key]["order_number"]=$task->task_order_number($value["id"]);
            //面试人数
            $task_list[$key]["interview_number"]=$task->task_interview_number($value["id"]);
        }

        $this->assign('task_list',$task_list);
        $this->assign("pageinfo",$pagenitor->pageinfo());

        $this->display();
    }

}
