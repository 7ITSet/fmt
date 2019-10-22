<?
defined ('_DSITE') or die ('Access denied');
global $sql,$user;

$q='SELECT * FROM `formetoo_cdb`.`m_contragents` 
	LEFT JOIN `formetoo_cdb`.`m_contragents_rs` 
		ON `m_contragents_rs_contragents_id`=`m_contragents_id`  
	WHERE 
		`m_contragents_id`=3363726835 AND 
		`m_contragents_rs_main`=1 
		LIMIT 1;';
$contragent=$sql->query($q)[0];

$q='SELECT * FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id`='.$contragent['m_contragents_id'].';';
$address=$sql->query($q,'m_address_type');

$q='SELECT * FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$contragent['m_contragents_id'].';';
$tel=$sql->query($q);
?>
<h2>Основные реквизиты</h2>
<p><strong>Полное наименование</strong><br/><?=$contragent['m_contragents_c_name_full'];?></p>
<p><strong>Краткое наименование</strong><br/><?=$contragent['m_contragents_c_name_short'];?></p>
<p><strong>ИНН / КПП / ОГРН</strong><br/><?=$contragent['m_contragents_c_inn'].' / '.$contragent['m_contragents_c_kpp'].' / '.$contragent['m_contragents_c_ogrn'];?></p>
<p><strong>Должность руководителя</strong><br/><?=$contragent['m_contragents_c_director_post'];?></p>
<p><strong>Имя руководителя</strong><br/><?=$contragent['m_contragents_c_director_name'].' <span class="grey">(в род. падеже — '.$contragent['m_contragents_c_director_name_rp'].')</span>';?></p>
<p><strong>Юридический адрес</strong><br/><?=$address[1][0]['m_address_full'];?></p>
<p><strong>Адрес для корреспонденции (он же фактический)</strong><br/><?=$address[3][0]['m_address_full'];?></p>
<h2>Банковские рекизиты</h2>
<p><strong>Наименование банка</strong><br/><?=$contragent['m_contragents_rs_bank'];?></p>
<p><strong>БИК</strong><br/><?=$contragent['m_contragents_rs_bik'];?></p>
<p><strong>Расчётный счёт компании</strong><br/><?=$contragent['m_contragents_rs_rs'];?></p>
<p><strong>Корреспондентский счёт банка</strong><br/><?=$contragent['m_contragents_rs_ks'];?></p>
<h2>Контакты</h2>
<p><strong>Телефоны</strong></p>
<?
foreach($tel as $_tel){
	echo '<p><a href="tel:'.transform::telClean($_tel['m_contragents_tel_numb']).'">'.$_tel['m_contragents_tel_numb'].'</a> — '.$_tel['m_contragents_tel_comment'].'</p>';
}
?>
<p><strong>Электронная почта</strong><br/><a href="mailto:<?=$contragent['m_contragents_email'];?>"><?=$contragent['m_contragents_email'];?></a></p>
<p><strong>Сайт</strong><br/><a href="https://www.<?=$contragent['m_contragents_www'];?>"><?=$contragent['m_contragents_www'];?></a></p>