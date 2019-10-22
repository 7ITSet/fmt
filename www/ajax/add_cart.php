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
	exit;
};
require_once(__DIR__.'/../../functions/order.php');
$order=new order;

$data['product_id']=array(1,null,null,10,1);
$data['product_count']=array(1,null,null,null,1);
$data['update']=array(null);
array_walk($data,'check');


if(!$e){
	echo $order->cart_update_item($data);
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
unset($order);
?>