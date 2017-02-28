<?php
error_reporting(0); //E_ALL & ~E_NOTICE
if(version_compare(PHP_VERSION, '5.3.0', '<')){
	set_magic_quotes_runtime(0);
}
if(function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get")){
	date_default_timezone_set('Asia/Shanghai'); //ini_set('date.timezone','Asia/Shanghai');
}
session_cache_limiter('private,must-revalidate');
session_start();
//define('WEB_ROOT',substr(dirname(__FILE__),0,-11));
define('WEB_ROOT',preg_replace('/web_include(.*)/i','',str_replace('\\','/',__FILE__)));
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
include_once(WEB_ROOT.'web_include/360_safe3.php');
include_once(WEB_ROOT.'web_include/config.php');
include_once(WEB_ROOT.'web_include/index_config.php');
include_once(WEB_ROOT.'web_include/class.mysql.php');
include_once(WEB_ROOT.'web_include/function.php');
include_once(WEB_ROOT.'web_include/function_menu.php'); 
include_once(WEB_ROOT.'web_include/smarty.php');
$lang = $_GET['lang'];
$lang = (in_array($lang,array('cn','en','jp')))?$lang:'cn';//默认英文版(cn/en只是标记不要纠结) 
//$web_config = unserialize($web_config);
//config从数据库中查询
$web_config=$db->getone("select * from db_config where id=1",MYSQL_ASSOC);//MYSQL_ASSOC只产生关联数组，不传则两则。
web_status($web_config); 

$flag_class_array = array(
	0=>'<span class="f_F5AE03">管理员</span>',
	1=>'<span class="f_9933FF">超级管理员</span>',
	2=>'<span class="f_FF0000">高级管理员</span>',
	3=>'<span class="f_0000FF">普通管理员</span>'
);

$menu_module_array = array(
	0=>'导航',
	1=>'单页模块',
	
);

$language_array = array(
	'cn'=>'中文版', 
	//'en'=>'英文版', 
	//'jp'=>'日文版'
);
 
$Session['Admin']['UserID']     = $_SESSION[$web_config['Web_SessionName'].'_Admin_UserID'];
$Session['Admin']['UserName']   = $_SESSION[$web_config['Web_SessionName'].'_Admin_UserName'];
$Session['Admin']['PassWord']   = $_SESSION[$web_config['Web_SessionName'].'_Admin_PassWord'];
$Session['Admin']['Flag']       = $_SESSION[$web_config['Web_SessionName'].'_Admin_Flag'];
$Session['Admin']['Purview']    = $_SESSION[$web_config['Web_SessionName'].'_Admin_Purview'];
$Session['Admin']['RandomCode'] = $_SESSION[$web_config['Web_SessionName'].'_Admin_RandomCode'];

 
//session
//$Session['Member']['UId']	    = $_SESSION[$web_config['Web_SessionName'].'_member']['id'];
//$Session['Member']['Name']	    = $_SESSION[$web_config['Web_SessionName'].'_member']['name'];
//$Session['Member']['Email']	    = $_SESSION[$web_config['Web_SessionName'].'_member']['email']; 

//cookie
// $Cookie_arr = unserialize($_COOKIE[$web_config['Web_SessionName']."_member"]) ;
// $Session['Member']['UId']	    = $Cookie_arr['id'];
// $Session['Member']['Name']	    = $Cookie_arr['name'];
// $Session['Member']['Email']	    = $Cookie_arr['email'];

$case_classes = $index_config['case_class'];
$case_classes = explode('#',$case_classes);
$smarty->assign('case_classes',$case_classes);

$time_classes = $index_config['time_class'];
$time_classes = explode('#',$time_classes);
$smarty->assign('time_classes',$time_classes);
$smarty->assign(
	array(
		'web'=>$web_config,
		'index'=>$index_config,
		'Session'=>$Session,
		'flag_class_array'=>$flag_class_array,
		'menu_module_array'=>$menu_module_array,
		'language_array'=>$language_array
	)
);


$from_url = urlencode('http://'.$_SERVER['HTTP_HOST'] . request_uri());
$smarty->assign("from_url",$from_url); 
$prior=$_SERVER['HTTP_REFERER'];
$smarty->assign("prior",$prior);
?>
