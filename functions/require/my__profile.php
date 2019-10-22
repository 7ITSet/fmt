<?
defined ('_DSITE') or die ('Access denied');

global $order,$sql,$user;

//верификация e-mail (читаем ссылку из почты)
$email_confirm=$user->getInfo('m_users_email_confirm');
if(get('email_confirm')){
	global $e;
	$data['email_confirm']=array(null,null,null,32);
	array_walk($data,'check',true);
	
	if(!$e){
		if($user->confirmEmail($data['email_confirm'])){
			echo '<div class="form-alert success">Ваша электронная почта успешно верифицирована.</div>';
			$email_confirm=1;
		}
	}
}
//верификация телефона
$tel_confirm=$user->getInfo('m_users_tel_confirm');

if(!$email_confirm&&$user->getInfo('m_users_email'))
	echo '<div class="form-alert info">
		Для верификации e-mail перейдите по ссылке в письме, которые мы отправили на указанный Вами e-mail.<br/>
		<a href="#" id="open_popup_resend_email" class="dotted">Письмо не пришло?</a>
	</div>';
if(!$tel_confirm&&$user->getInfo('m_users_tel'))
	echo '<div class="form-alert info">
		Для верификации телефона введите код из SMS-сообщения: 
		<form id="confirm_tel_form">
			<input autocomplete="off" maxlength="5" type="text" placeholder="код из SMS, 5 цифр" id="confirm_tel_code" name="confirm_tel_code"/>
			<input type="hidden" name="token" value="'.$user->getInfo('cookies_token').'">
		</form><br/>
		<a href="#" id="open_popup_resend_sms" class="dotted">Не получили код?</a>
	</div>';
if(get('change_account')=='error')
	echo '<div class="form-alert error">Произошла ошибка во время сохранения данных аккаунта.</div>';
if(get('change_account')=='error_password')
	echo '<div class="form-alert error">Введён неверный текущий пароль.</div>';
if(get('change_account')=='success')
	echo '<div class="form-alert success">Изменения аккаунта успешно сохранены.</div>';
if(get('change_password')=='error')
	echo '<div class="form-alert error">Введён неверный текущий пароль.</div>';
if(get('change_password')=='success')
	echo '<div class="form-alert success">Пароль успешно изменён.</div>';	
if(1){
	$user_actions=$user->getUserActions();
	$captcha_account=isset($user_actions[9])&&sizeof($user_actions[9])>10?true:false;
?>
<div class="login_registration">
	<h2>Данные аккаунта</h2>
	<div class="login_container">
		<form id="account_form" action="/login/" method="post">
			<table class="no-border form-table">
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Имя пользователя</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<input type="text" name="name" autocomplete="off" placeholder="имя *" value="<?=$user->getInfo('m_users_name');?>">
						</div>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">E-mail</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<input type="text" name="email" autocomplete="off" placeholder="email" value="<?=$user->getInfo('m_users_email');?>">
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<?=($user->getInfo('m_users_email')?($email_confirm?'<span class="icon icon-check-green-circle"></span><span class="green">e-mail подтверждён</span>':'<span class="icon icon-check-red-circle"></span><span class="red">e-mail не подтверждён</span>'):'');?>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc" id="cnf">Мобильный телефон</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<input type="text" name="tel" autocomplete="off" placeholder="моб. телефон" value="<?=$user->getInfo('m_users_tel');?>">
						</div>
					</td>
					<td class="tel_confirm_info">
						<div class="login_authorization_form_input_container">
							<?=($user->getInfo('m_users_tel')?($tel_confirm?'<span class="icon icon-check-green-circle"></span><span class="green">номер подтверждён</span>':'<span class="icon icon-check-red-circle"></span><span class="red">номер не подтверждён</span>'):'');?>
						</div>
					</td>
				</tr>
				<?
				if($captcha_account){
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
					</td>
					<td>
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
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Пароль</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="icon icon-eye-close password_show" title="Нажмите, чтобы показать/скрыть пароль"></span>
							<input type="password" class="input_password ym-disable-keys" name="password" placeholder="подтвердите изменения *">
						</div>
					</td>
					<td></td>
				</tr>
			</table>
			<div class="clr"></div>

			<div class="login_authorization_form_input_container">
				<button type="submit" class="med_button grey">Сохранить изменения</button>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_change_account">
		</form>
	</div>
</div>
<div class="clr"></div>
<div class="login_registration">
	<h2>Сменить пароль</h2>
	<div class="login_container">
		<form id="password_form" action="/login/" method="post">
			<table class="no-border form-table">
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Текущий пароль</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="icon icon-eye-close password_show" title="Нажмите, чтобы показать/скрыть пароль"></span>
							<input type="password" class="input_password ym-disable-keys" name="password" placeholder="старый пароль *">
						</div>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="desc">Новый пароль</span>
						</div>
					</td>
					<td>
						<div class="login_authorization_form_input_container">
							<span class="icon icon-eye-close password_show" title="Нажмите, чтобы показать/скрыть пароль"></span>
							<input type="password" class="input_password ym-disable-keys" name="new_password" placeholder="новый пароль *">
						</div>
					</td>
					<td></td>
				</tr>
				<?
				if($captcha_account){
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
					</td>
					<td>
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
				<button type="submit" class="med_button grey">Сменить пароль</button>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_change_password">
		</form>
	</div>
</div>
<div class="clr"></div>
<div class="login_registration">
	<h2>Данные контрагентов</h2>
</div>
<div id="popup_resend" class="popup" style="width:24.2em;margin-left:-11.3em;top:45%;">
	<div class="popup_header">
		<p>Повторить отправку</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<form id="form_popup_resend">
		<div class="login_authorization_form_input_container short">
			<input type="text" name="captcha" placeholder="текст справа *" maxlength="7" autocomplete="off"/>
		</div>
		<div class="login_authorization_form_sep">
			<span class="icon icon-arrow-left"></span>
		</div>
		<div class="login_authorization_form_input_container">
			<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<div class="clr"><p></p></div>
		<div class="login_authorization_form_input_container" style="text-align:center;width:100%;">
			<button type="submit" class="med_button" id="popup_resend_submit">Отправить код ещё раз</button>
		</div>
	</form>
</div>
<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
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
	$('#jur').on('change',function(){
		if($(this).prop('checked'))
			$('.inn_container,.kpp_container,.org_name_container').show().find('input').prop('disabled',false);
		else $('.inn_container,.kpp_container,.org_name_container').hide().find('input').prop('disabled',true);
	});
	$('.password_show').on('click',function(){
		if($(this).siblings('.input_password').attr('type')=='password'){
			$(this).siblings('.input_password').attr('type','text');
			$(this).addClass('icon-eye-open').removeClass('icon-eye-close');
			$(this).next().focus();
		}
		else{
			$(this).siblings('.input_password').attr('type','password');
			$(this).addClass('icon-eye-close').removeClass('icon-eye-open');
			$(this).next().focus();
		}
	});
	$('[name="tel"]').mask('+7 999 999-99-99',{placeholder:'_'});
	$.validator.methods.tel=function(value,element) {
		return this.optional(element)||/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i.test(value);
	}
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
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
	
	//ВЕРИФИКАЦИЯ ТЕЛЕФОНА И ПОВТОРНАЯ ОТПРАВКА СООБЩЕНИЙ ДЛЯ ПРОВЕРКИ
	$('#open_popup_resend_sms').on('click',function(){
		resend='sms';
		$('#popup_resend_submit').text('Отправить SMS c кодом ещё раз').prop('disabled',false);
		$('#form_popup_resend img.captcha_img').triggerHandler('click');
		$('#form_popup_resend input[name="captcha"]').val('').prop('disabled',false);
		popup_show('#popup_resend',function(){
			$('#popup_resend input[name="captcha"]').focus();
		});
		return false;
	});
	$('#open_popup_resend_email').on('click',function(){
		resend='email';
		$('#popup_resend_submit').text('Отправить письмо ещё раз').prop('disabled',false);
		$('#form_popup_resend img.captcha_img').triggerHandler('click');
		$('#form_popup_resend input[name="captcha"]').val('').prop('disabled',false);
		popup_show('#popup_resend',function(){
			$('#popup_resend input[name="captcha"]').focus();
		});
		return false;
	});
	var resend='sms';
	//ПРОВЕРКА ВВЕДЁННОГО КОДА ИЗ SMS
	$('#confirm_tel_form').validate({
		rules:{
			confirm_tel_code:{
				required:true,
				digits:true,
				rangelength:[5,5],
				remote:{
					url: '/ajax/confirm_tel.php',
					type: 'post',
					data: {
						code:function(){
							return $('#confirm_tel_form [name="confirm_tel_code"]').val()
						},
						token:function(){
							return $('#confirm_tel_form [name="token"]').val()
						}
					},
					dataFilter: function(data) {
						if(data == 'OK') {
							//убираем предупреждение и меняем пометку у телефона
							$('#confirm_tel_form').parents('.form-alert:first').remove();
							$('td.tel_confirm_info').html('<span class="icon icon-check-green-circle"></span><span class="green">номер подтверждён</span>');
							return '"true"';
						}
						return false;
					}
				}
			}
		},
		messages:{
			confirm_tel_code:{
				remote:'Введён неверный код'
			}
		},
		submitHandler:function(form){
			return false;
		}
	});
	//запускаем проверку при первоначальном изменении при введении 5 символов
	$('#confirm_tel_code').on('keyup',function(){if($(this).val().length==5) $(this).blur();});
	
	
	//КАПЧА ДЛЯ ПОВТОРНОЙ ОТПРАВКИ СМС ИЛИ E-MAIL
	$('#form_popup_resend').validate({
		rules:{
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#form_popup_resend [name="captcha"]').val()
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
				remote:'Код неверный'
			}
		},
		submitHandler:function(form){
			$.post(
				'/ajax/resend_confirm.php',
				{
					captcha:$('#form_popup_resend [name="captcha"]').val(),
					type:resend
				},
				function(data){
					$('#popup_resend_submit').text('Сообщение отправлено').prop('disabled',true);
					$('#form_popup_resend input[name="captcha"]').prop('disabled',true);
				}
			);
			return false;
		}
	});
	
	
	//ФОРМА ДАННЫХ АККАУНТА
	$("#account_form").validate({
		rules:{
			password:{
				required:true,
				maxlength:50
			},
			email:{
				email:true,
				required:function(el){
					return $('#account_form [name="tel"]').val()?false:true;
				},
				remote:{
					url: '/ajax/check_email.php',
					type: 'post',
					data: {
						email:function(){
							return $('#account_form [name="email"]').val();
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
			tel:{
				required:function(el){
					return $('#account_form [name="email"]').val()?false:true;
				},
				tel: true,
				remote:{
					url: '/ajax/check_tel.php',
					type: 'post',
					data: {
						tel:function(){
							return $('#account_form [name="tel"]').val();
						}
					},
					dataFilter: function(data) {console.log(data);
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#account_form [name="captcha"]').val()
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
	$("#account_form").on('submit',function(){
		if(!$("#account_form").validate())
			return false;
	});
	
	//ФОРМА СМЕНЫ ПАРОЛЯ
	$("#password_form").validate({
		rules:{
			password:{
				required:true,
				maxlength:50
			},
			new_password:{
				required:true,
				maxlength:50
			}
		}
	});
	$("#password_form").on('submit',function(){
		if(!$("#password_form").validate())
			return false;
	});
	
	
	
	$("#login_registration_form").validate({
		rules:{
			name:{
				required:true,
				maxlength:180
			},
			email:{
				email:true,
				required:function(el){
					return $('#login_registration_form [name="tel"]').val()?false:true;
				},
				remote:{
					url: '/ajax/check_email.php',
					type: 'post',
					data: {
						email:function(){
							return $('#login_registration_form [name="email"]').val();
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
			tel:{
				required:function(el){
					return $('#login_registration_form [name="email"]').val()?false:true;
				},
				tel: true,
				remote:{
					url: '/ajax/check_tel.php',
					type: 'post',
					data: {
						tel:function(){
							return $('#login_registration_form [name="tel"]').val();
						}
					},
					dataFilter: function(data) {console.log(data);
						if(data == 'OK') {
							return '"true"';
						}
						return false;
					}
				}
			},
			inn:{
				required:function(el){
					return $('#login_registration_form [name="jur"]').prop('checked')?true:false;
				},
				digits:true,
				rangelength:[10,12],
				inn:true,
				remote:{
					url: '/ajax/check_inn.php',
					type: 'post',
					data: {
						inn:function(){
							return $('#login_registration_form [name="inn"]').val()
						},
						kpp:function(){
							return $('#login_registration_form [name="kpp"]').val()
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
			kpp:{
				required:function(el){
					return $('#login_registration_form [name="jur"]').prop('checked')
						?($('#login_registration_form [name="inn"]').val()&&$('#login_registration_form [name="inn"]').val().length==10
							?true
							:false
						)
						:false;
				},
				digits:true,
				rangelength:[9,9]	
			},
			org_name:{
				required:function(el){
					return $('#login_registration_form [name="jur"]').prop('checked')?true:false;
				}
			},
			password:{
				required:true,
				maxlength:50
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#login_registration_form [name="captcha"]').val()
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
			},
			inn:{
				remote:'ИНН уже есть в системе'
			}
		}
	});
	$("#login_registration_form").on('submit',function(){
		if(!$("#login_registration_form").validate())
			return false;
	});
	
	
	$('#login_registration_form [name="inn"]').on('keyup',function(){
		$('[name="org_name"]').val('');
		$('[name="kpp"]').val('');
		if(validINN($(this).val()))
			$.post(
				'/ajax/get_inn_info.php',
				{
					inn:$('#login_registration_form [name="inn"]').val()
				},
				function(data){
					if(data!="ERROR"){
						var firm=JSON.parse(data);
						$('[name="org_name"]').val(firm.suggestions[0].value?firm.suggestions[0].value:firm.suggestions[0].data.name.full_with_opf);
						$('[name="kpp"]').val(firm.suggestions[0].data.kpp);					
					}
				}
			);
	});
	
	
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$(this).parents('form:first').find('[name="captcha"]').val('').focus();
	});
	
});
</script>
<?
}
?>