<?php 
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');

$cache_url=$_SERVER['REQUEST_URI'];
if (!$smarty->isCached($lang.'/about.html', $cache_url)){
   /*************************************基本信息开始***********************************/
	$menu_info1 = array(
		'class_title_cn' => $web_config['Web_SiteTitle_cn'],
		'class_keywords_cn' => $web_config['Web_Keywords_cn'],
		'class_description_cn' => $web_config['Web_Description_cn']
	);  
	$smarty->assign('menu_info1',$menu_info1); 
	$class_id = addslashes(trim($_GET['cid']));
	// $cid=($class_id=='')?get_default_menu_id('3'):get_default_menu_id($class_id);
	$cid=($class_id=='')?'15':$class_id;
	$smarty->assign('first_cid',$cid);   
	
	//公共部分
	include('./common.php');
    
}

$smarty->display('pc/jsz.html');