	/**20121108 用于分类和排序的异动*/
	$(document).ready(function(){ 
		//$("#order_id").html('');
		/*编辑的时候*/
		$cid=$("#class_id").find('option:selected').val();   //编辑信息的时候开始的栏目id 
		if($cid!='null'&&typeof($cid)!='undefined'){   //判断是否在执行编辑操作   
			$table=$("#tb_name").val(); 
			$language=$("#lang").val()
			$now_order_id=$("#now_order_id").val(); //目前的排序id
			$.post("qc_jquery.php",{action:'order_id',class_id:$cid,table:$table,language:$language,now_order_id:$now_order_id},function(result){
				$("#order_id").html(result);
			});
		}
		/**/ 
		$("#class_id").change(function(){
			$cid=$("#class_id").find('option:selected').val(); //id  
			if($cid=='null'){
				$("#order_id").html('');
			}else{
				$table=$("#tb_name").val(); 
				$language=$("#lang").val(); 
				$.post("qc_jquery.php",{action:'order_id',class_id:$cid,table:$table,language:$language},function(result){
					$("#order_id").html(result);
				}); 				
			}			
		}); 
	}); 