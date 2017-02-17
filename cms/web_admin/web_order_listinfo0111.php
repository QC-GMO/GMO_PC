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

$id       = $_REQUEST['id']; 
$number          = $_GET['number'];
$type          = $_GET['type'];
$status          = $_REQUEST['status'];
$start=$_GET['start'];
$end=$_GET['end'];
$is_merge=$_GET['is_merge'];

switch ($action){			
//------------------编辑一条信息--------------------------
	case 'edit':
		if(!is_numeric($id))  WriteErrMsg('参数错误'); 
		$sql = "SELECT * FROM `".$db_pre."order` WHERE id='".$id."'";
		if($db->num_rows($sql)==0)	WriteErrMsg('操作错误'); 
		$rows = $db->getone($sql);
		
		//$user=$db->getone("select username from db_member where id='".$rows['uid']."'");
		$item_list=unserialize($rows['item_list']);
		$smarty->assign(
			array(
			    'item_list'=>$item_list,
				"rows"=>$rows
			)
		);
		break; 
//------------------编辑一条信息--------------------------
				
//------------------更新一条信息--------------------------
	case 'update': 
		if(!is_numeric($id))	WriteErrMsg('参数错误'); 
		if(!is_numeric($status))	WriteErrMsg('操作错误');
		$sql = "UPDATE `".$db_pre."order` SET `status`='".$status."' WHERE id='".$id."'"; 
		if ($db->query($sql)){
			WriteSuccessMsg('更新成功', $CurrentPageName);
		}
		else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------更新一条信息--------------------------
		
//------------------删除一条信息--------------------------
	case 'delete':
		if(!is_numeric($id))	WriteErrMsg('参数错误'); 
		//$sql = "UPDATE `".$db_pre."order` SET `is_show`=0 WHERE id='".$id."'";
		$sql = "DELETE FROM `" . $db_pre . "order` WHERE id='" . $id . "'";
		if ($db->query($sql)){
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------删除一条信息--------------------------
				
	case 'operation':
		if(!is_array($id)) WriteErrMsg('参数错误'); 
		$id_list = implode(",",$id); 
		switch ($operationclass){
			/****** 批量操作 ********/ 
		    //取消申请。退积分
			case 'batch_status_2': 
			    //检查订单中是否存在申请中之外的状态，有则退回。
			    foreach($id as $v){
			        $order = $db->getone("SELECT type,number,status FROM `".$db_pre."order` WHERE id='".$v."'");
			        if($order['status']!=1){WriteErrMsg('选择列表中存在申请中以外的状态订单。');}
			        if($order['type']==1){//类型是商品的订单拼订单号
			            $number_list[]=$order['number'];
			        }
			    }
			    //请求大麦城，同步订单状态，大麦城取消为2
			    if(!empty($number_list)){//有商品订单时
			        $number_list = implode(",",$number_list);
			        $url="http://www.test.damaicheng.com/test/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=2&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=2'));
			        if(strlen($url)>2000){WriteErrMsg('所选商品订单过多');}
			        $result=httpGet($url);
			        $result=json_decode($result,true);
			        if($result['status']==0){
			            WriteErrMsg($result['message']);
			        }
			    }
			    $sql  = "UPDATE `".$db_pre."order` SET status=2 WHERE id IN (".$id_list.")";
    			if($db->query($sql)){//状态改变成功，返还积分
    			    foreach($id as $v){//循环改变积分
    			        $order = $db->getone("SELECT uid,total FROM `".$db_pre."order` WHERE id='".$v."'");
    			        $db->query("UPDATE `".$db_pre."member` SET `integral`=`integral`+'".$order['total']."' WHERE id='".$order['uid']."'");
    			        $db->query("INSERT INTO `".$db_pre."integral`(`uid`,`type`,`val`,`source`,`add_time`)VALUES('".$order['uid']."','1','".$order['total']."','取消申请','".date('Y-m-d H:i:s')."')");
    			    }
    			    header('Location:'.$_SERVER['HTTP_REFERER']);
    			}else{
    			    WriteErrMsg(mysql_error());
    			}
			 break;
			 //审核通过
			case 'batch_status_3': 
			    //检查订单中是否存在申请中之外的状态，有则退回。
			    foreach($id as $v){
			        $order = $db->getone("SELECT type,number,status FROM `".$db_pre."order` WHERE id='".$v."'");
			        if($order['status']!=1){WriteErrMsg('选择列表中存在申请中以外的状态订单。');}
			        if($order['type']==1){//类型是商品的订单拼订单号
			            $number_list[]=$order['number'];
			        }
			    }
			    //请求大麦城，同步订单状态,大麦城通过为1
			    if(!empty($number_list)){//有商品订单时
			        $number_list = implode(",",$number_list);
			        $url="http://www.test.damaicheng.com/test/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=1&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=1'));
			        if(strlen($url)>2000){WriteErrMsg('所选商品订单过多');}
			        $result=httpGet($url);
			        $result=json_decode($result,true);
			        if($result['status']==0){
			            WriteErrMsg($result['message']);
			        }
			    }
			    $sql  = "UPDATE `".$db_pre."order` SET status=3 WHERE id IN (".$id_list.")"; 
    			if($db->query($sql)){
    			    header('Location:'.$_SERVER['HTTP_REFERER']);
    			}else{WriteErrMsg(mysql_error());}
			break;
			//审核不通过，积分不退
			case 'batch_status_4': 
			    //检查订单中是否存在申请中之外的状态，有则退回。
			    foreach($id as $v){
			        $order = $db->getone("SELECT type,number,status FROM `".$db_pre."order` WHERE id='".$v."'");
			        if($order['status']!=1){WriteErrMsg('选择列表中存在申请中以外的状态订单。');}
			        if($order['type']==1){//类型是商品的订单拼订单号
			            $number_list[]=$order['number'];
			        }
			    }
			    //请求大麦城，同步订单状态,大麦城不通过为2
			    if(!empty($number_list)){//有商品订单时
			        $number_list = implode(",",$number_list);
			        $url="http://www.test.damaicheng.com/test/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=2&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=2'));
			        if(strlen($url)>2000){WriteErrMsg('所选商品订单过多');}
			        $result=httpGet($url);
			        $result=json_decode($result,true);
			        if($result['status']==0){
			            WriteErrMsg($result['message']);
			        }
			    }
			    $sql  = "UPDATE `".$db_pre."order` SET status=4 WHERE id IN (".$id_list.")"; 
    			if($db->query($sql)){
    			    header('Location:'.$_SERVER['HTTP_REFERER']);
    			}else{WriteErrMsg(mysql_error());}
			break;
			//退回，返还积分
			case 'batch_status_6': 
			    //检查订单中是否存在申请中之外的状态，有则退回。
			    foreach($id as $v){
			        $order = $db->getone("SELECT type,number,status FROM `".$db_pre."order` WHERE id='".$v."'");
			        if($order['status']!=1 && $order['status']!=3 && $order['status']!=5){WriteErrMsg('选择列表中存在申请中和审核通过以外的状态订单。');}
			        if($order['type']==1 && $order['status']==1){//类型是商品并且状态未改变过的的订单拼订单号，，大麦城只允许订单修改订单信息一次。
			            $number_list[]=$order['number'];
			        }
			    }
			    //请求大麦城，同步订单状态,大麦城退回为4，，大麦城只允许订单修改订单信息一次。
			    if(!empty($number_list)){//有商品订单时
			        $number_list = implode(",",$number_list);
			        $url="http://www.test.damaicheng.com/test/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=4&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=4'));
			        if(strlen($url)>2000){WriteErrMsg('所选商品订单过多');}
			        $result=httpGet($url);
			        $result=json_decode($result,true);
			        if($result['status']==0){
			            WriteErrMsg($result['message']);
			        }
			    }
			    $sql  = "UPDATE `".$db_pre."order` SET status=6 WHERE id IN (".$id_list.")"; 
    			if($db->query($sql)){//状态改变成功，返还积分
    			    foreach($id as $v){//循环改变积分
    			        $order = $db->getone("SELECT uid,total FROM `".$db_pre."order` WHERE id='".$v."'");
    			        $db->query("UPDATE `".$db_pre."member` SET `integral`=`integral`+'".$order['total']."' WHERE id='".$order['uid']."'");
    			        $db->query("INSERT INTO `".$db_pre."integral`(`uid`,`type`,`val`,`source`,`add_time`)VALUES('".$order['uid']."','1','".$order['total']."','申请退回','".date('Y-m-d H:i:s')."')");
    			    }
    			    header('Location:'.$_SERVER['HTTP_REFERER']);
    			}else{
    			    WriteErrMsg(mysql_error());
    			}
			break;
			default: WriteErrMsg('请选择批量操作选项');
			/****** 批量操作 ********/ 
		}
		break; 
//------------------下载交换信息--------------------------
	case 'down':
	    $sql = "SELECT * FROM `" . $db_pre . "order` n WHERE `status`=3";
	    $sql.=($number!='')?" and `number`='".$number."'":'';
	    $sql.=($type!='')?" and `type`='".$type."'":'';
	    $sql.=($start!='' && $end!='')?" and `add_time` BETWEEN '".$start."' AND '".$end."'":'';
	    $list=$db->getall($sql);
	    if(empty($list)){
	        echo "<script> alert('当前结果为空');window.history.back();</script>";
	        exit();
	    }
	    exit(var_dump($is_merge));
	    break;
//------------------下载交换信息--------------------------
	default:
		$url = $CurrentPageName;
		$url .= ($number == '') ? '' : is_query_string($url) . 'number=' .$number;
		$url .= ($type == '') ? '' : is_query_string($url) . 'type=' .$type;
		$url .= ($status == '') ? '' : is_query_string($url) . 'status=' .$status;
		$url.=($start=='')?"":is_query_string($url).'start='.$start;
		$url.=($end=='')?"":is_query_string($url).'end='.$end;
		$url.= is_query_string($url);
		$sql = "SELECT * FROM `".$db_pre."order` WHERE 1";
		$sql.=($number!='')?" and `number`='".$number."'":'';
		$sql.=($type!='')?" and `type`='".$type."'":'';
		$sql.=($status!='')?" and `status`='".$status."'":'';
		$sql.=($start!='' && $end!='')?" and `add_time` BETWEEN '".$start."' AND '".$end."'":'';
		switch ($order){ 
			default: 		$sql .= " ORDER BY add_time";
		}
		switch ($sort){
			case 'asc': 	$sql .= " ASC"; 	break;
			case 'desc': 	$sql .= " DESC"; 	break;
			default: 		$sql .= " DESC";
		} 
		$list_array = page($sql,$page,$web_config['Web_PageSize'],$url);
		$status_txt=array(
		    '1'=>'申请中',
		    '2'=>'取消申请',
		    '3'=>'审核通过',
		    '4'=>'审核未通过',
		    '5'=>'审核通过已下载',//现金专有
		    '6'=>'退回',
		    '7'=>'申请后注销'
		);
		if(!empty($list_array)){
		    foreach ($list_array as $key=>$val){
		        /* $re=$db->getone("select username from db_member where id='".$val['uid']."'");
		        $list_array[$key]['uname']=$re['username']; */
		        $list_array[$key]['status_txt']=$status_txt[$val['status']];
		    }
		}
		$smarty->assign(
			array(  
				"order_time"=>orderby('add_time','下单时间'),
				"list_array"=>$list_array, 
				"url"=>$url
			)
		);
}
$smarty->display("admin/web_order_listinfo.html");
$runtime->stop_write();
?>