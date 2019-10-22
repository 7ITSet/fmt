<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user,$G;

$user_actions=$user->getUserActions();
$captcha_return=isset($user_actions[17])&&sizeof($user_actions[17])>5?true:false;
if(get('send')=='error')
	echo '<div class="form-alert error">При отправке сообщения произошла ошибка. Пожалуйста, обратитесь к нам по телефону или email.</div>';
if(get('send')=='success')
	echo '<div class="form-alert success">Ваше сообщение успешно отправлено.</div>';	
?>
<h2>Форма рекламации</h2>
<p>Все поля нижеприведённой формы обязательны для заполнения.</p>
<div class="login_container">
	<form id="return_form" action="/info/return/" method="post" novalidate="novalidate">
		<table class="no-border form-table top">
			<tr>
				<th width="15%"></th>
				<th width="25%"></th>
				<th width="60%"></th>
			</tr>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">ФИО</span>
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
						<span class="desc">Email</span>
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
						<span class="desc">Телефон</span>
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
						<span class="desc">Адрес проживания</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<textarea name="address" rows="3"></textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Номер заказа</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container">
						<input type="text" name="order" autocomplete="off" placeholder="10-значный номер">
					</div>
				</td>
			</tr>
			<tr>
				<td class="top">
					<div class="login_authorization_form_input_container">
						<span class="desc">Содержание рекламации</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container max">
						<textarea name="comment" rows="5"></textarea>
					</div>
				</td>
			</tr>
			<?
			if($captcha_return){
			?>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Анти-робот</span>
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
						<button type="submit" class="med_button orange" id="send">Отправить</button>
						<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
						<input type="hidden" name="handler" value="user_feedbackReturn">
					</div>
				</td>
			</tr>
			<?if(!$user->getInfo('m_users_name')){?>
			<tr>
				<td>
					<div class="login_authorization_form_input_container">
						<span class="desc">Нажимая кнопку «Отправить»:</span>
					</div>
				</td>
				<td colspan="2">
					<div class="login_authorization_form_input_container long">
						<div class="cb">
							<input name="politic" id="politic" type="checkbox" checked value="1"/>
							<label for="politic" onselectstart="return false">Я принимаю условия <a href="/terms-of-sale" class="underline" target="_blank">Публичной оферты</a> и даю своё согласие Интернет-магазину на обработку моей персональной информации на условиях, определенных <a href="/personal-data-agreement" class="underline" target="_blank">Политикой конфиденциальности</a>.</label>
						</div>
					</div>
				</td>
			</tr>
			<?}?>
		</table>
	</form>
</div>
<div class="clr"></div>
<h2>Права потребителя</h2>
<p>В соответствии с нормами действующего законодательства Российской Федерации, регулирующего вопросы по защите прав потребителей, при соблюдении определенных данным законодательством условий, потребитель вправе потребовать от продавца (изготовителя):</p>
<ul>
	<li>Заменить товар ненадлежащего качества;</li>
	<li>Обменять не подошедший по каким-либо характеристикам товар надлежащего качества на другой товар в течение 7 дней со дня передачи товара (Закон РФ «О защите прав потребителей» Статья 26.1. п.4 Дистанционный способ продажи товара);</li>
	<li>Отказаться от исполнения договора купли-продажи и, возвратив товар ненадлежащего качества, потребовать возврата уплаченной за товар денежной суммы (в течение 15 дней со дня передачи товара) (Закон РФ «О защите прав потребителей» Статья 18 Права потребителя при обнаружении в товаре недостатков);</li>
	<li>При отказе потребителя от&nbsp;товара продавец должен возвратить ему денежную сумму, уплаченную потребителем по&nbsp;договору, за&nbsp;исключением расходов продавца на&nbsp;доставку, не&nbsp;позднее чем через&nbsp;десять дней со дня предъявления потребителем соответствующего требования;</li>
</ul>
<p>При сдаче товара нужно предоставить документы:</p>
<ol>
	<li>Договор купли-продажи;</li>
	<li>Кассовый чек;</li>
	<li>Гарантийный талон;</li>
	<li>Акт авторизованного сервисного центра (при возврате товара ненадлежащего качества);</li>
</ol>
<p>Решение вопросов, связанных с гарантийным ремонтом товара, осуществляется Авторизованными сервисными центрами, адреса и телефоны которых указаны в гарантийных талонах на товар.</p>

<h2>Обмен товара надлежащего качества</h2>
<p>Потребитель вправе обменять у продавца непродовольственный товар надлежащего качества. При дистанционном способе продажи, срок вашего обращения для обмена товара или возврата денежных средств при отказе от товара надлежащего качества ограничен — в течении 7 дней со дня передачи товара потребителю (ЗоЗПП Статья 26.1. Дистанционный способ продажи товара).</p>
<p>Решение вопросов, связанных с гарантийным ремонтом товара, осуществляется Авторизованными сервисными центрами, адреса и телефоны которых указаны в гарантийных талонах на товар.</p>

<h2>Замена товара ненадлежащего качества</h2>
<p>В случае обнаружения покупателем недостатков в товаре и предъявления требований о его замене продавец обязан заменить такой товар на новый. Если в момент предъявления данного требования у продавца отсутствует необходимый для замены товар, замена должна быть проведена в течение месяца со дня предъявления такого требования.</p>
<p>Замена технически сложных товаров, перечень которых утвержден Постановлением Правительства Российской Федерации от 10.11.2011 № 924 производится продавцом исключительно при обнаружении существенных недостатков товара. Под существенным подразумевается неустранимый недостаток или недостаток, который не может быть устранен без несоразмерных расходов или затрат времени, или выявляется неоднократно, или проявляется вновь после его устранения, или другие подобные недостатки.</p>
<p>Основанием для произведения продавцом замены товара являются соответствующая рекламация и акт авторизованного сервисного центра о наличии дефекта в товаре, его неремонтопригодности либо о произведенном неоднократном ремонте. Для сокращения сроков рассмотрения рекламации просим вас подробно указать, какой именно дефект содержит возвращаемый вами товар.</p>
<p>В случае если товар был в&nbsp;употреблении, он принимается в&nbsp;момент обращения на&nbsp;проверку подлинности брака. Проверка проводится в&nbsp;срок до&nbsp;10 календарных дней. В&nbsp;случае подтверждения брака, возврат денежных средств Покупателю осуществляется в&nbsp;соответствии с&nbsp;Законодательством РФ</p>
<p>В случае если подлинность брака не&nbsp;подтверждается, денежные средства Покупателю не&nbsp;возвращаются</p>

<h2>Общая информация</h2>
<p>При получении товара заказчик должен проверить его на отсутствие механических повреждений. В соответствии со ст. 459 ч.2 ГК РФ (также ст.211 ГК РФ), претензии по внешнему виду и комплектности доставленного покупателю товара можно предъявить только до момента передачи ему товара продавцом.</p>
<p>Продавец передает покупателю</p>
<ul>
	<li>Товар</li>
	<li>Кассовый чек (при оплате за наличный расчет)</li>
	<li>Счет, УПД (при оплате по безналичному расчету)</li>
	<li>Гарантийный талон (если иные способы активации гарантии не предусмотрены)</li>
</ul>
<p>Приобретение товара с доставкой не дает покупателю права требования транспортировки купленного товара в целях гарантийного обслуживания или замену товара посредством выезда к покупателю, в случаях, не предусмотренных Законом РФ «О защите прав потребителей». В отношении возврата денежных средств за приобретенный товар, денежные средства за предоставленную услугу доставки не возвращаются.</p>
<p>При оплате картами возврат наличными денежными средствами не&nbsp;допускается. Порядок возврата регулируется правилами международных платежных систем.</p>
<p>Возврат денежных средств будет осуществлен на&nbsp;банковскую карту в&nbsp;течение 21 (двадцати одного) рабочего дня со дня получения заявления о&nbsp;возврате денежных средств компанией.</p>
<p>Для возврата денежных средств по&nbsp;операциям проведенными с&nbsp;ошибками необходимо обратиться с&nbsp;письменным заявлением и&nbsp;приложением копии паспорта и&nbsp;чеков/квитанций, подтверждающих ошибочное списание. Данное заявление необходимо направить по&nbsp;адресу <a href="mailto: return@formetoo.ru" class="underline">return@formetoo.ru</a></p>
<p>Сумма возврата будет равняться сумме покупки. Срок рассмотрения Заявления и&nbsp;возврата денежных средств начинает исчисляться с&nbsp;момента получения Компанией Заявления и&nbsp;рассчитывается в&nbsp;рабочих днях без&nbsp;учета праздников/выходных дней.</p>
<p>Требования о возврате уплаченной за товар денежной суммы подлежит удовлетворению в течение 10 дней со дня предъявления вами соответствующего требования (ЗоЗПП Статья 22. Сроки удовлетворения отдельных требований потребителя).</p>
<p>Товары надлежащего качества, которые нельзя обменять (вернуть), перечислены в&nbsp;Перечне, утвержденном постановлением Правительства РФ от&nbsp;19 января 1998 г.&nbsp;№&nbsp;55. Приводим его полностью.
<ol>
	<li>Товары для&nbsp;профилактики и&nbsp;лечения заболеваний в&nbsp;домашних условиях (предметы санитарии и&nbsp;гигиены из&nbsp;металла, резины, текстиля и&nbsp;других материалов, инструменты, приборы и&nbsp;аппаратура медицинские, средства гигиены полости рта, линзы очковые, предметы по&nbsp;уходу за&nbsp;детьми), лекарственные препараты.</li>
	<li>Предметы личной гигиены (зубные щетки, расчески, заколки, бигуди для&nbsp;волос, парики, шиньоны и&nbsp;другие аналогичные товары).</li>
	<li>Парфюмерно-косметические товары.</li>
	<li>Текстильные товары (хлопчатобумажные, льняные, шелковые, шерстяные и&nbsp;синтетические ткани, товары из&nbsp;нетканых материалов типа тканей — ленты, тесьма, кружево и&nbsp;другие); кабельная продукция (провода, шнуры, кабели); строительные и&nbsp;отделочные материалы (линолеум, пленка, ковровые покрытия и&nbsp;другие) и&nbsp;другие товары, отпускаемые на&nbsp;метраж.</li>
	<li>Швейные и&nbsp;трикотажные изделия (белье, чулочно-носочные изделия).</li>
	<li>Изделия и&nbsp;материалы, контактирующие с&nbsp;пищевыми продуктами, из&nbsp;полимерных материалов, в&nbsp;том числе для&nbsp;разового использования (посуда и&nbsp;принадлежности столовые и&nbsp;кухонные, емкости и&nbsp;упаковочные материалы для&nbsp;хранения и&nbsp;транспортирования пищевых продуктов).</li>
	<li>Товары бытовой химии, пестициды и&nbsp;агрохимикаты.</li>
	<li>Мебель бытовая (мебельные гарнитуры и&nbsp;комплекты)</li>
	<li>Ювелирные и&nbsp;другие изделия из&nbsp;драгоценных металлов и&nbsp;(или) драгоценных камней, ограненные драгоценные камни.</li>
	<li>Автомобили и&nbsp;мотовелотовары, прицепы и&nbsp;номерные агрегаты к&nbsp;ним; мобильные средства малой механизации сельскохозяйственных работ; прогулочные суда и&nbsp;иные плавсредства бытового назначения.</li>
	<li>Технически сложные товары бытового назначения, на&nbsp;которые установлены гарантийные сроки (станки металлорежущие и&nbsp;деревообрабатывающие бытовые; электробытовые машины и&nbsp;приборы; бытовая радиоэлектронная аппаратура; бытовая вычислительная и&nbsp;множительная техника; фото- и&nbsp;киноаппаратура; телефонные аппараты и&nbsp;факсимильная аппаратура; электромузыкальные инструменты; игрушки электронные, бытовое газовое оборудование и&nbsp;устройства; часы наручные и&nbsp;карманные механические, электронно-механические и&nbsp;электронные, с&nbsp;двумя и&nbsp;более функциями).</li>
	<li>Гражданское оружие, основные части гражданского и&nbsp;служебного огнестрельного оружия, патроны к&nbsp;нему.</li>
	<li>Животные и&nbsp;растения.</li>
	<li>Непериодические издания (книги, брошюры, альбомы, картографические и&nbsp;нотные издания, листовые изоиздания, календари, буклеты, издания, воспроизведенные на&nbsp;технических носителях информации).</li>
</ol>

<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script>
$(document).ready(function(){
	$('[name="tel"]').mask('+7 999 999-99-99',{placeholder:'_'});
	$('[name="order"]').mask('9999999999',{placeholder:'_'});
	$.validator.methods.tel=function(value,element) {
		return this.optional(element)||/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i.test(value);
	}
	$.validator.methods.email=function(value,element) {
		return this.optional(element)||/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i.test(value);
	}
	$("#return_form").validate({
		rules:{
			name:{
				required:true,
				maxlength: 150
			},
			email:{
				email:true,
				required:true
			},
			tel:{
				required:true,
				tel: true
			},
			order:{
				required:true,
				rangelength:[10,10],
				digit:true
			},
			address:{
				required:true,
				rangelength:[20,500]
			},
			comment:{
				required:true,
				maxlength:5000
			},
			captcha:{
				required:true,
				rangelength:[5,7],
				remote:{
					url: '/ajax/check_captcha.php',
					type: 'get',
					data: {
						captcha:function(){
							return $('#return_form [name="captcha"]').val()
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
	$('[name=tel]').on('click',function(){
		if($(this).val()=='+7 ___ ___-__-__')
			$(this).setCursorPosition(3);
	});
	$('[name=order]').on('click',function(){
		if($(this).val()=='__________')
			$(this).setCursorPosition(0);
	});
	$("#return_form #send").on('click',function(){
		$('#return_form input:not([name=captcha]):not([name=order])').each(function(index,el){
			if($(el).attr('placeholder')&&!$(el).val())
				$(el).val($(el).attr('placeholder'));
		});
	});
	$("#return_form").on('submit',function(){
		if(!$("#return_form").validate())
			return false;
	});
	$('.captcha_img').on('click',function(){
		$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
		$('[name="captcha"]').val('');
	});	
	$('#return_form #politic').on('change',function(){
		if(!$(this).prop('checked'))
			$('#send').prop('disabled',true);
		else $('#send').prop('disabled',false);
	});
});
</script>