<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user;

$q='SELECT * FROM `formetoo_cdb`.`m_contragents` WHERE 
		`m_contragents_id`=3363726835  
		LIMIT 1;';
$contragent=$sql->query($q)[0];

$q='SELECT * FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id`='.$contragent['m_contragents_id'].';';
$address=$sql->query($q,'m_address_type');

$q='SELECT * FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$contragent['m_contragents_id'].';';
$tel=$sql->query($q);
?>
<h2>Контакты</h2>
<p><strong>Телефоны</strong></p>
<?
foreach($tel as $_tel){
	echo '<p><a href="tel:'.transform::telClean($_tel['m_contragents_tel_numb']).'">'.$_tel['m_contragents_tel_numb'].'</a> — '.$_tel['m_contragents_tel_comment'].'</p>';
}
?>
<p><strong>Электронная почта</strong><br/><a href="mailto:<?=$contragent['m_contragents_email'];?>"><?=$contragent['m_contragents_email'];?></a></p>
<p><strong>Сайт</strong><br/><a href="https://www.<?=$contragent['m_contragents_www'];?>"><?=$contragent['m_contragents_www'];?></a></p>
<h2>Адреса компании</h2>
<p><strong>Юридический адрес</strong><br/><?=$address[1][0]['m_address_full'];?></p>
<p><a id="address_3"></a><strong>Адрес для корреспондецнии</strong><br/><?=$address[3][0]['m_address_full'];?></p>
<p><a id="address_2"></a><strong>Центральный офис</strong><br/><?=$address[2][0]['m_address_full'];?></p>
<div id="map-office"></div>
<p><a id="address_40"></a><strong>Склад</strong><br/><?=$address[4][0]['m_address_full'];?></p>
<div id="map-stock1"></div>
<p><a id="address_41"></a><strong>Склад</strong><br/><?=$address[4][1]['m_address_full'];?></p>
<div id="map-stock2"></div>
<style>
	#map-office, #map-stock1, #map-stock2{
		width:100%;
		margin-bottom:10px;
		height:200px;
	}
</style>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
	ymaps.ready(init);

	function init() {
		var officeMap = new ymaps.Map(
					"map-office", {
						center: [<?=$address[2][0]['m_address_map_lat'];?>, <?=$address[2][0]['m_address_map_lon'];?>],
						zoom: 16
					},
					{
						searchControlProvider: 'yandex#search'
					}
				),
			 stockMap1 = new ymaps.Map(
					"map-stock1", {
						center: [<?=$address[4][0]['m_address_map_lat'];?>, <?=$address[4][0]['m_address_map_lon'];?>],
						zoom: 16
					},
					{
						searchControlProvider: 'yandex#search'
					}
				),
			 stockMap2 = new ymaps.Map(
					"map-stock2", {
						center: [<?=$address[4][1]['m_address_map_lat'];?>, <?=$address[4][1]['m_address_map_lon'];?>],
						zoom: 16
					},
					{
						searchControlProvider: 'yandex#search'
					}
				);
				
		officeMap.geoObjects
			.add(new ymaps.Placemark([<?=$address[2][0]['m_address_map_lat'];?>, <?=$address[2][0]['m_address_map_lon'];?>], {
				balloonContent: '<strong>Интернет-Магазин formetoo</strong>, центральный офис'
			}, {
				preset: 'islands#dotIcon',
				iconColor: '#735184'
			}));
		stockMap1.geoObjects
			.add(new ymaps.Placemark([<?=$address[4][0]['m_address_map_lat'];?>, <?=$address[4][0]['m_address_map_lon'];?>], {
				balloonContent: '<strong>Интернет-Магазин formetoo</strong>, склад'
			}, {
				preset: 'islands#dotIcon',
				iconColor: '#735184'
			}));
		stockMap2.geoObjects
			.add(new ymaps.Placemark([<?=$address[4][1]['m_address_map_lat'];?>, <?=$address[4][1]['m_address_map_lon'];?>], {
				balloonContent: '<strong>Интернет-Магазин formetoo</strong>, склад'
			}, {
				preset: 'islands#dotIcon',
				iconColor: '#735184'
			}));
			
	}
});
</script>