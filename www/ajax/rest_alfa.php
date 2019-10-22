<?
/*

 * Отправка параметров заказа в банк для получения id заказа в банке
 * и формирования ссылки на оплату на стороне банка.

 * Если получен уникальный id, прикрепляем его к заказу в БД и возварщаем
 * ссылку на оплату.

 * Если у заказа уже имеется id банка, то возвращаем ссылку на оплату 
 * для него

*/
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/bank.php');
$sql=new sql;
$bank=new bank;

$data['order']=array(1,null,null,10,1);
array_walk($data,'check');

if(!$e){
	//выбираем последний исходящий счёт по запрошенному заказу
	$q='SELECT `m_documents`.*, `m_orders`.* FROM `formetoo_cdb`.`m_documents` 
		LEFT JOIN `formetoo_cdb`.`m_orders` ON 
			`m_orders_id`=`m_documents_order` WHERE  
		`m_documents_order`='.$data['order'].' AND 
		`m_documents_performer`=3363726835 AND 
		`m_documents_templates_id`=2363374033 
		ORDER BY `m_documents_date` DESC LIMIT 1;';
	if($doc=$sql->query($q)){
		$doc=$doc[0];
		echo $bank->register($data['order'],$doc);
	}
	else echo 'ERROR DOCUMENT NOT FOUND';
}
//ошибка заказа
else{
	elogs(__FILE__,__FUNCTION__,$data);
	echo 'ERROR INPUT DATA';
}

unset($sql);
unset($bank);
?>