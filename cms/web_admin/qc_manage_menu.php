<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action = $_REQUEST['action'];
$class_id = $_REQUEST['class_id'];
$class_name = $_REQUEST['class_name'];
$class_parent_id = $_REQUEST['class_parent_id'];
$class_url = $_REQUEST['class_url'];
$is_show = $_REQUEST['is_show'];
$menu_flag = $_REQUEST['menu_flag']; 
switch ($action){
	case 'add':
		$smarty->assign("menu_array",menu_array());
		break;
	case 'edit':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('操作错误');
		}
		$rows = $db->getone($sql);
		$smarty->assign(
			array(
			   "rows"=>$rows,
			   "menu_array"=>menu_array(),
			)
		);
		break; 
	case 'insert':
		if ($class_name==''){
			WriteErrMsg('栏目名称不能为空');
		}
		if ($class_parent_id==''){
			WriteErrMsg('栏目所属不能为空');
		}
		$sql = "SELECT MAX(class_id),MAX(class_root_id) FROM `{$db_pre}menu`";
		//echo 'sql 01.'.$sql.'<br>';
		$maxid = $db->getone($sql);
		$class_id = intval($maxid[0])+1;
		$maxrootid = intval($maxid[1]);
		$class_root_id = $maxrootid + 1;
		
		if ($class_parent_id > 0){
			$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_parent_id}";
			//echo 'sql 02.'.$sql.'<br>';
			$result = $db->getone($sql);
			$class_root_id     = $result['class_root_id'];
			$parentdepth       = $result['class_depth'];
			$class_child       = $result['class_child'];
			$prevorderid       = $result['class_order_id'];
			$class_parent_path = $result['class_parent_path'].','.$class_parent_id;
			if ($class_child > 0){
				$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_id={$class_parent_id}";
				//echo 'sql 03.'.$sql.'<br>';
				$prevorderid = $db->getone($sql);
				$prevorderid = intval($prevorderid[0]);
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_id={$class_parent_id} AND class_order_id={$prevorderid}";
				//echo 'sql 04.'.$sql.'<br>';
				$class_prev_id = $db->getone($sql);
				$class_prev_id = $class_prev_id[0];
				$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_path LIKE '$class_parent_path,%'";
				//echo 'sql 05.'.$sql.'<br>';
				$result = $db->getone($sql);
				if (intval($result[0]) > $prevorderid){
					$prevorderid = intval($result[0]);
				}
			}
			else{
				$class_prev_id = 0;
			}
		}
		else{
			if ($maxrootid > 0){
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_root_id={$maxrootid} AND class_depth=0";
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
		}
		else{
			$class_depth = 0;
		}
		$sql = "INSERT INTO `{$db_pre}menu`(`class_id`,`class_name`,`class_root_id`,`class_child`,`class_parent_id`,`class_depth`,`class_parent_path`,`class_order_id`,`class_prev_id`,`class_next_id`,`class_url`,`is_show`,`menu_flag`)VALUES('$class_id','$class_name','$class_root_id',0,'$class_parent_id','$class_depth','$class_parent_path','$prevorderid','$class_prev_id',0,'$class_url','$is_show','$menu_flag')";
		//echo 'sql 07.'.$sql.'<br>';
		if ($db->query($sql)){
			if ($class_prev_id > 0){
				$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_id={$class_prev_id}";
				//echo 'sql 08.'.$sql.'<br>';
				$db->query($sql);
			}
			if ($class_parent_id > 0){
				$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child+1 WHERE class_id={$class_parent_id}";
				//echo 'sql 09.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_order_id=class_order_id+1 WHERE class_root_id={$class_root_id} AND class_order_id>{$prevorderid}";
				//echo 'sql 10.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$prevorderid}+1 WHERE class_id={$class_id}";
				//echo 'sql 11.'.$sql.'<br>';
				$db->query($sql);
			}
		}
		else{
		   die(mysql_error());
		}
		header("Location:{$CurrentPageName}");
		break;
	case 'update':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		if ($class_name==''){
			WriteErrMsg('栏目名称不能为空');
		}
		if ($class_parent_id==''){
			WriteErrMsg('栏目所属不能为空');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql) == 0){
			WriteErrMsg('找不到指定的栏目');
		}
		$result = $db->getone($sql);
		if ($result['class_parent_id'] != $class_parent_id){ //判断类别是否更改
			if ($result['class_id'] == $class_parent_id){    //判断类别是否为自己
				WriteErrMsg('所属栏目不能为自己');
			}
			if ($result['class_parent_id'] == 0){ //如果原类别为根类别时
				if ($class_parent_id > 0){ //如果指定类别不为根类别时
					$sql = "SELECT class_root_id FROM `{$db_pre}menu` WHERE class_id={$class_parent_id}";
					//echo 'sql 02.'.$sql.'<br>';
					if ($db->num_rows($sql) == 0){ 
						WriteErrMsg('不能指定外部栏目为所属栏目');
					}
					else{
						$dbroot = $db->getone($sql);
						if ($result['class_root_id'] == $dbroot['class_root_id']){ //判断所指定的类别是否其下属类别
							WriteErrMsg('不能指定该栏目的下属栏目作为所属栏目');
						}
					}
				}
			}
			else{ //如果原类别不为根类别时
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '{$result[class_parent_path]},{$result[class_id]}%' AND class_id={$class_parent_id}";
				//echo 'sql 03.'.$sql.'<br>';
				if ($db->num_rows($sql) > 0){
					WriteErrMsg('不能指定该栏目的下属栏目作为所属栏目');
				}	
			}	
		}
		if ($result['class_parent_id'] == 0){
			$cparentid = $result['class_id'];
			$iparentid = 0;
		}
		else{
			$cparentid = $result['class_parent_id'];
			$iparentid = $result['class_parent_id'];
		}
		$class_depth       = $result['class_depth'];
		$class_child       = $result['class_child'];
		$class_root_id     = $result['class_root_id'];
		$class_parent_path = $result['class_parent_path'];
		$class_prev_id     = $result['class_prev_id'];
		$class_next_id     = $result['class_next_id'];
		$sql = "SELECT MAX(class_root_id) FROM `{$db_pre}menu`";
		//echo 'sql 04.'.$sql.'<br>';
		$maxid = $db->getone($sql);
		$maxrootid = intval($maxid[0]);
		if ($iparentid!=$class_parent_id && !($iparentid==0 && $cparentid==0)){
			if ($class_prev_id > 0){
				$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
				//echo 'sql 05.'.$sql.'<br>';
				$db->query($sql);
			}
			if ($class_next_id > 0){
				$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
				//echo 'sql 06.'.$sql.'<br>';
				$db->query($sql);
			}
			if ($iparentid > 0 && $class_parent_id == 0){    //如果原来不是根栏目，现改为根栏目
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_root_id={$maxrootid} AND class_depth=0";
				//echo 'sql 07.'.$sql.'<br>';
				$dbclass = $db->getone($sql); //获取排在最后的跟栏目ID
				$class_prev_id = $dbclass[0];
				$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_root_id={$maxrootid} AND class_depth=0";
				//echo 'sql 08.'.$sql.'<br>';
				$db->query($sql);
				$maxrootid = $maxrootid + 1;
				$sql = "UPDATE `{$db_pre}menu` SET class_depth=0,class_order_id=0,class_root_id={$maxrootid},class_parent_id=0,class_parent_path=0,class_prev_id={$class_prev_id},class_next_id=0 WHERE class_id={$class_id}";
				//echo 'sql 09.'.$sql.'<br>';
				$db->query($sql);
				if ($class_child > 0){
					$class_parent_path = $class_parent_path.',';
					$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}{$class_id}%' ORDER BY class_root_id,class_order_id";
					//echo 'sql 10.'.$sql.'<br>';
					$result = $db->getall($sql);
					if (is_array($result)){
						$i = 0;
						foreach ($result as $key=>$value){
							$i++;
							$mparentpath = str_replace($class_parent_path,'0,',$value['class_parent_path']);
							$sql = "UPDATE `{$db_pre}menu` SET class_depth=class_depth-{$class_depth},class_root_id={$maxrootid},class_order_id={$i},class_parent_path='{$mparentpath}' WHERE class_id={$value['class_id']}";
							//echo 'sql 11.'.$sql.'<br>';
							$db->query($sql);	
						}
					}
				}
				$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child-1 WHERE class_id={$iparentid}";
				//echo 'sql 12.'.$sql.'<br>';
				$db->query($sql);	
			}
			elseif ($iparentid > 0 && $class_parent_id > 0){ //如果是将一个分栏目移动到其他分栏目下
				$class_parent_path = $class_parent_path.',';
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}{$class_id}%'";
				//echo 'sql 13.'.$sql.'<br>';
				$classcount = $db->num_rows($sql); //获得当前类别所有子栏目数
				$classcount = ($classcount == 0)?1:$classcount;
				$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_parent_id}";
				//echo 'sql 14.'.$sql.'<br>';
				$result = $db->getone($sql); //获取指定栏目的父栏目信息
				if ($result['class_child'] > 0){ //当指定栏目的父栏目下包含其他栏目时
					$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_id={$result['class_id']}";
					//echo 'sql 15.'.$sql.'<br>';
					$prevorderid = $db->getone($sql); //得到与本栏目同级的最后一个栏目的OrderID
					$prevorderid = intval($prevorderid[0]);
					$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_id={$result['class_id']} AND class_order_id={$prevorderid}";
					//echo 'sql 16.'.$sql.'<br>';
					$class_prev_id = $db->getone($sql); //得到与本栏目同级的最后一个栏目的ID
					$class_prev_id = $class_prev_id[0];
					$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_parent_id={$result['class_id']} AND class_order_id={$prevorderid}";
					//echo 'sql 17.'.$sql.'<br>';
					$db->query($sql); //更新与本栏目同级的最后一个栏目的class_next_id为当前ID
					$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_path LIKE '{$result['class_parent_path']},{$result['class_id']},%'";
					//echo 'sql 18.'.$sql.'<br>';
					$maxorderid = $db->getone($sql); //得到与本栏目同级栏目包括子栏目最后一个栏目的OrderID
					$maxorderid = intval($maxorderid[0]);
					if ($maxorderid > $prevorderid){
						$prevorderid = $maxorderid;
					}
				}
				else{
					$class_prev_id = 0;
					$prevorderid = $result['class_order_id'];
				}
				$sql = "UPDATE `{$db_pre}menu` SET class_order_id=class_order_id+{$classcount}+1 WHERE class_root_id={$result['class_root_id']} AND class_order_id>{$prevorderid}";
				//echo 'sql 19.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_depth={$result['class_depth']}+1,class_order_id={$prevorderid}+1,class_root_id={$result['class_root_id']},class_parent_id={$class_parent_id},class_parent_path='{$result['class_parent_path']},{$result['class_id']}',class_prev_id={$class_prev_id},class_next_id=0 WHERE class_id={$class_id}";
				//echo 'sql 20.'.$sql.'<br>';
				$db->query($sql);
				$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}{$class_id}%' ORDER BY class_order_id";
				//echo 'sql 21.'.$sql.'<br>';
				$dborder = $db->getall($sql);
				if (is_array($dborder)){
					$i = 0;
					foreach ($dborder as $key=>$value){
						$i++;
						$iparentpath = $result['class_parent_path'].','.$result['class_id'].','.str_replace($class_parent_path,'',$value['class_parent_path']);
						$sql = "UPDATE `{$db_pre}menu` SET class_depth=class_depth-{$class_depth}+{$result['class_depth']}+1,class_order_id={$prevorderid}+{$i}+1,class_root_id={$result['class_root_id']},class_parent_path='{$iparentpath}' WHERE class_id={$value['class_id']}";
						//echo 'sql 22.'.$sql.'<br>';
						$db->query($sql);	
					}
				}
				$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child+1 WHERE class_id={$class_parent_id}";
				//echo 'sql 23.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child-1 WHERE class_id={$iparentid}";
				//echo 'sql 24.'.$sql.'<br>';
				$db->query($sql);	
			}
			else{                                            //如果原来是根栏目，现改为其他栏目的下属栏目
				$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_root_id={$class_root_id}";
				//echo 'sql 25.'.$sql.'<br>';
				$classcount = $db->num_rows($sql); //得到移动的栏目总数
				$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_parent_id}";
				//echo 'sql 26.'.$sql.'<br>';
				$result = $db->getone($sql);
				if ($result['class_child'] > 0){
					$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_id={$result['class_id']}";
					//echo 'sql 27.'.$sql.'<br>';
					$prevorderid = $db->getone($sql);
					$prevorderid = intval($prevorderid[0]);
					$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_id={$result['class_id']} AND class_order_id={$prevorderid}";
					//echo 'sql 28.'.$sql.'<br>';
					$class_prev_id = $db->getone($sql);
					$class_prev_id = $class_prev_id[0];
					$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_parent_id={$result['class_id']} AND class_order_id={$prevorderid}";
					//echo 'sql 29.'.$sql.'<br>';
					$db->query($sql);
					$sql = "SELECT MAX(class_order_id) FROM `{$db_pre}menu` WHERE class_parent_path LIKE '{$result['class_parent_path']},{$result['class_id']},%'";
					//echo 'sql 30.'.$sql.'<br>';
					$maxorderid = $db->getone($sql); //得到与本栏目同级栏目包括子栏目最后一个栏目的OrderID
					$maxorderid = intval($maxorderid[0]);
					if ($maxorderid > $prevorderid){
						$prevorderid = $maxorderid;
					}
				}
				else{
					$class_prev_id = 0;
					$prevorderid = $result['class_order_id'];
				}
				$sql = "UPDATE `{$db_pre}menu` SET class_order_id=class_order_id+{$classcount} WHERE class_root_id={$result['class_root_id']} AND class_order_id>{$prevorderid}";
				//echo 'sql 27.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_prev_id},class_next_id=0 WHERE class_id={$class_id}";
				//echo 'sql 28.'.$sql.'<br>';
				$db->query($sql);
				$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_root_id={$class_root_id} ORDER BY class_order_id";
				//echo 'sql 29.'.$sql.'<br>';
				$dbarray = $db->getall($sql);
				if (is_array($dbarray)){
					$i = 0;
					foreach ($dbarray as $key=>$value){
						$i++;
						if ($value['class_parent_id'] == 0){
							$class_parent_path = $result['class_parent_path'].','.$result['class_id'];
							$sql = "UPDATE `{$db_pre}menu` SET class_depth=class_depth+{$result['class_depth']}+1,class_order_id={$prevorderid}+{$i},class_root_id={$result['class_root_id']},class_parent_path='{$class_parent_path}',class_parent_id={$class_parent_id} WHERE class_id={$value['class_id']}";
							//echo 'sql 30.'.$sql.'<br>';
							$db->query($sql);	
						}
						else{
							$class_parent_path = $result['class_parent_path'].','.$result['class_id'].','.str_replace('0,','',$value['class_parent_path']);
							$sql = "UPDATE `{$db_pre}menu` SET class_depth=class_depth+{$result['class_depth']}+1,class_order_id={$prevorderid}+{$i},class_root_id={$result['class_root_id']},class_parent_path='{$class_parent_path}' WHERE class_id={$value['class_id']}";
							//echo 'sql 31.'.$sql.'<br>';
							$db->query($sql);
						}
					}
				}
				$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child+1 WHERE class_id={$class_parent_id}";
				//echo 'sql 32.'.$sql.'<br>';
				$db->query($sql);
				$sql = "UPDATE `{$db_pre}menu` SET class_root_id=class_root_id-1 WHERE class_root_id>{$class_root_id}";
				//echo 'sql 33.'.$sql.'<br>';
				$db->query($sql);
			}
			$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_root_id=(SELECT class_root_id FROM `{$db_pre}menu` WHERE class_id={$iparentid}) ORDER BY class_root_id,class_order_id";
			//echo 'sql 34.'.$sql.'<br>';
			$result = $db->getall($sql);
			if (is_array($result)){
				$i = 0;
				foreach ($result as $key=>$value){
					//$sql = "CREATE TABLE tmp AS SELECT class_root_id AS tmprootid FROM `{$db_pre}menu` WHERE class_id={$iparentid};UPDATE `{$db_pre}menu` SET class_order_id=$i WHERE class_root_id=(SELECT tmprootid FROM tmp) AND class_id={$value['class_id']};DROP TABLE tmp;";
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 35.'.$sql.'<br>';
					$db->query($sql);
					$i++;
				}
			}
		}
		$sql = "UPDATE `{$db_pre}menu` SET class_name='{$class_name}',class_url='{$class_url}',is_show='{$is_show}',menu_flag='{$menu_flag}' WHERE class_id={$class_id}";
		//echo 'sql 36.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}");
	   break;
	case 'delete':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$rows = $db->getone($sql);
		$class_depth       = $rows['class_depth'];
		$class_child       = $rows['class_child'];
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_parent_id   = $rows['class_parent_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$class_root_id     = $rows['class_root_id'];
		$sql = "DELETE FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		$db->query($sql);
		if ($class_depth > 0){
			$sql = "UPDATE `{$db_pre}menu` SET class_child=class_child-1 WHERE class_id={$class_parent_id}";
			$db->query($sql);
		}
		if ($class_child > 0){
			$sql = "DELETE FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			$db->query($sql);
		}
		if ($class_prev_id > 0){
			$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			$db->query($sql);
		}
		if ($class_next_id > 0){
			$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
			$db->query($sql);
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_root_id={$class_root_id} ORDER BY class_root_id,class_order_id";
		$result = $db->getall($sql);
		if (is_array($result)){
			$i = 0;
			foreach ($result as $key=>$value){
				$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$i} WHERE class_id={$value['class_id']}";
				$db->query($sql);
				$i++;
			}
		}
		header("Location:{$CurrentPageName}");
		break;
	case 'order':
		$smarty->assign("menu_array",menu_array('class_parent_id=0'));
		break;
	case 'up_order':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		//echo 'sql 01.'.$sql.'<br>';
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_root_id     = $rows['class_root_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_prev_id}";
		//echo 'sql 03.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$crootid     = $rows['class_root_id'];
		$cparentpath = $rows['class_parent_path'].','.$class_prev_id;
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$crootid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
		//echo 'sql 04.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$class_root_id},class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		//echo 'sql 05.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_id={$cprevid}";
		$db->query($sql);
		//echo 'sql 06.'.$sql.'<br>';
		$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
		//echo 'sql 07.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$crootid} WHERE class_parent_path LIKE '%{$class_parent_path}%'";
		//echo 'sql 08.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$class_root_id} WHERE class_parent_path LIKE '%{$cparentpath}%'";
		//echo 'sql 09.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order");
		break;
	case 'down_order':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		if ($db->num_rows($sql)==0){
			WriteErrMsg('栏目不存在，或者已经被删除');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
		$rows = $db->getone($sql);
		$class_prev_id     = $rows['class_prev_id'];
		$class_next_id     = $rows['class_next_id'];
		$class_root_id     = $rows['class_root_id'];
		$class_parent_path = $rows['class_parent_path'].','.$class_id;
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_next_id}";
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$crootid     = $rows['class_root_id'];
		$cparentpath = $rows['class_parent_path'].','.$class_next_id;
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$crootid},class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$class_root_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_id} WHERE class_id={$cnextid}";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$crootid} WHERE class_parent_path LIKE '%{$class_parent_path}%'";
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_root_id={$class_root_id} WHERE class_parent_path LIKE '%{$cparentpath}%'";
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order");
		break;
	case 'order_other':
		$menu_array = menu_array();
		for ($i = 0; $i < count($menu_array); $i++){
			$sql = "SELECT Count(class_id) FROM `{$db_pre}menu` WHERE class_parent_id={$menu_array[$i]['class_parent_id']} AND class_order_id<{$menu_array[$i]['class_order_id']}";
			$result = $db->getone($sql);
			$menu_array[$i]['class_up'] = $result[0];
			$sql = "SELECT Count(class_id) FROM `{$db_pre}menu` WHERE class_parent_id={$menu_array[$i]['class_parent_id']} AND class_order_id>{$menu_array[$i]['class_order_id']}";
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
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
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
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_prev_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$corderid    = $rows['class_order_id'];
		$cchild      = $rows['class_child'];
		$cparentpath = $rows['class_parent_path'].','.$class_prev_id;
		if ($class_child > 0 && $cchild == 0){
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			//echo 'sql 03.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 04.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$classcount}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 05.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 06.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 07.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}
		elseif ($class_child == 0 && $cchild > 0){
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 08.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 09.'.$sql.'<br>';
			$db->query($sql);	
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 10.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 11.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
		}
		elseif ($class_child > 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%'";
			//echo 'sql 12.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;	
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 13.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$classcount}+1,class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 14.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 15.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 16.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 17.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid}+{$classcount}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 18.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}
		else{
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid},class_prev_id={$cprevid},class_next_id={$class_prev_id} WHERE class_id={$class_id}";
			//echo 'sql 19.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id},class_prev_id={$class_id},class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
			//echo 'sql 20.'.$sql.'<br>';
			$db->query($sql);	
		}
		$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_id} WHERE class_id={$cprevid}";
		//echo 'sql 21.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_prev_id} WHERE class_id={$class_next_id}";
		//echo 'sql 22.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order_other");
		break;
	case 'down_order_other':
		if (!is_numeric($class_id)){
			WriteErrMsg('参数错误');
		}
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_id}";
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
		$sql = "SELECT * FROM `{$db_pre}menu` WHERE class_id={$class_next_id}";
		//echo 'sql 02.'.$sql.'<br>';
		$rows = $db->getone($sql);
		$cprevid     = $rows['class_prev_id'];
		$cnextid     = $rows['class_next_id'];
		$corderid    = $rows['class_order_id'];
		$cchild      = $rows['class_child'];
		$cparentpath = $rows['class_parent_path'].','.$class_next_id;
		if ($class_child > 0 && $cchild == 0){
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 03.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 04.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 05.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 06.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}
		elseif ($class_child == 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%'";
			//echo 'sql 07.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+{$classcount}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 08.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 09.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 10.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+{$i} WHERE class_id={$value['class_id']}";
					//echo 'sql 11.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
		}
		elseif ($class_child > 0 && $cchild > 0){
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%'";
			//echo 'sql 12.'.$sql.'<br>';
			$classcount = $db->num_rows($sql);
			$classcount = ($classcount == 0)?1:$classcount;
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+{$classcount}+1,class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 13.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 14.'.$sql.'<br>';
			$db->query($sql);
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$class_parent_path}%' ORDER BY class_order_id";
			//echo 'sql 15.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+{$classcount}+{$i}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 16.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}
			$sql = "SELECT class_id FROM `{$db_pre}menu` WHERE class_parent_path LIKE '%{$cparentpath}%' ORDER BY class_order_id";
			//echo 'sql 17.'.$sql.'<br>';
			$rows = $db->getall($sql);
			if (is_array($rows)){
				$i = 1;
				foreach ($result as $key=>$value){
					$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id}+1 WHERE class_id={$value['class_id']}";
					//echo 'sql 18.'.$sql.'<br>';
					$db->query($sql);
					$i++;	
				}
			}	
		}
		else{
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$corderid},class_prev_id={$class_next_id},class_next_id={$cnextid} WHERE class_id={$class_id}";
			//echo 'sql 19.'.$sql.'<br>';
			$db->query($sql);
			$sql = "UPDATE `{$db_pre}menu` SET class_order_id={$class_order_id},class_prev_id={$class_prev_id},class_next_id={$class_id} WHERE class_id={$class_next_id}";
			//echo 'sql 20.'.$sql.'<br>';
			$db->query($sql);	
		}
		$sql = "UPDATE `{$db_pre}menu` SET class_next_id={$class_next_id} WHERE class_id={$class_prev_id}";
		//echo 'sql 21.'.$sql.'<br>';
		$db->query($sql);
		$sql = "UPDATE `{$db_pre}menu` SET class_prev_id={$class_id} WHERE class_id={$cnextid}";
		//echo 'sql 22.'.$sql.'<br>';
		$db->query($sql);
		header("Location:{$CurrentPageName}?action=order_other");
		break;
	default:
		$smarty->assign('menu_array',menu_array());
}
$smarty->display('admin/qc_manage_menu.html');
$runtime->stop_write();
?>