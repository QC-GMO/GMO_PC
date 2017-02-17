$(function(){
	$('.box .item input').keyup(function(){
		if($(this).val()==''){
			$(this).siblings('label').show()
		}else{
			$(this).siblings('label').hide()
		}
	})
})
