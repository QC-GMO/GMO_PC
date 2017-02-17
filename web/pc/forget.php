<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$lang = 'cn';
$action = trim($_GET['act']);

if($action=='forget'){
    $mobile=trim($_POST['mobile']);
    if($_COOKIE['forget_mobile']!=$mobile){
        error_log_web('忘记密码', 2, '会员进行密码重置','该手机号码与获取验证码的手机号不一致');
        ajaxReturn(0,'该手机号码与获取验证码的手机号不一致');
    }
    $password=md5(trim($_POST['password']));
    $user_data = $db->getone("select id,password from db_member where mobile='".$mobile."'");
    if($user_data['password']==$password){
        error_log_web('忘记密码', 2, 'id为'.$user_data['id'].'的会员进行密码重置','请设置与当前密码不同的新密码');
        ajaxReturn(0,'请设置与当前密码不同的新密码');
    }
    $sql="UPDATE `".$db_pre."member` SET
        `password`='" .$password. "'
        WHERE `mobile`='".$mobile."'";
    if($db->query($sql)){
        setcookie('SMSCode','',time()-900,'/');//验证码使用过后注销。
        setcookie('forget_mobile','',time()-900,'/');//验证码使用过后注销获取手机。
        error_log_web('忘记密码', 1, 'id为'.$user_data['id'].'的会员进行密码重置','密码修改成功');
        ajaxReturn(1,'密码修改成功');
    }else{
        error_log_web('忘记密码', 3, 'id为'.$user_data['id'].'的会员进行密码重置',mysql_error());
        ajaxReturn(0,'修改失败，请重试');
    }
}

$menu_info1 = array(
    'class_title_cn' => $web_config['Web_SiteTitle_cn'],
    'class_keywords_cn' => $web_config['Web_Keywords_cn'],
    'class_description_cn' => $web_config['Web_Description_cn']
);
$smarty->assign('menu_info1',$menu_info1);
//include('./common.php');
$menu_info['class_name_cn']="重置密码";
$smarty->assign('menu_info',$menu_info);
$smarty->display('pc/forget.html');