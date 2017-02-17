<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$action = trim($_GET['act']);

if($action=='login'){
    $mobile   = daddslashes(trim(strip_tags($_POST['mobile'])));
    $password = daddslashes(trim(strip_tags($_POST['password'])));
    $user_data = $db->getone("select id,username,mobile,password,status from db_member where mobile='".$mobile."'");
    if(empty($user_data)){
        error_log_web('会员登录', 2, '会员进行登录','不存在此手机号码');
        ajaxReturn(0,'不存在此手机号码');
    }elseif($user_data['status']==3){
        error_log_web('会员登录', 2,'id为'.$user_data['id'].'的会员进行登录','您已注销该账户');
        ajaxReturn(0,'您已注销该账户');
    }elseif($user_data['status']==4){
        error_log_web('会员登录', 2, 'id为'.$user_data['id'].'的会员进行登录','该账户已被强制注销');
        ajaxReturn(0,'该账户已被强制注销');
    }else{
        if(md5($password)!=$user_data['password']){
            error_log_web('会员登录', 2, 'id为'.$user_data['id'].'的会员进行登录','密码错误');
            ajaxReturn(0,'密码错误');
        }else{
            setcookie('member_name',$user_data['username'],time()+86400,'/');
            setcookie('member_mobile',$user_data['mobile'],time()+86400,'/');
            error_log_web('会员登录', 1, 'id为'.$user_data['id'].'的会员进行登录','登录成功');
            //成功登陆，更新最后登录时间
            $db->query("UPDATE `".$db_pre."member` SET `lastlogin_time`='".date('Y-m-d H:i:s',time())."' WHERE `mobile`='".$user_data['mobile']."'");
            if($user_data['status']==1){//未完善信息
                ajaxReturn(2,'');
            }
            ajaxReturn(1,'成功');
            /* if(empty($_POST['url'])){
                echo "<script>location.href='index.php'</script>";
                exit();
            }else{
                echo "<script>location.href='".$_POST['url']."'</script>";
                exit();
            } */
        }
    }
}elseif($action=='logout'){
    setcookie('member_name','',time()-86400,'/');
    setcookie('member_mobile','',time()-86400,'/');
    echo "<script>location.href='index.php'</script>";
    exit();
}elseif($action=='yz'){
    $user_data = $db->getone("select id,password,birthday from db_member where mobile='".$_COOKIE['member_mobile']."'");
    $birthday=date('Y-m-d',strtotime($_POST['y'].'-'.$_POST['m'].'-'.$_POST['d']));
    if($user_data['birthday']!=$birthday){
        error_log_web('个人身份验证', 2, 'id为'.$user_data['id'].'的会员进行个人身份验证','生日验证失败');
        ajaxReturn(0,'生日验证失败');
    }
    if($user_data['password']!=md5($_POST['password'])){
        error_log_web('个人身份验证', 2, 'id为'.$user_data['id'].'的会员进行个人身份验证','密码验证失败');
        ajaxReturn(0,'密码验证失败');
    }
    error_log_web('个人身份验证', 1, 'id为'.$user_data['id'].'的会员进行个人身份验证','验证成功');
    ajaxReturn(1,'');
}

$menu_info1 = array(
    'class_title_cn' => $web_config['Web_SiteTitle_cn'],
    'class_keywords_cn' => $web_config['Web_Keywords_cn'],
    'class_description_cn' => $web_config['Web_Description_cn']
);
$smarty->assign('menu_info1',$menu_info1);
//include('./common.php');
$menu_info['class_name_cn']="会员登录";
$url=($_GET['url']=='up')?'':$_SERVER['HTTP_REFERER'];
$smarty->assign('menu_info',$menu_info);
$smarty->assign('url',$url);
$smarty->display('pc/login.html');