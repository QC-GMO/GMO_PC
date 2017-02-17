<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
Authentication_Admin();

$action   = daddslashes($_POST['action']);

switch ($action){
	case 'pic_del':
		$id       = daddslashes($_POST['id']);    //图片信息id
		$name     = daddslashes($_POST['name']);	//表名字
		$field    = daddslashes($_POST['field']);	//字段名字
		$base_id = ($_POST['type']=='true')?'class_id':'id';	//是否是导航类图片
		$sql = "SELECT ".$field." FROM `".$db_pre.$name."` WHERE ".$base_id."='".$id."'";
		if($db->num_rows($sql)>0) {
			$rows = $db->getone($sql);
			$sql = "UPDATE `".$db_pre.$name."` SET ".$field."='' WHERE ".$base_id."='".$id."'";
			if ($db->query($sql)){
				@unlink("../".$rows[0]);
				$json_arr['status']  = 'success';
				$json_arr['message'] = '图片删除成功';
			} else {
				$json_arr['status']  = 'failure';
				$json_arr['message']  = mysql_error();
			}
		}else {
			$json_arr['status']  = 'failure';
			$json_arr['message'] = '参数错误';
		}
		break; 
	case 'mini_pic_del':
		$id       = daddslashes($_POST['id']);    //图片信息id
		$name     = daddslashes($_POST['name']);	//表名字 
		$base_id = ($_POST['type']=='true')?'class_id':'id';	//是否是导航类图片
		$sql = "SELECT `img_url`,`mini_img_url` FROM `".$db_pre.$name."` WHERE ".$base_id."='".$id."'";
		if ($db->num_rows($sql)>0) {
			$rows = $db->getone($sql);
			$sql = "UPDATE `".$db_pre.$name."` SET `img_url`='',`mini_img_url`='' WHERE ".$base_id."='".$id."'";
			if ($db->query($sql)){
				@unlink("../".$rows[0]);
				@unlink("../".$rows[1]);
				$json_arr['status']  = 'success';
				$json_arr['message'] = '图片删除成功';
			} else {
				$json_arr['status']  = 'failure';
				$json_arr['message']  = mysql_error();
			}
		} else {
			$json_arr['status']  = 'failure';
			$json_arr['message'] = '参数错误';
		}
		break; 
	/*20121108 异步显示栏目的排序*/
	case 'order_id':   
		$class_id = $_POST['class_id'];   //分类id
		$table = $_POST['table'];		//表名字
		$language = $_POST['language'];  //语言版本
		$now_order_id = $_POST['now_order_id']; //编辑的时候，现在的排序id
		$sql = "SELECT `id` FROM `".$db_pre.$table."` WHERE `class_id`='".$class_id."' AND `language`='".$language."'";
		$count = $db->num_rows($sql);   
		$new_count = ($now_order_id!='')?$count:($count+1); //针对‘编辑’还是‘添加’id总数不同的变化
		for($i=0;$i<$new_count;$i++){
			if($now_order_id==($i+1)) $result .=  "<option value='".($i+1)."' selected>".($i+1)."</option>" ;
			else $result .=  "<option value='".($i+1)."'>".($i+1)."</option>" ;
		}
		echo $result ;
		break;	 
	/*删除json图片*/
	case 'json_img_del':   
		$img_id = $_POST['img_id'];   
		$id = $_POST['id'];   
		$table = $_POST['table'];   
		$field = $_POST['json_string'];   
		$sql = "SELECT `".$field."` FROM `".$db_pre.$table."` WHERE `id`='".$id."'";
		$result = $db->getone($sql);  
		$rows = json_decode($result[0],true);
		@unlink('../'.$rows[$img_id]['img']);  
		break;	
	default:
		$json_arr['status'] = 'error';
}
echo json_encode($json_arr);
?>