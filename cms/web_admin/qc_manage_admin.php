<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action = $_REQUEST['action'];
$id = $_REQUEST['id'];
$class_id    = $_POST['class_id'];
$username    = $_POST['username'];
$password    = $_POST['password'];
$conpassword = $_POST['conpassword'];
$oldpassword = $_POST['oldpassword'];
$flag        = $_REQUEST['flag'];
$purview     = $_POST['purview'];
$operationclass = $_REQUEST['operationclass']; 
switch ($action){
	case 'add':
		break;
	case 'edit':
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}   
		if($Session['Admin']['UserID']==$id || $Session['Admin']['Flag']<2){  //如果是编辑自己权限 
			$sql = "SELECT * FROM `{$db_pre}manage` WHERE id={$id}";
		}else{ 
			$sql = "SELECT * FROM `{$db_pre}manage` WHERE flag>{$Session['Admin']['Flag']} AND id={$id}";
		}   
		if($db->num_rows($sql)==0){ 
			WriteErrMsg('等级相同，不能编辑对方资料'); 
		} 
		$rows = $db->getone($sql);
		$smarty->assign('rows',$rows);
		break;
	case 'insert': 
		if ($username == ''){
			WriteErrMsg('用户名不能为空');
		}
		if ($password == ''){
			WriteErrMsg('密码不能为空');
		}
		if ($conpassword != $password){
			WriteErrMsg('密码和确认密码不一致');
		}
		if ($db->num_rows("SELECT id FROM `{$db_pre}manage` WHERE username='{$username}'")>0){
			WriteErrMsg('数据库中已经存在这个用户了! 请输入一个独一无二的用户名^_^');
		}  
		if($Session['Admin']['Flag']>1){ //超级和高级管理员不做限制
			if(!is_numeric($flag) || $flag<=$Session['Admin']['Flag']){
				WriteErrMsg('只能添加等级低于自己的管理员');
			} 
		}
		$sql = "INSERT INTO `{$db_pre}manage` (username,password,flag)VALUES('{$username}','".md5($password)."','{$flag}')";
		if ($db->query($sql)){
			WriteSuccessMsg('成功添加',$CurrentPageName);
		}
		//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'管理成员设置','添加管理员成功');
		break;
	case 'update': 
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}
		if($password != ''){ 
				if($oldpassword == ''){
					WriteErrMsg('请输入旧密码');
				}
				$sql = "SELECT id FROM `{$db_pre}manage` WHERE password='".md5($oldpassword)."' AND id={$id}";
				if ($db->num_rows($sql)==0){
					WriteErrMsg('您输入的旧密码错误');
				}	
//			}
			if($conpassword != $password){
				WriteErrMsg('两次输入的新密码不相同');
			}
		}  
		if($Session['Admin']['Flag']>1){  //超级或者高级  不限制
			if($Session['Admin']['UserID']!=$id){  //先判断是不是自己 
				if(!is_numeric($flag) || $flag<=$Session['Admin']['Flag']){
					WriteErrMsg('管理员权限必须低于自己');
				} 
			}else{
				$flag=$Session['Admin']['Flag'];  //不能自己修改自己的级别
			}
		}
		/****************/
		$sql = "UPDATE `{$db_pre}manage` SET flag='{$flag}'"; 
		$sql.= ($password!='')?",password='".md5($password)."'":"";
		$sql.= " WHERE flag>={$Session['Admin']['Flag']} AND id={$id}";
		if ($db->query($sql)){
			if ($Session['Admin']['UserID']==$id){
				$sql = "SELECT * FROM `{$db_pre}manage` WHERE flag>={$Session['Admin']['Flag']} AND id={$id} LIMIT 0,1";
				$rows = $db->getone($sql);
				$_SESSION[$web_config['Web_SessionName'].'_Admin_PassWord'] = $rows['password'];
				$_SESSION[$web_config['Web_SessionName'].'_Admin_Flag'] = $rows['flag'];
			}
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'管理成员设置','更新管理员信息成功');
			WriteSuccessMsg('成功更新',$CurrentPageName);
		}else{
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'管理成员设置','更新管理员信息失败');
			WriteErrMsg(mysql_error());
		}
		break;
	case 'delete': 
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		} 
		if ($id == $Session['Admin']['UserID']){
			WriteErrMsg('您不可以删除您当前登录的账号');
		} 
		if($Session['Admin']['Flag']>1){   //超级和高级不限制
			$sql = "SELECT * FROM `{$db_pre}manage` WHERE flag>{$Session['Admin']['Flag']} AND id={$id}"; 
			if ($db->num_rows($sql)==0){ 
				WriteErrMsg('等级相同，不能操作对方信息');
			}
		}
		$sql = "DELETE FROM `{$db_pre}manage` WHERE flag>{$Session['Admin']['Flag']} AND id={$id}";
		if ($db->query($sql)){
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'管理成员设置','删除用户信息成功');
			WriteSuccessMsg('成功删除',$CurrentPageName);
		}
		else{
			//insert_Adminlog($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'],'管理成员设置','删除用户信息失败');
			WriteErrMsg(mysql_error());
		}
		break; 
	case 'purview_install':  
		/**20121112_add**/
		$sql = "SELECT * FROM `{$db_pre}manage` WHERE flag>{$Session['Admin']['Flag']} AND id={$id}"; 
		if ($db->num_rows($sql)==0){ 
			WriteErrMsg('等级相同，不能设置对方权限'); 
		}  
		/***************/
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}
		
		$sql = "SELECT * FROM `{$db_pre}manage` WHERE id={$id}";
		$rows = $db->getone($sql);   
		$rows['purview'] = explode(',',$rows['purview']);
		$smarty->assign('rows',$rows);  
		
		/*20121111_add管理员 只能设置自己拥有的权限给其他人    超级和高级除外*/ 
		$condition = ($Session['Admin']['Flag']<2)?"":" AND `class_id` in (".$Session['Admin']['Purview'].")";
		/*******************/
		$sql = "SELECT *,(SELECT count(class_id) FROM `{$db_pre}menu` WHERE class_parent_id=A.class_id AND is_show=1 AND menu_flag>0) AS class_count FROM `{$db_pre}menu` AS A WHERE is_show=1 AND menu_flag>0 AND class_depth=0 ORDER BY class_root_id";
		$menu_array = $db->getall($sql);
		if (is_array($menu_array)){
			foreach($menu_array as $key=>$value){
				$sql = "SELECT * FROM `{$db_pre}menu` WHERE is_show=1 AND `menu_flag`>".$Session['Admin']['Flag']." AND class_parent_id={$menu_array[$key]['class_id']}".$condition." ORDER BY class_order_id";
				$menu_array[$key]['son']  = $db->getall($sql);  
			}			
		}
		$smarty->assign('menu_array',$menu_array);
		break;
	case 'purview_update':
		/**20121112_add**/
		$sql = "SELECT * FROM `{$db_pre}manage` WHERE flag>{$Session['Admin']['Flag']} AND id={$id}"; 
		if ($db->num_rows($sql)==0){ 
			WriteErrMsg('等级相同，不能设置对方权限'); 
		}  
		/***************/
		if (!is_numeric($id)){
			WriteErrMsg('参数错误');
		}
		$class_id = implode(",",$class_id);
		$sql = "UPDATE `{$db_pre}manage` SET purview='{$class_id}' WHERE id={$id}";
		if ($db->query($sql)){
			WriteSuccessMsg('成功更新',$CurrentPageName);
		}
		else{
			WriteErrMsg(mysql_error());
		}
		break;
	default:
		$sql = "SELECT * FROM `{$db_pre}manage`";
		$sql.= ($Session['Admin']['Flag']<3)?" WHERE flag>={$Session['Admin']['Flag']}":" WHERE username='{$Session['Admin']['UserName']}'";
		$sql.= " ORDER BY flag ASC,id ASC"; 
		$manage_array = $db->getall($sql);
		$smarty->assign('manage_array',$manage_array);
} 

$smarty->display('admin/qc_manage_admin.html');
$runtime->stop_write();
?>