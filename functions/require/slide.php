<?
defined ('_DSITE') or die ('Access denied');

$path=array_reverse($path);
$path=implode('/',$path);

switch($path){
	case 'products':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/products.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Всё для плоских кровель
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-description">
					<p>
						Кровельная ПВХ мембрана на сегодняшний день является материалом, постепенно отвоевывающим свою долю рынка в у более традиционных кровельных материалов. Процент кровель, обустроенныхпо мембранной технологии неуклонно растет — а это значит, что и строители, и клиентыстроительных компаний по достоинству оценивают преимущества мембранной кровли.
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-info-box">
					<div class="slide-info">
						<p class="slide-info-header">
							> 400 000 м<sup>2</sup>
						</p>
						<p class="slide-info-description">
							выполненных нами кровельных работ
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							ТПО, EPDM, ПВХ
						</p>
						<p class="slide-info-description">
							мы работаем с основными мембранами
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							50 лет
						</p>
						<p class="slide-info-description">
							срок службы мембранной кровли
						</p>
					</div>
				</div>
			</div>
		</div>
<?
		break;
	case 'products/gidroizolyyaciya-krovli':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/gidroizolyaciya.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Мембранная кровля
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-description">
					<p>
						Гидроизоляция кровли — один из обязательных этапов ее обустройства. Обработать покрытие здания так, чтобы оно не протекло после первого ливня – задача крайне важная. И с этой задачей прекрасно справляются современные кровельные материалы — мембраны ТПО, EPDM и ПВХ. Наша компания не только реализует, но и выполняет профессиональный монтаж мембранных кровель всех типов.
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-info-box">
					<div class="slide-info">
						<p class="slide-info-header">
							долговечно
						</p>
						<p class="slide-info-description">
							срок службы кровли — 50 лет 
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							быстро
						</p>
						<p class="slide-info-description">
							монтаж до 1000 м<sup>2</sup> кровли в день
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							экологично
						</p>
						<p class="slide-info-description">
							ТПО и EPDM асболютно безвредны
						</p>
					</div>
				</div>
			</div>
		</div>
<?		
		break;
	case 'products/gidroizolyyaciya-krovli/tpo-membrany':
	case 'products/gidroizolyyaciya-krovli/tpo-membrany/carlisle':
	case 'products/gidroizolyyaciya-krovli/tpo-membrany/firestone':
	case 'products/gidroizolyyaciya-krovli/epdm-membrany':
	case 'products/gidroizolyyaciya-krovli/epdm-membrany/carlisle':
	case 'products/gidroizolyyaciya-krovli/epdm-membrany/firestone':
	case 'products/gidroizolyyaciya-krovli/pvh-membrany':
	case 'products/gidroizolyyaciya-krovli/pvh-membrany/logicroof':
	case 'products/gidroizolyyaciya-krovli/pvh-membrany/ecoplast':
	case 'products/gidroizolyyaciya-krovli/pvh-membrany/plastfoil':
	case 'products/gidroizolyyaciya-krovli/pvh-membrany/extraroof':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/gidroizolyaciya.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/krovelnye-rulonnie-materialy':
	case 'products/krovelnye-rulonnie-materialy/technoelast':
	case 'products/krovelnye-rulonnie-materialy/uniflex':
	case 'products/krovelnye-rulonnie-materialy/ecoflex':
	case 'products/krovelnye-rulonnie-materialy/linokrom':
	case 'products/krovelnye-rulonnie-materialy/bikrost':
	case 'products/krovelnye-rulonnie-materialy/stekloizol':
	case 'products/krovelnye-rulonnie-materialy/ruberoid':
?>
	<div id="slide" class="mini">
		<div id="slide-bg" style="background-image:url('/img/bg/rulonka.jpg');"></div>
		<div class="shadow"></div><div class="shadow1"></div>
	</div>
<?
		break;
	case 'products/uteplitel':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/uteplitel.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Утепление и пароизоляция
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-description">
					<p>
						От правильно выбранного варианта теплоизоляции зависит не только сохранение тепла в помещении, но и долговечность кровельного покрытия. Наша компания осуществялет поставки слудующих утеплителей: минеральной ваты (от 80 кг/м<sup>3</sup>) от трёх производителей, экструдированного пенополистирола, PIR утеплителя; пароизоляционной плёнки, геотекстиля разной плотности, а таже грамотный монтаж вышеперечисленных материалов. 
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-info-box">
					<div class="slide-info">
						<p class="slide-info-header">
							> 92 000 м<sup>3</sup>
						</p>
						<p class="slide-info-description">
							утеплителя уложили наши бригады
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							> 1 200 фур
						</p>
						<p class="slide-info-description">
							утеплителя куплено в нашей компании
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							6 заводов
						</p>
						<p class="slide-info-description">
							с которыми мы работаем напрямую
						</p>
					</div>
				</div>
			</div>
		</div>
<?		
		break;
	case 'products/uteplitel/minvata-baswool':
	case 'products/uteplitel/minvata-tecnonicol':
	case 'products/uteplitel/minvata-rockwool':
	case 'products/uteplitel/penopleks':
	case 'products/uteplitel/polispen':
	case 'products/uteplitel/pir-plita':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/pir.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?		
		break;
	case 'products/uteplitel/penoplast':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/uteplitel.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/geotekstil-stekloholst':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/geotextile.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/paroizolyaciya':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/paroizolyaciya.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/krepezh-vodootvedenie':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/krepezh.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Крепёж и водоотведение
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-description">
					<p>
						
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-info-box">
					<div class="slide-info">
						<p class="slide-info-header">
							низкие цены
						</p>
						<p class="slide-info-description">
							работаем напрямую<br/>с производителями
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							надёжность
						</p>
						<p class="slide-info-description">
							продаём крепёж,<br/>проверенный временем 
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							широкий выбор
						</p>
						<p class="slide-info-description">
							крепёж для кровли<br/>и фасадов всех типов
						</p>
					</div>
				</div>
			</div>
		</div>
<?		
		break;
	case 'products/krepezh-vodootvedenie/termoclip-krepezh':
	case 'products/krepezh-vodootvedenie/termoclip-vodootvedenie':
	case 'products/krepezh-vodootvedenie/hilti':
	case 'products/krepezh-vodootvedenie/rocks':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/krepezh.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/oborudovanie':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/oborudovanie.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Инструмент и расходники
					</p>
				</div>
			</div>
			<div class="slide-box no-info">
				<div id="slide-description">
					<p>
						Для устройства эффективной и долговечной гидроизоляции плоской кровли необходимо, но недостаточно иметь только высококлассный кровельный материал, нужно использовать так же и хорошее, качественное оборудование и профессиональные инструменты.
					</p>
				</div>
			</div>
		</div>
<?		
		break;
	case 'products/oborudovanie/montazh-tpo-pvh-membran':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/index/apparaty.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'products/oborudovanie/instrumenty-rashodniki':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/rashodniki.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Инструмент и расходники
					</p>
				</div>
			</div>
			<div class="slide-box no-info">
				<div id="slide-description">
					<p>
						Заказывая в нашей компании кровельные работы или работы по гидроизоляции прудов, вы можете быть верены в том, что наши бригады укомплектованы профессиональным инструментом и оборудованием, необходимым для работы, а также умеют с ним обращаться.
					</p>
				</div>
			</div>
		</div>
<?		
		break;
	case 'products/fixatory-armatury':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/fixatory.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'services':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/bg/services.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Работы по гидроизоляции
					</p>
				</div>
			</div>
			<div class="slide-box no-info">
				<div id="slide-description">
					<p>
						Заказывая в нашей компании кровельные работы или работы по гидроизоляции прудов, вы можете быть верены в том, что наши бригады укомплектованы профессиональным инструментом и оборудованием, необходимым для работы, а также умеют с ним обращаться.
					</p>
				</div>
			</div>
		</div>
<?		
		break;
	case 'services/montazh-tpo-membran':
	case 'services/montazh-epdm-membran':
	case 'services/montazh-pvh-membran':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/services.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'services/gidroizolyaciya-pruda':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/gidroizolyaciya-pruda.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
		break;
	case 'services/ustroystvo-naplavlyaemoy-krovli':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/naplavlyay.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?		
		break;
	case 'portfolio':
?>
		<div id="slide">
			<div id="slide-bg" style="background-image:url('/img/index/portfolio.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
			<div class="slide-box">
				<div id="slide-header">
					<p>
						Наши работы
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-description">
					<p>
						Заказывая в нашей компании кровельные работы или работы по гидроизоляции прудов, вы можете быть верены в том, что наши бригады укомплектованы профессиональным инструментом и оборудованием, необходимым для работы, а также умеют с ним обращаться.
					</p>
				</div>
			</div>
			<div class="slide-box">
				<div id="slide-info-box">
					<div class="slide-info">
						<p class="slide-info-header">
							> 10 лет
						</p>
						<p class="slide-info-description">
							мы занимаемся кровельными работами
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							> 400 000 м<sup>2</sup>
						</p>
						<p class="slide-info-description">
							кровель утеплили и гидроизолировали
						</p>
					</div>
					<div class="slide-info">
						<p class="slide-info-header">
							5 бригад
						</p>
						<p class="slide-info-description">
							работает у нас уже более 3 лет
						</p>
					</div>
				</div>
			</div>
		</div>
<?		
		break;
	case 'portfolio/moscow-kometa':
	case 'portfolio/moscow-odintsovo-kubinka':
	case 'portfolio/moscow-luhovitsy-tsk':
	case 'portfolio/moscow-newton-plaza':
	case 'portfolio/lobnya-logisticheskiy-kompleks':
	case 'portfolio/nikolskaya-sloboda-kottedge':
	case 'portfolio/ryazan-detskiy-sad':
	case 'portfolio/ryazan-moskovskoe-shosse':
	case 'portfolio/moscow-vyhino':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/index/portfolio.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?		
		break;
	case 'dostavka-oplata':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/bg/dostavka.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>
<?
	break;
	case 'contacts':
?>
		<div id="slide" class="mini">
			<div id="slide-bg" style="background-image:url('/img/index/contacts_<?=$area;?>.jpg');"></div>
			<div class="shadow"></div><div class="shadow1"></div>
		</div>		
<?		
		break;
}
?>