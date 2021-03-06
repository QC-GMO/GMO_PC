<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action         = $_REQUEST['action'];
$searchclass    = $_REQUEST['searchclass'];
$keyword        = $_REQUEST['keyword']; 
$page           = $_REQUEST['page'];
$page           = ($page>0)?$page:1;
$order          = $_GET['order'];
$sort           = $_GET['sort'];
$operationclass = $_POST['operationclass'];
$new_class_id   = $_POST['new_class_id'];

$id       = $_REQUEST['id'];
$class_id = $_REQUEST['class_id']; 
$title_cn  = daddslashes($_POST['title_cn']); 
$title_en  = daddslashes($_POST['title_en']); 
$title_jp  = daddslashes($_POST['title_jp']); 
$content_cn  = daddslashes($_POST['content_cn']); 
$content_en  = daddslashes($_POST['content_en']); 
$content_jp  = daddslashes($_POST['content_jp']);
$start_time=$_POST['start_time'];
$end_time=$_POST['end_time'];
$add_time     = $_POST['add_time'];  

switch ($action){ 
	case 'add':  
		$smarty->assign(
			array( 
				"add_time"=>date("Y-m-d H:i:s"),
			)
		);
		break; 
	case 'edit':
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `".$db_pre."pageinfo` WHERE id='".$id."'";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql); 
		$smarty->assign(
			array(
				"class_array"=>$class_array,
				"rows"=>$rows,
			)
		);   
		break; 
		
	case 'insert':
		if (empty($title_cn)){
		    error_log_admin('通知信息的追加',2,'管理员'.$Session['Admin']['UserName'].'进行通知信息的追加','名称不能为空');
			WriteErrMsg('名称不能为空');
		} 

		$sql = "INSERT INTO `".$db_pre."pageinfo`( 
					`class_id`, 
					`title_cn`,
		            `title_en`, 
		            `title_jp`, 
		            `content_cn`, 
					`content_en`,  
		            `content_jp`, 
		            `start_time`,
		            `end_time`,
					`add_time`
				)VALUES( 
					'1',
					'".$title_cn."',
					'".$title_en."',
					'".$title_jp."',
					'".$content_cn."',
					'".$content_en."',
					'".$content_jp."',
					'".$start_time."',
					'".$end_time."',
					'".$add_time."'
				)";
		if ($db->query($sql)){ 
		    error_log_admin('通知信息的追加',1,'管理员'.$Session['Admin']['UserName'].'进行通知信息的追加','添加成功');
			WriteSuccessMsg('添加成功', $CurrentPageName);
		}
		else{
		    error_log_admin('通知信息的追加',3,'管理员'.$Session['Admin']['UserName'].'进行通知信息的追加',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break; 
	case 'update':
		if (!is_numeric($id)){
		    error_log_admin('通知信息的修改',2,'管理员'.$Session['Admin']['UserName'].'进行通知信息的修改','参数错误');
			WriteErrMsg('参数错误');
		} 
		$sql = "UPDATE `".$db_pre."pageinfo` SET  
					`title_cn`='".$title_cn."',
					`title_en`='".$title_en."',
					`title_jp`='".$title_jp."',
					`content_cn`='".$content_cn."',
					`content_en`='".$content_en."',
					`content_jp`='".$content_jp."',
					`start_time`='".$start_time."',
					`end_time`='".$end_time."'
				WHERE id='".$id."'"; 
		if ($db->query($sql)){
		    error_log_admin('通知信息的修改',1,'管理员'.$Session['Admin']['UserName'].'进行通知信息的修改','更新成功');
			WriteSuccessMsg('更新成功', $CurrentPageName);
		}else{
		    error_log_admin('通知信息的修改',3,'管理员'.$Session['Admin']['UserName'].'进行通知信息的修改',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break;
		
	case 'delete':
		if (!is_numeric($id)){
		    error_log_admin('通知信息的删除',2,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除','参数错误');
			WriteErrMsg('参数错误');
		} 
		$sql = "DELETE FROM `".$db_pre."pageinfo` WHERE id='".$id."'";
		if ($db->query($sql)){
		    error_log_admin('通知信息的删除',1,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除','删除成功');
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
		    error_log_admin('通知信息的删除',3,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break; 
	case 'operation':
		if (!is_array($id)){
		    error_log_admin('通知信息的删除',2,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除','参数错误');
			WriteErrMsg('参数错误');
		}
		$id = implode(",",$id); //explode(",",$id);
		switch ($operationclass){
			case 'batch_delete':
				$sql  = "DELETE FROM `".$db_pre."pageinfo` WHERE id IN (".$id.")";
				break;  
			default:
				WriteErrMsg('请选择批量操作选项');
		}
		if ($db->query($sql)){
		    error_log_admin('通知信息的删除',1,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除','删除成功');
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
		    error_log_admin('通知信息的删除',3,'管理员'.$Session['Admin']['UserName'].'进行通知信息的删除',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break;  
	default:
		$url = $CurrentPageName;
		$url.= ($class_id =='')?'':is_query_string($url).'class_id='.$class_id;
		$url.= ($searchclass =='')?'':is_query_string($url).'searchclass='.$searchclass;
		$url.= ($keyword =='')?'':is_query_string($url).'keyword='.urlencode($keyword);
		$url.= is_query_string($url);
		$sql = "SELECT * FROM `".$db_pre."pageinfo` n WHERE 1 and  class_id=1";
		if ($keyword != ''){
			$sql.= ($searchclass==2)?" AND(concat(`content_cn`) LIKE '%".$keyword."%')":" AND (concat(`title_cn`) LIKE '%".$keyword."%')";
		}
		switch ($order){
			case 'class_id':
				$sql .= " ORDER BY class_id";
				break; 
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
		$list_array = page($sql, $page, $web_config['Web_PageSize'], $url); 
		$smarty->assign(
			array(
				"order_time"=>orderby('add_time','时间'),
				"list_array"=>$list_array,
				"class_array"=>$class_array 
			)
		);
}
$smarty->display("admin/web_news_pageinfo.html");
$runtime->stop_write();
?>