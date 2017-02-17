<?php
header('content-Type: text/html; charset=utf-8');
/*  
 * 手机注册信息
 * */
include_once('../web_include/init.php');
$action = trim($_GET['act']);

if($action=='getsession'){
    $code=trim($_POST['code']);
    if($code==$_COOKIE['SMSCode']){
        ajaxReturn(1,'正确');
    }else{
        ajaxReturn(0,'验证码错误');
    }
}elseif ($action=='register'){
    $mobile=trim($_POST['mobile']);
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$mobile."'")>0){
        error_log_web('会员注册', 2, '会员进行注册','该手机号码已被注册');
        ajaxReturn(0,'该手机号码已被注册');
    }
    $password=md5(trim($_POST['password']));
    $add_time=date('Y-m-d H:i:s',time());
    $sql = "INSERT INTO `".$db_pre."member`(`mobile`,`password`,`add_time`)VALUES(
    '".$mobile."','".$password."','".$add_time."')";
    if ($db->query($sql)){
        setcookie('SMSCode','',time()-900,'/');//验证码使用过后注销。
        setcookie('member_mobile',$_POST['mobile'],time()+86400,'/');
        error_log_web('会员注册', 1, '会员进行注册','注册成功');
        ajaxReturn(1,'注册成功');
    }else{
        error_log_web('会员注册', 3, '会员进行注册',mysql_error());
        ajaxReturn(0,'提交失败，请重试');//mysql_error()
    }
}elseif($action=='is_mobile'){
    $mobile=trim($_POST['mobile']);
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$_POST['mobile']."'")>0){
        ajaxReturn(0,'该手机号码已被注册');
    }else{
        ajaxReturn(1,'1');
    }
}
$menu_info1 = array(
    'class_title_cn' => $web_config['Web_SiteTitle_cn'],
    'class_keywords_cn' => $web_config['Web_Keywords_cn'],
    'class_description_cn' => $web_config['Web_Description_cn']
);
$smarty->assign('menu_info1',$menu_info1);

//公共部分导航，头部
//include('./common.php');

$menu_info['class_name_cn']="会员注册";
$smarty->assign('menu_info',$menu_info);

$smarty->display('pc/register.html');