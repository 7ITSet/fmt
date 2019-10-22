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
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/order.php');
$order=new order;

$data['code']=array(1,null,null,10);
array_walk($data,'check',true);

if(!$e){
	echo $order->checkPromocode($data);
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
unset($order);
?>