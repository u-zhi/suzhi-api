<?php
//通用配置
return array(
    //路由配置
    'URL_ROUTER_ON'=>true, //开启路由
    'URL_ROUTE_RULES'=>array( //路由规则
        // '/^my\/(\w+)$/'=>'Index/index?name=:1',
        array('/^v1\/companys.*?$/','v1/company/lists','','put'), //kk=23&jj=87 其它地址参数
        array('/^v1\/msg$/','v1/user/sms','','post'), //发送短信
        array('/^v1\/register$/','v1/user/register','','post'), //注册
        array('/^v1\/login$/','v1/user/login','','post'), //登录
        array('/^v1\/open_city$/','v1/city/open_city_list','','get'), //首页获取开通城市列表
        array('/^v1\/task/jober_lists/','v1/task/jober_lists','','get'), //获取求职者首页列表-求职者工作列表
        array('/^v1\/user\/job_user_info$/','v1/user/job_user_info','','get'), //查看简历---
        array('/^v1\/user\/job_user_info_edit$/','v1/user/job_user_info_edit','','post'), //求职者编辑简历---post--头像-基本信息
        array('/^v1\/user\/work_experience$/','v1/user/work_experience','','post'), //求职者编辑简历---求职者工作简历添加和编辑-post
        array('/^v1\/user\/work_experiences$/','v1/user/work_experience_row','','get'), //求职者工作经历(单条)
        array('/^v1\/user\/work_list_del$/','v1/user/work_list_del','','post'), //求职者编辑简历---求职者工作简历--删除-post
        array('/^v1\/user\/tag_list$/','v1/user/tag_list','','get'), //求职者技能标签列表
        array('/^v1\/user\/tag_add$/','v1/user/tag_add','','post'), //求职者技能标签-post
        array('/^v1\/user\/tag_list$/','v1/user/tag_list','','get'), //求职者技能标签列表
        array('/^v1\/user\/tag_del$/','v1/user/tag_del','','post'), //求职者技能标签删除
        array('/^v1\/user\/job_row$/','v1/user/job_row','','post'), //求职者编辑简历---期望工作-post
        array('/^v1\/user\/self_intro_edit$/','v1/user/self_intro_edit','','post'), //求职者编辑简历---自我描述
        array('/^v1\/image\/upload$/','v1/image/upload','','post'), //图片上传
        array('/^v1\/image\/base64_url$/','v1/image/base64_url','','get'), //获取图片base64位地址
        array('/^v1\/user\/photo$/','v1/user/user_avatar_url','','post'), //保存用户头像
        array('/^v1\/image\/create_thumb$/','v1/image/create_thumb','','get'), //缩略图
        array('/^v1\/banner\/banner_list$/','v1/banner/banner_list','','get'), //banenr 列表
        array('/^v1\/fair\/fair_lists$/','v1/fair/fair_lists','','get'), //求职者-招聘会列表
        array('/^v1\/fair\/base_info$/','v1/fair/base_info','','get'), //求职者-招聘会基本信息
        array('/^v1\/fair\/jobfair_task_profile$/','v1/fair/jobfair_task_profile','','get'), //求职者-招聘会页面---招聘会职位
        array('/^v1\/fair\/join_fair$/','v1/fair/join_fair','','post'), //求职者-参加招聘会
        array('/^v1\/task\/task_one$/','v1/task/task_one','','get'), //获取任务详情-求职端工作详情-已投递
        array('/^v1\/user\/post_task_add$/','v1/user/post_task_add','','post'), //求职者-投递简历
        array('/^v1\/task\/has_join_task$/','v1/task/has_join_task','','get'), //求职者是否参与了某职位
        array('/^v1\/task\/occupation_type$/','v1/task/occupation_type','','get'), //职位类别
        array('/^v1\/user\/message$/','v1/user/message','','get'), //猎头消息中心--求职者消息中心
        array('/^v1\/task\/hunter_lists$/','v1/task/hunter_lists','','get'), //获取顾问首页列表-顾问任务
        array('/^v1\/user\/user_balance$/','v1/user/user_balance','','get'), //顾问-当前余额--提现申请展示
        array('/^v1\/user\/company_staff$/','v1/user/company_staff','','get'), //顾问-我的公司内推职位
        array('/^v1\/talent\/talentlist$/','v1/talent/talentlist','','get'), //顾问-我的人才库
        array('/^v1\/user\/statements$/','v1/user/statements','','get'), //顾问-当前余额--收入明细
        array('/^v1\/user\/withdraw_order$/','v1/user/withdraw_order','','get'), //顾问-当前余额--提现记录
        array('/^v1\/task\/join_progress$/','v1/task/join_progress','','get'), //求职简历投递进程相关信息
        array('/^v1\/user\/headhunter_extra_add$/','v1/user/headhunter_extra_add','','post'), //顾问-资料存入
        array('/^v1\/user\/headhunter_extra_info$/','v1/user/headhunter_extra_info','','get'), //顾问-猎头资料获取
        array('/^v1\/user\/jober_progress$/','v1/user/jober_progress','','post'), //求职者--求职进程 -及操作-post
        array('/^v1\/user\/hunter_progress$/','v1/user/hunter_progress','','post'), //猎头--求职进程 -及操作-post
        array('/^v1\/order\/refuse_interview$/','v1/order/refuse_interview','','post'), //订单流程--拒绝面试
        array('/^v1\/order\/accept_interview$/','v1/order/accept_interview','','post'), //订单流程--接受面试
        array('/^v1\/order\/cancel_interview$/','v1/order/cancel_interview','','post'), //订单流程--取消面试
        array('/^v1\/order\/refuse_position$/','v1/order/refuse_position','','post'), //订单流程--拒绝就职
        array('/^v1\/order\/accept_position$/','v1/order/accept_position','','post'), //订单流程--确认就职
        array('/^v1\/task\/head_receive$/','v1/task/head_receive','','post'), //顾问领取的任务
        array('/^v1\/task\/head_cancle$/','v1/task/head_cancle','','post'), //顾问取消任务
        array('/^v1\/task\/company_apply_interview$/','v1/task/company_apply_interview','','get'), //企业主动发起邀面试列表
        array('/^v1\/user\/user_withdraw_order$/','v1/user/user_withdraw_order','','post'), //顾问-提现-提现方式
        array('/^v1\/talent\/join_mytalent$/','v1/talent/join_mytalent','','post'), //顾问-邀请加入人才库
        array('/^v1\/talent\/hunter_join_mytalent$/','v1/talent/hunter_join_mytalent','','post'), //顾问-邀请加入投递职位
        array('/^v1\/talent\/hunter_join_mytalent1$/','v1/talent/hunter_join_mytalent1','','post'), //顾问-邀请加入投递职位
        array('/^v1\/talent\/join_hunter_talent$/','v1/talent/join_hunter_talent','','post'), //顾问-邀请的人才库加入
        array('/^v1\/task\/type_classify$/','v1/task/type_classify','','get'), //公司行业筛选项
        array('/^v1\/wx\/config/','v1/wx/config','','post'), //微信配置
        array('/^v1\/user\/info$/','v1/user/info','','get'), //用户进本信息
        array('/^v1\/location$/','v1/wx/location','','get'), //获取城市定位






        array('/^v1\/tasks$/','v1/task/lists','','get'), //获取顾问首页列表-顾问任务
        array('/^v1\/task/jober_lists/','v1/task/jober_lists','','get'), //获取求职者首页列表-求职者工作列表
        array('/^api.php\/v1\/city\/open_city_list$/','api.php/v1/city/open_city_list','','get'), //首页获取开通城市列表
        array('/^api.php\/v1\/task\/task_one$/','api.php/v1/task/task_one','','get'), //获取任务详情-求职端工作详情-已投递
        array('/^api.php\/v1\/user\/info$/','api.php/v1/user/info','','get'), //我的
        array('/^api.php\/v1\/user\/job_user_info$/','api.php/v1/user/job_user_info','','get'), //查看简历---
        array('/^api.php\/v1\/user\/company_staff$/','api.php/v1/user/company_staff','','get'), //顾问-我的公司内推职位
        array('/^api.php\/v1\/user\/message$/','api.php/v1/user/message','','get'), //顾问-消息中心
        array('/^api.php\/v1\/talent\/join_mytalent$/','api.php/v1/talent/join_mytalent','','get'), //顾问-邀请加入人才库
        array('/^api.php\/v1\/talent\/talentlist$/','api.php/v1/talent/talentlist','','get'), //顾问-我的人才库
        array('/^api.php\/v1\/order\/order_problems$/','api.php/v1/order/order_problems','','get'), //顾问-任务中心
        array('/^api.php\/v1\/user\/user_balance$/','api.php/v1/user/user_balance','','get'), //顾问-当前余额--提现申请展示
        array('/^api.php\/v1\/user\/statements$/','api.php/v1/user/statements','','get'), //顾问-当前余额--收入明细
        array('/^api.php\/v1\/user\/withdraw_order$/','api.php/v1/user/withdraw_order','','get'), //顾问-当前余额--提现记录
        array('/^api.php\/v1\/user\/balance_withdraw$/','api.php/v1/user/balance_withdraw','','get'), //顾问-提现申请的姓名用户和账号
        array('/^api.php\/v1\/user\/user_withdraw_profile$/','api.php/v1/user/user_withdraw_profile','','post'), //顾问-提现的方式
        array('/^api.php\/v1\/user\/user_withdraw_order$/','api.php/v1/user/user_withdraw_order','','post'), //顾问-提现
        array('/^api.php\/v1\/user\/headhunter_extra_add$/','api.php/v1/user/headhunter_extra_add','','post'), //顾问-资料存入
        array('/^api.php\/v1\/user\/headhunter_extra_info$/','api.php/v1/user/headhunter_extra_info','','get'), //顾问-猎头资料获取

        array('/^api.php\/v1\/fair\/fair_lists$/','api.php/v1/fair/fair_lists','','get'), //求职者-招聘会列表
        array('/^api.php\/v1\/fair\/join_fair$/','api.php/v1/fair/join_fair','','get'), //求职者-参加招聘会
        array('/^api.php\/v1\/fair\/jobfair_task_profile$/','api.php/v1/fair/jobfair_task_profile','','get'), //求职者-招聘会页面---招聘会职位
        array('/^api.php\/v1\/user\/jobhunter_interview_list$/','api.php/v1/user/jobhunter_interview_list','','get'), //求职者-面试邀请页面--列表
        array('/^api.php\/v1\/user\/jobhunter_status$/','api.php/v1/user/jobhunter_status','','post'), //求职者-求职者被邀请面试的操作状态
        array('/^api.php\/v1\/user\/post_task_add$/','api.php/v1/user/post_task_add','','post'), //求职者-投递简历-及判断是否有投递简历的状态
        array('/^api.php\/v1\/user\/job_user_info_edit$/','api.php/v1/user/job_user_info_edit','','post'), //求职者编辑简历---post--头像-基本信息-自我描述
        array('/^api.php\/v1\/user\/job_row$/','api.php/v1/user/job_row','','post'), //求职者编辑简历---期望工作-post
        array('/^api.php\/v1\/user\/work_experience$/','api.php/v1/user/work_experience','','post'), //求职者编辑简历---求职者工作简历添加和编辑-post
        array('/^api.php\/v1\/user\/work_list_del$/','api.php/v1/user/work_list_del','','post'), //求职者编辑简历---求职者工作简历--删除-post
        array('/^api.php\/v1\/user\/tag_add$/','api.php/v1/user/tag_add','','post'), //求职者技能标签-post
        array('/^api.php\/v1\/user\/tag_list$/','api.php/v1/user/tag_list','','get'), //求职者技能标签列表
        array('/^api.php\/v1\/user\/tag_del$/','api.php/v1/user/tag_del','','get'), //求职者技能标签删除
        array('/^api.php\/v1\/banner\/banner_list$/','api.php/v1/banner/banner_list','','get'), //banenr 列表
        array('/^api.php\/v1\/task\/qrcode$/','api.php/v1/task/qrcode','','post'), //二维码

        /*剩余接口*/
        array('/^api.php\/v1\/user\/jober_progress$/','api.php/v1/user/jober_progress','','post'), //求职者--求职进程 -及操作-post

    ),
);