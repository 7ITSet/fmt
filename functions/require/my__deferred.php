<?
defined ('_DSITE') or die ('Access denied');

/* $order=new order;
$sql=new sql;
$user=new user; */
global $order,$sql,$user;

if(!$order->getDeferredCart()){
	echo '<p class="main_products_list_null">У Вас пока нет отложенных товаров.<br>Отложить на потом товар можно в <a href="/catalog/" class="clean-filters underline">каталоге товаров</a> или на <span id="replace_link">странице товара</span>.</p>';
}
else{

	if(isset($_GET['success']))
		echo '<div class="form-alert success">Изменения успешно сохранены.</div>';
	if(isset($_GET['error']))
		echo '<div class="form-alert error">Произошла ошибка при сохранении данных.</div>';
?>
<div class="login_registration">
	<div class="login_container">
		<form id="login_registration_form" action="" method="post">
			<div class="login_authorization_form_input_container min_margin">
				<p>Информирование о статусах заказа и сделанных пользователем изменениях:</p>
				<div class="cb">
					<input name="statusorder_email" id="statusorder_email" type="checkbox" <?=($user->getInfo('m_users_accept_statusorder_email')?'checked ':'');?>value="1"/>
					<label for="statusorder_email" onselectstart="return false">e-mail письма</label>
				</div>
				<div class="cb">
					<input name="statusorder_sms" id="statusorder_sms" type="checkbox" <?=($user->getInfo('m_users_accept_statusorder_sms')?'checked ':'');?>value="1"/>
					<label for="statusorder_sms" onselectstart="return false">SMS сообщения</label>
				</div>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container min_margin">
				<p>Информирование о новых товарах, скидках и акциях:</p>
				<div class="cb">
					<input name="newsletter_email" id="newsletter_email" type="checkbox" <?=($user->getInfo('m_users_accept_newsletter_email')?'checked ':'');?>value="1"/>
					<label for="newsletter_email" onselectstart="return false">e-mail письма</label>
				</div>
				<div class="cb">
					<input name="newsletter_sms" id="newsletter_sms" type="checkbox" <?=($user->getInfo('m_users_accept_newsletter_sms')?'checked ':'');?>value="1"/>
					<label for="newsletter_sms" onselectstart="return false">SMS сообщения</label>
				</div>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<p>Уведомления в браузере</p>
				<div class="cb">
					<input name="notification" id="notification" type="checkbox" <?=($user->getInfo('m_users_accept_notification')?'checked ':'');?>value="1"/>
					<label for="notification" onselectstart="return false">Включить уведомления в браузере</label>
				</div>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<button type="submit" class="med_button orange" id="reg_submit">Сохранить изменения</button>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_change_subscriptions">
		</form>
	</div>
</div>
<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
	
});
</script>
<?
}
?>
<script>
$(document).ready(function(){
	if(localStorage.getItem('vg')){
		var vg=localStorage.getItem('vg').split('|'),
			good=vg.pop();
		if(good)
			$('#replace_link').html('<a href="/product/'+good+'/" class="clean-filters underline">странице товара</a>');
	}
});
</script>