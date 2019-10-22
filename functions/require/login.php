<?
defined ('_DSITE') or die ('Access denied');

global $order,$sql,$user;

if($user->getInfo('m_users_name'))
	header('Location: /my/');
else{
	$uact=$user->getUserActions();
	$captcha_auth=isset($uact[7])&&sizeof($uact[7])>5?true:false;
	$captcha_reg=true;
	
	if($auth=get('auth'))
		switch($auth){
			case 'error_login':
				echo '<div class="form-alert error">Пользователь с таким телефоном/email не зарегистрирован в интернет-магазине.</div>';
				break;
			case 'error_password':
				echo '<div class="form-alert error">Пароль к учётной записи не совпадает с указанным ранее.</div>';
				break;
			case 'error_block':
				echo '<div class="form-alert error">Пользователь заблокирован. Пожалуйста, обратитесь к нам по адресу: <a href="mailto:tech@formetoo.ru" class="underline">tech@formetoo.ru</a> для решения проблемы.</div>';
				break;	
			case 'error':
				echo '<div class="form-alert error">Произошла ошибка во время авторизации. Пожалуйста, обратитесь к нам по адресу: <a href="mailto:tech@formetoo.ru" class="underline">tech@formetoo.ru</a> для решения проблемы.</div>';
				break;
		}
?>
<div class="login_authorization">
	<h2>Авторизация</h2>
	<div class="login_container">
		<form id="login_authorization_form" action="/" method="post" novalidate="novalidate">
			<div class="login_authorization_form_input_container">
				<input type="text" name="email" autocomplete="off" placeholder="email">
			</div>
			<div class="login_authorization_form_sep">
				или
			</div>
			<div class="login_authorization_form_input_container">
				<input type="text" name="tel" autocomplete="off" placeholder="моб. телефон">
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<span class="icon icon-eye-close password_show" title="Нажмите, чтобы показать/скрыть пароль"></span>
				<input type="password" class="input_password ym-disable-keys" name="password" autocomplete="off" placeholder="пароль *">
			</div>
			<div class="clr"></div>
	<?
	if($captcha_auth){
	?>
			<div class="login_authorization_form_input_container">
				<input type="text" name="captcha" placeholder="текст с картинки *">
			</div>
			<div class="login_authorization_form_sep">
				<span class="icon icon-arrow-left"></span>
			</div>
			<div class="login_authorization_form_input_container">
				<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
			</div>
			<div class="clr"></div>
	<?
	}
	?>
			<div class="login_authorization_form_input_container">
				<button type="submit" class="med_button">Войти</button>
			</div>
			<div class="login_authorization_form_input_container">
				<a href="#" class="dotted">Забыли пароль?</a>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_authorization">
			<input type="hidden" name="from_cart" value="">
		</form>
		<div class="clr"></div>
		<div class="login_authorization_social" style="display:none;">
			<h3>Авторизация через соцсети</h3>
			<div class="login_authorization_social_container">
				<div class="login_authorization_social_item login_authorization_social_ok" title="Авторизоваться через Одноклассники"></div>
				<div class="login_authorization_social_item login_authorization_social_vk" title="Авторизоваться через ВКонтакте"></div>
				<div class="login_authorization_social_item login_authorization_social_facebook" title="Авторизоваться через Facebook"></div>
				<div class="login_authorization_social_item login_authorization_social_twitter" title="Авторизоваться через Twitter"></div>
				<div class="login_authorization_social_item login_authorization_social_yandex" title="Авторизоваться через Яндекс"></div>
				<div class="login_authorization_social_item login_authorization_social_googleplus" title="Авторизоваться через Google"></div>
				<div class="login_authorization_social_item login_authorization_social_mailru" title="Авторизоваться через Mail.Ru"></div>
			</div>
		</div>
	</div>
</div>
<div class="login_registration">
	<h2>Регистрация</h2>
	<div class="login_container">
		<form id="login_registration_form" action="/" method="post">
			<div class="login_authorization_form_input_container">
				<input type="text" name="name" autocomplete="off" placeholder="имя *">
			</div>
			<div class="login_authorization_form_sep">
				&nbsp;
			</div>
			<div class="login_authorization_form_input_container">
				<div class="cb">
					<input name="jur" id="jur" type="checkbox" value="1"/>
					<label for="jur" onselectstart="return false">Я — представитель юр. лица или ИП</label>
				</div>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<input type="text" name="email" autocomplete="off" placeholder="email">
			</div>
			<div class="login_authorization_form_sep">
				&nbsp;
			</div>
			<div class="login_authorization_form_input_container short inn_container" style="display:none">
				<input type="text" name="inn" autocomplete="off" placeholder="ИНН *" maxlength="12" disabled>
			</div>
			<div class="login_authorization_form_input_container short kpp_container" style="display:none">
				<input type="text" name="kpp" autocomplete="off" placeholder="КПП" maxlength="9" disabled>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<input type="text" name="tel" autocomplete="off" placeholder="моб. телефон">
			</div>
			<div class="login_authorization_form_sep">
				&nbsp;
			</div>
			<div class="login_authorization_form_input_container org_name_container" style="display:none">
				<input type="text" name="org_name" autocomplete="off" maxlength="90" placeholder="наименование *, автозаполнение" disabled>
			</div>
			<div class="clr"></div>
			<div class="login_authorization_form_input_container">
				<span class="icon icon-eye-close password_show" title="Нажмите, чтобы показать/скрыть пароль"></span>
				<input type="password" class="input_password ym-disable-keys" name="password" placeholder="пароль *">
			</div>
			<div class="login_authorization_form_sep">
				&nbsp;
			</div>
			<div class="login_authorization_form_input_container nds_container" style="display:none">
				<div class="cb">
					<input name="nds" id="nds" type="checkbox" value="1"/>
					<label for="nds" onselectstart="return false">Работаем с НДС</label>
				</div>
			</div>
			<div class="clr"></div>
	<?
	if($captcha_reg){
	?>
			<div class="login_authorization_form_input_container">
				<input type="text" name="captcha" placeholder="текст с картинки *">
			</div>
			<div class="login_authorization_form_sep">
				<span class="icon icon-arrow-left"></span>
			</div>
			<div class="login_authorization_form_input_container">
				<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
			</div>
			<div class="clr"></div>
	<?
	}
	?>
			<div class="login_authorization_form_input_container">
				<button type="submit" class="med_button" id="reg_submit">Зарегистрироваться</button>
			</div>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_registration">
			<input type="hidden" name="from_cart" value="">
			<div class="clr"></div>
			<p><span class="small">Нажимая кнопку «Зарегистрироваться»:</span></p>
			<div class="login_authorization_form_input_container">
				<div class="cb">
					<input name="politic" id="politic" type="checkbox" checked value="1"/>
					<label for="politic" onselectstart="return false">Я принимаю <a href="/terms-of-sale/" class="underline" target="_blank">условия продажи товаров</a> и даю своё согласие Интернет-магазину на обработку моей персональной информации на условиях, определенных <a href="/personal-data-agreement/" class="underline" target="_blank">Политикой конфиденциальности</a>.</label>
				</div>
				<div class="cb">
					<input name="newsletter" id="newsletter" type="checkbox" value="1"/>
					<label for="newsletter" onselectstart="return false">Я принимаю <a href="/subscribe-adv-inf-conditions/" class="underline" target="_blank" style="border-bottom-color: rgba(85, 97, 121, 0.4);">условия подписки</a> и согласен получать SMS и email информационно-рекламные сообщения о новых товарах, скидках и акциях Интернет-магазина.</label>
				</div>
			</div>
			<p style="width:40em;"><span class="small">Регистрация на сайте позволит хранить и просматривать историю заказов, получать персональные скидки и предложения, участвовать в бонусной программе.<span></p>
		</form>
	</div>
</div>
<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
	if(sessionStorage.getItem('activeorder')){
		var order_detail=$.parseJSON(sessionStorage.getItem('activeorder'));
		if(order_detail.total_weight>0)
			$('[name="from_cart"]').val(1);
	}
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
			$('.inn_container,.kpp_container,.org_name_container,.nds_container').show().find('input').prop('disabled',false);
		else $('.inn_container,.kpp_container,.org_name_container,.nds_container').hide().find('input').prop('disabled',true);
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
	$("#login_authorization_form").validate({
		rules:{
			email:{
				email:true,
				required:function(el){
					return $('#login_authorization_form [name="tel"]').val()?false:true;
				}
			},
			tel:{
				required:function(el){
					return $('#login_authorization_form [name="email"]').val()?false:true;
				},
				tel: true
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
							return $('#login_authorization_form [name="captcha"]').val()
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
		submitHandler: function(form) {
			$('#login_authorization_form button[type=submit]').prop('disabled',true);
			form.submit();
		}
	});
	$("#login_authorization_form").on('submit',function(){
		if(!$("#login_authorization_form").validate())
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
		},
		submitHandler: function(form) {
			$('#login_registration_form button[type=submit]').prop('disabled',true);
			form.submit();
		}
	});
	$('#politic').on('change',function(){
		if(!$(this).prop('checked'))
			$('#reg_submit').prop('disabled',true);
		else $('#reg_submit').prop('disabled',false);
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
		$('[name="captcha"]').val('');
		$(this).parents('form:first').find('[name="captcha"]').focus();
	});
	$('.login_authorization').css('min-height',$('.login_registration').height());
	
});
</script>
<?
}
?>