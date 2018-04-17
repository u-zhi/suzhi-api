<?php
/*
 * 支付宝支付接口
 */
class Tool_Alipay{

    public function __construct(){

    }

    /*
     * 发起跳转支付
     * $order_number 订单号
     * $subject 订单名称
     * $money 订单金额
    */
    public function pay($order_number,$order_name,$money){
        require_once dirname(__FILE__).'/alipay/config.php';
        require_once dirname(__FILE__).'/alipay/pagepay/service/AlipayTradeService.php';
        require_once dirname(__FILE__).'/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $order_number;

        //订单名称，必填
        $subject = $order_name;

        //付款金额，必填
        $total_amount = $money;

        //商品描述，可空
        $body = '';

        //构造参数
        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,C("alipay_return_url"),C("alipay_notify_url"));

        //输出表单
        var_dump($response);
    }

    //异步通知校验(同步回调同理,只是用get)
    public function notify_check($type="notify"){
        require_once dirname(__FILE__).'/alipay/config.php';
        require_once dirname(__FILE__).'/alipay/pagepay/service/AlipayTradeService.php';

        if($type=="notify"){
            $arr=$_POST;
        }elseif($type=="return"){
            $arr=$_GET;
        }
        if(isset($arr["_URL_"])){
            unset($arr["_URL_"]);
        }
        $alipaySevice = new AlipayTradeService($config);
        $result = $alipaySevice->check($arr);
        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            //商户订单号
            $out_trade_no = $arr['out_trade_no'];
            //支付宝交易号
            $trade_no = $arr['trade_no'];
            //交易状态
            $trade_status = $arr['trade_status'];
            //金额
            $total_amount=$arr["total_amount"];
            //appid进行校验
            $app_id=$arr['app_id'];
            if(!$app_id || $app_id!=$config["app_id"]){
                echo "fail";exit;
            }
            $data=array(
                'order_number'=>$out_trade_no,
                'trade_no'=>$trade_no,
                'money'=>$total_amount,
            );
            if($type=="return"){
                return $data;
            }
            if($trade_status == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                return $data;
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                return $data;
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
            }else{
                echo "fail";exit;
            }
        } else {
            echo "fail";exit;
        }
    }
}
