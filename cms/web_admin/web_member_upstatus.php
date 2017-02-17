<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);
Authentication_Admin();
Authentication_Purview();
$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];

switch ($action) {
	    //批量修改会员状态
	case 'status':
	    if ( isset( $_FILES['status'] )) {
	        if($_FILES['status']['size'] > 1024000*2){
	            error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','单个文件不能大于2M');
	            WriteErrMsg('单个文件不能大于2M');
	        }
	        if ($_FILES['status']['tmp_name']!=''){
	            $file=upload_Excel($_FILES['status']['tmp_name'], $_FILES['status']['name'], "files");
	        }else{
	            error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','请选择文件');
	            WriteErrMsg('请选择文件');
	        }
	        if(empty($file)){
	            error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','文件格式错误');
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
	        error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','文件编码不是utf8无bom');
	        WriteErrMsg('文件编码不是utf8无bom');
	    }
	    $files = fopen($file,'r');
	    while ($data = fgetcsv($files)) { //每次读取CSV里面的一行内容
	        //print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
	        $exceArray[] = $data;
	    }
	    fclose($files);
	    if(count($exceArray)<2 || empty($exceArray)){
	        error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','未查到文件内ID信息');
	        WriteErrMsg('未查到文件内ID信息');
	    }
	    //检查文件格式是否错误
	    foreach($exceArray as $k=>$v){
	        if($k==0){continue;}
	        //检查id合乎规范
	        if(empty($v[0]) && empty($v[1])){
	            continue;
	        }
	        if(!empty($v[0]) && empty($v[1])){
	            $info[] = '第'.($k+1).'行'.'：注销理由为空';
	        }
	        if(empty($v[0]) && !empty($v[1])){
	            $info[] = '第'.($k+1).'行'.'：用户ID'.$v[0].'为空';
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
	    }
	    if(!empty($info)){//显示文件内格式错误信息
	        foreach($info as $k=>$v){$str2.=$v.'<br>';}
	        error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','信息不符合规则');
	        WriteErrMsg($str2);
	    }
	    //更改会员状态值
	    $i=0;
	    foreach($exceArray as $k=>$v){
	        if($k==0){continue;}
	        if(empty($v[0])){continue;};
	        $user = $db->getone("select integral from db_member where id='".$v[0]."'");
	        $sql="SELECT type,number FROM `".$db_pre."order` WHERE uid=".$v[0]." and status=1";
	        $order=$db->getall($sql);
	        if(!empty($order)){//有申请中的订单时
	            foreach ($order as $v){
	                if($v['type']==1){//类型是商品的订单拼订单号
	                    $number_list[]=$v['number'];
	                }
	            }
	            //请求大麦城，同步订单状态，大麦城申请后退会为3
	            if(!empty($number_list)){//有商品订单时
	                $number_list = implode(",",$number_list);
	                $url="http://www.damaicheng.com/index.php?r=orderapi/MakeStatus&ids={$number_list}&kehuid=40&status=3&sign=".strtoupper(md5('131c2465ff870b9eab7c0bca38992041ids='.$number_list.'&kehuid=40&status=3'));
	                $result=httpGet($url);
	                $result=json_decode($result,true);
	                if($result['status']==0){
	                    $info[] = 'id为'.$v[0].'的会员的申请状态更新失败';
	                }
	                if(!empty($info)){//显示文件内格式错误信息
	                    foreach($info as $k=>$v){$str2.=$v.'<br>';}
	                    error_log_admin('集中强制注销',2,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销',$str2);
	                    WriteErrMsg($str2);
	                }
	            }
	            //本地订单状态修改
	            $sql="UPDATE `".$db_pre."order` SET `status`=8 WHERE uid=".$v[0]." and status=1";
	            $db->query($sql);
	        }
	        //退会成功后积分清零，
	        $sql="UPDATE `".$db_pre."member` SET `status`='4',`integral`=0,`reason`='".$v[1]."' WHERE id='".$v[0]."'";
	        if($db->query($sql)){//会员状态更改成功
	            //插入积分变动表
	            $sql = "INSERT INTO `".$db_pre."integral` (`uid`,`type`,`val`,`source`,`add_time`)VALUES(
					'".$v[0]."','2','".$user['integral']."','强制注销','".date('Y-m-d H:i:s')."')";
	            $db->query($sql);
	            $i++;//会员表中积分更新成功
	        }else{
	            $info[] = '第'.($k+1).'行'.'：会员状态更新失败';
	            //WriteErrMsg(mysql_error());
	        }
	    }
	    //插入数据库的结果信息
	    $str2='共'.$i.'条数据更新成功<br/>';
	    if(!empty($info)){//显示文件内格式错误信息
	        foreach($info as $k=>$v){$str2.=$v.'<br>';}
	        error_log_admin('集中强制注销',3,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销',$str2);
	        WriteErrMsg($str2);
	    }else{
	        error_log_admin('集中强制注销',1,'管理员'.$Session['Admin']['UserName'].'进行集中强制注销','更新成功');
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
$smarty->display("admin/web_member_upstatus.html");
$runtime->stop_write();
?>