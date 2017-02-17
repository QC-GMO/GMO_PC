jQuery(document).ready(function($) {
	$('.fadeOut').owlCarousel({
		items: 1,
		//loop: true,
	});
	$('.nav .fadeOut1').owlCarousel({
		items: 1,
		//loop: true,
	})
	$('.left .fadeOut1').owlCarousel({
		items: 1,
		//loop: true,
		nav:true,
		responsiveClass:true
	})
	$('.ny_nav').owlCarousel({
		autoWidth:true,
		loop: false,
		responsive:{
          0:{
              items:3,
          },
          520:{
              items:5,
          },
          960:{
              items:7,
          }
      }
	})
});
$(function(){
	$('.head .nav li').hover(function(){
		$(this).find('div.nav_sub').slideDown()
		$(this).addClass('hover')
	},function(){
		$(this).find('div.nav_sub').slideUp()
		$(this).removeClass('hover')
	})
	$('.lang a.languan_icon').hover(function(){
		$('.lang ul').slideDown()
		clearTimeout(timer)
	},function(){
		timer=setTimeout(function(){
			$('.lang ul').slideUp()
		},300)
	})
	$('.lang ul').hover(function(){
		$('.lang ul').slideDown()
		clearTimeout(timer)
	},function(){
		timer=setTimeout(function(){
			$('.lang ul').slideUp()
		},300)
	})
	$(document).click(function (e) {
		if(document.documentElement.clientWidth <= 960){
			var _con1 = $('.class');  // 设置目标区域
			if (!_con1.is(e.target) && _con1.has(e.target).length === 0) { // Mark 1
				$('.class div').slideUp()   // 功能代码
			}
		}
    })
	$('.head .nav01').click(function (e) {
        var _con1 = $('.nav_1');  // 设置目标区域
        if (!_con1.is(e.target) && _con1.has(e.target).length === 0) { // Mark 1
            $('.head .nav01').animate({left:'100%'},300)   // 功能代码
        }
    })
	$('.nav i.nav_icon').click(function(){
		$('.head .nav01').animate({left:0},300)
	})
	$('.in_service .content li:nth-child(2n-1)').each(function(){
		$(this).css({'margin-right': '20px'});
	});
	$('.about03 li:nth-child(4n-3)').each(function(){
		$(this).css({'margin-left': '0px'});
	});
	function width1(){
		var w1=$('.in_service .content').width()-25
		var height2=$('.nav01').height()-15
		var w2=$('.about03 ul').width()+19
		var w3=$('.site').width()+31
		if (document.documentElement.clientWidth <= 960) {
			$('.popbox').removeAttr('style')
			$('.site li').removeAttr('style')
			$('.nav_1').height(height2)
			w2=$('.about03 ul').width()+10
			$('.about03').find('li').width((w2/4)-22)
			if (document.documentElement.clientWidth <= 640) {
				$('.in_service .content').find('li').removeAttr('style')
			} else{
				$('.in_service .content').find('li').width(w1/2)
			}
		}else {
			$('.in_service .content').find('li').width(w1/2)
			$('.site').find('li').width((w3/4)-75)
			$('.nav_1').removeAttr('style')
			$('.about03').find('li').width((w2/4)-22)
			setTimeout(function(){
				var height1=$(document).height()
				$('.popbox').height(height1)
			},200)
		}
		var height1=$('.bottom dl').height()
		$('.bottom dd').height(height1)
		
	}
	$('body').each(function(){
		width1()
		if (document.documentElement.clientWidth >= 961) {
			$('.head .nav li i').click(function(){
				window.location=$(this).siblings("a").attr("href");
			})
		}
	});
	$(window).resize(width1);
	$(".in_about").click(function(){
		window.location=$(this).find("a").attr("href");
		return false;
	});
	$('.property01 .item1 a.more').click(function(){
		if($(this).siblings('div').hasClass('height1')){
			$(this).siblings('div').removeClass('height1').addClass('height2')
			$(this).text('收起更多 》')
		}else{
			$(this).siblings('div').removeClass('height2').addClass('height1')
			$(this).text('查看更多 》')
		}
	})
	
	$('.p_b_content .but a.but_01').click(function(){
		$('.popbox').hide()
	})
	
	$('.class i').click(function() {
		$(this).siblings('div.clearfix').slideDown()
	});
	$('.class span').click(function(){
		if(document.documentElement.clientWidth <= 960){
			$(this).parent('div.clearfix').slideUp()
		}
	})
	$('.login_content .item input').keyup(function(){
		if($(this).val()==''){
			$(this).siblings('label').show()
		}else{
			$(this).siblings('label').hide()
		}
	})
	
	
	
	
})