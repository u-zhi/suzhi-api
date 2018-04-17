<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <link rel="shortcut icon" href="/images/logo1.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="/css/common.css"/>
    <link rel="stylesheet" type="text/css" href="/v1/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/v1/css/font.css"/>
    <link rel="stylesheet" type="text/css" href="/v1/css/xadmin.css"/>
    <script type="text/javascript" src="/js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="/js/common.js"></script>
    <script type="text/javascript" src="/v1/layui.js"></script>
    <script type="text/javascript" src="/v1/js/xadmin.js"></script>
</head>
<body>
<script type="text/javascript">
    (function(){
        layui.use(['layer'], function () {
            var layer=layui.layer;
            var text='<present name="message"><div style="padding: 20px 100px;color: red"><?php echo($message); ?></div><else/><div style="padding: 20px 100px;color: red"><?php echo($error); ?></div></present>';
            layer.open({
                type: 1,
                skin: 'kefu-skin',
                title: "提示"
                , offset: "auto" //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                , id: 'layerDemo' + "" //防止重复弹出
                , content: text
                , btnAlign: 'c' //按钮居中
                , shade: 0 //不显示遮罩
                ,time:3000
                , yes: function () {
                    layer.closeAll();
                },end:function () {
                    location.href = "<?php echo($jumpUrl); ?>";
                }
            });
        });
    })();
</script>
</body>
</html>