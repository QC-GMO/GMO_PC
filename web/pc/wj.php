<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');

$action = daddslashes($_GET['action']);

switch ($action) {
    //接收问卷返回信息，给会员加分
    case 'add':
        $uid=daddslashes($_POST['member_id']);//会员id
        $user = $db->getone("select id,status from db_member where id='".$uid."'");
        if(empty($user)){
            error_log_web('加积分', 2, '会员进行加积分','参数无效，无此普通会员');
            exit('1');
        }//参数无效，无此普通会员
        if($user['status']!=2){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','会员状态不是正常会员');
            exit('2');
        }//会员状态不是正常会员
        $val=daddslashes($_POST['point']);//积分值
        if(!is_int($val+0) || $val<1){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','参数无效，积分值需为正整数');
            exit('1');
        }//参数无效，积分值需为正整数
        $survey_id=daddslashes($_POST['survey_id']);//问卷id
        if(empty($survey_id)){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','参数无效，问卷id为空');
            exit('1');
        }//参数无效，问卷id为空
        $survey_name=daddslashes($_POST['survey_name']);//问卷名称
        if(empty($survey_name)){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','参数无效，问卷名称为空');
            exit('1');
        }//参数无效，问卷名称为空
        $grant_times=daddslashes($_POST['grant_times']);//同一会员同一项目只能存在一个
        if(empty($grant_times)){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','参数无效，grant_times值为空');
            exit('1');
        }//参数无效，grant_times值为空
        if($db->num_rows("SELECT id FROM `".$db_pre."integral` WHERE uid='".$uid."' and survey_id='".$survey_id."' and grant_times='".$grant_times."'")>0){
            error_log_web('加积分', 2, 'id为'.$user['id'].'会员进行加积分','重复回答');
            exit('3');//同一会员同一项目只能存在一个,已有则返回3，重复回答
        }
        $status=daddslashes($_POST['status']);//回答状态，暂只记录，不判断
        //通过验证会员的积分增加
        $sql="UPDATE `".$db_pre."member` SET `integral`=`integral`+'" .$val. "' WHERE id='".$uid."'";
        if($db->query($sql)){//增加积分成功后插入积分变动表并返回信息
            $sql = "INSERT INTO `".$db_pre."integral`(`uid`,`type`,`val`,`source`,`survey_id`,`survey_name`,`grant_times`,`status`,`add_time`)VALUES(
					'".$uid."','1','".$val."','回答问卷','".$survey_id."','".$survey_name."','".$grant_times."','".$status."','".date('Y-m-d H:i:s')."')";
            $db->query($sql);
            error_log_web('加积分', 1, 'id为'.$user['id'].'会员进行加积分','加分成功');
            exit('0');//成功返回0
        }else{
            error_log_web('加积分', 1, 'id为'.$user['id'].'会员进行加积分',mysql_error());
            exit('2');//内部错误
        }
        break;
        //定时生成会员信息文件
    case 'member_list':
        $time=date('Y-m-d H:i:s',time()-86400*30*3);
        $sql = "SELECT * FROM `" . $db_pre . "member` WHERE status=2 and lastlogin_time>'".$time."'";
        $list=$db->getall($sql);
        $str='MONITOR_ID,EMAIL,EMAIL_MD5,SEX_CD,AGE,BIRTHDAY,PREF_CD,ZIP_CD1,ZIP_CD2,MARRIAGE,INDUSTRY_TYPE_CD,JOB_CD,C_PHONE_COMP,DRIVER_LICENSE,HOUSE_OWNER,ENTRY_DATE,LAST_LOGIN_DATE,ACTIVE_FLAG'."\n";
        
        if(!empty($list)){
            foreach ($list as $k=>$v){
                $age=ceil((time()-strtotime($v['birthday']))/31536000);//年龄，向上取整
                $str.=$v['id'].','.encrypt($web_config['Web_Blowfish'],$v['email']).','.md5($v['email']).',';
                $str.=$v['sex'].','.$age.','.date('Ymd',strtotime($v['birthday'])).','.$v['city_id'].',,,,,,,,,'.date('Ymd',strtotime($v['add_time'])).','.date('Ymd',strtotime($v['lastlogin_time'])).',01'."\n";
            }
        }
        //iconv("gb2312","utf-8//IGNORE",)
        $filename='memberList.csv';
        
        $handle=fopen($filename,"w+");
        
        $str=fwrite($handle,$str);
        
        fclose($handle);
        
        $zip=new ZipArchive();
        $filename_zip = "memberList.csv.zip";
        if ($zip->open($filename_zip, ZIPARCHIVE::CREATE)!==TRUE) {
            exit("cannot open <$filename_zip>\n");
        }
        $zip->addFile($filename);
        $zip->close();
        break;
        
}





