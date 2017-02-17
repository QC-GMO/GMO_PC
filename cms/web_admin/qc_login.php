<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
$action = daddslashes($_REQUEST['action']);

switch ($action){
	case 'login':
		$UserName  = daddslashes($_POST['UserName']);
		$PassWord  = daddslashes($_POST['PassWord']);
		$CheckCodes = $_POST['CheckCode'];
		if ($web_config['Web_Login_Num']>0){
			$sql = "SELECT id FROM `{$db_pre}record` WHERE ip='".get_real_ip()."' AND add_time>='".date('Y-m-d H:i:s',time()-(60*30))."' AND add_time<='".date('Y-m-d H:i:s')."' AND commend=0";
			$Login_Num = $db->num_rows($sql);
			if ($Login_Num>=$web_config['Web_Login_Num']){
				$ErrorMessages.='<li>对不起，您的登录密码已经连续输错<span class="f_FF0000 en">'.$Login_Num.'</span>次！</li><br><li>当前IP已被锁定，请30分钟后后再尝试登录，谢谢！</li><br>';
				break;
			}	
		}
		if ($UserName == ''){
			$ErrorMessages.='<li>用户名不能为空！</li><br>';
			break;
		}
		if ($PassWord == ''){
			$ErrorMessages.='<li>密码不能为空！</li><br>';
			break;
		}
		if ($CheckCodes == ''){
			$ErrorMessages.='<li>验证码不能为空！</li><br>';
			break;
		}
		if (!$_SESSION['CheckCode']){  
			$ErrorMessages.='<li>你登录时间过长，请重新返回登录页面进行登录。</li><br>';
			break;
		}
		if ($_SESSION['CheckCode']!=$CheckCodes){  
			$ErrorMessages.='<li>您输入的确认码和系统产生的不一致，请重新输入。</li><br>';
			break;
		}
		$sql = "SELECT * FROM `{$db_pre}manage` WHERE username='".$UserName."' AND password='".md5($PassWord)."' LIMIT 0,1";
		if ($db->num_rows($sql)==0){
			$ErrorMessages.='<li>用户名或密码错误';
			if ($web_config['Web_Login_Num']>0){
				$ErrorMessages.='，您还可以尝试<span class="f_FF0000 en">'.($web_config['Web_Login_Num']-$Login_Num-1).'</span>次';	
			}
			$ErrorMessages.='。</li><br>';
			//*********************************** 写入日志 ***********************************
			$sql = "INSERT INTO `{$db_pre}record` (title,content,ip,add_time)VALUES('登录系统','注意：主机 ".get_real_ip()." 尝试使用用户名 [ ".$UserName." ] 登陆系统，登陆失败！','".get_real_ip()."','".date('Y-m-d H:i:s')."')";
			$db->query($sql);
			//*********************************** 写入日志 ***********************************
			break;
		}
		else{
			$rows = $db->getone($sql);
			$UserID     = $rows['id'];
			$UserName   = $rows['username'];
			$PassWord   = $rows['password'];
			$Flag       = $rows['flag'];
			$Purview    = $rows['purview'];
			$RandomCode = md5($UserName.$PassWord.time());
			$sql = "UPDATE `{$db_pre}manage` SET
						login_ip='".get_real_ip()."',
						login_time='".date("Y-m-d H:i:s")."',
						last_login_ip='".$rows['login_ip']."',
						last_login_time='".$rows['login_time']."',
						randomcode='".$RandomCode."',
						login_num=login_num+1
					WHERE id={$UserID}";
			$db->query($sql);
			unset($_SESSION['CheckCode']);
			$_SESSION[$web_config['Web_SessionName'].'_Admin_UserID']     = $UserID;
			$_SESSION[$web_config['Web_SessionName'].'_Admin_UserName']   = $UserName;
			$_SESSION[$web_config['Web_SessionName'].'_Admin_PassWord']   = $PassWord;
			$_SESSION[$web_config['Web_SessionName'].'_Admin_Flag']       = $Flag;
			$_SESSION[$web_config['Web_SessionName'].'_Admin_Purview']    = $Purview;
			$_SESSION[$web_config['Web_SessionName'].'_Admin_RandomCode'] = $RandomCode;
			if($_SESSION[$web_config['Web_SessionName'].'_Admin_Flag']>0){
				//*********************************** 写入日志 ***********************************
				$sql = "INSERT INTO `{$db_pre}record` (title,content,ip,add_time,username,commend)VALUES('登录系统','登陆系统成功！','".get_real_ip()."','".date('Y-m-d H:i:s')."','".$UserName."',1)";
				$db->query($sql);		
				//*********************************** 写入日志 ***********************************
			}
			//记录在线人数
			$start=date("Y-m-d H:i:s",strtotime(date("Y-m-d")));
			$end=date("Y-m-d H:i:s",time());
			$sql_onlinenums="SELECT `id` FROM `".$db_pre."limitnums` WHERE  `add_time` > '".$start."' and `add_time` < '".$end."'";
			if($db->num_rows($sql_onlinenums) > $web_config['Web_Limitnums'] ){
				$ErrorMessages.='<li>你无法登陆，在线人数超过了后台的限制</li><br>';
				break;
			}	
			$sql_nums="SELECT `id` FROM `".$db_pre."limitnums` WHERE `username` = '".$UserName."'";
			if($db->num_rows($sql_nums) ==  0){
					$sql_limitnums="INSERT INTO `".$db_pre."limitnums` (`id`, `username`, `ip`, `add_time`) VALUES (NULL, '".$UserName."', '".get_real_ip()."', '".date('Y-m-d H:i:s')."');";
					$db->query($sql_limitnums);
			}
			//记录在线人数
			header('Location:qc_manage_index.php');
		}
		break;
	case 'logout':
		$sql_delnums="DELETE FROM `".$db_pre."limitnums` WHERE `username` = '".$_SESSION[$web_config['Web_SessionName'].'_Admin_UserName']."' ";
		$db->query($sql_delnums);	
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_UserID']);
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_UserName']);
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_PassWord']);
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_Flag']);
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_Purview']);
		unset($_SESSION[$web_config['Web_SessionName'].'_Admin_RandomCode']);
		header('Location:'.$CurrentPageName);
		break;
}

$smarty->assign('ErrorMessages',$ErrorMessages);
$smarty->assign('CurrentPageName',$CurrentPageName);
$smarty->display('admin/qc_login.html');
?>