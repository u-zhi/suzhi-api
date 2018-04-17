<?php
/*
 * CURL操作类
 * $result=(new Tool_Curl())->expire(10)->head(false)->get($url,$data);
 */
class Tool_Curl{
    protected $_expire_time=10;//请求超时时间
    protected $_head=false;//是否返回头部
    protected $_nobody=false;//是否不返回主体
    protected $_file=false;//发送文件
    protected $_ca=false;//ca证书
    protected $_referer=false;//referer地址
    protected $_useragent=false;//浏览器信息
    protected $_cookie=false;//cookie存储文件
    protected $_http_code=false;//是否只返回状态码
    public function __construct(){
        $this->_default_expire_time=$this->_expire_time;
    }
    //设置超时时间
    public function expire($time=10){
        $this->_expire_time=$time;
        return $this;
    }
    //是否返回头部
    public function head($flag=true){
        $this->_head=$flag;
        return $this;
    }
    //是否不返回body
    public function nobody($flag=true){
        $this->_nobody=$flag;
        return $this;
    }
    //设置发送文件
    public function file(array $data){
        $file=array();
        foreach($data as $key=>$value){
            $file[$key]="@".$value;
        }
        $this->_file=$file;
        return $this;
    }
    //设置ca证书
    public function ca($ca){
        $this->_ca=$ca;//$ca cacert.pem
        return $this;
    }
    //设置referer
    public function referer($referer){
        $this->_referer=$referer;
        return $this;
    }
    //设置浏览器信息
    public function useragent($type="fixfore"){
        $type=strtolower($type);
        if($type=="fixfore"){
            $this->_useragent="Mozilla/5.0 (Windows NT 6.1; rv:53.0) Gecko/20100101 Firefox/53.0";
        }elseif($type=="chrome"){
            $this->_useragent="Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36";
        }elseif($type=="ie"){
            $this->_useragent="Mozilla/5.0 (Windows NT 6.1; rv:53.0) Gecko/20100101 Firefox/53.0";
        }
        return $this;
    }
    //设置cookie存储文件
    public function cookie($cookie_file){
        $this->_cookie=$cookie_file;
        return $this;
    }
    //是否只返回状态码
    public function http_code($flag=true){
        $this->_http_code=$flag;
        return $this;
    }
    //清除中间设置
    protected function clean(){
        $this->_expire_time=$this->_default_expire_time;
        $this->_head=false;
        $this->_file=false;
        $this->_ca=false;
        $this->_referer=false;
        $this->_useragent=false;
        $this->_cookie=false;
        $this->_nobody=false;
        $this->_http_code=false;
    }
    //post操作
    public function post($url,array $data=array(),array $head=array()){
        return $this->_request($url,$data,$head,"post");
    }
    //get操作
    public function get($url,array $data=array(),array $head=array()){
        return $this->_request($url,$data,$head,"get");
    }
    //put操作
    public function put($url,array $data=array(),array $head=array()){
        return $this->_request($url,$data,$head,"put");
    }
    //delete操作
    public function delete($url,array $data=array(),array $head=array()){
        return $this->_request($url,$data,$head,"delete");
    }
    /*
     * 请求
     * $url  请求地址
     * $data 请求数据
     * $head 头部信息
     * $type 请求类型 post get
    */
    private function _request($url,array $data=array(),array $head=array(),$type){
        if($this->_file && $type=="post"){
            $data=array_merge($data,$this->_file);
        }
        $data=http_build_query($data);
        if($type=="get"){
            $url.=strpos($url,"?")===false?'?'.$data:'&'.$data;
        }
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//结果是否返回
        curl_setopt($ch, CURLOPT_HEADER, $this->_head);//是否返回头部信息
        curl_setopt($ch, CURLOPT_NOBODY, $this->_nobody);//是否不返回主体信息
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_expire_time);//设置curl超时秒数
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );//是否重定向
        curl_setopt($ch, CURLOPT_MAXREDIRS,10 );//重定向最多次数
        if($this->_referer){
            curl_setopt($ch, CURLOPT_REFERER, $this->_referer);//带来的Referer
        }else{
            curl_setopt($ch, CURLOPT_AUTOREFERER,true); // 自动设置Referer
        }
        //模拟浏览器
        if($this->_useragent){
            curl_setopt($ch, CURLOPT_USERAGENT,$this->_useragent);
        }
        //是否存储cookie模拟请求和访问(模拟登陆状态)
        if($this->_cookie){
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie); // 存放Cookie信息的文件名称
            curl_setopt($ch, CURLOPT_COOKIEFILE,$this->_cookie); // 读取上面所储存的Cookie信息
        }
        //https请求时强制不验证证书
        if(strpos(strtolower($url),"https")===0){
            if($this->_ca){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
                curl_setopt($ch, CURLOPT_CAINFO, $this->_ca); // CA根证书（用来验证的网站证书是否是CA颁布）
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
            }else{
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
            }
        }
        //发送消息体数据
        if($type=="post"){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        }elseif($type=="put"){
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        }elseif($type=="delete"){
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        }

        //发送头部数据
        if(!empty($head)){
            $httpHeaders=array(
                "Expect"=>"",//避免data数据过长问题
                "Accept-Language"=>"zh-cn",
                "Accept-Encoding"=>"gzip,deflate",
                "Connection"=>"Keep-Alive",
                "Cache-Control"=>"no-cache",
            );
            foreach($head as $key=>$value){
                $httpHeaders[]=$key.":".$value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }
        //执行并获取内容
        $info=curl_exec($ch);
        if($this->_http_code){
            //清除中间设置
            $this->clean();
            return curl_getinfo($ch,CURLINFO_HTTP_CODE);
        }
        //清除中间设置
        $this->clean();
        if($info===false){
            return curl_error($ch);
        }
        //释放curl句柄
        curl_close($ch);
        return $info;
    }
}