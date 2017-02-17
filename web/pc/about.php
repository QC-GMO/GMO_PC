<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
// error_reporting(7);
//************************

$cache_url=$_SERVER['REQUEST_URI'];
if (!$smarty->isCached($lang.'/about.html', $cache_url)){  
	
	/*************************************基本信息开始***********************************/
	$menu_info1 = array(
		'class_title_cn' => $web_config['Web_SiteTitle_cn'],
		'class_keywords_cn' => $web_config['Web_Keywords_cn'],
		'class_description_cn' => $web_config['Web_Description_cn']
	);  
	$smarty->assign('menu_info1',$menu_info1); 
	$class_id = trim($_GET['cid']);
	
	$cid=($class_id=='')?'2':$class_id;
	$smarty->assign('first_cid',$cid); 	
	if($cid=='2')
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
	//单页信息
	$sql="SELECT content_cn FROM `".$db_pre."pageinfo` WHERE  `class_id` = '".$cid."'";
	$about = $db->getone($sql);
	$about['content_cn']=strtr($about['content_cn'],array('/uploadfiles/kindles/image/'=>$web_config['Web_admin_url'].'/uploadfiles/kindles/image/'));
	$smarty->assign('about',$about);
	//通知信息
	$tid = $_GET['tid'];
	if(!empty($tid)){
	    $sql="SELECT * FROM `".$db_pre."pageinfo` WHERE  `id` = '".$tid."'";
	    $about = $db->getone($sql);
	    $about['content_cn']=strtr($about['content_cn'],array('/uploadfiles/kindles/image/'=>$web_config['Web_admin_url'].'/uploadfiles/kindles/image/'));
	    $smarty->assign('about',$about);
	}
	//公共部分导航，头部
	include('./common.php');
}
    
//************************
$smarty->display('pc/about.html');
