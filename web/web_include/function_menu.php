<?php 
    //顶部导航
	function get_menu_list($class_id=0,$menu_module=0,$flag=false){
		global $db_pre,$db;
		$sql= "SELECT * FROM `".$db_pre."web_class` WHERE 1";
		$sql.=" and `class_parent_id` = ".$class_id;
		$sql.=" and `is_show` = 1 and `menu_module`=".$menu_module;
		$sql.=" ORDER BY class_root_id asc";
		// $sql.=" ORDER BY class_order_id desc";
		$list_menu= $db->getall($sql);
		if(is_array($list_menu)){
			foreach($list_menu as $key => $val){
				if($val['class_child']>0){
					$list_menu[$key]['cid'] = get_default_menu_id($val['class_id']); 
					$list_menu[$key]['son'] = ($flag?get_menu_list1($val['class_id'],true):""); 
				}else{
					$list_menu[$key]['cid'] = $val['class_id'];
				}  
			}	
		}
		return $list_menu; 
	} 

	    //顶部导航
	function get_menu_list1($class_id=0,$flag=false){
		global $db_pre,$db;
		$sql= "SELECT * FROM `".$db_pre."web_class` WHERE 1";
		$sql.=" and `class_parent_id` = ".$class_id;
		$sql.=" and `is_show` = 1";
		$sql.=" ORDER BY class_root_id desc";
		//$sql.=" ORDER BY class_order_id " .(($class_id==28||$class_id==29||$class_id==30||$class_id==3||$class_id==4||$class_id==5)?'desc':'asc');
		$list_menu= $db->getall($sql);
		if(is_array($list_menu)){
			foreach($list_menu as $key => $val){
				if($val['class_child']>0){
					$list_menu[$key]['cid'] = get_default_menu_id($val['class_id']); 
					$list_menu[$key]['son'] = ($flag?get_menu_list1($val['class_id'],true):""); 
				}else{
					$list_menu[$key]['cid'] = $val['class_id'];
				}  
			}	
		}
		return $list_menu; 
	} 
	//得到第一个底级子类信息
    function get_son_menu_message($cid){
		global $db_pre,$db;
		$sql= "SELECT * FROM `".$db_pre."web_class` WHERE 1";
		$sql.=" and `class_parent_id` = ".$cid;
		$sql.=" and `is_show` = 1 ";
		//$sql.=" ORDER BY class_order_id " .(($cid==28||$cid==29||$cid==30||$cid==3||$cid==4||$cid==5)?'desc':'asc');
		$sql.=" ORDER BY class_order_id asc";
		$sql.=" LIMIT 1 ";
		$list_menu= $db->getone($sql);
		if(!empty($list_menu)){
			if($list_menu['class_child']>0){
				$list_menu = get_son_menu_message($list_menu['class_id']);
			}
		}
		return $list_menu;

	}
	//得到底级子类全部信息
	function get_son_menu_message1($cid){
	    global $db_pre,$db;
	    $sql= "SELECT * FROM `".$db_pre."web_class` WHERE 1";
	    $sql.=" and `class_parent_id` = ".$cid;
	    $sql.=" and `is_show` = 1 ";
	    //$sql.=" ORDER BY class_order_id " .(($cid==28||$cid==29||$cid==30||$cid==3||$cid==4||$cid==5)?'desc':'asc');
	    $sql.=" ORDER BY class_order_id asc";
	    //$sql.=" LIMIT 1 ";
	    $list_menu= $db->getall($sql);
	    if(!empty($list_menu)){
	        if($list_menu['class_child']>0){
	            $list_menu = get_son_menu_message($list_menu['class_id']);
	        }
	    }
	    return $list_menu;
	
	}
	//得到顶级父类信息（包含子子类）
	function get_parent_menu_message($cid){
		$now_menu=get_menu_info($cid);
		if($now_menu['class_parent_id']){
			$now_menu=get_parent_menu_message($now_menu['class_parent_id']);
		}
		return $now_menu;
	}

	//栏目关键字
	function get_menu_info($cid){
		global $db_pre,$db;  
		$sql="SELECT * FROM `".$db_pre."web_class` WHERE `class_id` = '".$cid."'";
		$info = $db->getone($sql);   
		return $info;
	} 
	//默认栏目id
	function get_default_menu_id($cid){
		global $db_pre,$db;  
		$sql="SELECT `class_child`,`class_id`,`menu_module` FROM `".$db_pre."web_class` WHERE `class_id` = '".$cid."'"; 
		$info = $db->getone($sql);    
		if($info['class_child']>0&&$info['menu_module']==0){
			$sql="SELECT `class_id` FROM `".$db_pre."web_class` WHERE 1";
			$sql.=" and `class_parent_id` = '".$cid."'"; 
			$sql.=" ORDER BY class_root_id,class_order_id";
			$result = $db->getone($sql);
			return get_default_menu_id($result['class_id']);   
		}else{  
			return $info['class_id']; 
		}
	}  
	//面包屑导航
	function get_bread_menu($string){
		global $db_pre,$db,$lang,$cid;
		$spl = "SELECT * FROM `".$db_pre."web_class` WHERE `class_parent_id` = {$cid}";
	    $now_menu = $db->getone($spl);

		$sql = "select `class_id`,`class_url`,`class_name_".$lang."` as `class_name` from `".$db_pre."web_class` where 1";
		$sql.=" and `class_id` in(".$string.") order by find_in_set(`class_id`,'".$string."')" ;
		$rows= $db->getall($sql); 
		$bread_menu = "";
		if(is_array($rows)){ 
			foreach($rows as $key => $val){ 
				$bread_menu .= "<a href=\"".$val['class_url']."?cid=".$val['class_id']."\">".$val['class_name']."</a> > " ;
			}	 
		}
		// $bread_menu.= "<a href=\"".$now_menu['class_url']."?cid=".$now_menu['class_id']."\">".$now_menu['class_name']."</a> > " ;
		return $bread_menu; 
	} 
	
?>