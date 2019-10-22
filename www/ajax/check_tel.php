<?
define ('_DSITE',1);
global $e;

require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php');
$sql=new sql;
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/user.php');
$user=new user(false);

$data['tel']=array(1,null,null,16,2);
array_walk($data,'check');

if(!$e){
	if($user->checkTel($data['tel']))
		echo 'ERROR_DUPLICATE_TEL';
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