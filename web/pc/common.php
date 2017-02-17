<?php
//$_COOKIE['member_name']
	//顶部导航列表
	$top_array = get_menu_list('0','0',true); 
	$smarty->assign('top_array',$top_array);
	//底部单页导航列表
	$foot_array = get_menu_list('0','1',true);
	$smarty->assign('foot_array',$foot_array);
	//栏目关键字
	$menu_info = get_menu_info($cid);
	$smarty->assign('menu_info',$menu_info);//当前菜单信息 
	
	if(empty($parent_menu_message3)){
	    //当前同级菜单集合
	    $parent_menu_message3 = get_menu_list1($menu_info['class_parent_id']);
	    $smarty->assign('parent_menu_message3',$parent_menu_message3);
	}
	
	//当前菜单父菜单信息
	$menu_info_parent = get_menu_info($menu_info['class_parent_id']);
	$smarty->assign('menu_info_parent',$menu_info_parent);
	//exit(var_dump($parent_menu_message3));
	//当前栏目 父栏目集合
	$parent_path_array = explode(',', $menu_info['class_parent_path']);  
	$smarty->assign('parent_path_array',$parent_path_array); 
	
	//侧栏菜单
	$left_menu = get_menu_list1($p_cid,true); 
	$smarty->assign('left_menu',$left_menu);
	//会员二级菜单列表
	$member_menu = get_menu_list1('16',true);
	$smarty->assign('member_menu',$member_menu);
	
	//当前根栏目信息
	//echo $menu_info['class_base_id'];
	$root_menu_info = get_menu_info($menu_info['class_base_id']);
	$smarty->assign('root_menu_info',$root_menu_info);
	
	//面包屑导航
	$bread_menu = get_bread_menu($menu_info['class_parent_path']);
	$smarty->assign('bread_menu',$bread_menu);
	
	//当前栏目的子栏目
	$sql = "SELECT * FROM `".$db_pre."web_class` WHERE `class_parent_id` = {$cid}";
	$son_menu = $db->getall($sql);
	$smarty->assign('son_menu',$son_menu);
	// print_r($son_menu);
//exit(var_dump($parent_menu_message3));
	
?>