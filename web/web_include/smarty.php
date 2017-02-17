<?php
include_once(WEB_ROOT."web_smarty/libs/Smarty.class.php");

$smarty = new Smarty; //设置各个目录的路径，这里是安装的重点
$smarty->caching         = false; //smarty模板高速缓存的功能
$smarty->template_dir    = WEB_ROOT.'web_smarty/templates/';
$smarty->compile_dir     = WEB_ROOT.'web_smarty/templates_c/';
$smarty->config_dir      = WEB_ROOT.'web_smarty/config/';
$smarty->cache_dir       = WEB_ROOT.'web_smarty/cache/';
$smarty->left_delimiter  = '{';
$smarty->right_delimiter = '}';
?>