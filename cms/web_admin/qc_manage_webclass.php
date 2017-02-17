<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action               = $_REQUEST['action'];
$class_id             = $_REQUEST['class_id'];
$class_name_cn        = $_REQUEST['class_name_cn']; 
$class_title_cn       = $_REQUEST['class_title_cn'];
$class_keywords_cn    = $_REQUEST['class_keywords_cn'];
$class_description_cn = $_REQUEST['class_description_cn']; 
$class_content_cn 	  = daddslashes($_REQUEST['class_content_cn']); 
$class_name_en        = $_REQUEST['class_name_en'];
$class_title_en       = $_REQUEST['class_title_en'];
$class_keywords_en    = $_REQUEST['class_keywords_en'];
$class_description_en = $_REQUEST['class_description_en'];
$class_content_en 	  = daddslashes($_REQUEST['class_content_en']); 
$class_name_jp        = $_REQUEST['class_name_jp'];
$class_title_jp       = $_REQUEST['class_title_jp'];
$class_keywords_jp    = $_REQUEST['class_keywords_jp'];
$class_description_jp = $_REQUEST['class_description_jp'];
$class_content_jp 	  = $_REQUEST['class_content_jp'];
$class_parent_id      = $_REQUEST['class_parent_id'];
$class_url            = $_REQUEST['class_url'];
$is_show              = $_REQUEST['is_show'];
$menu_module          = $_REQUEST['menu_module'];   

switch ($action){
	case 'add_banner':
		$smarty->assign("menu_array", webclass_array()); 
		$content_cn = '';
	if(!empty($_POST['content_cn'])) {
		if (get_magic_quotes_gpc()) {
			$content_cn = stripslashes($_POST['content_cn']);
		} else {
			$content_cn = $_POST['content_cn'];
		}
	}
		break;
	case 'add':
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql);
		$smarty->assign("rows",$rows);
		$class_content_cn = '';
	if(!empty($_POST['class_content_cn'])) {
		if (get_magic_quotes_gpc()) {
			$class_content_cn = stripslashes($_POST['class_content_cn']);
		} else {
			$class_content_cn = $_POST['class_content_cn'];
		}
	}
		break;
	case 'edit':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT *,(SELECT `class_name_cn` from `{$db_pre}web_class` where `class_id`=n.class_parent_id) as `class_parent_name` FROM `{$db_pre}web_class` n WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql);
		$smarty->assign("rows",$rows);
	$class_content_cn = '';
	if(!empty($_POST['class_content_cn'])) {
		if (get_magic_quotes_gpc()) {
			$class_content_cn = stripslashes($_POST['class_content_cn']);
		} else {
			$class_content_cn = $_POST['class_content_cn'];
		}
	}
		break; 
	case 'insert':
		if ($class_name_cn=='' && $class_name_en=='' && $class_name_jp=='' ){
			WriteErrMsg('栏目名称不能为空');
		}
		if ($class_parent_id==''){
			WriteErrMsg('栏目所属不能为空');
		}
		$sql = "SELECT MAX(class_id),MAX(class_root_id) FROM `{$db_pre}web_class`"; 
		$maxid = $db->getone($sql);
		$class_id = intval($maxid[0])+1;
		$maxrootid = intval($maxid[1]);
		$class_root_id = $maxrootid + 1;
		
		if ($class_parent_id > 0){
			$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_parent_id}";
			//echo 'sql 02.'.$sql.'<br>';
			$result = $db->getone($sql);
			$class_root_id     = $result['class_root_id'];
			$parentdepth       = $result['class_depth'];
			$parentbase       = $result['class_base_id'];
			$class_child       = $result['class_child'];
			$prevorderid       = $result['class_order_id'];
			$class_parent_path = $result['class_parent_path'].','.$class_parent_id;
			if ($class_child > 0){
				$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}web_class` WHERE class_parent_id={$class_parent_id}";
				//echo 'sql 03.'.$sql.'<br>';
				$prevorderid = $db->getone($sql);
				$prevorderid = intval($prevorderid[0]);
				$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_id={$class_parent_id} AND class_order_id={$prevorderid}";
				//echo 'sql 04.'.$sql.'<br>';
				$class_prev_id = $db->getone($sql);
				$class_prev_id = $class_prev_id[0];
				$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '$class_parent_path,%'";
				//echo 'sql 05.'.$sql.'<br>';
				$result = $db->getone($sql);
				if (intval($result[0]) > $prevorderid){
					$prevorderid = intval($result[0]);
				}
			}else{
				//父菜单的模型重置为空
				$sql = "update `{$db_pre}web_class` set `menu_module`=0 where class_id={$class_parent_id}" ;
				if($db->num_rows($sql)==1){ //确定只能影响一行
					$db->query($sql);
				}
				$class_prev_id = 0;
			}
		}else{
			if ($maxrootid > 0){
				$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_root_id={$maxrootid} AND class_depth=0";
				//echo 'sql 06.'.$sql.'<br>';
				$class_prev_id = $db->getone($sql);
				$class_prev_id = $class_prev_id[0];
			}
			$class_prev_id = intval($class_prev_id);
			$prevorderid = 0;
			$class_parent_path = 0;
		}
		if ($class_parent_id > 0){
			$class_depth = $parentdepth + 1;
			$class_base_id = $parentbase; 
		}
		else{
			$class_depth = 0; 
			$class_base_id = $class_id; 
		} 
		/****** 上传文件 ********/ 
		if($_FILES['img_url_cn']['tmp_name']!=''){
			$img_url_cn = upload_img($_FILES['img_url_cn']['tmp_name'],$_FILES['img_url_cn']['name'],"images");
		} 
		if($_FILES['img_url_en']['tmp_name']!=''){
			$img_url_en = upload_img($_FILES['img_url_en']['tmp_name'],$_FILES['img_url_en']['name'],"images");
		} 
		if($_FILES['img_url_jp']['tmp_name']!=''){
			$img_url_jp = upload_img($_FILES['img_url_jp']['tmp_name'],$_FILES['img_url_jp']['name'],"images");
		} 
		/****** 上传文件 ********/
		$sql = "INSERT INTO `{$db_pre}web_class`(
		`class_id`,`class_name_cn`,`class_name_en`,`class_name_jp`,`class_root_id`,`class_child`,
		`class_parent_id`,`class_depth`,`class_parent_path`,`class_order_id`,`class_prev_id`,
		`class_next_id`,`class_url`,`class_base_id`,
		`class_title_cn`,`class_keywords_cn`,`class_description_cn`,`class_content_cn`,`img_url_cn`,
		`class_title_en`,`class_keywords_en`,`class_description_en`,`class_content_en`,`img_url_en`,
		`class_title_jp`,`class_keywords_jp`,`class_description_jp`,`class_content_jp`,`img_url_jp`,
		`is_show`,`menu_module`
		)VALUES(
		'$class_id','$class_name_cn','$class_name_en','$class_name_jp','$class_root_id',0,
		'$class_parent_id','$class_depth','$class_parent_path','$prevorderid','$class_prev_id',
		0,'$class_url','$class_base_id',
		'$class_title_cn','$class_keywords_cn','$class_description_cn','$class_content_cn','$img_url_cn',
		'$class_title_en','$class_keywords_en','$class_description_en','$class_content_en','$img_url_en',
		'$class_title_jp','$class_keywords_jp','$class_description_jp','$class_content_jp','$img_url_jp',
		'$is_show','$menu_module'
		)"; 
		//echo 'sql 07.'.$sql.'<br>';
		if ($db->query($sql)){
			if ($class_prev_id > 0){
				$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_id} WHERE class_id={$class_prev_id}";
				//echo 'sql 08.'.$sql.'<br>';
				$db->query($sql);
			}
			if ($class_parent_id > 0){
				$sql = "UPDATE `{$db_pre}web_class` SET class_child=class_child+1 WHERE class_id={$class_parent_id}";
				//echo 'sql 09.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}web_class` SET class_order_id=class_order_id+1 WHERE class_root_id={$class_root_id} AND class_order_id>{$prevorderid}";
				//echo 'sql 10.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$prevorderid}+1 WHERE class_id={$class_id}";
				//echo 'sql 11.'.$sql.'<br>';
				$db->query($sql);
			}
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'网站导航设置','添加网站导航设置成功');

		}else{
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'网站导航设置','添加网站导航设置失败');
 			die(mysql_error());
		} 
		location($CurrentPageName);
		//header("Location:{$CurrentPageName}");
		break;
	case 'update':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		if ($class_name_cn=='' && $class_name_en=='' && $class_name_jp==''){
			WriteErrMsg('栏目名称不能为空');
		} 
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql) == 0){
			WriteErrMsg('找不到指定的栏目');
		}
		$result = $db->getone($sql);
		/****** 上传文件 ********/ 
		if ($_FILES['img_url_cn']['tmp_name']!=''){
			@unlink('../'.$result['img_url_cn']);
			$img_url_cn = upload_img($_FILES['img_url_cn']['tmp_name'], $_FILES['img_url_cn']['name'],'images');
		}else{
			$img_url_cn = $result['img_url_cn'];
		} 
		if ($_FILES['img_url_en']['tmp_name']!=''){
			@unlink('../'.$result['img_url_en']);
			$img_url_en = upload_img($_FILES['img_url_en']['tmp_name'], $_FILES['img_url_en']['name'],'images');
		}else{
			$img_url_en = $result['img_url_en'];
		} 
		if ($_FILES['img_url_jp']['tmp_name']!=''){
			@unlink('../'.$result['img_url_jp']);
			$img_url_jp = upload_img($_FILES['img_url_jp']['tmp_name'], $_FILES['img_url_jp']['name'],'images');
		}else{
			$img_url_jp = $result['img_url_jp'];
		} 
		/****** 上传文件 ********/     
		$sql = "UPDATE `{$db_pre}web_class` SET 
					class_name_cn='{$class_name_cn}', 
					class_name_en='{$class_name_en}', 
					class_name_jp='{$class_name_jp}', 
					class_url='{$class_url}',
					class_title_cn='{$class_title_cn}',
					class_keywords_cn='{$class_keywords_cn}',
					class_description_cn='{$class_description_cn}', 
					class_content_cn='{$class_content_cn}', 
					img_url_cn='{$img_url_cn}',
					class_title_en='{$class_title_en}',
					class_keywords_en='{$class_keywords_en}',
					class_description_en='{$class_description_en}',
					class_content_en='{$class_content_en}', 
					img_url_en='{$img_url_en}',
					class_title_jp='{$class_title_jp}',
					class_keywords_jp='{$class_keywords_jp}',
					class_description_jp='{$class_description_jp}',
					class_content_jp='{$class_content_jp}', 
					img_url_jp='{$img_url_jp}',
					is_show='{$is_show}',
					menu_module='{$menu_module}'
				WHERE class_id={$class_id}";
		//echo 'sql 36.'.$sql.'<br>';
	   if ($db->query($sql)){
		   location($CurrentPageName);
		   //insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'网站导航设置','更新网站导航设置成功');
		   exit;
	   }else{
		   die(mysql_error());
		   //insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'网站导航设置','更新网站导航设置失败');
	   }
	   break;
	case 'delete':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$rows = $db->getone($sql);
		@unlink("../".$rows['img_url_cn']);
		@unlink("../".$rows['img_url_en']);
		@unlink("../".$rows['img_url_jp']);
		$class_depth       = $rows['class_depth'];
		$class_child       = $rows['class_child'];
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_parent_id   = $rows['class_parent_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$class_root_id     = $rows['class_root_id'];
		$sql = "DELETE FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		$db->query($sql);
		if ($class_depth > 0){
			$sql = "UPDATE `{$db_pre}web_class` SET class_child=class_child-1 WHERE class_id={$class_parent_id}";
			$db->query($sql);
		}
		if ($class_child > 0){
			$sql = "DELETE FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			$db->query($sql);
		}
		if ($class_prev_id > 0){
			$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			$db->query($sql);
		}
		if ($class_next_id > 0){
			$sql = "UPDATE `{$db_pre}web_class` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
			$db->query($sql);
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_root_id={$class_root_id} ORDER BY class_root_id,class_order_id";
		$result = $db->getall($sql);
		if (is_array($result)){
			$i = 0;
			foreach ($result as $key=>$value){
				$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$i} WHERE class_id={$value['class_id']}";
				$db->query($sql);
				$i++;
			}
		}
		//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'网站导航设置','删除网站栏目成功');
		header("Location:{$CurrentPageName}");
		break;
	case 'order':
		$smarty->assign("menu_array",webclass_array(' and class_parent_id=0')); 
		break;
	case 'up_order':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_root_id     = $rows['class_root_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_prev_id}";
		//echo 'sql 03.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$crootid     = $rows['class_root_id'];
		$cparentpath = $rows['class_parent_path'].','.$class_prev_id;
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$crootid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
		//echo 'sql 04.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$class_root_id},class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		//echo 'sql 05.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_id} WHERE class_id={$cprevid}";
		$db->query($sql);
		//echo 'sql 06.'.$sql.'<br>';
		$sql = "UPDATE `{$db_pre}web_class` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
		//echo 'sql 07.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$crootid} WHERE class_parent_path LIKE '%{$class_parent_path}%'";
		//echo 'sql 08.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$class_root_id} WHERE class_parent_path LIKE '%{$cparentpath}%'";
		//echo 'sql 09.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order");
		break;
	case 'down_order':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_root_id     = $rows['class_root_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_next_id}";
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$crootid     = $rows['class_root_id'];
		$cparentpath = $rows['class_parent_path'].','.$class_next_id;
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$crootid},class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$class_root_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_prev_id={$class_id} WHERE class_id={$cnextid}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$crootid} WHERE class_parent_path LIKE '%{$class_parent_path}%'";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_root_id={$class_root_id} WHERE class_parent_path LIKE '%{$cparentpath}%'";
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order");
		break;
	case 'order_other':
		$menu_array = webclass_array();
		for ($i = 0; $i < count($menu_array); $i++){
			$sql = "SELECT Count(class_id) FROM `{$db_pre}web_class` WHERE class_parent_id={$menu_array[$i]['class_parent_id']} AND class_order_id<{$menu_array[$i]['class_order_id']}";
			$result = $db->getone($sql);
			$menu_array[$i]['class_up'] = $result[0];
			$sql = "SELECT Count(class_id) FROM `{$db_pre}web_class` WHERE class_parent_id={$menu_array[$i]['class_parent_id']} AND class_order_id>{$menu_array[$i]['class_order_id']}";
			$result = $db->getone($sql);
			$menu_array[$i]['class_down'] = $result[0];
		}
		//print_r($menu_array);
		$smarty->assign("menu_array",$menu_array);
		break;
	case 'up_order_other':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_order_id    = $rows['class_order_id'];
		$class_child       = $rows['class_child'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$class_parent_id   = $rows['class_parent_id'];
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_prev_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$corderid    = $rows['class_order_id'];
		$cchild      = $rows['class_child'];
		$cparentpath = $rows['class_parent_path'].','.$class_prev_id;
		if ($class_child > 0 && $cchild == 0){
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			//echo 'sql 03.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 04.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$classcount}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 05.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 06.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 07.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}elseif ($class_child == 0 && $cchild > 0){
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 08.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 09.'.$sql.'<br>';
			$db->query($sql);	
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 10.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 11.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
		}elseif ($class_child > 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			//echo 'sql 12.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;	
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 13.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$classcount}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 14.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 15.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 16.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 17.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid}+{$classcount}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 18.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}else{
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 19.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id},class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 20.'.$sql.'<br>';
			$db->query($sql);	
		}
		$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_id} WHERE class_id={$cprevid}";
		//echo 'sql 21.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
		//echo 'sql 22.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order_other");
		break;
	case 'down_order_other':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_order_id    = $rows['class_order_id'];
		$class_child       = $rows['class_child'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$class_parent_id   = $rows['class_parent_id'];
		$sql = "SELECT * FROM `{$db_pre}web_class` WHERE class_id={$class_next_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$corderid    = $rows['class_order_id'];
		$cchild      = $rows['class_child'];
		$cparentpath = $rows['class_parent_path'].','.$class_next_id;
		if ($class_child > 0 && $cchild == 0){
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 03.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 04.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 05.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 06.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}elseif ($class_child == 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%'";
			//echo 'sql 07.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+{$classcount}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 08.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 09.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 10.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 11.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
		}elseif ($class_child > 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%'";
			//echo 'sql 12.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+{$classcount}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 13.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 14.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 15.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+{$classcount}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 16.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
			$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 17.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 18.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}else{
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$corderid},class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 19.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}web_class` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 20.'.$sql.'<br>';
			$db->query($sql);	
		}
		$sql = "UPDATE `{$db_pre}web_class` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		//echo 'sql 21.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}web_class` SET class_prev_id={$class_id} WHERE class_id={$cnextid}";
		//echo 'sql 22.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order_other");
		break;
	/*全栏目更新关键字和描述 20121118_add*/
	case 'update_key_des':
		$sql = "update `{$db_pre}web_class` set 
				`class_keywords_cn`='".$web_config['Web_Keywords_cn']."',
				`class_description_cn`='".$web_config['Web_Description_cn']."',
				`class_keywords_en`='".$web_config['Web_Keywords_en']."',
				`class_description_en`='".$web_config['Web_Description_en']."',
				`class_keywords_jp`='".$web_config['Web_Keywords_jp']."',
				`class_description_jp`='".$web_config['Web_Description_jp']."'
			  " ;
		//print_r( $sql);exit('123');	  
		if ($db->query($sql)){
			WriteSuccessMsg('更新成功', $CurrentPageName);
		}else{
			WriteErrMsg(mysql_error());
		}
		break ;
	default: 
		$smarty->assign('menu_array',webclass_array()); 
}
$smarty->display('admin/qc_manage_webclass.html');
$runtime->stop_write();
?>