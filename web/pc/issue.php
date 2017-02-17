<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$act = trim($_GET['act']);
if($act=='message'){
    $order   = array("\r\n", "\n", "\r");
    $message=str_replace($order,'<br>',$_POST['message']);
    $sql = "INSERT INTO `".$db_pre."message`(`name`,`email`,`subject`,`number`,`message`,`type`,`sys`,`browser`,`add_time`)VALUES(
    '".daddslashes($_POST['name'])."','".$_POST['email']."','".daddslashes($_POST['subject'])."','".daddslashes($_POST['number'])."','".$message."','".$_POST['_type']."','".$_POST['sys']."','".$_POST['browser']."','".date('Y-m-d H:i:s')."')";
    if ($db->query($sql)){
        error_log_web('会员咨询', 1, 'email为'.$_POST['email'].'用户进行会员咨询','提交成功');
        //提交咨询后向该邮箱发送email
        $mailsubject='【Z.com Research (最网e调查)】您的咨询提交成功通知邮件';
        $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                        加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
            
                       ---------------------------------------------------------------------<br>
　                                                      本邮件是为了确认内容而自动回复给发件人的。关于您的咨询，我们会另作回复。<br>
　                                                      ---------------------------------------------------------------------<br><br>
                                                           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>
                                    　                   ■Z.com Research (最网e调查)■  <br>
                                    　                   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br><br>
                                                            亲爱的'.$_POST['name'].'<br><br>
                                                　       这里是最网e调查客服中心。<br>
                                                　       我们已收到您的咨询内容。<br><br>
                   *+*----------------------------------------------------------------*+*<br>
                        ■咨询种类 ：'.(($_POST['_type']==1)?'普通咨询':'问卷咨询').'<br>
                        '.(($_POST['_type']==2)?"■问卷编号：{$_POST['number']}<br>
                        ■问卷名称 ：{$_POST['subject']}<br>":"").'
                   ■咨询内容：'.$_POST['message'].'<br><br>
                   *+*----------------------------------------------------------------*+*<br><br>
                                                        　我们会根据您的问题内容做出具体答复，可能会花一些时间，尽请谅解。感谢您的咨询。<br><br>
                                                        　----------------------------------------------------------------------<br><br>
                       
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
        send_mail('smtp',$_POST['email'],$mailsubject,$mailbody);
        ajaxReturn(1,'提交成功');
    }else{
        error_log_web('会员咨询', 3, 'email为'.$_POST['email'].'用户进行会员咨询',mysql_error());
        ajaxReturn(0,'提交失败，请重试');//mysql_error()
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
        
        $cid=($class_id=='')?'27':$class_id;
        $smarty->assign('p_cid',$cid);
        if($cid=='27')
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
        
        $smarty->assign('parent_menu_message',$parent_menu_message);
        $smarty->assign('cid',$cid);
        //当前会员登录信息
        if(!empty($_COOKIE['member_mobile'])){
            $user = $db->getone("select id,username,email,integral from db_member where mobile='".$_COOKIE['member_mobile']."'");
            $smarty->assign('user',$user);
        }
        
        include('./common.php');
        
        $sql="SELECT title_cn,content_cn FROM `".$db_pre."pageinfo` WHERE  `class_id` = '".$cid."'";
        $issue = $db->getall($sql);
        $smarty->assign('issue',$issue);
        //exit(var_dump($issue));
        
$smarty->display('pc/issue.html');