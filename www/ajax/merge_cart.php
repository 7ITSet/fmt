<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../functions/user.php');
$user=new user;
require_once(__DIR__.'/../../functions/order.php');
require_once(__DIR__.'/../../functions/order.php');
$order=new order;

$data['delete']=array(null,null,null,1,1);
array_walk($data,'check');

if(!$e){
	if($order->getCarts()){
		if($data['delete']) $order->deleteOldCarts();
		else $order->mergeCarts();
	}
	else echo 'ERROR_NO_MORE_1_CART';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}



unset($sql);
unset($user);
unset($order);
?>