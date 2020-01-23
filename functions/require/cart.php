<?
defined ('_DSITE') or die ('Access denied');

/* $order=new order;
$sql=new sql; */
global $order,$sql,$user;

$q='SELECT `value` FROM `formetoo_main`.`m_settings_cart` WHERE `key`="min_total_sum" LIMIT 1;';
$res=$sql->query($q);
$min_total_sum = (isset($res) && is_numeric($res[0]['value']) && $res[0]['value'] > 0) ? $res[0]['value'] : 0;

$uact=$user->getUserActions();
if(isset($uact[13])&&sizeof($uact[13])>50){
	$captcha_reg=true;
}
else $captcha_reg=false;


if($order->getCart()){
	$goods=array();
	foreach($order->getCart()->items as &$_item)
		$goods[]=$_item->product_id;
	$q='SELECT `id`,`slug`,`m_products_name_full`,`m_products_name`,`m_products_foto`,`measure_id`,`id_isolux` FROM `formetoo_main`.`m_products` WHERE `id` IN ('.implode(',',$goods).');';
	if($products=$sql->query($q,'id')){
		//ед. измерения
		$units=$sql->query('SELECT * FROM `formetoo_cdb`.`m_info_units`;','m_info_units_id');
		$q='SELECT * FROM `formetoo_main`.`m_products_attributes_list` 
			RIGHT JOIN `formetoo_main`.`m_products_attributes`
				ON `m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
			WHERE
				`m_products_attributes`.`m_products_attributes_product_id` IN ('.implode(',',$goods).') 
			ORDER BY `m_products_attributes_list_name`;';
		/* $q='SELECT * FROM `formetoo_main`.`m_products_attributes_list` 
			RIGHT JOIN (
				`formetoo_main`.`m_products_attributes`	 RIGHT JOIN
					`formetoo_main`.`m_products_attributes_values` ON `m_products_attributes`.`m_products_attributes_value`= `m_products_attributes_values`.`m_products_attributes_values_id`
			)
				
				ON `m_products_attributes_list`.`m_products_attributes_list_id`=`m_products_attributes`.`m_products_attributes_list_id`
			WHERE
				`m_products_attributes`.`m_products_attributes_product_id` IN ('.implode(',',$goods).')
			ORDER BY `m_products_attributes_list_name`;'; */
		if($attr=$sql->query($q,'m_products_attributes_product_id')){
			$attr_values=array();
			foreach($attr as $_attr)
				foreach($_attr as $__attr)
					if($__attr['m_products_attributes_list_type']!=2)
						$attr_values[]=$__attr['m_products_attributes_value'];
			if($attr_values)			
				$attr_values_all=$sql->query('SELECT * FROM `formetoo_main`.`m_products_attributes_values` WHERE `m_products_attributes_values_id` IN('.implode(',',$attr_values).');','m_products_attributes_values_id');
		}
	}

?>
<table class="table-cart">
	<thead>
		<tr>
			<th style="width:5%"></th>
			<th style="width:20%">Наименование</th>
			<th style="width:15%">Размеры позиции</th>
			<th style="width:10%;">Цена</th>
			<th style="width:15%;">Количество</th>
			<th style="width:9%;">Сумма</th>
			<th style="width:7%"></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="4" class="align-top">
				<div class="cart_total_dimensions">
					<div class="cart_total_dimensions_item">
						<span class="cart_total_dimensions_item_desc">Общий вес:</span>
						<span class="cart_total_dimensions_item_value weight"></span>
					</div>
					<div class="cart_total_dimensions_item">
						<span class="cart_total_dimensions_item_desc">Общий объём:</span>
						<span class="cart_total_dimensions_item_value volume"></span>
					</div>
					<div class="cart_total_dimensions_item">
						<span class="cart_total_dimensions_item_desc">Макс. длина:</span>
						<span class="cart_total_dimensions_item_value length"></span>
					</div>
				</div>
			</td>
			<td class="align-right align-top">
				<div class="cart_total_sum">
					<div class="cart_total_sum_cash">
						<span class="cart_total_sum_cash_desc">Итого:</span>
					</div>
					<!--<div class="cart_total_sum_bonus">
						</span><span class="cart_total_sum_bonus_desc">Бонусы за покупку:</span>
					</div>-->
				</div>
			</td>
			<td colspan="2" class="align-top">
				<div class="cart_total_sum">
					<div class="cart_total_sum_cash">
						<span class="cart_total_sum_cash_value cash"></span>
					</div>
					<!--<div class="cart_total_sum_bonus">
						<span class="cart_total_sum_bonus_value bonus">3 615,77 Б</span><br/>		
					</div>-->
				</div>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?
			$total_sum=0;
			$total_length=0;
			$total_weight=0;
			
			foreach($order->getCart()->items as $_item){
				$item_v['length']=0;
				$item_v['depth']=0;
				$item_v['width']=0;
				$item_v['weight']=0;
				$item_v['volume']=0;
				
				$unit=$units[$products[$_item->product_id][0]['measure_id']][0];
				$foto='';
				$products[$_item->product_id][0]['m_products_foto']=json_decode($products[$_item->product_id][0]['m_products_foto']);
				foreach($products[$_item->product_id][0]['m_products_foto'] as $_foto)
					if($_foto->main)
						$foto=$_foto->file;
				if(!$foto)
					foreach($products[$_item->product_id][0]['m_products_foto'] as $_foto){
						$foto=$_foto->file;
						break;
					}
				echo '
					<tr data-id="'.$_item->product_id.'" data-price='.$_item->product_price.'>
						<td>',
							($products[$_item->product_id][0]['id_isolux']
								? ($foto?'<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($products[$_item->product_id][0]['id_isolux'],0,2).'/SN'.$products[$_item->product_id][0]['id_isolux'].'/'.$foto.'_min.jpg" alt="'.$products[$_item->product_id][0]['m_products_name_full'].'"/>':'<img src="/foto/products/empty_foto.svg" alt="Фото товара отсутствует"/>')
								: ($foto?'<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/v/'.$products[$_item->product_id][0]['id'].'/'.$foto.'_min.jpg" alt="'.$products[$_item->product_id][0]['m_products_name_full'].'"/>':'<img src="/foto/products/empty_foto.svg" alt="Фото товара отсутствует"/>')
							),
						'</td>
						<td class="name"><a href="/product/'.$_item->product_id.'/">'.$products[$_item->product_id][0]['m_products_name'].'</a><br/><span class="grey">арт. '.substr($_item->product_id,0,4).'-'.substr($_item->product_id,4).'</span></td>
						<td class="alt_units">
							<ul class="list_dotts">';
				switch($unit['m_info_units_name']){
					case 'упак':
					case 'компл':
					case 'рул':
						foreach($attr[$_item->product_id] as $_attr){
							//кол-во в упаковке (шт)
							if($_attr['m_products_attributes_list_id']==3784943609)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Количество</span></div>
									<div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;шт</div>
								</li>';										
							//кол-во в упаковке (м2)
							if(in_array($_attr['m_products_attributes_list_id'],array(6447361156,6875679191,2233750374)))
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м<sup>2</sup></div>
								</li>';
							//кол-во в упаковке (м3)
							if($_attr['m_products_attributes_list_id']==3493624856)								
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м<sup>3</sup></div>
								</li>';
							//вес брутто
							if($_attr['m_products_attributes_list_id']==9139021748)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Вес брутто</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;кг</div>
								</li>';
						}
						break;
					case 'м2':
					case 'м':
						foreach($attr[$_item->product_id] as $_attr){
							//кол-во в упаковке (шт)
							if($_attr['m_products_attributes_list_id']==3784943609)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Количество</span></div>
									<div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;шт</div>
								</li>';
							//кол-во в упаковке (м3)
							if($_attr['m_products_attributes_list_id']==3493624856)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м<sup>3</sup></div>
								</li>';
							/* //длина
							if($_attr['m_products_attributes_list_id']==6920958743)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Длина</span></div>
									<div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м</div>
								</li>'; */
							//вес брутто
							if($_attr['m_products_attributes_list_id']==9139021748)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Вес брутто</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;кг</div>
								</li>';
						}
						break;
					case 'шт':
					case 'лист':
						foreach($attr[$_item->product_id] as $_attr){
							//кол-во в упаковке (м2)
							if(in_array($_attr['m_products_attributes_list_id'],array(6447361156,2233750374,6875679191))){
								$continue=false;
								foreach($attr[$_item->product_id] as $__attr)
									if($__attr['m_products_attributes_list_id']==6875679191&&$_attr['m_products_attributes_list_id']==2233750374)
										$continue=true;
								if($continue)
									continue;
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м<sup>2</sup></div>
								</li>';
							}
							//кол-во в упаковке (м3)
							if($_attr['m_products_attributes_list_id']==3493624856)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м<sup>3</sup></div>
								</li>';
							//длина
							if($_attr['m_products_attributes_list_id']==6920958743){
								if($_attr['m_products_attributes_list_unit']=='мм')
									echo '
									<li>
										<div class="list_dotts_name"><span class="list_dotts_name_text">Пог. м</span></div>
										<div class="list_dotts_value" data-volume-value="'.($_attr['m_products_attributes_value']*$_item->product_count/1000).'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.($_attr['m_products_attributes_value']*$_item->product_volume/1000).'"><span>'.($_attr['m_products_attributes_value']*$_item->product_count/1000).'</span>&nbsp;м</div>
									</li>';
								else echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Пог. м</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;'.$_attr['m_products_attributes_list_unit'].'</div>
								</li>';
							}
							//длина рулона (м) в обоях
							if($_attr['m_products_attributes_list_id']==4530177957)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Длина</span></div>
									<div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;м</div>
								</li>';
							//вес брутто
							if($_attr['m_products_attributes_list_id']==9139021748)
								echo '
								<li>
									<div class="list_dotts_name"><span class="list_dotts_name_text">Вес брутто</span></div>
									<div class="list_dotts_value" data-volume-value="'.$_attr['m_products_attributes_value']*$_item->product_count.'" data-volume-id="'.$_attr['m_products_attributes_list_id'].'" data-value="'.$_attr['m_products_attributes_value']*$_item->product_volume.'"><span>'.$_attr['m_products_attributes_value']*$_item->product_count.'</span>&nbsp;кг</div>
								</li>';
							//из размера 100х100 мм считаем площадь
							if($_attr['m_products_attributes_list_id']==3622535174){
								$_attr['m_products_attributes_value']=$attr_values_all[$_attr['m_products_attributes_value']][0]['m_products_attributes_values_value'];
								$_attr['m_products_attributes_value']=explode(' ',$_attr['m_products_attributes_value']);
								$un=isset($_attr['m_products_attributes_value'][1])?$_attr['m_products_attributes_value'][1]:0;
								$_attr['m_products_attributes_value']=explode('х',$_attr['m_products_attributes_value'][0]);
								if($un&&isset($_attr['m_products_attributes_value'][0])&&isset($_attr['m_products_attributes_value'][1]))
									switch($un){
										case 'мм':
											$sq=$_attr['m_products_attributes_value'][0]*$_attr['m_products_attributes_value'][1]/1000000;
											break;
										case 'м':
											$sq=$_attr['m_products_attributes_value'][0]*$_attr['m_products_attributes_value'][1];
											break;
										default:
											break;
									}
								if(isset($sq))
									echo '
									<li>
										<div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
										<div class="list_dotts_value" data-value="'.$sq*$_item->product_volume.'"><span>'.$sq*$_item->product_count.'</span>&nbsp;м</div>
									</li>';
							}
						}
						break;	
					default:
						break;
				}
				echo '		</ul>
						</td>
						<td>'.transform::price_o($_item->product_price).'&nbsp;<span class="symb_rouble">₽</span></td>
						<td>
							<div class="main_products_list_items_info_pay_count_inputs_pay_count_change">
								<span class="pay_count_minus" onselectstart="return false">–</span>
								<input autocomplete="off" type="text" name="pay_count" class="pay_count" data-unitvolume="'.$_item->product_volume.'" value="'.$_item->product_count.'"/>
								<span class="pay_count_plus" onselectstart="return false">+</span>
							</div>
							<span class="unit">'.$unit['m_info_units_name'].'</span>
						</td>
						<td class="strong item_sum" data-value="'.$_item->product_price*$_item->product_count.'">'.transform::price_o($_item->product_price*$_item->product_count).'&nbsp;<span class="symb_rouble">₽</span></td>
						<td><span class="icon icon-close item_remove">Удалить</span></td>
					</tr>';
			}
		?>
</tbody>
</table>
<div class="new_order">
	<div class="new_order_submit">
		<div class="button big_button disabled" data-href="/my/orders/" onselectstart="return false">Оформить заказ</div>
		<p><span class="red">Минимальная сумма заказа — 5 000&nbsp;<span class="symb_rouble">₽</span></span></p>
	</div>
	<?
	if($user->getInfo('m_users_name')=='') echo '
	<div class="new_order_quicksubmit">
		<div class="button big_button grey disabled" data-href="/my/orders/" onselectstart="return false">Купить без регистрации</div>
	</div>';
	?>
	
	<div class="clr"></div>
	<div class="new_order_promo_code">
		<span class="icon icon-dot-grey"></span>
		<span class="vert-sep"></span>
		<input name="promocode" type="text" placeholder="промокод" maxlength="10"/>
		<p style="line-height:1.4;text-align:left;font-size:.75em;margin-top:.4em;" id="promo_code_desc"><span class="grey"></span></p>
	</div>
</div>	
<div id="popup_quicksubmit" class="popup" style="width:22em;margin-left:-11.5em;top:30%;">
	<div class="popup_header">
		<p>Купить в один клик</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<div id="form_quicksubmit_done" class="form-center">
		<p><span class="icon icon-big-ok"></span></p>
		<p>Ваш заказ № <span class="form_quicksubmit_done_result"></span> оформлен. В ближайшее время с Вами свяжется менеджер для уточнения деталей.</p>
	</div>
	<div class="clr"></div>
	<form id="form_quicksubmit" action="/" method="post">
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="name" autocomplete="off" placeholder="имя *">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="email" autocomplete="off" placeholder="email">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="tel" autocomplete="off" placeholder="моб. телефон">
		</div>
		<div class="clr"></div>
<?
if($captcha_reg){
?>
		<div class="login_authorization_form_input_container">
			<input type="text" name="captcha" placeholder="текст с картинки *">
		</div>
		<div class="login_authorization_form_input_container">
			<img width="95" src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<div class="clr"></div>
<?
}
?>
		<div class="login_authorization_form_input_container form-center">
			<button type="submit" class="med_button" id="reg_submit">Отправить заказ</button>
		</div>
		<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
		<input type="hidden" name="handler" value="user_registration_quick">
		<input type="hidden" name="from_cart" value="1">
		<div class="clr"></div>
		<p><span class="small">Нажимая кнопку «Отправить заказ»:</span></p>
		<div class="login_authorization_form_input_container">
			<div class="cb">
				<input name="politic" id="politic" type="checkbox" checked value="1"/>
				<label for="politic" onselectstart="return false">Я принимаю условия <a href="/terms-of-sale/" class="underline" target="_blank">Пользовательского соглашения</a> и даю своё согласие Интернет-магазину на обработку моей персональной информации на условиях, определенных <a href="/personal-data-agreement/" class="underline" target="_blank">Политикой конфиденциальности</a>.</label>
			</div>
		</div>
	</form>
</div>
<style>
	#form_quicksubmit_done{
		display:none;
	}
	.form_quicksubmit_done_result{
		font-weight:700;
	}
</style>
<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<?
}
else echo '<p class="main_products_list_null">В Вашей корзине пока нет товаров.</p>'
?>
<script>
$(document).ready(function(){
	var promocode='';
	$('.new_order_promo_code input').on('keyup',function(){
		var self=$(this);
		if(self.val()!=promocode)
			$.get(
				'/ajax/check_promocode.php',
				{
					code:self.val()
				},
				function(data){
					$('#promo_code_desc').hide();
					switch(data){
						case 'SUCCESS':
							$('#promo_code_desc').show().find('span').html('Скидка активируется во время<br/>оформления заказа');
							localStorage.setItem('promocode',$('[name="promocode"]').val());
							self.siblings('.icon').addClass('icon-check-green').removeClass('icon-dot-grey').removeClass('icon-check-time').attr('title','Введён действующий промокод');;
							break;
						case 'ERROR_PROMOCODE_EXPIRED':
							$('#promo_code_desc').show().find('span').html('Промокод истёк или ещё<br/>не начал действовать');
							self.siblings('.icon').addClass('icon-check-time').removeClass('icon-dot-grey').removeClass('icon-check-green').attr('title','Срок действия промокода истёк или ещё не наступил');
							break;
						case 'ERROR_UNKNOWN_PROMOCODE':
							$('#promo_code_desc').show().find('span').html('Промокода нет в системе');
							self.siblings('.icon').addClass('icon-check-red').removeClass('icon-dot-grey').removeClass('icon-check-time').removeClass('icon-check-green').attr('title','Неизвестный промокод');
							break;
						case 'ERROR_UPDATE_CART':
						case 'ERROR_LIMIT_TRY_CODE':
							self.siblings('.icon').addClass('icon-dot-grey').removeClass('icon-check-green').removeClass('icon-check-time').removeClass('icon-check-red').attr('title','Превышен лимит количества попыток введения промокода');
							break;
						case 'ERROR_INPUT_DATA':
							self.siblings('.icon').addClass('icon-dot-grey').removeClass('icon-check-green').removeClass('icon-check-time').removeClass('icon-check-red').attr('title','Промокод должен состоять из 10 символов');
							break;
						default:
							break;
					}
				}
			);
		promocode=self.val();
	});
	$('.new_order_submit .button').on('click',function(){
		if($(this).hasClass('disabled')) return false;
		window.location=$(this).data('href');
		sessionStorage.setItem('activeorder','{"total_weight":'+total_weight+',"total_max_length":'+total_max_length+',"total_volume":'+total_volume+',"total_sum":'+total_sum+',"total_bonus":'+total_bonus+'}');
	});
	
	/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ, ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
	function normalizeNumb(numb){
		numb+="";
		numb=numb.replace(",",".");
		numb=numb.replace(/[^.0-9]/gim,"");
		numb=(numb*1).toFixed(4);
		return numb*1;
	};

	/* ИЗМЕНЕНИЕ КОЛ-ВА ТОВАРА В КОРЗИНЕ */
	$(document).on('click','.pay_count_plus',function(){
		var volume=$(this).prev().attr('data-unitvolume')*1,
			new_val=normalizeNumb(normalizeNumb($(this).prev().val())+volume),
			tr=$(this).parents('tr:first');
		$(this).prev().val(new_val);
		var change=new_val/volume;
		tr.find('.alt_units li').each(function(index,el){
			var def_val=$(el).find('.list_dotts_value').data('value');
			$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			$(el).find('.list_dotts_value').attr('data-volume-value',normalizeNumb(def_val*change));
		});
		tr.find('.item_sum')
			.html(($(this).prev().val()*tr.data('price')).toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;<span class="symb_rouble">₽</span>')
			.attr('data-value',$(this).prev().val()*tr.data('price'));
		cartUpdate(tr.data('id'),tr.find('.pay_count').val());	
	});
	$(document).on('click','.pay_count_minus',function(){
		var volume=$(this).next().attr('data-unitvolume')*1,
			new_val=normalizeNumb(normalizeNumb($(this).next().val())-volume),
			tr=$(this).parents('tr:first');
		$(this).next().val(new_val>=volume?new_val:volume);
		var calc_val=$(this).next().val()*1,
			change=calc_val/volume;
		tr.find('.alt_units li').each(function(index,el){
			var def_val=$(el).find('.list_dotts_value').data('value');
			$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			$(el).find('.list_dotts_value').attr('data-volume-value',normalizeNumb(def_val*change));
		});
		tr.find('.item_sum')
			.html(($(this).next().val()*tr.data('price')).toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;<span class="symb_rouble">₽</span>')
			.attr('data-value',$(this).next().val()*tr.data('price'));
		cartUpdate(tr.data('id'),tr.find('.pay_count').val());
	});
	$(document).on('change','.pay_count',function(){
		var val=normalizeNumb($(this).val()),
			volume=$(this).attr('data-unitvolume')*1,
			tr=$(this).parents('tr:first');
		if(val%volume!=0)
			val=Math.ceil(val/volume)*volume;
		$(this).val(normalizeNumb(val>=volume?val:volume));
		var calc_val=$(this).val()*1,
			change=calc_val/volume;
		tr.find('.alt_units li').each(function(index,el){
			var def_val=$(el).find('.list_dotts_value').data('value');
			$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			$(el).find('.list_dotts_value').attr('data-volume-value',normalizeNumb(def_val*change));
		});
		tr.find('.item_sum')
			.html(($(this).val()*tr.data('price')).toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;<span class="symb_rouble">₽</span>')
			.attr('data-value',$(this).val()*tr.data('price'));
		cartUpdate(tr.data('id'),tr.find('.pay_count').val());
	});
	$(document).on('mouseup','.item_remove',function(){
		var tr=$(this).parents('tr:first'),
			id=tr.data('id');
		tr.fadeOut(200,function(){
			$(this).remove();
			cartUpdate(id,-1);
		});
		
	});
	//КОРЗИНА
	var total_weight=0,
		total_max_length=0,
		total_volume=0,
		total_sum=0,
		total_bonus=0;
	cartUpdate=function(p,c){
		total_weight=0;
		total_max_length=0;
		total_volume=0;
		total_sum=0;
		total_bonus=0;
		$('.list_dotts_value').each(function(index,el){
			//общий вес
			if($(el).data('volume-id')=='9139021748')
				total_weight+=$(el).attr('data-volume-value')*1;
			//общий объём
			if($(el).data('volume-id')=='3493624856')
				total_volume+=$(el).attr('data-volume-value')*1;
			//макс. длина
			if($(el).data('volume-id')=='6920958743')
				total_max_length=$(el).attr('data-value')*1>total_max_length?$(el).attr('data-value')*1:total_max_length;
			//сумма
		});
		$('.table-cart tbody tr').each(function(index,el){
			total_sum+=$(el).find('.item_sum').attr('data-value')*1;
		});
		console.log(total_weight);
		$('.cart_total_dimensions_item .weight').html(total_weight.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;кг');
		$('.cart_total_dimensions_item .volume').html(total_volume.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;м<sup>3</sup>');
		$('.cart_total_dimensions_item .length').html(total_max_length.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;м');
		$('.cart_total_sum .cash').html(total_sum.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;<span class="symb_rouble">₽</span>');
		
		//обновление корзины на сервере, если передан продукт и кол-во
		if(p&&c){
			var self=$(this);
			$.post(
				'/ajax/add_cart.php',
				{
					product_id:p,
					product_count:c,
					update:true
				},
				function(data){
					if(data!='ERROR_INPUT_DATA'){
						try{
							data=$.parseJSON(data)
							$('.nav_cart .desc').html(data.sum.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;р.');
							$('.nav_cart_size').text(data.items.length).addClass('active');
							self.text('Добавлен в корзину').addClass('success');
							$('.nav_cart').addClass('success');
							setTimeout(function(){
								$('.nav_cart').removeClass('success');
							},100);
							cart=data;
						}
						catch(e){
							$('.nav_cart_size').text(data.items.length).removeClass('active');
						}
					}
					else{
						$('.nav_cart_size').text(data.items.length).removeClass('active');
					}
				}
			);
		}
		if(total_sum > <?=$min_total_sum;?> || <?=$min_total_sum;?> == 0){
			$('.new_order_submit .button,.new_order_quicksubmit .button').removeClass('disabled');
			$('.new_order_submit p').css('visibility','hidden');
		}
		else{
			$('.new_order_submit .button,.new_order_quicksubmit .button').addClass('disabled');
			$('.new_order_submit p').css('visibility','visible');
		}
	}
	cartUpdate();

	//ПОПАП ПОКУПКИ БЕЗ РЕГИСТРАЦИИ
	$(document).on('click','.new_order_quicksubmit .button',function(){
		if($(this).hasClass('disabled')) return false;
		popup_show('#popup_quicksubmit',function(){
			$('#popup_quicksubmit input:text:first').focus();
		});
		return false;
	});

	$('[name="tel"]').mask('+7 999 999-99-99',{placeholder:'_'});
	$('[name="tel"]').on('click',function(){
		if($(this).val()=='+7 ___ ___-__-__')
			$(this).setCursorPosition(3);
	});
	$.validator.methods.tel=function(value,element) {
		return this.optional(element)||/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i.test(value);
	}
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
	}
	//отправка формы быстрого заказа
	$("#form_quicksubmit").on('submit',function(){
		if(!$("#form_quicksubmit").valid())
			return false;
		$.post(
			'/ajax/add_order.php',
			{
				name:$('#form_quicksubmit [name="name"]').val(),
				email:$('#form_quicksubmit [name="email"]').val(),
				tel:$('#form_quicksubmit [name="tel"]').val(),
				politic:$('#form_quicksubmit [name="politic"]:checked').val(),
				token:$('#form_quicksubmit [name="token"]').val()
			},
			function(data){
				if(data.length==10)
					$('#form_quicksubmit').fadeOut(100,function(){$(this).remove()});
					$('.form_quicksubmit_done_result').text(data);
					$('#form_quicksubmit_done').fadeIn(100);
					$('#form_quicksubmit_done span.icon').on('click',function(){
						$(this).parents('.popup:first').find('.icon-close').on('click',function(){
							location.reload();
						});
						$(this).parents('.popup:first').find('.icon-close').triggerHandler('click');
					});
			}
		);
		$("#form_quicksubmit button").prop('disabled',true);
		return false;
	});
	//валидация формы быстрого заказ
	$("#form_quicksubmit").validate({
		rules:{
			name:{
				required:true,
				maxlength:180
			},
			email:{
				email:true,
				required:function(el){
					return $('#form_quicksubmit [name="tel"]').val()?false:true;
				}
			},
			tel:{
				required:function(el){
					return $('#form_quicksubmit [name="email"]').val()?false:true;
				},
				tel: true
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#form_quicksubmit [name="captcha"]').val()
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
			},
			email:{
				remote:'E-mail уже есть в системе'
			},
			tel:{
				remote:'Телефон уже есть в системе'
			}
		}
	});
	$('#politic').on('change',function(){
		if(!$(this).prop('checked'))
			$('#reg_submit').prop('disabled',true);
		else $('#reg_submit').prop('disabled',false);
	});
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$('[name="captcha"]').val('');
		$(this).parents('form:first').find('[name="captcha"]').focus();
	});
});
</script>