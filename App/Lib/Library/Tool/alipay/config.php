<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016122904721123",

		//商户私钥
		'merchant_private_key' => "MIIEowIBAAKCAQEAweR8Xg8XKCbtmmDPjip5WLYT96ZqDL1W/FOFnT3AHJnnVZFz3t1ce0PlgXUnMwLrCYk0X15la4HczsZs2lxTymlBZdNVO4VWa34oyyv6800S9n6OVYwVfxcLTcmRI66jgUAWcland4xSAa83qxRGvdwWg6RjN+Beq1GsbriXJpe/Hlsq1/coMxcncAECkRICpQpExT0pt99F9wDXlBwTXqJ9WEerdPp8SqJKwF97+m54s75SIiAxkCBCVHSc1FwIEaguSWxKmDtfiPjnoYlRv0ZAan0LoMRXvv25MBqTA2YAmTDTpVJBMZivToCV0z7Te7ywVLDxHgY8SL8imefqKwIDAQABAoIBAGLitMsDxzujHurtFO3cm6aytdxH8y4sIoDt1+cp5yCvHVx2ojG3k8MKDbLInQVXJ6flYeCBtBfwUzzh1gJHtDQ3GjNkZMPGbRzFauiztXkAhftQ8CaRGyWGCy3Q70TTfHAez1Pg/efqmKhjRHDsFmgeJx1caX3F4IJfVSHi4rO7LA911PpqEDl8uA95Wb8GBhowTCb+XQsm+fGCJZLS7SWqYpmh/rdV6UXFs1wRLOryEusjiI2b+Q2VRlMH5NRQBSRVXewiQWPc//VxPjOAy0JRtYCiW65z0ou/a0tjAkic8X+rR00QC8n9TC9ksBitpQk/HiQ/UDVEu6yLLb1wj4ECgYEA+Je2J0as5BY4Gf80mtAlIJk0TOhE/CsmYWfQH7yBQodUHQoSas0+Uemuj/b2/a9b/fu7swCX0PUsrMR+/mSqdyG5dYTX9aN1EFUxFro23mfyzS1bwSCuH6Lu7DcMB9Qn/bPWJ6JmDzYKhNKQlJ4tD7eCpht5htt2g87r4EHZ40UCgYEAx6uEQo39GkwTPtsofWFXNtjOsHwzF4p8UNNbMAW9X8mF8vaNTLdDVHH+cZzjlXv53SUmYIA6BN3/UZudcpf5bbulqqnc5gEqCHlgufN2PmCKSokMfH5AnNikYFBSso44yXKNebQRoHFgKeWQ6ZNymBZNGcLbGnJwdsf1IDXBNq8CgYBlJ/brTIsV1STHD14skP5KoYzyrqFDq5tWj5PVFjPTCpZjqrGL3DC7zEdINcqTuEPKuiDK2jbsxJeFRgAXLIdhKsv40jG+tuDv4Hq1MNka71mRvB9WGyI/pWFrpYqztNUBG6jNP/wfvHHTUouarjLP7nCvfwaYpb04LX82JhWvsQKBgAbTFfIwaDY942qCkx+19AUr4+SkWhqz4QviLXu2toJPoQRs6Od2XBbGzquTcTPqyN+GJYmm2FTSQafIadlA2IWnpEHxDFvQLl+bxnKUn7YCFrf1dzfKGOtDfFrOZAU2VzqLb8p5HSEevuqvrgRNxdzs5jW7M0H0J2Pn8bjoHXKBAoGBAPF3JCvpO599GPCwP0aBAJ/Tk4c8axQFZpomCjo1c/HiwFXFqo7qLURvv0FOnEO+mqxxnE0POX/fTHecDk/WdpVHjZAVjM5GVShyhcMOX48EcNfEF+cuDWyMFOG/wGxQ96DKNpdxosdfbQXxBBZ6zQAQnukFwga9FBsW1XHsH0Nc",
		
		//异步通知地址
		'notify_url' => "http://ccc.test.com/alipay/notify_url.php",
		
		//同步跳转
		'return_url' => "http://ccc.test.com/alipay/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAli01MtEyEdMN9+68ZxX06OUHCJRK3m0hnpR89tIJzCdAbB60uWX+23I4gAgO3Yg/Dg9pFKhz1fpyBL66UpSZg7g3/6jaoPW392Egv38V34t4fE4c8Ws5v9csTCRGk5l0EcqGWlGgvF1GY9OuFyD4JWNNalIJwMvVZZUJ4Y+xbzPfmd1vraVj7i2rWOkXZyTriXGlDOXiwYZkt9ZbIGlMKkz2M2bHIitlNbvz1vG4ocJr0fKd+fQdQ3uMO0J0Tm9uxk+KtaZSwHhIL9hlmDKDIeXh4I5+tfJqqEvvmMf8ftU7Nk6iFZZgrVWDDvrygztL0CmTxF80Tp6jDjJZRTvH9QIDAQAB",
);