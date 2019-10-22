<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../functions/user.php');
$user=new user;

$data['captcha']=array(null,5,7);
$data['add_address_index']=array(null,null,null,6,1);
$data['add_address_area']=array(1,null,80);
$data['add_address_district']=array(null,null,80);
$data['add_address_city']=array(null,null,80);
$data['add_address_city_district']=array(null,null,80);
$data['add_address_city_settlement']=array(null,null,80);
$data['add_address_street']=array(1,null,80);
$data['add_address_house']=array(null,null,30);
$data['add_address_corp']=array(null,null,30);
$data['add_address_build']=array(null,null,30);
$data['add_address_mast']=array(null,null,30);
$data['add_address_detail']=array(null,null,30);
$data['add_address_additional']=array(null,null,180);
$data['add_address_full']=array(1,null,480);
$data['add_address_map_lat']=array(null,null,20);
$data['add_address_map_lon']=array(null,null,20);
$data['contragent']=array(1,null,null,10,1);
array_walk($data,'check');

if(!$e){
	if($result=$user->addContragentAddress())
		echo json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	else echo 'ERROR';
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR_INPUT_DATA';
}

unset($sql);
unset($user);
unset($order);
?>