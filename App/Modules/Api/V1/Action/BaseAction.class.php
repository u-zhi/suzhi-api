<?php
//基础抽象类
abstract class BaseAction extends Action {
    protected $_user_id=0;//当前登录用户ｉｄ
    //校验基本信息
    public function _initialize(){
        $controller=strtolower(MODULE_NAME);
        $action=strtolower(ACTION_NAME);
        $url=$controller."/".$action;
        //白名单
        $white=array("user/login","user/sms","user/register",
            "city/open_city_list","task/jober_lists","image/base64_url",
            "banner/banner_list","task/hunter_lists","image/create_thumb",
            "task/task_one","task/occupation_type","task/jober_lists",
            "task/type_classify","wx/config","talent/hunter_join_mytalent1",
            "talent/join_mytalent","wx/location"
        );
        //判断token并获取user_id
        $token=$this->_server("HTTP_TOKEN");
        if($url=="image/upload"){
            $token=$this->_get("token");
        }
        if(!$token || !$user_id=decode_token($token)){
            if(!in_array($url,$white)){
                $this->error("未登录或TOKEN过期");
            }
        }

        //判断用户是否存在
        $user=new User();
        $user_row=$user->get_row($user_id);
        if(!$user_row){
            if(!in_array($url,$white)){
                $this->error("未登录或TOKEN过期");
            }
        }
        if($user_row['is_deleted']){
            if(!in_array($url,$white)){
                $this->error("该用户已被禁止登录");
            }
        }
        $this->_user_id=$user_id;
    }
    //报错
    protected function error($msg='',$code=200){
        $result=array("status"=>"fail","msg"=>$msg,"data"=>array());
        $this->response($result,'json',$code);
    }
    //成功
    protected function success($data,$code=200){
        $result=array("status"=>"success","msg"=>"处理成功","data"=>$data);
        $this->response($result,'json',$code);
    }
    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     * 对原始方法进行重载,修复不能获取 $this->_get()　类似全部参数的方法
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,$method.'_'.$this->_method.'_'.$this->_type)) { // RESTFul方法支持
                $fun  =  $method.'_'.$this->_method.'_'.$this->_type;
                $this->$fun();
            }elseif($this->_method == C('REST_DEFAULT_METHOD') && method_exists($this,$method.'_'.$this->_type) ){
                $fun  =  $method.'_'.$this->_type;
                $this->$fun();
            }elseif($this->_type == C('REST_DEFAULT_TYPE') && method_exists($this,$method.'_'.$this->_method) ){
                $fun  =  $method.'_'.$this->_method;
                $this->$fun();
            }elseif(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }elseif(file_exists_case(C('TMPL_FILE_NAME'))){
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            }else{
                // 抛出异常
                throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }
        }else{
            switch(strtolower($method)) {
                // 获取变量 支持过滤和默认值 调用方式 $this->_post($key,$filter,$default);
                case '_get': $input =& $_GET;break;
                case '_post':$input =& $_POST;break;
                case '_put':
                case '_delete':parse_str(file_get_contents('php://input'), $input);break;
                case '_request': $input =& $_REQUEST;break;
                case '_session': $input =& $_SESSION;break;
                case '_cookie':  $input =& $_COOKIE;break;
                case '_server':  $input =& $_SERVER;break;
                default:
                    throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            }
            if(empty($args)){return $input;} //修复不能获取全部参数的bug
            if(isset($input[$args[0]])) { // 取值操作
                $data	 =	 $input[$args[0]];
                $fun  =  $args[1]?$args[1]:C('DEFAULT_FILTER');
                $data	 =	 $fun($data); // 参数过滤
            }else{ // 变量默认值
                $data	 =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }
}
