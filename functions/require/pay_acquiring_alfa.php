<?
defined ('_DSITE') or die ('Access denied');

global $user,$sql,$content,$menu,$e;

$data['order']=array(1,null,null,10,1);
$data['cid']=array(null,null,null,10);
$data['hash']=array(null,4,32);
array_walk($data,'check',true);


if(!$e){		
	//выбираем последний исходящий счёт по запрошенному заказу
	$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE 
		`m_documents_order`='.$data['order'].' AND 
		`m_documents_performer` IN (3363726835) AND 
		`m_documents_templates_id`=2363374033 
		ORDER BY `m_documents_date` DESC LIMIT 1;';
	if($doc=$sql->query($q)){
		$doc=$doc[0];
		$doc_params=json_decode($doc['m_documents_params']);
		//успешная оплата
		if($data['hash']){
			if(md5($doc['m_documents_params'].'jh2o5gkf7')==$data['hash'])
				echo '<div class="form-alert success">Оплата заказа № '.$data['order'].' на сумму '.transform::price_o($doc_params->doc_sum).'&nbsp;<span class="symb_rouble">₽</span> прошла успешно. Мы уведомим Вас о готовности заказа.</div>';
			else echo '<div class="form-alert error">Платёж отклонён. Пожалуйста, обратитесь к операторам нашего интернет-магазина для других способов оплаты.</div>';
		}
		//только перешли по ссылке оплаты, еще не платили
		elseif($data['cid']==substr(md5($doc[0]['m_contragents_id']),0,10)) 
			echo '<div class="form-alert info">Пожалуйста, подождите. Сейчас страница будет перенаправлена на платёжный шлюз банка…</div>';
	}
	else echo '<div class="form-alert error">Неверный номер заказа для оплаты.</div>';
}
//ошибка заказа
else echo '<div class="form-alert error">Ошибка во входных параметрах.</div>';
?>
<script>
$(document).ready(function(){
	if($('.form-alert.info').length)
		$.post(
			'/ajax/rest_alfa.php',
			{
				order:<?=$data['order'];?>
			},
			function(data){console.log(data)
				if(data.length>24)
					window.location=data;
				else $('.form-alert.info').removeClass('info').addClass('error').text('Произошла ошибка в процессе оплаты. Пожалуйста, обратитесь к операторам нашего интернет-магазина для решения проблемы.');
			}
		);
});
</script>