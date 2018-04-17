<?php
/*
 * 分页类
 */
class Tool_Pagenitor{

    protected $_content=null;//查询数据信息
    protected $_total_page=null;//总页数
    protected $_count=null; //总记录数
    protected $_current_page=0;//当前页码
    public function __construct(Model &$model,$page=1,$pageItem=20){
        $clone_model=clone $model;
        //获取查询数据
        $float=$pageItem*($page-1);
        $this->_content=$model->limit($float,$pageItem)->select();
        if(!$this->_content){
            $this->_content=array();
        }
        //获取总页数
        $count=$clone_model->field("count(*) as total")->find();
        $this->_total_page=ceil($count["total"]/$pageItem);
        $this->_count = $count['total'];
        $this->_current_page=$page;
        unset($clone_model);
    }

    //获取内容
    public function content(){
        return $this->_content;
    }

    //获取分页
    public function pageinfo(){
        $start=$this->_current_page-4;
        if($start<1){$start=1;}
        $end=$start+8;
        if($end>$this->_total_page){
            $end=$this->_total_page;
        }
        $page=array(
            "total"=>$this->_total_page,
            "count"=>$this->_count,
            "current"=>$this->_current_page,
            "previous"=>$this->_current_page>1?($this->_current_page-1):null,
            "next"=>$this->_current_page<$this->_total_page?($this->_current_page+1):null,
            "first"=>1,
            "last"=>$this->_total_page,
            "range"=>$this->_total_page>0?range($start,$end):array(),
        );
        return $page;
    }

    //获取分页
    public function pageinfo2(){
        $_total_page = $this->_total_page > 50 ? 50 : $this->_total_page;
        $_current_page = $this->_current_page > $_total_page ? $_total_page : $this->_current_page;
        $start=$_current_page-4;
        if($start<1){$start=1;}
        $end=$start+4;
        if($end>$_total_page){
            $end=$_total_page;
        }

        if ($_total_page > 10) {
            if ($_total_page - $_current_page < 9 ) {
                if ($_total_page - $_current_page < 3) {
                    $rightStart = $_total_page - $_current_page < 2 ? $_total_page -4 : $_current_page - 2;
                    $rightEnd = $_total_page - $_current_page < 2 ? $_total_page : $_current_page + 2;
                    $range = array_merge(range(1, 5), ['······'], range($_total_page - 4, $_total_page));
                } else {
                    $range = array_merge(range(1, 5), ['······'], range($_current_page -2, $_current_page + 2));
                }
            } else {
                $leftStart = $_current_page > 2 ? $_current_page -2 : $_current_page;
                $leftEnd = $_current_page > 2 ? $_current_page + 2 : $_current_page + 4;
                $range = array_merge(range($leftStart, $leftEnd), ['······'], range($_total_page - 4, $_total_page));
            }
        } else {
            $range = $this->_total_page>0?range($start,$end):array();
        }
        $page=array(
            "total"=>$_total_page,
            "count"=>$this->_count,
            "current"=>$_current_page,
            "previous"=>$_current_page>1?($_current_page-1):null,
            "next"=>$_current_page<$_total_page?($_current_page+1):null,
            "first"=>1,
            "last"=>$_total_page,
            "range"=>$range,
        );
        return $page;
    }

    public function __destruct(){
        $this->_content=null;
        $this->_total_page=null;
        $this->_count=null;
        $this->_current_page=null;
    }
}
