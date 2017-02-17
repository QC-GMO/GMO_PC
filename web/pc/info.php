<?php 
header('content-Type: text/html; charset=utf-8');
/* 
 * 完善资料页
 *  */
include_once('../web_include/init.php');
//检测当前用户是否登录
again();
$action = trim($_GET['act']);
//完善信息，插入数据库
if($action=='perfect'){
    $username=trim($_POST['username']);
    if(empty($username)){ajaxReturn(0,'请填写用户名');}
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `username`='".$username."'")>0){
        ajaxReturn(0,'该用户名已被使用');
    }
    $email=trim($_POST['email']);
    if(empty($email)){ajaxReturn(0,'请填写邮箱');}
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `email`='".$email."'")>0){
        ajaxReturn(0,'该邮箱已被使用');
    }
    if(empty($_POST['Y']) || empty($_POST['M']) || empty($_POST['D'])){ajaxReturn(0,'请选择生日');}
    $birthday=$_POST['Y'].'-'.$_POST['M'].'-'.$_POST['D'];
    if(empty($_POST['sex'])){ajaxReturn(0,'请选择性别');}
    if(empty($_POST['province_id']) || empty($_POST['city_id']) || empty($_POST['province']) || empty($_POST['city'])){ajaxReturn(0,'请选择省市');}
    if(empty($_POST['address'])){ajaxReturn(0,'请填写详细地址');}
    
    $sql="UPDATE `".$db_pre."member` SET 
            `username`='" .$username. "',
            `birthday`='" .$birthday. "',
            `sex`='" .$_POST['sex']. "',
            `name`='" .$_POST['name']. "',
            `province_id`='" .$_POST['province_id']. "',
            `city_id`='" .$_POST['city_id']. "',
            `province`='" .$_POST['province']. "',
            `city`='" .$_POST['city']. "',
            `address`='" .trim($_POST['address']). "',
            `email`='" .$email. "',
            `status`='2'
            WHERE `mobile`='".$_COOKIE['member_mobile']."'";
    if($db->query($sql)){
        setcookie('member_name',$username,time()+86400,'/');
        //信息完善成功后向该会员发送email
        $mailsubject='【Z.com Research (最网e调查)】感谢您的注册！会员注册成功通知邮件';
        $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>

                                                        【Z.com Research (最网e调查)】https://www.zcom.asia<br><br>
                                                        亲爱的'.$username.'<br><br>
        
                                                        您好，这里是技慕驿动市场调查（上海）有限公司在线调研及活动的会员平台—— Z.com Research (最网e调查)。<br>
                                                        感谢您的注册。                   <br>                                                                                                     
                                                        您注册所使用的手机号码及密码将用于登录最网e调查网站，请勿遗忘。          <br>                                                                                       
                                                        账号（手机号）　：'.$_COOKIE['member_mobile'].'<br>
                                                        密码      ：【由于个人信息保护的原因，密码将不被显示】<br><br>
        
                                                        　　≫≫　来通过最网e调查参加活动，与我们一起互动吧！ <br><br><br>
                                                            
                                                            ※本邮件是送信专用邮件<br>
                                                        我们无法回复本邮件，敬请谅解。<br>
                                                        <hr />
                                                          ■Z.com Research (最网e调查)■　 
                                                       <hr />
                                                       ◆公司介绍： http://www.gmo-e-lab.com <br>
                                                       ◆会员网站： https://www.zcom.asia <br>
                                                       ◆使用方法：https://www.zcom.asia/pc/about.php?cid=26 <br>
                                                       ◆回答问卷须知：https://www.zcom.asia/pc/about.php?cid=28 <br>
                                                       ◆常见问题：https://www.zcom.asia/pc/issue.php?cid=27 <br>
                                                       ◆会员条款： https://www.zcom.asia/pc/about.php?cid=31 <br>
                                                       ◆积分兑换方法： https://www.zcom.asia/pc/about.php?cid=35 <br><br>
                                                        
                                                        运营：技慕驿动市场调查（上海）有限公司<br>
                                                        地址：中国上海市西藏中路336号华旭国际大厦2207室<br>
                           URL ：http://www.gmo-e-lab.com<br>
                           <hr />
                           (C) Since 2016 GMO E-Lab Marketing Research (Shanghai) CO, LTD.';
        send_mail('smtp',$email,$mailsubject,$mailbody);
        ajaxReturn(1,'成功完善信息');
    }else{
        ajaxReturn(0,'完善失败，请重试');
    }
}elseif($action=='getcity'){
    $city=$db->getall("select * from db_city where p_id=".$_GET['p_id']);
    exit(json_encode($city));
}


$menu_info1 = array(
    'class_title_cn' => $web_config['Web_SiteTitle_cn'],
    'class_keywords_cn' => $web_config['Web_Keywords_cn'],
    'class_description_cn' => $web_config['Web_Description_cn']
);
$smarty->assign('menu_info1',$menu_info1);
$info=$db->getone("select status from `".$db_pre."member` WHERE `mobile`='".$_COOKIE['member_mobile']."'");
if($info['status']!='1'){echo "<script> alert('您已完善信息');window.history.back();</script>";}
//公共部分导航，头部
//include('./common.php');
$province=$db->getall("select * from db_province where 1 order by id asc");
$smarty->assign('province',$province);
$menu_info['class_name_cn']="完善个人信息";
$smarty->assign('menu_info',$menu_info);

$smarty->display('pc/info.html');
