<?php
function abdeff()
{
    echo "1233344";
}

function request_post($url = '', $param = '')
{
    if (empty($url) || empty($param)) {
        return false;
    }

    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}

function location()
{
    $data = json_decode(request_post("https://api.map.baidu.com/location/ip", array("ip" => get_client_ip(), "ak" => "yWagtfxs1qDgVQedR73c8bCS")));
    if (!session("location_city")) {
        if (isset($data->status) && $data->status == 0) {
            session("location_city", $data->content->address_detail->city);
        } else {
            session("location_city", "杭州市");
        }

    }
}

function getAgeByBirthday($birthday) {
    $age = strtotime($birthday);
    if($age === false){
        return false;
    }
    list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
    $now = strtotime("now");
    list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
    $age = $y2 - $y1;
    if((int)($m2.$d2) < (int)($m1.$d1))
        $age -= 1;
    return $age;
}

function formatDatetime($datetime, $format='Y.m.d') {
    $ts = strtotime($datetime);
    return date($format, $ts);
}