<?php
////生成token
//function create_token($user_id){
//    import("ORG.Crypt.Crypt");
//    $crypt=new Crypt();
//    $str=json_encode(array(
//        "user_id"=>$user_id,
//        "time"=>time(),
//    ));
//    return base64_encode($crypt->encrypt($str,"ranmiaozai_mutouren"));
//}
////解析token
//function decode_token($token){
//    import("ORG.Crypt.Crypt");
//    $crypt=new Crypt();
//    $str=$crypt->decrypt(base64_decode($token),"ranmiaozai_mutouren");
//    if($str && $result=json_decode($str,true)){
//        if($result["time"]>time()-30*86400){
//            return $result["user_id"];
//        }
//    }
//    return false;
//}