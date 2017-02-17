<?php
header('content-Type: text/html; charset=utf-8');
include_once('../web_include/init.php');
$act = trim($_GET['act']);
if($act=='wenjuan'){
    $user = $db->getone("select id from db_member where mobile='".$_COOKIE['member_mobile']."'");
    if(!empty($_POST['research_id'])){
        error_log_web('点击回答问卷', 1, 'id为'.$user['id'].'会员进行点击回答问卷'.$_POST['research_id'],'点击成功');
        exit();
    }else{
        error_log_web('点击回答问卷', 2, 'id为'.$user['id'].'会员进行点击回答问卷','未获取到问卷id');
        exit();
    }
}
//************************
	/*************************************基本信息开始***********************************/
	$menu_info1 = array(
		'class_title_cn' => $web_config['Web_SiteTitle_cn'],
		'class_keywords_cn' => $web_config['Web_Keywords_cn'],
		'class_description_cn' => $web_config['Web_Description_cn']
	);  
	$smarty->assign('menu_info1',$menu_info1); 
	$class_id = addslashes(trim($_GET['cid']));
	// $cid=($class_id=='')?get_default_menu_id('3'):get_default_menu_id($class_id);
	$cid=($class_id=='')?'1':$class_id;
	$smarty->assign('first_cid',$cid);   
	
	//公共部分
	include('./common.php');	
	
	//当前会员登录信息
	if(!empty($_COOKIE['member_mobile'])){
	    $user = $db->getone("select id,username,email,integral from db_member where mobile='".$_COOKIE['member_mobile']."'");
	    $smarty->assign('user',$user);
	    //Blowfish加密，key、panelType由Gmo提供
	    $uid=$user['id'];
	    //$uid='2067715';//测试账号有值的会员id
	    $crypt=encrypt($web_config['Web_Blowfish'],$uid.':'.$web_config['Web_panelType'].':'.time());
	    //问卷测试地址
	    $url='https://cn.infopanel.asia/pollon/jp/gmor/research/pollon/enqueteList/facade/EnqueteList.json?panelType='.$web_config['Web_panelType'].'&crypt='.$crypt;
	    $result=httpGet($url);
	    if(!empty($result)){
	        error_log_web('调用问卷列表API', 1, 'id为'.$uid.'会员进行调用问卷列表API','调用成功');
	    }else{
	        error_log_web('调用问卷列表API', 2, 'id为'.$uid.'会员进行调用问卷列表API','返回值为空');
	    }
	    $wenjuan=json_decode($result,true);
	    $smarty->assign('wenjuan',$wenjuan);
	}
	//顶部图片
	$sql="SELECT web1_url_cn,img1_url_cn,web_url_cn,img_url_cn FROM `".$db_pre."table_flash` where class_id=1 and typeof =1 and is_index=1 order by add_time desc limit 1";
	$flash = $db->getone($sql);
	$smarty->assign('flash',$flash);
	//推荐图片
	$sql="SELECT img_url_cn FROM `".$db_pre."table_flash` where class_id=1 and typeof =2 and is_index=1 order by add_time desc limit 1";
	$tuijian = $db->getone($sql);
	$smarty->assign('tuijian',$tuijian);
	//通知信息,在开启时间段内
	$sql="SELECT id,class_id,title_cn,add_time FROM `".$db_pre."pageinfo` WHERE `class_id` ='1' and NOW() between `start_time` and date_add(`end_time`,interval 1 day) order by add_time desc limit 5";
	$news = $db->getall($sql);
	$smarty->assign('news',$news);
	//最新动态，包括获得积分和兑换商品
	$sql="SELECT val,add_time,(SELECT username FROM `".$db_pre."member` AS m WHERE m.id=n.uid) AS username FROM `".$db_pre."integral` n where type=1 and survey_id>0 order by add_time desc";
	$dongtai1=$db->getall($sql);//回答问卷所的积分的动态
	$sql="SELECT type,item_list,add_time,(SELECT username FROM `".$db_pre."member` AS m WHERE m.id=n.uid) AS username FROM `".$db_pre."order` n where type=1 and (status ='6' or status='9') order by add_time desc";
	$dongtai2=$db->getall($sql);//购买大麦城商品的数量动态
	if(!empty($dongtai2)){
	    foreach ($dongtai2 as $k=>$v){
	        $dongtai2[$k]['item_list']=count(unserialize($v['item_list']));
	    }
	}
	$dongtai=array_merge($dongtai1,$dongtai2);//动态合并为一个数组
	if(!empty($dongtai)){
	    foreach ($dongtai as $k=>$v){
	        $datetime[]=$v['add_time'];
	    }
	    array_multisort($datetime,SORT_DESC,$dongtai);//对数组进行时间排序，倒序
	}
	$smarty->assign('dongtai',$dongtai);
	//设置数组长度
    $tebie=range(0,7);
    $smarty->assign('tebie',$tebie);
//************************
$smarty->display('pc/index.html');