<?
define ('_DSITE',1);
global $e,$G;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city`;','m_info_city_url');
foreach($area_list as &$_area)
	$_area=$_area[0];
$G['CITY']=$area_list[$G['SUBDOMAIN']];
require_once(__DIR__.'/../../functions/message.php');
$message=new message;
require_once(__DIR__.'/../../functions/user.php');
$user=new user;
require_once(__DIR__.'/../../functions/order.php');
$order=new order;

$data['name']=array(1,null,50);
$data['tel']=array(null,null,null,16,2);
$data['email']=array(null,null,null,null,4);
$data['politic']=array(1,null,null,1,1);

array_walk($data,'check');

if(!$e){
	$_POST['name']=$data['name'];
	$_POST['email']=$data['email'];
	$_POST['tel']=$data['tel'];
	$_POST['password']='!1mf8f(*7';
	$_POST['jur']=0;
	$_POST['nds']=0;
	$_POST['politic']=$data['politic'];
	$_POST['newsletter']=0;
	$_POST['quick']=1;

	$_POST['add_contragent_3_sex']=1;
	$_POST['add_contragent_3_name1']=$data['name'];
	if($contragent=$user->register()){
		//добавляем адрес-пустышку для нового контрагента
		$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` SET 
			`m_address_contragents_id`='.$contragent.',
			`m_address_full`=\'адрес не указан (быстрый заказ)\',
			`m_address_date`=\''.dt().'\';';
		$sql->query($q);
		if($address=$sql->query('SELECT `m_address_id` FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id`='.$contragent.' ORDER BY `m_address_date` DESC LIMIT 1;'))
			$address=$address[0]['m_address_id'];
		$_POST['pay_method']=1;
		$_POST['customer']=$contragent;
		$_POST['delivery']=1;
		$_POST['address']=$address;

		$result=$order->create();
		if(strlen($result)==10)
			echo $result;
		else
			echo 'ERROR';
	}
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