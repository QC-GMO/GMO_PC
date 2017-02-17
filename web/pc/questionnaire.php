<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
// error_reporting(7);
//************************
//检测当前用户是否登录
again();

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
	$user = $db->getone("select id,username,email,integral from db_member where mobile='".$_COOKIE['member_mobile']."'");
	$smarty->assign('user',$user);
	//Blowfish加密，key、panelType由Gmo提供
	$uid=$user['id'];
	//$uid='2067715';//测试账号有值的会员id
	$crypt=encrypt($web_config['Web_Blowfish'],$uid.':'.$web_config['Web_panelType'].':'.time());
	//问卷测试地址
	$url='https://cn.infopanel.asia/pollon/jp/gmor/research/pollon/enqueteList/facade/EnqueteList.json?panelType='.$web_config['Web_panelType'].'&crypt='.$crypt;
	$result=httpGet($url);
	if(!empty($result)){
	    error_log_web('调用问卷列表API', 1, 'id为'.$uid.'会员进行调用问卷列表API','调用成功');
	}else{
	    error_log_web('调用问卷列表API', 2, 'id为'.$uid.'会员进行调用问卷列表API','返回值为空');
	}
	$wenjuan=json_decode($result,true);
	$smarty->assign('wenjuan',$wenjuan);
	
	//banner图
	$sql="SELECT json_arr_cn FROM `".$db_pre."table_flash` where class_id=2 and is_index=1 order by add_time desc limit 1";
	$banner = $db->getone($sql);
	$json_arr_cn= json_decode($banner['json_arr_cn'],true);
	$smarty->assign('json_arr_cn',$json_arr_cn);
	
	/* $sql = "select * from `".$db_pre."pageinfo` WHERE class_id = '".$cid."'";
	$aboutContent = $db->getone($sql);
	$smarty->assign('list',$aboutContent);   */ 
	
	$page = $_GET['page'];
	$page = ($page>0)?$page:1;
	$url = basename(__FILE__);
	$url_string = $_SERVER['PHP_SELF'];
	$url= substr( $url_string , strrpos($url_string , '/')+1 );
	$url.= ($cid=='')?'':is_query_string($url).'cid='.$cid;
	$url.= is_query_string($url);
	$sql="SELECT * FROM `".$db_pre."pageinfo` WHERE  `class_id` = '".$cid."'";
	$sql.=" ORDER BY add_time desc ";
	//$sql.=" ORDER BY class_id asc ";
	
	$aboutContent = page($sql,$page,10,$url);
	$smarty->assign('list',$aboutContent);
	$smarty->assign('parent_menu_message',$parent_menu_message);
	$smarty->assign('cid',$cid);
	
	//公告板
	$sql="SELECT * FROM `".$db_pre."pageinfo` WHERE  `class_id` = '15'  order by add_time desc limit 5 ";
	$call_board = $db->getall($sql);
	$smarty->assign('call_board',$call_board);
	//公共部分导航，头部
	include('./common.php');
	
    
    //$smarty->assign('in_index','turein');
//************************
$smarty->display('pc/questionnaire.html');
