<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);//$_SERVER['HTTP_REFERER']
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName',$CurrentPageName);
$action = $_REQUEST['action'];
switch ($action){
	case 'update': 
		$Web_SiteName = ihtmlspecialchars($_POST['Web_SiteName']);
		$Web_SiteName = (!empty($Web_SiteName))?$Web_SiteName:$web_config['Web_SiteName'];
		
		$Web_Version = ihtmlspecialchars($_POST['Web_Version']);
		$Web_Version = (!empty($Web_Version))?$Web_Version:$web_config['Web_Version'];
		
		$Web_Admin_Path = ihtmlspecialchars($_POST['Web_Admin_Path']);
		$Web_Admin_Path = (!empty($Web_Admin_Path))?$Web_Admin_Path:$web_config['Web_Admin_Path'];
		
		$config_array  =  array(
			'Web_DB_Host'        => $web_config['Web_DB_Host'],
			'Web_DB_User'        => $web_config['Web_DB_User'],
			'Web_DB_Pass'        => $web_config['Web_DB_Pass'],
			'Web_DB_Name'        => $web_config['Web_DB_Name'],
			'Web_SiteName'       => $Web_SiteName,
			'Web_Version'        => $Web_Version,
			'Web_Admin_Path'     => $Web_Admin_Path,
			'Web_SiteTitle_cn'   => ihtmlspecialchars($_POST['Web_SiteTitle_cn']),
			'Web_Keywords_cn'    => ihtmlspecialchars($_POST['Web_Keywords_cn']),
			'Web_Description_cn' => ihtmlspecialchars($_POST['Web_Description_cn']),
			'Web_SiteTitle_en'   => ihtmlspecialchars($_POST['Web_SiteTitle_en']),
			'Web_Keywords_en'    => ihtmlspecialchars($_POST['Web_Keywords_en']),
			'Web_Description_en' => ihtmlspecialchars($_POST['Web_Description_en']),
			'Web_SiteTitle_jp'   => ihtmlspecialchars($_POST['Web_SiteTitle_jp']),
			'Web_Keywords_jp'    => ihtmlspecialchars($_POST['Web_Keywords_jp']),
			'Web_Description_jp' => ihtmlspecialchars($_POST['Web_Description_jp']),  
			'Web_Url'            => ihtmlspecialchars($_POST['Web_Url']),
			'Web_ICP'            => ihtmlspecialchars($_POST['Web_ICP']),
			'Web_Root'           => ihtmlspecialchars($_POST['Web_Root']),
			'Web_PageSize'       => ihtmlspecialchars($_POST['Web_PageSize']),
			'Web_CheckCode'      => ihtmlspecialchars($_POST['Web_CheckCode']),
			'Web_Login_Num'      => ihtmlspecialchars($_POST['Web_Login_Num']),
			'Web_SessionName'    => ihtmlspecialchars($_POST['Web_SessionName']),
			'Web_Close'          => ihtmlspecialchars($_POST['Web_Close']),
			'Web_Reason'         => ihtmlspecialchars($_POST['Web_Reason']),
			'Web_Smtp'           => ihtmlspecialchars($_POST['Web_Smtp']),
			'Web_Smtp_ID'        => ihtmlspecialchars($_POST['Web_Smtp_ID']),
			'Web_Smtp_PW'        => ihtmlspecialchars($_POST['Web_Smtp_PW']),
			'Web_Email_In'       => ihtmlspecialchars($_POST['Web_Email_In']),
			'Company_Address_cn'    => ihtmlspecialchars($_POST['Company_Address_cn']),
		    'Company_Address_en'    => ihtmlspecialchars($_POST['Company_Address_en']),
		    'Company_Address_jp'    => ihtmlspecialchars($_POST['Company_Address_jp']),
			'Company_Fax'        => ihtmlspecialchars($_POST['Company_Fax']),
			'Company_Url'        => ihtmlspecialchars($_POST['Company_Url']),
		    'Web_Blowfish'        => $web_config['Web_Blowfish'],
		    'Web_panelType'        => $web_config['Web_panelType'],
		);
		
		$old_admin_path = $web_config['Web_Admin_Path'];
		$new_admin_path = $config_array['Web_Admin_Path'];
		$config_array   = var_export($config_array, true); //$config_array = serialize($config_array);
		$write_content  = '<?php
/********* 系统配置 *********/
$web_config = '.$config_array.';
?>';
		$fp = fopen(WEB_ROOT.'web_include/config.php','w+');
		if(fwrite($fp, $write_content)){
			if ($new_admin_path==$old_admin_path){
				WriteSuccessMsg('信息更新成功',$CurrentPageName);
			}else{
				if (rename('../'.$old_admin_path, '../'.$new_admin_path)){
					WriteSuccessMsg('系统检测到您修改<span class="f_0000FF">后台管理地址</span>操作成功，您必须<span class="cursor_p f_FF0000" onClick="top.location.href=\'../'.$new_admin_path.'/qc_manage_index.php\';">跳转到新的管理地址</span>才能继续操作！',1);
				}else{
					WriteErrMsg('系统检测到您修改<span class="f_0000FF">后台管理地址</span>操作失败，很可能是您的服务器不支持此修改！');
				}
			}
		}else{
			WriteErrMsg('出错了！请确保 config.php 文件是可读写属性');	       
		}
		break;
	default:
}
$smarty->display('admin/qc_manage_config.html');
$runtime->stop_write();
?>