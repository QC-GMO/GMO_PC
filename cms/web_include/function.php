<?php
/*fckeditor内容分页*/
function pageBreak($content) 
{ 
	$content = $content; 
	$pattern = "/<div style=\"page-break-after: always\"><span style=\"display: none\">&#160;<\/span><\/div>/"; 
	$strSplit = preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY); 
	$count = count($strSplit); 
	$outStr = ""; 
	$i = 1; 
	
	if ($count > 1 ) { 
	$outStr = "<div id='page_break'>"; 
	foreach($strSplit as $value) { 
	if ($i <= 1) { 
	$outStr .= "<div id='page_$i'>$value</div>"; 
	} else { 
	$outStr .= "<div id='page_$i' class='collapse'>$value</div>"; 
	} 
	$i++; 
	} 
	
	$outStr .= "<div class='num'>"; 
	for ($i = 1; $i <= $count; $i++) { 
	$outStr .= "<li>$i</li>"; 
	} 
	$outStr .= "</div></div>"; 
	return $outStr; 
	} else { 
	return $content; 
	} 
} 
//验证是否登陆，是否超时，是否多人登陆
function Authentication_Admin(){
	global $db;
	global $db_pre;
	global $Session;	
	if ($Session['Admin']['UserID']=='' || $Session['Admin']['UserName']=='' || $Session['Admin']['PassWord']=='' || $Session['Admin']['Flag']==''){
		WriteErrMsg('您的帐号已超时，为了安全期间，您必须<span class="cursor_p f_FF0000" onClick="top.location.href=\'qc_login.php?action=LoginOut\';">重新登录</span>');	
	}
	else{
		$sql = "SELECT id FROM `{$db_pre}manage` WHERE username='".$Session['Admin']['UserName']."' AND password='".$Session['Admin']['PassWord']."' AND randomcode='".$Session['Admin']['RandomCode']."'";
		if ($db->num_rows($sql)==0){
		   $sql = "SELECT login_ip,login_time FROM `{$db_pre}manage` WHERE username='".$Session['Admin']['UserName']."' LIMIT 0,1";
		   $rows = $db->getone($sql);
		   WriteErrMsg('您的帐号已在 <span class="f_FF0000">'.$rows['login_time'].'</span> IP为 <span class="f_FF0000">'.$rows['login_ip'].'</span> 的地方登录，为了安全期间，您的帐号已自动退出！<span class="cursor_p f_FF0000" onClick="top.location.href=\'qc_login.php?action=LoginOut\';">重新登录</span>？');	
		}
	}
}
/*
 * 验证页面访问权限【aben】
 */
function Authentication_Purview(){
	global $db;
	global $db_pre;
	global $Session;
	global $CurrentPageName;
	if ($Session['Admin']['Flag']>1){
		$sql_purview = ($Session['Admin']['Purview']!='')?" AND class_id IN (".$Session['Admin']['Purview'].")":" AND class_id IN (0)";
	}
	$sql = "SELECT * FROM `{$db_pre}menu` WHERE is_show=1 AND menu_flag>={$Session['Admin']['Flag']}{$sql_purview} AND class_url LIKE '%{$CurrentPageName}%'";
	if ($db->num_rows($sql)==0){
		WriteErrMsg('对不起，您没有访问此页面的权限！');
	}	
}
// 获取网站开启状态
function web_status($web_config) {
	if ($web_config['Web_Close'] == 'close'){
		global $smarty;
		$web_status = '<style type="text/css">.close{margin:0px;padding:0px;text-align:center;width:100%;background-color:#FFF;border:1px #CCC solid;}.close strong{color:#F00;font-size:14px;font-weight:bold;line-height:30px;}.close div {font-size:12px;line-height:20px;}pre {white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;margin:0px;}* html pre {word-wrap:break-word;white-space:normal;}</style><div class="close"><strong>网站状态提示</strong><div><pre>'.autolink($web_config['Web_Reason']).'</pre></div></div>';
		$smarty->assign("web_status",$web_status);
	}
}
//*****************************************************************************
function getMenuID($class_id, $parent_id){
	global $db;
	global $db_pre;
	if (!is_numeric($class_id)) {
		$sql = "SELECT class_id FROM `{$db_pre}web_class` WHERE is_show=1 AND class_parent_id = '".$parent_id."' AND class_child = 0 ORDER BY class_order_id LIMIT 0,1";
		if ($db->num_rows($sql) > 0){
			$rows = $db->getone($sql);
			$class_id = $rows[0];
		} else {
			$class_id = 0;
		}
	}
	return $class_id;
}
/*
 * 根据服务器是否开启过滤，来判断信息是否过滤
 * 待过滤的字符串  - 是否强制过滤
 */
function daddslashes($string,$force=0){
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(is_array($string)){
		foreach($string as $key => $val){
			$string[$key] = daddslashes($val, $force);
		}
	}else{
		if(!MAGIC_QUOTES_GPC || $force){
		    
			$string = addslashes($string);
		}
		$string = ihtmlspecialchars(trim($string));
	}
	return $string;
}
/*************************************************************************
 * htmlspecialchars() 函数把一些预定义的字符转换为 HTML 实体。
 * */
function ihtmlspecialchars($string){ 
	if(is_array($string)){ 
		foreach($string as $key => $val){ 
			$string[$key] = ihtmlspecialchars($val); 
		}
	}else{ 
		$string = preg_replace('/&amp;((#(\d{3,5}x[a-fA-F0-9]{4})[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', 
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string)); 
	} 
	return trim($string); 
}
//*****************************************************************************
function filed($id){
	global $db;
	global $db_pre;
	$a = $db->getone("SELECT content FROM `".$db_pre."field` WHERE id=".$id);
	$b = explode('|',$a[0]);
	foreach ($b as $k=>$v){
		$c = explode(',',$v);
		$d[$c[1]] = $c[0];
	}
	return $d;
}
//*****************************************************************************
function send_mail($mail_type, $mail_to, $mailsubject, $mailbody){
	global $web_config;
	$mail_from = $web_config['Web_Smtp_ID'];
	$mail_fromName = $web_config['Web_Smtp_ID'];
	$smtp_host = $web_config['Web_Smtp'];
	$smtp_username = $web_config['Web_Smtp_ID'];
	$smtp_password = $web_config['Web_Smtp_PW'];
	switch ($mail_type){
		case 'mail':
			$mailheaders = 'MIME-Version: 1.0' . "\r\n";  
			$mailheaders.= 'Content-type: text/html; charset=utf-8' . "\r\n"; 
			$mailheaders.= 'Reply-To:'. $mail_to . "\r\n";
			$mailheaders.= 'From:'. $mail_from . "\r\n";
			$mailsubject = "=?UTF-8?B?".base64_encode($mailsubject)."?=";
			if (mail($mail_to, $mailsubject, $mailbody, $mailheaders)){
				return 'success';
			}
			else{
				return 'failure';
			}
			break;
		case 'smtp':
			require_once("class.phpmailer.php");
			require_once("class.smtp.php");
			$mail = new PHPMailer();
			$mail->CharSet = "UTF-8";          // 设置编码
			$mail->IsSMTP();
			$mail->SMTPAuth = true;            // 设置为安全验证方式
			$mail->Host     = $smtp_host;      // SMTP服务器地址
			$mail->Username = $smtp_username;  // 登录用户名
			$mail->Password = $smtp_password;  // 登录密码
			$mail->From     = $mail_from;      // 发件人地址(username@163.com)
			$mail->FromName = $mail_fromName;    
			$mail->WordWrap   = 50;
			$mail->IsHTML(true);               // 是否支持html邮件，true 或false
			$mail_arr = explode(";",$mail_to);
			foreach ($mail_arr as $mailto){
				$mail->AddAddress($mailto);        // 客户邮箱地址
			}
			$mail->Subject = $mailsubject;
			$mail->Body    = '<div style="line-height:25px">'.$mailbody.'<div>';
			if($mail->Send()){
				return 'success';
			}
			else{
				return "Mailer Error: " . $mail->ErrorInfo;
			}
			break;
		default:
			return 'failure';
			break;
	}
}
//介绍朋友发件箱
function send_mail2($mail_to, $mailsubject, $mailbody){
    $mail_from = 'info@zcom.asia';
    $mail_fromName = 'info@zcom.asia';
    $smtp_host = 'smtp.qiye.163.com';
    $smtp_username = 'info@zcom.asia';
    $smtp_password = '0214qaz@@';
    
    require_once("class.phpmailer.php");
    require_once("class.smtp.php");
    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";          // 设置编码
    $mail->IsSMTP();
    $mail->SMTPAuth = true;            // 设置为安全验证方式
    $mail->Host     = $smtp_host;      // SMTP服务器地址
    $mail->Username = $smtp_username;  // 登录用户名
    $mail->Password = $smtp_password;  // 登录密码
    $mail->From     = $mail_from;      // 发件人地址(username@163.com)
    $mail->FromName = $mail_fromName;
    $mail->WordWrap   = 50;
    $mail->IsHTML(true);               // 是否支持html邮件，true 或false
    $mail_arr = explode(";",$mail_to);
    foreach ($mail_arr as $mailto){
        $mail->AddAddress($mailto);        // 客户邮箱地址
    }
    $mail->Subject = $mailsubject;
    $mail->Body    = '<div style="line-height:25px">'.$mailbody.'<div>';
    if($mail->Send()){
        return 'success';
    }
    else{
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}
//*****************************************************************************
function is_email($email){
	$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i";
	return preg_match($pattern, trim($email))?true:false;
}

function autolink($foo){
    $foo = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_+.~#?&//=]+)', '<a href="\1" target=_blank rel=nofollow>\1</a>', $foo);
    if( strpos($foo, "http") === FALSE ){
        $foo = eregi_replace('(www.[-a-zA-Z0-9@:%_+.~#?&//=]+)', '<a href="http://\1" target=_blank rel=nofollow >\1</a>', $foo);
    }
    else{
        $foo = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_+.~#?&//=]+)', '\1<a href="http://\2" target=_blank rel=nofollow >\2</a>', $foo);
    }
    return $foo;
}

/*
 * 获取后台操作管理员的IP地址【aben】
 */
function get_real_ip(){ 	
	$ip=false; 
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){ 
		$ip = $_SERVER["HTTP_CLIENT_IP"]; 
	} 
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
		$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']); 
		if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
		for ($i = 0; $i < count($ips); $i++) {
			if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
				$ip = $ips[$i];
				break;
			}
		}
	}
	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']); 
} 

function cut_str($string,$sublen,$start=0,$code='UTF-8'){
    if($code=='UTF-8'){
        $pa="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa,$string,$t_string);
        if(count($t_string[0])-$start>$sublen)return join('',array_slice($t_string[0],$start,$sublen))."...";
        return join('',array_slice($t_string[0],$start,$sublen));
    }
    else{
        $start=$start*2;
        $sublen=$sublen*2;
        $strlen=strlen($string);
        $tmpstr='';
        for($i=0;$i<$strlen;$i++){
            if($i>=$start&&$i<($start+$sublen)){
                if(ord(substr($string,$i,1))>129){
                    $tmpstr.=substr($string,$i,2);
                }
                else{
                    $tmpstr.=substr($string,$i,1);
                }
            }
            if(ord(substr($string,$i,1))>129)$i++;
        }
        if(strlen($tmpstr)<$strlen)$tmpstr.="...";
        return$tmpstr;
    }
}
/*
 * fck编辑器【aben】
 */
//*************************************************************
function location($url){
	echo "<script>";
	echo "location.href='".$url."'";
	echo "</script>";
	exit;
}
//*************************************************************
function is_query_string($url){
	if (strpos($url,'?')){
		$ju  =  "&";
	}
	else{
		$ju  =  "?";
	}
	return $ju;
} 
//**********************辅助page函数********************************
function list_page($page,$page_count){ //$page当前页，$page_count总页数
	$navi_length  = 3; //每页显示的分页页码数量 $navi_length*2+1
	$page_start   = $page - $navi_length;                 //计算页码开始值
	$page_end     = $page + $navi_length;                   //计算页码结束值
	if ($page_start <= 1){
		$page_end   = $navi_length*2+1;
	}
	if ($page_end > $page_count){                        //如果页码结束值大于总页数,则=总页数
		$page_start = $page_count-$navi_length*2;
		$page_end   = $page_count;	
	}
	if ($page_start <= 1){
		$page_start = 1;
	}
	for ($j=$page_start;$j<=$page_end;$j++){
		$page_array[]=$j;
	}
	return $page_array;
}
//*************************************************************
function page($sql,$page,$pagesize,$url){//列表分页
	global $db;
	global $smarty;
	$records = $db->num_rows($sql);      // 总的记录数
	$page_count = ceil($records/$pagesize); // 总页数
	$szl = $sql." LIMIT ".($page - 1)*$pagesize.",".$pagesize;
	$array = $db->getall($szl);   //当前也的记录集
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key]['time']  = date("Y.m.d",strtotime($value['time']));
			$array[$key]['datetime']  = date("Y-m-d H:i:s",strtotime($value['time']));
		}
	}
	$page = ($page>=$page_count)?$page_count:$page;
	$smarty->assign("records",$records);
	$smarty->assign("page",$page);
	$smarty->assign("url",$url);
	$smarty->assign("page_count",$page_count);
	$page_array = list_page($page,$page_count);
	$smarty->assign("page_array",$page_array);
	//exit(var_dump($page_count));
	return $array;
}  
//*************************************************************
function rand_number(){
	$a   =  "1,a,2,b,3,c,4,d,5,e,6,f,6,g,7,h,8,i,9,j,10,k,11,l,12,m,13,n,14,o,15,p,16,q";
	$a_array = explode(",",$a);
	for($i=1;$i<12;$i++){
		$tname.=$a_array[rand(0,30)];
	}
	return $tname;
}
//*************************************************************
function rand_salt(){
	$a =  "1,a,2,b,3,c,4,d,5,e,6,f,6,g,7,h,8,i,9,j,10,k,11,l,12,m,13,n,14,o,15,p,16,q";
	$a_array = explode(",",$a);
	for($i=1;$i<7;$i++){
		$tname.=$a_array[rand(0,30)];
	}
	return $tname;
}
//*************************************************************
function to_url($message,$url){
	global $smarty;
	$smarty->assign("message",$message);
	$smarty->assign("url",$url);
	$content  = $smarty->display("to_url.tpl");
	die($content);
}
//*************************************************************
function server_url(){
	$url  = "http://".$_SERVER['HTTP_HOST'];
	$php_self  =  $_SERVER['PHP_SELF'];
	$pos   =  strrpos($php_self,"/");
	$url.=substr($php_self,0,$pos+1);
	return $url;
}
//*************************************************************
function alert($message,$url=''){
	echo "<script language='javascript'>alert('".$message."');";
	if (!empty($url)) {
		echo "location.href='".$url."'";
	}
	echo "</script>";
	exit;
}
//*************************************************************
function to_back($message){
	echo "<script>";
	echo "alert('".$message."');history.back();";
	echo "</script>";
	exit;
}
//*************************************************************
function class_options($class_id){
	global $db;
	global $db_pre;
    $sql    =  "SELECT * FROM '{$db_pre}web_class' WHERE class_id='$class_id' ORDER BY order_id ASC,id DESC";
	$array  =  $db->option_array($sql);
	return $array;
}
//*************************************************************
function class_sub_id($class_id){
	global $db;
	global $db_pre;
	$rows = $db->getall("SELECT id FROM `{$db_pre}web_class` WHERE class_id=$class_id");
	if(is_array($rows)){
		$sub_id = $rows[0]['id'];
		do{
			$rows_sub = $db->getall("SELECT id FROM `{$db_pre}web_class` WHERE class_id=$sub_id");
			if(is_array($rows_sub)){
				$sub_id = $rows_sub[0]['id'];
			}
			else{
				break;
			}
		}while(1==1);
	}
	if(!$sub_id){
		$sub_id = $class_id;
	}
	return $sub_id;
}
//*************************************************************
function class_parent_path($class_id){
	global $db;
	global $db_pre;
	$rows = $db->getall("SELECT name,content,class_id,type,is_bottom FROM `{$db_pre}web_class` WHERE id=$class_id");
	if($rows[0]['class_id']!=0){
		$class_tem_id = $rows[0]['class_id'];
		$parent_path  = '&nbsp;&nbsp;&gt;&nbsp;&nbsp;<b>'.$rows[0]['name'].'</b>';
		do{
			$rows_path = $db->getall("SELECT name,class_id FROM `{$db_pre}web_class` WHERE id=$class_tem_id");
			if($rows_path[0]['class_id']!=0){
				$class_tem_id = $rows_path[0]['class_id'];
				$parent_path  = '&nbsp;&nbsp;&gt;&nbsp;&nbsp;'.$rows_path[0]['name'].$parent_path;
			}
			else{
				$parent_path  = '首页&nbsp;&nbsp;&gt;&nbsp;&nbsp;'.$rows_path[0]['name'].$parent_path;
				break;
			}
		}while(1==1);
	}	
	return $parent_path;
}
//************************************************************* 
/*
 * 错误信息【aben】
 */
function WriteErrMsg($msg,$url=0){
	global $smarty;
	switch ($url){
		case '0':
			$url = ' onclick="history.back()" value="返回上一页"';
			break;
		case '1':
			$url = ' onclick="window.close()" value="关闭窗口"';
			break;
		default:
			$url = ' onclick="location.href=\''.$url.'\'" value=" 返 回 "';
			break;
	}
	$smarty->assign('msg',$msg);
	$smarty->assign('url',$url);
	$content = $smarty->display('admin/qc_errmsg.html');
	die($content);
}
/*
 * 成功信息【aben】
 */
function WriteSuccessMsg($msg,$url=0){
	global $smarty;
	switch ($url){
		case '0':
			$url = ' onclick="history.back()"';
			break;
		case '1':
			$url = ' disabled';
			break;
		default:
			$url = ' onclick="location.href=\''.$url.'\'"';
			break;
	}
	$smarty->assign('msg',$msg);
	$smarty->assign('url',$url);
	$content = $smarty->display('admin/qc_successmsg.html');
	die($content);
} 
//*************************************************************
function menu_array($where=''){
	global $db;
	global $db_pre;
	$sql = "SELECT * FROM `{$db_pre}menu`";
	$sql.= ($where!='')?" WHERE {$where}":""; 
	$sql .= " ORDER BY class_root_id,class_order_id";
	$result = $db->getall($sql);
	$dbcount = count($result);
	for ($i = 0; $i < $dbcount; $i++){
		$class_depth = $result[$i]['class_depth'];
		$arrLine[$class_depth] = $result[$i]['class_next_id']>0?1:0;
		$result[$i]['class_count'] = $dbcount;
		if ($class_depth > 0){
			for ($j = 0; $j <= $class_depth; $j++){
				if ($j == $class_depth){
					$result[$i]['class_line'] .= '<img src="images/tree_line'.($result[$i]['class_next_id']>0?1:2).'.gif">';
					$result[$i]['class_icon'] .= $result[$i]['class_next_id']>0?'&nbsp;├&nbsp;':'&nbsp;└&nbsp;';
				}
				else{
					$result[$i]['class_line'] .= '<img src="images/tree_line'.($arrLine[$j]==1?3:4).'.gif">';
					$result[$i]['class_icon'] .= $arrLine[$j]==1?'&nbsp;│&nbsp;':'&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}	
		}
	}
	return $result;
}
/*
 * 网站后台 判断导航模块【aben】
 */
function webclass_array($where='', $is_icon=true){
	global $db;
	global $db_pre;
	global $lang;
	$sql = "SELECT *,class_name_{$lang} AS class_name FROM `{$db_pre}web_class` AS A";
	$sql.= ($where!='')?" WHERE 1 {$where}":"";
	$sql.= " ORDER BY class_root_id,class_order_id";  
	$result = $db->getall($sql); 
	$dbcount = count($result); 
	for ($i = 0; $i < $dbcount; $i++){
		$class_depth = $result[$i]['class_depth'];
		$arrLine[$class_depth] = $result[$i]['class_next_id']>0?1:0;
		$result[$i]['class_count'] = $dbcount;   
		if ($class_depth > 0 && $is_icon){  
			for ($j = 0; $j <= $class_depth; $j++){
				if ($j == $class_depth){
					$result[$i]['class_line'] .= '<img src="images/tree_line'.($result[$i]['class_next_id']>0?1:2).'.gif">';
					$result[$i]['class_icon'] .= $result[$i]['class_next_id']>0?'&nbsp;├&nbsp;':'&nbsp;└&nbsp;';
				}
				else{
					$result[$i]['class_line'] .= '<img src="images/tree_line'.($arrLine[$j]==1?3:4).'.gif">';
					$result[$i]['class_icon'] .= $arrLine[$j]==1?'&nbsp;│&nbsp;':'&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}	
		}
	}  
	return $result;
}
//*************************************************************
function class_array($where=''){
	global $db;
	global $db_pre;
	global $lang;
	$sql = "SELECT class_id,class_url AS class_web_url,class_link_to,class_name_{$lang} AS class_name,(SELECT class_name_{$lang} FROM `{$db_pre}web_class` WHERE class_id=A.class_link_to) AS class_link_to_name,(SELECT class_url FROM `{$db_pre}web_class` WHERE class_id=A.class_link_to) AS class_link_to_url
	FROM `{$db_pre}web_class` AS A";
	$sql.= ($where!='')?" WHERE {$where}":"";
	$sql.= " ORDER BY class_root_id,class_order_id";
	$result = $db->getall($sql);
	$dbcount = count($result);
	for ($i = 0; $i < $dbcount; $i++){
		$result[$i]['class_count'] = $dbcount;
		if ($result[$i]['class_link_to']==0){
			$result[$i]['class_url'] = $result[$i]['class_web_url'];
		}
		else{
			$result[$i]['class_url'] = $result[$i]['class_link_to_url'];
		}
	}
	return $result;
}
//*************************************************************
function path_name($parent_path){
	global $db;
	global $db_pre;
	global $lang;
	$parent_path = explode(',',$parent_path);
	$sql = "SELECT class_id,class_name_{$lang} AS class_name FROM `{$db_pre}web_class` WHERE is_show=1 AND class_depth>0";
	$rows = $db->getall($sql);
	if (is_array($rows)){
		foreach ($rows as $key=>$value){
			$rows_name[$value['class_id']] = " > ".$value['class_name'];
		}	
	}
	foreach ($parent_path as $k=>$v){
		$path_name.=$rows_name[$v];
	}
	return $path_name;
} 

/*
 * 后台列表首页  点击“推荐”按照“推荐”排序，类似效果有时间、审核、分类等【aben】
 * 按照排序的字段名  - 模版显示的名字
 */
function orderby($name,$title){
	global $url;
	global $order;
	global $sort;  
	if ($order!=$name){
		$orderby = '<a href="'.$url.'order='.$name.'&sort=asc" class="no">'.$title.'</a>';
	}
	else{
		$orderby = '<a href="'.$url.'order='.$name.'&sort='.(($sort=='asc')?'desc':'asc').'" class="no">';
		$orderby.= '<span class="f_FF0000'.(($sort=='asc')?' arrow_up':' arrow_down').'">'.$title.'</span></a>';
	}
	return $orderby;
} 

/*
 * 重新排序【aben】
 */
function reorder($db_name,$language='',$class_id=''){
	global $db;
	$sql = "UPDATE `".$db_name."` SET order_id=(SELECT @new_id:=@new_id+1) WHERE 1"; 
	$sql.= ($language!='')?" AND language='".$language."'":"";
	$sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
	$sql.= " ORDER BY order_id ASC";
	$db->query("SET @new_id=0");
	$db->query($sql);
}

/*
 * 排序上移/下移【aben】
 * 表名字 - 数据id - 上移还是下移 - 语言 - 分类id
 */
function update_order($db_name,$id,$type,$language='',$class_id='') { 
	global $db;
	$sql = "SELECT id, order_id FROM `".$db_name."` WHERE id='".$id."' LIMIT 0,1";
	$rows = $db->getone($sql);
	$id_1 = $rows['id']; 
	$order_id_1 = $rows['order_id'];
	if($type=='up')  $sql = "SELECT id, order_id FROM `".$db_name."` WHERE order_id < '".$order_id_1."'";
	else $sql = "SELECT id, order_id FROM `".$db_name."` WHERE order_id > '".$order_id_1."'"; 
	$sql.= ($language!='')?" AND language='".$language."'":"";
	$sql.= ($class_id!='')?" AND class_id='".$class_id."'":"";
	if($type=='up') $sql.= " ORDER BY order_id DESC LIMIT 0,1"; 
	else $sql.= " ORDER BY order_id ASC LIMIT 0,1"; 
	if ($db->num_rows($sql)>0){
		$rows = $db->getone($sql);
		$id_2 = $rows['id'];
		$order_id_2 = $rows['order_id']; 
		$db->query("UPDATE `".$db_name."` SET order_id='".$order_id_2."' WHERE id='".$id_1."'");
		$db->query("UPDATE `".$db_name."` SET order_id='".$order_id_1."' WHERE id='".$id_2."'");	 
	}
} 
	
/*
 * 更新'0'或'1'【aben】
 * 表名字 - 字段名字 - 数据id
 */
function update_flag($db_name,$field,$id) {
	global $db;
	$sql = "SELECT `".$field."` FROM `".$db_name."` WHERE id='".$id."'";
	if ($db->num_rows($sql)==0) WriteErrMsg("操作错误"); 
	$rows = $db->getone($sql);
	$flag = ($rows[0]==1)?0:1;  
	$sql = "UPDATE `".$db_name."` SET `".$field."`='".$flag."' WHERE id='".$id."'";
	if(!$db->query($sql)) WriteErrMsg(mysql_error()); 
} 
/*
 * 上传图片【aben】
 * 缓存名字 - 临时名字 - 上传图片存储文件夹 - 上传图片被重置高度  - 宽度
 */
function upload_img($t_file, $o_file, $path, $width='', $height=''){
	if($t_file == ''){
		return '';
	}
	else{
		$paths = '../uploadfiles/'.$path.'/';
		@mkdir($paths,0777);
		$file_array = pathinfo($o_file);
		$file_type  = strtolower($file_array['extension']);
		if ($file_type=='png' || $file_type=='jpg' || $file_type=='gif'){
			$file_name = date('YmdHis').mt_rand(1000, 9999).'.'.$file_type;
			if(move_uploaded_file($t_file,$paths.$file_name)){
				if($width >0 && $height >0) {
					ImageResize($paths.$file_name, $width, $height);
				}
				return 'uploadfiles/'.$path.'/'.$file_name;
			}else{
				return '';
			}
		}else{
			return '';
		}
	}
}   


//上传水印图片
function upload_water_img($t_file,$o_file,$path,$waterimg){
	if($waterimg=='') $waterimg = "images/watermark_1.gif";  //一张默认图片
	else $waterimg = "../".$waterimg; 
	if($t_file == ''){
		return '';
	}
	else{
		$paths = '../uploadfiles/'.$path.'/';
		@mkdir($paths,0777);
		$file_array = pathinfo($o_file); 
		$file_type  = strtolower($file_array['extension']);
		if($file_type=='png' || $file_type=='jpg' || $file_type=='gif'){
			$file_name = date('YmdHis').mt_rand(1000, 9999).'.'.$file_type;
			include_once("../web_include/function_waterimg.php");
			$img=img_water_mark($t_file,$waterimg,$paths,$file_name);  
			if(in_array($img,array(-1,-2,-3,-4,-5))){
				return '';
			}else{
				return ltrim($img,"\.\.\/");  //去掉返回字符串的 "../"
				//return 'uploadfiles/'.$path.'/'.$file_name;
			}
		}else{
			return '';
		}
	}
} 



function copy_img($t_file, $o_file, $path, $width='', $height=''){
	if($t_file == ''){
		return '';
	}
	else{
		$paths = '../uploadfiles/'.$path.'/';
		@mkdir($paths,0777);
		$file_array = pathinfo($o_file);
		$file_type  = strtolower($file_array['extension']);
		if ($file_type=='png' || $file_type=='jpg' || $file_type=='gif'){
			$file_name = date('YmdHis').mt_rand(1000, 9999).'.'.$file_type;
			if(copy($t_file,$paths.$file_name)){
				if($width >0 && $height >0) {
					ImageResize($paths.$file_name, $width, $height);
				} 
				return 'uploadfiles/'.$path.'/'.$file_name;
			}else{
				return '';
			}
		}else{
			return '';
		}
	}
} 
/*
 * 上传文件【aben】
 * 缓存名字 - 文件名字 - 路径
 */
function upload_file($t_file, $o_file, $path){
	if ($t_file == ''){
		return '';
	}
	else{
		$paths = '../uploadfiles/'.$path.'/';
		@mkdir($paths,0777);
		$file_array = pathinfo($o_file);
		$file_type  = strtolower($file_array['extension']);
		if ($file_type=='zip' || $file_type=='rar'  || $file_type=='doc' || $file_type=='pdf' || $file_type=='xls' || $file_type=='ppt' || $file_type=='swf' || $file_type=='wmv' ){
			$file_name = date('YmdHis').mt_rand(1000, 9999).'.'.$file_type;
			if (move_uploaded_file($t_file,$paths.$file_name)){
				return 'uploadfiles/'.$path.'/'.$file_name;
			}
			else{
				return '';
			}
		}
		else{
			return '';
		}
	}
} 
//*************************************************************
function randomname($prefix) {
   //第一步:初始化种子 
   //microtime(); 是个数组
   $seedstr = explode(" ",microtime(),5); 
   $seed = $seedstr[0]*10000;   
   //第二步:使用种子初始化随机数发生器 
   srand($seed);   
   //第三步:生成指定范围内的随机数 
   $random = rand(1000,10000);
   $filename = date("YmdHis", time()).$random.$prefix;
   return $filename;
}
/*
 * 上传图片的时候重新定义图片宽度和高度【aben】
 * 文件上传路径 - 文件名字 - 文件宽度 - 文件高度
 */
function ImageResize($srcFile,$toW='',$toH='',$toFile=''){
	if(!is_numeric($toW) && !is_numeric($toH)){
		return false;
	}
	if($toFile=='' || !isset($toFile)){
	   $toFile= $srcFile;
	}
	$data= @getimagesize($srcFile);
	if(!is_array($data)){
		return;
	}
	switch ($data[2]){
		case 1:
			if(!function_exists('imagecreatefromgif')){
				echo '你的GD库不能使用GIF格式的图片，请使用Jpeg或PNG格式！<a href="javascript:go(-1);">返回</a>';
				exit();
			}
			$im= imagecreatefromgif($srcFile);
		break;
		case 2:
			if(!function_exists('imagecreatefromjpeg')){
				echo '你的GD库不能使用jpeg格式的图片，请使用其它格式的图片！<a href="javascript:go(-1);">返回</a>';
				exit();
			}
			$im= imagecreatefromjpeg($srcFile);
		break;
		case 3:
			$im= imagecreatefrompng($srcFile);
		break;
	}
	$srcW=$data[0];
	$srcH=$data[1];
	$srcWH=$srcW/$srcH; //旧的尺寸比例
	if(trim($toW)=='' && is_numeric($toH)){//使用设定的高度
		if($srcH > $toH){
			$toW=$toH*$srcWH;
			if(function_exists("imagecreatetruecolor")){
				@$ni= imagecreatetruecolor($toW,$toH);
				if($ni){
					imagecopyresampled($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}else{
					$ni=imagecreate($toW,$toH);
					imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}
			}else{
				$ni=imagecreate($toW,$toH);
				imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
			}
			if(function_exists('imagejpeg')){
				imagejpeg($ni,$toFile);
			}else{
				imagepng($ni,$toFile);
			}
			imagedestroy($ni);
			imagedestroy($im);
		}
	}
	if(trim($toH)=='' && is_numeric($toW)){//使用设定的宽度
		if($srcW > $toW){
			$toH=intval($toW/$srcWH);
			if(function_exists("imagecreatetruecolor")){
				@$ni= imagecreatetruecolor($toW,$toH);
				if($ni){
					imagecopyresampled($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}else{
					$ni=imagecreate($toW,$toH);
					imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}
			}else{
				$ni=imagecreate($toW,$toH);
				imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
			}
			if(function_exists('imagejpeg')){
				imagejpeg($ni,$toFile);
			}else{
				imagepng($ni,$toFile);
			}
			imagedestroy($ni);
			imagedestroy($im);
		}
	}
	if(is_numeric($toW) && is_numeric($toH)){/*对高度，宽度都进行限制*/
		$toWH=$toW/$toH;
		if($toWH > $srcWH){/*以高度为准*/
			if($srcH > $toH){
				$toW=$toH*$srcWH;
				if(function_exists("imagecreatetruecolor")){
					@$ni= imagecreatetruecolor($toW,$toH);
					if($ni){
						imagecopyresampled($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}else{
						$ni=imagecreate($toW,$toH);
						imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}
				}else{
					$ni=imagecreate($toW,$toH);
					imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}
				if(function_exists('imagejpeg')){
					imagejpeg($ni,$toFile);
				}else{
					imagepng($ni,$toFile);
				}
				imagedestroy($ni);
				imagedestroy($im);
			}
		}else if($toWH < $srcWH){/*以宽度为准*/
			if($srcW > $toW){
				$toH=intval($toW/$srcWH);
				if(function_exists("imagecreatetruecolor")){
					@$ni= imagecreatetruecolor($toW,$toH);
					if($ni){
						imagecopyresampled($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}else{
						$ni=imagecreate($toW,$toH);
						imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}
				}else{
					$ni=imagecreate($toW,$toH);
					imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}
				if(function_exists('imagejpeg')){
					imagejpeg($ni,$toFile);
				}else{
					imagepng($ni,$toFile);
				}
				imagedestroy($ni);
				imagedestroy($im);
			}
		}else{/*相等的情况*/
			if($srcW > $toW){
				$toH=intval($toW/$srcWH);
				if(function_exists("imagecreatetruecolor")){
					@$ni= imagecreatetruecolor($toW,$toH);
					if($ni){
						imagecopyresampled($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}else{
						$ni=imagecreate($toW,$toH);
						imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
					}
				}else{
					$ni=imagecreate($toW,$toH);
					imagecopyresized($ni,$im,0,0,0,0,$toW,$toH,$srcW,$srcH);
				}
				if(function_exists('imagejpeg')){
					imagejpeg($ni,$toFile);
				}else{
					imagepng($ni,$toFile);
				}
				imagedestroy($ni);
				imagedestroy($im);
			}
		}
	}
}
//生成16位随机数
function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}
//导出excel
function export_xlsx($filename){
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header('Content-Disposition:attachment;filename="'.$filename.'"');
    header("Content-Transfer-Encoding:binary");
}
//读取上传的excel
function readExcel($path){
    //引入PHPExcel类库
    include 'Classes/PHPExcel.php';
    include 'Classes/PHPExcel/IOFactory.php';
    $type = 'Excel2007';//设置为Excel5代表支持2003或以下版本，Excel2007代表2007版
    $xlsReader = \PHPExcel_IOFactory::createReader($type);
    $xlsReader->setReadDataOnly(true);
    $xlsReader->setLoadSheetsOnly(true);
    $Sheets = $xlsReader->load($path);
    //开始读取上传到服务器中的Excel文件，返回一个 二维数组
    $dataArray = $Sheets->getSheet(0)->toArray();
    return $dataArray;
}
/*
 * 上传Excel文件【aben】
 * 缓存名字 - 文件名字 - 路径
 */
function upload_Excel($t_file, $o_file, $path){
    if ($t_file == ''){
        return '';
    }
    else{
        $paths = $path.'/';
        @mkdir($paths,0777);
        $file_array = pathinfo($o_file);
        $file_type  = strtolower($file_array['extension']);
        if ($file_type=='csv'){
            $file_name = date('YmdHis').mt_rand(1000, 9999).'.'.$file_type;
            if (move_uploaded_file($t_file,$paths.$file_name)){
                return $path.'/'.$file_name;
            }
            else{
                return '';
            }
        }
        else{
            return '';
        }
    }
}
/*  Blowfish加密，
 *  key GMO提供值gB9d280c6fd0C398
 *  */
function encrypt($key , $text){
    $size = mcrypt_get_block_size('blowfish', 'ecb');
    $input = pkcs5_pad($text, $size);
    $td = mcrypt_module_open('blowfish', '', 'ecb', '');
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, $key , $iv);
    $data = mcrypt_generic($td, $input);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    $data = bin2hex($data);
    return $data;
}
function pkcs5_pad ($text, $blocksize){
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}
/*  Blowfish加密，
 *  key GMO提供值gB9d280c6fd0C398
 *  */
function decrypt($key , $text){
    $td = mcrypt_module_open('blowfish', '', 'ecb', '');

    $data = pack("H*" , $text);
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, $key , $iv);

    $data = mdecrypt_generic($td, $data);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);

    $data = pkcs5_unpad($data);
    return $data;
}
function pkcs5_unpad($text){
    $pad = ord($text{strlen($text)-1});
    if ($pad > strlen($text)) return false;
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
    return substr($text, 0, -1 * $pad);
}
//get获取数据
function httpGet($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}
function httpPost($url,$data){
    //以上方式获取到的数据是json格式的
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
//ajax返回数据
function ajaxReturn($code,$msg,$result=array()){
    exit( json_encode( array('code'=>$code,'msg'=>$msg,'result'=>$result) ) );
}
//用户登录信息过期
function again(){
    if(empty($_COOKIE['member_mobile'])){
        echo "<script>location.href='login.php'</script>";
    }
}
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}
// 说明：获取 _SERVER['REQUEST_URI'] 值的通用解决方案
// 来源：drupal-5.1 bootstrap.inc
// 整理：CodeBit.cn ( http://www.CodeBit.cn )
 //*************************************************************
function request_uri(){
    if (isset($_SERVER['REQUEST_URI'])){
        $uri = $_SERVER['REQUEST_URI'];
    }
    else{
        if (isset($_SERVER['argv'])){
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        }
        else{
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}
//生成日志文件,发送至后台服务器记录
function error_log_web($title,$type,$parameter,$reason){
    global $web_config;
    $url = $web_config['Web_admin_url'].'/web_admin/qc_web_log.php?action=error_log_web';
    $data=array(
        'title'=>$title,
        'type'=>$type,
        'parameter'=>$parameter,
        'reason'=>$reason,
        'userip'=>get_real_ip(),
    );
    httpPost($url,$data);
}
//日志前台传至后台记录
function error_log_admin($title,$type,$parameter,$reason,$userip=null){
    // 发生的时间
    $dt = date("Y-m-d H:i:s (T)");
    $userip=($userip=='')?get_real_ip():$userip;//空值为后台日志。
    
    $err = "<info>\n";
    $err .= "\t<datetime>" . $dt . "</datetime>\n";
    $err .= "\t<subject>" . $title . "</subject>\n";
    $err .= "\t<parameter>" . $parameter . "</parameter>\n";
    $err .= "\t<type>" . ($type==1?'succeed':($type==2?'fail':'error')) . "</type>\n";
    $err .= "\t<reason>" . $reason . "</reason>\n";
    $err .= "\t<userip>" . $userip . "</userip>\n";
    $err .= "</info>\n\n";
    
    error_log($err, 3, "log/".date('Ymd').".log");
    //每次检测log文件是否可压缩，并压缩
    zip_log();
    //发生错误发送邮件
    if($type==3){
        $email="793904274@qq.com";
        $mailbody=$err;
        $email=array('793904274@qq.com',/* 'zyz@dn.cn','toffee.tao@gmo-e-lab.com','skuld.zhang@gmo-e-lab.com' */);
        foreach($email as $v){
            send_mail('smtp',$v,$title,$mailbody);
        }
    }
}
//压缩log文件,三个月压缩一次,7776000
function zip_log(){
    $filearray=byDirAddFile('log');
    //数组排序，倒叙
    if(!empty($filearray)){
        foreach ($filearray as $k=>$v){
            $datetime[]=$v;
        }
        array_multisort($datetime,SORT_ASC,$filearray);//对数组进行时间排序，倒序
    }
    if(strtotime($filearray[0])<(time()-7776000)){
        $zip=new ZipArchive();
        $filename_zip = 'log/'.$filearray[0]."-".end($filearray).".zip";
        if ($zip->open($filename_zip, ZIPARCHIVE::CREATE)!==TRUE) {
            exit("cannot open <$filename_zip>\n");
        }
        foreach($filearray as $v){
            $zip->addFile('log/'.$v.'.log',$v.'.log');
        }
        //echo "numfiles: " . $zip->numFiles . "\n";
        //echo "status:" . $zip->status . "\n";
        $numFiles=$zip->numFiles;
        $status=$zip->status;
        //成功生成文件后，删除log
        $zip->close();
        if($status == 0 && $numFiles > 0){//状态成功并且数目大于0,则删除所有$filearray文件
            foreach($filearray as $v){
                //if(is_file($filename)) {//如果是文件，则执行删除操作
                @unlink('log/'.$v.'.log');
                //}
            }
        }
    }
}
//获取所有log文件夹下log文件
function byDirAddFile($dir, &$out = null){
    $out == null && $out = array();
    if (is_dir($dir) && ($dh = opendir($dir)))
    {
        while (($file = readdir($dh)) !== false)
        {
            if($file == '.' || $file == '..')
            {
                continue;
            }
            if(file_exists($dir. '/'. $file) && !is_dir($dir. '/'. $file))// && !is_dir($dir. '/'. $file)子目录下文件一并添加到out,不包含目录元素
            {
                if( substr(strrchr($file, '.'), 1)=='log'){
                    //$out_dir[] = $dir. '/'. $file;  //包含路径
                    //过滤文件后缀
                    $file=strstr ( $file , '.' , true );
                    $out[] = $file;    //不包路径
                }
            }
            else
            {
                byDirAddFile($dir. '/'. $file, $out);
            }
        }
        closedir($dh);
    }
    return $out;
}
// 用户自定义错误处理函数
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    // 错误发生的时间
    $dt = date("Y-m-d H:i:s (T)");

    // 定义错误字符串的关联数组
    // 在这里我们只考虑
    // E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING 和 E_USER_NOTICE
    $errortype = array (
        E_ERROR              => 'Error',
        E_WARNING            => 'Warning',
        E_PARSE              => 'Parsing Error',
        E_NOTICE             => 'Notice',
        E_CORE_ERROR         => 'Core Error',
        E_CORE_WARNING       => 'Core Warning',
        E_COMPILE_ERROR      => 'Compile Error',
        E_COMPILE_WARNING    => 'Compile Warning',
        E_USER_ERROR         => 'User Error',
        E_USER_WARNING       => 'User Warning',
        E_USER_NOTICE        => 'User Notice',
        E_STRICT             => 'Runtime Notice',
        E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
    );
    // 设置要保存变量跟踪信息的错误类别
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    if($errno==1 || $errno==2 || $errno==4){
        $err = "<errorentry>\n";
        $err .= "\t<datetime>" . $dt . "</datetime>\n";
        $err .= "\t<errornum>" . $errno . "</errornum>\n";
        $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
        $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
        $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
        $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";
        
        if (in_array($errno, $user_errors)) {
            $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
        }
        $err .= "</errorentry>\n\n";
        
        // for testing
        // echo $err;
        
        // 记录错误信息到错误日志，并在发生关键用户错误时发送电子邮件
        error_log($err, 3, "error.log");
    }
    
}
//*************************************************************
//后台管理员操作记录函数

function operatingLog(){
	global $db;
	echo '记录后台管理员操作';
	exit;
}
//后台操作记录【aben】
function insert_Adminlog($u,$t,$c){
	global $db;
	global $db_pre;
	$username=$u;
	$title=$t;
	$content=$c;
	$ip=get_real_ip();
	$time=date("Y-m-d H:i:s",time());
	$sql_insert="INSERT INTO `".$db_pre."operatinglog` (`id`, `username`, `title`, `content`, `ip`, `add_time`) VALUES (NULL, '{$username}', '{$title}', '{$content}', '{$ip}', '{$time}');";	
	$db->query($sql_insert);
}

//*************************************************************
class runtime{ //计算页面执行时间
    var $StartTime = 0; 
    var $StopTime = 0; 
    function get_microtime() { 
        list($usec, $sec) = explode(' ', microtime()); 
        return ((float)$usec + (float)$sec); 
    } 
    function start() { 
        $this->StartTime = $this->get_microtime(); 
    } 
    function stop() { 
        $this->StopTime = $this->get_microtime(); 
    } 
    function spent() { 
        return round(($this->StopTime - $this->StartTime), 5); 
    }
    function stop_write() { 
		$this->stop();
		echo '<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr><td align="center">页面执行时间：<span class="f_FF0000">'.$this->spent().'</span> 秒</td></tr></table>';
    } 
}
$runtime = new runtime;
$runtime->start();
?>