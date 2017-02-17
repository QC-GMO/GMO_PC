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
$add_time     = $_POST['add_time'];  
/******************** 上传图片处理  ******************/

$webclass_array = webclass_array(" and class_base_id in (25,26,28,29,30,31,35) ", true);
if (is_array($webclass_array)){
	foreach ($webclass_array as $key=>$value){
		$class_array[$value['class_id']] = $value['class_icon'].$value['class_name_cn'];
		$class_id_array .= $value['class_id'].",";
	}	
	$class_id_array = trim($class_id_array,',');
} 
switch ($action){ 
	case 'add':  
		$smarty->assign(
			array( 
				"class_array"=>$class_array,
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
		if (!is_numeric($class_id)){
			WriteErrMsg('类别不能为空');
		} 
		if ($db->num_rows("SELECT class_id FROM `".$db_pre."web_class` WHERE class_parent_id='".$class_id."'")>0){
			WriteErrMsg('您选择的分类下不能包含子分类');
		} 
		/******************** 判断单页是否已经存在 *****************/
		 $sql = "SELECT id FROM `".$db_pre."pageinfo` WHERE `class_id`='".$class_id."'";
		 $count = $db->num_rows($sql);
		 if ($count!=0){
		 WriteErrMsg('单页已存在，不可重复添加');
		 }
		 
		/****************************end******************************/
		$sql = "INSERT INTO `".$db_pre."pageinfo`( 
					`class_id`, 
					`title_cn`,
		            `title_en`, 
		            `title_jp`, 
		            `content_cn`, 
					`content_en`,  
		            `content_jp`, 
					`add_time`
				)VALUES( 
					'".$class_id."',
					'".$title_cn."',
					'".$title_en."',
					'".$title_jp."',
					'".$content_cn."',
					'".$content_en."',
					'".$content_jp."',
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
		if (!is_numeric($id)&&!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		} 
		if ($db->num_rows("SELECT class_id FROM `".$db_pre."web_class` WHERE class_parent_id='".$class_id."'")>0){
			WriteErrMsg('您选择的分类下不能包含子分类');

		}   
		/******************** 判断单页是否已经存在 *****************/
		 $sql = "SELECT id FROM `".$db_pre."pageinfo` WHERE `id` not in ('".$id."') and `class_id`='".$class_id."'";
		 $count = $db->num_rows($sql);
		 if ($count!=0){
		 WriteErrMsg('单页已存在，不可重复添加');
		 }
		 
		/****************************end******************************/
		$sql = "UPDATE `".$db_pre."pageinfo` SET  
					`class_id`='".$class_id."',
					`title_cn`='".$title_cn."',
					`title_en`='".$title_en."',
					`title_jp`='".$title_jp."',
					`content_cn`='".$content_cn."',
					`content_en`='".$content_en."',
					`content_jp`='".$content_jp."'
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
		$sql = "DELETE FROM `".$db_pre."pageinfo` WHERE id='".$id."'";
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
				$sql  = "DELETE FROM `".$db_pre."pageinfo` WHERE id IN (".$id.")";
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
		$url.= ($class_id =='')?'':is_query_string($url).'class_id='.$class_id;
		$url.= ($searchclass =='')?'':is_query_string($url).'searchclass='.$searchclass;
		$url.= ($keyword =='')?'':is_query_string($url).'keyword='.urlencode($keyword);
		$url.= is_query_string($url);
		$sql = "SELECT *,(SELECT class_name_cn FROM `".$db_pre."web_class` AS w WHERE w.class_id=n.class_id) AS class_name FROM `".$db_pre."pageinfo` n WHERE 1";
		$sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
		$sql.=" and class_id in(".$class_id_array.")";
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
				"order_class_id"=>orderby('class_id','分类'), 
				"order_time"=>orderby('add_time','时间'),
				"list_array"=>$list_array,
				"class_array"=>$class_array 
			)
		);
}
$smarty->display("admin/web_module_pageinfo.html");
$runtime->stop_write();
?>