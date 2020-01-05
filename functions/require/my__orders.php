<?
defined ('_DSITE') or die ('Access denied');
global $order,$sql,$user,$e;

if(get('change_account')=='error')
	echo '<div class="form-alert error">Произошла ошибка во время сохранения данных аккаунта.</div>';
if(get('change_account')=='error_password')
	echo '<div class="form-alert error">Введён неверный текущий пароль.</div>';
if(get('change_account')=='success')
	echo '<div class="form-alert success">Изменения аккаунат успешно сохранены.</div>';
if(get('change_password')=='error')
	echo '<div class="form-alert error">Введён неверный текущий пароль.</div>';
if(get('change_password')=='success')
	echo '<div class="form-alert success">Пароль успешно изменён.</div>';	
if(1){
	$uact=$user->getUserActions();
	$captcha_new_order=isset($uact[13])&&sizeof($uact[13])>10?true:false;
	$captcha_new_contragent=isset($uact[14])&&sizeof($uact[14])>5?true:false;
	$captcha_new_address=isset($uact[15])&&sizeof($uact[15])>5?true:false;
	
	$contragents=$user->getUserContragents();
?>

<?
if(get('new')=='success'){
	$data['id']=array(1,null,null,10,1);
	array_walk($data,'check',true);
	if(!$e){
		//контрагент
		$data_contr=$user->getUserContragents(false);
		//заказ
		$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_id`='.$data['id'].' LIMIT 1;';
		$data_order=$sql->query($q)[0];
		//телефоны и адрес доставки
		$data_order_tel=json_decode($data_order['m_orders_contacts']);
		$data_order_tel_=array();
		foreach($data_order_tel as $_tel)
			$data_order_tel_[]=$_tel->tel_numb.($_tel->tel_comment?' ('.$_tel->tel_comment.')':'');
		if($data_order_tel_)
			$data_order_tel=implode(', ',$data_order_tel_);
		if($data_order['m_orders_delivery_type']!=1)
			if($data_order_address=json_decode($data_order['m_orders_address']))
				$data_order_address=$data_order_address->m_address_full;
		//товары
		$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE 
			`m_documents_order`='.$data['id'].' AND 
			`m_documents_templates_id`=2363374033 AND 
			`m_documents_customer`='.$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_id'].' 
			LIMIT 1;';
		$data_prod=array();
		if($res_doc=$sql->query($q)[0]){
			$params=json_decode($res_doc['m_documents_params']);
			foreach($params->items as $_item)
				foreach($_item->services as $__item)
					$data_prod[]=$__item->id;
			$q='SELECT `id`,`slug`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `id` IN('.implode(',',$data_prod).');';	
			$data_prod=$sql->query($q,'id');
			//ед. измерения
			$data_units=array();
			foreach($data_prod as $_prod)
				$data_units[]=$_prod[0]['m_products_unit'];
			$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');
		}
		
		echo '
			<h2>Оформление заказа</h2>
			<div class="form-alert success" style="display:none">
				<p>Заказ № <b>'.get('id').'</b> успешно оформлен!</p>
				<p style="margin-bottom:0;">По указанным контактам с Вами свяжется наш сотрудник для подтверждения заказа. Ссылка для оплаты онлайн или счёт на оплату будет отправлен Вам автоматически после подтверждения заказа.</p>
			</div>
			<div class="form-alert success">
				Заказ № <span style="text-decoration:underline;">'.get('id').'</span> успешно оформлен!
				<p style="margin:.5em 0 0;">По указанным контактам с Вами свяжется наш сотрудник для подтверждения заказа. Ссылка для оплаты онлайн или счёт на оплату будет отправлен Вам автоматически после подтверждения заказа.</p>
			</div>
			<h3>Состав заказа</h3>
			<table class="info mini" width="100%">
				<thead>
					<tr>
					<td class="name" style="border-right:1px solid #ddd;" width="3%">№</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%">Арт.</td>
					<td class="name" style="border-right:1px solid #ddd;" width="60%">Наименование</td>
					<td class="name" style="border-right:1px solid #ddd;" width="10%">Цена</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%">Количество</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%"><nobr>Ед. измерения</nobr></td>
					<td class="name" width="12%">Сумма</td>
					</tr>
				</thead>
				<tbody>';
		foreach($params->items as $_item)
			foreach($_item->services as $k=>$__item)
				echo '
					<tr>
						<td class="center" style="border-right:1px solid #ddd;">'.($k+1).'</td>
						<td style="border-right:1px solid #ddd;">'.$__item->id.'</td>
						<td class="left" style="border-right:1px solid #ddd;">'.$data_prod[$__item->id][0]['m_products_name_full'].'</td>
						<td class="right" style="border-right:1px solid #ddd;">'.transform::price_o($__item->price,true,true).'&nbsp;₽</td>
						<td class="center" style="border-right:1px solid #ddd;">'.transform::price_o($__item->count,true,true).'</td>
						<td class="center" style="border-right:1px solid #ddd;">'.$data_units[$data_prod[$__item->id][0]['m_products_unit']][0]['m_info_units_name'].'</td>
						<td class="right">'.transform::price_o(round($__item->price*$__item->count,2),true,true).'&nbsp;₽</td>
					</tr>';
		echo '
				<tr>
					<td class="name" style="border-right:1px solid #ddd;text-align:right;" colspan="6">Итого'.($data_order['m_orders_delivery_type']!=1?' (без учёта доставки)':'').':</td>
					<td class="right">'.transform::price_o($params->doc_sum,true,true).'&nbsp;₽</td>
				</tr>
				</tbody>
			</table>
			<p>Выбранный метод оплаты: '.($data_order['m_orders_pay_method']==2?'счёт на оплату для '.($data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']?$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']:$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_p_fio']):'онлайн, банковской картой на сайте.').'</p>
			<p>Доставка: '.($data_order['m_orders_delivery_type']==1?'самовывоз':'доставить по адресу: '.$data_order_address).'</p>
			<p>Контактный телефон: '.$data_order_tel.'</p>
			<p>Комментарий к заказу: '.$data_order['m_orders_comment'].'</p>';;
	}
}
if(get('action')=='detail'){
	$data['id']=array(1,null,null,10,1);
	array_walk($data,'check',true);
	if(!$e){
		//контрагент
		$data_contr=$user->getUserContragents(false);
		//заказ
		$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_id`='.$data['id'].' LIMIT 1;';
		$data_order=$sql->query($q)[0];
		//телефоны и адрес доставки
		$data_order_tel=json_decode($data_order['m_orders_contacts']);
		$data_order_tel_=array();
		foreach($data_order_tel as $_tel)
			$data_order_tel_[]=$_tel->tel_numb.($_tel->tel_comment?' ('.$_tel->tel_comment.')':'');
		if($data_order_tel_)
			$data_order_tel=implode(', ',$data_order_tel_);
		if($data_order['m_orders_delivery_type']!=1)
			if($data_order_address=json_decode($data_order['m_orders_address']))
				$data_order_address=$data_order_address->m_address_full;
		//товары
		$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE 
			`m_documents_order`='.$data['id'].' AND 
			`m_documents_templates_id`=2363374033 AND 
			`m_documents_customer`='.$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_id'].' 
			LIMIT 1;';
		$data_prod=array(0);
		$data_serv=array(0);
		if($res_doc=$sql->query($q)[0]){
			$params=json_decode($res_doc['m_documents_params']);
			foreach($params->items as $_item)
				foreach($_item->services as $__item)
					if($__item->table=='products')
						$data_prod[]=$__item->id;
					else $data_serv[]=$__item->id;
			$q='SELECT `id`,`slug`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `id` IN('.implode(',',$data_prod).');';	
			$data_prod=$sql->query($q,'id');
			$q='SELECT `m_services_id`,`m_services_name`,`m_services_unit` FROM `formetoo_main`.`m_services` WHERE `m_services_id` IN('.implode(',',$data_serv).');';	
			$data_serv=$sql->query($q,'m_services_id');
			//ед. измерения
			$data_units=array();
			if($data_prod)
				foreach($data_prod as $_prod)
					$data_units[]=$_prod[0]['m_products_unit'];
			if($data_serv)
				foreach($data_serv as $_serv)
					$data_units[]=$_serv[0]['m_services_unit'];		
			$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');
			
			$delivery=false;
		}
		
		echo '
			<h2>Подробности заказа</h2>
			<table class="info mini" width="100%">
				<thead>
					<tr>
					<td class="name" style="border-right:1px solid #ddd;" width="3%">№</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%">Арт.</td>
					<td class="name" style="border-right:1px solid #ddd;" width="60%">Наименование</td>
					<td class="name" style="border-right:1px solid #ddd;" width="10%">Цена</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%">Количество</td>
					<td class="name" style="border-right:1px solid #ddd;" width="5%"><nobr>Ед. измерения</nobr></td>
					<td class="name" width="12%">Сумма</td>
					</tr>
				</thead>
				<tbody>';
		foreach($params->items as $_item)
			foreach($_item->services as $k=>$__item){
				//если среди позиций есть доставка
				if($__item->table=='services'&&$__item->id==1235463442)
					$delivery=true;
				echo '
					<tr '.($__item->table=='products'?'data-href="/product/'.$__item->id.'/" class="tr_link"':'').'>
						<td class="center" style="border-right:1px solid #ddd;">'.($k+1).'</td>
						<td style="border-right:1px solid #ddd;">'.$__item->id.'</td>
						<td class="left" style="border-right:1px solid #ddd;">'.($__item->table=='products'?$data_prod[$__item->id][0]['m_products_name_full']:$data_serv[$__item->id][0]['m_services_name']).'</td>
						<td class="right" style="border-right:1px solid #ddd;">'.transform::price_o($__item->price,true,true).'&nbsp;₽</td>
						<td class="center" style="border-right:1px solid #ddd;">'.transform::price_o($__item->count,true,true).'</td>
						<td class="center" style="border-right:1px solid #ddd;">'.$data_units[$__item->table=='products'?$data_prod[$__item->id][0]['m_products_unit']:$data_serv[$__item->id][0]['m_services_unit']][0]['m_info_units_name'].'</td>
						<td class="right">'.transform::price_o(round($__item->price*$__item->count,2),true,true).'&nbsp;₽</td>
					</tr>';
			}
		echo '
				<tr>
					<td class="name" style="border-right:1px solid #ddd;text-align:right;" colspan="6">Итого'.($data_order['m_orders_delivery_type']!=1?($delivery?'':' (без учёта доставки)'):'').':</td>
					<td class="right">'.transform::price_o($params->doc_sum,true,true).'&nbsp;₽</td>
				</tr>
				</tbody>
			</table>
			<p>Выбранный метод оплаты: '.($data_order['m_orders_pay_method']==2?'счёт на оплату для '.($data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']?$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']:$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_p_fio']):'онлайн, банковской картой на сайте.').'</p>
			<p>Доставка: '.($data_order['m_orders_delivery_type']==1?'самовывоз':'доставить по адресу: '.$data_order_address).'</p>
			<p>Контактный телефон: '.$data_order_tel.'</p>
			<p>Комментарий к заказу: '.$data_order['m_orders_comment'].'</p>';
	}
}
?>	
	<h2 id="order_form_header" style="display:none;">Оформление заказа</h2>
	<div class="login_container">
		<form id="order_form" action="/my/orders/" method="post" style="display:none;">
			<table class="no-border form-table">
				<thead>
					<tr>
						<th width="20%;"></th>
						<th width="80%;"></th>
					</tr>
				</thead>
				<tr>
					<td class="top">
						<div class="login_authorization_form_input_container">
							<span class="desc">Параметры заказа</span>
						</div>
					</td>
					<td class="order-info">
						<p><span class="grey">Вес:&nbsp;</span><span id="order-info-weight"></span></p>
						<p><span class="grey">Объём:&nbsp;</span><span id="order-info-volume"></span></p>
						<p><span class="grey">Длина:&nbsp;</span><span id="order-info-length"></span></p>
						<p><span class="grey">Сумма:&nbsp;</span><span id="order-info-sum"></span></p>
					</td>
				</tr>
				<tr id="customer_field">
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Покупатель</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<div class="main_products_list_toppanel_sort" onselectstart="return false">
								<div class="select_default_container">
									<div class="select_default">
										<p class="select_default_option_selected"><?=($contragents['contragents'][0]['m_contragents_p_fio']?$contragents['contragents'][0]['m_contragents_p_fio']:$contragents['contragents'][0]['m_contragents_c_name_short'])?></p>
										<div class="items">
											<?
											foreach($contragents['contragents'] as $k=>$_c)
												echo '<p class="select_default_option '.($k==0?'selected':'').'" data-value="'.$_c['m_contragents_id'].'">'.($_c['m_contragents_p_fio']?$_c['m_contragents_p_fio']:$_c['m_contragents_c_name_short']).'</p>';
											?>
											<p class="select_default_option new" data-value="0" id="open_popup_add_customer">+ добавить покупателя…</p>
										</div>
										<span class="icon icon-arrow-down"></span>
										<input type="hidden" name="customer" value="<?=$contragents['contragents'][0]['m_contragents_id'];?>"/>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Доставка</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<div class="main_products_list_toppanel_sort" onselectstart="return false">
								<div class="select_default_container">
									<div class="select_default">
										<p class="select_default_option_selected">Самовывоз со склада</p>
										<div class="items">
											<p class="select_default_option selected" data-value="1">Самовывоз со склада</p>
											<p class="select_default_option" data-value="2">Доставка интернет-магазина formetoo.ru</p>
											<p class="select_default_option" data-value="3">Доставка транспортной компанией</p>
										</div>
										<span class="icon icon-arrow-down"></span>
										<input type="hidden" name="delivery" value="1"/>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr id="address_field" style="display:none;">
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Адрес доставки</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<div class="main_products_list_toppanel_sort" onselectstart="return false">
								<div class="select_default_container">
									<div class="select_default">
										<?
										if(isset($contragents['addresses'][$contragents['contragents'][0]['m_contragents_id']])){
											echo '
												<p class="select_default_option_selected">'.$contragents['addresses'][$contragents['contragents'][0]['m_contragents_id']][0]['m_address_full'].'</p>
												<div class="items">';
											foreach($contragents['addresses'][$contragents['contragents'][0]['m_contragents_id']] as $k=>$_a)
												echo '
													<p class="select_default_option '.($k==0?'selected':'').'" data-value="'.$_a['m_address_id'].'">'.$_a['m_address_full'].'</p>
												';
											echo '
													<p class="select_default_option new" data-value="0" id="open_popup_add_address">+ добавить адрес…</p>
												</div>
												<span class="icon icon-arrow-down"></span>
												<input type="hidden" name="address" value="'.$contragents['addresses'][$contragents['contragents'][0]['m_contragents_id']][0]['m_address_id'].'"/>';
										}
										else echo '
											<p class="select_default_option_selected">Выберите адрес…</p>
											<div class="items">
												<p class="select_default_option new" data-value="0" id="open_popup_add_address">+ добавить адрес…</p>
											</div>
											<span class="icon icon-arrow-down"></span>
											<input type="hidden" name="address" value="0"/>';
										?>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="top">
						<div class="login_authorization_form_input_container">
							<span class="desc">Контактный телефон</span>
						</div>
					</td>
					<td style="width:23em;">
						<div class="login_authorization_form_input_container" id="telephones">
							<?
							if(isset($contragents['telephones'][$contragents['contragents'][0]['m_contragents_id']])){
								foreach($contragents['telephones'][$contragents['contragents'][0]['m_contragents_id']] as $_t)
									echo '
										<div class="multirow">
											<div class="row">
												<input type="text" name="tel[]" class="tel_input" autocomplete="off" placeholder="телефон" value="'.$_t['m_contragents_tel_numb'].'">
												<input type="text" name="tel_comment[]" class="tel_comment" autocomplete="off" placeholder="контактное лицо" value="'.$_t['m_contragents_tel_comment'].'">
												<div class="multirow-btn">
													<a class="add" href="javascript:void(0);"><span class="icon icon-df-add"></span></a>
													<a class="delete" href="javascript:void(0);"><span class="icon icon-df-delete"></span></a>
												</div>
											</div>
										</div>
									';
							}
							else echo '
								<div class="multirow">
									<div class="row">
										<input type="text" name="tel[]" class="tel_input" autocomplete="off" placeholder="телефон" value="">
										<input type="text" name="tel_comment[]" class="tel_comment" autocomplete="off" placeholder="контактное лицо" value="">
										<div class="multirow-btn">
											<a class="add" href="javascript:void(0);"><span class="icon icon-df-add"></span></a>
											<a class="delete" href="javascript:void(0);"><span class="icon icon-df-delete"></span></a>
										</div>
									</div>
								</div>';
							?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="top">
						<div class="login_authorization_form_input_container">
							<span class="desc">Способ оплаты</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<div class="rb">
								<input name="pay_method" id="pay_method_online" type="radio" class="main_products_filters_checkbox" checked value="1"/>
								<label for="pay_method_online" onselectstart="return false">Онлайн оплата банковской картой на сайте</label>
							</div>
							<div class="rb">
								<input name="pay_method" id="pay_method_bank" type="radio" class="main_products_filters_checkbox" value="2"/>
								<label for="pay_method_bank" onselectstart="return false">Выставить счёт для банковского перевода</label>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="top">
						<div class="login_authorization_form_input_container">
							<span class="desc">Комментарий к заказу</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container" style="width:100%;">
							<textarea name="comment" rows="3" maxlength="500"></textarea>
						</div>
					</td>
				</tr>
				<?
				if($captcha_new_order){
				?>
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Анти-робот</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<input type="text" name="captcha" placeholder="текст с картинки *" autocomplete="off"/>
						</div>
						<div class="login_authorization_form_sep">
							<span class="icon icon-arrow-left"></span>
						</div>
						<div class="login_authorization_form_input_container">
							<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
						</div>
					</td>
				</tr>
				<?
				}
				?>
			</table>
			<div class="clr"></div>

			<div class="login_authorization_form_input_container">
				<button type="submit" class="med_button orange">Оформить заказ</button>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="orderinfo" value="">
			<input type="hidden" name="handler" value="order_new">
		</form>
	</div>

<div class="clr"></div>
<?if($orders_list=$order->getOrders()){?>
<h2>История заказов</h2>
<table class="table-cart">
	<thead>
		<tr>
			<th style="width:5%">№</th>
			<th style="width:15%;">Дата заказа</th>
			<th style="width:20%;">Покупатель</th>
			<th style="width:37%;">Адрес доставки</th>
			<th style="width:10%;">Статус</th>
			<th style="width:13%;">Сумма</th>
		</tr>
	</thead>
	<tbody>
		<?
		$status=array(
			0=>array('class'=>'yellow','value'=>'ожидает подтверждения'),
			1=>array('class'=>'yellow','value'=>'не оплачен'),
			2=>array('class'=>'yellow','value'=>'в процессе оплаты'),
			3=>array('class'=>'green','value'=>'оплачен'),
			4=>array('class'=>'green','value'=>'оплачен'),
			5=>array('class'=>'green','value'=>'оплачен')
		);
		$order_ids=array();
		foreach($orders_list as $_order)
			$order_ids[]=$_order['m_orders_id'];
		$data_contr=$user->getUserContragents(false);
		//документы для пулучения сумм
		$sum=array();
		$q='SELECT * FROM `formetoo_cdb`.`m_documents` 
				INNER JOIN `formetoo_cdb`.`m_documents_templates`
					ON `m_documents`.`m_documents_templates_id`=`m_documents_templates`.`m_documents_templates_id` 
				WHERE `m_documents_order` IN('.implode(',',$order_ids).') AND 
				`m_documents_performer` IN(1306686034,1306686034) AND 
				`m_documents`.`m_documents_templates_id`=2363374033;';
		if($res_doc=$sql->query($q,'m_documents_order'))
			foreach($res_doc as $_res_doc){
				$_res_doc=$_res_doc[0];
				$params=json_decode($_res_doc['m_documents_params']);
				$sum[$_res_doc['m_documents_order']]=$params->doc_sum;
			}
		//таблица заказов
		foreach($orders_list as $k=>$_order){
			$_order['m_orders_address']=json_decode($_order['m_orders_address']);
			echo '
				<tr>
					<td><a class="underline" href="/my/orders/?action=detail&id='.$_order['m_orders_id'].'">'.$_order['m_orders_id'].'</a></td>
					<td><nobr>'.transform::date_f(dtu($_order['m_orders_date'])).'</nobr></td>
					<td>'.($data_contr['contragents'][$_order['m_orders_customer']][0]['m_contragents_type']==3?$data_contr['contragents'][$_order['m_orders_customer']][0]['m_contragents_p_fio']:$data_contr['contragents'][$_order['m_orders_customer']][0]['m_contragents_c_name_short']).'</td>
					<td>'.($_order['m_orders_delivery_type']==1?'самовывоз':$_order['m_orders_address']->m_address_full).'</td>
					<td>
						<nobr><span class="'.$status[$_order['m_orders_status']]['class'].'">'.$status[$_order['m_orders_status']]['value'].'</span></nobr>';
			//выводим кнопку для оплаты или ссылку на чек
			switch($_order['m_orders_status']){
				//выставлен счёт / готова ссылка для оплаты - выводим кнопку для оплаты
				case 1:	
				case 2:
					$doc=$res_doc[$_order['m_orders_id']][0];
					if($doc['m_documents_filesize']){
						$url_pay=$_order['m_orders_pay_method']==1
							?'https://www.formetoo.ru/pay-online/?order='.$doc['m_documents_order'].'&cid='.substr(md5($client['m_contragents_id']),0,10)
							:'https://www.formetoo.ru/files/'.$doc['m_documents_templates_folder'].'/'.$doc['m_documents_folder'].'/'.$doc['m_documents_templates_filename'].'.pdf';
						echo '<br/><a href="'.$url_pay.'"><button type="submit" class="min_button orange">'.($_order['m_orders_status']==1?'Оплатить':'Продолжить…').'</button></a>';
					}
					break;
				case 5:
					if($_order['m_orders_kassa_id']){
						$q='SELECT * FROM `formetoo_cdb`.`m_buh_kassa` WHERE `m_buh_kassa_id`='.$_order['m_orders_kassa_id'].' LIMIT 1;';
						if($res=$sql->query($q)){
							$res=json_decode($res[0]['m_buh_kassa_response']);
							$fp=$res->results[0]->result->fiscalParams->fiscalDocumentSign;
							$total=$res->results[0]->result->fiscalParams->total;
							if(is_numeric($fp)&&is_numeric($total)){
								$url_receipt='http://receipt.taxcom.ru/v01/show?fp='.$fp.'&s='.$total;
								echo '<br/><a href="'.$url_receipt.'" target="_blank"><button type="submit" class="min_button grey">Скачать&nbsp;чек</button></a>';
							}
						}
					}
					break;
				default:
					break;
			}
			echo '
					</td>
					<td><nobr>'.transform::price_o($sum[$_order['m_orders_id']]).'&nbsp;<span class="symb_rouble">₽</span></nobr></td>
				</tr>';
			
		}
		?>
	</tbody>
</table>
<?}?>
<div id="popup_add_contragent" class="popup" style="width:32.2em;margin-left:-16.5em;top:30%;">
	<div class="popup_header">
		<p>Добавить покупателя</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<form id="form_popup_add_contragent">
		<div class="login_authorization_form_input_container">
			<div class="rb">
				<input name="add_contragent_type" id="add_contragent_type_3" type="radio" class="main_products_filters_checkbox" checked value="3"/>
				<label for="add_contragent_type_3" onselectstart="return false">Физическое лицо</label>
			</div>
		</div>
		<div class="login_authorization_form_sep">
			или
		</div>
		<div class="login_authorization_form_input_container">
			<div class="rb">
				<input name="add_contragent_type" id="add_contragent_type_2" type="radio" class="main_products_filters_checkbox" value="2"/>
				<label for="add_contragent_type_2" onselectstart="return false">Юридическое лицо</label>
			</div>
		</div>
		<div class="clr"></div>
		<div id="add_contragent_3_container">
			<div class="login_authorization_form_input_container">
				<input type="text" name="add_contragent_3_name1" placeholder="фамилия *" maxlength="50" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_3_name2" placeholder="имя *" maxlength="50" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_3_name3" placeholder="отчество" maxlength="50" autocomplete="off"/>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_3_birtday" placeholder="дата рождения" maxlength="10" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container">
				<div class="rb">
					<input name="add_contragent_3_sex" id="add_contragent_3_sex_1" type="radio" class="main_products_filters_checkbox" checked value="1"/>
					<label for="add_contragent_3_sex_1" onselectstart="return false">Мужской пол</label>
				</div>

			</div>
			<div class="login_authorization_form_input_container">
				<div class="rb">
					<input name="add_contragent_3_sex" id="add_contragent_3_sex_2" type="radio" class="main_products_filters_checkbox" value="2"/>
					<label for="add_contragent_3_sex_2" onselectstart="return false">Женский пол</label>
				</div>
			</div>
			<div class="clr"></div>
			<p><span class="grey small">Паспортные данные (нужны в случае отправки транспортной компанией)</span></p>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_3_passport_sn" placeholder="cерия и номер" maxlength="12" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_3_passport_date" placeholder="дата выдачи" maxlength="50" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container">
				<input type="text" name="add_contragent_3_passport_v" placeholder="кем выдан" maxlength="100" autocomplete="off"/>
			</div>
		</div>
		<div id="add_contragent_2_container" style="display:none;">
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_2_inn" placeholder="ИНН *" maxlength="12" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container short">
				<input type="text" name="add_contragent_2_kpp" placeholder="КПП" maxlength="9" autocomplete="off"/>
			</div>
			<div class="login_authorization_form_input_container">
				<input type="text" name="add_contragent_2_name" placeholder="наименование (автозаполнение) *" maxlength="180" autocomplete="off"/>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<div class="cb">
					<input name="add_contragent_2_nds" id="add_contragent_2_nds" type="checkbox" class="main_products_filters_checkbox" value="1"/>
					<label for="add_contragent_2_nds" onselectstart="return false">Работаем с НДС</label>
				</div>
			</div>
		</div>
		<div class="clr"></div>
		<?if($captcha_new_contragent){?>
		<p></p>
		<div class="login_authorization_form_input_container">
			<input type="text" name="captcha" placeholder="текст справа *" maxlength="7" autocomplete="off"/>
		</div>
		<div class="login_authorization_form_sep">
			<span class="icon icon-arrow-left"></span>
		</div>
		<div class="login_authorization_form_input_container">
			<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<?}?>
		<div class="clr"><p></p></div>
		<div class="login_authorization_form_input_container" style="text-align:center;width:100%;">
			<button type="submit" class="med_button" id="popup_add_contragent_submit">Сохранить данные</button>
		</div>
	</form>
</div>
<div id="popup_add_address" class="popup" style="width:32.2em;margin-left:-16.5em;top:30%;">
	<div class="popup_header">
		<p>Добавить адрес доставки</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<form id="form_popup_add_address">
		<div class="login_authorization_form_input_container" style="width:100%">
			<input type="text" name="add_address" placeholder="город, улица, дом, квартира/офис" maxlength="500" style="width:100%" autocomplete="off"/>
			<input type="hidden" name="add_address_index" placeholder="индекс" maxlength="6" autocomplete="off"/>
			<input type="hidden" name="add_address_area" placeholder="субъект РФ" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_district" placeholder="район" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_city" placeholder="город/населённый пункт" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_city_district" placeholder="район города" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_city_settlement" placeholder="доп. инфо по району" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_street" placeholder="улица" maxlength="80" autocomplete="off"/>
			<input type="hidden" name="add_address_house" placeholder="дом" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_corp" placeholder="корпус" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_build" placeholder="строение" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_mast" placeholder="владение" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_detail" placeholder="владение" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_map_lat" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_map_lon" maxlength="20" autocomplete="off"/>
			<input type="hidden" name="add_address_full" value=""/>
			<input type="hidden" name="add_address_" value=""/>
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container" style="width:100%">
			<textarea name="add_address_additional" placeholder="дополнительная информация (домофон, этаж, особенности проезда)" maxlength="180" style="width:100%" autocomplete="off"></textarea>
		</div>
		<div class="clr"></div>
		<p style="display:none;"><span class="grey small">На карте ниже отмечена метка указанного адреса. Если метка стоит не в том месте, — пожалуйста, передвиньте её.</span></p>
		<div class="map_container"></div>
		<?if($captcha_new_address){?>
		<p></p>
		<div class="login_authorization_form_input_container">
			<input type="text" name="captcha" placeholder="текст справа *" maxlength="7" autocomplete="off"/>
		</div>
		<div class="login_authorization_form_sep">
			<span class="icon icon-arrow-left"></span>
		</div>
		<div class="login_authorization_form_input_container">
			<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<?}?>
		<div class="clr"><p></p></div>
		<div class="login_authorization_form_input_container" style="text-align:center;width:100%;">
			<button type="submit" class="med_button" id="popup_add_address_submit">Добавить адрес доставки</button>
		</div>
	</form>
</div>
<style>
.multirow:not(:last-child){
	height:2.8em;}
.multirow .tel_input{
	min-width:10em!important;
	width:10em;
	float:left;}
.multirow .tel_comment{
	min-width:11em!important;
	width:11em;
	float:left;
	margin:0 .5em;}
.multirow .multirow-btn{
	float:right;}
.order-info span{
	font-size: .875em;
	color: #000;
    font-weight: 400;}
.order-info p > span:first-child{
	color:#999;}
</style>
<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
	//ОТПРАВКА ГЛАВНОЙ ФОРМЫ
	$('#order_form').on('submit',function(){
		var model=$('#order_form').serializeArray();
		$.map(['a','b','c'],function(val,i){
			return model.push({"name":"collection["+i+"]","value":val});
		});
		$.post(
			'/ajax/add_tel.php',
			model,
			function(data){
				sessionStorage.removeItem('activeorder');
				localStorage.removeItem('promocode');
			}
		);
		return true;
	});

	//ИНФОРМАЦИЯ О ЗАКАЗЕ
	if(sessionStorage.getItem('activeorder')){
		$('#order_form,#order_form_header').show();
		$('[name="orderinfo"]').val(sessionStorage.getItem('activeorder'));
		var order_detail=$.parseJSON(sessionStorage.getItem('activeorder')),
			promocode=localStorage.getItem('promocode');
		$('#order_form').show();
		$('#order-info-weight').html(order_detail.total_weight.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;кг');
		$('#order-info-volume').html(order_detail.total_volume.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;м<sup>3</sup>');
		$('#order-info-length').html(order_detail.total_max_length.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;м');
		$('#order-info-sum').html(order_detail.total_sum.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;<span class="symb_rouble">₽</span>');
	}
	
	//СТРОКИ ТЕЛЕФОНОВ
	$('#telephones').df({
		max:5,
		f_a:function(){
			$('#telephones .multirow:last').find('input[name="tel[]"]').mask('+7 999 999-99-99',{placeholder:'_'});
			$('#telephones .multirow:last').find('input[name="tel[]"]').on('click',function(){
				if($(this).val()=='+7 ___ ___-__-__')
					$(this).setCursorPosition(3);
			});
		}
	});
	$('[name="tel[]"]').mask('+7 999 999-99-99',{placeholder:'_'});
	$('[name="tel[]"]').on('click',function(){
		if($(this).val()=='+7 ___ ___-__-__')
			$(this).setCursorPosition(3);
	});

	//ЗАПОЛНЯЕМ ТЕЛЕФОНЫ И АДРЕС ПРИ ВЫБОРЕ КОНТРАГЕНТА
	var telephones=$.parseJSON('<?=(json_encode($contragents['telephones'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?:'{}');?>'),
		addresses=$.parseJSON('<?=(json_encode($contragents['addresses'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?:'{}');?>');
	$(document).on('change','[name="customer"]',function(){
		$('#telephones').df('clear');
		if(telephones&&telephones[$(this).val()]!==undefined&&telephones[$(this).val()]!==null)
			for(var k in telephones[$(this).val()]){
				if($('#telephones').find('input[name="tel[]"]:last').val())
					$('#telephones').find('.add').trigger('click');
				$('#telephones').find('input[name="tel[]"]:last').val(telephones[$(this).val()][k]['m_contragents_tel_numb']);
				$('#telephones').find('input[name="tel_comment[]"]:last').val(telephones[$(this).val()][k]['m_contragents_tel_comment']);
			};
		$('#address_field .select_default .items').html('');
		$('#address_field .select_default .select_default_option_selected').text('');
		if(addresses&&addresses[$(this).val()]!==undefined&&addresses[$(this).val()]!==null){console.log(addresses[5994423014][0]['m_address_full']);
			$('#address_field .select_default').html('<p class="select_default_option_selected">'+addresses[$(this).val()][0]['m_address_full']+'</p><div class="items"></div><span class="icon icon-arrow-down"></span><input type="hidden" name="address" value="'+addresses[$(this).val()][0]['m_address_id']+'"/>');
			for(var k in addresses[$(this).val()])
				$('#address_field .select_default .items').append('<p class="select_default_option '+(k==0?'selected':'')+'" data-value="'+addresses[$(this).val()][k]['m_address_id']+'">'+addresses[$(this).val()][k]['m_address_full']+'</p>');
		}
		if(!$('#address_field .select_default .items p').length)
			$('#address_field .select_default .select_default_option_selected').text('Выберите адрес…');
		$('#address_field .select_default .items').append('<p class="select_default_option new" data-value="new" id="open_popup_add_address">+ добавить адрес…</p>');
	});
	
	
	//ПОКАЗ ПОЛЯ АДРЕСА ПРИ ВЫБОРЕ ДОСТАВКИ
	$('[name="delivery"]').on('change',function(){
		if($(this).val()==1) $('#address_field').hide();
		else $('#address_field').show();
	});
	
	//ДОБАВЛЕНИЕ КОНТРАГЕНТА
	//показ формы добавления контрагента
	$('#open_popup_add_customer').on('click',function(){
		type='customer';
		$('#popup_add_contragent_submit').text('Добавить покупателя').prop('disabled',false);
		popup_show('#popup_add_contragent',function(){
			$('#popup_add_contragent input:text:first').focus();
		});
		return false;
		
	});
	//физическое или юридическое лицо
	$('[name="add_contragent_type"]').on('change',function(){
		if($(this).val()==3){
			$('#add_contragent_2_container').hide().find('input').prop('disabled',true);
			$('#add_contragent_3_container').show().find('input').prop('disabled',false);
			$('#add_contragent_3_container').show().find('input:first').focus();
		}
		else{
			$('#add_contragent_3_container').hide().find('input').prop('disabled',true);
			$('#add_contragent_2_container').show().find('input').prop('disabled',false);
			$('#add_contragent_2_container').show().find('input:first').focus();
		}
	});
	$('[name="add_contragent_3_birtday"],[name="add_contragent_3_passport_date"]').mask('99.99.9999',{placeholder:'_'});
	$('[name="add_contragent_3_passport_sn"]').mask('99 99 999999',{placeholder:'_'});

	//ФОРМА ОТПРАВКИ ЗАКАЗА
	$('#order_form').validate({
		rules:{
			'tel[]':{
				required:true,
				tel:true
			},
			customer:{
				required:true,
				digits:true,
				rangelength:[10,10],
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#form_popup_add_contragent [name="captcha"]').val()
						}
					},
					dataFilter: function(data) {
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			},
		},
		messages:{
			captcha:{
				remote:'Код с картинки неверный'
			}
		}
	});
	
	//ФОРМА ДОБАВЛЕНИЯ ПОКУПАТЕЛЯ
	$('#form_popup_add_contragent').validate({
		rules:{
			add_contragent_3_name1:{
				required:true,
				maxlength:80
			},
			add_contragent_3_name2:{
				required:true,
				maxlength:80
			},
			add_contragent_3_name3:{
				maxlength:80
			},
			add_contragent_3_birtday:{
				rangelength:[10,10]
			},
			add_contragent_3_passport_sn:{
				rangelength:[12,12]
			},
			add_contragent_3_passport_date:{
				rangelength:[10,10]
			},
			add_contragent_3_passport_v:{
				maxlength:[100]
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#form_popup_add_contragent [name="captcha"]').val()
						}
					},
					dataFilter: function(data) {
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			},
			add_contragent_2_inn:{
				required:function(el){
					return $('[name="add_contragent_type"]').val()==2?true:false;
				},
				digits:true,
				rangelength:[10,12],
				inn:true,
				remote:{
					url: '/ajax/check_inn.php',
					type: 'post',
					data: {
						inn:function(){
							return $('[name="add_contragent_2_inn"]').val()
						},
						kpp:function(){
							return $('[name="add_contragent_2_kpp"]').val()
						}
					},
					dataFilter: function(data) {
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			},
			add_contragent_2_kpp:{
				required:function(el){
					return $('[name="add_contragent_type"]').val()==2
						?($('[name="add_contragent_2_inn"]').val()&&$('[name="add_contragent_2_inn"]').val().length==10
							?true
							:false
						)
						:false;
				},
				digits:true,
				rangelength:[9,9]	
			},
			add_contragent_2_name:{
				required:function(el){
					return $('[name="add_contragent_type"]').val()==2?true:false;
				}
			},
		},
		messages:{
			captcha:{
				remote:'Код с картинки неверный'
			},
			add_contragent_2_inn:{
				remote:'ИНН уже есть в системе'
			}
		},
		submitHandler:function(form){
			if(!$("#form_popup_add_contragent").validate())
				return false;
				$.post(
					'/ajax/add_contragent.php',
					{
						captcha:$('#form_popup_add_contragent [name="captcha"]').val(),
						add_contragent_3_name1:$('[name="add_contragent_3_name1"]').val(),
						add_contragent_3_name2:$('[name="add_contragent_3_name2"]').val(),
						add_contragent_3_name3:$('[name="add_contragent_3_name3"]').val(),
						add_contragent_3_birtday:$('[name="add_contragent_3_birtday"]').val(),
						add_contragent_3_sex:$('[name="add_contragent_3_sex"]:checked').val(),
						add_contragent_3_passport_sn:$('[name="add_contragent_3_passport_sn"]').val(),
						add_contragent_3_passport_date:$('[name="add_contragent_3_passport_date"]').val(),
						add_contragent_3_passport_v:$('[name="add_contragent_3_passport_v"]').val(),
						add_contragent_2_inn:$('[name="add_contragent_2_inn"]').val(),
						add_contragent_2_kpp:$('[name="add_contragent_2_kpp"]').val(),
						add_contragent_2_nds:($('[name="add_contragent_2_nds"]:checked').length?1:0),
						add_contragent_2_name:$('[name="add_contragent_2_name"]').val(),
						add_contragent_type:$('[name="add_contragent_type"]:checked').val()
					},
					function(data){
						if(data.indexOf('m_contragents_id')!==-1){
							data=$.parseJSON(data);
							addresses=data['addresses'];
							customers=data['contragents'];
								if(customers){
									$('#customer_field .select_default .items').html('');
									$('#customer_field .select_default .select_default_option_selected').text('');
									$('#customer_field .select_default').html('<p class="select_default_option_selected">'+(customers[0]['m_contragents_c_name_short']?customers[0]['m_contragents_c_name_short']:customers[0]['m_contragents_p_fio'])+'</p><div class="items"></div><span class="icon icon-arrow-down"></span><input type="hidden" name="customer" value="'+customers[0]['m_contragents_id']+'"/>');
									for(var k in customers)
										$('#customer_field .select_default .items').append('<p class="select_default_option '+(k==0?'selected':'')+'" data-value="'+customers[k]['m_contragents_id']+'">'+(customers[k]['m_contragents_c_name_short']?customers[k]['m_contragents_c_name_short']:customers[k]['m_contragents_p_fio'])+'</p>');
								}
								if(!$('#customer_field .select_default .items p').length)
									$('#customer_field .select_default .select_default_option_selected').text('Выберите покупателя…');
								$('#customer_field .select_default .items').append('<p class="select_default_option new" data-value="new" id="open_popup_add_address">+ добавить покупателя…</p>');
							
							$('[name="customer"]').trigger("change");
						}
						popup_hide();
					}
				);
			return false;
		}
	});
	
	//ФОРМА ДОБАВЛЕНИЯ АДРЕСА ДОСТАВКИ
	$('#form_popup_add_address').validate({
		rules:{
			add_address_additional:{
				maxlength:180
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#form_popup_add_address [name="captcha"]').val()
						}
					},
					dataFilter: function(data) {
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			}
		},
		messages:{
			captcha:{
				remote:'Код с картинки неверный'
			}
		},
		submitHandler:function(form){
			if(!$("#form_popup_add_address").validate())
				return false;
				var addr_full=[
					$('[name="add_address_index"]').val(),
					$('[name="add_address_area"]').val(),
					$('[name="add_address_district"]').val(),
					$('[name="add_address_city"]').val(),
					$('[name="add_address_street"]').val(),
					$('[name="add_address_house"]').val(),
					$('[name="add_address_corp"]').val(),
					$('[name="add_address_build"]').val(),
					$('[name="add_address_mast"]').val(),
					$('[name="add_address_detail"]').val()];
				addr_full=addr_full.filter(function(n){return (n!=undefined&&n!='')});
				$('[name="add_address_full"]').val(addr_full.join(', '));
				$.post(
					'/ajax/add_address.php',
					{
						captcha:$('#form_popup_add_address [name="captcha"]').val(),
						add_address_index:$('[name="add_address_index"]').val(),
						add_address_area:$('[name="add_address_area"]').val(),
						add_address_district:$('[name="add_address_district"]').val(),
						add_address_city:$('[name="add_address_city"]').val(),
						add_address_city_district:$('[name="add_address_city_district"]').val(),
						add_address_city_settlement:$('[name="add_address_city_settlement"]').val(),
						add_address_street:$('[name="add_address_street"]').val(),
						add_address_house:$('[name="add_address_house"]').val(),
						add_address_corp:$('[name="add_address_corp"]').val(),
						add_address_build:$('[name="add_address_build"]').val(),
						add_address_mast:$('[name="add_address_mast"]').val(),
						add_address_detail:$('[name="add_address_detail"]').val(),
						add_address_additional:$('[name="add_address_additional"]').val(),
						add_address_map_lat:$('[name="add_address_map_lat"]').val(),
						add_address_map_lon:$('[name="add_address_map_lon"]').val(),
						add_address_full:$('[name="add_address_full"]').val(),
						contragent:$('[name="customer"]').val()
					},
					function(data){
						if(data.indexOf('m_contragents_id')!==-1){
							data=$.parseJSON(data);
							addresses=data['addresses'];							
							$('[name="customer"]').trigger("change");
						}
						popup_hide();
					}
				);
			return false;
		}
	});
	
	
	
	function validINN(value){
		if(value.length==10)
			if(value.substr(-1)==((2*value.substr(0,1)+4*value.substr(1,1)+10*value.substr(2,1)+3*value.substr(3,1)+5*value.substr(4,1)+9*value.substr(5,1)+4*value.substr(6,1)+6*value.substr(7,1)+8*value.substr(8,1))%11)%10)
				return true;
			else
				return false;
		if(value.length==12)
			if(value.substr(-2,1)==((7*value.substr(0,1)+2*value.substr(1,1)+4*value.substr(2,1)+10*value.substr(3,1)+3*value.substr(4,1)+5*value.substr(5,1)+9*value.substr(6,1)+4*value.substr(7,1)+6*value.substr(8,1)+8*value.substr(9,1))%11)%10&&value.substr(-1,1)==((3*value.substr(0,1)+7*value.substr(1,1)+2*value.substr(2,1)+4*value.substr(3,1)+10*value.substr(4,1)+3*value.substr(5,1)+5*value.substr(6,1)+9*value.substr(7,1)+4*value.substr(8,1)+6*value.substr(9,1)+8*value.substr(10,1))%11)%10)
				return true;
			else
				return false;
	}
	
	
	$.validator.methods.tel=function(value,element) {
		return this.optional(element)||/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i.test(value);
	}
	$.validator.methods.inn=function(value,element) {
		return validINN(value);
	}
	$.validator.methods.inn_unique=function(value,element) {
		if(!$('[name="inn"]').val()||!$('[name="kpp"]').val()) return true;
		$.post(
			'/ajax/check_inn.php',
			{
				inn:$('[name="inn"]').val(),
				kpp:$('[name="kpp"]').val()
			},
			function(data){
				if(data!='OK')
					return false;
				else return true;
			}
		);
	}
	
	//ДОБАВЛЕНИЕ КОНТРАГЕНТОВ И АДРЕСОВ
	$(document).on('click','#open_popup_add_address',function(){
		popup_show('#popup_add_address',function(){
			$('#popup_add_address input:text:first').focus();
		});
		return false;
	});	
	//ПРОВЕРКА ИНН
	$('[name="add_contragent_2_inn"]').on('keyup',function(){
		$('[name="add_contragent_2_name"]').val('');
		$('[name="add_contragent_2_kpp"]').val('');
		if(validINN($(this).val()))
			$.post(
				'/ajax/get_inn_info.php',
				{
					inn:$('[name="add_contragent_2_inn"]').val()
				},
				function(data){
					if(data!="ERROR"){
						var firm=null;
						try{firm=$.parseJSON(data)}
						catch{}
						if(firm!==null){
							$('[name="add_contragent_2_name"]').val(firm.suggestions[0].value?firm.suggestions[0].value:firm.suggestions[0].data.name.full_with_opf);
							$('[name="add_contragent_2_kpp"]').val(firm.suggestions[0].data.kpp);
						}
					}
				}
			);
	});
	
	//АВТОЗАПОЛНЕНИЕ АДРЕСОВ
	$('input[name=add_address]').sug_addr();
	$('[name="add_address_"]').on('change',function(){
		var addr_json=null;
		try{
			addr_json=$.parseJSON($(this).val());
		}
		catch{}
		if(addr_json!==null){
			$('[name="add_address_index"]').val(addr_json.data.postal_code);
			$('[name="add_address_area"]').val(addr_json.data.region_with_type);
			$('[name="add_address_district"]').val(addr_json.data.area_with_type);
			$('[name="add_address_city"]').val(addr_json.data.city_with_type);
			$('[name="add_address_city_district"]').val(addr_json.data.city_district_with_type);
			$('[name="add_address_city_settlement"]').val(addr_json.data.settlement_with_type);
			$('[name="add_address_street"]').val(addr_json.data.street_with_type);
			if(addr_json.data.house_type=='д')
				$('[name="add_address_house"]').val(addr_json.data.house);
			if(addr_json.data.house_type=='влд')
				$('[name="add_address_mast"]').val(addr_json.data.house);
			if(addr_json.data.block_type=='к')
				$('[name="add_address_corp"]').val(addr_json.data.block);
			if(addr_json.data.block_type=='стр')
				$('[name="add_address_build"]').val(addr_json.data.block);
			$('[name="add_address_detail"]').val(addr_json.data.flat?((addr_json.data.flat_type?addr_json.data.flat_type+' ':'')+addr_json.data.flat):'');
			$('[name="add_address_map_lat"]').val(addr_json.data.geo_lat);
			$('[name="add_address_map_lon"]').val(addr_json.data.geo_lon);
			$('[name="add_address_full"]').val(addr_json.unrestricted_value);
		}
	});
	
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$(this).parents('form:first').find('[name="captcha"]').val('').focus();
	});
	
	$('.tr_link').on('click',function(){
		return window.open($(this).data('href'),'_blank');
	});
	
});
</script>
<?
}
?>
<script src="/js/jquery.df.js"></script>
<script src="/js/jquery.suggest_addr.js"></script>