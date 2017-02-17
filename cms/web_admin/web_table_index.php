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
$language       = $_REQUEST['language'];
$language       = ($language=='')?'cn':$language;
$page           = $_REQUEST['page'];
$page           = ($page>0)?$page:1;
$order          = $_GET['order'];
$sort           = $_GET['sort'];
$operationclass = $_POST['operationclass']; 

$id       = $_REQUEST['id']; 
$title    = $_POST['title'];
$add_time     = $_POST['add_time'];
$order_id = $_POST['order_id']; 
$web_url_cn    = $_POST['web_url_cn'];  
$web_url_en    = $_POST['web_url_en'];
$web_url_jp    = $_POST['web_url_jp'];
/******************** 上传图片处理  ******************/
$img_url_cn ='';
$img_url_en ='';
$img_url_jp ='';
if($_FILES['img_url_cn']['tmp_name']!=''){
    $img_url_cn = upload_img($_FILES['img_url_cn']['tmp_name'],$_FILES['img_url_cn']['name'],"images");
}
if($_FILES['img_url_en']['tmp_name']!=''){
    $img_url_en = upload_img($_FILES['img_url_en']['tmp_name'],$_FILES['img_url_en']['name'],"images");
}
if($_FILES['img_url_jp']['tmp_name']!=''){
    $img_url_jp = upload_img($_FILES['img_url_jp']['tmp_name'],$_FILES['img_url_jp']['name'],"images");
}
switch ($action){	
//------------------添加一条信息--------------------------
	case 'add':  
		$smarty->assign(
			array(    
				"add_time"=>date("Y-m-d H:i:s"),
			)
		);
		break;
//------------------添加一条信息--------------------------
		
//------------------编辑一条信息--------------------------
	case 'edit':
		if(!is_numeric($id))  WriteErrMsg('参数错误'); 
		$sql = "SELECT * FROM `".$db_pre."table_flash` WHERE id='".$id."' and typeof =2 ";
		if($db->num_rows($sql)==0)	WriteErrMsg('操作错误'); 
		$rows = $db->getone($sql);  
		$smarty->assign(
			array(  
				"rows"=>$rows
			)
		);
		break; 
//------------------编辑一条信息--------------------------
		
//------------------插入一条信息--------------------------
	case 'insert': 
		if ($title=='') WriteErrMsg('名称不能为空');  
		/****** 上传文件 ********/ 
		$sql = "INSERT INTO `".$db_pre."table_flash`( 
		            `class_id`,
		            `typeof`,
					`title`,
					`img_url_cn`,
					`img_url_en`,
					`img_url_jp`,
					`web_url_cn`,  
					`web_url_en`,
					`web_url_jp`,
					`add_time` 
				)VALUES( 
		            '1',
		            '2',
					'".$title."',
					'".$img_url_cn."',
					'".$img_url_en."',
					'".$img_url_jp."',
					'".$web_url_cn."', 
					'".$web_url_en."',
					'".$web_url_jp."',
					'".$add_time."' 
				)";
		if ($db->query($sql)){
			WriteSuccessMsg('添加成功', $CurrentPageName.'?language='.$language);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------插入一条信息--------------------------

		
//------------------更新一条信息--------------------------
	case 'update': 
	    if (!is_numeric($id)){
	        WriteErrMsg('参数错误');
	    }
		if($title=='') WriteErrMsg('名称不能为空');  
		$sql = "UPDATE `".$db_pre."table_flash` SET  
					`title`='".$title."',"
					.(empty($img_url_cn)?"":"`img_url_cn`='".$img_url_cn."',")
					.(empty($img_url_en)?"":"`img_url_en`='".$img_url_en."',")
					.(empty($img_url_jp)?"":"`img_url_jp`='".$img_url_jp."',")
					."`web_url_cn`='".$web_url_cn."', 
					`web_url_en`='".$web_url_en."', 
					`web_url_jp`='".$web_url_jp."'
				WHERE id='".$id."'"; 
		if ($db->query($sql)){
			WriteSuccessMsg('更新成功', $CurrentPageName.'?language='.$language);
		}
		else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------更新一条信息--------------------------
		
//------------------删除一条信息--------------------------
	case 'delete':
		if(!is_numeric($id))	WriteErrMsg('参数错误'); 
		$sql = "DELETE FROM `".$db_pre."table_flash` WHERE id='".$id."'";
		if ($db->query($sql)){
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------删除一条信息--------------------------
		
//------------------重新排序-----------------------------
	case 'reorder':
		reorder($db_pre.'table_flash',$language);
		header('Location:'.$_SERVER['HTTP_REFERER']);
		break;
//------------------重新排序-----------------------------
		
		
	case 'operation':
		if(!is_array($id)) WriteErrMsg('参数错误'); 
		$id = implode(",",$id); 
		switch ($operationclass){
			/****** 批量操作 ********/ 
			case 'batch_delete':   
				$sql  = "DELETE FROM `".$db_pre."table_flash` WHERE id IN (".$id.")"; 
				break;
			case 'batch_index_1': $sql  = "UPDATE `".$db_pre."table_flash` SET is_index='1' WHERE id IN (".$id.")"; break;
			case 'batch_index_0': $sql  = "UPDATE `".$db_pre."table_flash` SET is_index='0' WHERE id IN (".$id.")"; break;
			case 'batch_show_1': $sql  = "UPDATE `".$db_pre."table_flash` SET is_show='1' WHERE id IN (".$id.")"; break;
			case 'batch_show_0': $sql  = "UPDATE `".$db_pre."table_flash` SET is_show='0' WHERE id IN (".$id.")"; break;
			default: WriteErrMsg('请选择批量操作选项');
			/****** 批量操作 ********/ 
		}
		if($db->query($sql))  header('Location:'.$_SERVER['HTTP_REFERER']);
		else WriteErrMsg(mysql_error()); 
		break; 
		
//------------------推荐--------------------------
	case 'index':
		if(!is_numeric($id)) WriteErrMsg('参数错误'); 
		update_flag($db_pre.'table_flash','is_index',$id);
		header('Location:'.$_SERVER['HTTP_REFERER']);
		break;
//------------------推荐--------------------------
		
//------------------审核--------------------------
	case 'show':
		if(!is_numeric($id)) WriteErrMsg('参数错误'); 
		update_flag($db_pre.'table_flash','is_show',$id);
		header('Location:'.$_SERVER['HTTP_REFERER']);
		break;
//------------------审核--------------------------
		
	default:
		$url = $CurrentPageName;
		$url.= ($language =='')?'':is_query_string($url).'language='.$language; 
		$url.= ($searchclass =='')?'':is_query_string($url).'searchclass='.$searchclass;
		$url.= ($keyword =='')?'':is_query_string($url).'keyword='.urlencode($keyword);
		$url.= is_query_string($url);
		$sql = "SELECT * FROM `".$db_pre."table_flash` n WHERE 1 and class_id=1 and typeof =2 ";
		if ($keyword != ''){
			$sql.= ($searchclass==2)?" AND (`content` LIKE '%".$keyword."%')":" AND (`title` LIKE '%".$keyword."%')";
		}
		switch ($order){ 
			case 'is_index':  	$sql .= " ORDER BY is_index";  	break;
			case 'is_show':   	$sql .= " ORDER BY is_show";   	break;
			case 'add_time':  		$sql .= " ORDER BY add_time";  		break;
			default: 			$sql .= " ORDER BY add_time";
		}
		switch ($sort){
			case 'asc': 	$sql .= " ASC"; 	break;
			case 'desc': 	$sql .= " DESC"; 	break;
			default: 		$sql .= " DESC";
		} 
		/****** 第二个排序参数 ********/ 
		if($order!=''&&$order!='add_time')	$sql .= ",add_time DESC"; 
		/****** 第二个排序参数 ********/ 
		$list_array = page($sql,$page,$web_config['Web_PageSize'],$url);
		$smarty->assign(
			array(  
				"order_is_index"=>orderby('is_index','推荐'),
				"order_is_show"=>orderby('is_show','审核'),
				"order_time"=>orderby('add_time','时间'),
				"list_array"=>$list_array, 
				"order_up_id"=>$order_up_id,
				"order_down_id"=>$order_down_id,
				"url"=>$url
			)
		);
}
$smarty->display("admin/web_table_index.html");
$runtime->stop_write();
?>