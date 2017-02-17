<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__); 
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$data_backup_dir		= WEB_ROOT.'data_backup'; 
$db_size				= 0.000;
$action         		= daddslashes($_REQUEST['action']);
$table					= daddslashes($_REQUEST['table']);
$operationclass 		= daddslashes($_POST['operationclass']);
$backup_way    			= daddslashes($_REQUEST['backup_way']);
$backup_table			= daddslashes($_REQUEST['backup_table']);
$backup_volume     		= daddslashes($_REQUEST['backup_volume']);
$volume_size     		= daddslashes($_REQUEST['volume_size']);
$backup_location   		= daddslashes($_REQUEST['backup_location']);
$smarty->assign('data_backup_dir',$data_backup_dir);
//$web_config['Web_DB_Name']
switch($action){
	case 'backup':
		$sql = "show table status from `".$web_config['Web_DB_Name']."`";
		$list_array=$db->getall($sql);
		foreach($list_array as $key=>$list){
			$list_array[$key]['Data_length']=number_format($list_array[$key]['Data_length']/1048576,3);
			$db_size+=$list_array[$key]['Data_length'];
		}
		$smarty->assign('db_size',$db_size);
		$smarty->assign('db_Engine',$list_array[0]['Engine']);
		$smarty->assign('list_array',$list_array);
	break;
	case 'restore':
		$sql = "show table status from `".$web_config['Web_DB_Name']."`";
		$list_array=$db->getall($sql);
		foreach($list_array as $key=>$list){
			$list_array[$key]['Data_length']=number_format($list_array[$key]['Data_length']/1048576,3);
			$db_size+=$list_array[$key]['Data_length'];
		}
		$smarty->assign('db_size',$db_size);
		$smarty->assign('db_Engine',$list_array[0]['Engine']);
	break;
	case 'optimize':
		$sql="OPTIMIZE TABLE `".$table."`"; 
		if($db->query($sql)){
			WriteSuccessMsg('优化表'.$table.'成功',$CurrentPageName);
		}else{
			WriteErrMsg('优化表'.$table.'失败');
		}
	break;
	case 'repair':
		$sql="REPAIR TABLE `".$table."`";
		if($db->query($sql)){
			WriteSuccessMsg('修复表'.$table.'成功',$CurrentPageName);
		}else{
			WriteErrMsg('修复表'.$table.'失败');
		}
	break;
	case 'drop':
		$sql="drop table `".$table."`";
		if($db->query($sql)){
			WriteSuccessMsg('删除表'.$table.'成功',$CurrentPageName);
		}else{
			WriteErrMsg('删除表'.$table.'失败');
		}
	break;
	case 'submit_backup':
		if(!is_numeric($backup_way) || !in_array($backup_way,array('0','1'))){
			WriteErrMsg('请选择备份方式');
		}
		if($backup_way==1 && $backup_table==''){
			WriteErrMsg('请选择需要备份的表');
		}
		if($backup_volume=='1' && !is_numeric($volume_size)){
			WriteErrMsg('请填写备份的卷大小');
		}
		if(!is_numeric($backup_location) || !in_array($backup_location,array('0','1'))){
			WriteErrMsg('请选择备份存储位置');
		}
		if($backup_volume==1 && $backup_location==1){
			WriteErrMsg('只有选择备份到服务器，才能使用分卷备份功能');
		}
		if(!is_dir($data_backup_dir)){
			if(!mkdir($data_backup_dir,0777)){
				WriteErrMsg('服务器备份存储目录不存在，请联系管理员创建'.$data_backup_dir.'这样一个目录');
			}
		}
		if(!is_writeable($data_backup_dir)){
			WriteErrMsg('服务器备份存储目录不可写，请联系管理员修改'.$data_backup_dir.'目录权限');
		}
		$make_sql="";
		if($backup_way==0){  //整站备份
			if($backup_volume==1){  //分卷备份
				
				if($backup_location==0){  //备份到服务器
					
				}else if($backup_location==0){  //备份到本地
					
				}
			}else{    //不分卷备份
				$sql = "show table status from `".$web_config['Web_DB_Name']."`;\n";
				$list_array=$db->getall($sql);
				foreach($list_array as $list){
					$make_sql.=make_header($list['Name']);
					$sql = "select count(*) from information_schema.COLUMNS where TABLE_SCHEMA='".$web_config['Web_DB_Name']."' and table_name='".$list['Name']."'";
					if($db->num_rows($sql)>0){
						$result = $db->getone($sql);
						$fieldsNum = $result[0];
					} 
					$sql="select * from `".$list['Name']."`"; 
					if($db->num_rows($sql)>0){
						$rows=$db->getall($sql);
						foreach($rows as $row){
							$make_sql.=make_record($list['Name'],$fieldsNum,$row);
						}
					}
				}
				$filename=date("YmdHis",time())."_all.sql";
				if($backup_location==0){  //备份到服务器
					if(write_file($make_sql,$data_backup_dir.'/'.$filename)){
						WriteSuccessMsg('整站数据库备份成功',$CurrentPageName);
					}else{
						WriteErrMsg('整站数据库备份失败');
					}
				}else if($backup_location==1){  //备份到本地
					down_file($make_sql,$filename);
				}
			}
		}else if($backup_way==1){  //备份单张表
			if($backup_volume==1){  //分卷备份
				if($backup_location==0){  //备份到服务器
					
				}else if($backup_location==0){  //备份到本地
					
				}
			}else{    //不分卷备份
				if($backup_location==0){  //备份到服务器
					
				}else if($backup_location==0){  //备份到本地
					
				}
			}
		}
	break;
	case 'operation':
		if(!is_array($id)){
			WriteErrMsg('参数错误');
		}
		$table_str=implode(' , ',$table);
		switch($operationclass){
			case 'batch_optimize':
				foreach($table as $key=>$value){
					$sql="OPTIMIZE TABLE `".$value."`";
					$db->mysql_query_rst($sql);
					if(!$db->__get('result')){
						WriteErrMsg('优化表'.$value.'失败');
					}
				}
				WriteSuccessMsg('批量优化表'.$table_str.'成功',$CurrentPageName);
			break;
			case 'batch_repair':
				foreach($table as $key=>$value){
					$sql="REPAIR TABLE `".$value."`";
					$db->mysql_query_rst($sql);
					if(!$db->__get('result')){
						WriteErrMsg('修复表'.$value.'失败');
					}
				}
				WriteSuccessMsg('批量修复表'.$table_str.'成功',$CurrentPageName);
			break;
			default:
				WriteErrMsg('请选择批量操作选项');
			break;	
		}
	break;
	default:
		$sql = "show table status from `".$web_config['Web_DB_Name']."`"; 
		$list_array=$db->getall($sql);
		foreach($list_array as $key=>$list){
			$list_array[$key]['Data_length']=number_format($list_array[$key]['Data_length']/1048576,3);
			$db_size+=$list_array[$key]['Data_length'];
		}
		$smarty->assign('db_size',$db_size);
		$smarty->assign('db_Engine',$list_array[0]['Engine']);
		$smarty->assign('list_array',$list_array);
	break;
}
function down_file($content,$filename){
	ob_end_clean();
	header("Content-Encoding: none");
	header("Content-Type: ".(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	header("Content-Disposition: ".(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'inline; ' : 'attachment; ')."filename=".$filename);
	header("Content-Length: ".strlen($content));
	header("Pragma: no-cache");
	header("Expires: 0");
	$e=ob_get_contents();
	echo $content;
	ob_end_clean();
}
function make_header($table){
	global $db;
	$sql="DROP TABLE IF EXISTS `".$table."`;\n";
	$tmp=$db->getone("show create table `".$table."`");
	$tmp=preg_replace("/\n/","",$tmp['Create Table']);
	$sql.=$tmp.";\n";
	return $sql;
}
function make_record($table,$num_fields,$record){
	$comma="";
	$sql .= "INSERT INTO ".$table." VALUES(";
	for($i = 0; $i < $num_fields; $i++){
		$sql .= ($comma."'".mysql_escape_string($record[$i])."'");
		$comma = ',';
	}
	$sql .= ");\n";
	return $sql;
}
function write_file($content,$file){
	if(!$fp=fopen($file,"w+")){
		return false;
	}
	if(!fwrite($fp,$content)){
		return false;
	}
	if(!fclose($fp)){
		return false;
	}
	return true;
}
$smarty->display("admin/qc_manage_db.html");
$runtime->stop_write();
?>