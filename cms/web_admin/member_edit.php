<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$CurrentPageName = basename(__FILE__);

$smarty->assign('CurrentPageName', $CurrentPageName);
$action = $_REQUEST['action'];

$language = ($language == '') ? 'cn' : $language;

$id = $_REQUEST['id'];


if ($action == 'update') {

	$class_id = $_REQUEST['class_id'];
	$num = count($class_id);
	if ($num > 0) {
		$typeof = implode(",", $_REQUEST['class_id']);
		foreach ($class_id as $k => $v) {
			$start .= ($k + 1 < $num) ? $v . "|" . ($_REQUEST['start_time' . $v] ? $_REQUEST['start_time' . $v] : date('Y-m-d', time())) . "," : $v . "|" . ($_REQUEST['start_time' . $v] ? $_REQUEST['start_time' . $v] : date('Y-m-d', time()));
			$end .= ($k + 1 < $num) ? $v . "|" . ($_REQUEST['end_time' . $v] ? $_REQUEST['end_time' . $v] : date('Y-m-d', time())) . "," : $v . "|" . ($_REQUEST['end_time' . $v] ? $_REQUEST['end_time' . $v] : date('Y-m-d', time()));
		}
	} else {
		to_back('请选择属于哪届');
		return;
	}
	$sql = "update db_member set
			typeof ='" . $typeof . "',
			start_time='" . $start . "',
			end_time='" . $end . "' ,
			is_show = 1
	where id=" . $id;
	if ($db->query($sql)) {
		alert('修改成功');
	}
}
$webclass_array = get_menu_list1(28, true);
$smarty->assign("webclass_array", $webclass_array);

if ($id) {
	$sql = "select * from db_member where id =" . $id;
	$member_data = $db->getone($sql);
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
$smarty->assign("member_data", $member_data);
$smarty->assign("language", $language);

$smarty->display("admin/member_edit.html");
$runtime->stop_write();
?>