<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
//短信获取验证码
require_once '../sms/include/Client.php';
$action=daddslashes($_REQUEST['act']);

switch ($action) {
    //注册获取手机验证码
    case 'getcode':
        $mobile = trim($_POST['mobile']);
        if(empty($mobile)){
            ajaxReturn(0,'手机号码不能为空');
        }
        if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$mobile."'")>0){
            ajaxReturn(0,'该手机号码已注册过');
        }
        $code = Code();
        $content = '【最网e调查】欢迎注册Z.com Research会员！验证码:'.$code.'，请在15分钟内完成注册。';
        
        doSend( $mobile,$content );
        break;
        //重置密码获取手机验证码
   case 'forgetcode':
       $mobile = trim($_POST['mobile']);
       if(empty($mobile)){
           ajaxReturn(0,'手机号码不能为空');
       }
       if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$mobile."'")==0){
           ajaxReturn(0,'该手机号码未注册');
       }
       $code = Code();
       setcookie('forget_mobile',$mobile,time()+900,'/');//短信验证15分有效期
       $content = '【最网e调查】您好！找回密码操作验证码:'.$code.'，15分钟内有效，切勿泄露于他人。';
       doSend( $mobile,$content );
       break;
   //更换手机获取手机验证码
   case 'upmobile':
       $mobile = trim($_POST['mobile']);
       if(empty($mobile)){
           ajaxReturn(0,'手机号码不能为空');
       }
       if($db->num_rows("SELECT id FROM `".$db_pre."member` WHERE `mobile`='".$mobile."'")>0){
           ajaxReturn(0,'该手机号码已注册');
       }
       $code = Code();
       $content = '【最网e调查】您好！手机号更换操作验证码：'.$code.'，15分钟内有效，切勿泄露于他人。';
       doSend( $mobile,$content );
       break;
}
//生成code
function Code(){
    $str = '9876543210';
    for($i=0;$i<6;$i++){
        $code.= $str[mt_rand(0, 9)];
    }
    setcookie('SMSCode',$code,time()+900,'/');//短信验证15分有效期
    return $code;
}

//发送短信
function doSend( $mobile,$content ){

    $client = new Client();
    $client->setOutgoingEncoding("UTF-8");
    $statusCode = $client->sendSMS(array($mobile),$content);
    if ($statusCode!=null && $statusCode==0){
        ajaxReturn(1,'发送成功');
        //echo '发送成功';
    }else{
        ajaxReturn(0,'发送失败,请重试');
        //echo "处理状态码:".$statusCode;
    }
}