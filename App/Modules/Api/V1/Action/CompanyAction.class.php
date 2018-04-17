<?php
//公司信息
class CompanyAction extends BaseAction {
    //公司列表
    public function lists(){
        $result=$this->_put();
        $this->success($result);
    }
}
