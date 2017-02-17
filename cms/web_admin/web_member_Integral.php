<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];

switch ($action) {
	    //分配积分
	case 'Integral':
	    if ( isset( $_FILES['Integral'] )) {
	        if($_FILES['Integral']['size'] > 1024000*2){
	            error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','单个文件不能大于2M');
	            WriteErrMsg('单个文件不能大于2M');
	        }
	        if ($_FILES['Integral']['tmp_name']!=''){
	            $file=upload_Excel($_FILES['Integral']['tmp_name'], $_FILES['Integral']['name'], "files");
	        }else{
	            error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','请选择文件');
	            WriteErrMsg('请选择文件');
	        }
	        if(empty($file)){
	            error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','文件格式错误');
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
	        error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','文件编码不是utf8无bom');
	        WriteErrMsg('文件编码不是utf8无bom');
	    }
	    $files = fopen($file,'r');
	    while ($data = fgetcsv($files)) { //每次读取CSV里面的一行内容
	        //print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
	        $exceArray[] = $data;
	    }
	    fclose($files);
	    if(count($exceArray)<2 || empty($exceArray)){
	        error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','未查到文件内ID信息');
	        WriteErrMsg('未查到文件内ID信息');
	    }
	    //检查文件格式是否错误
	    foreach($exceArray as $k=>$v){
	        if($k==0){continue;}
	        //检查id合乎规范
	        if( empty($v[0]) ){
	            if(!empty($v[1])){
	                $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'为空';
	            }else{
	                continue;
	            }
	        }
            if(!is_numeric($v[0])||strpos($v[0],".")!==false){
                $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'不是整数';
            }
            if(!($v[0]>0)){
                $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'小于或等于0';
            }
            //检查该id会员是否存在
            if ($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE id=".$v[0]."") == 0) {
                $info[] = '第'.($k+1).'行'.'：id为'.$v[0].'的用户信息不存在';
            }
            //检查积分值合乎规范
            if(!is_numeric($v[1])||strpos($v[1],".")!==false){
                $info[] = '第'.($k+1).'行'.'：积分值'.$v[1].'不是整数';
            }
            if($v[1]==0){
                $info[] = '第'.($k+1).'行'.'：积分值'.$v[1].'等于0';
            }
	    }
	    if(!empty($info)){//显示文件内格式错误信息
	        foreach($info as $k=>$v){$str2.=$v.'<br>';}
	        error_log_admin('分配积分',2,'管理员'.$Session['Admin']['UserName'].'进行分配积分','信息不符合规则');
	        WriteErrMsg($str2);
	    }
	    //将积分值加到会员表中
	    $i=0;
	    foreach($exceArray as $k=>$v){
	        if($k==0){continue;}
	        if(empty($v[0])){continue;};
	        $sql="UPDATE `".$db_pre."member` SET `integral`=`integral`+'" .$v[1]. "' WHERE id='".$v[0]."'";
	        if($db->query($sql)){//增加积分值成功后插入积分变动历史
	            $sql = "INSERT INTO `".$db_pre."integral`(`uid`,`type`,`val`,`source`,`add_time`)VALUES(
					'".$v[0]."','".(($v[1]>0)?1:2)."','".abs($v[1])."','".$v[2]."','".date('Y-m-d H:i:s')."')";
                $db->query($sql);
	            $i++;//会员表中积分更新成功
	        }else{
	            $info[] = '第'.($k+1).'行'.'：积分值分配失败';
	            //WriteErrMsg(mysql_error());
	        }
	    }
	    //插入数据库的结果信息
	    $str2='共'.$i.'条数据分配成功<br/>';
	    if(!empty($info)){//显示文件内格式错误信息
	        foreach($info as $k=>$v){$str2.=$v.'<br>';}
	        error_log_admin('分配积分',3,'管理员'.$Session['Admin']['UserName'].'进行分配积分',$str2);
	        WriteErrMsg($str2);
	    }else{
	        error_log_admin('分配积分',1,'管理员'.$Session['Admin']['UserName'].'进行分配积分','分配成功');
	    }
	    WriteSuccessMsg($str2, $CurrentPageName);
	    break;
	default:
	    $smarty->assign(
	        array(
	            "add_time" => date("Y-m-d H:i:s"),
	        )
	    );
}
$smarty->display("admin/web_member_Integral.html");
$runtime->stop_write();
?>