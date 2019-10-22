<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user,$G;

if(get('send')=='error')
	echo '<div class="form-alert error">При отправке отзыва произошла ошибка. Пожалуйста, обратитесь к нам по телефону или email.</div>';
if(get('send')=='success')
	echo '<div class="form-alert success">Ваш отзыв успешно отправлен на модерацию.</div>';	
?>
<p class="main_products_list_null">Пока никто не оставил отзыва о нашей работе.<br>Вы можете быть первым, <a id="add_review1" href="#" class="dotted">оставив свой отзыв</a>.</p>
<div id="popup_add_review1" class="popup" style="width:25em;margin-left:-16.5em;top:30%;">
	<div class="popup_header">
		<p>Добавить отзыв</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<form id="review1_form" action="/about/feedback/" method="post" novalidate="novalidate">
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="name" autocomplete="off" placeholder="<?=$user->getInfo('m_users_name')?$user->getInfo('m_users_name'):'ваше имя *';?>">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="email" autocomplete="off" placeholder="<?=$user->getInfo('m_users_email')?$user->getInfo('m_users_email'):'email *';?>">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="city" autocomplete="off" placeholder="<?=$G['CITY']['m_info_city_name_city_im']?$G['CITY']['m_info_city_name_city_im']:'город *';?>">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center" style="width:100%;">
			<textarea name="comment" rows="4" maxlength="600" placeholder="текст отзыва *"></textarea>
		</div>
		<?if(!$user->getInfo('m_users_name')){?>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container middle">
			<input type="text" name="captcha" placeholder="текст с картинки *" autocomplete="off"/>
		</div>
		<div class="login_authorization_form_sep">
			<span class="icon icon-arrow-left"></span>
		</div>
		<div class="login_authorization_form_input_container">
			<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<?}?>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<button type="submit" class="med_button orange" id="send">Отправить отзыв</button>
			<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
			<input type="hidden" name="handler" value="user_review_im">
		</div>
		<?if(!$user->getInfo('m_users_name')){?>
		<div class="clr"></div>
		<p>Нажимая кнопку «Отправить отзыв»:</p>
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
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
	}
	$("#review1_form").validate({
		rules:{
			name:{
				required:true,
				maxlength:150
			},
			email:{
				email:true,
				required:true
			},
			comment:{
				required:true,
				maxlength:10000
			},
			city:{
				required:true,
				maxlength:80
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#review1_form [name="captcha"]').val()
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
			$('#review1_form button[type=submit]').prop('disabled',true);
			form.submit();
		}
	});
	$("#review1_form #send").on('click',function(){
		$('#review1_form input:not([name=captcha])').each(function(index,el){
			if($(el).attr('placeholder')&&$(el).attr('placeholder').indexOf(' *')===false&&!$(el).val())
				$(el).val($(el).attr('placeholder'));
		});
	});
	$("#review1_form").on('submit',function(){
		if(!$("#review1_form").validate())
			return false;
	});
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$('[name="captcha"]').val('');
	});	
	$('#review1_form #politic').on('change',function(){
		if(!$(this).prop('checked'))
			$('#send').prop('disabled',true);
		else $('#send').prop('disabled',false);
	});
	
	//показ формы добавления отзыва
	$('#add_review1').on('click',function(){
		popup_show('#popup_add_review1',function(){
			$('#popup_add_review1 input:text:first').focus();
		});
		return false;
		
	});
});
</script>