<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/user.php');
require_once(__DIR__.'/../../functions/message.php');
$sql=new sql;
$user=new user;
$message=new message;


$data['captcha']=array(1,5,7);
$data['type']=array(1,3,5);
array_walk($data,'check');

if(!$e){
	if($user->checkCaptcha($data['captcha'],true)){
		switch($data['type']){
			case 'sms':
				if($user->sendConfirmTel())
					echo 'OK';
				else echo 'ERROR';
				break;
			case 'email':
				if($user->sendConfirmEmail())
					echo 'OK';
				else echo 'ERROR';
				break;
		}
	}
	else echo 'ERROR_INPUT_CAPTCHA';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
unset($order);
?>