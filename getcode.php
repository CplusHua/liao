<?php
include 'liao.class.php';
if(isset($_GET['tel'])){
  $obj=new smsPush($_GET['tel']);
  $res=$obj->getVcode($_GET['tel']);
  if($res) echo '获取验证码成功！';
}
else{
	echo "要这样获取验证码哦~";
  	echo "http://".$_SERVER['HTTP_HOST'].'getvcode.php?tel=12345567';
}
