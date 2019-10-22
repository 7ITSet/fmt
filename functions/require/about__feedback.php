<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user;

$user_actions=$user->getUserActions();
$captcha_feeback=isset($user_actions[17])&&sizeof($user_actions[17])>5?true:true;
if(get('send')=='error')
	echo '<div class="form-alert error">При отправке сообщения произошла ошибка. Пожалуйста, обратитесь к нам по телефону или email.</div>';
if(get('send')=='success')
	echo '<div class="form-alert success">Ваше сообщение успешно отправлено.</div>';	
?>
<div class="login_container">
	<form id="feedback_form" action="/about/feedback/" method="post" novalidate="novalidate">
		<table class="no-border form-table" style="width:60%">
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Ваше имя</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<input type="text" name="name" autocomplete="off" placeholder="<?=$user->getInfo('m_users_name');?>">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Email *</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<input type="text" name="email" autocomplete="off" placeholder="<?=$user->getInfo('m_users_email');?>">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Телефон *</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<input type="text" name="tel" autocomplete="off" placeholder="<?=$user->getInfo('m_users_tel');?>">
					</div>
				</td>
			</tr>
			<tr>
				<td class="top">
					<div class="login_authorization_form_input_container">
						<span class="desc">Сообщение *</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container" style="width:100%;">
						<textarea name="comment" rows="4" maxlength="600"></textarea>
					</div>
				</td>
			</tr>
			<?
			if($captcha_feeback){
			?>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Анти-робот *</span>
					</div>
				</td>
				<td>
					<div class="login_authorization_form_input_container middle">
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
				<td></td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<button type="submit" class="med_button orange" id="send">Отправить сообщение</button>
						<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
						<input type="hidden" name="handler" value="user_feedback">
					</div>
				</td>
			</tr>
		</table>
		<div class="clr"></div>

		<?if(!$user->getInfo('m_users_name')){?>
		<div class="clr"></div>
		<p>Нажимая кнопку «Отправить сообщение»:</p>
		<div class="login_authorization_form_input_container">
			<div class="cb">
				<input name="politic" id="politic" type="checkbox" checked value="1"/>
				<label for="politic" onselectstart="return false">Я принимаю условия <a href="/terms-of-sale" class="underline" target="_blank">Публичной оферты</a> и даю своё согласие Интернет-магазину на обработку моей персональной информации на условиях, определенных <a href="/personal-data-agreement" class="underline" target="_blank">Политикой конфиденциальности</a>.</label>
			</div>
		</div>
		<?}?>
	</form>
</div>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
	$('[name="tel"]').mask('+7 999 999-99-99',{placeholder:'_'});
	$.validator.methods.tel=function(value,element) {
		return this.optional(element)||/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i.test(value);
	}
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
	}
	$("#feedback_form").validate({
		rules:{
			email:{
				email:true,
				required:function(el){
					return $('#feedback_form [name="tel"]').val()?false:true;
				}
			},
			tel:{
				required:function(el){
					return $('#feedback_form [name="email"]').val()?false:true;
				},
				tel: true
			},
			comment:{
				required:true,
				maxlength:1000
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#feedback_form [name="captcha"]').val()
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
		}
	});
	$("#feedback_form #send").on('click',function(){
		$('#feedback_form input:not([name=captcha])').each(function(index,el){
			if($(el).attr('placeholder')&&!$(el).val())
				$(el).val($(el).attr('placeholder'));
		});
	});
	$("#feedback_form").on('submit',function(){
		if(!$("#feedback_form").validate())
			return false;
	});
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$('[name="captcha"]').val('');
	});	
	$('#feedback_form #politic').on('change',function(){
		if(!$(this).prop('checked'))
			$('#send').prop('disabled',true);
		else $('#send').prop('disabled',false);
	});
});
</script>