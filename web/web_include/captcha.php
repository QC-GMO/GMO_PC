<?php
session_start();
header("Content-type:image/png"); 
include('class.captcha.php');//验证码类文件请下载captcha_class.php
$code_obj = new imageCaptcha();
 
/** 用于调整显示样式,注释则使用默认样式
*    set_show_mode($w,  $h,  $num,  $fc,  $fz,  $ff_url,  $lang,  $bc,  $m,  $n,  $b,  $border);
*    $w验证码宽度;
*    $验证码高度;
*    $num验证码位数;
*    $fc字符颜色;
*    $fz字符大小;
*    $ff_url字体存放路径;
*    $lang定义字符语言'en'或'cn';
*    $bc背景颜色;
*    $m干扰点个数;
*    $n干扰线条数; 
*    $b是否扭曲字符,TRUE或FALSE;
*    $border是否有边框,TRUE或FALSE; 
*/ 
$code_obj->set_show_mode('130',  '50',  '5',  '#2F596F',  '18',  'fonts/verdana.ttf',  'en',  '#F8F8F8',  '100',  '3',  TRUE,  FALSE);

$code = $code_obj->createImage();

$_SESSION['imageCaptcha_content'] = strtolower($code);//获取验证码的值并转换成小写存到session中;
$_SESSION['imageCaptcha_time'] = time();//获取验证码输出时间并存到session中;
?>