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
$order_id = $_POST['order_id'];
$kid = $_REQUEST['kid'];
$title_cn  = daddslashes($_POST['title_cn']); 
$title_en  = daddslashes($_POST['title_en']); 
$title_jp  = daddslashes($_POST['title_jp']); 
$price=$_POST['price'];
$start_time=$_POST['start_time'];
$end_time=$_POST['end_time'];
$content_cn  = daddslashes($_POST['content_cn']); 
$content_en  = daddslashes($_POST['content_en']); 
$content_jp  = daddslashes($_POST['content_jp']);
$add_time     = $_POST['add_time'];  
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
$webclass_array = webclass_array(" and class_base_id=11 ", true);
if (is_array($webclass_array)){
	foreach ($webclass_array as $key=>$value){
		$class_array[$value['class_id']] = $value['class_icon'].$value['class_name_cn'];
		$class_id_array .= $value['class_id'].",";
	}	
	$class_id_array = trim($class_id_array,',');
} 
//客户信息
$kehu=$db->getall("SELECT id,title FROM `".$db_pre."kehu` WHERE 1 order by add_time desc");

switch ($action){ 
	case 'add':  
	    //排序处理
	    $sql = "SELECT id FROM `".$db_pre."listinfo` WHERE 1";
	    $count = $db->num_rows($sql);
	    $count+= 1;
	    for ($i = 1; $i <= $count; $i++){
	        $order_id[$i] = $i;
	    }
		$smarty->assign(
			array( 
			    "count"=>$count,
			    "order_id"=>$order_id,
			    "kehu"=>$kehu,
				"class_array"=>$class_array,
				"add_time"=>date("Y-m-d H:i:s"),
			)
		);

		break; 
	case 'edit':
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `".$db_pre."listinfo` WHERE id='".$id."'";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql); 
		//排序处理
		$sql = "SELECT id FROM `".$db_pre."listinfo` WHERE 1 AND class_id ='".$class_id."'";
		$count = $db->num_rows($sql);
		for ($i = 1; $i <= $count; $i++){
		    $order_id[$i] = $i;
		}
		$smarty->assign(
			array(
			    "kehu"=>$kehu,
			    "order_id"=>$order_id,
				"class_array"=>$class_array,
				"rows"=>$rows,
			)
		);   
		break; 
		
	case 'insert':
		if (!is_numeric($class_id)){
			WriteErrMsg('请选择栏目所属');
		} 
		if (!is_numeric($kid) || empty($kid)){
		    WriteErrMsg('请选择客户所属');
		}
		if ($db->num_rows("SELECT class_id FROM `".$db_pre."web_class` WHERE class_parent_id='".$class_id."'")>0){
			WriteErrMsg('您选择的栏目下不能包含子分类');
		}
		/****** 排序处理 ********/
		$sql = "SELECT id FROM `".$db_pre."listinfo` WHERE 1";
		$count = $db->num_rows($sql);
		if ($order_id <= $count){
		    $sql = "UPDATE `".$db_pre."listinfo` SET order_id=order_id+1 WHERE order_id>='".$order_id."' AND order_id<='".$count."'";
		    $db->query($sql);
		}
		/****** 排序处理 ********/
		$sql = "INSERT INTO `".$db_pre."listinfo`( 
					`class_id`,
		            `order_id`,
		            `kid`,
					`title_cn`,  
					`title_en`,  
					`title_jp`,
		            `price`,
		            `start_time`,  
					`end_time`,
					`content_cn`,  
					`content_en`, 
					`content_jp`,
					`img_url_cn`,
					`img_url_en`,
		            `img_url_jp`,
					`add_time`
				)VALUES(
		            '".$class_id."',
					'".$order_id."',
					'".$kid."',
					'".$title_cn."',
					'".$title_en."',
					'".$title_jp."',
					'".$price."',
					'".$start_time."',
					'".$end_time."',
					'".$content_cn."',
					'".$content_en."',  
					'".$content_jp."',  
					'".$img_url_cn."',  
					'".$img_url_en."',
				    '".$img_url_jp."',  
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
		$sql = "SELECT `order_id`  FROM `".$db_pre."listinfo` WHERE id='".$id."' LIMIT 0,1";
		$rows = $db->getone($sql);
		/****** 排序处理 ********/
		if ($order_id > $rows['order_id']) {
		    $sql = "UPDATE `".$db_pre."listinfo` SET order_id=order_id-1 WHERE order_id > '".$rows['order_id']."' AND order_id <= '".$order_id."'";
		    $sql.= ($language!='')?" AND language='".$language."'":"";
		    $sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
		    $db->query($sql);
		}elseif($order_id < $rows['order_id']) {
		    $sql = "UPDATE `".$db_pre."listinfo` SET order_id=order_id+1 WHERE order_id >= '".$order_id."' AND order_id < '".$rows['order_id']."'";
		    $sql.= ($language!='')?" AND language='".$language."'":"";
		    $sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
		    $db->query($sql);
		}
		/****** 排序处理 ********/
		$sql = "UPDATE `".$db_pre."listinfo` SET  
					`class_id`='".$class_id."',
					`order_id`='".$order_id."',
					`kid`='".$kid."',
					`title_cn`='".$title_cn."',
					`title_en`='".$title_en."',
					`title_jp`='".$title_jp."',
					`price`='".$price."',
					`start_time`='".$start_time."',
					`end_time`='".$end_time."',    
					`content_en`='".$content_en."',
					`content_cn`='".$content_cn."',"
					.(empty($img_url_cn)?"":"`img_url_cn`='".$img_url_cn."',")
					.(empty($img_url_en)?"":"`img_url_en`='".$img_url_en."',")
					.(empty($img_url_jp)?"":"`img_url_jp`='".$img_url_jp."',")
					."`content_jp`='".$content_jp."'
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
		
		
	//------------------向上、向下排序--------------------------
	case 'up_order':
	    if (!is_numeric($id)) WriteErrMsg('参数错误');
	    //if (!is_numeric($class_id)) WriteErrMsg('分类参数错误');
	    update_order($db_pre.'listinfo',$id,'up',$language,$class_id);
	    header('Location:'.$_SERVER['HTTP_REFERER']);
	    break;
	case 'down_order':
	    if(!is_numeric($id)) WriteErrMsg('参数错误');
	    //if(!is_numeric($class_id)) WriteErrMsg('分类参数错误');
	    update_order($db_pre.'listinfo',$id,'down',$language,$class_id);
	    header('Location:'.$_SERVER['HTTP_REFERER']);
	    break;
	    //------------------向上、向下排序--------------------------
	default:
		$url = $CurrentPageName;
		$url.= ($class_id =='')?'':is_query_string($url).'class_id='.$class_id;
		$url.= ($searchclass =='')?'':is_query_string($url).'searchclass='.$searchclass;
		$url.= ($keyword =='')?'':is_query_string($url).'keyword='.urlencode($keyword);
		$url.= is_query_string($url);
		$sql = "SELECT *,(SELECT class_name_cn FROM `".$db_pre."web_class` AS w WHERE w.class_id=n.class_id) AS class_name FROM `".$db_pre."listinfo` n WHERE 1";
		$sql.= ($language!='')?" AND `language`='".$language."'":"";
		$sql.= ($class_id!='')?" AND `class_id`='".$class_id."'":"";
		$sql.= " and class_id in(".$class_id_array.")"; 
		if ($keyword != ''){
			$sql.= ($searchclass==2)?" AND (`content` LIKE '%".$keyword."%')":" AND (`title_cn` LIKE '%".$keyword."%')";
		}
		switch ($order){
			case 'class_id':  	$sql .= " ORDER BY class_id";  	break;
			case 'is_index':  	$sql .= " ORDER BY is_index";  	break;
			case 'is_show':   	$sql .= " ORDER BY is_show";   	break;
			case 'add_time':  		$sql .= " ORDER BY add_time";  		break;
			default: 			$sql .= " ORDER BY order_id";
		}
		switch ($sort){
			case 'asc': 	$sql .= " ASC"; 	break;
			case 'desc': 	$sql .= " DESC"; 	break;
			default: 		$sql .= " ASC";
		} 
		/****** 第二个排序参数 ********/ 
		if($order!=''&&$order!='add_time')	$sql .= ",add_time DESC"; 
		/****** 第二个排序参数 ********/ 
		$list_array = page($sql,$page,$web_config['Web_PageSize'],$url);
//-------------------获得第一个和最后一个信息的ID 开始-------------------
		$sql = "SELECT `id` FROM `".$db_pre."listinfo` WHERE 1";
		$sql.= ($language!='')?" AND language='".$language."'":"";
		$sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
		$sql.= " ORDER BY order_id ASC LIMIT 0,1";
		$order_up_id = $db->getone($sql);
		$order_up_id = $order_up_id[0];
		
		$sql = "SELECT `id` FROM `".$db_pre."listinfo` WHERE 1";
		$sql.= ($language!='')?" AND language='".$language."'":"";
		$sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
		$sql.= " ORDER BY order_id DESC LIMIT 0,1";
		$order_down_id = $db->getone($sql);
		$order_down_id = $order_down_id[0];
//-------------------获得第一个和最后一个信息的ID 结束-------------------
		$smarty->assign(
			array( 
				"order_class_id"=>orderby('class_id','分类'),
				"order_is_index"=>orderby('is_index','推荐'),
				"order_time"=>orderby('add_time','时间'),
				"list_array"=>$list_array,
				"class_array"=>$class_array,
				"order_up_id"=>$order_up_id,
				"order_down_id"=>$order_down_id,
				"url"=>$url
			)
		);
}
$smarty->display("admin/web_product_listinfo.html");
$runtime->stop_write();
?>