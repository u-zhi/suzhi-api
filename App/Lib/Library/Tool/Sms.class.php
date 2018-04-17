<?php
/*
 * 发送短信接口
*/
class Tool_Sms{
    //阿里短信接口
	public function sendMsg($phone,$conent,$code){
//		include  "aliyun/top/TopClient.php";
		require __DIR__."/aliyun/TopSdk.php";
		require __DIR__."/aliyun/top/TopClient.php";
		require __DIR__."/aliyun/top/ResultSet.php";
		require __DIR__."/aliyun/top/RequestCheckUtil.php";
		require __DIR__."/aliyun/top/TopLogger.php";
		require __DIR__."/aliyun/top/request/AlibabaAliqinFcSmsNumSendRequest.php";//将需要的类引入
		//$code=self::createNum();
		$appkey = "23592276";//你的App key
		$secret = "2caa66a514a1d9276a13ac56f2e6e4f1";//你的App Secret
		$c = new TopClient;
		$c->appkey = $appkey;
		$c->secretKey = $secret;
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req->setExtend($conent);//确定发给的是哪个用户，参数为用户id,此处作为type类型
		$req->setSmsType("normal");
		/*进入阿里大鱼的管理中心找到短信签名管理，输入已存在签名的名称，这里是身份验证*/
		$req->setSmsFreeSignName("速职");
		$req->setSmsParam("{'code':\"".$code."\"}");
		//这里设定的是发送的短信内容：验证码${code}，您正在进行${product}身份验证，打死不要告诉别人哦！”
		$req->setRecNum($phone);//参数为用户的手机号码
		$req->setSmsTemplateCode("SMS_38740074");
		$resp = $c->execute($req);
		$data = self::object_to_array($resp);
		$jsonarr = array('status'=>0);
		if(isset($data['result']) && $data['result']['success']){
			//其他处理
		}else{
			$jsonarr = array('status'=>'1');
		}
		return $jsonarr;
	}
	//将对象转换成数组
	public function object_to_array($obj){
		$_arr = is_object($obj) ? get_object_vars($obj) :$obj;
		foreach ($_arr as $key=>$val){
			$val = (is_array($val) || is_object($val)) ? $this->object_to_array($val):$val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	//发送容联短信接口
    public function send_msg($mobile,$params,$template_id){
        include_once(__DIR__ ."/CCPRestSmsSDK.php");
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        $accountSid= '8aaf0708570865c0015727749785017e';
        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        $accountToken= '1f94766ec8934f2fad3501b35b4fce6a';
        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        $appId='8a216da85e6fff2b015e76b391170400';
        //请求地址
        $serverIP='app.cloopen.com';
        //请求端口，生产环境和沙盒环境一致
        $serverPort='8883';
        //REST版本号，在官网文档REST介绍中获得。
        $softVersion='2013-12-26';

        // 初始化REST SDK
        $rest = new REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $result = $rest->sendTemplateSMS($mobile,$params,$template_id);
        if($result == NULL ) {
            return false;
        }
        if($result->statusCode!=0) {
            return false;
        }else{
            return true;
        }
    }
}