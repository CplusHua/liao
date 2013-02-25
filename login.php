<?php
include 'liao.class.php';
if(isset($_GET['tel'])&&isset($_GET['vcode']))
{
	$obj=new smsPush($_GET['tel']);
        $res=$obj->SubmitVcode($_GET['tel'],$_GET['vcode']);
        if($res) echo "成功登录";
}
else
{
	echo "要这样登录哦~";
 	 echo "http://".$_SERVER['HTTP_HOST']."/login.php?tel=12345567&vcode=验证码";
}