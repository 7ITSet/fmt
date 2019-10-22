<?
if(!defined ('_DSITE')) define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../../functions/system.php');
require_once(__DIR__.'/../../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../../functions/user.php');
$user=new user(false);

//если пользователь не заходил раньше (авторегистрация прошла)
if($cookie=$user->registerAuto()){
	setcookie('uid',$cookie,dtu(dtc(dt(),'+90 day')),'/','.'.$_SERVER['SERVER_NAME'],true,true);
	echo 2;
}
//если уже заходил
elseif($cookie===0)
	echo 1;
//если авторегистрация не прошла - ничего не возвращаем
unset($sql);
unset($user);
?>