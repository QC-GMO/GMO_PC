<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');

$action = daddslashes($_GET['action']);

switch ($action) {
    //大麦城获取用户信息
    case 'getuserinfo':
        $token=daddslashes($_POST['Userid']);
        //$token='cc6da704bd58fc12f1855aa47c98d22d';
        //$token_de=decrypt($web_config['Web_Blowfish'],$token);//解密token,Blowfish方式，gmo提供
        //$uid  =  strtok ( $token ,  ':' );
        //$uid  =  strstr ( $token_de ,  ':' ,  true );
        $user = $db->getone("select * from db_member where token='".$token."' and status=2");
        $data=array(
            'Userid'=>$token,
            'Username'=>$user['username'],
            'Point'=>$user['integral'],//会员当前积分
            'Mobile'=>$user['mobile'],
            'Email'=>$user['email'],
            'Address'=>array(
                'name'=>$user['name'],
                'mobile'=>$user['mobile'],
                'address'=>$user['province'].$user['city'].$user['address'],
            )
        );
        exit( json_encode( $data ) );
        break;
    //平台购买商品，扣除积分
    case 'deduct':
        $token=daddslashes($_POST['Userid']);
        $integral=$_POST['UsePoint'];
        if(!is_numeric($integral) || $integral<1){
            exit( json_encode( array('Status'=>101,'Msg'=>'UsePoint is not a number or Less than 1') ) );
        }
        //$token_de=decrypt($web_config['Web_Blowfish'],$token);//解密token,Blowfish方式，gmo提供
        //$uid  =  strstr ( $token_de ,  ':' ,  true );
        $user = $db->getone("select id,integral from db_member where token='".$token."' and status=2");
        if(empty($user)){exit( json_encode( array('Status'=>104,'Msg'=>'Did not check the user information') ) );}
        if($integral>$user['integral']){
            exit( json_encode( array('Status'=>102,'Msg'=>'Your balance is insufficient!') ) );
        }
        $sql="UPDATE `".$db_pre."member` SET `integral`=`integral`-'" .$integral. "' WHERE id='".$user['id']."'";
        if($db->query($sql)){//扣除积分成功后插入积分变动表并返回信息给大麦城
            $sql = "INSERT INTO `".$db_pre."integral`(`uid`,`type`,`val`,`source`,`add_time`)VALUES(
					'".$user['id']."','2','".$integral."','兑换商品','".date('Y-m-d H:i:s')."')";
            $db->query($sql);
            exit( json_encode( array('Status'=>100,'Msg'=>'') ) );
        }else{
            exit( json_encode( array('Status'=>103,'Msg'=>mysql_error()) ) );
        }
        break;
     //大麦城返回订单信息，生成订单
    case 'order':
        $token=daddslashes($_POST['Userid']);
        //$token_de=decrypt($web_config['Web_Blowfish'],$token);//解密token,Blowfish方式，gmo提供
        //$uid  =  strstr ( $token_de ,  ':' ,  true );
        $user = $db->getone("select id,username,email,integral from db_member where token='".$token."' and status=2");
        if(empty($user)){
            error_log_web('兑换积分', 2, '会员进行兑换积分','没有检查到会员信息');
            exit( json_encode( array('Status'=>101,'Msg'=>'Did not check the user information') ) );
        }
        $item=unserialize($_POST['item_list']);
        if(empty($item)){
            error_log_web('兑换积分', 2, 'id为'.$user['id'].'的会员进行兑换积分','商品列表为空');
            exit( json_encode( array('Status'=>104,'Msg'=>'Commodity list is empty') ) );
        }
        if(empty($_POST['number']) || empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['total'])){
            error_log_web('兑换积分', 2, 'id为'.$user['id'].'的会员进行兑换积分','参数是无效');
            exit( json_encode( array('Status'=>102,'Msg'=>'Parameter is invalid') ) );
        }
        $sql = "INSERT INTO `".$db_pre."order`(`type`,`uid`,`number`,`name`,`mobile`,`address`,`item_list`,`total`,`status`,`add_time`)VALUES(
					'1','".$user['id']."','".$_POST['number']."','".$_POST['name']."','".$_POST['mobile']."','".$_POST['address']."','".$_POST['item_list']."','".$_POST['total']."','1','".date('Y-m-d H:i:s')."')";
        if($db->query($sql)){//订单信息插入成功,注销token
            $db->query("UPDATE `".$db_pre."member` SET `token`='' WHERE id='".$user['id']."'");
            error_log_web('兑换积分', 1, 'id为'.$user['id'].'的会员进行兑换积分','订单生成成功');
            //订单生成成功向会员发送邮件
            foreach($item as $v){
                $tit.=$v['title'].'X'.$v['num'].',';
            }
            $mailsubject='【Z.com Research (最网e调查)】您的积分兑换申请提交成功通知邮件';
            $mailbody='【Z.com Research (最网e调查)】<br>
　　                                                            我说、我做，世界更精彩！<br>
    　　　　                                         最网e调查是一个在线调研和活动的会员社区。由这里，我们将大家的心声传递给海内外企业。<br>
                                                            加入我们，踏上你的精彩之旅→:https://www.zcom.asia<br><br>
                
                                                            亲爱的'.$user['username'].'<br><br>
                                                            您好，这里是技慕驿动市场调查（上海）有限公司在线调研及活动的会员平台—— Z.com Research (最网e调查)。<br><br>
                                                            您的积分兑换申请已成功提交。<br>
                                                            我们将依据以下内容进行兑换。<br><br>
                                                                
                                                        　*+*----------------------------------------------------------------*+*<br>
                                                        　兑换前的积分　：'.$user['integral'].' 积分<br>
                                                        　兑换所需积分　：'.$_POST['total'].' 积分<br>
                                                        　兑换的商品　    ：'.$tit.'<br>
                                                        　兑换后所剩积分：'.$user['integral']-$_POST['total'].' 积分<br>
                                                        　*+*----------------------------------------------------------------*+*<br><br>
                                                           请登录Z.com Research (最网e调查)，进入【会员中心】后点击【<a href="https://www.zcom.asia/pc/user.php?cid=19">积分兑换历史记录</a>】查询您的订单进度。<br>
                                                           ※订单的审核及发放周期请参考本站【<a href="https://www.zcom.asia/pc/about.php?cid=35">积分兑换规则</a>】。<br>
                                                           ※若您的地址或兑换账户有变化，请及时登录最网e调查进行修改。<br>
                                                                收到本网站自动发送的变更成功通知邮件后方可进行相应兑换操作。<br><br>
                                                           ★★！！重要！！★★<br>
                                                                  若您在积分兑换订单成功前注销，将导致积分兑换立即失效，账户积分将被清零。<br>
                                                            　   请注意，请勿在积分兑换成功前注销您的账户。<br><br>
                                                          感谢您对最网e调查的大力支持！<br><br>
                                                                
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
            exit( json_encode( array('Status'=>100,'Msg'=>'') ) );
        }else{
            error_log_web('兑换积分', 3, 'id为'.$user['id'].'的会员进行兑换积分',mysql_error());
            exit( json_encode( array('Status'=>103,'Msg'=>mysql_error()) ) );
        }
        break;
     //用户确认收货后的大麦城返回确认订单信息
    case 'receiving':
        //$token=daddslashes($_POST['Userid']);
        //$token_de=decrypt($web_config['Web_Blowfish'],$token);//解密token,Blowfish方式，gmo提供
        //$uid  =  strstr ( $token_de ,  ':' ,  true );
        $number=daddslashes($_POST['number']);
        $order = $db->getone("select type,status from db_order where number='".$number."'");
        if(empty($order)){exit( json_encode( array('Status'=>104,'Msg'=>'Is empty') ) );}
        if($order['status']!=6){exit( json_encode( array('Status'=>101,'Msg'=>'State error') ) );}
        if($order['type']!=1){exit( json_encode( array('Status'=>102,'Msg'=>'Type error') ) );}
        $sql="UPDATE `".$db_pre."order` SET `status`='9' WHERE number='".$number."'";
        if($db->query($sql)){//确认收货成功后改变状态并返回信息给大麦城
            exit( json_encode( array('Status'=>100,'Msg'=>'') ) );
        }else{
            exit( json_encode( array('Status'=>103,'Msg'=>mysql_error()) ) );
        }
        break;
}







