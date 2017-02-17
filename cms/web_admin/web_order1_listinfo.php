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

$status_txt=array(
    '1'=>'申请中',
    '2'=>'取消申请',
    '3'=>'审核通过未下载',
    '4'=>'审核通过已下载',
    '5'=>'',
    '6'=>'大麦城发货',//商品专有
    '7'=>'退回',
    '8'=>'申请后注销',
    '9'=>'已完成',//商品专有
);
switch ($action){			
//------------------编辑一条信息--------------------------
	case 'edit':
		if(!is_numeric($id))  WriteErrMsg('参数错误'); 
		$sql = "SELECT * FROM `".$db_pre."order` WHERE id='".$id."'";
		if($db->num_rows($sql)==0)	WriteErrMsg('操作错误'); 
		$rows = $db->getone($sql);
		$rows['status_txt']=$status_txt[$rows['status']];
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
						
				
//------------------下载交换信息--------------------------
	case 'down':
	    $sql = "SELECT * FROM `" . $db_pre . "order` WHERE 1";
	    $sql.=($number!='')?" and `number`='".$number."'":'';
	    $sql.=($type!='')?" and `type`='".$type."'":'';
	    $sql.=($status!='')?" and `status`='".$status."'":'';
	    $sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
		$sql.=" order by add_time desc";
	    $list=$db->getall($sql);
	    if(empty($list)){
	        error_log_admin('积分申请统计管理的下载',2,'管理员'.$Session['Admin']['UserName'].'进行积分申请统计管理的下载','当前结果为空');
	        echo "<script> alert('当前结果为空');window.history.back();</script>";
	        exit();
	    }
	    //引入PHPExcel类库
	    $_type=array(
	        '1'=>'兑换商品',
	        '2'=>'兑换现金(支付宝)',
	        '3'=>'兑换现金(微信)'
	    );
	    $str='申请类型,用户ID,订单号,收件人,收件人电话,收件地址,商品信息,花费积分,订单状态,申请时间'."\n";
    	foreach( $list as $row ){
    	    $item=unserialize($row['item_list']);
    	    $text='';
    	    foreach ($item as $val){
    	        $text.='商品名称：'.$val['title'].'，数量：'.$val['num'].'，单价：'.$val['total'].'。';
    	    }
    	    $str.=$_type[$row['type']].','.$row['uid'].','.$row['number'].','.$row['name'].','.$row['mobile'].','.$row['address'].','.$text.','.$row['total'].','.$status_txt[$row['status']].','.$row['add_time']."\n";
    	    
    	}
    	if(file_exists('files/Integralexchange_list.csv')){
    	    @unlink('files/Integralexchange_list.csv');
    	}
    	$filename='files/Integralexchange_list.csv';
        $handle=fopen($filename,"w+");
        $str=fwrite($handle,$str);
        fclose($handle);
        if($str){
            error_log_admin('积分申统计请管理的下载',1,'管理员'.$Session['Admin']['UserName'].'进行积分申统计请管理的下载','积分申请信息写入成功');
        }else{
            error_log_admin('积分申统计请管理的下载',3,'管理员'.$Session['Admin']['UserName'].'进行积分申统计请管理的下载','积分申请信息写入失败');
        }
        header("Location:".$filename);
    	exit();
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
		$sql.=($start!='')?" and `add_time`>'".$start."'":'';
		$sql.=($end!='')?" and `add_time`<'".$end."'":'';
		switch ($order){ 
			default: 		$sql .= " ORDER BY add_time";
		}
		switch ($sort){
			case 'asc': 	$sql .= " ASC"; 	break;
			case 'desc': 	$sql .= " DESC"; 	break;
			default: 		$sql .= " DESC";
		} 
		$list_array = page($sql,$page,$web_config['Web_PageSize'],$url);
		
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
$smarty->display("admin/web_order1_listinfo.html");
$runtime->stop_write();
?>