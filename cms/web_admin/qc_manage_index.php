<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();

$action = daddslashes($_REQUEST['action']); 

switch ($action){
	case 'indexleft':
		if ($Session['Admin']['Flag'] > 1 ){   //超级和高级除外
			$sql_purview = ($Session['Admin']['Purview']!='')?" AND class_id IN (".$Session['Admin']['Purview'].")":" AND class_id IN (0)";
		}
		$sql = "SELECT class_id,class_name FROM `{$db_pre}menu` WHERE is_show=1 AND class_depth=0 AND menu_flag>={$Session['Admin']['Flag']}{$sql_purview} ORDER BY class_root_id";
		
		$menu_array = $db->getall($sql);
		if (is_array($menu_array)){
			foreach($menu_array as $key=>$value){
				$sql = "SELECT class_id,class_name,class_url,(SELECT MAX(class_id) FROM `{$db_pre}menu` WHERE is_show=1 AND class_depth=1 AND menu_flag>={$Session['Admin']['Flag']}{$sql_purview}) AS menu_max FROM `{$db_pre}menu` WHERE class_parent_id={$menu_array[$key]['class_id']} AND is_show=1 AND menu_flag>={$Session['Admin']['Flag']}{$sql_purview} ORDER BY class_root_id,class_order_id";
				$menu_array[$key]['son'] = $db->getall($sql);
			}			
		}
		$smarty->assign('menu_array',$menu_array);
		$smarty->assign('menu_count',$db->num_rows($sql));
		$smarty->assign('CurrentPageName',$CurrentPageName);
		$smarty->assign('bg_class','body_bg_2');
		$smarty->assign('w3c', 1);
		break;
	case 'indexright':
		$smarty->assign('REMOTE_ADDR',$_SERVER['REMOTE_ADDR']);
		$smarty->assign('SERVER_SOFTWARE',$_SERVER['SERVER_SOFTWARE']);
		$smarty->assign('SERVER_NAME',$_SERVER['SERVER_NAME']);
		$smarty->assign('SERVER_ADDR',$_SERVER['SERVER_ADDR']);
		$smarty->assign('HTTP_HOST',$_SERVER['HTTP_HOST']);
		$smarty->assign('HTTP_USER_AGENT',$_SERVER['HTTP_USER_AGENT']);
		$smarty->assign('MYSQL_VERSION',mysql_get_client_info());
		$smarty->assign('bg_class','body_bg_3');
		$smarty->assign('w3c', 1);
		break;
	default :
		$smarty->assign('bg_class','overflow');
		$smarty->assign('w3c', 0);
		break;
} 
$smarty->display('admin/qc_manage_index.html');
?>