<?php
/*
*翼聊短信发送类，由于翼聊短信限制，非电信用户每天对每个号码不能发送5条以上的短信，发送多条时会出现验证码，所以会发送失败；
*其实只要输入验证码就好了，反正翼聊短信也就验证一次验证码~
*Author:CplusHua
*URI:http://weibo.com/sdnugonghua
*/
Class smsPush{
	public $PhoneNum;
	public $vcode;
	public $cookie;
	function __construct($PhoneNum=null){
		if(null==$PhoneNum) echo '您没有设置发送者手机号码，这样是发不出短信滴~';
		$this->PhoneNum=$PhoneNum;
	}
  	function __get($var){
  		return $this->$var;
        }
	function getVcode($PhoneNum=null){
		if($PhoneNum==null&& $this->PhoneNum!=null)	$PhoneNum=$this->PhoneNum;
		else if($PhoneNum==null) return false;
          $url='http://115.239.133.251:6090/imweb/phoneCheckCode.s?0.9838632841128856&sendPhone='.$PhoneNum.'&methodType=getPhoneCode';
		$option=array(
			CURLOPT_URL=>$url,
			CURLOPT_POST=>false,
			CURLOPT_RETURNTRANSFER=>true,
			//CURLOPT_HEADER=>true,
                );
		$result=$this->exec($option);
      	$this->saveCookie($this->cookie);
      	if(100==$result) return true;
      	else return false;
	}
	function SubmitVcode($PhoneNum=null,$vcode=null){
		if(null==$vcode) $vcode=$this->vcode;
		if(null==$PhoneNum) $PhoneNum=$this->PhoneNum;
		$this->cookie='JSESSIONID=C72FD92F73AB532C0676565D2D8B7971;loginType=1; firsstYZ=yes';
		$option=array(
			CURLOPT_URL=>'http://115.239.133.251:6090/imweb/codeLogin.s?clientId=46&account='.$PhoneNum.'&checkCode='.$vcode.'&rid=0.19796998496167362',
			CURLOPT_COOKIE=>$this->cookie,
			CURLOPT_HEADER=>true,
			CURLOPT_RETURNTRANSFER=>true,
		);
		$result=$this->exec($option);
		preg_match_all('/\nSet-Cookie:\s(.*)\s\n[\w|\W]*({"code":"100","loginSessionInfo":{.*})/i', $result, $matches);
          //print_r($matches);//echo $matches[0][1];
		$res=json_decode($matches[2][0]);
          //	print_r($res);
		if(100!=$res->code) return false;
		$this->cookie=$matches[1][0];
		$this->saveCookie($this->cookie);
		return true;
	}
	function exec($option=array()){
		if(empty($option)) return false;
		$c=curl_init();
		curl_setopt_array($c, $option);
		$res=curl_exec($c);
       		curl_close($c);
		return $res;
	}
	function sendSMS($receivePhone=null,$msg=null,$checkCode=null){
		if(null==$receivePhone||null==$msg) return false;
		$data='&checkCode='.$checkCode.'&receivePhone='.$receivePhone.'&smsContent='.$msg.'&random=0.7006821087561548';
		$this->readCookie();
		$this->cookie.=';loginType=1; firsstYZ=yes';
          //echo $this->cookie;
		$option = array(
			CURLOPT_URL =>'http://115.239.133.251:6090/imweb/smsPush.s?clientId=46' ,
			CURLOPT_POST=>true,
			CURLOPT_POSTFIELDS=>$data,
			CURLOPT_COOKIE=>$this->cookie,
			CURLOPT_RETURNTRANSFER=>true,
		);
		$result=$this->exec($option);echo $result;
          	if(101==$result){
          		echo "这个IP还未登录，如果您是用在了分布式服务器，那么肯定是因为出口的IP不一样了~";
                        return $result;
         	 }
		if(104==$result){
			echo "发送次数超限！";
			return $result;
		}
		if(201==$result) {
			echo '请输入验证码！就这样悲剧了~';//其实就输入一次，抓取回来输入进去不就完事了嘛~ 人家翼聊短信是记录IP的，不要用多IP的服务器，否则悲剧了~
			return $result;
		}
		if(100==$result) return true;
		else return $result;
	}
        function sae_saveCookie($string){
		$mmc=memcache_init();
	    if($mmc==false){
	        echo "mc init failed\n"; return 0;	    	
	    }
	    else
	    {
	        return memcache_set($mmc,$this->PhoneNum,$string);
	    }
	}
	function sae_readCookie(){
		$mmc=memcache_init();
	    if($mmc==false){
	        echo "mc init failed\n"; return 0;
	    }
	    else
	    {
	        return memcache_get($mmc,$this->PhoneNum);
	    }

	}
	//虽然兼容了SAE环境的写cookie问题，但是SAE是多线出口，所以没有办法保证每次的出口IP都一样。使用该cookie，出口IP不同的时候是无法使用的
	function saveCookie($string){
		if(!empty($_SERVER['HTTP_APPNAME'])&&!empty($_SERVER['HTTP_APPVERSION'])) return $this->sae_saveCookie($string);
		$f=fopen($this->PhoneNum.'.txt', 'w');
		return fwrite($f, $string);
	}
	function readCookie(){
          if(isset($_SERVER['HTTP_APPNAME'])&&isset($_SERVER['HTTP_APPVERSION'])){  $this->cookie= $this->sae_readCookie(); return 1;}
		if(filesize($this->PhoneNum.'.txt')){
			$f=fopen($this->PhoneNum.'.txt', 'r');
			$cookie=fread($f, filesize($this->PhoneNum.'.txt'));
			if(!empty($cookie)) return $this->cookie=$cookie;
		}
	}
	function setImgcode(){

	}
	function getImg($imgurl="http://115.239.133.251:6090/imweb/imageServlet.s",$reffer="http://liao.189.cn/"){
		$this->readCookie();
		$this->cookie.=';loginType=1; firsstYZ=yes';
		//echo $this->cookie;
		$option = array(
			CURLOPT_URL =>$imgurl ,
			CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_HEADER=>0,
			CURLOPT_USERAGENT=>'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; QQDownload 1.7; TencentTraveler 4.0',
			CURLOPT_REFERER=>'http://liao.189.cn/',
			CURLOPT_COOKIE=>$this->cookie,
			CURLOPT_COOKIESESSION=>true,
		);
		$result=$this->exec($option);
		header('Content-Type: image/jpeg'); 
		echo $result;
		return $result;
	}
}
