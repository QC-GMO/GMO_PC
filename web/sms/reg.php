<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 默认 首页模块
*/
class Reg extends Front_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('web/mpublic');
		$this->load->model('web/mpub');
		$this->load->helper('url');
		$this->load->helper('cookie');
		$this->load->library('session');
	}
	/**
	 * 默认首页
	 */
	 public function index()
	{

		$this->load->helper('captcha');
		$captchaDir = dirname(dirname(dirname(__FILE__))).'/captcha/';
		self::clear_dir($captchaDir);
		$vals = array(
		    'word' => rand(1000,10000),
		    'img_path' => $captchaDir,
		    'img_url' => base_url().'captcha/',
		    'img_width' => '100',
		    'img_height' => 36,
		    'expiration' => 7200
		    );
	
		$data = create_captcha($vals);
		$this->session->set_userdata("captcha",$data['word']);
		$data['nav'][0]="注册";			
		$this->front_header($data['nav']);	
		$this->load->view('web/reg/reg.php',$data);
		$this->front_footer();
	}
	
	public function testmail()
	{
		echo '<pre>';
		print_r(sendemail('cnmiss@qq.com'));
	}


	public function checkemail()
	{
			$params = $_POST;
			$where ="email = '{$params['email']}'";
			$result = $this->mpublic->getRow('member',$fields = "",$where);
			if(count($result) >1){
				exit("-4");  /*邮箱已经存在*/
			}
	}


	public function checkusername()
	{
			$params = $_POST;
			$where ="account = '{$params['username']}'";
			$result = $this->mpublic->getRow('member',$fields = "",$where);
			if(count($result) >1){
				exit("-2");  /*用户名已经存在*/
			}	
	}

	public function act()
	{
		//print_r($_REQUEST);die;

		$params = $_REQUEST;
		if($params['captcha'] == $this->session->userdata('captcha')){


			$where ="account = '{$params['username']}'";
			$result = $this->mpublic->getRow('member',$fields = "",$where);
			if(count($result) >1){
				exit("-2");  /*用户名已经存在*/
			}	

			$where ="email = '{$params['email']}'";
			$result = $this->mpublic->getRow('member',$fields = "",$where);
			if(count($result) >1){
				exit("-4");  /*邮箱已经存在*/
			}

			$actcode=self::random_str(32);	
			$dataInfo = array(					
					'account'=>$params['username'],
					'password'=>md5($params['password']),
					'gender'=>$params['gender'],
					'email'=>$params['email'],
					'province'=>$params['province'],
					'city'=>$params['city'],
					'industry'=>$params['industry'],
					'birthday'=>$params['birthday'],					
					'createtime'=>date('Y-m-d G:i:s'),
					'lastlogintime'=>date('Y-m-d G:i:s'),
					'activecode'=>$actcode,
					'point'=>100,
					'isenable'=>1
			);
			//默认头像
			if($params['gender']==1){
			    $dataInfo['icon']='/images/user_01.jpg';
			}else if($params['gender']==0){
			    $dataInfo['icon']='/images/user_01_1.jpg';
			}
			//处理选填项目内容
			if(isset($params['otherregstr'])&&$params['otherregstr']!=""){
				$otherregstr=$params['otherregstr'];
				$otherregstr=trim($otherregstr,"|");
				$strarr = explode("|",$otherregstr);
		    	foreach($strarr as $newstr){
			        $data = explode("=",$newstr);        
			        $dataInfo[$data[0]]=$data[1];
		    	}
	    	}
			$result = $this->mpublic->db->insert('member',$dataInfo);
			$id=$this->mpublic->exc_sql('select Id from member where account="'.$params['username'].'"');
	     	//注册赠送积分
			$reg_add_point = array(					
				'mid'=>$id[0]['Id'],
				'point'=>$this->config->item('reg_add_point'),
				'gold'=>$this->config->item('reg_add_gold'),	
				'createtime'=>date('Y-m-d G:i:s'),
				'modifytime'=>date('Y-m-d G:i:s')				
			);
			$this->mpublic->db->insert('point',$reg_add_point);

			if($result){
				set_cookie("account",$params['username'],72000); 
				set_cookie("email",$params['email'],300);
				set_cookie("activecode",$actcode,300);
	    		exit("1");	/*注册成功*/		
			}else{
				  exit("-3");  /*注册失败*/
			}


		}else{
			exit("-1");  /*验证码不对*/
		}
	}



	public function activemail(){
	
		 $email=get_cookie("email");
		 $activecode=get_cookie("activecode");
		 
		 $subject = "enstylement邮件激活";
		 $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
		 $message="感谢您关注enstylement.com<br><p>

			如果上面不是链接形式，请将以下地址手工粘贴到浏览器地址栏再访问。<br>
			http://www.enstylement.com/reg/chksuccess/?register=".$activecode."<br><p>

			此致<br><p>
			enstylement.com管理团队<br><p>
			http://www.enstylement.com<br><p><br><p>

			----------------------------------------------------------------------<br><p>
			这封信是由ensylement.com发送的。您收到这封邮件，是由于在enstylement.com进行新用户注册时填写了这个邮箱地址。<br><p>
			如果您并没有访问过enstylement.com，或没有进行上述操作，请忽略这封邮件。<br><p>
			您不需要回复次邮件或进行其他进一步的操作。<br><p>";
		sendemail($email, $subject, $message);
		 
		 delete_cookie("email");
		 delete_cookie("activecode");
	
		$data['nav'][0]="邮件激活账号";
		$this->front_header($data['nav']);
		$this->load->view('web/reg/activemail.php');
		$this->front_footer();
	}
	/* public function activemail1(){
	    //header('content-Type: text/html; charset=utf-8');
	    	
	    $subject = "enstylement邮件激活";
	    $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
	    $message="感谢您关注enstylement.com<br><p>
	
			如果上面不是链接形式，请将以下地址手工粘贴到浏览器地址栏再访问。<br>
			http://www.enstylement.com/reg/chksuccess/?register=".$activecode."<br><p>
	
			此致<br><p>
			enstylement.com管理团队<br><p>
			http://www.enstylement.com<br><p><br><p>
	
			----------------------------------------------------------------------<br><p>
			这封信是由ensylement.com发送的。您收到这封邮件，是由于在enstylement.com进行新用户注册时填写了这个邮箱地址。<br><p>
			如果您并没有访问过enstylement.com，或没有进行上述操作，请忽略这封邮件。<br><p>
			您不需要回复次邮件或进行其他进一步的操作。<br><p>";
	    //$message = "=?UTF-8?B?".base64_encode($message)."?=";
	    sendemail('whc@dn.cn', $subject, $message);
	    	
	} */
	public function chksuccess(){
		if (isset($_REQUEST['register'])) {
			$chkcode=$_REQUEST['register'];
	
			$where ="activecode = '{$chkcode}'";
			$result = $this->mpublic->getRow('member',$fields = "Id",$where);

			if(count($result)>0){
				$login_session = array('islogin'=>1,'userid'=>$result['Id']);
				$this->session->set_userdata($login_session);
				$this->mpublic->update('member',array('activecode' =>""),array('Id' => $result['Id'] ));

				$data['nav'][0]="邮件激活成功";
				$this->front_header($data['nav']);
				$this->load->view('web/reg/chksuccess.php');
				$this->front_footer();
			}else{				
				echo "<script>alert('请输入正确的注册码')</script>";
				echo "<script>window.location.href='/'</script>";
			}
		}else{
			echo "<script>alert('请重新注册')</script>";
			echo "<script>window.location.href='/reg/'</script>";
		}
	}






	public static function  clear_dir($dir){
		if(is_dir($dir)){
		    $fp=opendir($dir);
		    while (($fstr=readdir($fp)) !== false){
			if ($fstr != "." && $fstr != "..") {
			    $fname=$dir.'/'.$fstr;
			    if(is_dir($fname)){   
				    self::clear_dir($fname);                        
			    }else{
				if(is_file($fname)){
				    unlink($fname);
				}                    
			    }
			}
		    }
		    return true;
		}else{
		    return false;
		}
    }

	private function random_str($length)
	{
	    //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
	    $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));	 
	    $str = '';
	    $arr_len = count($arr);
	    for ($i = 0; $i < $length; $i++)
	    {
	        $rand = mt_rand(0, $arr_len-1);
	        $str.=$arr[$rand];
	    }	 
	    return $str;
	}

	
} 
