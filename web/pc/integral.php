<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
// error_reporting(7);
//检测当前用户是否登录
again();
//************************
        
        /*************************************基本信息开始***********************************/
        $menu_info1 = array(
            'class_title_cn' => $web_config['Web_SiteTitle_cn'],
            'class_keywords_cn' => $web_config['Web_Keywords_cn'],
            'class_description_cn' => $web_config['Web_Description_cn']
        );
        $smarty->assign('menu_info1',$menu_info1);
        $class_id = trim($_GET['cid']);
        
        $cid=($class_id=='')?'11':$class_id;
        $smarty->assign('first_cid',$cid);
        if($cid=='11')
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
        $user = $db->getone("select * from db_member where mobile='".$_COOKIE['member_mobile']."'");
        //会员加密值，含会员id
        /* $crypt=encrypt($web_config['Web_Blowfish'],$user['id'].':'.$web_config['Web_panelType'].':'.time());
        $smarty->assign('crypt',$crypt); */
        //$a=decrypt($web_config['Web_Blowfish'],$crypt);
        //公共部分导航，头部
        include('./common.php');
        if($cid==14){
            $area = $_GET['area'];
            //商品列表
            $page = $_GET['page'];
            $page = ($page>0)?$page:1;
            $url = basename(__FILE__);
            $url_string = $_SERVER['PHP_SELF'];
            $url= substr( $url_string , strrpos($url_string , '/')+1 );
            $url.= ($cid=='')?'':is_query_string($url).'cid='.$cid;
            $url.= ($area=='')?'':is_query_string($url).'area='.$area;
            $url.= is_query_string($url);
            $sql="SELECT * FROM `".$db_pre."listinfo` WHERE  `class_id` = '".$cid."'";
            if(!empty($area)){
                $sql.=($area=='1')?' and `price` between 1 and 99':'';
                $sql.=($area=='2')?' and `price` between 100 and 499':'';
                $sql.=($area=='3')?' and `price` between 500 and 999':'';
                $sql.=($area=='4')?' and `price` between 1000 and 5000':'';
                $sql.=($area=='5')?' and `price`>5000':'';
            }
            $sql.=" ORDER BY order_id asc,add_time desc ";
            
            $product = page($sql,$page,12,$url);
            $smarty->assign('product',$product);
            $smarty->assign('user',$user);
            //************************
            $view='/integral.html';
        }elseif($cid==12){
            $smarty->assign('user',$user);
            $view='/integral_cash.html';
        }elseif($cid==13){
            if(empty($user['token'])){
                $token=date('YmdHis').createNonceStr(8);
                $db->query("UPDATE `".$db_pre."member` SET `token`='" .$token. "' WHERE mobile='".$_COOKIE['member_mobile']."'");
                $user['token']=$token;
            }
            $smarty->assign('user',$user);
            $view='/integral_dmc.html';
        }
$smarty->display('pc'.$view);