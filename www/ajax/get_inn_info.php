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
	echo 'ERROR';
	exit;
};

$data['inn']=array(1,10,12,null,5);
array_walk($data,'check');

if(!$e){
	$res=$user->getInnInfo($data['inn']);
	if($res)
		echo $res;
	else echo 'ERROR';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR';
}

unset($sql);
unset($user);
?>