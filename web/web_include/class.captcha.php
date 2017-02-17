<?php
/* 
 * Captcha Class base on PHP GD Lib
 * 这是一个较完整的图形验证码类，可更改各种显示效果
 * @for example:
 * include('captcha_class.php');
 * $code_obj = new imageCaptcha();
 * //+------用于调整显示样式,注释则使用默认样式------
 * //	set_show_mode($w,$h,$num,$fc,$fz,$ff_url,$lang,$bc,$m,$n,$b,$border);
 * //	$w验证码宽度; $验证码高度; $num验证码位数; $fc字符颜色; $fz字符大小; 
 * //	$ff_url字体存放路径; $lang定义字符语言'en'或'cn'; $bc背景颜色; $m干扰点个数; $n干扰线条数; 
 * //	$b是否扭曲字符,TRUE或FALSE; $border是否有边框,TRUE或FALSE; 
 * //$code_obj->set_show_mode('120','38','6','#222222','15','c:\\windows\\fonts\SIMYOU.ttf','en','#ffffff','100','0',TRUE,FALSE);
 * //+------
 * $code_obj->createImage();
 */

class imageCaptcha{
	private $height; 				//@定义验证码图片高度
	private $width; 				//@定义验证码图片宽度
	private $textNum; 				//@定义验证码字符个数
	private $textContent; 			//@定义验证码字符内容
	private $fontColor; 			//@定义字符颜色
	private $randFontColor; 		//@定义随机出的文字颜色
	private $fontSize; 				//@定义字符大小
	private $fontFamily; 			//@定义字体
	private $bgColor; 				//@定义背景颜色
	private $randBgColor; 			//@定义随机出的背景颜色
	private $textLang;				//@定义字符语言
	private $noisePoint; 			//@定义干扰点数量
	private $noiseLine; 			//@定义干扰线数量
	private $distortion; 			//@定义是否扭曲
	private $distortionImage; 		//@定义扭曲图片源
	private $showBorder; 			//@定义是否有边框
	private $image; 				//@定义验证码图片源

	public function imageCaptcha(){	//@Constructor 构造函数
		//设置一些默认值
	 	$this->textNum 		= 4;
		$this->fontSize 	= 15;
	 	$this->fontFamily 	= 'fonts/verdana.ttf';//设置字体，可以改成linux的目录
	 	$this->textLang 	= 'en';
	 	$this->noisePoint 	= 100;
	 	$this->noiseLine 	= 0;
	 	$this->distortion 	= false;
	 	$this->showBorder 	= false;
	}
	
	public function set_show_mode($w,$h,$num,$fc,$fz,$ff_url,$lang,$bc,$m,$n,$b,$border){
		$this->width=$w;							//@设置图片宽度
		$this->height=$h;							//@设置图片高度
		$this->textNum=$num;						//@设置字符个数
		$this->fontColor=sscanf($fc,'#%2x%2x%2x');	//@设置字符颜色
		$this->fontSize=$fz;						//@设置字号
		$this->fontFamily=$ff_url;					//@设置字体url
		$this->textLang=$lang;						//@设置字符语言
		$this->bgColor=sscanf($bc,'#%2x%2x%2x');	//@设置图片背景
		$this->noisePoint=$m;						//@设置干扰点数量
		$this->noiseLine=$n;						//@设置干扰线数量
		$this->distortion=$b;						//@设置是否扭曲字符
		$this->showBorder=$border;					//@设置是否显示边框
	}
	
	public function initImage(){    //@初始化验证码图片
	 	if(empty($this->width)){$this->width=floor($this->fontSize*1.3)*$this->textNum+10;}
	 	if(empty($this->height)){$this->height=floor($this->fontSize*2.5);}
	 	$this->image=imagecreatetruecolor($this->width,$this->height);
		if(empty($this->bgColor)){
			$this->randBgColor=imagecolorallocate($this->image,mt_rand(100,255),mt_rand(100,255),mt_rand(100,255));
	 	}else{
	 		$this->randBgColor=imagecolorallocate($this->image,$this->bgColor[0],$this->bgColor[1],$this->bgColor[2]);
	 	}
	 	imagefill($this->image,0,0,$this->randBgColor);
	}
	
	public function randText($type){    //@产生随机字符
		$string='';
		switch($type){
			case 'en':
				$str='ABCDEFGHJKLMNOPQRSTUVWXYabcdehkmnprsuvwxy3456789';//要随机的字符内容
				for($i=0;$i<$this->textNum;$i++){
					$string=$string.','.$str[mt_rand(0,strlen($str)-1)];
				}
	 		break;
			case 'cn':
				for($i=0;$i<$this->textNum;$i++) {
					$string=$string.','.chr(mt_rand(0xB0,0xCC)).chr(mt_rand(0xA1,0xBB));
				}
				$string=iconv('GB2312','UTF-8',$string); //转换编码到utf8
			break;
		}
		return substr($string,1);
	}
	
	public function createText(){    //@输出文字到验证码
		$text_array=explode(',',$this->randText($this->textLang));
		$this->textContent=join('',$text_array);
		if(empty($this->fontColor)){
			$this->randFontColor=imagecolorallocate($this->image,mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
		}else{
			$this->randFontColor=imagecolorallocate($this->image,$this->fontColor[0],$this->fontColor[1],$this->fontColor[2]);
		}
		for($i=0;$i<$this->textNum;$i++){
			$angle=mt_rand(-1,1)*mt_rand(1,20);
			imagettftext($this->image,$this->fontSize,$angle,5+$i*floor($this->fontSize*1.3),floor($this->height*0.75),$this->randFontColor,$this->fontFamily,$text_array[$i]);
		}
	}
	
	public function createNoisePoint(){    //@生成干扰点
		for($i=0;$i<$this->noisePoint;$i++){
			$pointColor=imagecolorallocate($this->image,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($this->image,mt_rand(0,$this->width),mt_rand(0,$this->height),$pointColor);
		}
	}
	
	public function createNoiseLine(){    //@产生干扰线
		for($i=0;$i<$this->noiseLine;$i++) {
			$lineColor=imagecolorallocate($this->image,mt_rand(0,255),mt_rand(0,255),20);
			imageline($this->image,0,mt_rand(0,$this->width),$this->width,mt_rand(0,$this->height),$lineColor);
		}
	}
	
	public function distortionText(){    //@扭曲文字
		$this->distortionImage=imagecreatetruecolor($this->width,$this->height);
		imagefill($this->distortionImage,0,0,$this->randBgColor);
		for($x=0;$x<$this->width;$x++){
			for($y=0;$y<$this->height;$y++){
				$rgbColor=imagecolorat($this->image,$x,$y);
				imagesetpixel($this->distortionImage,(int)($x+sin($y/$this->height*2*M_PI-M_PI*0.5)*3),$y,$rgbColor);
			}
	 	}
		$this->image=$this->distortionImage;
	}
	
	public function createImage(){    //@生成验证码图片
		$this->initImage(); //创建基本图片
		$this->createText(); //输出验证码字符
		$this->createNoisePoint(); //产生干扰点
		$this->createNoiseLine(); //产生干扰线
		if($this->distortion !=false){$this->distortionText();}//扭曲文字
		if($this->showBorder){imagerectangle($this->image,0,0,$this->width-1,$this->height-1,$this->randFontColor);} //添加边框
		imagepng($this->image);
		imagedestroy($this->image);
		if($this->distortion !=false){imagedestroy($this->distortionImage);}
		return $this->textContent;
	}

}
?>