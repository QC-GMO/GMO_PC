<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$action=$_GET['action'];
$title=$_POST['title'];
$type=$_POST['type'];
$parameter=$_POST['parameter'];
$reason=$_POST['reason'];
$userip=$_POST['userip'];

switch ($action){
    case 'error_log_web':
        error_log_admin($title,$type,$parameter,$reason,$userip);
        break;
}