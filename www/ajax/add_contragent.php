<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../functions/user.php');
$user=new user;

$data['add_contragent_type']=array(1,null,null,1,1);
$data['captcha']=array(null,5,7);
if(post('add_contragent_type')==3){
	$data['add_contragent_3_name1']=array(1,null,50);
	$data['add_contragent_3_name2']=array(1,null,50);
	$data['add_contragent_3_name3']=array(null,null,50);
	$data['add_contragent_3_birtday']=$data['add_contragent_3_passport_date']=array(null,null,null,10);
	$data['add_contragent_3_sex']=array(1,null,null,1,1);
	$data['add_contragent_3_passport_sn']=array(null,null,null,12);
	$data['add_contragent_3_passport_v']=array(null,null,100);
}
elseif(post('add_contragent_type')==2){
	$data['add_contragent_2_inn']=array(1,10,12,null,5);
	$data['add_contragent_2_kpp']=array(null,null,null,9,1);
	$data['add_contragent_2_name']=array(1,null,180);
	$data['add_contragent_2_nds']=array(1,null,null,1,1);
}
array_walk($data,'check');

if(!$e){
	if($data['add_contragent_type']==3)
		if($result=$user->addContragentPerson())
			echo json_encode($result,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
		else echo 'ERROR';
	elseif($data['add_contragent_type']==2)
		if($result=$user->addContragentFirm())
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