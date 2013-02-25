<?php
include 'liao.class.php';
if(isset($_GET['tel'])&&isset($_GET['text'])&&isset($_GET['aim']))
{
   	$obj=new smsPush($_GET['tel']);
	$flag=$obj->sendSMS($_GET['aim'],$_GET['text'],$_GET['imgcode']);
	if($flag) echo '发送成功';	
}
else
{
	echo "要这样发送短信哦~";
  	echo "http://".$_SERVER['HTTP_HOST']."/send.php?tel=12345567&aim=接收方&text=短信内容";
}