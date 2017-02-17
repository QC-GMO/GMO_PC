<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];
$status = $_REQUEST['status'];
$username = daddslashes($_REQUEST['username']);
$mobile=daddslashes($_REQUEST['mobile']);
$email=daddslashes($_REQUEST['email']);
$start=$_REQUEST['start'];
$end=$_REQUEST['end'];

$page = $_REQUEST['page'];
$page = ($page > 0) ? $page : 1;
$order = $_GET['order'];
$sort = $_GET['sort'];
$operationclass = $_POST['operationclass'];
$new_class_id = $_POST['new_class_id'];

$id = $_REQUEST['id'];
$birthday = daddslashes($_POST['birthday']);
$province_id = daddslashes($_POST['province_id']);
$city_id = daddslashes($_POST['city_id']);
$province = daddslashes($_POST['province']);
$city = daddslashes($_POST['city']);
$sex = daddslashes($_POST['sex']);
$address = daddslashes($_POST['address']);

$add_time = $_POST['add_time'];

switch ($action) {
    case 'getcity':
        $city=$db->getall("select * from db_city where p_id=".$_GET['p_id']);
        exit(json_encode($city));
        break;
	case 'edit':
		if (!is_numeric($id)) {
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `" . $db_pre . "member` WHERE id='" . $id . "'";
		if ($db->num_rows($sql) == 0) {
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql);
		$province=$db->getall("select * from db_province where 1 order by id asc");
		$city=$db->getall("select * from db_city where p_id=".$rows['province_id']);
		$smarty->assign(
			array(
				"rows" => $rows,
			    "province"=>$province,
			    "city"=>$city
			)
		);
		break;
		//会员的积分历史列表页
	case 'integral':
	    if (!is_numeric($id)) {
	        WriteErrMsg('参数错误');
	    }
	    $url = $CurrentPageName;
	    $url .= ($action == '') ? '' : is_query_string($url) . 'action=' .$action;
	    $url .= ($id == '') ? '' : is_query_string($url) . 'id=' .$id;
	    $url .= is_query_string($url);
	    $sql = "SELECT * FROM `" . $db_pre . "integral` WHERE uid='" . $id . "' order by add_time desc";
	    $list_array = page($sql, $page, $web_config['Web_PageSize'], $url);
	    $smarty->assign(
	        array(
	            "list_array" => $list_array,
	        )
	    );
	    break;
	case 'insert':
	case 'update':
		if (!is_numeric($id)) {
		    error_log_admin('会员修改',2,'管理员'.$Session['Admin']['UserName'].'进行会员修改','参数错误');
			WriteErrMsg('参数错误');
		}
		if (empty($email)) {
		    error_log_admin('会员修改',2,'管理员'.$Session['Admin']['UserName'].'进行会员修改','邮箱不能为空');
		    WriteErrMsg('邮箱不能为空');
		}
		if ($db->num_rows("SELECT username FROM `" . $db_pre . "member` WHERE id NOT IN ('" . $id . "') AND email='" . $email . "'") > 0) {
		    error_log_admin('会员修改',2,'管理员'.$Session['Admin']['UserName'].'进行会员修改','邮箱已存在');
		    WriteErrMsg('邮箱已存在');
		}
	    $sql = "UPDATE `" . $db_pre . "member` SET
				`email`='" . $email . "',
				`birthday`='" .$birthday. "',
				`province_id`='" . $province_id . "',
				`city_id`='" . $city_id . "',
				`province`='" . $province . "',
				`city`='" . $city . "',
				`sex`='" . $sex . "',
			    `address`='" . $address . "',
				`status`='" . $status . "'
			    WHERE id='" . $id."'";
		if ($db->query($sql)) {
		    //后台更新成功后更新表中后台更新时间
		    $db->query("UPDATE `".$db_pre."member` SET `admin_up`='".date("Y-m-d H:i:s")."' WHERE id='".$id."'");
		    error_log_admin('会员修改',1,'管理员'.$Session['Admin']['UserName'].'进行会员修改','更新成功');
		    WriteSuccessMsg('更新成功', $CurrentPageName);
		} else {
		    error_log_admin('会员修改',3,'管理员'.$Session['Admin']['UserName'].'进行会员修改',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break;

	case 'delete':
		if (!is_numeric($id)) {
			WriteErrMsg('参数错误');
		}
		$sql = "DELETE FROM `" . $db_pre . "member` WHERE id='" . $id . "'";
		if ($db->query($sql)) {
			header('Location:' . $_SERVER['HTTP_REFERER']);
		} else {
			WriteErrMsg(mysql_error());
		}
		break;
	case 'operation':
		if (!is_array($id)) {
			WriteErrMsg('参数错误');
		}
		$id = implode(",", $id); //explode(",",$id);
		switch ($operationclass) {
			case 'batch_delete':
				$sql = "DELETE FROM `" . $db_pre . "member` WHERE id IN (" . $id . ")";
				break;
			case 'batch_show_1': $sql  = "UPDATE `".$db_pre."member` SET is_show='1' WHERE id IN (".$id.")"; break;
			case 'batch_show_0': $sql  = "UPDATE `".$db_pre."member` SET is_show='0' WHERE id IN (".$id.")"; break;
				break;
			default:
				WriteErrMsg('请选择批量操作选项');
		}
		if ($db->query($sql)) {
		    error_log_admin('会员删除',1,'管理员'.$Session['Admin']['UserName'].'进行会员删除','删除成功');
			header('Location:' . $_SERVER['HTTP_REFERER']);
		} else {
		    error_log_admin('会员删除',3,'管理员'.$Session['Admin']['UserName'].'进行会员删除',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break;
	case 'show':
		if (!is_numeric($id)) WriteErrMsg('参数错误');
		update_flag($db_pre . 'member', 'is_show', $id);
		header('Location:' . $_SERVER['HTTP_REFERER']);
		break;
		//重置密码
	case 'reset':
	    if (!is_numeric($id)) {
	        error_log_admin('会员重置密码',2,'管理员'.$Session['Admin']['UserName'].'进行重置密码','参数错误');
	        WriteErrMsg('参数错误');
	    }
	    $sql = "UPDATE `" . $db_pre . "member` SET `password`='" .md5('12345678'). "' WHERE id='" . $id."'";
	    if ($db->query($sql)) {
	        error_log_admin('会员重置密码',1,'管理员'.$Session['Admin']['UserName'].'进行重置密码','重置成功');
	        WriteSuccessMsg('重置成功', $_SERVER['HTTP_REFERER']);
	    } else {
	        error_log_admin('会员重置密码',3,'管理员'.$Session['Admin']['UserName'].'进行重置密码',mysql_error());
	        WriteErrMsg(mysql_error());
	    }
	    break;
	    //下载会员信息
	case 'down':
	    $sql = "SELECT * FROM `" . $db_pre . "member` n WHERE 1";
	    $sql.=($status!='')?" and `status`='".$status."'":'';
	    $sql.=($id!='')?" and `id`='".$id."'":'';
	    $sql.=($mobile!='')?" and `mobile` LIKE '%".$mobile."%'":'';
		$sql.=($email!='')?" and `email` LIKE '%".$email."%'":'';
		$sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
		$sql.=($username!='')?" and `username` LIKE '%".$username."%'":'';
		$sql.=" order by add_time desc";
	    $list=$db->getall($sql);
	    if(empty($list)){
	        echo "<script> alert('当前结果为空');window.history.back();</script>";
	        exit();
	    }
	    $str='用户ID,用户名,手机号码,email,性别(1男，2女),生日,省,市,详细地址,当前积分,会员来源(1自主推广，2外部推广，3会员推广),来源code,会员状态(1认证中，2普通会员，3自己注销，4强制注销),注销理由,最后登录时间,会员更新时间,后台更新时间,注册时间'."\n";
	    foreach( $list as $row ){
	        $str.=$row['id'].','.$row['username'].','.$row['mobile'].','.$row['email'].','.$row['sex'].','.$row['birthday'].','.$row['province'].','.$row['city'].','.$row['address'].',';
	        $str.=$row['integral'].','.$row['source_type'].','.$row['web_code'].','.$row['status'].','.$row['reason'].','.$row['lastlogin_time'].','.$row['own_up'].','.$row['admin_up'].','.$row['add_time']."\n";
	    }
	    if(file_exists('files/member.csv')){
	        @unlink('files/member.csv');
	    }
	    $filename='files/member.csv';
	    
	    $handle=fopen($filename,"w+");
	    
	    $str=fwrite($handle,$str);
	    
	    fclose($handle);
	    header("Location:".$filename);
    	/* $write = new PHPExcel_Writer_Excel2007($excel);
    	$filename='会员信息结果'.date('Ymd',time()).'.csv';
    	export_xlsx($filename);
    	$write->save('php://output'); */
    	exit();
	    break;
	default:
		$url = $CurrentPageName;
		$url .= ($status == '') ? '' : is_query_string($url) . 'status=' .$status;
		$url .= ($id == '') ? '' : is_query_string($url) . 'id=' .$id;
		$url .= ($username == '') ? '' : is_query_string($url) . 'username=' .urlencode($username);
		$url .= ($mobile == '') ? '' : is_query_string($url) . 'mobile=' .$mobile;
		$url .= ($email == '') ? '' : is_query_string($url) . 'email=' .$email;
		$url.=($start=='')?"":is_query_string($url).'start='.$start;
		$url.=($end=='')?"":is_query_string($url).'end='.$end;
		$url .= is_query_string($url);
		$sql = "SELECT * FROM `" . $db_pre . "member` n WHERE 1";
		$sql.=($status!='')?" and `status`='".$status."'":'';
		$sql.=($id!='')?" and `id`='".$id."'":'';
		$sql.=($mobile!='')?" and `mobile` LIKE '%".$mobile."%'":'';
		$sql.=($email!='')?" and `email` LIKE '%".$email."%'":'';
		$sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
		$sql.=($username!='')?" and `username` LIKE '%".$username."%'":'';
		$sql .= " ORDER BY add_time";
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
		//exit(var_dump($_REQUEST));
		$list_array = page($sql, $page, $web_config['Web_PageSize'], $url);
		//查询写入日志
		if(!empty($list_array)){
		    error_log_admin('会员查询',1,'管理员'.$Session['Admin']['UserName'].'进行会员查询','查询成功');
		}else{
		    error_log_admin('会员查询',2,'管理员'.$Session['Admin']['UserName'].'进行会员查询','查询结果为空');
		}
		$smarty->assign(
			array(
				"order_time" => orderby('add_time', '注册时间'),
				"list_array" => $list_array,
			)
		);
}
$smarty->display("admin/web_member_listinfo.html");
$runtime->stop_write();
?>