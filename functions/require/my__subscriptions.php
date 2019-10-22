<?
defined ('_DSITE') or die ('Access denied');
/* $sql=new sql;
$user=new user; */
global $sql,$user;

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
				<p>Отмечая вышеуказанные поля, я принимаю <a href="/subscribe-adv-inf-conditions/" class="underline" target="_blank" style="border-bottom-color: rgba(85, 97, 121, 0.4);">условия подписки</a>.</p>
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
				<button type="submit" class="med_button grey" id="reg_submit">Сохранить изменения</button>
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
	function sendNotification(title, options) {
		// Проверим, поддерживает ли браузер HTML5 Notifications
		if ("Notification" in window) {
			// Проверим, есть ли права на отправку уведомлений
			if (Notification.permission === "granted") {
				// Если права есть, отправим уведомление
				var notification = new Notification(title, options);
				function clickFunc(){ alert('Пользователь кликнул на уведомление'); }
				notification.onclick = clickFunc;
			}
			// Если прав нет, пытаемся их получить
			else{
				if (Notification.permission !== 'denied') {
					Notification.requestPermission(function (permission) {
						// Если права успешно получены, отправляем уведомление
						if (permission === "granted"){
							var notification = new Notification(title, options);
						}
						else {
							alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
						}
					});
				}
			}
		}
	}
	$('#notification').on('change',function(){
		if($(this).prop('checked')){
			var self=$(this);
			if ("Notification" in window){
				if(Notification.permission === "granted"){
					sendNotification('Уведомления в браузере formetoo.ru', {
						body: 'Уведомления для интернет-магазина formetoo.ru успешно включены. Если требуется отключить уведомления — снимите галочку на этой странице и нажмите "Отклонить" в появившемся окне.',
						icon: '/img/logo_wo_text.png',
						dir: 'auto'
					});
					return true;
				}
				else{
					Notification.requestPermission(function (permission) {
						if (permission === "granted"){
							return true;
						}
						else {
							alert('Вы отклонили показ уведомлений. Для включения уведомлений нажмите "Разрешить" во всплывающем окне.');
							self.prop('checked',false);
							return false;
						}
					});
				}
			}
			else{
				self.prop('checked',false);
				alert('В Вашем браузере не доступна система уведомлений.');
				return false;
			}
		}
	});
});
</script>