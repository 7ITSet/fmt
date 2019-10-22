<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/user.php');
$sql=new sql;
$user=new user;


$data['captcha']=array(1,5,7);
array_walk($data,'check',true);

if(!$e){
	if($user->checkCaptcha($data['captcha']))
		echo 'OK';
	else echo 'ERROR';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
?>