$(function(){ //控制状态栏，链接去虚框，屏蔽右键，防复制
	$("a").focus(function(){this.blur()})
	//$(document).bind('contextmenu',function(){return false;});
	//$(document).bind('selectstart',function(){return false;});
//************************************************************
	if(!$.browser.safari){ //登录框垂直居中
		$("#login").css("margin-top",($(window).height()/2 - $("#login").outerHeight()/2)+'px');
	}
//************************************************************
	$("#Login").submit(function(){
		if($('#UserName').val()==""){
			jAlert('请输入用户名。', '错 误 提 醒', function(){$('#UserName').focus();});
			return false;
		}
		if($('#PassWord').val()==""){
			jAlert('请输入密码。', '错 误 提 醒', function(){$('#PassWord').focus();});
			return false;
		}
		if($('#CheckCode').val()==""){
			jAlert('请输入您的验证码。', '错 误 提 醒', function(){$('#CheckCode').focus();});
			return false;
		}
	});
//************************************************************
	$('.del').click(function(){ // 删除 退出 提示
		if(confirm('确定要删除该信息吗？一旦删除将不能恢复！')){
			return true;
		} else {
			return false;
		}
	});
	$('.quit').click(function(){
		if(confirm('确认要退出吗？')){
			top.location.href='qc_login.php?action=logout';
		} else {
			return false;
		}
	});
	$('.confirm').click(function(){
		if(confirm('确定要这样操作吗？')){
			return true;
		} else {
			return false;
		}
	});	
//************************************************************
	var status = 1; //左侧菜单 隐藏/显示
	$("#switchPoint").click(function(){
		$(window.parent.document).find("#frm_left").animate({opacity: 'toggle'}, "fast",function(){
			if (status++ % 2==0){
				$("#switchPoint img").attr("alt","隐藏左侧菜单").attr("src","images/shrink_left.gif");
			}
			else{
				$("#switchPoint img").attr("alt","显示左侧菜单").attr("src","images/shrink_right.gif");
			}
 		});
	});
//************************************************************
	$(".left_menu_title") //左侧菜单子菜单 显示/隐藏
	/*--.hover(
		function(){$(this).addClass('left_menu_title_over');},
		function(){$(this).removeClass('left_menu_title_over');}
	)---*/
	.click(
		function(){
			$(this).toggleClass('left_menu_title_over');
			$(this).next().slideToggle("fast");
			$(this).siblings('.left_menu_title').next('.left_menu_content').slideUp()
			$(this).siblings('.left_menu_title').removeClass('left_menu_title_over');
		}
	);
//************************************************************
	$(".left_menu_content ul li a").attr("target","frmright"); //左侧菜单子菜单 点击后状态
	$(".left_menu_content ul li a").each(function($i){
		$(this).click(
			function(){
 				$(this).addClass("left_over");
				$(".left_menu_content ul li a:gt("+$i+")").removeClass("left_over");
				$(".left_menu_content ul li a:lt("+$i+")").removeClass("left_over");
			}
		)
	});
//************************************************************
	/*--$('.btn').each(function(){ //按钮背景
		$(this).hover(
			function(){$(this).addClass('getfocus').removeClass('lostfocus');},
			function(){$(this).addClass('lostfocus').removeClass('getfocus');}
		)
	});--*/
	$('.back').click(function(){history.back();}); //返回
//************************************************************
	$('#checkboxall').click(function(){ //控制全选/取消全选
		$('input[name="id[]"], input[name="class_id[]"]')
		.attr('disabled',false)
		.attr('checked',this.checked)
		.click(function(){$('#checkboxall').attr('checked',false);})
		.each(function($i){
			(this.checked)?$('.tr_check:eq('+$i+')').addClass('tr_over').removeClass('tr_out'):$('.tr_check:eq('+$i+')').addClass('tr_out').removeClass('tr_over');
		});
	});
//************************************************************
	$('.boxs').each(function($i){ //权限选择
		$(this).click(function(){
			$('.box_'+$i).attr('disabled',(this.checked)?false:true);
		})
		$('.box_'+$i).click(function(){
			$(this).attr('checked')?$('.boxs:eq('+$i+')').attr('checked',true):'';
		});
	});
//************************************************************
	$('.tr_check').each(function($i){ //控制tr td背景
		$(this).hover(
			function(){
				$('input[name="id[]"]:eq('+$i+')').attr('checked')?'':$(this).addClass('tr_over').removeClass('tr_out');
			},
			function(){
				$('input[name="id[]"]:eq('+$i+')').attr('checked')?'':$(this).addClass('tr_out').removeClass('tr_over');
			}
		)
	});
	
	if ($('#operationclass').length > 0) {
		$('#operationclass').change(function(){
			if ($(this).val()=='batch_move') {
				$('#new_class_id').show();
			} else {
				$('#new_class_id').hide();
			}
		});
	}
//************************************************************
	var today=new Date(); //日历
	var h=checkTime(today.getHours());
	var m=checkTime(today.getMinutes());
	var s=checkTime(today.getSeconds());	
	var dateConfig = {
		yearRange: '-10:+10', //'-20:+20'前后20年   
		//yearRange: '1950:' + new Date().getFullYear(), //'-20:+20'前后20年
		//dayNamesMin: ['日','一','二','三','四','五','六'],
		//changeFirstDay: false,        //这个参数干什么的呢，星期一是日历的第一个，不可以改动的
		//numberOfMonths: 2,            //显示二个月，默认一个月   
		monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'],
		dateFormat: 'yy-mm-dd '+h+":"+m+":"+s,  //日期格式，自己设置 
		buttonImage: 'images/calendar.gif',  //按钮的图片路径，自己设置 
		buttonText: '选择时间',
		buttonImageOnly: true, //显示图片的地方有一个突出部分，这个就是隐藏那玩意的
		showOn: 'focus', //触发条件，both表示点击文本域和图片按钮都生效
		changeYear: true,
		changeMonth: true
	};
	var dateConfig2 = {
			yearRange: '-60:+60', //'-20:+20'前后20年    
			monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'],
			dateFormat: 'yy-mm-dd '+h+":"+m+":"+s,  //日期格式，自己设置 
			buttonImage: 'images/calendar.gif',  //按钮的图片路径，自己设置 
			buttonText: '选择时间',
			buttonImageOnly: true, //显示图片的地方有一个突出部分，这个就是隐藏那玩意的
			showOn: 'focus', //触发条件，both表示点击文本域和图片按钮都生效
			changeYear: true,
			changeMonth: true
		};
	if ($('#add_time').length>0){
		$('#add_time').datepicker(dateConfig);	
	}
	
	if ($('#starttime').length>0){
		$('#starttime').datepicker(dateConfig);	
	}
	
	if ($('#endtime').length>0){
		$('#endtime').datepicker(dateConfig);
	}
	
	if($("#birthday").length > 0){
		$("#birthday").datepicker(dateConfig2);
	}
//************************************************************
	$('.reorder').click( // 排序提醒
		function(){
			var $_local = '?action=reorder&language='+$(this).attr('name');
			jConfirm('确定要这样操作吗？当您数据过多时该操作将耗用大量系统资源！','操 作 提 示',function(result) {
				if(result){window.location.href = $_local;}
			});
		}
	);
});

$(function(){
	$('#is_manual').click(function(){
		if ($(this).attr('checked')) {
			$('#file_url').attr('disabled', true)
			$('#file_manual_url').attr('disabled', false)
		} else {
			$('#file_url').attr('disabled', false)
			$('#file_manual_url').attr('disabled', true)
		}
	});	
}); 
/*
 * 删除图片
 * 表名-字段名-id号-影藏的对象-是否是导航栏目
 * */
function pic_del($name, $field, $id, $obj,$type){
//	$type= arguments[5] || false;  //判断第五个参数是否为空； 
	if($type=='') $type='false'
	jConfirm('确定要删除吗？','操 作 提 示',
		function(result) {
			if(result){
				$.post('qc_jquery.php',{'action':'pic_del', 'name':$name, 'field':$field, 'id':$id,'type':$type}, function(data){
					switch (data.status) {
						case 'success':
							jAlert(data.message, '操 作 提 示');
							$('#'+$obj).hide();
							break;
						case 'failure':
							jAlert(data.message, '错 误 提 示');
							break;
						default:
					}
				},"json");
			}
		}
	);
}
function mini_pic_del($name, $id, $obj,$type){ 
	if($type=='') $type='false'
	jConfirm('确定要删除吗？','操 作 提 示',
		function(result) {
			if(result){
				$.post('qc_jquery.php',{'action':'mini_pic_del', 'name':$name, 'id':$id,'type':$type}, function(data){
					switch (data.status) {
						case 'success':
							jAlert(data.message, '操 作 提 示');
							$('#'+$obj).hide();
							break;
						case 'failure':
							jAlert(data.message, '错 误 提 示');
							break;
						default:
					}
				},"json");
			}
		}
	);
}
//------------------------删除图片--------------------------------
function checkTime(i){ //时间补零
	if (i < 10){i = '0' + i}; return i;
}
//************************************************************
$(function(){ //图片预览
	img_view();
});
function img_view(){
	$('<div class="div_preview"></div>').appendTo('body');
	img_w = 200;
	img_h = 200;
	$('.pic_view').addClass('cursor_p').each(function(){
		$(this)
		.mousemove(
			function(){
				var ImgD = new Image(); ImgD.src = $(this).attr('alt'); DrawImage(ImgD,img_w,img_h);
				$('.div_preview')
				.html('<img src="' + $(this).attr('alt') + '" width="' + ImgD.width + '" height="' + ImgD.height + '" />')
				.css('top',$(document).scrollTop() + window.event.clientY - (ImgD.height/2) - 10 + 'px')
				.css('left',$(document).scrollLeft() + window.event.clientX + 20 + 'px')
				.css('display', 'block');
			}
		)
		.mouseout(function(){$('.div_preview').css('display', 'none')})
		.click(function(){window.open($(this).attr('alt'))});
	});
}
//************************************************************
function DrawImage(ImgD,ImgDWidth,ImgDHeight){ //等比例缩放
	var image = new Image();
	image.src = ImgD.src;
	if(image.width/image.height >= ImgDWidth/ImgDHeight){
		if(image.width>ImgDWidth){
			ImgD.width=ImgDWidth;
			ImgD.height=(image.height*ImgDWidth)/image.width;
		}
		else{
			ImgD.width=image.width;
			ImgD.height=image.height;
		}
	}
	else{
		if(image.height>ImgDHeight){
			ImgD.width=(image.width*ImgDHeight)/image.height;
			ImgD.height=ImgDHeight;
		}
		else{
			ImgD.width=image.width;
			ImgD.height=image.height;
		}
	}
}
//************************************************************
$(function(){
	$(".title_list li").each(function($i){
		$(this).click(
			function(){
				$(this).addClass("over");
				$(".title_list li:lt("+$i+")").removeClass("over");
				$(".title_list li:gt("+$i+")").removeClass("over");
				$(".content_list").hide();
				$(".list_"+$i).show();
			}
		)
	})
	$(".content_list").hide();
	$(".list_0").show();
});
//*************************************************************************
function addname(used){
	var thisfield="";
	var tishi=prompt("格式：显示内容,值\n注意：逗号必须在英文状态下输入并且左右不能有空格",thisfield);
	if(tishi!=null&&tishi!=""){
		if(tishi.indexOf(",")<0){tishi=tishi+","+tishi;};
		used.options[used.length]=new Option(tishi,tishi);
	}
}
function modifyname(used){
	if(used.length==0) return false;
	var thisfield=used.value;
	if(thisfield==""){alert("请先选择一个字段内容，再点修改按钮！");return false;}
	var tishi=prompt("格式：显示内容,值\n注意：逗号必须在英文状态下输入并且左右不能有空格",thisfield);
	if(tishi!=thisfield&&tishi!=null&&tishi!=""){
		if(tishi.indexOf(",")<0){tishi=tishi+","+tishi;};
		used.options[used.selectedIndex]=new Option(tishi,tishi);
	}
}
function delname(used){
	if(used.length==0) return false;
	var thisfield=used.value;
	if(thisfield==""){alert("请先选择一个字段内容，再点删除按钮！");return false;}
	used.options[used.selectedIndex]=null;
}
function fieldcheck(){
	if(!$("#title").val()){
		alert("字段名称不能为空！");
		$("#title").focus();
		return false;
	}
	if($("#content option").length==0){
		alert("字段内容不能为空！");
		return false;
	}
	$("#content option").each(function(){
		$(this).attr("selected",true);
	});
}
function CheckIntensity(pwd){
	var Mcolor,Wcolor,Scolor,Color_Html;
	var m = 0;
	var Modes = 0;
	for (i=0; i<pwd.length; i++){
		var charType = 0;
		var t = pwd.charCodeAt(i);
		if (t>=48 && t <=57){charType = 1;}
		else if (t>=65 && t <=90){charType = 2;}
		else if (t>=97 && t <=122){charType = 4;}
		else{charType = 4;}
		Modes |= charType;
	}
	for (i=0;i<4;i++){if (Modes & 1){m++;}Modes>>>=1;}
	if (pwd.length<=4){m = 1;}
	if (pwd.length<=0){m = 0;}
	switch(m){
		case 1 :
			Wcolor = "pwd pwd_Weak_c";Mcolor = "pwd pwd_c";Scolor = "pwd pwd_c pwd_c_r";Color_Html = "弱";
			break;
		case 2 :
			Wcolor = "pwd pwd_Medium_c";Mcolor = "pwd pwd_Medium_c";Scolor = "pwd pwd_c pwd_c_r";Color_Html = "中";
			break;
		case 3 :
			Wcolor = "pwd pwd_Strong_c";Mcolor = "pwd pwd_Strong_c";Scolor = "pwd pwd_Strong_c pwd_Strong_c_r";Color_Html = "强";
			break;
		default :
			Wcolor = "pwd pwd_c";Mcolor = "pwd pwd_c pwd_f";Scolor = "pwd pwd_c pwd_c_r";Color_Html = "无";
			break;
	}
	$('#pwd_Weak').attr('class',Wcolor);
	$('#pwd_Medium').attr('class',Mcolor);
	$('#pwd_Strong').attr('class',Scolor);
	$('#pwd_Medium').html(Color_Html);
}
//*************************************************************************
function img_rowindex(tr){
	if ($.browser.msie){
		return tr.rowIndex;
	}
	else{
		table = tr.parentNode.parentNode;
		for (i = 0; i < table.rows.length; i ++ ){
			if (table.rows[i] == tr){
				return i;
			}
		}
	}
}
/*json数据添加一行*/
function json_add_cn(obj){  
	//var idx  = $('.json_order_id').length; //img_rowindex(src)
	var html = "链接：<input name=\"json_title_cn[]\" type=\"text\" size=\"23\" /> &nbsp;&nbsp;&nbsp;"
		      +"图片：<input name=\"json_img_cn[]\" type=\"file\" class=\"W200\" size=\"20\"/> &nbsp;&nbsp;"  
		      //+"排序：<input name=\"json_order_id[]\" type=\"text\" class=\"json_order_id\" value=\""+(idx+1)+"\" size=\"2\"/>&nbsp;&nbsp;" 
		      +"<img src=\"images/icons_jia.gif\" title=\"添加图片\" onclick=\"json_add_cn(this)\" class=\"cursor_p\"/>&nbsp;"
		      +"<img src=\"images/icons_del.gif\" title=\"删除图片\" onclick=\"json_remove(this)\" class=\"cursor_p\"/>";
	$(obj).closest("table").append("<tr><td>"+html+"<td></tr>");
}
/*json数据添加一行*///en
function json_add_en(obj){  
	//var idx  = $('.json_order_id').length; //img_rowindex(src)
	var html = "链接：<input name=\"json_title_en[]\" type=\"text\" size=\"23\" /> &nbsp;&nbsp;&nbsp;"
		      +"图片：<input name=\"json_img_en[]\" type=\"file\" class=\"W200\" size=\"20\"/> &nbsp;&nbsp;"  
		      //+"排序：<input name=\"json_order_id[]\" type=\"text\" class=\"json_order_id\" value=\""+(idx+1)+"\" size=\"2\"/>&nbsp;&nbsp;" 
		      +"<img src=\"images/icons_jia.gif\" title=\"添加图片\" onclick=\"json_add_en(this)\" class=\"cursor_p\"/>&nbsp;"
		      +"<img src=\"images/icons_del.gif\" title=\"删除图片\" onclick=\"json_remove(this)\" class=\"cursor_p\"/>";
	$(obj).closest("table").append("<tr><td>"+html+"<td></tr>");
}
/*json数据添加一行*///jp
function json_add_jp(obj){  
	//var idx  = $('.json_order_id').length; //img_rowindex(src)
	var html = "链接：<input name=\"json_title_jp[]\" type=\"text\" size=\"23\" /> &nbsp;&nbsp;&nbsp;"
		      +"图片：<input name=\"json_img_jp[]\" type=\"file\" class=\"W200\" size=\"20\"/> &nbsp;&nbsp;"  
		      //+"排序：<input name=\"json_order_id[]\" type=\"text\" class=\"json_order_id\" value=\""+(idx+1)+"\" size=\"2\"/>&nbsp;&nbsp;" 
		      +"<img src=\"images/icons_jia.gif\" title=\"添加图片\" onclick=\"json_add_jp(this)\" class=\"cursor_p\"/>&nbsp;"
		      +"<img src=\"images/icons_del.gif\" title=\"删除图片\" onclick=\"json_remove(this)\" class=\"cursor_p\"/>";
	$(obj).closest("table").append("<tr><td>"+html+"<td></tr>");
}
/*json数据删除一行 并删除 图片*/
function json_remove(obj,img_id,id,table,json_string){ //对象 json数据串中的img_id 记录id 表名 字段名
	jConfirm('确定要删除吗？','操 作 提 示',function(result){
		if(result){
			$(obj).closest("tr").detach(); //删除当前行  
			if(img_id!=''&&id!=''){  //操作数据库删除图片 
				$.post("qc_jquery.php",{'action':'json_img_del','img_id':img_id,'id':id,'table':table,'json_string':json_string}); 
			} 
		}
	});
} 
function img_update(){
	var $num = $("input[name='old_img_order_id[]']").length;
	$("input[name='img_order_id[]']").each(function($i){
		$(this).val($num+$i+1);
	});
}
function img_del(obj, name, id){
	jConfirm('您确定要删除本条记录，该操作不可恢复。','操 作 提 示',
		function(result) {
			if(result){
				$.post('qc_jquery.php',{'action':'ad_del', 'id':id}, function(data){
					switch (data.status) {
						case 'success':
							jAlert(data.message, '操 作 提 示');
							img_remove(obj, name);
							break;
						case 'failure':
							jAlert(data.message, '错 误 提 示');
							break;
						default:
					}
				},"json");
			}
		}
	);
}

$(function(){
	if ($('#phone_from').length > 0 && $('#phone_to').length > 0) {
		$("#phone_from").dblclick(function() {$.listTolist("phone_from","phone_to","move",false);});
		$("#phone_to").dblclick(function() {$.listTolist("phone_to","phone_from","move",false);});
		$("#moveright").click(function() {$.listTolist("phone_from","phone_to","move",false);});
		$("#moverightall").click(function() {$.listTolist("phone_from","phone_to","move",true);});
		$("#moveleft").click(function() {$.listTolist("phone_to","phone_from","move",false);});
		$("#moveleftall").click(function() {$.listTolist("phone_to","phone_from","move",true);});
	}
});


$(function(){
	$("#changepassword").click(function(){
		if($("#changepassword").attr("checked")==true){
			$("#password").val("");	
		}else{
			$("#password").val("123456");	
		}
	});		   
});