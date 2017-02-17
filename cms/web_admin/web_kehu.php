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
$title  = daddslashes($_POST['title']); 
$name  = daddslashes($_POST['name']); 
$email  = daddslashes($_POST['email']); 
$address  = daddslashes($_POST['address']); 
$mobile  = daddslashes($_POST['mobile']); 
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
		$sql = "SELECT * FROM `".$db_pre."kehu` WHERE id='".$id."'";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql);
		$smarty->assign(
			array(
				"rows"=>$rows,
			)
		);   
		break; 
	case 'insert':
		if (empty($title)){
			WriteErrMsg('公司名称不能为空');
		} 
		/******************** 判断公司名称是否已经存在 ******************/
		$sql = "SELECT id FROM `".$db_pre."kehu` WHERE `title`='".$title."'";
		$count = $db->num_rows($sql); 
		if ($count!=0){
			WriteErrMsg('该公司已存在，不可重复添加');
		}
		/****************************end******************************/
		$sql = "INSERT INTO `".$db_pre."kehu`( 
					`title`, 
					`name`,  
					`email`,
					`address`,
		            `mobile`,
					`add_time`
				)VALUES( 
					'".$title."',
					'".$name."',
					'".$email."',
					'".$address."',
					'".$mobile."',  
					'".$add_time."'
				)";
		if ($db->query($sql)){ 
			WriteSuccessMsg('添加成功', $CurrentPageName);
		}
		else{
			WriteErrMsg(mysql_error());
		}
		break; 
	case 'update':
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		} 
		if (empty($title)){
		    WriteErrMsg('公司名称不能为空');
		}
		if ($db->num_rows("SELECT id FROM `".$db_pre."kehu` WHERE id NOT IN ('" . $id . "') AND title='".$title."'")>0){
			WriteErrMsg('该公司已存在，不可重复添加');
		}
		$sql = "UPDATE `".$db_pre."kehu` SET  
					`title`='".$title."',
					`name`='".$name."',
					`email`='".$email."',
					`address`='".$address."',
					`mobile`='".$mobile."',
					`add_time`='".$add_time."'
				WHERE id='".$id."'"; 
		if ($db->query($sql)){
			WriteSuccessMsg('更新成功', $CurrentPageName);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
	case 'delete':
        if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		} 
		$sql = "DELETE FROM `".$db_pre."kehu` WHERE id='".$id."'";
		if ($db->query($sql)){
		        header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
	case 'operation':
		if (!is_array($id)){
			WriteErrMsg('参数错误');
		}
		$id = implode(",",$id); //explode(",",$id);
		switch ($operationclass){
			case 'batch_delete':
				$sql  = "DELETE FROM `".$db_pre."kehu` WHERE id IN (".$id.")";
				break;  
			default:
				WriteErrMsg('请选择批量操作选项');
		}
		if ($db->query($sql)){
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;  
	default:
		$url = $CurrentPageName;
		$url.= ($searchclass =='')?'':is_query_string($url).'searchclass='.$searchclass;
		$url.= ($keyword =='')?'':is_query_string($url).'keyword='.urlencode($keyword);
		$url.= is_query_string($url);
		$sql = "SELECT * FROM `".$db_pre."kehu` n WHERE 1";
		if ($keyword != ''){
			$sql.= ($searchclass==2)?" AND(`name` LIKE '%".$keyword."%')":" AND (`title` LIKE '%".$keyword."%')";
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
			)
		);
}
$smarty->display("admin/web_kehu.html");
$runtime->stop_write();
?>