$(function(){

	$('#search_form').find("input[id='keyword']").focus(function(){
		var val = $(this).val();
		if($.trim(val)=='Search'){ 
			$(this).val('');
		}
	}); 
	$('#search_form').find("input[id='keyword']").blur(function(){
		var val = $(this).val();
		if($.trim(val)==''){ 
			$(this).val('Search');
		}
	}); 
	$('#search_form').submit(function(){ 			
		$keyword    = $("#keyword");  
		if($.trim($keyword.val())==''||$.trim($keyword.val())=='_'||$.trim($keyword.val())=='Search') {
			alert('keyword can not be null');
			$keyword.focus();
			return false;
		} 
	});
	$('#register_from').submit(function(){
		$email        = $("#email");								
		$password      	 = $("#password");
		$confirm_password    = $("#confirm_password");
		$username   = $("#username");
		$telphone =$("#telphone");
		$security_code=$("#security_code");
		var phone="134,135,136,137,138,139,150,151,152,157,158,159,187,188,147,182,130,131,132,155,156,185,186,145,133,153,180,189";

		if( phone.indexOf($("#telphone").val().substring(0,3)) <= 0){
			alert('手机号码格式不正确');
			$telphone.focus();
			return false;
		}else if( $.trim($telphone.val()).length !=11 ){
			alert('手机号码位数不对');
			$telphone.focus();
			return false;
		}	
		if ($.trim($email.val())=='') {
			alert('E-mail不能为空');
			$email.focus();
			return false;
		}else if(!Validate($.trim($email.val()),/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/)){
			alert('E-mail格式不正确');
			$email.focus();
			return false;
		}
		if ($.trim($password.val()) =='' ) {
			alert('密码不能为空');
			$password.focus();
			return false;	
		}else if( !Validate($password.val(),/^\S{6,20}$/) ){
			alert('必须字母和数字组合并且6-20');
			$password.focus();
			return false;
		}
		
		if ($.trim($confirm_password.val()) =='' ) {
			alert('确认密码不能为空');
			$confirm_password.focus();
			return false;
		}else if( $.trim($confirm_password.val()) != $.trim($password.val()) ){
			alert('确认密码与密码不相等');
			$confirm_password.focus();
			return false;	
		}
		if ($.trim($username.val())=='') {
			alert('姓名不能为空');
			$username.focus();
			return false;
		}
		
		if ($.trim($security_code.val())=='') {
			alert('验证码不能为空');
			$security_code.focus();
			return false;
		}
		
		if( $("input[name=checkbox]:checked").length ==0){
			alert('请勾选‘同意服务条款’');
			return false;
		}
		
});
	
//简历提交验证
$("#form_submit_jobs").submit(function(){
	$username=$("#username");
	$houseaddress=$("#houseaddress");
	$monthly_pay=$("#monthly_pay");
	$experience=$("#experience");
	
	if($.trim($username.val())==''){
		alert('姓名不能为空');
		$username.focus();
		return false;
	}
	if($.trim($houseaddress.val())==''){
		alert('家庭地址不能为空');
		$houseaddress.focus();
		return false;
	}
	if($.trim($monthly_pay.val())==''){
		alert('期望月薪不能为空');
		$monthly_pay.focus();
		return false;
	}	
	if($.trim($experience.val())==''){
		alert('公司经历不能为空');
		$experience.focus();
		return false;
	}
	if( $("input[name=info_ok]:checked").length ==0){
		alert('请勾选‘请确认以上所填信息属实’');
		return false;
	}
	
	
	
});

$("#form_my_sellcar").submit(function(){
	$title= $("#title");
	$wish_price= $("#wish_price");
	$car_info= $("#car_info");
	$road_input_one=$("#road_input_one");
	$road_input_two=$("#road_input_two");
	$road_input_three=$("#road_input_three");

	
	if($.trim($title.val())==''){
		alert('标题不能为空');
		$title.focus();
		return false;
	}
	
	if( $.trim($road_input_one.val()) =='' ||  $.trim($road_input_two.val()) =='' || $.trim($road_input_three.val()) =='' ){
		alert('详细地址信息不全');
		return false;	
	}
	
	if($.trim($wish_price.val())==''){
		alert('理想价位不能为空');
		$wish_price.focus();
		return false;
	}
	
	if($.trim($car_info.val())==''){
		alert('详细说明不能为空');
		$car_info.focus();
		return false;
	}
		

});

$("#check_mycar_materials").submit(function(){
	if( $.trim($("#alipay_number").val())=='' ){
		alert('支付宝账号不能为空');
		return false;
	}											
											
	if ( $("input[class=materials]:checked").length != $('#count_materials').val() ) {
			alert("你的材料不全不能提交");
			return false;
	}
		
	if($("input[name=position]:checked").length ==0 ){
		alert('请勾选所在地')
		return false;
	}
	
	if($.trim($("#total_fee").val()) ==''){
		alert('价格不能为空');
		return false;
	}
	
		
});

$("#member_login_form").submit(function(){
	
	$username   = $("#username");
	$password   = $("#password");
	if( $("input[name=login_way]:checked").val() ==1){
		
		if ($.trim($username.val())=='') {
			alert('会员邮箱不能为空');
			$username.focus();
			return false;
		}else if(!Validate($.trim($username.val()),/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/)){
			alert('会员邮箱格式不正确');
			$username.focus();
			return false;
		}
		
		if ($.trim($password.val())=='') {
			alert('会员密码不能为空');
			$password.focus();
			return false;
		}
		
	}else{
		if ($.trim($username.val())=='') {
			alert('会员卡号不能为空');
			$username.focus();
			return false;
		}
		
		if ($.trim($password.val())=='') {
			alert('会员密码不能为空');
			$password.focus();
			return false;
		}
	}
	
	
});

$("#myinfo_update_form").submit(function(){
		$email   = $("#email");
		$telphone   = $("#telphone");
		$company_name   = $("#company_name");
		
		if ($.trim($email.val())=='') {
			alert('电子邮箱不能为空');
			$email.focus();
			return false;
		}else if(!Validate($.trim($email.val()),/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/)){
			alert('电子邮箱格式不正确');
			$email.focus();
			return false;
		}
		
		if ($.trim($telphone.val())=='') {
			alert('电话号码不正确');
			$telphone.focus();
			return false;
		}else if( !Validate($.trim($telphone.val()),/^[0-9-+]+$/)){
			alert('手机号码格式不正确');
			$telphone.focus();
			return false;
		}	
		
		if ($.trim($company_name.val())=='') {
			alert('公司名称不能为空');
			$company_name.focus();
			return false;
		}
		
});




$("#members_car_form").submit(function(){
		$license_number3   = $("#license_number3");
		$up_the_date   = $("#up_the_date");
		$memberaddress_check	= $("#memberaddress_check");
		
		if ($.trim($license_number3.val())=='') {
			alert('车牌号信息不全');
			$license_number3.focus();
			return false;
		}
		
		if($.trim($up_the_date.val()) == '' ){
			alert('起保日期不能为空');
			$up_the_date.focus();
			return false;
		}
		
		
});

$("#integrals_form").submit(function(){
		$receiver   = $("#receiver");
		$receiver_phone    = $("#receiver_phone");
		$road_input_one    = $("#road_input_one");
		$road_input_two    = $("#road_input_two");
		$road_input_three    = $("#road_input_three");
		
		$receiver_three=$("#receiver_three");
		$receiver_phone_three=$("#receiver_phone_three");
	
	if( $("input[name=delivery]:checked").length ==0){
		alert('请勾选配送方式');
		return false;
	}else{
			if( $("input[name=delivery]:checked").val() ==1){
				return true;			
			}else if( $("input[name=delivery]:checked").val() ==2 ){
				
				if( $.trim($receiver.val())=='' ){
					alert('收货人不能为空');
					$receiver.focus();
					return false;
				}
				if( $.trim($receiver_phone.val())=='' ){
					alert('收货人电话不能为空');
					$receiver_phone.focus();
					return false;
				}else if( !Validate($.trim($receiver_phone.val()),/^[0-9-+]+$/)){
					alert('收货人电话格式不正确');
					$receiver_phone.focus();
					return false;
				}	
				
				if( $.trim($road_input_one.val())==''  || $.trim($road_input_two.val())==''  || $.trim($road_input_three.val())==''  ){
					alert('收货人地址信息不全');
					return false;
				}
		}else if( $("input[name=delivery]:checked").val() ==3 ){
				
				if( $.trim($receiver_three.val())=='' ){
					alert('收货人不能为空');
					$receiver.focus();
					return false;
				}
				if( $.trim($receiver_phone_three.val())=='' ){
					alert('收货人电话不能为空');
					$receiver_phone.focus();
					return false;
				}else if( !Validate($.trim($receiver_phone_three.val()),/^[0-9-+]+$/)){
					alert('收货人电话格式不正确');
					$receiver_phone.focus();
					return false;
				}
				
				if( $("input[name=old_integrals_address]:checked").length != 1 ){
					alert('请选择配送地址');
					return false;
				}
				
		
		}
	
	}
});

$("#maintain_form").submit(function(){
		if($.trim($("#area").val()) == ''){
			alert('服务区域不能为空');
			return false;
		}								
});

	
$('#form_login').submit(function(){
		$username   = $("#username");
		$password      	 = $("#password");
		if ($.trim($username.val())=='') {
			alert('用户名不能为空');
			$username.focus();
			return false;
		}
		
		if ($.trim($password.val()) =='' ) {
			alert('密码不能为空');
			$password.focus();
			return false;
		}else if($.trim($password.val()).length < 6 ){
			alert('密码长度不能小于6');
			$password.focus();
			return false;
		}
		var param=$('#form_login').serialize();
		$.post('jquery_member.php?a=1&'+param,function (data){
			if(data=='true'){
				alert('登录成功')	;
				window.location='./';
			}else{
				alert('登录失败');
			}										   
	    });
				
	});
	
});


function Validate(o,r){
	reg = r;
	if(!reg.test(o)){
		return false;
	}else{
		return true;
	}	
}

$(function(){
	$("#all_select").click(function(){
		$('input[name="maintain_id[]"]').attr('checked',this.checked);						
});	   
});

$(function(){
	$("#financial_form").submit(function(){
		if( $.trim($("#alipay_number").val()) ==''){
			alert('支付宝账号不能为空');
			return false;
		}									 
	});		   
});



$(function(){
	$("#add_servercar_form").submit(function(){
		if($("input[name=score]:checked").length ==0 ){
			alert('请勾选单选按钮')
			return false;
		}								 
	});		   
});
