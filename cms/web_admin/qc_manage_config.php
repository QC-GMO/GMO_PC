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
	    
	    $sql = "UPDATE `".$db_pre."config` SET
					`Web_SiteName`='".$Web_SiteName."',
					`Web_Version`='".$Web_Version."',
					`Web_Admin_Path`='".$Web_Admin_Path."',
					`Web_SiteTitle_cn`='".ihtmlspecialchars($_POST['Web_SiteTitle_cn'])."',
					`Web_Keywords_cn`='".ihtmlspecialchars($_POST['Web_Keywords_cn'])."',
					`Web_Description_cn`='".ihtmlspecialchars($_POST['Web_Description_cn'])."',
					`Web_SiteTitle_en`='".ihtmlspecialchars($_POST['Web_SiteTitle_en'])."',
					`Web_Keywords_en`='".ihtmlspecialchars($_POST['Web_Keywords_en'])."',
					`Web_Description_en`='".ihtmlspecialchars($_POST['Web_Description_en'])."',
					`Web_SiteTitle_jp`='".ihtmlspecialchars($_POST['Web_SiteTitle_jp'])."',
					`Web_Keywords_jp`='".ihtmlspecialchars($_POST['Web_Keywords_jp'])."',
					`Web_Description_jp`='".ihtmlspecialchars($_POST['Web_Description_jp'])."',
					`Web_ICP`='".ihtmlspecialchars($_POST['Web_ICP'])."',
					`Web_PageSize`='".ihtmlspecialchars($_POST['Web_PageSize'])."',
					`Web_CheckCode`='".ihtmlspecialchars($_POST['Web_CheckCode'])."',
					`Web_Login_Num`='".ihtmlspecialchars($_POST['Web_Login_Num'])."',
					`Web_SessionName`='".ihtmlspecialchars($_POST['Web_SessionName'])."',
					`Web_Smtp`='".ihtmlspecialchars($_POST['Web_Smtp'])."',
					`Web_Smtp_ID`='".ihtmlspecialchars($_POST['Web_Smtp_ID'])."',
					`Web_Smtp_PW`='".ihtmlspecialchars($_POST['Web_Smtp_PW'])."',
					`Web_admin_url`='".ihtmlspecialchars($_POST['Web_admin_url'])."',
					`Company_Fax`='".ihtmlspecialchars($_POST['Company_Fax'])."'    
				WHERE id='1'";
	    if ($db->query($sql)){
	        WriteSuccessMsg('信息更新成功',$CurrentPageName);
	    }
	    else{
	        WriteErrMsg(mysql_error());
	    }
		break;
	default:
}
$smarty->display('admin/qc_manage_config.html');
$runtime->stop_write();
?>