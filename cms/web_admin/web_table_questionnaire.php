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
$web_url    = $_POST['web_url'];  
  
$smarty->assign("language", $language);
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
		$sql = "SELECT * FROM `".$db_pre."table_flash` WHERE id='".$id."'";
		if($db->num_rows($sql)==0)	WriteErrMsg('操作错误'); 
		$rows = $db->getone($sql);  
		/**********************处理json***********************/ 
		$json_arr_cn= json_decode($rows['json_arr_cn'],true);  
		$smarty->assign('json_arr_cn',$json_arr_cn); 
		//exit(var_dump($rows['json_arr_cn']));
		$json_arr_en= json_decode($rows['json_arr_en'],true);
		$smarty->assign('json_arr_en',$json_arr_en);
		$json_arr_jp= json_decode($rows['json_arr_jp'],true);
		$smarty->assign('json_arr_jp',$json_arr_jp);
		/**********************处理图片_json***********************/ 
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
		/******************** json图片数据处理 **************************************/
		$json_title_cn =$_POST['json_title_cn']; 
		$json_img_cn = $_POST['json_img_cn'];  
		$json_arr_cn='{' ;
		for($i=1;$i<=count($json_title_cn);$i++){
			if ($_FILES['json_img_cn']['tmp_name'][$i-1]!=''){ 
				$image_cn = upload_img($_FILES['json_img_cn']['tmp_name'][$i-1], $_FILES['json_img_cn']['name'][$i-1], "images");
			}else{
				$image_cn = '';
			}
			$json_arr_cn .= "\"$i\":{";
			$json_arr_cn .= "\"id\":\"".$i."\",";
			$json_arr_cn .= "\"title\":\"".$json_title_cn[$i-1]."\",";
			$json_arr_cn .= "\"img\":\"".$image_cn."\"";
			$json_arr_cn .= "}" ;
			if($i!=count($json_title_cn)){
				$json_arr_cn .= "," ;
			}
		}
		$json_arr_cn .= '}' ;   
		//
		$json_title_en =$_POST['json_title_en'];
		$json_img_en = $_POST['json_img_en'];
		$json_arr_en='{' ;
		for($i=1;$i<=count($json_title_en);$i++){
		    if ($_FILES['json_img_en']['tmp_name'][$i-1]!=''){
		        $image_en = upload_img($_FILES['json_img_en']['tmp_name'][$i-1], $_FILES['json_img_en']['name'][$i-1], "images");
		    }else{
		        $image_en = '';
		    }
		    $json_arr_en .= "\"$i\":{";
		    $json_arr_en .= "\"id\":\"".$i."\",";
		    $json_arr_en .= "\"title\":\"".$json_title_en[$i-1]."\",";
		    $json_arr_en .= "\"img\":\"".$image_en."\"";
		    $json_arr_en .= "}" ;
		    if($i!=count($json_title_en)){
		        $json_arr_en .= "," ;
		    }
		}
		$json_arr_en .= '}' ;
		//
		$json_title_jp =$_POST['json_title_jp']; 
		$json_img_jp = $_POST['json_img_jp'];  
		$json_arr_jp='{' ;
		for($i=1;$i<=count($json_title_jp);$i++){
			if ($_FILES['json_img_cn']['tmp_name'][$i-1]!=''){ 
				$image_jp = upload_img($_FILES['json_img_jp']['tmp_name'][$i-1], $_FILES['json_img_jp']['name'][$i-1], "images");
			}else{
				$image_jp = '';
			}
			$json_arr_jp .= "\"$i\":{";
			$json_arr_jp .= "\"id\":\"".$i."\",";
			$json_arr_jp .= "\"title\":\"".$json_title_jp[$i-1]."\",";
			$json_arr_jp .= "\"img\":\"".$image_jp."\"";
			$json_arr_jp .= "}" ;
			if($i!=count($json_title_jp)){
				$json_arr_jp .= "," ;
			}
		}
		$json_arr_jp .= '}' ;   
		/******************** json图片数据处理 **************************************/
		$sql = "INSERT INTO `".$db_pre."table_flash`(
		            `class_id`,
					`title`,
					`json_arr_cn`,
		            `json_arr_en`,
		            `json_arr_jp`,
					`add_time`
				)VALUES( 
		            '2',
					'".$title."',
					'".$json_arr_cn."',
					'".$json_arr_en."',
					'".$json_arr_jp."',
					'".$add_time."'
				)";
		//exit(var_dump($sql));
		if ($db->query($sql)){
			WriteSuccessMsg('添加成功', $CurrentPageName.'?language='.$language);
		}else{
			WriteErrMsg(mysql_error());
		}
		break;
//------------------插入一条信息--------------------------

		
//------------------更新一条信息--------------------------
	case 'update': 
		if($title=='') WriteErrMsg('名称不能为空');  
		$sql = "SELECT * FROM `".$db_pre."table_flash` WHERE id='".$id."' LIMIT 0,1";
		if($db->num_rows($sql)>0){
			$rows = $db->getone($sql);
			/****** json图片数据处理 **********/
			$json_array_cn = json_decode($rows['json_arr_cn'],true);
			$json_title_cn = $_POST['json_title_cn']; 
			$json_img_cn = $_POST['json_img_cn'];  
			$json_img_url_cn = $_POST['json_img_url_cn'];  
			$json_arr_cn='{' ;
			for($i=1;$i<=count($json_title_cn);$i++){
				if ($_FILES['json_img_cn']['tmp_name'][$i-1]!=''){
					@unlink('../'.$json_array_cn[$i]['img']);
					$image_cn = upload_img($_FILES['json_img_cn']['tmp_name'][$i-1], $_FILES['json_img_cn']['name'][$i-1], "images");
				}else{
					$image_cn = $json_img_url_cn[$i-1];
				}
				$json_arr_cn .= "\"$i\":{";
				$json_arr_cn .= "\"id\":\"".$i."\",";
				$json_arr_cn .= "\"title\":\"".$json_title_cn[$i-1]."\",";
				$json_arr_cn .= "\"img\":\"".$image_cn."\"";
				$json_arr_cn .= "}" ;
				if($i!=count($json_title_cn)){
					$json_arr_cn .= "," ;
				}
			}
			$json_arr_cn .= '}' ;  
			//
			$json_array_en = json_decode($rows['json_arr_en'],true);
			$json_title_en = $_POST['json_title_en'];
			$json_img_en = $_POST['json_img_en'];
			$json_img_url_en = $_POST['json_img_url_en'];
			$json_arr_en='{' ;
			for($i=1;$i<=count($json_title_en);$i++){
			    if ($_FILES['json_img_en']['tmp_name'][$i-1]!=''){
			        @unlink('../'.$json_array_en[$i]['img']);
			        $image_en = upload_img($_FILES['json_img_en']['tmp_name'][$i-1], $_FILES['json_img_en']['name'][$i-1], "images");
			    }else{
			        $image_en = $json_img_url_en[$i-1];
			    }
			    $json_arr_en .= "\"$i\":{";
			    $json_arr_en .= "\"id\":\"".$i."\",";
			    $json_arr_en .= "\"title\":\"".$json_title_en[$i-1]."\",";
			    $json_arr_en .= "\"img\":\"".$image_en."\"";
			    $json_arr_en .= "}" ;
			    if($i!=count($json_title_en)){
			        $json_arr_en .= "," ;
			    }
			}
			$json_arr_en .= '}' ;
			//
			$json_array_jp = json_decode($rows['json_arr_jp'],true);
			$json_title_jp = $_POST['json_title_jp'];
			$json_img_jp = $_POST['json_img_jp'];
			$json_img_url_jp = $_POST['json_img_url_jp'];
			$json_arr_jp='{' ;
			for($i=1;$i<=count($json_title_jp);$i++){
			    if ($_FILES['json_img_jp']['tmp_name'][$i-1]!=''){
			        @unlink('../'.$json_array_jp[$i]['img']);
			        $image_jp = upload_img($_FILES['json_img_jp']['tmp_name'][$i-1], $_FILES['json_img_jp']['name'][$i-1], "images");
			    }else{
			        $image_jp = $json_img_url_jp[$i-1];
			    }
			    $json_arr_jp .= "\"$i\":{";
			    $json_arr_jp .= "\"id\":\"".$i."\",";
			    $json_arr_jp .= "\"title\":\"".$json_title_jp[$i-1]."\",";
			    $json_arr_jp .= "\"img\":\"".$image_jp."\"";
			    $json_arr_jp .= "}" ;
			    if($i!=count($json_title_jp)){
			        $json_arr_jp .= "," ;
			    }
			}
			$json_arr_jp .= '}' ;
			/****** json图片数据处理 **********/ 
		}  
		$sql = "UPDATE `".$db_pre."table_flash` SET  
					`title`='".$title."',
					`json_arr_cn`='".$json_arr_cn."',
					`json_arr_en`='".$json_arr_en."',
					`json_arr_jp`='".$json_arr_jp."'
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
		$sql = "SELECT * FROM `".$db_pre."table_flash` n WHERE 1 and class_id =2 ";
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
$smarty->display("admin/web_table_questionnaire.html");
$runtime->stop_write();
?>