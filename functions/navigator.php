<?
defined ('_DSITE') or die ('Access denied');

$_SERVER['G_VARS']['SERV_ST']=$settings->getSetting('server_st_formetoo');
//на сайте ведутся работы - заглушка
$settings_503=$settings->getSetting('503_formetoo',true);
$settings_503=json_decode($settings_503['m_settings_value']);
if($settings_503->enable==true&&user_info::ip()!='109.111.92.11'){
	require_once(__DIR__.'/../www/503.php');
	exit;
}

$t_time=time();
$t_time+=15552000;

//разбор региона
$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city`;','m_info_city_url');
foreach($area_list as &$_area)
	$_area=$_area[0];
$G['CITY']=!empty($G['SUBDOMAIN'])?$area_list[$G['SUBDOMAIN']]:$area_list['www'];

//город (из субдомена)
$area=get('area')?get('area'):$_SERVER['SUBDOMAIN_PART'];

//если не выбран регион или стоит московский и нет куки региона - определяем регион
if($area==''||($area=='www'&&!isset($_COOKIE['area']))){
	//определяем столицу области, из которой зашел пользователь
	$area=user_info::city(true);
	//если город определен, присваиваем area его транслит, если нет, area - пустое значение
	$area=$area?$area['url']:'';
	//если в городе, определенном по IP, есть представительство - перенаправляем на соответствующий субдомен, если нет - присваиваем регион Москва
	$current_area=isset($area_list[$area])?$area_list[$area]:$area_list['www'];
	//ставим куки с транслитом города
	setcookie('area',$current_area['m_info_city_url'],$t_time,'/','.formetoo.ru',false,true);
	//если город найден, перенаправляем на соответствующий субдомен
	if($current_area['m_info_city_url']!='www'){
		header('Location: https://'.$current_area['m_info_city_url'].'.formetoo.ru'.($_SERVER['REQUEST_URI']!='/?'?$_SERVER['REQUEST_URI']:'/'));
		exit;
	}
}
else{
	$current_area=$area_list[$G['SUBDOMAIN']];
	setcookie('area',$current_area['m_info_city_url'],$t_time,'/','.formetoo.ru',false,true);
}

function getCategory($name) {
	global $sql;
	$q = 'SELECT * FROM `formetoo_main`.`menu` 
		WHERE `url`="' . $name . '" 
		LIMIT 1;';
	
	$res = $sql->query($q);

	return $res;
}
function getCategoryCatalog($name) {
	global $sql;
	$q = 'SELECT * FROM `formetoo_main`.`m_products_categories` 
		WHERE `slug`="' . $name . '" 
		LIMIT 1;'; 
	
	$res = $sql->query($q);

	return $res;
}
function getProduct($name) {
	global $sql;
	$q = 'SELECT * FROM `formetoo_main`.`m_products` 
		WHERE `slug`="' . $name . '" 
		LIMIT 1;';
	
	$res = $sql->query($q);

	return $res;
}

//разбор пути
$path=get('path');
if ($path!='') {
	$uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
				$uri = explode( '/', $uri );
				$uri = array_filter( $uri );
				$uri = array_values( $uri );
$is_product = false;

	if ($uri[0] == 'catalog') {
		$i = 1;
		
		for ($i = 1; $i < count($uri); $i++) {
			$res = getCategoryCatalog($uri[$i]);
			if (empty($res) && count($uri) - 1 !== $i) {
				require_once(__DIR__.'/../www/404.php');
				exit;
			}


			if (!empty($res) && count($uri) - 1 === $i) {
				$current = $res[0];
			}
			if (!empty($res) ) {
				continue;
			}

			if (empty($res) && count($uri) - 1 === $i) {
				$res = getProduct($uri[$i]);

				$q = 'SELECT `m_products`.*, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
					LEFT JOIN `formetoo_main`.`m_products_category` 
						ON `m_products_category`.`product_id`=`m_products`.`id` 
					WHERE `slug`=\''.$uri[$i].'\' 
					GROUP BY `m_products_category`.`product_id` 
					LIMIT 1;';
				if($current_product=$sql->query($q)){
					$is_product = true;
					$current_product=$current_product[0];
					$product_categories=explode('|',$current_product['categories_id']);
					$q='SELECT `id` FROM `formetoo_main`.`menu` WHERE `category`=\''.$product_categories[0].'\' LIMIT 1;';
					if($current=$sql->query($q)){
						$current=$current[0];
					}
				}

			} else {
				require_once(__DIR__.'/../www/404.php');
				exit;
			}
		}
	}
	
	if (!$is_product && $uri[0] != 'catalog') {
		$path=explode('/',$path);
	array_pop($path);
	//сначала - последний раздел
	$path=array_reverse($path);

		//выбираем контент нужного раздела
		$q='SELECT * FROM `formetoo_main`.`content` WHERE `city`=\''.$area.'\' AND `menu`=(';
		//находим id нужного раздела
		foreach($path as $node)
			$q.='SELECT `id` FROM `formetoo_main`.`menu` WHERE `url`=\''.$node.'\' AND `active`=1 AND `parent`=(';
		//родительский пункт самого старшего пункта = 0
		$q=substr($q,0,-1).'0';
		//закрываем скобки
		for($i=0;$i<sizeof($path);$i++)
			$q.=')';
		if($current=$sql->query($q))
			$current=$current[0];
		//если такого пункта меню в городе нет, показываем контент Москвы
		else{
			$q='SELECT * FROM `formetoo_main`.`content` WHERE `city`=\'www\' AND `menu`=(';
			//находим id нужного раздела
			foreach($path as $node)
				$q.='SELECT `id` FROM `formetoo_main`.`menu` WHERE `url`=\''.$node.'\' AND `active`=1 AND `parent`=(';
			//родительский пункт самого старшего пункта = 0
			$q=substr($q,0,-1).'0';
			//закрываем скобки
			for($i=0;$i<sizeof($path);$i++)
				$q.=')';
			if($current=$sql->query($q))
				$current=$current[0];
			//если его нет - проверяем index.php в папке
			else{
				if(file_exists(__DIR__.'/../www/'.get('path').'index.php')){
					require_once(__DIR__.'/../www/'.get('path').'index.php');
					exit;
				}
				//если и его нет - показывем 404
				else{
					require_once(__DIR__.'/../www/404.php');
					exit;
				}
			}
		}
	}

	// //разбиение адреса по разделам
	// $path=explode('/',$path);
	// array_pop($path);
	// //сначала - последний раздел
	// $path=array_reverse($path);
	// //если открыта карточка товара
	// if(is_numeric($path[0])&&strlen($path[0])==10&&$path[1]=='product'&&sizeof($path)==2){
	// 	//выбираем товар
	// 	//$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `id`=\''.$path[0].'\' LIMIT 1;';
	// 	$q = 'SELECT `m_products`.*, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
	// 		LEFT JOIN `formetoo_main`.`m_products_category` 
	// 			ON `m_products_category`.`product_id`=`m_products`.`id` 
	// 		WHERE `id`=' . $id . '  
	// 		GROUP BY `m_products_category`.`product_id` 
	// 		LIMIT 1;';
	// 	if($current_product=$sql->query($q)){
	// 		$current_product=$current_product[0];
	// 		$product_categories=explode('|',$current_product['categories_id']);
	// 		$q='SELECT `id` FROM `formetoo_main`.`menu` WHERE `category`=\''.$product_categories[0].'\' LIMIT 1;';
	// 		if($current=$sql->query($q))
	// 			$current=$current[0];
	// 	}
	// 	//если такого товара нет
	// 	else{
	// 		require_once(__DIR__.'/../www/404.php');
	// 		exit;
	// 	}
	// }
	// else if (!is_numeric($path[0]) && $path[1]=='product' && sizeof($path)==2) {
	// 	//выбираем товар ЧПУ
	// 	//$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `slug`=\''.$path[0].'\' LIMIT 1;';
	// 	$q = 'SELECT `m_products`.*, GROUP_CONCAT(`m_products_category`.`category_id` SEPARATOR \'|\') AS categories_id FROM `formetoo_main`.`m_products` 
	// 		LEFT JOIN `formetoo_main`.`m_products_category` 
	// 			ON `m_products_category`.`product_id`=`m_products`.`id` 
	// 		WHERE `slug`=\''.$path[0].'\' 
	// 		GROUP BY `m_products_category`.`product_id` 
	// 		LIMIT 1;';
	// 	if($current_product=$sql->query($q)){
	// 		$current_product=$current_product[0];
	// 		$product_categories=explode('|',$current_product['categories_id']);
	// 		$q='SELECT `id` FROM `formetoo_main`.`menu` WHERE `category`=\''.$product_categories[0].'\' LIMIT 1;';
	// 		if($current=$sql->query($q)){
	// 			$current=$current[0];
	// 		}
	// 	}
	// 	//если такого товара нет
	// 	else{
	// 		require_once(__DIR__.'/../www/404.php');
	// 		exit;
	// 	}
	// }
	// else {
	// 	//выбираем контент нужного раздела
	// 	$q='SELECT * FROM `formetoo_main`.`content` WHERE `city`=\''.$area.'\' AND `menu`=(';
	// 	//находим id нужного раздела
	// 	foreach($path as $node)
	// 		$q.='SELECT `id` FROM `formetoo_main`.`menu` WHERE `url`=\''.$node.'\' AND `active`=1 AND `parent`=(';
	// 	//родительский пункт самого старшего пункта = 0
	// 	$q=substr($q,0,-1).'0';
	// 	//закрываем скобки
	// 	for($i=0;$i<sizeof($path);$i++)
	// 		$q.=')';
	// 	if($current=$sql->query($q))
	// 		$current=$current[0];
	// 	//если такого пункта меню в городе нет, показываем контент Москвы
	// 	else{
	// 		$q='SELECT * FROM `formetoo_main`.`content` WHERE `city`=\'www\' AND `menu`=(';
	// 		//находим id нужного раздела
	// 		foreach($path as $node)
	// 			$q.='SELECT `id` FROM `formetoo_main`.`menu` WHERE `url`=\''.$node.'\' AND `active`=1 AND `parent`=(';
	// 		//родительский пункт самого старшего пункта = 0
	// 		$q=substr($q,0,-1).'0';
	// 		//закрываем скобки
	// 		for($i=0;$i<sizeof($path);$i++)
	// 			$q.=')';
	// 		if($current=$sql->query($q))
	// 			$current=$current[0];
	// 		//если его нет - проверяем index.php в папке
	// 		else{
	// 			if(file_exists(__DIR__.'/../www/'.get('path').'index.php')){
	// 				require_once(__DIR__.'/../www/'.get('path').'index.php');
	// 				exit;
	// 			}
	// 			//если и его нет - показывем 404
	// 			else{
	// 				require_once(__DIR__.'/../www/404.php');
	// 				exit;
	// 			}
	// 		}
	// 	}
	// }
}

//если открыта главная страница
else{
	$q='SELECT * FROM `formetoo_main`.`content` WHERE `id`=1000000000;';
	if($current=$sql->query($q))
		$current=$current[0];
}
//результаты поиска
if(isset($_GET['search']))
	$current['h1']=$current['title']=$current['description']=$current['keywords']='Поиск по сайту: «'.$_GET['search'].'»';
?>