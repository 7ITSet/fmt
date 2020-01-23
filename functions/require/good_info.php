<?
defined ('_DSITE') or die ('Access denied');

$_GET['id']=$current_product['id'];

if($attr=$content->getItemAttributes()){
	$videoItem = $attr['VIDEO'];
	$docsItem = $attr['DOCS'];
	//варианты товара
	$gV=$content->getGoodVariants();
	$gv_count=sizeof($gV);
	for($i=0;$i<$gv_count;$i++)
		$goodVariants[$gV[$i]['id']]=$gV[$i];


	//выбираем все атрибуты всех вариантов (для возможности выбора в карточке товара), сгруппированные по типу атрибута
	$print_variants='';
	$q='SELECT * FROM `formetoo_main`.`m_products_attributes_list`
			RIGHT JOIN `formetoo_main`.`m_products_attributes`
				ON `m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
			RIGHT JOIN `formetoo_main`.`m_products_attributes_values`
				ON `m_products_attributes_values`.`m_products_attributes_values_id`=`m_products_attributes`.`m_products_attributes_value`
				WHERE
				`m_products_attributes`.`m_products_attributes_product_id` IN ('.implode(',',array_keys($goodVariants)).') AND
				`is_active`=1
			ORDER BY `m_products_attributes_list_name`,`m_products_attributes_values_value`;';
	$attr_variants=$sql->query($q,'m_products_attributes_list_id');
	$attr_values=array();
	foreach($attr_variants as $_attr_id=>$_attr_data)
		foreach($_attr_data as $_attr_data_variant)
			$attr_values[]=$_attr_data_variant['m_products_attributes_value'];
	//значения атрибутов
	foreach($attr as $_attr)
		if($_attr['m_products_attributes_list_type']!=2)
			$attr_values[]=$_attr['m_products_attributes_value'];
	if($attr_values)
		$attr_values_all=$sql->query('SELECT * FROM `formetoo_main`.`m_products_attributes_values` WHERE `m_products_attributes_values_id` IN('.implode(',',$attr_values).');','m_products_attributes_values_id');


	//генерируем выбор вариантов товара
	foreach($attr_variants as $_index=>$_attr_data)
		if(sizeof($_attr_data)<=1)
			unset($attr_variants[$_index]);
	//активный вариант товара
	$active_variants=array();
	foreach($attr_variants as $_type=>$_variants)
		foreach($_variants as $j=>$_variant){
			if($_variant['m_products_attributes_product_id']==$current_product['id'])
				$active_variants[$_type]=$_variant['m_products_attributes_value'];
		}
	/*------*/
	//возможные пересечения вариантов
	$print_variants_cross=array();
	//пробегаемся по каждому типу
	foreach($attr_variants as $_type=>$_values)
		//по каждому варианту типа
		foreach($_values as $_value)
			//если значение варианта типа является одноим из активных
			if($active_variants[$_type]==$_value['m_products_attributes_value'])
				//пробегаемся по всем типам снова
				foreach($attr_variants as $__type=>$__values)
					//пропускаем тип, с которого начали цикл
					if($__type==$_type) continue;
					//пробугаемся по всем варианам других типов
					else foreach($__values as $__value)
						//если среди них есть значение из активных
						if($__value['m_products_attributes_product_id']==$_value['m_products_attributes_product_id'])
							//добвляем его в массив пересечений
							$print_variants_cross[$__type][$__value['m_products_attributes_value']]=$__value;
	//пробегаемся по каждому типу
	foreach($attr_variants as $_type=>$_values){
		//заголовок типа
		$print_variants.='
			<p>'.$_values[0]['m_products_attributes_list_name'].'</p>
			<div class="main_products_list_items_pagination_container good_variants">
				<div class="pagination">';
		//обработанные варианты типа (только когда массив активных имеет единственное значение (т.е. пересеченией нет)
		$printed_0=array();
		//пробегаемся по каждому варианту типа
		foreach($_values as $_value){
			//пропускаемый вариант
			if(
				(
					//если пересечений нет и вариант уже обрабатывался
					sizeof($active_variants)==1&&
					in_array($_value['m_products_attributes_value'],$printed_0)
				)||
				(
					//или вариант есть в массиве пересечений, но это не текущй открытый вариант (очередной вариант типа, открытого в данный момент, но не сам тип)
					isset($print_variants_cross[$_type][$_value['m_products_attributes_value']])&&
					in_array($_value['m_products_attributes_value'],$print_variants_cross[$_type][$_value['m_products_attributes_value']])&&
					!($_value['m_products_attributes_product_id']==$current_product['id'])&&
					($_value['m_products_attributes_product_id']!=$print_variants_cross[$_type][$_value['m_products_attributes_value']]['m_products_attributes_product_id'])
				)
			) continue;
			//добавляем вариант в обработанные (если пересечений нет)
			elseif(sizeof($active_variants)==1) $printed_0[]=$_value['m_products_attributes_value'];
			$active=$inactive=false;
			//если вариант сейчас открыт (если пересечений нет)
			if($_value['m_products_attributes_product_id']==$current_product['id'])
				//выделяем активный пункт
				$active=true;
			//если варианта нет в пересечениях - помечаем его как неактивный
			if(
				isset($print_variants_cross[$_type][$_value['m_products_attributes_value']])&&
				!in_array($_value['m_products_attributes_value'],$print_variants_cross[$_type][$_value['m_products_attributes_value']])
			)
				$inactive=true;
			//выводим варианты
			$print_variants.='<a class="good_variants pagination-button first'.
				($active?' active':'').
				(!$active&&$inactive?' inactive':'').
				'" href="/product/'.$_value['m_products_attributes_product_id'].'/">'.
					$attr_values_all[$_value['m_products_attributes_value']][0]['m_products_attributes_values_value'].
				'</a>';
		}
		$print_variants.='
				</div>
			</div>';
	}
}


/*------*/

/*------*/
/* $arr_variants=array();
foreach($attr_variants as $_type=>$_variants){

	$arr_variants[$_type]=array();
	foreach($_variants as $_variant){
		$active=$_variant['m_products_attributes_product_id']==$current_product['id']?true:false;
		$inactive=false;
		if(!$active)
			foreach($attr_variants as $__type=>$__variants)
				if($__type!=$_type)
					foreach($__variants as $__variant)
						if($__variant['m_products_attributes_value']!=$active_variants[$__variant['m_products_attributes_list_id']])
							if($__variant['m_products_attributes_product_id']!=$current_product['id'])
								$inactive=true;

		$arr_variants_['product']=$_variant['m_products_attributes_product_id'];
		$arr_variants_['value_id']=$_variant['m_products_attributes_value'];
		$arr_variants_['value']=$attr_values_all[$_variant['m_products_attributes_value']][0]['m_products_attributes_values_value'];
		$arr_variants_['name']=$_variant['m_products_attributes_list_name'];
		$arr_variants_['active']=$active?1:0;
		$arr_variants[$_type][]=$arr_variants_;
	}
}
pre($active_variants);
pre($arr_variants);
$print_variants_cross=array();
foreach($arr_variants as $_type=>$_values){
	foreach($_values as $_value){
		if($active_variants[$_type]==$_value['value_id'])
			foreach($arr_variants as $__type=>$__values){
				if($__type==$_type) continue;
				foreach($__values as $__value){
					if($__value['product']==$_value['product'])
						$print_variants_cross[$__type][$__value['value_id']]=$__value;
				}

			}
	}

}
pre($print_variants_cross);

foreach($arr_variants as $_type=>$_values){
	$print_variants.='
		<p>'.$_values[0]['name'].'</p>
		<div class="main_products_list_items_pagination_container good_variants">
			<div class="pagination">';
	$printed_0=array();
	foreach($_values as $_value){
		if(sizeof($active_variants)==1&&in_array($_value['value_id'],$printed_0)) continue;
		elseif(sizeof($active_variants)==1) $printed_0[]=$_value['value_id'];
		if(
			in_array($_value['value_id'],$print_variants_cross[$_type][$_value['value_id']])&&
			!$_value['active']&&
			$_value['product']!=$print_variants_cross[$_type][$_value['value_id']]['product']
		)
			continue;
		$active=false;
		$inactive=false;
		if($_value['product']==$current_product['id'])
			$active=true;
		if(in_array($_value['value_id'],$print_variants_cross[$_type][$_value['value_id']])&&$_value['active'])
			$active=true;
		if(!in_array($_value['value_id'],$print_variants_cross[$_type][$_value['value_id']]))
			$inactive=true;
		$print_variants.='<a class="good_variants pagination-button first'.
			($active?' active':'').
			(!$active&&$inactive?' inactive':'').
			'" href="/product/'.$_value['product'].'/">'.$_value['value'].'</a>';
	}
	$print_variants.='
			</div>
		</div>';
} */
/*------*/

$unit=$content->getGoodUnit();

$text=$content->getGoodText()[0];

$sort_reviews=array(
	'rel_desc'=>'Полезности',
	'date_desc'=>'Свежести',
	'date_ask'=>'Давности',
	'rate_desk'=>'Высокие оценки',
	'rate_ask'=>'Низкие оценки'
);
$reviews=$content->getGoodReviews();

$sort_qna=array(
	'rel_desc'=>'Полезности',
	'date_desc'=>'Свежести',
	'date_ask'=>'Давности'
);
$QNA=$content->getGoodQNA();

$q='SELECT `m_info_city_id`,`m_info_city_name_city_im` FROM `formetoo_main`.`m_info_city`;';
$cities=$sql->query($q,'m_info_city_id');

$price=$content->getGoodPrice();

$uact=$user->getUserActions();
if(isset($uact[13])&&sizeof($uact[13])>50){
	$captcha_reg=true;
}
else $captcha_reg=false;


?>

<div itemscope itemtype="http://schema.org/Product">
<div class="main_container_header">
	<div class="breadcrumbs">
		<?$menu->breadcrumbs(true)?>
	</div>
	<div class="clr"></div>
</div>
<div class="main_container_body good_info">
	<div class="main_content good_info_content">
		<div class="main_products_list_items">
            <div class="good_info row top-lg">
                <div class="main_products_list_items_info_foto col-lg-4 col-xs">
                    <div class="main_products_list_items_info_foto_gallery carousel" id="carousel">
                        <div class="main_products_list_items_info_foto_gallery_container">
                            <div class="main_products_list_items_info_foto_gallery_container_slider">
                            <?
                                //сортируем массив с фото, делаем главную фотку первой
                                $current_product['m_products_foto']=json_decode($current_product['m_products_foto'],true);
                                foreach ($current_product['m_products_foto'] as $key=>$row) {
                                    $file[$key]=$row['file'];
                                    $main[$key]=$row['main'];
                                }
                                array_multisort($main,SORT_DESC,$current_product['m_products_foto']);

                                if($current_product['m_products_foto'])
                                    foreach($current_product['m_products_foto'] as $_foto)
                                        echo '
                                            <div class="main_products_list_items_info_foto_gallery_item'.($_foto['main']?' selected':'').'">
                                                <a data-fancybox="gallery" href=" https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_max.'.$_foto['ext'].'" onclick="return false;">
                                                    <img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src=" https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_min.'.$_foto['ext'].'  " data-med=" https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_med.'.$_foto['ext'].'" data-max=" https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_max.'.$_foto['ext'].'"'.($_foto['main']?' itemprop="image"':'').' >
                                                </a>
                                            </div>';

                                    //было так
//                     <a data-fancybox="gallery" href="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_max.jpg" onclick="return false;">
//                     <img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_min.jpg" data-med="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_med.jpg" data-max="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_max.jpg"'.($_foto['main']?' itemprop="image"':'').' >
                            ?>
                            </div>
                        </div>
                    </div>
                    <div class="main_products_list_items_info_foto_preview">
                        <div class="main_products_list_items_info_foto_preview_container">
                        <?
                            if($current_product['m_products_foto'])
                                $mainfoto=0;
                                foreach($current_product['m_products_foto'] as $_foto)
                                    if($_foto['main']){
//                                        echo '<img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_med.jpg" data-origin="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_max.jpg">';
                                        echo '<img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src="https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_med.'.$_foto['ext'].'" data-origin="https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_max.'.$_foto['ext'].'">';
                                        $mainfoto=1;
                                    }
                                if(!$mainfoto)
                                    foreach($current_product['m_products_foto'] as $_foto){
//                                        echo '<img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_med.jpg" data-origin="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_max.jpg">';
                                        echo '<img title="'.htmlspecialchars($current_product['m_products_name_full']).'" src="https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_med.'.$_foto['ext'].'" data-origin="https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_max.'.$_foto['ext'].'">';
                                        break;
                                    }
                        ?>

                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xs">
                    <div class="row">
                        <div class="about_good col-lg-8 col-xs">
                            <div class="good_title">
                                <h1 itemprop="name"><?=$current['h1']?></h1>
                            </div>
                            <div class="main_products_list_items_miniinfo">
                                <span class="exist_span">Наличие на складе: </span>
                                <div class="exist">
                                    <noindex>
                                <span class="gif-mini-preloader-container">
                                    <img src="/img/loader-firstyle-4.svg" class="gif-mini-preloader" title="Уточняем наличие…"/>
                                </span>
                                        <?=($current_product['m_products_exist']==1?'<p class="exist-1"> <a href="#" rel="nofollow" title="Нажмите, чтобы уточнить наличие" class="dotted" id="get_exist">Уточнить наличие</a></p>':'<p class="exist-0">Нет в наличии, заказ от 30000 р.</p>');?>
                                    </noindex>
                                </div>
                            </div>
                            <div class="main_products_list_items_miniinfo">
                                <?
                                echo '
                        <div class="main_products_list_items_miniinfo_art">Код товара: <span class="grey">'.substr($current_product['id'],0,3).''.substr($current_product['id'],3,3).''.substr($current_product['id'],6).'</span></div>
                        <div class="main_products_list_items_miniinfo_rating">
                            <div class="rating" title="'.($current_product['m_products_rate']?'Средняя оценка: '.$current_product['m_products_rate'].' на основании '.$current_product['m_products_feedbacks'].' '.transform::numberof(($current_product['m_products_rate']?$current_product['m_products_feedbacks']:0),'оцен',array('ки','ок','ок')):'У товара пока нет оценок и отзывов').'">
                                <div class="stars">';
                                for($i=1;$i<=5;$i++)
                                    echo '<div class="star'.($current_product['m_products_rate']>=$i?' full-fill':(abs($current_product['m_products_rate']-$i)<=.5?' half-fill':'')).'"></div>';
                                echo '
                                </div>
                                <div class="feedbacks-count">
                                    <a href="#products_reviews" class="dotted" id="downto_products_reviews">
                                        '.($current_product['m_products_rate']?$current_product['m_products_feedbacks']:0).' '.transform::numberof(($current_product['m_products_rate']?$current_product['m_products_feedbacks']:0),'оцен',array('ка','ки','ок')).'
                                    </a>
                                </div>
                            </div>
                        </div>
                        ';
                                ?>
                            </div>
                            <div class="good_info_form">
                                <? if ($user->isVisiblePrice()) { ?>
                                    <form class="main_products_list_items_info_pay" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                        <div class="main_products_list_items_info_pay_price" itemprop="price" content="<?=$price['price'];?>">
                                            <?=transform::price_o($price['price']);?><span class="rouble"> руб.</span>
                                        </div>
                                        <meta itemprop="priceCurrency" content="RUB">
                                        <?
                                        if($price['bonus']){
                                            ?>
                                            <div class="main_products_list_items_info_pay_bonus">
                                                +&nbsp;<?=transform::price_o($price['bonus']);?><span class="rouble">&nbsp;&#8381;</span>
                                                <p class="desc">на бонусную карту</p>
                                            </div>
                                        <?}?>

                                        <div class="main_products_list_items_info_pay_count">
                                            <div class="info_pay_count_inputs_parent">
                                                <div class="main_products_list_items_info_pay_count_inputs">
                                                    <div class="main_products_list_items_info_pay_count_inputs_pay_count_change">
                                                        <span id="pay_count_minus" onselectstart="return false">–</span>
                                                    </div>
                                                    <input type="text" name="pay_count" id="pay_count" data-unitvolume="<?=round($current_product['measure_ratio'],4);?>" value="<?=round($current_product['measure_ratio'],4);?>"/>
                                                    <div class="main_products_list_items_info_pay_count_inputs_pay_count_change">
                                                        <span id="pay_count_plus" onselectstart="return false">+</span>
                                                    </div>
                                                </div>
                                                <div class="main_products_list_items_info_pay_count_units">
                                                    <?=$unit['m_info_units_name'];?>
                                                </div>
                                            </div>
                                            <div class="clr"></div>
                                            <div class="main_products_list_items_info_pay_count_units_add">
                                                <ul class="list_dotts">
                                                    <?
                                                    if($attr)
                                                        switch($unit['m_info_units_name']){
                                                            case 'упак':
                                                                foreach($attr as $_attr){
                                                                    //кол-во в упаковке (шт)
                                                                    if($_attr['m_products_attributes_list_id']==3784943609)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Количество</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;шт</div>
                                                                </li>';
                                                                    //кол-во в упаковке (м2)
                                                                    if($_attr['m_products_attributes_list_id']==6447361156||$_attr['m_products_attributes_list_id']==2233750374)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>2</sup></div>
                                                                </li>';
                                                                    //кол-во в упаковке (м3)
                                                                    if($_attr['m_products_attributes_list_id']==3493624856)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>3</sup></div>
                                                                </li>';
                                                                }
                                                                break;
                                                            case 'м2':
                                                                foreach($attr as $_attr){
                                                                    //кол-во в упаковке (шт)
                                                                    if($_attr['m_products_attributes_list_id']==3784943609)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Количество</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;шт</div>
                                                                </li>';
                                                                    //кол-во в упаковке (м2)
                                                                    if($_attr['m_products_attributes_list_id']==6447361156||$_attr['m_products_attributes_list_id']==2233750374)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>2</sup></div>
                                                                </li>';
                                                                    //кол-во в упаковке (м3)
                                                                    if($_attr['m_products_attributes_list_id']==3493624856)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>3</sup></div>
                                                                </li>';
                                                                }
                                                                break;
                                                            case 'шт':
                                                                foreach($attr as $_attr){
                                                                    //кол-во в упаковке (м2)
                                                                    if($_attr['m_products_attributes_list_id']==6447361156||$_attr['m_products_attributes_list_id']==2233750374)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Площадь</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>2</sup></div>
                                                                </li>';
                                                                    //кол-во в упаковке (м3)
                                                                    if($_attr['m_products_attributes_list_id']==3493624856)
                                                                        echo '
                                                                <li>
                                                                        <div class="list_dotts_name"><span class="list_dotts_name_text">Объём</span></div>
                                                                        <div class="list_dotts_value" data-value="'.$_attr['m_products_attributes_value'].'"><span>'.$_attr['m_products_attributes_value'].'</span>&nbsp;м<sup>3</sup></div>
                                                                </li>';
                                                                }
                                                                break;
                                                            default:
                                                                break;
                                                        }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="main_products_list_items_info_pay_buy">
                                            <span id="pay_add_cart" onselectstart="return false">Купить</span>
                                            <input class="product_id" type="hidden" name="product_id" data-value="<?=$current_product['id'];?>" value="<?=$current_product['id'];?>"/>
                                        </div>
                                    </form>
                                <? } ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div>
                                <a href="#" class="good_info_comparison_a comparison_a" title="Сравнить">
                                    <span class="good_info_comparison_icon"></span>
                                </a>
                                <a href="#" class="good_info_goods_a goods_a" title="Отложить">
                                    <span class="good_info_like_icon"></span>
                                </a>
                                <a href="#" class="share" title="Поделиться">
                                    <img src="/img/share.svg" alt="поделиться" class="share_img">
                                </a>
                                <a href="https://wa.me/79105199977" class="good_info_whatsapp_href" target="_blank">
                                    <img src="/img/whatsapp.png" alt="whatsapp_icon">
                                </a>
                            </div>
                            <? if ($user->isVisiblePrice()) { ?>
                                <span id="pay_oneclick" onselectstart="return false">Быстрый заказ</span>
                            <? } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="detail-info_header">
                                <div class="detail-info_header_item active" id="products_tech">Технические характеристики</div>
                                <div class="detail-info_header_item" id="products_about">О товаре</div>
                                <? if ($docsItem) { ?>
                                    <div class="detail-info_header_item" id="products_docs">Документы и сертификаты<span class="detail-info_header_item_count"></span></div>
                                <? } ?>
                                <? if ($videoItem) { ?>
                                    <div class="detail-info_header_item" id="products_video">Видео<span class="detail-info_header_item_count"></span></div>
                                <? } ?>
                                <div class="detail-info_header_item" id="products_reviews">Отзывы<span class="detail-info_header_item_count"><?=$reviews?sizeof($reviews):0;?></span></div>
                                <div class="detail-info_header_item" id="products_qa">Вопрос-ответ<span class="detail-info_header_item_count"><?=$QNA?sizeof($QNA):0;?></span></div>
                            </div>
                            <div class="clr"></div>
                            <div class="detail-info__body">
                                <div class="detail-info__body_item products_tech active">
                                    <? if($attr) { ?>
                                        <ul class="list_dotts">
                                            <?
                                            foreach($attr as $_attr) {
                                                if ( $_attr['is_visible_detail']) {
                                                    echo '
											<li>
												<div class="list_dotts_name" data-list-id="'.$_attr['m_products_attributes_list_id'].'">
													<span class="list_dotts_name_text">'.$_attr['m_products_attributes_list_name'].($_attr['m_products_attributes_list_hint']?'<span class="icon icon-question-circle main_products_filters_name_hint"></span>':'').'</span>
												</div>
												<div class="list_dotts_value">



												'.($_attr['m_products_attributes_list_type']==2?$_attr['m_products_attributes_value'].'&nbsp;'.$_attr['m_products_attributes_list_unit']:$_attr['m_products_attributes_value']).'



												</div>',
                                                    ($_attr['m_products_attributes_list_hint']?'
												<div class="main_products_filters_desc_container">
													<span class="main_products_filters_desc">
														<noindex><b>'.$_attr['m_products_attributes_list_name'].($_attr['m_products_attributes_list_unit']?'&nbsp;<span>'.$_attr['m_products_attributes_list_unit'].'<span>':'').'</b>
														'.$_attr['m_products_attributes_list_hint'].'</noindex>
														<span class="icon icon-close"></span>
													</span>
												</div>':''),
                                                    '</li>
										';
                                                }
                                            }
                                            ?>
                                        </ul>
                                    <?}?>
                                    <div class="clr"></div>
                                </div>
                                <!--					<div class="detail-info__body_item products_about" itemprop="description"><noindex>--><?//=str_replace('<p>&nbsp;</p>','',$text['m_products_desc_text']);?><!--</noindex></div>-->
                                <div class="detail-info__body_item products_about" itemprop="description"><noindex><?=str_replace('<p>&nbsp;</p>','',htmlspecialchars_decode($text['m_products_desc_text']));?></noindex></div>
                                <? if ($docsItem){
                                    $docsArray = json_decode($docsItem['m_products_attributes_value']);
                                    ?>
                                    <div class="detail-info__body_item products_docs">
                                        <div class="products_docs_container">
                                            <?foreach($docsArray as $_doc){
                                                $ext=explode('.',$_doc->file);
                                                $ext=array_pop($ext);
                                                // echo '
                                                // <div class="products_docs_container_item">
                                                // 	<div class="products_docs_container_item_type_img">
                                                // 		<span class="icon icon-'.$ext.'"></span>
                                                // 	</div>
                                                // 	<div class="products_docs_container_item_info">
                                                // 		<p class="products_docs_container_item_info_name">'.$_doc['m_products_docs_desc'].'</p>
                                                // 		<p class="products_docs_container_item_info_download"><a href="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($_doc['m_products_docs_filedir'],0,2).'/SN'.$_doc['m_products_docs_filedir'].'/'.$_doc['m_products_docs_filename'].'" target="_blank">Скачать<span class="underline"></span></a></p>
                                                // 		<p class="products_docs_container_item_info_fileinfo">'.$ext.', '.$_doc['m_products_docs_filesize'].'</p>
                                                // 	</div>
                                                // 	<div class="clr"></div>
                                                // </div>';
                                                ?>
                                                <div class="products_docs_container_item">
                                                    <div class="products_docs_container_item_type_img">
                                                        <span class="icon icon-<?=$ext?>"></span>
                                                    </div>
                                                    <div class="products_docs_container_item_info">
                                                        <p class="products_docs_container_item_info_name"><?=$_doc->name?></p>
                                                        <p class="products_docs_container_item_info_download"><a href="//crm.formetoo.loc/uploads/files/products/<?=$current_product['id']?>/<?=$_doc->file?>" target="_blank">Скачать<span class="underline"></span></a></p>
                                                        <p class="products_docs_container_item_info_fileinfo"><?=$ext?>, <?=$_doc->size?></p>
                                                    </div>
                                                    <div class="clr"></div>
                                                </div>
                                            <? } ?>
                                            <div class="clr"></div>
                                        </div>
                                    </div>
                                <? } ?>

                                <? if ($videoItem){ ?>
                                    <div class="detail-info__body_item products_video">
                                        <?=$videoItem['m_products_attributes_value'];?>
                                    </div>
                                <? } ?>

                                <div class="detail-info__body_item products_reviews">
                                    <div class="main_products_list_toppanel_sort">
                                        <label>Сортировать по&nbsp;</label>
                                        <div class="select_default_container">
                                            <div class="select_default">
                                                <p class="select_default_option_selected"><?=$sort_reviews['rel_desc'];?></p>
                                                <div class="items">
                                                    <?
                                                    foreach($sort_reviews as $_name=>$_name_ru)
                                                        echo $_name!=null?'<p class="select_default_option'.($_name=='rel_desc'?' selected':'').'" data-value="'.$_name.'">'.$_name_ru.'</p>':'';
                                                    ?>
                                                </div>
                                                <span class="icon icon-arrow-down"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clr"></div>
                                    <div class="hr"></div>
                                    <?
                                    if($reviews){
                                        ?>
                                        <div class="products_reviews_container">
                                            <?
                                            foreach($reviews as $_review){
                                                if(!$_review['m_products_feedbacks_text_plus']&&!$_review['m_products_feedbacks_text_minus']&&!$_review['m_products_feedbacks_text_total'])
                                                    continue;
                                                echo '
							<div class="products_reviews_container_item" id="review'.$_review['m_products_feedbacks_id'].'">
								<div class="products_reviews_date_from">
									<div class="products_reviews_date_from_name">'.$_review['m_users_name'].'</div>
									<div class="products_reviews_date_from_city">'.$cities[$_review['m_users_city']][0]['m_info_city_name_city_im'].'</div>
									<div class="products_reviews_date_from_datetime">'.transform::date_f(dtu($_review['m_products_feedbacks_date']),true).'<br/>'.dtu($_review['m_products_feedbacks_date'],'H:m:i').'</div>
								</div>
								<div class="products_reviews_body">
									<div class="products_reviews_body_stars">
										<div class="stars">
									';
                                                for($i=1;$i<=5;$i++)
                                                    echo '<div class="star'.($_review['m_products_feedbacks_rating']>=$i?' full-fill':(abs($_review['m_products_feedbacks_rating']-$i)<=.5?' half-fill':'')).'" title="Пользователь оценил этот товар в '.$_review['m_products_feedbacks_rating'].' '.transform::numberof($_review['m_products_feedbacks_rating'],'балл',array('','а','ов')).'"></div>';
                                                echo '
										</div>
									</div>
									<div class="products_reviews_body_review">
										'.$_review['m_products_feedbacks_text_total'].'
									</div>';
                                                if($_review['m_products_feedbacks_text_plus'])
                                                    echo '
									<div class="products_reviews_body_plus">
										<p class="strong"><b>Достоинства</b></p>
										'.$_review['m_products_feedbacks_text_plus'].'
									</div>';
                                                if($_review['m_products_feedbacks_text_minus'])
                                                    echo '
									<div class="products_reviews_body_minus">
										<p class="strong"><b>Недостатки</b></p>
										'.$_review['m_products_feedbacks_text_minus'].'
									</div>';
                                                echo '
									<div class="products_reviews_body_likes">
										<span class="desc">Оцените отзыв</span>
										<div class="likes_block">
											<div class="likes_block_like">
												<span class="icon icon-like"></span>
											</div>
											<div class="likes_block_value">
												'.($_review['m_products_feedbacks_like']<=0?($_review['m_products_feedbacks_like']?'<span class="bad">'.$_review['m_products_feedbacks_like'].'</span>':'<span>0</span>'):'<span class="good">+'.$_review['m_products_feedbacks_like']).'</span>
											</div>
											<div class="likes_block_dislike">
												<span class="icon icon-dislike"></span>
											</div>
										</div>
									</div><div class="clr"></div>';
                                                //пометка отзыва о другом варианте товара
                                                if($_review['m_products_feedbacks_products_id']!=$current_product['id']){
                                                    echo '
											<div class="products_reviews_other_variant">
												<p>Этот отзыв о другом варианте товара: <a class="underline" href="/product/'.$_review['m_products_feedbacks_products_id'].'/">'.$goodVariants[$_review['m_products_feedbacks_products_id']]['m_products_name'].'</a></p>
											</div>';
                                                }
                                                echo '
								</div>
							</div>
							<div class="clr"></div>
							<div class="hr"></div>';
                                            }
                                            ?>

                                        </div>
                                    <?}
                                    else{?>
                                        <div class="products_reviews_container">
                                            <p class="main_products_list_null"><?=$current_product['m_products_name_full'];?> пока не имеет отзывов на нашем сайте.<br>Если уже имели дело с этим товаром, пожалуйста, <a href="#" class="dashed" onclick="$('#add_review').triggerHandler('click');">оставьте отзыв первым</a>, или оцените товар →</p>
                                        </div>
                                    <?}?>
                                    <div class="products_reviews_stars">
                                        <div class="products_reviews_stars_my">
                                            <p>Ваша оценка</p>
                                            <div class="products_reviews_stars_my_rateit">
                                                <div class="products_reviews_stars_my_rateit_stars">
                                                    <div class="stars">
                                                        <div class="star" title="Оценить этот товар на 1 балл"></div><div class="star" title="Оценить этот товар на 2 балла"></div><div class="star" title="Оценить этот товар на 3 балла"></div><div class="star" title="Оценить этот товар на 4 балла"></div><div class="star" title="Оценить этот товар на 5 баллов"></div>
                                                    </div>
                                                </div>
                                                <div class="clr"></div>
                                                <div class="products_reviews_stars_my_rateit_review">
                                                    <div class="mini-button" id="add_review" onselectstart="return false"><span class="desc">Оставить отзыв о товаре</span><span class="icon icon-comment"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                        if($reviews){
                                            ?>
                                            <div class="products_reviews_stars_all">
                                                <p>Оценка покупателей</p>
                                                <div class="products_reviews_stars_all_stars">
                                                    <div class="stars">
                                                        <?
                                                        for($i=1;$i<=5;$i++)
                                                            echo '<div class="star'.($current_product['m_products_rate']>=$i?' full-fill':(abs($current_product['m_products_rate']-$i)<=.5?' half-fill':'')).'"></div>';
                                                        ?>
                                                    </div>
                                                    <span class="products_reviews_stars_all_desc">
									<?
                                    echo ($current_product['m_products_rate']?$current_product['m_products_feedbacks']:0).' '.transform::numberof(($current_product['m_products_rate']?$current_product['m_products_feedbacks']:0),'оцен',array('ка','ки','ок'));
                                    ?>
									</span>
                                                </div>
                                                <div class="clr"></div>
                                                <div class="products_reviews_stars_all_details">
                                                    <div class="products_reviews_body_stars">
                                                        <?
                                                        for($i=5;$i>=1;$i--){
                                                            echo '<div class="stars">';
                                                            for($j=1;$j<=5;$j++)
                                                                echo '<div class="star'.($i>=$j?' full-fill':(abs($i-$j)<=.5?' half-fill':'')).'"></div>';
                                                            $rev_count=0;
                                                            foreach($reviews as $_review)
                                                                if($_review['m_products_feedbacks_rating']==$i){
                                                                    $rev_count++;
                                                                    break;
                                                                }
                                                            echo '
												<span class="desc">'.$rev_count.' '.transform::numberof($rev_count,'оцен',array('ка','ки','ок')).'</span>
												</div>
												<div class="clr"></div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?}?>
                                    </div>
                                    <div class="clr"></div>
                                </div>
                                <div class="detail-info__body_item products_qa">
                                    <div class="main_products_list_toppanel_sort">
                                        <label>Сортировать по&nbsp;</label>
                                        <div class="select_default_container">
                                            <div class="select_default">
                                                <p class="select_default_option_selected"><?=$sort_qna['rel_desc'];?></p>
                                                <div class="items">
                                                    <?
                                                    foreach($sort_qna as $_name=>$_name_ru)
                                                        echo $_name!=null?'<p class="select_default_option'.($_name=='rel_desc'?' selected':'').'" data-value="'.$_name.'">'.$_name_ru.'</p>':'';
                                                    ?>
                                                </div>
                                                <span class="icon icon-arrow-down"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="products_reviews_stars">
                                        <div class="products_reviews_stars_my">
                                            <div class="products_reviews_stars_my_qna">
                                                <div class="mini-button" id="add_qna" onselectstart="return false"><span class="desc">Задать вопрос о товаре</span><span class="icon icon-question"></span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clr"></div>
                                    <div class="hr"></div>
                                    <?
                                    if($QNA){
                                        ?>
                                        <div class="products_reviews_container">
                                            <?
                                            //показываем только вопросы (ссылка на вопрос = 0)
                                            foreach($QNA[0] as $_q_id=>$_review){
                                                echo '
							<div class="products_reviews_container_item" id="qna'.$_review['m_products_qna_id'].'">
								<div class="products_reviews_date_from">
									<div class="products_reviews_date_from_name">'.$_review['m_users_name'].'</div>
									<div class="products_reviews_date_from_city">'.$cities[$_review['m_users_city']][0]['m_info_city_name_city_im'].'</div>
									<div class="products_reviews_date_from_datetime">'.transform::date_f(dtu($_review['m_products_qna_date']),true).'<br/>'.dtu($_review['m_products_qna_date'],'H:m:i').'</div>
								</div>
								<div class="products_reviews_body">
									<div class="products_reviews_body_review">
										'.$_review['m_products_qna_text'].'
									</div>';
                                                echo '
									<div class="clr"></div>
									<div class="products_reviews_body_my_answer">
										<div class="mini-button" id="add_answer" onselectstart="return false"><span class="desc">Ответить</span><span class="icon icon-pencil"></span></div>
									</div>
									<div class="products_reviews_body_likes">
										<span class="desc">Оцените вопрос</span>
										<div class="likes_block">
											<div class="likes_block_like">
												<span class="icon icon-like"></span>
											</div>
											<div class="likes_block_value">
												'.($_review['m_products_qna_like']<=0?($_review['m_products_qna_like']?'<span class="bad">'.$_review['m_products_qna_like'].'</span>':'<span>0</span>'):'<span class="good">+'.$_review['m_products_qna_like']).'</span>
											</div>
											<div class="likes_block_dislike">
												<span class="icon icon-dislike"></span>
											</div>
										</div>
									</div>
									<div class="clr"></div>';
                                                //пометка вопроса о другом варианте товара
                                                if($_review['m_products_qna_products_id']!=$current_product['id']){
                                                    echo '
									<div class="products_reviews_other_variant">
										<p>Этот вопрос о другом варианте товара: <a class="underline" href="/product/'.$_review['m_products_qna_products_id'].'/">'.$goodVariants[$_review['m_products_qna_products_id']]['m_products_name'].'</a></p>
									</div>';
                                                }
                                                echo '
								</div>
							</div>
							<div class="clr"></div>
							<div class="hr"></div>';
                                                //если есть ответы на вопросы
                                                $icon_answer=0;
                                                if(isset($QNA[$_review['m_products_qna_id']]))
                                                    foreach($QNA[$_review['m_products_qna_id']] as $__review){
                                                        echo (!$icon_answer++?'<div class="products_reviews_container_item_answer"><span class="icon icon-answer"></span></div>':'<div class="products_reviews_container_item_answer"></div>').'
							<div class="products_reviews_container_item sub" id="qna'.$__review['m_products_qna_id'].'">
								<div class="products_reviews_date_from">
									<div class="products_reviews_date_from_name">'.$__review['m_users_name'].'</div>
									<div class="products_reviews_date_from_city">'.$cities[$__review['m_users_city']][0]['m_info_city_name_city_im'].'</div>
									<div class="products_reviews_date_from_datetime">'.transform::date_f(dtu($__review['m_products_qna_date']),true).'<br/>'.dtu($__review['m_products_qna_date'],'H:m:i').'</div>
								</div>
								<div class="products_reviews_body">
									<div class="products_reviews_body_review">
										'.$__review['m_products_qna_text'].'
									</div>';
                                                        echo '
									<div class="clr"></div>
									<div class="products_reviews_body_likes">
										<span class="desc">Оцените вопрос</span>
										<div class="likes_block">
											<div class="likes_block_like">
												<span class="icon icon-like"></span>
											</div>
											<div class="likes_block_value">
												'.($__review['m_products_qna_like']<=0?($__review['m_products_qna_like']?'<span class="bad">'.$__review['m_products_qna_like'].'</span>':'<span>0</span>'):'<span class="good">+'.$__review['m_products_qna_like']).'</span>
											</div>
											<div class="likes_block_dislike">
												<span class="icon icon-dislike"></span>
											</div>
										</div>
									</div>
									<div class="clr"></div>';
                                                        //пометка ответа о другом варианте товара
                                                        if($__review['m_products_qna_products_id']!=$current_product['id']){
                                                            echo '
									<div class="products_reviews_other_variant">
										<p>Этот ответ о другом варианте товара: <a class="underline" href="/product/'.$__review['m_products_qna_products_id'].'/">'.$goodVariants[$__review['m_products_qna_products_id']]['m_products_name'].'</a></p>
									</div>';
                                                        }
                                                        echo '
								</div>
							</div>
							<div class="clr"></div>
							<div class="hr"></div>';
                                                    }
                                            }
                                            ?>
                                        </div>
                                    <?}
                                    else{?>
                                        <div class="products_reviews_container">
                                            <p class="main_products_list_null">Здесь ещё не было вопросов об этом товаре. Если Вас интересует дополнительная информация, <a href="#" class="dashed" onclick="$('#add_qna').triggerHandler('click');">оставьте свой вопрос</a>.</p>
                                        </div>
                                    <?}?>
                                    <div class="clr"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
<?if($current_product['m_products_links']){?>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories no-background after_good">
			<h2>Могут пригодиться</h2>
		</div>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories links_goods product_links">
			<?$content->getGoods($current_product['id'],'table',20);?>
		</div>
<?}?>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories no-background after_good viewed_goods_header">
			<h2>Просмотренные товары</h2>
		</div>
		<div class="clr"></div>
		<div class="main_products_list_items after_categories viewed_goods product_links"></div>
	</div>
</div>
</div>
<div id="popup_quicksubmit" class="popup" style="width:22em;margin-left:-11.5em;top:30%;">
	<div class="popup_header">
		<p>Быстрый заказ</p>
	</div>
	<div class="popup_header_close popup_close">
		<span class="icon icon-close"></span>
	</div>
	<div class="clr"></div>
	<div id="form_quicksubmit_done" class="form-center">
		<p><span class="icon icon-big-ok"></span></p>
		<p>Ваш заказ № <span class="form_quicksubmit_done_result"></span> оформлен. В ближайшее время с Вами свяжется менеджер для уточнения деталей.</p>
	</div>
	<div class="clr"></div>
	<form id="form_quicksubmit" action="/" method="post">
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="name" autocomplete="off" placeholder="имя *" value="<?=($user->getInfo('m_users_name')?$user->getInfo('m_users_name'):'');?>">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="email" autocomplete="off" placeholder="e-mail" value="<?=($user->getInfo('m_users_email')?$user->getInfo('m_users_email'):'');?>">
		</div>
		<div class="clr"></div>
		<div class="login_authorization_form_input_container form-center">
			<input type="text" name="tel" autocomplete="off" placeholder="моб. телефон" value="<?=($user->getInfo('m_users_tel')?$user->getInfo('m_users_tel'):'');?>">
		</div>
		<div class="clr"></div>
<?
if($captcha_reg){
?>
		<div class="login_authorization_form_input_container">
			<input type="text" name="captcha" placeholder="текст с картинки *">
		</div>
		<div class="login_authorization_form_input_container">
			<img width="95" src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
		</div>
		<div class="clr"></div>
<?
}
?>
		<div class="login_authorization_form_input_container form-center">
			<button type="submit" class="med_button" id="reg_submit">Отправить заказ</button>
		</div>
		<input type="hidden" name="token" value="<?=$user->getInfo('cookies_token');?>">
		<input type="hidden" name="handler" value="user_registration_quick">
		<input type="hidden" name="from_cart" value="1">
		<div class="clr"></div>
		<p><span class="small">Нажимая кнопку «Отправить заказ»:</span></p>
		<div class="login_authorization_form_input_container">
			<div class="cb">
				<input name="politic" id="politic" type="checkbox" checked value="1"/>
				<label for="politic" onselectstart="return false">Я принимаю <a href="/terms-of-sale/" class="underline" target="_blank">Условия продажи товаров</a> и даю своё согласие Интернет-магазину на обработку моей персональной информации на условиях, определенных <a href="/personal-data-agreement/" class="underline" target="_blank">Политикой конфиденциальности</a>.</label>
			</div>
		</div>
	</form>
</div>

<div class="goods_avails">
    <div class="goods_avails_header">
        <h1>Наличие товара ({ГОРОД})</h1>
    </div>
    <div class="goods_avails_content">
        <div class="tabs">
            <div class="tab_list active">Список</div>
            <div class="tab_map">Карта</div>
        </div>
        <div class="panels">
            <div class="avails_list active">
                <div class="list_head">
                    <span class="name_adress">Название и адрес магазина</span>
                    <span class="schedule">График работы</span>
                    <span class="buy_good">Наличие товара</span>
                </div>
                <div class="list_items">
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                    <div class="avails_list_item row">
                        <div class="col-lg-6">
                            <input type="radio">
                            <div class="shop_name">
                                <p>г. Москва</p>
                                <p>ул. Тамбовская, д. 69Б</p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <p>пн-пт: 09:00 - 18:00</p>
                        </div>
                        <div class="col-lg-3">
                            <span>осталось: </span>
                            10
                        </div>
                    </div>
                </div>
            </div>
            <div class="avails_map">

            </div>
        </div>
    </div>
</div>

<style>
	#form_quicksubmit_done{
		display:none;
	}
	.form_quicksubmit_done_result{
		font-weight:700;
	}
</style>
<link rel="stylesheet" type="text/css" href="/js/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="/js/slick/slick-theme.css"/>
<script type="text/javascript" src="/js/jquery.imgzoom.js"></script>
<link rel="stylesheet" href="/js/fancybox/jquery.fancybox.min.css" />
<script src="/js/fancybox/jquery.fancybox.min.js"></script>

<script type="text/javascript" src="/js/validation/core.js"></script>
<script type="text/javascript" src="/js/validation/localization/messages_ru.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="/js/slick/slick.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		/* ФОТОГАЛЕРЕЯ */
		<?if($current_product['m_products_foto']){?>
		$('.main_products_list_items_info_foto_gallery_item')
			.on('mouseenter',function(){
				$('.main_products_list_items_info_foto_preview_container').off();
				$(this).addClass('selected').siblings().removeClass('selected');
				preview=$('.main_products_list_items_info_foto_preview img');
				preview.parent().off();
				preview.attr("src",$(this).find('img').attr('data-med'));
				preview.attr("data-origin",$(this).find('img').attr('data-max'));
				$('.main_products_list_items_info_foto_preview_container').imgZoom({startPos:$('.main_products_list_items_info_foto')});
				currentimage=$(this).find('a');
				index=$(this).index();
				preview.parent().on("click",function(){
					$.fancybox.open([
					<?
						foreach($current_product['m_products_foto'] as $_foto)
							echo '
								{
								src : "https://crm.formetoo.ru/images/products/'.$_GET['id'].'/'.$_foto['file'].'_max.'.$_foto['ext'].'",

									opts:{
										caption : "'.$current_product['m_products_name_full'].'"
									}
								},';

						//было раньше так
//									src : "//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($current_product['id_isolux'],0,2).'/SN'.$current_product['id_isolux'].'/'.$_foto['file'].'_max.jpg",

                        ?>
					], {padding:0,index:index});
				});
			});
		$('.main_products_list_items_info_foto_gallery_item:first').triggerHandler('mouseenter');
		<?}?>
		/* ВКЛАДКИ С ИНФОРМАЦИЕЙ О ТОВАРЕ */
		var currentState='';
		$('.detail-info_header_item').on('click',function(){
			$(this).addClass('active').siblings().removeClass('active');
			$('.detail-info__body_item.'+$(this).attr('id')).addClass('active').siblings().removeClass('active');
			//document.location.hash='#'+$(this).attr('id');
			history.pushState(null,null,'#'+$(this).attr('id'));
		});
		if(document.location.hash&&$(document.location.hash)!=undefined)
			$(document.location.hash).triggerHandler('click');
		$('#downto_products_reviews').on('click',function(){$('#products_reviews').triggerHandler('click');});
		$('#downto_products_tech').on('click',function(){$('#products_tech').triggerHandler('click');});

		/* ОЦЕНКА ТОВАРА */
		$('.products_reviews_stars_my_rateit_stars').find('.star')
			.on('mouseenter',function(){
				$(this).addClass('full-fill');
				$(this).prevAll().addClass('full-fill');
				$(this).nextAll().removeClass('full-fill');
			});
		$('.products_reviews_stars_my_rateit_stars').on('mouseleave',function(){
			$(this).find('.star').removeClass('full-fill');
		});

		/* СКРОЛЛ ФОТО */
		var scrollCurrent=0,
			scrollCount=$('.main_products_list_items_info_foto_gallery_item').length;
		$('.main_products_list_items_info_foto_gallery_scrolldown').on('click',function(){
			scrollCurrent--;
			if($(this).hasClass('disabled')){
				scrollCurrent++;
				return false;
			}
			var oneheight=$('.main_products_list_items_info_foto_gallery_item').height()+12;
			$('.main_products_list_items_info_foto_gallery_container_slider').animate(
				{top:"-="+oneheight},
				200
			);
			$('.main_products_list_items_info_foto_gallery_scrollup').removeClass('disabled');
			if(Math.abs(scrollCurrent)>=scrollCount-5)
				$(this).addClass('disabled');
		});
		$('.main_products_list_items_info_foto_gallery_scrollup').on('click',function(){
			scrollCurrent++;
			if($(this).hasClass('disabled')){
				scrollCurrent--;
				return false;
			}
			var oneheight=$('.main_products_list_items_info_foto_gallery_item').height()+12;
			$('.main_products_list_items_info_foto_gallery_container_slider').animate(
				{top:"+="+oneheight},
				200
			);
			$('.main_products_list_items_info_foto_gallery_scrolldown').removeClass('disabled');
			if(scrollCurrent==0)
				$(this).addClass('disabled');
		});
		/* КНОПКА ОТВЕТИТЬ ПРИ НАВЕДЕНИИ НА ВОПРОС */
		$('.products_reviews_container_item')
			.on('mouseenter',function(){
				$(this).find('.mini-button').addClass('hover');
			})
			.on('mouseleave',function(){
				$(this).find('.mini-button').removeClass('hover');
			});
		$('#add_review').on('click',function(){

		});
		$('#add_qna').on('click',function(){

		});
		/* ТОЛЬКО ЦИФРЫ, ТОЧКА ВМЕСТО ЗАПЯТОЙ, ОКРУГЛЕНИЕ ДО 3-Х ЗНАКОВ ПОСЛЕ ТОЧКИ В ПОЛЯХ С ЧИСЛОВЫМИ ЗНАЧЕНИЯМИ */
		function normalizeNumb(numb){
			numb+="";
			numb=numb.replace(",",".");
			numb=numb.replace(/[^.0-9]/gim,"");
			numb=(numb*1).toFixed(4);
			return numb*1;
		};
		/* ИЗМЕНЕНИЕ КОЛ-ВА ТОВАРА ДЛЯ ЗАКАЗА В КАРТОЧКЕ ТОВАРА */
		$('#pay_count_plus').on('click',function(){
			var volume=$('#pay_count').attr('data-unitvolume')*1,
				new_val=normalizeNumb(normalizeNumb($('#pay_count').val())+volume);
			$('#pay_count').val(new_val);
			var change=new_val/volume;
			$('.main_products_list_items_info_pay_count_units_add li').each(function(index,el){
				var def_val=$(el).find('.list_dotts_value').data('value');
				$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			});
		});
		$('#pay_count_minus').on('click',function(){
			var volume=$('#pay_count').attr('data-unitvolume')*1,
				new_val=normalizeNumb(normalizeNumb($('#pay_count').val())-volume);
			$('#pay_count').val(new_val>=volume?new_val:volume);
			var calc_val=$('#pay_count').val()*1,
				change=calc_val/volume;
			$('.main_products_list_items_info_pay_count_units_add li').each(function(index,el){
				var def_val=$(el).find('.list_dotts_value').data('value');
				$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			});
		});
		$('#pay_count').on("change",function(){
			var val=normalizeNumb($(this).val()),
				volume=$('#pay_count').attr('data-unitvolume')*1;
			if(val%volume!=0)
				val=Math.ceil(val/volume)*volume;
			$(this).val(normalizeNumb(val>=volume?val:volume));
			var calc_val=$(this).val()*1,
				change=calc_val/volume;
			$('.main_products_list_items_info_pay_count_units_add li').each(function(index,el){
				var def_val=$(el).find('.list_dotts_value').data('value');
				$(el).find('.list_dotts_value span').text(normalizeNumb(def_val*change));
			});
		});
		/* НАЛИЧИЕ ТОВАРА */
		//$('#get_exist').on('click',function(){
		//	var container=$(this).parent();
		//	container.hide();
		//	container.prev().show();
		//	$.get(
		//		'/ajax/get_exist.php',
		//		{
		//			id:"<?//=$current_product['id'];?>//"
		//		},
		//		function(data){
        //
        //            console.log('good__info');
		//		    console.log(data);
        //
        //
		//			container.prev().hide();
		//			container.show();
		//			if(data&&data!=0)
		//				container.html('<span title="Есть в наличии на складе" itemprop="availability" href="http://schema.org/InStock">В наличии <b>'+data+'</b> <?//=$unit['m_info_units_name'];?>// (склад: Москва)</span>');
		//			else{
		//				container.html('<span title="Под заказ, срок поставки примерно 1–2 дня" itemprop="availability" href="http://schema.org/PreOrder">Нет в наличии, заказ от 30 000<span class="rouble">&nbsp;₽</span></span>').removeClass('exist-1').addClass('exist-0');}
		//		}
		//	);
		//	return false;
		//});
		<?=(!$user->checkCrawler()?'$(\'#get_exist\').triggerHandler(\'click\');':'');?>
		/* ПОКАЗ И СКРЫТИЕ ПОДСКАЗКИ */
		$(".main_products_filters_name_hint").on("click",function(e){
			$(this).addClass("open").parents('.list_dotts_name:first').nextAll(".main_products_filters_desc_container").fadeIn(200);
			$("#blocked").show();
			e.stopPropagation();
		});
		//КОРЗИНА
		$('#pay_add_cart').on('click',function(){
			var self=$(this);
			$.post(
				'/ajax/add_cart.php',
				{
					product_id:$('.product_id').data('value'),
					product_count:$('#pay_count').length?$('#pay_count').val():$('.product_count').data('value')
				},
				function(data){
					var cart_data=null;
					try{
						cart_data=$.parseJSON(data)
					}
					catch(e){
						$('.nav_cart_size').text(data.items.length).removeClass('active');
					}
					if(cart_data!==null){
						$('.nav_cart .desc').html(cart_data.sum.toLocaleString("ru",{useGrouping:true,minimumFractionDigits:2,maximumFractionDigits:2})+'&nbsp;р.');
						$('.nav_cart_size').text(cart_data.items.length).addClass('active');
						self.text('Добавлен в корзину').addClass('success');
						$('.nav_cart').addClass('success');
						setTimeout(function(){
							$('.nav_cart').removeClass('success');
						},100);
						setTimeout(function(){
							self.text('В корзину').removeClass('success');
						},1000);
						cart=cart_data;
					}
					else{
						$('.nav_cart_size').text(cart_data.items.length).removeClass('active');
					}
				}
			);
		});
		//ЗАПОМИНАЕМ НЕДАВНО ПРОСМОТРЕННЫЙ ТОВАР
		function viewedGoods(good,variants){
			if(getCookie('vg')){
				var vg=getCookie('vg').split('_');
				for (var i=0;i<vg.length;i++)
					if(vg[i]==(good+"")||in_array(vg[i],variants))
						vg.splice(i,1);
				vg.unshift(good+"");
				setCookie('vg',vg.join('_'),{expires:5184000,path:'/',domain:'<?=$G['DOMAIN'];?>'});
			}
			else{
				setCookie('vg',good,{expires:5184000,path:'/',domain:'<?=$G['DOMAIN'];?>'})
			}
		}
		viewedGoods(<?
			echo $current_product['id'],',[';
			if(!empty($attr))
				foreach($gV as $j=>$_val)
					echo $j!=0?','.$_val['id']:$_val['id'];
			echo ']';
		?>);
		//ПОКАЗ НЕДАВНО ПРОСМОТРЕННЫХ ТОВАРОВ
		if(getCookie('vg')){
			var vg=getCookie('vg').split('_');
			for (var i=0;i<vg.length;i++)
				if(vg[i]==$('.product_id').data('value'))
					delete vg[i];
			if(vg=vg.join('_'))
				$.post(
					'/ajax/viewed_goods.php',
					{
						goods:vg
					},
					function(data){
						$('.viewed_goods').append(data);
						$('.viewed_goods_header,.viewed_goods').show();
						$('.viewed_goods').slick({
							slidesToScroll: 5,
							slidesToShow: 5,
							infinite: false,
							responsive: [
								{
								  breakpoint: 1280,
								  settings: {
									slidesToShow: 4,
									slidesToScroll: 4
								  }
								},
								{
								  breakpoint: 1024,
								  settings: {
									slidesToShow: 3,
									slidesToScroll: 3
								  }
								},
								{
								  breakpoint: 640,
								  settings: {
									slidesToShow: 2,
									slidesToScroll: 2
								  }
								},
								{
								  breakpoint: 512,
								  settings: {
									slidesToShow: 1,
									slidesToScroll: 1
								  }
								}
							],
							nextArrow:'<button class="slide_arrow slide_arrow_next"></button>',
							prevArrow:'<button class="slide_arrow slide_arrow_prev"></button>',

						});
					}
				);
		}


		//ПОПАП ПОКУПКИ БЕЗ РЕГИСТРАЦИИ
		$(document).on('click','#pay_oneclick',function(){
			if($(this).hasClass('disabled')) return false;
			popup_show('#popup_quicksubmit',function(){
				$('#popup_quicksubmit input:text:first').focus();
			});
			return false;
		});

		//БЫСТРЫЙ ЗАКАЗ
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
		//отправка формы быстрого заказа
		$("#form_quicksubmit").on('submit',function(){
			if(!$("#form_quicksubmit").valid())
				return false;
			$('#pay_add_cart').triggerHandler('click');
			$.post(
				'/ajax/add_order.php',
				{
					name:$('#form_quicksubmit [name="name"]').val(),
					email:$('#form_quicksubmit [name="email"]').val(),
					tel:$('#form_quicksubmit [name="tel"]').val(),
					politic:$('#form_quicksubmit [name="politic"]:checked').val(),
					token:$('#form_quicksubmit [name="token"]').val()
				},
				function(data){
					if(data.length==10)
						$('#form_quicksubmit').fadeOut(100,function(){$(this).remove()});
						$('.form_quicksubmit_done_result').text(data);
						$('#form_quicksubmit_done').fadeIn(100);
						$('#form_quicksubmit_done span.icon').on('click',function(){
							$(this).parents('.popup:first').find('.icon-close').on('click',function(){
								location.reload();
							});
							$(this).parents('.popup:first').find('.icon-close').triggerHandler('click');
						});
				}
			);
			$("#form_quicksubmit button").prop('disabled',true);
			return false;
		});
		//валидация формы быстрого заказ
		$("#form_quicksubmit").validate({
			rules:{
				name:{
					required:true,
					maxlength:180
				},
				email:{
					email:true,
					required:function(el){
						return $('#form_quicksubmit [name="tel"]').val()?false:true;
					}
				},
				tel:{
					required:function(el){
						return $('#form_quicksubmit [name="email"]').val()?false:true;
					},
					tel: true
				},
				captcha:{
					required:true,
					rangelength:[5,7],
					remote:{
						url: '/ajax/check_captcha.php',
						type: 'get',
						data: {
							captcha:function(){
								return $('#form_quicksubmit [name="captcha"]').val()
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
		$('#politic').on('change',function(){
			if(!$(this).prop('checked'))
				$('#reg_submit').prop('disabled',true);
			else $('#reg_submit').prop('disabled',false);
		});
		$('.captcha_img').on('click',function(){
			$(this).attr('src','/ajax/captcha/captcha.php?'+Math.random(1,10000));
			$('[name="captcha"]').val('');
			$(this).parents('form:first').find('[name="captcha"]').focus();
		});

		//ПОХОЖИЕ ТОВАРЫ
		$('.links_goods').slick({
			slidesToScroll: 5,
			slidesToShow: 5,
			infinite: false,
			responsive: [
				{
				  breakpoint: 1281,
				  settings: {
					slidesToShow: 3,
					slidesToScroll: 3
				  }
				},
				{
				  breakpoint: 1024,
				  settings: {
					slidesToShow: 3,
					slidesToScroll: 3
				  }
				},
				{
				  breakpoint: 641,
				  settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				  }
				},
				{
				  breakpoint: 513,
				  settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				  }
				}
			],
			nextArrow:'<button class="slide_arrow slide_arrow_next"></button>',
			prevArrow:'<button class="slide_arrow slide_arrow_prev"></button>',
		});

	});
</script>
<script type="text/javascript">

    $('.main_products_list_items_info_foto_gallery_container_slider').slick({
        slidesToScroll: 1,
        slidesToShow: 4,
        infinite: false,
        nextArrow:'<button class="good_slide_arrow good_slide_arrow_next"></button>',
        prevArrow:'<button class="good_slide_arrow good_slide_arrow_prev"></button>',
    });
</script>