<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/user.php');
require_once(__DIR__.'/../../functions/message.php');
$sql=new sql;
$user=new user;

$data['token']=array(1,null,null,32);
$data['email']=array(1,null,null,null,4);
$data['politic']=array(1,null,null,1,1);
array_walk($data,'check');

if(!$e){
	$res=$user->subscribe();
	//новый пользователь (вернулся id контрагента)
	if($res&&$res!==true)
		echo 'OK';
	//пользователь из ЛК
	elseif($res===true)
		echo 'OK_CONFIRM';
	else echo 'ERROR';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
?>