<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action         = $_REQUEST['action'];
$operationclass = $_POST['operationclass'];
$searchclass    = $_REQUEST['searchclass'];
$keyword        = $_REQUEST['keyword'];
$page           = $_REQUEST['page'];
$page           = ($page>0)?$page:1;
$order          = $_GET['order'];
$sort           = $_GET['sort'];

$id = $_REQUEST['id'];

switch ($action){
	case 'operation':
		if (!is_array($id)){
			WriteErrMsg('参数错误');
		}
		$id = implode(",",$id);//explode(",",$id);
		switch ($operationclass){
			case 1:
				$sql  = "DELETE FROM `{$db_pre}record` WHERE id IN ({$id})";
				break;
			default:
				WriteErrMsg('请选择批量操作选项');
		}
		if ($db->query($sql)){  
		   header("Location:{$_SERVER['HTTP_REFERER']}");
		}
		else{
		   WriteErrMsg(mysql_error());
		}
		break;
	default:
		$url  = $CurrentPageName;
		$url .= ($sort =='')?"":is_query_string($url)."sort=".$sort;
		$url .= ($order =='')?"":is_query_string($url)."order=".$order;
		$url .= ($class_id =='')?"":is_query_string($url)."class_id=".$class_id;
		$url .= ($searchclass =='')?"":is_query_string($url)."searchclass=".$searchclass;
		$url .= ($keyword =='')?"":is_query_string($url)."keyword=".urlencode($keyword);
		$url .= is_query_string($url);
		$sql  = "SELECT * FROM `{$db_pre}record` AS A WHERE 1";
		$sql .= ($class_id =='')?"":" AND class_id=".$class_id;
		if ($keyword != ''){
			$sql .= ($searchclass==2)?" AND content LIKE '%".$keyword."%'":" AND username LIKE '%".$keyword."%'";
		}
		switch ($order){
			case 'add_time':
				$sql .= " ORDER BY add_time";
				break;
			default:
				$sql .= " ORDER BY add_time";
		}
		switch ($sort){
			case 'asc':
				$sql .= " ASC";
				break;
			case 'desc':
				$sql .= " DESC";
				break;
			default:
				$sql .= " DESC";
		}
		if ($order!='' && $order!='add_time'){
			$sql .= ",add_time DESC";
		}
		$list_array = page($sql,$page,$web_config['Web_PageSize'],$url,$db_pre.'record');
		$smarty->assign(
			array(
				"order_time"=>orderby('add_time','时间'),
				"list_array"=>$list_array,
				"url"=>$url
			)
		);
}
$smarty->display("admin/qc_manage_log.html");
$runtime->stop_write();
?>