<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
// error_reporting(7);
//************************
//检测当前用户是否登录
again();
$action = trim($_GET['act']);
//修改个人信息
if($action=='upinfo'){
    $user = $db->getone("select id from db_member where mobile='".$_COOKIE['member_mobile']."'");
    //验证用户名
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile` not in (".$_COOKIE['member_mobile'].") and `username`='".$_POST['username']."'")>0){
        error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','该用户名已被使用');
        ajaxReturn(0,'该用户名已被使用');
    }
    if($_COOKIE['member_mobile']!=$_POST['mobile']){
        if(empty($_COOKIE['SMSCode'])){
            error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','验证码已过期');
            ajaxReturn(0,'验证码错误或已过期');
        }
        if($_COOKIE['SMSCode']!=$_POST['code']){
            error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','验证码错误');
            ajaxReturn(0,'验证码错误或已过期');
        }
        if(empty($_POST['mobile'])){
            error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','手机号码不能为空');
            ajaxReturn(0,'手机号码不能为空');
        }
        if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$_POST['mobile']."'")>0){
            error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','该手机号码已被使用');
            ajaxReturn(0,'该手机号码已被使用');
        }
    }
    $email=trim($_POST['email']);
    if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile` not in (".$_COOKIE['member_mobile'].") and `email`='".$email."'")>0){
        error_log_web('修改个人信息', 2, 'id为'.$user['id'].'会员进行修改个人信息','该邮箱已被使用');
        ajaxReturn(0,'该邮箱已被使用');
    }
    $sql="UPDATE `".$db_pre."member` SET 
            `username`='" .$_POST['username']. "',
            `name`='" .$_POST['name']. "',
            `province`='" .$_POST['province']. "',
            `city`='" .$_POST['city']. "',
            `province_id`='" .$_POST['province_id']. "',
            `city_id`='" .$_POST['city_id']. "',
            `address`='" .trim($_POST['address']). "',
            `email`='" .$email. "',"
            .(($_COOKIE['member_mobile']==$_POST['mobile'])?"":"`mobile`='".$_POST['mobile']."',")
            ."`own_up`='".date('Y-m-d H:i:s')."'
            WHERE `mobile`='".$_COOKIE['member_mobile']."'";
    if($db->query($sql)){
        if($_COOKIE['member_mobile']!=$_POST['mobile']){
            setcookie('SMSCode','',time()-900,'/');//验证码使用过后注销。
            setcookie('member_mobile',$_POST['mobile'],time()+86400,'/');
        }
        error_log_web('修改个人信息', 1, 'id为'.$user['id'].'会员进行修改个人信息','修改成功');
        //信息修改成功后向该会员发送email
        $mailsubject='【Z.com Research (最网e调查)】您的注册信息变更成功通知邮件';
        $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
    
                                                        亲爱的'.$_POST['username'].'<br><br>
    
                                                        您好，这里是技慕驿动市场调查（上海）有限公司在线调研及活动的会员平台—— Z.com Research (最网e调查)。<br>
                                                　　 您的注册信息变更已成功。<br><br>
                                                
                                                　　 变更后请登录 https://www.zcom.asia 来确认您的变更信息。<br><br>
                                                
                                                　　 感谢您对最网e调查的大力支持！<br><br><br>
    
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
        ajaxReturn(1,'成功修改信息');
    }else{
        error_log_web('修改个人信息', 3, 'id为'.$user['id'].'会员进行修改个人信息',mysql_error());
        ajaxReturn(0,'修改失败，请重试');
    }
}elseif($action=='uppass'){//修改密码
    $user = $db->getone("select id,username,password,email from db_member where mobile='".$_COOKIE['member_mobile']."'");
    if(md5($_POST['pass'])!=$user['password']){
        error_log_web('修改密码', 2, 'id为'.$user['id'].'会员进行修改密码','原密码错误');
        ajaxReturn(0,'原密码错误');
    }
    $sql="UPDATE `".$db_pre."member` SET `password`='" .md5($_POST['password']). "' WHERE `mobile`='".$_COOKIE['member_mobile']."'";
    if($db->query($sql)){
        error_log_web('修改密码', 1, 'id为'.$user['id'].'会员进行修改密码','修改成功');
        //信息完善成功后向该会员发送email
        $mailsubject='【Z.com Research (最网e调查)】您的密码变更成功通知邮件';
        $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
    
                                                        亲爱的'.$user['username'].'<br><br>
    
                                                       您好，这里是技慕驿动市场调查（上海）有限公司在线调研及活动的会员平台—— Z.com Research (最网e调查)。<br>
                                                　　您的密码变更已成功。<br><br>
                                                
                                                　　请登录 https://www.zcom.asia 来确认变更后的登录信息。<br><br>
                                                
                                                　　感谢您对最网e调查的大力支持！<br><br><br>
    
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
        send_mail('smtp',$user['email'],$mailsubject,$mailbody);
        setcookie('member_name','',time()-86400,'/');
        setcookie('member_mobile','',time()-86400,'/');
        ajaxReturn(1,'修改成功，请重新登录');
    }else{
        error_log_web('修改密码', 3, 'id为'.$user['id'].'会员进行修改密码',mysql_error());
        ajaxReturn(0,'修改失败，请重试');
    }
}elseif($action=='send'){//介绍朋友发送邮件
    $user = $db->getone("select id,username,email from db_member where mobile='".$_COOKIE['member_mobile']."'");
    $crypt=encrypt($web_config['Web_Blowfish'],$user['id'].':'.$web_config['Web_panelType'].':'.time());
    $title="【Z.com Research (最网e调查)】■".$user['username']." ■向您发送注册会员邀请！";
    $content=$mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
                       '.$user['username'].'邀请您注册并体验最网e调查~~~<br><br>
                                                                    
                                                                    　　　　■□■＜来自'.$user['username'].'的留言 ＞■□■<br>
                                                                    □┓─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─┏□<br>
                                                                   ┗┛  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┗┛<br>
                           '.$_POST['content'].'<br>
                                                                  ┏┓ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┏┓<br>
                                                                    □┛─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─┗□<br><br>
                           ▼电脑上注册请点击：<br>
                             https://'.$_SERVER['HTTP_HOST'].'/pc/register.php?type=1&number='.$crypt.' <br>
                                    　                               ※请注意，由以上链接以外渠道注册将可能无法收到我们的积分奖励哦。<br><br>
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
    //$content=$_POST['content'].'<br><br>http://'.$_SERVER['HTTP_HOST'].'/pc/cn/register.php?type=1&number='.$crypt;
    if(!empty($_POST['email'])){
        foreach ($_POST['email'] as $v){
            $return=send_mail2($v,$title,$content);
            $str+=$v.',';
            if($return!='success'){
                $info[]=$v;
            }
        }
        //邮件发送成功后给当前用户发
        $title="【Z.com Research (最网e调查)】给好友发送了注册邀请";
        $content=$mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
                                                        亲爱的'.$user['username'].'：<br><br>
                                                        本邀请涵内容如下，由最网e调查向您及您的受邀朋友<'.$str.'>发送。<br>
                                                              <hr />
                       ■邀请函■【Z.com Research (最网e调查)】<br>
                                                            我说、我做，世界更精彩！<br>
                                                        最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia
                                                            <hr />
                      '.$user['username'].'邀请您注册并体验最网e调查~~~<br>
                                                                    　　　　■□■＜来自'.$user['username'].'的留言 ＞■□■<br>
                                                                    □┓─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─┏□<br>
                                                                   ┗┛  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┗┛<br>
                           '.$_POST['content'].'<br>
                                                                  ┏┓ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┏┓<br>
                                                                    □┛─━─━─━─━─━─━─━─━─━─━─━─━─━─━─━─┗□<br><br>
                           ▼电脑上注册请点击：<br>
                             https://'.$_SERVER['HTTP_HOST'].'/pc/register.php?type=1&number='.$crypt.' <br>
                                    　                               ※请注意，由以上链接以外渠道注册将可能无法收到我们的积分奖励哦。<br><br>
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
        send_mail2($user['email'],$title,$content);
        ajaxReturn(1,$info);
    }else{
        ajaxReturn(0,'无邮箱地址');
    }
    
}elseif($action=='zhuxiao'){
    $user = $db->getone("select id,username,password,email,integral from db_member where mobile='".$_COOKIE['member_mobile']."'");
    $sql="SELECT type,number FROM `".$db_pre."order` WHERE uid=".$user['id']." and status=1";
    $order=$db->getall($sql);
    if(!empty($order)){//有申请中的订单时
        foreach ($order as $v){
            if($v['type']==1){//类型是商品的订单拼订单号
                $number_list[]=$v['number'];
            }
        }
        //请求大麦城，同步订单状态，大麦城申请后退会为3
        if(!empty($number_list)){//有商品订单时
            $number_list = implode(",",$number_list);
            $url="http://www.damaicheng.com/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=3&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=3'));
            $result=httpGet($url);
            $result=json_decode($result,true);
            if($result['status']==0){
                error_log_web('注销', 2, 'id为'.$user['id'].'会员进行注销',$result['message']);
                ajaxReturn(0,'退会失败，请重试');//$result['message']
            }
        }
        //本地订单状态修改
        $sql="UPDATE `".$db_pre."order` SET `status`=8 WHERE uid=".$user['id']." and status=1";
        $db->query($sql);
    }
    if(!empty($_POST['reason'])){
        $reason = implode(";",$_POST['reason']);
    }
    if(!empty($_POST['other'])){
        $reason.=';'.$_POST['other'];
    }
    if(empty($reason)){
        error_log_web('注销', 2, 'id为'.$user['id'].'会员进行注销','退会原因不能为空');
        ajaxReturn(0,'退会原因不能为空');
    }
    //退会改变状态并清空积分
    $sql="UPDATE `".$db_pre."member` SET `reason`='".$reason."',`status`=3,`integral`=0 WHERE `mobile`='".$_COOKIE['member_mobile']."'";
    if($db->query($sql)){
        error_log_web('注销', 1, 'id为'.$user['id'].'会员进行注销','退会成功');
        //退会成功后向该会员发送email
        $mailsubject='【Z.com Research (最网e调查)】您的账户注销成功通知邮件';
        $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
        
                                                        亲爱的'.$user['username'].'<br><br>
        
                                                      您好，这里是技慕驿动市场调查（上海）有限公司在线调研及活动的会员平台—— Z.com Research (最网e调查)。<br><br>

                                                　　您的最网e调查退会申请已成功提交。<br><br>
                                                
                                                　　虽然您已经完成了退会手续，在您退会之后的数日仍有可能会收到问卷邀请邮件。<br>
                                                        为此给您带来诸多不便，我们深表歉意。<br><br>
                                                　　                                                                                                                                      
                                                        退会后我们仍将您的个人信息保留6个月，用于对应您日后的咨询，之后我们将删除您的信息。<br><br>                                                                                                                    
                                   
                                                        ※ 若要重新注册最网e调查，请联系本站管理员。欢迎您的再次光临。https://www.zcom.asia<br><br> 
                                                            
                                                        感谢您对最网e调查问卷的大力支持。<br>
                                                            
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
        send_mail('smtp',$user['email'],$mailsubject,$mailbody);
        //删除cookie
        setcookie('member_name','',time()-86400,'/');
        setcookie('member_mobile','',time()-86400,'/');
        //退会成功后积分清零，插入积分变动表
        $sql = "INSERT INTO `".$db_pre."integral` (`uid`,`type`,`val`,`source`,`add_time`)VALUES(
					'".$user['id']."','2','".$user['integral']."','注销','".date('Y-m-d H:i:s')."')";
        $db->query($sql);
        ajaxReturn(1,'退会成功');
    }else{
        error_log_web('注销', 3, 'id为'.$user['id'].'会员进行注销',mysql_error());
        ajaxReturn(0,'退会失败，请重试');
    }
}
        /*************************************基本信息开始***********************************/
        $menu_info1 = array(
            'class_title_cn' => $web_config['Web_SiteTitle_cn'],
            'class_keywords_cn' => $web_config['Web_Keywords_cn'],
            'class_description_cn' => $web_config['Web_Description_cn']
        );
        $smarty->assign('menu_info1',$menu_info1);
        $class_id = trim($_GET['cid']);
        
        $cid=($class_id=='')?'16':$class_id;
        $smarty->assign('first_cid',$cid);
        if($cid=='16')
        {
            $parent_menu_message = get_menu_info($cid);
            $p_cid = $cid;
            //找到第一个子类
        
            $now_menu_message = get_son_menu_message($cid);
        
            $cid = $now_menu_message['class_id'];
        
        }else{
            //找到父类
            $parent_menu_message = get_parent_menu_message($cid);
            $p_cid = $parent_menu_message['class_id'];
            $smarty->assign('p_cid',$p_cid);
            $now_menu_message = get_menu_info($cid);
            if($now_menu_message['class_child']>0){//还有子类要找到子类
                $smarty->assign('parent_menu_message2',$now_menu_message);
                $now_menu_message = get_son_menu_message($cid);
                $cid = $now_menu_message['class_id'];
            }
        }
        //会员信息
        $user = $db->getone("select * from db_member where mobile='".$_COOKIE['member_mobile']."'");
        $smarty->assign('user',$user);
        
        $smarty->assign('parent_menu_message',$parent_menu_message);
        $smarty->assign('cid',$cid);
        
        //exit(var_dump($cid));
        include('./common.php');
    //积分变动情况
    if($cid==18){
        $page = $_GET['page'];
        $page = ($page>0)?$page:1;
        $url = basename(__FILE__);
        $url_string = $_SERVER['PHP_SELF'];
        $url= substr( $url_string , strrpos($url_string , '/')+1 );
        $url.= ($cid=='')?'':is_query_string($url).'cid='.$cid;
        $url.= is_query_string($url);
        $sql="SELECT * FROM `".$db_pre."integral` where uid=".$user['id']." and source!='兑换商品' order by add_time desc";
        $integral = page($sql,$page,12,$url);
        $smarty->assign('integral',$integral);
        $view='pc/user.html';
    }elseif($cid==19){
        $status_txt=array(
            '1'=>'申请中',
            '2'=>'兑换失败：申请被取消',
            '3'=>'处理中',
            '4'=>'审核通过，兑换合作方正在准备发货',
            '5'=>'',
            '6'=>'配送中',//商品专有
            '7'=>'兑换失败：兑换信息有误 （说明：积分将被退回账户，请确认兑换信息无误后再次申请）',
            '8'=>'-',
            '9'=>'兑换成功',//商品专有
        );
        
        $page = $_GET['page'];
        $page = ($page>0)?$page:1;
        $url = basename(__FILE__);
        $url_string = $_SERVER['PHP_SELF'];
        $url= substr( $url_string , strrpos($url_string , '/')+1 );
        $url.= ($cid=='')?'':is_query_string($url).'cid='.$cid;
        $url.= is_query_string($url);
        $sql="SELECT * FROM `".$db_pre."order` where uid=".$user['id']." order by add_time desc";
        $order = page($sql,$page,12,$url);
        if(!empty($order)){
            foreach ($order as $key=>$val){
                $order[$key]['status_txt']=$status_txt[$val['status']];
                $order[$key]['item_list']=unserialize($val['item_list']);
            }
        }
        $smarty->assign('order',$order);
        $view='pc/user.html';
    }elseif ($cid==20 || $cid==21){
        $province=$db->getall("select * from db_province where 1 order by id asc");
        $smarty->assign('province',$province);
        $city=$db->getall("select * from db_city where p_id=".$user['province_id']);
        $smarty->assign('city',$city);
        $view='pc/user_up.html';
    }elseif ($cid==22){
        //会员加密值，含会员id
        $crypt=encrypt($web_config['Web_Blowfish'],$user['id'].':'.$web_config['Web_panelType'].':'.time());
        $smarty->assign('crypt',$crypt);
        $view='pc/user_tj.html';
    }elseif ($cid==24){
        $num=$db->num_rows("SELECT id FROM `".$db_pre."order` WHERE uid=".$user['id']." and status=1");
        $smarty->assign('num',$num);
        $view='pc/user_off.html';
    }
$smarty->display($view);
        
        
        