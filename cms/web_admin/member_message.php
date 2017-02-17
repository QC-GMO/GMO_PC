<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
include_once('../country.php');
$CurrentPageName = basename(__FILE__);

$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];
$language = ($language == '') ? 'cn' : $language;
$id = $_REQUEST['id'];

$webclass_array = get_menu_list1(28, true);
$smarty->assign("webclass_array", $webclass_array);

$sql = "select * from " . $db_pre . "dict_position where status = 1 order by sort ";
if ($db->num_rows($sql) > 0) {
    $position_list = $db->getAll($sql);
    $smarty->assign('position_list', $position_list);
}

if ($id) {
    $start = strlen($max) > 4 ? 9 - strlen($max) : 5;
    $end = strlen($max) > 4 ? strlen($max) : 4;
    $member_code = "SPS" . date("Y", time()) . substr(strval($_COOKIE['member_id'] + 100000000), $start, $end);
    $smarty->assign('member_code', $member_code);
    $year = array();
    for ($i = date("Y", time()); $i >= date("Y", time()) - 100; $i--) {
        $year[] = $i;
    }
    $smarty->assign('country_config', $country_config);
    $smarty->assign('year', $year);
    $sql = "select * from db_member where id =" . $id;
    $member_data = $db->getone($sql);
    $member_data['birthday'] = $member_data['birthday'] ? explode("-", $member_data['birthday']) : '';
    if ($member_data['typeof']) {
        $member_data['typeof'] = explode(',', $member_data['typeof']);
    }
    if ($member_data['start_time']) {
        $member_data['start_time'] = explode(',', $member_data['start_time']);
        foreach ($member_data['start_time'] as $ks => $vs) {
            $k_v = explode('|', $vs);
            $member_data['start_time'][$k_v[0]] = $k_v[1];
            unset($member_data['start_time'][$ks]);
        }
    }
    if ($member_data['end_time']) {
        $member_data['end_time'] = explode(',', $member_data['end_time']);
        foreach ($member_data['end_time'] as $k => $v) {
            $k_v = explode('|', $v);
            $member_data['end_time'][$k_v[0]] = $k_v[1];
            unset($member_data['end_time'][$k]);
        }
    }
}

$smarty->assign("id", $id);
$smarty->assign("list", $member_data);
$smarty->assign("language", $language);

$smarty->display("admin/member05.html");
$runtime->stop_write();
?>