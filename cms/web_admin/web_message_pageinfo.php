<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__); 
Authentication_Admin();
Authentication_Purview(); 
$smarty->assign('CurrentPageName',$CurrentPageName);
$action         = $_REQUEST['action'];
$page           = $_REQUEST['page'];
$page           = ($page>0)?$page:1;
$order          = $_GET['order'];
$sort           = $_GET['sort'];
$operationclass = $_POST['operationclass']; 

$email=daddslashes($_REQUEST['email']);
$start=$_GET['start'];
$end=$_GET['end'];
$number=$_GET['number'];
$id       = $_REQUEST['id']; 
$reply_title    = $_POST['reply_title'];
$reply_content  = $_POST['reply_content'];
 
switch ($action){	
		
//------------------查看一条信息--------------------------
	case 'edit':
		if(!is_numeric($id))  WriteErrMsg('参数错误'); 
		$sql = "SELECT * FROM `".$db_pre."message` WHERE id='".$id."'";
		if($db->num_rows($sql)==0)	WriteErrMsg('操作错误'); 
		$rows = $db->getone($sql);  
        //根据邮箱查询当前用户历史的咨询
		$sql = "SELECT * FROM `".$db_pre."message` WHERE email='".$rows['email']."' and id not in (".$id.")";
		$list = $db->getall($sql);
		$smarty->assign(
			array(
			    "list_array"=>$list,
				"rows"=>$rows
			)
		);
		break; 
//------------------查看一条信息--------------------------
		
//------------------更新一条信息--------------------------
	case 'update': 
	    if (!is_numeric($id)){
	        error_log_admin('留言回复',2,'管理员'.$Session['Admin']['UserName'].'进行留言回复','参数错误');
	        WriteErrMsg('参数错误');
	    }
		if($reply_title==''){
		    error_log_admin('留言回复',2,'管理员'.$Session['Admin']['UserName'].'进行留言回复','回复标题不能为空');
		    WriteErrMsg('回复标题不能为空');
		} 
		$sql = "SELECT `reply_title`,`email` FROM `".$db_pre."message` WHERE id='".$id."'";
		$rows = $db->getone($sql);
		if(!empty($rows['reply_title'])){
		    error_log_admin('留言回复',2,'管理员'.$Session['Admin']['UserName'].'进行留言回复','已回复过此条咨询');
		    WriteErrMsg('已回复过此条咨询');
		}
		$sql = "UPDATE `".$db_pre."message` SET  
					`reply_title`='".$reply_title."',
					`reply_content`='".$reply_content."',
					`replier`='".$Session['Admin']['UserName']."'
				WHERE id='".$id."'"; 
		if ($db->query($sql)){//插入数据库中后发送邮件
		    if(send_mail('smtp',$rows['email'],$reply_title,$reply_content)=='success'){
		        error_log_admin('留言回复',1,'管理员'.$Session['Admin']['UserName'].'进行留言回复','回复成功');
		    }else{
		        error_log_admin('留言回复',2,'管理员'.$Session['Admin']['UserName'].'进行留言回复','邮件发送失败');
		    }
			WriteSuccessMsg('更新成功', $CurrentPageName);
		}
		else{
		    error_log_admin('留言回复',3,'管理员'.$Session['Admin']['UserName'].'进行留言回复',mysql_error());
			WriteErrMsg(mysql_error());
		}
		break;
//------------------更新一条信息--------------------------
	//------------------下载交换信息--------------------------
	case 'down':
	    $sql = "SELECT * FROM `".$db_pre."message` n WHERE 1";
		$sql.=($id!='')?" and `id`='".$id."'":'';
		$sql.=($number!='')?" and `number`='".$number."'":'';
		$sql.=($email!='')?" and `email` LIKE '%".$email."%'":'';
		$sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
		$sql.=" order by add_time desc";
	    $list=$db->getall($sql);
	    if(empty($list)){
	        echo "<script> alert('当前结果为空');window.history.back();</script>";
	        exit();
	    }
	    $_type=array(
	        '1'=>'兑换商品',
	        '2'=>'兑换现金(支付宝)',
	        '3'=>'兑换现金(微信)'
	    );
	    $str='咨询ID,咨询类型,用户名,Email,使用系统,使用浏览器,问卷标题,问卷号,咨询内容,咨询时间,回复状态'."\n";
	    foreach( $list as $row ){
	        $str.=$row['id'].','.(($row['type']==1)?'普通咨询':'问卷咨询').','.$row['name'].','.$row['email'].','.$row['sys'].','.$row['browser'].','.$row['subject'].','.$row['number'].',';
	        $str.=$row['message'].','.$row['add_time'].','.(($row['reply_title']!='')?'已回复':'未回复')."\n";
	    }
	    if(file_exists('files/message_list.csv')){
	        @unlink('files/message_list.csv');
	    }
	    $filename='files/message_list.csv';
	    $handle=fopen($filename,"w+");
	    $str=fwrite($handle,$str);
	    fclose($handle);
	    header("Location:".$filename);
	    exit();
	    break;
	    
	default:
		$url = $CurrentPageName;
		$url .= ($email == '') ? '' : is_query_string($url) . 'email=' .$email;
		$url.=($start=='')?"":is_query_string($url).'start='.$start;
		$url.=($end=='')?"":is_query_string($url).'end='.$end;
		$url.= is_query_string($url);
		$sql = "SELECT * FROM `".$db_pre."message` n WHERE 1";
		$sql.=($id!='')?" and `id`='".$id."'":'';
		$sql.=($number!='')?" and `number`='".$number."'":'';
		$sql.=($email!='')?" and `email` LIKE '%".$email."%'":'';
		$sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
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
$smarty->display("admin/web_message_pageinfo.html");
$runtime->stop_write();
?>