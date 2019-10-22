<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../functions/user.php');
$user=new user(false);
if(!$user->getInfo('m_users_id')){
	unset($user);
	unset($sql);
	echo 'ERROR_INPUT_DATA';
	exit;
};

$data['email']=array(1,null,null,null,4);
array_walk($data,'check');

if(!$e){
	if($user->checkEmail($data['email']))
		echo 'ERROR_DUPLICATE_EMAIL';
	else
		echo 'OK';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
?>