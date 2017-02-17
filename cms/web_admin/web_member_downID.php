<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];

switch ($action) {
	    //下载所上传ID会员信息
	case 'downID':
	    if ( isset( $_FILES['down'] )) {
	        if($_FILES['down']['size'] > 1024000*2){
	            error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','单个文件不能大于2M');
	            WriteErrMsg('单个文件不能大于2M');
	        }
	        if ($_FILES['down']['tmp_name']!=''){
	            $file=upload_Excel($_FILES['down']['tmp_name'], $_FILES['down']['name'], "files");
	        }else{
	            error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','请选择文件');
	            WriteErrMsg('请选择文件');
	        }
	        if(empty($file)){
	            error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','文件格式错误');
	            WriteErrMsg('文件格式错误');
	        }
	        //$exceArray = readExcel($file);//include 'Classes/PHPExcel.php';已引入
	    }
	    //检测文件编码是否为UTF-8无bom
	    $str = file_get_contents($file);
	    $charset[1] = substr($str, 0, 1);
	    $charset[2] = substr($str, 1, 1);
	    $charset[3] = substr($str, 2, 1);
	    if((ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) || mb_detect_encoding($str)!='UTF-8'){
	        error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','文件编码不是utf8无bom');
	        WriteErrMsg('文件编码不是utf8无bom');
	    }
	    $files = fopen($file,'r');
	    while ($data = fgetcsv($files)) { //每次读取CSV里面的一行内容
	        //print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
	        $exceArray[] = $data;
	    }
	    fclose($files);
	    if(count($exceArray)<2 || empty($exceArray)){
	        error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','未查到文件内ID信息');
	        WriteErrMsg('未查到文件内ID信息');
	    }
	    $str='';
	    foreach($exceArray as $k=>$v){
	        if($k==0){continue;}
	        if( empty( $v[0]) ){
	            continue;
	        }else{
	            if(!is_numeric($v[0])||strpos($v[0],".")!==false){
	                $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'不是整数';continue;
	            }
	            if(!($v[0]>0)){
	                $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'小于或等于0';continue;
	            }
	            //检查该id会员是否存在
	            if ($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE id=".$v[0]."") == 0) {
	                $info[] = '第'.($k+1).'行'.'：id为'.$v[0].'的用户信息不存在';continue;
	            }
	        }
	        $str.=$v[0].',';
	    }
	    if(!empty($info)){
	        foreach($info as $k=>$v){$str2.=$v.'<br>';}
	        error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','信息不符合规则');
	        WriteErrMsg($str2);
	    }
	    $str=substr($str, 0, -1);
	    $sql = "SELECT * FROM `" . $db_pre . "member` WHERE id in (".$str.") order by add_time desc";
	    $list=$db->getall($sql);
	    if(empty($list)){
	        error_log_admin('下载所上传ID会员信息',2,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','当前结果为空');
	        WriteErrMsg('当前结果为空');
	    }
	    $str='用户ID,用户名,手机号码,email,性别(1男，2女),生日,省,市,详细地址,当前积分,会员来源(1自主推广，2外部推广，3会员推广),来源code,会员状态(1认证中，2普通会员，3自己注销，4强制注销),注销理由,最后登录时间,会员更新时间,后台更新时间,注册时间'."\n";
	    foreach( $list as $row ){
	        $str.=$row['id'].','.$row['username'].','.$row['mobile'].','.$row['email'].','.$row['sex'].','.$row['birthday'].','.$row['province'].','.$row['city'].','.$row['address'].',';
	        $str.=$row['integral'].','.$row['source_type'].','.$row['web_code'].','.$row['status'].','.$row['reason'].','.$row['lastlogin_time'].','.$row['own_up'].','.$row['admin_up'].','.$row['add_time']."\n";
	    }
	    if(file_exists('files/memberforid.csv')){
	        @unlink('files/memberforid.csv');
	    }
	    $filename='files/memberforid.csv';
	    
	    $handle=fopen($filename,"w+");
	    
	    $str=fwrite($handle,$str);
	    
	    fclose($handle);
	    if($str){
	        error_log_admin('下载所上传ID会员信息',1,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','会员信息写入成功');
	    }else{
	        error_log_admin('下载所上传ID会员信息',3,'管理员'.$Session['Admin']['UserName'].'进行下载所上传ID会员信息','会员信息写入失败');
	    }
	    header("Location:".$filename);
    	exit();
	    break;
	default:
	    $smarty->assign(
	        array(
	            "add_time" => date("Y-m-d H:i:s"),
	        )
	    );
}
$smarty->display("admin/web_member_downID.html");
$runtime->stop_write();
?>