<?
define ('_DSITE',1);
global $e;

require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php');
$sql=new sql;
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/user.php');
$user=new user;

$data['code']=array(1,null,null,5,1);
$data['token']=array(1,null,null,32);
array_walk($data,'check');

if ($data['token']!=$user->getInfo('cookies_token'))
	$e[]='Неправильный идентификатор формы cookies_token='.$user->getInfo('cookies_token').', site_token='.$data['token'];
	
if(!$e){
	if($user->confirmTel($data['code']))
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