<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user,$G;

?>
<h2>Онлайн-оплата банковскими картами</h2>
<table class="no-border top">
	<tr>
		<th width="23%"></td>
		<th width="77%"></td>
	</tr>
	<tr>
		<td>
			<div class="main_products_list_items_info_pay_types_icons">
				<span class="icon nolink icon-visa"></span>
				<span class="icon nolink icon-mastercard"></span>
				<span class="icon nolink icon-mir"></span>
			</div>
			<div class="main_products_list_items_info_pay_types_icons">
				<span class="icon nolink icon-verified-by-visa"></span>
				<span class="icon nolink icon-mastercard-securecode"></span>
				<span class="icon nolink icon-mir-accept"></span>
			</div>
			<div class="main_products_list_items_info_pay_types_icons">
				<span class="icon nolink icon-alfa"></span>
				<div class="ssl-verify"></div>
			</div>
		</td>
		<td>
			<p>При заказе на&nbsp;сайте к&nbsp;оплате принимаются карты VISA, MasterCard, МИР и&nbsp;Maestro, платеж осуществляется через&nbsp;платежный шлюз АО «Альфа-Банк».</p>
			<p>Ссылка для&nbsp;совершения оплаты высылается покупателю (и появляется в&nbsp;личном кабинете в&nbsp;случае, если пользователь зарегистрирован) после подтверждения заказа.</p>
			<p>Услуга оплаты через&nbsp;интернет осуществляется в&nbsp;соответствии с&nbsp;Правилами международных платежных систем Visa, MasterCard и&nbsp;Платежная система «Мир» на&nbsp;принципах соблюдения конфиденциальности и&nbsp;безопасности совершения платежа, для&nbsp;чего используются самые современные методы проверки, шифрования и&nbsp;передачи данных по&nbsp;закрытым каналам связи. Ввод данных банковской карты осуществляется на&nbsp;защищенной платежной странице АО «АЛЬФА-БАНК».</p>
			<p class="hidden_text_show"><a href="#" class="dotted">Показать подробности</a></p>
			<p>На странице для&nbsp;ввода данных банковской карты потребуется ввести данные банковской карты: номер карты, имя владельца карты, срок действия карты, трёхзначный код безопасности (CVV2 для&nbsp;VISA или CVC2 для&nbsp;MasterCard). Все необходимые данные пропечатаны на&nbsp;самой карте. Трёхзначный код безопасности — это три цифры, находящиеся на&nbsp;обратной стороне карты. Далее вы будете перенаправлены на&nbsp;страницу Вашего банка для&nbsp;ввода 3DSecure кода, который придет к&nbsp;Вам в&nbsp;СМС. Если 3DSecure код к&nbsp;Вам не&nbsp;пришел, то следует обратится в&nbsp;банк выдавший Вам карту.</p>
			<p>Случаи отказа в&nbsp;совершении платежа:</p>
			<ul>
				<li>банковская карта не&nbsp;предназначена для&nbsp;совершения платежей через&nbsp;интернет, о&nbsp;чем можно узнать, обратившись в&nbsp;Ваш Банк;</li>
				<li>недостаточно средств для&nbsp;оплаты на&nbsp;банковской карте. Подробнее о&nbsp;наличии средств на&nbsp;банковской карте Вы можете узнать, обратившись в&nbsp;банк, выпустивший банковскую карту;</li>
				<li>данные банковской карты введены неверно;</li>
				<li>истек срок действия банковской карты. Срок действия карты, как правило, указан на&nbsp;лицевой стороне карты (это месяц и&nbsp;год, до&nbsp;которого действительна карта). Подробнее о&nbsp;сроке действия карты Вы можете узнать, обратившись в&nbsp;банк, выпустивший банковскую карту;</li>
			</ul>
			<p>По вопросам оплаты с&nbsp;помощью банковской карты и&nbsp;иным вопросам, связанным с&nbsp;работой сайта, Вы можете обращаться по&nbsp;телефону <?=$G['CITY']['m_info_city_tel_office'];?>.</p>
			<p>Предоставляемая вами персональная информация (имя, адрес, телефон, e-mail, номер банковской карты) является конфиденциальной и&nbsp;не подлежит разглашению. Данные вашей кредитной карты передаются только в&nbsp;зашифрованном виде и&nbsp;не сохраняются на&nbsp;нашем web-сервере.</p>
			
		</td>
	</tr>
</table>
<h2>Оплата по выставленному счёту (безналичный расчёт)</h2>
<table class="no-border">
	<tr>
		<th width="7%"></td>
		<th width="93%"></td>
	</tr>
	<tr>
		<td>
			<div class="main_products_list_items_info_pay_types_icons">
				<span class="icon nolink icon-invoice"></span>
			</div>
		</td>
		<td>
			<p>Счёт на&nbsp;оплату высылается после подтверждения заказа. При регистрации возможна привязка юридического лица к&nbsp;аккаунту по&nbsp;ИНН и&nbsp;КПП. К&nbsp;одному аккаунту пользователя можно привязать несколко юридических лиц или&nbsp;ИП.</p>
		</td>
	</tr>
</table>
<h2>Оплата наличными в центральном офисе</h2>
<table class="no-border">
	<tr>
		<th width="7%"></td>
		<th width="93%"></td>
	</tr>
	<tr>
		<td>
			<div class="main_products_list_items_info_pay_types_icons">
				<span class="icon nolink icon-cash"></span>
			</div>
		</td>
		<td>
			<p>Внести оплату наличными можно в&nbsp;<a href="/about/contacts/#address_2" class="underline">центральном офисе</a> интернет-магазина. Покупателю выдаётся кассовый чек.</p>
		</td>
	</tr>
</table>

<style>
.main_products_list_items_info_pay_types_icons{
	margin-bottom:.5em;
}
</style>