<?
define ('_DSITE',1);
ini_set('display_errors',0);
require_once(__DIR__.'/../functions/main.php');
ob_start();

$merge_carts=$order->getCarts();
if($merge_carts){
    $merge_carts=array_pop($merge_carts);
	$old_cart_date=transform::date_f(dtu($merge_carts['m_cart_date'])).'&nbsp;в&nbsp;'.dtu($merge_carts['m_cart_date'],'H:m');
	$merge_carts=true;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
	<meta name="description" content="<?=isset($current['description'])?$current['description']:''?>" />
	<meta name="keywords" content="<?=isset($current['keywords'])?$current['keywords']:''?>" />
	<title><? echo $current['title'].($_SERVER['REQUEST_URI']!='/'?' — интернет-магазин formetoo':''); ?></title>
	<!--[if IE]>
		<style type="text/css">
			span.current{
				padding-top:3px!important;
				padding-bottom:2px!important;}
			input[type="text"]{
				padding-bottom:6px!important;}
		</style>
	<![endif]-->

	<link rel="preconnect" href="https://<?=$_SERVER['G_VARS']['SERV_ST'];?>" crossorigin>
	<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>

	<link rel="stylesheet" type="text/css" href="/js/simplebar/simplebar.css"/>
	<link rel="stylesheet" type="text/css" href="/css/style.css" />
	<link rel="stylesheet" type="text/css" href="/css/jquery-ui.min.css"/>
	<link rel="stylesheet" type="text/css" href="/css/jquery-ui.structure.min.css"/>
	<link rel="stylesheet" type="text/css" href="/css/jquery-ui.theme.min.css"/>

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#b91d47">
	<meta name="theme-color" content="#ffffff">

	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/js/add_functions.js"></script>
	<script type="text/javascript" src="/js/jquery.suggest_search.js"></script>
	<script type="text/javascript" src="/js/validation/core.js"></script>
	<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
	<script type="text/javascript" src="/js/simplebar/simplebar.js"></script>
	<script type="text/javascript" src="/js/functions.js"></script>
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900&amp;subset=cyrillic" rel="stylesheet">
<?if(1/* !isset($_COOKIE['regionselect']) */){?>
	<script>
		var page=1,
			sort='rate',
			more=0,
			simpleBarGrad,
			<?=($order->getCart(true)?'cart='.$order->getCart(true).',':'');?>
			current=<?=$current['menu']?$current['menu']:0;?>;
			<?if($merge_carts){?>
			$(document).ready(function(){
				popup_show('#popup_mergecart');
				$('#popup_mergecart_yes').on('click',function(){
					$('#popup_mergecart button').prop('disabled',true);
					$.post(
						'/ajax/merge_cart.php',
						{},
						function(data){console.log(data);
							window.location='/cart/';
						}
					);
				});
				$('#popup_mergecart_no').on('click',function(){
					$('#popup_mergecart button').prop('disabled',true);
					$.post(
						'/ajax/merge_cart.php',
						{
							delete:1
						},
						function(data){console.log(data);
							popup_hide();
							$('#popup_mergecart').remove();
						}
					);
				});
			});
			<?}?>
	</script>
	<style>
		<?=($user->getInfo('m_users_accept_newsletter_email')||$user->getInfo('m_users_accept_newsletter_sms')?'#footer-links-subscribe{visibility:hidden;}':'');?>
	</style>
<?}?>
</head>
<body>
	<div style="position:absolute">
		<div id="width"></div>
		<div id="height"></div>
	</div>
	<div class="page-wrapper">

        <div id="slide" class="mini">
            <div id="slide-bg"></div>
        </div>

        <div class="header">
            <div class="header_top_left">
                <div class="header_logo" id="logo">
                    <a href="/"><img class="logo_img" src="/img/fmt_logo.svg" alt="formetoo"></a>
                </div>
            </div>
            <div class="header_top_right">
                <div class="contact_parent">
                    <div class="parent header_phone" id="contacts">
                        <div class="child child_contacts">
                            <p class="phone"><a href="tel:{tel_office_nobr}">{tel_office}</a></p>
                            <p class="schedule"><span class="schedule_content">пн-пт: 09:00 - 18:00</span></p>
                        </div>
                    </div>
                    <div class="parent social_icons">
                        <a href="https://wa.me/79105199977" class="child whatsapp_href" target="_blank">
                            <div class="whatsapp_icon">
                                <img src="/img/whatsapp.png" alt="whatsapp_icon" id="whatsapp">
                            </div>
                        </a>
                        <a href="viber://chat?number={tel_office}" class="child viber_href" target="_blank">
                             <div class="viber_icon">
                                 <img src="/img/viber.png" alt="viber_icon" id="viber">
                             </div>
                        </a>
                        <a href="mailto:{mail}" class="child email_href">
                             <div class="email_parent">
                                <div class="email_icon">
                                    <img src="/img/icon-message.svg" alt="email_icon" id="email">
                                </div>
                                <div class="email_adress">
                                    <p class="email">{mail}</p>
                                </div>
                             </div>
                        </a>
                    </div>
                </div>
                <div class="currency_account_parent">
                    <div class="parent header_city" id="city">
                        <a class="child city_href" href="#"><img id="map_icon" src="/img/map.png"><span class="city_span">{ГОРОД}</span><span class="icon icon-arrow-down"></span></a>
                    </div>
                    <div class="parent account">
                        <?=($user->getInfo('m_users_name')?'<div class="nav_logout" data-href="/logout/" title="Выйти из аккаунта"><span class="icon icon-logout"></span></div>':'');?>
                        <div class="child nav_account" data-href="/my/" title="Перейти в личный кабинет">
                            <div>
                                <span onselectstart="return false">Личный кабинет</span>
                                <span onselectstart="return false" class="desc"><?=($user->getInfo('m_users_name')?transform::some($user->getInfo('m_users_name'),20,true):'')?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="top_line_menu">
            <div class="left_sidebar">
                <div class="nav">
                    <div class="nav_container">
                        <div class="nav_container_inner">
                            <div class="nav_wrapper">
                                <ul>
                                    <li id="menu_id_2000000000" class="has_sublevel">
                                        <span class="menu-item-parent" style="font-size: 1em;">Каталог товаров</span>
                                    </li>
                                    <li id="menu_id_1000000000" class=""><a href="/" title="Главная"><span class="menu-item-parent" style="font-size: 1em;">Главная</span></a></li>
                                    <li id="nav_more" class="has_sublevel" style="display: none;"><a href="#"><span style="font-size: 1em;">Ещё</span></a><ul class="nav_sublevel" style="display: none;">&nbsp;</ul></li>
                                </ul>
                                <?
                                //$menu->display('top-catalog',0,false);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="top_line">
                <div class="top_line1">
                    <div class="header_search" id="search">
                        <input type="text" placeholder="Поиск по сайту"/><span class="icon icon-search"></span>
                    </div>
                    <div class="goods">
                        <a href="#" class="goods_a">
                            <span class="goods_icon"><img src="/img/heart-outline.svg" class="goods_img"></span>
                            <span class="goods_title">Отложенные</span>
                        </a>
                    </div>
                    <div class="comparison">
                        <a href="#" class="comparison_a">
                            <span class="comparison_icon"><img src="/img/adv_04.svg" class="comparison_img"></span>
                            <span class="comparison_title">Сравнение</span>
                        </a>
                    </div>
                    <div class="nav_cart" data-href="/cart/" title="Перейти к корзине">
                        <span class="icon-cart"><img src="/img/cart.svg" class="cart_icon"></span>
                        <div class="cart_title">
                            <span onselectstart="return false">Корзина<span class="nav_cart_size<?=$order->getCartSize()?' active">'.$order->getCartSize():'">'?></span></span>
                            <span onselectstart="return false" class="desc"><?=$order->getCartSum()?transform::price_o($order->getCartSum()).'&nbsp;руб':'нет товаров'?></span>
                        </div>
                    </div>
                </div>
                <div class="top_line2">
                    <span class="advanced_search"><a href="#" class="advanced_search">Расширенный поиск</a></span>
                </div>
            </div>
        </div>
        <div class="clr"></div>
        <div class="main">
            <div class="main_container">
                <div class="main_container_inner">
                    <?if($merge_carts){?>
                    <div id="popup_mergecart" class="popup" style="width:24.2em;margin-left:-11.3em;top:45%;">
                        <div class="popup_header">
                            <p>Объединить корзины?</p>
                        </div>
                        <div class="popup_header_close popup_close">
                            <span class="icon icon-close"></span>
                        </div>
                        <div class="clr"></div>
                        <p>Мы обнаружили корзину с товарами с Вашего прошлого визита <?=$old_cart_date;?>.</p>
                        <p>Нам стоит объединить товары старой и новой корзины?</p>

                        <div class="login_authorization_form_input_container" style="text-align:center;width:100%;">
                            <button type="submit" class="med_button orange" id="popup_mergecart_yes">Да, объединить корзины</button>
                        </div>
                        <div class="clr"></div>
                        <div class="login_authorization_form_input_container" style="text-align:center;width:100%;">
                            <button type="submit" class="med_button" id="popup_mergecart_no">Нет, оставить только новую корзину</button>
                        </div>

                    </div>
                    <?}?>
                    <?$content->getContent();?>
                </div>
            </div>
        </div>
	</div>
	<div id="footer">
		<div id="footer-container">
			<div id="footer-inner">
				<div class="footer_floor footer_top">
					<div class="footer_top_left">
						<div id="footer-logo">
                            <a href="/"><img class="logo_img" src="/img/fmt_logo.svg" alt="formetoo"></a>
                        </div>
					</div>
					<div class="footer_top_right">
						<div id="footer-info-social">
							<a href="#https://vk.com/formetoo-su" title="Formetoo in VK" target="_blank"><span class="icon-social-vk"><img src="/img/icons-vkontakte.svg"></span></a>
							<a href="#https://www.facebook.com/formetoo-su" title="Formetoo in Facebook" target="_blank"><span class="icon-social-fb"><img src="/img/icons-facebook.svg"></span></a>
							<a href="https://twitter.com/su_formetoo/" title="Formetoo in Twitter" target="_blank"><span class="icon-social-twitter"><img src="/img/icons-twitter.svg"></span></a>
							<a href="#https://www.odnoklassniki.ru/formetoo-su" title="Formetoo in OK" target="_blank"><span class="icon-social-ok"><img src="/img/icons-ok.svg"></span></a>
							<a href="#https://www.instagram.com/formetoo_su" title="Formetoo in Instagram" target="_blank"><span class="icon-social-insta"><img src="/img/icons-instagram.svg"></span></a>
						</div>
					</div>
				</div>
				<div class="footer_floor footer_midle">
					<div class="footer_midle_left">
                        <div class="footer_contacts1">
                            <a href="mailto:{mail}" class="child footer_email_href">
                                <div class="email_parent">
                                    <div class="email_icon">
                                        <img src="/img/icon-message.svg" alt="email_icon" id="email">
                                    </div>
                                    <div class="email_adress">
                                        <p class="email">{mail}</p>
                                    </div>
                                </div>
                            </a>
                            <a href="https://wa.me/79105199977" class="child footer_whatsapp_href" target="_blank">
                                <div class="whatsapp_icon">
                                    <img src="/img/whatsapp.png" alt="whatsapp_icon" id="whatsapp">
                                </div>
                            </a>
                            <a href="viber://chat?number={tel_office}" class="child footer_viber_href" target="_blank">
                                <div class="viber_icon">
                                    <img src="/img/viber.png" alt="viber_icon" id="viber">
                                </div>
                            </a>
                        </div>
                        <div class="footer_contacts2">
                            <div class="footer_city">
                                <span class="footer_city">г. Санкт-Петербург</span>
                                <span class="footer_city_street">ул. Тамбовская, д. 69Б</span>
                            </div>
                            <div class="footer_tel">
                                <span class="footer_tel"><a href="tel:{tel_office_nobr}">{tel_office}</a></span>
                                <span class="footer_schedule">пн-пт: 09:00 - 18:00</span>
                            </div>
                        </div>
                        <div class="footer_contacts3">
                            <div class="footer_city">
                                <span class="footer_city">г. Москва</span>
                                <span class="footer_city_street">ул. Скотопрогонная, д. 35с4</span>
                            </div>
                            <div class="footer_tel">
                                <span class="footer_tel"><a href="tel:{tel_office_nobr}">{tel_office}</a></span>
                                <span class="footer_schedule">пн-пт: 09:00 - 18:00</span>
                            </div>
                        </div>
					</div>
					<div class="footer_midle_center">
                        <a href="#" class="download_price"><img src="/img/download-price.svg" alt="скачать прайс-лист"></a>
                        <a href="#" class="yamarket"><img src="/img/market.svg" alt="яндекс маркет"></a>
					</div>
					<div class="footer_midle_right">
                        <div class="footer_midle_right_child">
                            <span class="footer_midle_right_title">О компании</span>
                            <ul class="footer_midle_right_list">
                                <li class="footer_midle_right_item"><a href="/about/company/">Реквизиты</a></li>
																<li class="footer_midle_right_item"><a href="/about/contacts/">Контакты</a></li>
																<li class="footer_midle_right_item"><a href="/about/feedback/">Обратная связь</a></li>
                            </ul>
                        </div>
                        <div class="footer_midle_right_child">
													<span class="footer_midle_right_title">Покупателям</span>
													<ul class="footer_midle_right_list">
															<li class="footer_midle_right_item"><a href="/info/delivery/">Доставка</a></li>
															<li class="footer_midle_right_item"><a href="/info/return">Возврат товара</a></li>
															<li class="footer_midle_right_item"><a href="/info/payments/">Способ оплаты</a></li>
													</ul>
                        </div>
												<div class="footer_midle_right_child">
													<span class="footer_midle_right_title">Информация</span>
													<ul class="footer_midle_right_list">
															<li class="footer_midle_right_item"><a href="/info/terms-of-sale/">Условия продажи<br>товаров</a></li>
															<li class="footer_midle_right_item"><a href="/info/">Персональные данные</a></li>
													</ul>
												</div>
					</div>
				</div>
				<div class="footer_floor footer_bottom">
					<div class="footer_bottom_left">
                        <span class="footer_bottom_left_info">Copyright © 2008 - 2019</span>
                        <span class="footer_bottom_left_info">Formetoo</span>
					</div>
					<div class="footer_bottom_center">
						<div id="footer-info-paysystems">
                            <span class="paysystem_title">Принимаем к оплате</span>
							<span class="icon icon-visa-mini nolink" title="Принимаем к оплате карты VISA"></span>
							<span class="icon icon-mastercard-mini nolink" title="Принимаем к оплате карты MasterCard"></span>
							<span class="icon icon-mir-mini nolink" title="Принимаем к оплате карты МИР"></span>
						</div>
					</div>
					<div class="footer_bottom_right">
                        <span class="footer_bottom_right_info"><a href="#">Политика конфеденциальности</a></span>
                        <span class="footer_bottom_right_info"><a href="#">Публичная оферта</a></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<noindex>
	<div id="cookie_policy">
		<div class="cookie_policy_container">
			<div class="cookie_policy">
				<p>Продолжая работу с formetoo.ru, Вы подтверждаете использование сайтом cookies Вашего браузера с целью исправной работы функционала сайта, а также улучшения предложений и сервисов на основе Ваших предпочтений и интересов. Подробнее условия работы с cookies описаны в <a class="underline" href="/info/cookies-policy/" target="_blank">Политике использования файлов cookies<span class="underline"></span></a></p>
				<button onselectstart="return false;" class="med_button orange">Принять</button>
			</div>
		</div>
	</div>
	</noindex>
	<div id="blocked"></div>
	<div id="popups">
		<div id="select_city" class="popup">
			<div class="select_city_header">
				<p>Выбор города</p>
			</div>
			<div class="select_city_header_close popup_header_close popup_close">
				<span class="icon icon-close"></span>
			</div>
			<div class="clr"></div>
			<div class="select_city_search">
				<input type="text" name="city_search" placeholder="поиск города"/>
			</div>
			<div class="select_city_selected">
				<p>Сейчас выбран <span>{ГОРОД}</span></p>
			</div>
			<div class="clr"></div>
			<div class="select_city_list">
				<article>
					<?
						foreach($area_list as $_domain=>$_city)
							echo '<a href="//',
									$_city['m_info_city_url'],
									'.'.$_SERVER['SERVER_NAME'],
									$_SERVER['REQUEST_URI'],
									'">',
									$_city['m_info_city_name_city_im'],
								'</a>';
					?>
				</article>
			</div>
		</div>
		<noindex>
			<div id="popup_subscribe" class="popup" style="width:22em;margin-left:-11.5em;top:30%;">
				<div class="popup_header">
					<p>Подписка на рассылку</p>
				</div>
				<div class="popup_header_close popup_close">
					<span class="icon icon-close"></span>
				</div>
				<div class="clr"></div>
				<div class="form-center">
					<p class="popup-success popup-success-email-confirmed"><span class="icon icon-big-ok nolink"></span></p>
					<p class="popup-success">Подписка успешно оформлена. Мы отправили Вам письмо для&nbsp;подтверждения электронной почты, пожалуйста перейдите по&nbsp;ссылке в&nbsp;письме.</p>
					<p class="popup-success-email-confirmed">Подписка успешно оформлена. Отказаться от&nbsp;подписки можно в&nbsp;личном кабинете в&nbsp;разделе <a href="/my/subscriptions/" class="underline">Мои подписки</a>.</p>
					<p class="popup-success popup-success-email-confirmed">Спасибо!</p>
					<p class="popup-error"><span class="icon icon-big-error nolink"></span></p>
					<p class="popup-error">Во&nbsp;время запроса произошла ошибка. Попробуйте повторить подписку или&nbsp;обратитесь к&nbsp;операторам интернет-магазина.</p>
				</div>
				<div class="clr"></div>
			</div>
			<div id="popup_infobox" class="popup" style="width:22em;margin-left:-11.5em;top:30%;">
				<div class="popup_header">
					<p></p>
				</div>
				<div class="popup_header_close popup_close">
					<span class="icon icon-close"></span>
				</div>
				<div class="clr"></div>
				<div class="form-center">
					<p class="popup-success"></p>
					<p class="popup-error"></p>
				</div>
				<div class="clr"></div>
			</div>
		</noindex>
	</div>
</body>
</html>
<?
transform::optimize(ob_get_clean());
ob_end_flush();
?>
