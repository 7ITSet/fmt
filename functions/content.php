<?
defined ('_DSITE') or die ('Access denied');

class content{
	private	$attributes,
			$goods,
			$categories,
			$units,
			$selectedIDGoods,
			$goodVariants,
			$ec;
	
	function __construct(){
		global 
			$sql,
			$current,
			$menu,
			$path,
			$G,
			$current_product;	
			
		//курс валют
		$ec_res=$sql->query('SELECT * FROM `formetoo_cdb`.`m_info_settings`;');
		$this->ec[1]=1;
		$this->ec[2]=$ec_res[0]['m_info_settings_exchange_usd'];
		$this->ec[3]=$ec_res[0]['m_info_settings_exchange_eur'];
		
		//ед. измерения
		$this->units=$sql->query('SELECT * FROM `formetoo_cdb`.`m_info_units`;','m_info_units_id');
		
		//если метатеги страницы не заполнены - отображаем стандартные на основе категории
		//если открыта категория товаров (/catalog/)
		if(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='catalog'){	
			if(!$current['title']){
				$q='SELECT * FROM `formetoo_main`.`m_products_categories` WHERE `m_products_categories_id`='.$menu->nodes_id[$current['menu']]['category'].' LIMIT 1;';
				if($res=$sql->query($q)){
					$res=$res[0];
					$current['title']=$res['m_products_categories_name'].', купить в '.$G['CITY']['m_info_city_name_city_pr'].' по выгодной цене';
					$current['description']=$res['m_products_categories_name'].' с доставкой в '.$G['CITY']['m_info_city_name_city_tv'].'. Купить '.mb_strtolower($res['m_products_categories_name'],'utf-8').' оптом и в розницу в интернет-магазине.';
					$current['keywords']=$res['m_products_categories_name'];
					$current['h1']=$res['m_products_categories_name'];
				}
			}
		}
		//если открыта карточка товара
		elseif(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='product'){

			$current['menu']=$current['id'];

//			$current['title']=$current_product['m_products_name_full'].', купить в '.$G['CITY']['m_info_city_name_city_pr'].' по выгодной цене';
//			$current['description']=$current_product['m_products_name_full'].' с доставкой в '.$G['CITY']['m_info_city_name_city_tv'].'. Купить '.mb_strtolower($current_product['m_products_name'],'utf-8').' оптом и в розницу в интернет-магазине.';
//			$current['keywords']=$current_product['m_products_name_full'];

            $current['title']=$current_product['m_products_seo_title'];
            $current['description']=$current_product['m_products_seo_description'];
            $current['keywords']=$current_product['m_products_seo_keywords'];

			$current['h1']=$current_product['m_products_name_full'];
		}
	}
	
	//показ контента в зависимости от типа меню
	function getContent(){
		global 	$current,
				$menu,
				$content,
				$products_sort,
				$path,
				$current_product,
				$G,
				$sql,
				$area_list,
				$user,
				$order;
		//если открыта главная страница
		if(!is_array($path)||!isset($path[sizeof($path)-1])||$path[sizeof($path)-1]==''){
			require_once(__DIR__.'/require/main_page.php');
		}
		//если открыта категория товаров (/catalog/)
		elseif(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='catalog'){
			//ПОКАЗЫВАТЬ ТОВАРЫ С ФИЛЬТРАМИ, ТОВАРЫ БЕЗ ФИЛЬТРОВ, ИЛИ КАТЕГОРИИ С ТОВАРАМИ ИЛИ БЕЗ НИХ
			$q='SELECT 
				`m_products_categories_show_attributes`,
				`m_products_categories_show_goods`,
				`m_products_categories_show_categories` 
				FROM `formetoo_main`.`m_products_categories` WHERE 
				`m_products_categories_id`='.$menu->nodes_id[$current['menu']]['category'].' AND 
				`m_products_categories_active`=1 
				LIMIT 1;';
			//если открыт весь каталог
			if($path[0]=='catalog'&&sizeof($path)==1){
				$q='SELECT 
					`m_products_categories_id`,
					`m_products_categories_parent` 
				FROM `formetoo_main`.`m_products_categories` WHERE 
				`m_products_categories_parent`=0 AND 
				`m_products_categories_active`=1;';
				$res=$sql->query($q);
				$_c=array();
				foreach($res as $_res)
					$_c[]=$_res['m_products_categories_id'];
				$q='SELECT 
					`m_products_categories_show_attributes`,
					`m_products_categories_show_goods`,
					`m_products_categories_show_categories` 
					FROM `formetoo_main`.`m_products_categories` WHERE 
					`m_products_categories_id` IN('.implode(',',$_c).') AND 
					`m_products_categories_active`=1 
					LIMIT 1;';
			}
				
			//если фильтры показывать не надо - выходим
			if($res=$sql->query($q)){
				//товары с фильтрами
				if(
					$res[0]['m_products_categories_show_attributes']&&
					$res[0]['m_products_categories_show_goods']
				)
					require_once(__DIR__.'/require/goods_list_w_filters.php');
				//товары без фильтров
				elseif(
					$res[0]['m_products_categories_show_goods']&&
					!$res[0]['m_products_categories_show_categories']
				)
					require_once(__DIR__.'/require/goods_list_wo_filters.php');
				//категории картинками с товарами
				elseif(
					$res[0]['m_products_categories_show_goods']&&
					$res[0]['m_products_categories_show_categories']
				)
					require_once(__DIR__.'/require/categories_list_w_goods.php');
				//категории картинками
				elseif(!$res[0]['m_products_categories_show_goods'])
					require_once(__DIR__.'/require/categories_list_wo_goods.php');			
			}
			
		}
		//если открыта карточка товара
		elseif(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='product'){
			require_once(__DIR__.'/require/good_info.php');
		}
		//выбрасываем анонимов из личного кабинета
		elseif(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='my'&&!$user->getInfo('m_users_name')){
			header('Location: /login/');
		}
		elseif(is_array($path)&&isset($path[sizeof($path)-1])&&$path[sizeof($path)-1]=='logout')
			$user->logout();
		//если открыта обычная страница
		else{
			require_once(__DIR__.'/require/page.php');
		}
	}
	
	
	public function getChCategories($cur=''){
		global $current,$menu;
		//находим дочерние меню текущего меню
		$ch=array();
		$cur=!$cur?$current['menu']:$cur;
		$menu->childs($cur,$ch);
		$ch[]=$menu->nodes_id[$cur];
		//ищем меню, к которым привязаны разделы каталога товаров
		$this->categories=array();
		foreach($ch as $_category)
			if($_category['category'])
				//формируем массив разделов каталога товаров
				$this->categories[]=$_category['category'];
		return $this->categories;
		
	}
	
	public function goodPage($id,$view='blank'){
		global $e,$sql,$current,$menu;
		
	}
	
	//выборка товарных позиций по фильтрам
	public function filterGoods($data){
		global $e,$sql;
		
		$data['p']=$data['p']?$data['p']:1;
		$limit=isset($data['limit'])&&$data['limit']?$data['limit']:24;
		$start=($data['p']-1)*$limit;
		$data['sort']=$data['sort']?$data['sort']:'rate';
		switch($data['sort']){
			case 'name':
				$order='`m_products_name_full`';
				break;
			case 'popular':
				$order='`m_products_feedbacks` DESC';
				break;
			case 'newest':
				$order='`m_products_date` DESC';
				break;
			case 'exist':
				$order='`m_products_exist` DESC';
				break;	
			case 'price':
				$order='`m_products_price_general`';
				break;
			case 'price-':
				$order='`m_products_price_general` DESC';
				break;
			case 'rate':
			default:
				$order='`m_products_rate` DESC';
				break;
			
				
		}
		
////		$attr=$sql->query('SELECT `m_products_attributes_id`,`m_products_attributes_value` FROM `formetoo_main`.`m_products_attributes` WHERE `m_products_attributes_product_id` IN (SELECT `m_products_id` FROM `m_products` WHERE `m_products_categories_id` IN ('.implode(',',$this->getChCategories($data['current'])).'));','m_products_attributes_id');
		//смотрим все фильтры
		$q_select=array();
		$q_join=array();
		$q_where=array();

		if($data['FILTER[]'])
			foreach($data['FILTER[]'] as $k=>$_vars){
				
				//ПРОПУСКАЕМ ЦЕНУ - ЕЕ НЕТ В АТРИБУТАХ
				if(!$k) continue;

				$q_select[]='Attribute_'.$k.'.m_products_attributes_value';
				$q_join[]='LEFT JOIN `formetoo_main`.`m_products_attributes` Attribute_'.$k.' ON 
								Attribute_'.$k.'.`m_products_attributes_product_id` = Product.`m_products_id` AND 
								Attribute_'.$k.'.`m_products_attributes_list_id`='.$k;		
				
				//ЗАПРОС ВЫБОРКИ ДЛЯ ДИАПАЗОНА
				if(isset($_vars['from']))
					$q_where[]='(Attribute_'.$k.'.`m_products_attributes_value` BETWEEN '.(float)$_vars['from'].' AND '.(float)$_vars['to'].')';

				//ЗАПРОС ВЫБОРКИ ДЛЯ ЧЕКБОКСА
				elseif(is_array($_vars)){
					//добавляем все выбранные варианты фильтра
					$q__filters=array();
					foreach($_vars as $__var)
						$q__filters[]='Attribute_'.$k.'.`m_products_attributes_value`=\''.$__var.'\'';
					$q__filters=implode(" OR ",$q__filters);
					$q_where[]='('.$q__filters.')';
				}
				
				//ЗАПРОС ВЫБОРКИ ДЛЯ РАДИО (ТОЛЬКО ОДНО ЗНАЧЕНИЕ ИЗ СПИСКА)
				else{
					$q_where[]='(Attribute_'.$k.'.`m_products_attributes_value`=\''.$_vars.'\')';
				}
				
			}
		
		//ОТДЕЛЬНО ДОБАВЛЯЕМ ФИЛЬТР ЦЕНЫ
		if(isset($data['FILTER[]'][0])){
			$q_where[]='(
	(Product.`m_products_price_general` BETWEEN '.(float)$data['FILTER[]'][0]['from'].' AND '.(float)$data['FILTER[]'][0]['to'].' AND Product.`m_products_price_currency`=1) OR
	(Product.`m_products_price_general` BETWEEN '.round(($data['FILTER[]'][0]['from']/$this->ec[2]),2).' AND '.round(($data['FILTER[]'][0]['to']/$this->ec[2]),2).' AND Product.`m_products_price_currency`=2) OR 
	(Product.`m_products_price_general` BETWEEN '.round(($data['FILTER[]'][0]['from']/$this->ec[3]),2).' AND '.round(($data['FILTER[]'][0]['to']/$this->ec[3]),2).' AND Product.`m_products_price_currency`=3)
)';
		}
		
/* 		$q='SELECT 
				SQL_CALC_FOUND_ROWS
				Product.*[SELECT]
			FROM `formetoo_main`.`m_products` Product
[JOIN]
			WHERE
[WHERE]
Product.`m_products_categories_id` IN('.implode(',',$this->getChCategories($data['current'])).') AND
Product.`m_products_show_site`=1 
			GROUP BY Product.`m_products_main_product` 
			ORDER BY '.$order.',`m_products_order` DESC
			LIMIT '.$start.','.$limit.';'; */
		//запрос без лимитов пагинации для правильного расчета кол-ва товаров по каждому фильтру	
		$q_wo_l='SELECT 
				SQL_CALC_FOUND_ROWS
				/* Product.`m_products_id`,Product.`m_products_show_site`,Product.`m_products_categories_id`[SELECT] */
				Product.`m_products_id`,`m_products_main_product`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_name_full`,`m_products_unit`,`m_products_price_general`,`m_products_price_currency`,`m_products_price_discount`,`m_products_price_bonus`,`m_products_multiplicity`,`m_products_min_order`,`m_products_show_site`,`m_products_date`,`m_products_order`,`m_products_exist`,`m_products_dir`,`m_products_foto`,`m_products_rate`,`m_products_feedbacks`[SELECT]
			FROM `formetoo_main`.`m_products` Product
[JOIN]
			WHERE
[WHERE]
Product.`m_products_categories_id` IN('.implode(',',$this->getChCategories($data['current'])).') AND
Product.`m_products_show_site`=1 
			GROUP BY Product.`m_products_main_product`
			ORDER BY '.$order.',`m_products_order` DESC;';
			
		//формируем запрос
		if($q_where){
			/* $q=str_replace(
				array('[SELECT]','[JOIN]','[WHERE]'),
				array(
					($q_select?",\r\n".implode(",\r\n",$q_select):''),
					implode("\r\n",$q_join),
					implode(" AND\r\n",$q_where)." AND\r\n"
				),
				$q
			); */
			$q_wo_l=str_replace(
				array('[SELECT]','[JOIN]','[WHERE]'),
				array(
					($q_select?",\r\n".implode(",\r\n",$q_select):''),
					implode("\r\n",$q_join),
					implode(" AND\r\n",$q_where)." AND\r\n"
				),
				$q_wo_l
			);
		}
		else{
			/* $q=str_replace(array('[SELECT]','[JOIN]','[WHERE]'),'',$q); */
			$q_wo_l=str_replace(array('[SELECT]','[JOIN]','[WHERE]'),'',$q_wo_l);
		}
		//вариант с отдельный выборкой по лимитам
		//$res=$sql->query($q);
		//выборка без лимита по страницам для правильного подсчета кол-ва в фильтрах
		$res_wo_l=$sql->query($q_wo_l);
		$res=$res_wo_l;//



		
		if($res){
			//создание списка товаров для подгрузки атрибутов
			$res_ids=array();
			foreach($res_wo_l as $_res)
				$res_ids[]=$_res['m_products_id'];
			$this->selectedIDGoods=$res_ids;
			
			$count=$sql->query('SELECT FOUND_ROWS();');
			$res['FOUND_ROWS()']=$count[0]['FOUND_ROWS()'];
			$this->getGoods($res,'table',$limit);
		}
		elseif($res==null){
			$res['FOUND_ROWS()']=0;
			$this->getGoods($res,'table',$limit);
		}
	}
	
	//прием параметров фильтра (не ajax) и вывод готовых товарных позиций
	public function getGoods($result=null,$view='table',$limit=24){
		global $e,$sql,$current,$menu;

		$data['p']=array(null,1,500,null,1);
		$data['FILTER[]']=array();
		$data['sort']=array();
		$data['view']=array();
		array_walk($data,'check',true);
		
		if(!$e){
			$data['p']=$data['p']?$data['p']:1;
			$start=($data['p']-1)*$limit;
			$data['sort']=$data['sort']?$data['sort']:'rate';
			switch($data['sort']){
				case 'name':
					$order='`m_products_name_full`';
					break;
				case 'popular':
					$order='`m_products_feedbacks` DESC';
					break;
				case 'newest':
					$order='`m_products_date` DESC';
					break;
				case 'exist':
					$order='`m_products_exist` DESC';
					break;
				case 'price':
					$order='`m_products_price_general`';
					break;
				case 'price-':
					$order='`m_products_price_general` DESC';
					break;
				case 'rate':
				default:
					$order='`m_products_rate` DESC';
					break;		
			}

			//обновление страницы без фильтров
			if(!$result&&!$data['FILTER[]']){
				$ch=$this->getChCategories();

//				$q='SELECT SQL_CALC_FOUND_ROWS `m_products_id`,`m_products_main_product`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_name_full`,`m_products_unit`,`m_products_price_general`,`m_products_price_currency`,`m_products_price_discount`,`m_products_price_bonus`,`m_products_multiplicity`,`m_products_min_order`,`m_products_show_site`,`m_products_date`,`m_products_order`,`m_products_exist`,`m_products_dir`,`m_products_foto`,`m_products_rate`,`m_products_feedbacks`
//					FROM `formetoo_main`.`m_products` WHERE
//						`m_products_categories_id` IN('.implode(',',($ch?$ch:array($menu->nodes_id[$current['id']]['category']))).') AND
//						`m_products_show_site`=1
//						GROUP BY `m_products_main_product`
//						ORDER BY '.$order.',`m_products_order` DESC
//						LIMIT '.$start.','.$limit.';';

                $q='SELECT SQL_CALC_FOUND_ROWS `m_products_id`,`m_products_main_product`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_name_full`,`m_products_unit`,`m_products_price_general`,`m_products_price_currency`,`m_products_price_discount`,`m_products_price_bonus`,`m_products_multiplicity`,`m_products_min_order`,`m_products_show_site`,`m_products_date`,`m_products_order`,`m_products_exist`,`m_products_dir`,`m_products_foto`,`m_products_rate`,`m_products_feedbacks`
					FROM `formetoo_main`.`m_products` WHERE
						`m_products_categories_id` IN('.implode(',',($ch?$ch:array($menu->nodes_id[$current['id']]['category']))).') AND
						`m_products_show_site`=1
						ORDER BY '.$order.',`m_products_order` DESC
						LIMIT '.$start.','.$limit.';';

				$res=$sql->query($q);

				$count=$sql->query('SELECT FOUND_ROWS();');
				$count=$count[0]['FOUND_ROWS()'];
			}

			//похожие товары
			elseif(is_numeric($result)){
				$q='SELECT `m_products_links` FROM `formetoo_main`.`m_products` WHERE `m_products_id`='.$result.' LIMIT 1;';
				if($links=$sql->query($q)){
					$links=explode('|',$links[0]['m_products_links']);
					$q='SELECT SQL_CALC_FOUND_ROWS `m_products_id`,`m_products_main_product`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_name_full`,`m_products_unit`,`m_products_price_general`,`m_products_price_currency`,`m_products_price_discount`,`m_products_price_bonus`,`m_products_multiplicity`,`m_products_min_order`,`m_products_show_site`,`m_products_date`,`m_products_order`,`m_products_exist`,`m_products_dir`,`m_products_foto`,`m_products_rate`,`m_products_feedbacks` 
						FROM `formetoo_main`.`m_products` WHERE
						`m_products_id` IN('.implode(',',$links).') AND
						`m_products_show_site`=1 
						ORDER BY '.$order.',`m_products_order` DESC 
						LIMIT '.$start.','.$limit.';';
					$res=$sql->query($q);
					$count=$sql->query('SELECT FOUND_ROWS();');
					$count=$count[0]['FOUND_ROWS()'];
					$exist_hide=true;
				}
				
			}

			//недавно просмотренные товары
			elseif(!is_array($result)&&mb_strpos($result,'_')!==false){
				$result=explode('_',$result);
				$viewed=array();
				foreach($result as $_result){
					if(is_numeric($_result))
						$viewed[]=$_result;
				}
				if($viewed){
					$q='SELECT SQL_CALC_FOUND_ROWS `m_products_id`,`m_products_main_product`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_name_full`,`m_products_unit`,`m_products_price_general`,`m_products_price_currency`,`m_products_price_discount`,`m_products_price_bonus`,`m_products_multiplicity`,`m_products_min_order`,`m_products_show_site`,`m_products_date`,`m_products_order`,`m_products_exist`,`m_products_dir`,`m_products_foto`,`m_products_rate`,`m_products_feedbacks` 
						FROM `formetoo_main`.`m_products` WHERE
						`m_products_id` IN('.implode(',',$viewed).') AND
						`m_products_show_site`=1 
						LIMIT '.$start.','.$limit.';';
					$res=$sql->query($q);
					$count=$sql->query('SELECT FOUND_ROWS();');
					$count=$count[0]['FOUND_ROWS()'];
					$exist_hide=true;
				}
			}

			//результат фильтрации (ajax)
			elseif($result){
				$res=$result;
				$count=$result['FOUND_ROWS()'];
			}
			//результат фильтрации (url)
			else{
				$data['current']=$current['menu'];
				$this->filterGoods($data);
			}

			if(isset($res)&&is_array($res)&&sizeof($res)>=1&&!isset($data['current'])){
				//количество товаров по каждому выбранному фильтру
				$attr_sum=array();
				$q_g=array();
				foreach($res as $j=>$_good){
					if($j==='FOUND_ROWS()') continue;
					$q_g[]=$_good['m_products_id'];
				}
				/* if($q_g)
					$attr_sum=$sql->query('SELECT * FROM `m_products_attributes` WHERE `m_products_attributes_product_id` IN ('.implode(',',$q_g).') LIMIT 100000;','m_products_attributes_list_id');

				if($attr_sum){
					echo '<script>var attrs_count='.json_encode($attr_sum,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>';
				} */
				$current['products_count']=$count;
				if(!isset($viewed)&&!isset($links))
					echo '<div class="main_products_list_items_item_count" data-count="'.$count.'"></div>';
				//массив с id товаров (и главных товаров), которые уже вывели
				$goods_id=array();
				//максимальная позиция в выборке товаров
				if(sizeof($res)<=$limit) $start=0;
				$maxpos=($limit+$start)<$count?($limit+$start):$count;//
				//выборка товаров нужной страницы из массива всех товаров
				for($k=$start;$k<$maxpos;$k++){//
					if(!isset($res[$k])) break;//
					$_good=$res[$k];//


				//вариант с готовой выборкой страницы средствами mysql
				//foreach($res as $j=>$_good){
					//первая запись  - с кол-вом товаров
					//if($j==='FOUND_ROWS()') continue;
					//если товар есть в массиве выведенных (его мог внести дублирующий товар),
					//или это товар, дубли или главныей товар которого уже выводдились - не выводим товар
					if(in_array($_good['m_products_main_product'],$goods_id)||in_array($_good['m_products_id'],$goods_id)) continue;
					//средняя оценка
					/* $m_rating=0;
					if($rating&&isset($rating[$_good['m_products_id']])){
						foreach($rating[$_good['m_products_id']] as $_rating)
							$m_rating+=$_rating['m_products_feedbacks_rating'];
						$m_rating=round($m_rating/sizeof($rating[$_good['m_products_id']]),2);
					} */

					$_good['m_products_foto']=json_decode($_good['m_products_foto']);
					$foto='';

					foreach($_good['m_products_foto'] as $_foto)

//                        echo "<pre>";
//                    var_dump($_foto);
//                    echo "</pre>";

//						if($_foto->main)
							$foto=$_foto->file;

//                    echo "<pre>";
//                    var_dump($foto);
//                    echo "</pre>";

					echo '
						<div class="main_products_list_items_item">
						    <div class="good_goods">
								<a href="#" class="good_goods_a">
									<span class="good_goods_icon"></span>
								</a>
							</div>
							<div class="good_comparison">
								<a href="#" class="good_comparison_a">
									<span class="good_comparison_icon"></span>
								</a>
							</div>
							<div class="main_products_list_items_item_info">
								<div class="foto">',
									($_good['m_products_id_isolux']
//										? '<a href="/product/'.$_good['m_products_id'].'/">'.($foto?'<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($_good['m_products_id_isolux'],0,2).'/SN'.$_good['m_products_id_isolux'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>':'<img src="/img/empty_foto.svg" alt="Фото товара отсутствует"/>').'</a>'
//										: '<a href="/product/'.$_good['m_products_id'].'/">'.($foto?'<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/v/'.$_good['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>':'<img src="/img/empty_foto.svg" alt="Фото товара отсутствует"/>').'</a>'

//                                        ? '<a href="/product/'.$_good['m_products_id'].'/">'.($foto?'<img src="https://www.formetoo.ru/images/products'.$_good['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>':'<img src="/img/empty_foto.svg" alt="Фото товара отсутствует"/>').'</a>'
//                                        : '<a href="/product/'.$_good['m_products_id'].'/">'.($foto?'<img src="https://www.formetoo.ru/images/products'.$_good['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>':'<img src="/img/empty_foto.svg" alt="Фото товара отсутствует"/>').'</a>'

                                        ? '<a href="/product/'.$_good['m_products_id'].'/">'.'<img src="https://crm.formetoo.ru/images/products/'.$_good['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>'.'</a>'
                                        : '<a href="/product/'.$_good['m_products_id'].'/">'.'<img src="https://crm.formetoo.ru/images/products/'.$_good['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_good['m_products_name_full']).'"/>'.'</a>'



									),
								'</div>
								<div class="title">
									<p>
										<a href="/product/'.$_good['m_products_id'].'/" title="'.htmlspecialchars($_good['m_products_name_full']).'">'.$_good['m_products_name_full'].'</a>
									</p>
								</div>
							</div>
							<div class="main_products_list_items_item_cart">
								<div class="cart">
								    <div class="main_products_list_items_item_price">
                                        <div class="price">
                                            <p>
                                                '.transform::price_o(round($_good['m_products_price_general']*$this->ec[$_good['m_products_price_currency']],2)).' <span>руб.</span>
                                            </p>
                                        </div>
								    </div>
									<div class="product_id hidden" data-value="'.$_good['m_products_id'].'"></div>
									<div class="product_count hidden" data-value="'.round($_good['m_products_multiplicity'],4).'"></div>
									<button class="btn-cart">Купить</button>
									<a href="https://wa.me/79105199977" class="good_child good_whatsapp_href" target="_blank">
                                      <div class="good_whatsapp_icon">
                                          <img src="/img/whatsapp_white.png" alt="whatsapp_icon" class="whatsapp">
                                      </div>
		                            </a>
								</div>
							</div>
						</div>';
					//добавляем id товара и id главного товара (если это дубль) в массив выведенных товаров
					$goods_id[]=$_good['m_products_id'];
					if($_good['m_products_main_product'])
						$goods_id[]=$_good['m_products_main_product'];	
				}
				if(!isset($viewed)&&!isset($links))
					echo '<div class="main_products_list_items_more_delimeter clr"></div>';
				//если больше одной страницы
				if(ceil($count/$limit)>1&&!isset($exist_hide)){
					$last_page=ceil($count/$limit);
					if($data['p']!=$last_page)
					echo '
						<div class="main_products_list_items_more">
							<a class="pagination-button more" href="?p='.($data['p']+1).'">Показать ещё</a>
						</div>';
					echo '
						<div class="main_products_list_items_pagination_container">
							<div class="pagination">
								<a class="pagination-button prev'.($data['p']!=1?'':' inactive').'" href="?p='.($data['p']-1).'">←&nbsp;Назад</a>';
					if($data['p']>3&&$last_page>5)
						echo '<a class="pagination-button first" href="?p=1">1</a>';
					if($data['p']>4&&$last_page>5)
						echo '<a class="threedots left">…</a>';
					if($last_page>=5)
						for($i=($last_page-$data['p']>=2?($data['p']>=3?$data['p']-2:1):($data['p']<=3?1:$last_page-4));$i<=($last_page-$data['p']>=2?($data['p']>=3?($data['p']+2):5):$last_page);$i++){
							$class=array();
							if($data['p']==$i)
								$class[]="active";
							if($i==($last_page-$data['p']>=2?($data['p']>=3?$data['p']-2:1):($data['p']<=3?1:$last_page-4)))
								$class[]="left";
							if($i==($last_page-$data['p']>=2?($data['p']>=3?$data['p']+2:5):$last_page))
								$class[]="right";
							echo '<a href="?p='.$i.'" class="pagination-button '.implode(' ',$class).'">'.$i.'</a>';
						}
					else{
						for($i=1;$i<=$last_page;$i++){
							$class=array();
							if($data['p']==$i)
								$class[]="active";
							if($i==1)
								$class[]="left";
							if($i==$last_page)
								$class[]="right";
							echo '<a href="?p='.$i.'" class="pagination-button '.implode(' ',$class).'">'.$i.'</a>';
						}
					}

					if($last_page-$data['p']>=4&&$last_page>5)
						echo '<a class="threedots right">…</a>';
					if($last_page-$data['p']>=3&&$last_page>5)
						echo '<a class="pagination-button last" href="?p='.$last_page.'">'.$last_page.'</a>';
					echo '	
								<a class="pagination-button next'.($data['p']!=$last_page?'':' inactive').'" href="?p='.($data['p']+1).'">Вперед&nbsp;→</a>
							</div>
						</div>';
				}
			}
			if(!isset($data['current'])&&(!isset($count)||!$count)){
				echo '<div class="main_products_list_items_item_count" data-count="'.$count.'"></div>
					<p class="main_products_list_null">К сожалению, нет товаров, удовлетворяющих выбранным условиям.<br/>Попробуйте изменить критерии поиска или <a href="#" class="clean-filters dashed">очистить фильтры</a>.</p>';
			}
		}
		
	}
	
	//АТРИБУТЫ ПО МАССИВУ ТОВАРОВ
	/* function updateAttrFromGoods($goods=null,&$result=null){
		global $sql,$current,$menu;	
		//если была выборка по товарам (фильтрация)
		if($goods){
			$q=' CREATE TEMPORARY TABLE IF NOT EXISTS `formetoo_main`.`temp__filters`
						SELECT `m_products_id` FROM `formetoo_main`.`m_products` WHERE 
						`m_products_id` IN('.implode(',',$goods).') 
						AND `m_products_show_site`=1;';
				$sql->query($q);
			//выбор атрибутов
			$q='SELECT * FROM `formetoo_main`.`m_products_attributes`
					LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
						`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
					WHERE 
						`m_products_attributes_product_id` IN(SELECT `m_products_id` FROM `formetoo_main`.`temp__filters`) AND 
						`m_products_attributes_list_site_filter`=1
					ORDER BY `m_products_attributes_list`.`m_products_attributes_list_order`, `m_products_attributes_list`.`m_products_attributes_list_name`;';		
			if(!is_array($result))
				$this->attributes=$sql->query($q,'m_products_attributes_list_id');
			else
				$result=$sql->query($q,'m_products_attributes_list_id');
		}
		//если не было - товары из открытой категории
		elseif($this->getChCategories()){
			//выбор ID товаров, находящихся в текущей категори или ее подкатегориях
			$q='SELECT `m_products_id`,`m_products_price_general`,`m_products_price_currency`,`m_products_categories_id` FROM `formetoo_main`.`m_products` WHERE 
				`m_products_categories_id` IN('.implode(',',$this->getChCategories()).') AND 
				`m_products_show_site`=1;';
			if($this->goods=$sql->query($q,'m_products_id')){
				$q=' CREATE TEMPORARY TABLE IF NOT EXISTS `formetoo_main`.`temp__filters`
						SELECT `m_products_id` FROM `formetoo_main`.`m_products` WHERE 
						`m_products_categories_id` IN('.implode(',',$this->getChCategories()).') 
						AND `m_products_show_site`=1;';
				$sql->query($q);
				//выбор атрибутов
				$q='SELECT * FROM `formetoo_main`.`m_products_attributes`
						LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
							`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
						WHERE 
							`m_products_attributes_product_id` IN(SELECT `m_products_id` FROM `formetoo_main`.`temp__filters`) AND 
							`m_products_attributes_list_site_filter`=1 
						ORDER BY `m_products_attributes_list`.`m_products_attributes_list_order`, `m_products_attributes_list`.`m_products_attributes_list_name`;';
				
				
				$this->attributes=$sql->query($q,'m_products_attributes_list_id');
			}
		}
		
	} */
	function updateAttrFromGoods($goods=null,&$result=null){
		global $sql,$current,$menu;
		//если была выборка по товарам (фильтрация)
	//	if($goods){
			/* $q=' CREATE TEMPORARY TABLE IF NOT EXISTS `formetoo_main`.`temp__filters`
						SELECT `m_products_id` FROM `formetoo_main`.`m_products` WHERE 
						`m_products_id` IN('.implode(',',$goods).') 
						AND `m_products_show_site`=1;';
				$sql->query($q); */
			//выбор атрибутов
			/* $q='SELECT * FROM `formetoo_main`.`m_products_attributes`
					LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
						`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
					WHERE 
						`m_products_attributes_product_id` IN(SELECT `m_products_id` FROM `formetoo_main`.`temp__filters`) AND 
						`m_products_attributes_list_site_filter`=1
					ORDER BY 
						`m_products_attributes_list`.`m_products_attributes_list_order`,
						`m_products_attributes_list`.`m_products_attributes_list_site_open` DESC,
						`m_products_attributes_list`.`m_products_attributes_list_name`;'; */
			/* $q='SELECT * FROM `formetoo_main`.`m_products_attributes`
					LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
						`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
					WHERE 
						`m_products_attributes_product_id` IN('.implode(',',$this->selectedIDGoods).') AND 
						`m_products_attributes_list_site_filter`=1
					ORDER BY 
						`m_products_attributes_list`.`m_products_attributes_list_order`,
						`m_products_attributes_list`.`m_products_attributes_list_site_open` DESC,
						`m_products_attributes_list`.`m_products_attributes_list_name`;';
			if(!is_array($result))
				$this->attributes=$sql->query($q,'m_products_attributes_list_id');
			else
				$result=$sql->query($q,'m_products_attributes_list_id'); */
	//	}
		//если не было - товары из открытой категории
		if($chCat=$this->getChCategories()){
			//выбор ID товаров, находящихся в текущей категори или ее подкатегориях
			/* $q='SELECT `m_products_id`,`m_products_price_general`,`m_products_price_currency`,`m_products_categories_id` FROM `formetoo_main`.`m_products` WHERE 
				`m_products_categories_id` IN('.implode(',',$chCat).') AND 
				`m_products_show_site`=1;';
			if($this->goods=$sql->query($q,'m_products_id')){	 */		
				 $q=' CREATE TEMPORARY TABLE IF NOT EXISTS `formetoo_main`.`temp__filters`
						SELECT `m_products_id` FROM `formetoo_main`.`m_products` WHERE 
						`m_products_categories_id` IN('.implode(',',$chCat).') 
						AND `m_products_show_site`=1;'; 
				$sql->query($q);
				//выбор атрибутов
				$q='SELECT * FROM `formetoo_main`.`m_products_attributes`
						LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
							`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
						WHERE 
							`m_products_attributes_product_id` IN(SELECT `m_products_id` FROM `formetoo_main`.`temp__filters`) AND 
							`m_products_attributes_list_site_filter`=1
						/* ORDER BY 
							`m_products_attributes_list`.`m_products_attributes_list_order`,
							`m_products_attributes_list`.`m_products_attributes_list_site_open` DESC,
							`m_products_attributes_list`.`m_products_attributes_list_name` */;';
			/*	$q='SELECT * FROM `formetoo_main`.`m_products_attributes`
						LEFT JOIN `formetoo_main`.`m_products_attributes_list` ON 
							`m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id` 
						WHERE 
							`m_products_attributes_product_id` IN('.implode(',',$this->selectedIDGoods).') AND 
							`m_products_attributes_list_site_filter`=1 
						ORDER BY 
							`m_products_attributes_list`.`m_products_attributes_list_order`,
							`m_products_attributes_list`.`m_products_attributes_list_site_open` DESC,
							`m_products_attributes_list`.`m_products_attributes_list_name`;';*/
				$this->attributes=$sql->query($q,'m_products_attributes_list_id');
				uasort($this->attributes,array(new attrname_ab_ord_mc(),'s_ord'));
			/*}*/
		}
		
	}
	
	//ВЫВОД НА СТРАНИЦУ АТРИБУТОВ
	public function getAttributes(){
		global $e,$sql,$current,$current_product,$menu;
			
		//получаем массив атрибутов по отфильтрованным товар
		$res_count_attr=array();
		if($this->selectedIDGoods)
			$this->updateAttrFromGoods($this->selectedIDGoods,$res_count_attr);
		//или по товарам из категории, если фильтры пустые	
		if(!isset($current_product)||!$current_product)
			$this->updateAttrFromGoods();
		
		if(!$this->attributes) return false;
		
		$print_res='';
		$attr_range=array();
		$attr_range_f=array();
		//вручную добавляем атрибут цены
		$attr_range_f[0]['min']=$attr_range[0]['min']=10000000;
		$attr_range_f[0]['max']=$attr_range[0]['max']=0;
		//диапазон цен для ОТФИЛЬТРОВАННЫХ товаров
		if($this->selectedIDGoods)
			foreach($this->selectedIDGoods as $_good){
				//перевод цены в рубли
				$_good=$this->goods[$_good][0];
				$_good['m_products_price_general']=round($this->ec[$_good['m_products_price_currency']]*$_good['m_products_price_general'],2);
				$attr_range_f[0]['min']=$_good['m_products_price_general']<$attr_range_f[0]['min']?$_good['m_products_price_general']:$attr_range_f[0]['min'];
				$attr_range_f[0]['max']=$_good['m_products_price_general']>$attr_range_f[0]['max']?$_good['m_products_price_general']:$attr_range_f[0]['max'];
			}
		//диапазон цен для товаров КАТЕГОРИИ БЕЗ ФИЛЬТРАЦИИ
		if($this->goods)
			foreach($this->goods as $_good){
				//перевод цены в рубли
				$_good=$_good[0];
				$_good['m_products_price_general']=round($this->ec[$_good['m_products_price_currency']]*$_good['m_products_price_general'],2);
				$attr_range[0]['min']=$_good['m_products_price_general']<$attr_range[0]['min']?$_good['m_products_price_general']:$attr_range[0]['min'];
				$attr_range[0]['max']=$_good['m_products_price_general']>$attr_range[0]['max']?$_good['m_products_price_general']:$attr_range[0]['max'];
			}
		$a[0][0]['m_products_attributes_list_name']='Цена';
		$a[0][0]['m_products_attributes_list_name_url']='price';
		$a[0][0]['m_products_attributes_list_type']=2;
		$a[0][0]['m_products_attributes_list_hint']='';
		$a[0][0]['m_products_attributes_list_unit']='руб';
		$a[0][0]['m_products_attributes_list_active']=1;
		$a[0][0]['m_products_attributes_list_required']=0;
		$a[0][0]['m_products_attributes_list_site_filter']=1;
		$a[0][0]['m_products_attributes_list_site_open']=1;
		$a[0][0]['m_products_attributes_list_order']=0;
		$this->attributes=$a+$this->attributes;
		
		if($res_count_attr)
			$res_count_attr=$a+$res_count_attr;
			
		$data['FILTER[]']=array();
		array_walk($data,'check',true);
			
		if(!$e){
			$attr_values_all=$sql->query('SELECT * FROM `formetoo_main`.`m_products_attributes_values`;','m_products_attributes_values_id');
			//ОБРАБАТЫВАЕМ АТРИБУТЫ
			//пробегаемся по всем атрибутам товаров КАТЕГОРИИ БЕЗ ФИЛЬТРОВ, объединённых по ID атрибута из базы
			foreach($this->attributes as $_attr_id=>$_items){
				//пропускаем вручную добавленный атрибут цены (здесь выборка только по атрибутам из базы)
				if(!$_attr_id) continue;
				$attr_range[$_attr_id]['min']=10000000000;
				$attr_range[$_attr_id]['max']=0;
				//пробегаемся по всем возможным значениям текущего атрибута для выбранных товаров, чтобы получить их пересечение для чексбоксов и радио и объединение для числовых
				foreach($_items as $__item){
					//текстовый тип (чекбоксы и радио) - собираем все возможные значения, группируя по значению
					if($__item['m_products_attributes_list_type']==1||$__item['m_products_attributes_list_type']==3){
						$attr_range[$_attr_id]['values'][$__item['m_products_attributes_value']][]=$__item['m_products_attributes_id'];
						//$attr_range[ID_атрибута_в_БД]['values'][ID_значения_текстового_атрибута_а_базе][]=ID_привязки_значения_атрибута_к_товару
					}
					//числовой тип - находим минимум и максимум, отсекая ID текстовых типов, которые попали с глюками Изолюкса
					if($__item['m_products_attributes_list_type']==2){
						if(strlen($__item['m_products_attributes_value'])!=10)
							$attr_range[$_attr_id]['min']=$__item['m_products_attributes_value']<$attr_range[$_attr_id]['min']?$__item['m_products_attributes_value']:$attr_range[$_attr_id]['min'];
						if(strlen($__item['m_products_attributes_value'])!=10)
							$attr_range[$_attr_id]['max']=$__item['m_products_attributes_value']>$attr_range[$_attr_id]['max']?$__item['m_products_attributes_value']:$attr_range[$_attr_id]['max'];
						$attr_range[$_attr_id]['values'][$__item['m_products_attributes_value']][]=$__item['m_products_attributes_id'];
					}
				}
			}
			//пробегаемся по всем атрибутам ВЫБРАННЫХ ФИЛЬТРАМИ товаров, объединённых по ID атрибута из базы
			foreach($res_count_attr as $_attr_id=>$_items){
				//пропускаем вручную добавленный атрибут цены (здесь выборка только по атрибутам из базы)
				if(!$_attr_id) continue;
				$attr_range_f[$_attr_id]['min']=10000000000;
				$attr_range_f[$_attr_id]['max']=0;
				//пробегаемся по всем возможным значениям текущего атрибута для выбранных товаров, чтобы получить их пересечение для чексбоксов и радио и объединение для числовых
				foreach($_items as $__item){
					//текстовый тип - собираем все возможные значения, группируя по значению
					if($__item['m_products_attributes_list_type']==1||$__item['m_products_attributes_list_type']==3){
						$attr_range_f[$_attr_id]['values'][$__item['m_products_attributes_value']][]=$__item['m_products_attributes_id'];
					}
					//числовой тип - находим минимум и максимум, отсекая ID текстовых типов, которые попали с глюками Изолюкса
					if($__item['m_products_attributes_list_type']==2){
						if(strlen($__item['m_products_attributes_value'])!=10)
							$attr_range_f[$_attr_id]['min']=$__item['m_products_attributes_value']<$attr_range_f[$_attr_id]['min']?$__item['m_products_attributes_value']:$attr_range_f[$_attr_id]['min'];
						if(strlen($__item['m_products_attributes_value'])!=10)
							$attr_range_f[$_attr_id]['max']=$__item['m_products_attributes_value']>$attr_range_f[$_attr_id]['max']?$__item['m_products_attributes_value']:$attr_range_f[$_attr_id]['max'];
						$attr_range_f[$_attr_id]['values'][$__item['m_products_attributes_value']][]=$__item['m_products_attributes_id'];
					}
				}
			}
			//если фильтрации нет - оставляем выборку по категориям без фильтров
			if(!$res_count_attr)
				$attr_range_f=$attr_range;

			
			//пробегаемся по всем атрибутам выбранных товаров, объединённых по ID атрибута из базы
			$coverage=100000;
			$min_items_value_id=0;
			//охват товаров для выбранного фильтра
			foreach($this->attributes as $_attr_id=>$_items){
				$_items=$_items[0];
				if($_items['m_products_attributes_list_type']==1||$_items['m_products_attributes_list_type']==3)
					foreach($attr_range[$_attr_id]['values'] as $_value_id=>$_values){
						if(isset($data['FILTER[]'][$_attr_id])&&in_array($_value_id,$data['FILTER[]'][$_attr_id])&&sizeof($attr_range_f[$_attr_id]['values'][$_value_id])<=$coverage){
							$coverage=sizeof($attr_range_f[$_attr_id]['values'][$_value_id]);
							$min_items_value_id=$_attr_id;
						}
					}
				if($_items['m_products_attributes_list_type']==2)
					if(isset($data['FILTER[]'][$_attr_id]['from'])||isset($data['FILTER[]'][0]['from']))
						$min_items_value_id=0;
					
			}
			
			//сортировка вариантов атрибутов по количеству товаров с ними
			/* function n_ord($a,$b){
				if (sizeof($a)==sizeof($b)) return 0;
				return (sizeof($a)<sizeof($b))?1:-1;
			}
			foreach($attr_range as &$_attr_range)
				uasort($_attr_range['values'],'n_ord'); */
			//или сортировка вариантов атрибутов в алфавитном порядке, функция сортировка в классе в конце файла
			foreach($attr_range as $k=>&$_attr_range){
				if($k==0) continue;
				uksort($_attr_range['values'],array(new attrname_ab_ord_mc($attr_values_all),'call'));
			}
			
			//ВЫВОДИМ АТРИБУТЫ
			foreach($this->attributes as $_attr_id=>$_items){
				$_items=$_items[0];
				//не показываем фильтры с диапазонами min=max
				if($_items['m_products_attributes_list_type']==2&&$attr_range[$_attr_id]['min']==$attr_range[$_attr_id]['max']) continue;
				//для информации об атрибуте берем первый элемент из набора
				$print_res.= '
					<div class="main_products_filters_block" onselectstart="return false">
						<div class="main_products_filters_name">'.
							$_items['m_products_attributes_list_name'].($_items['m_products_attributes_list_unit']?'&nbsp;<sup class="main_products_filters_name_unit">'.$_items['m_products_attributes_list_unit'].'</sup>':'').
							($_items['m_products_attributes_list_hint']?'<span class="icon icon-question-circle main_products_filters_name_hint"></span>':'').
							'<span class="icon icon-arrow-'.(isset($_items['m_products_attributes_list_site_open'])&&$_items['m_products_attributes_list_site_open']?'up':'down').' main_products_filters_name_expand"></span>'.
							($_items['m_products_attributes_list_hint']?'
							<div class="main_products_filters_desc_container">
								<span class="main_products_filters_desc">
									<noindex><b>'.$_items['m_products_attributes_list_name'].($_items['m_products_attributes_list_unit']?'&nbsp;<span>'.$_items['m_products_attributes_list_unit'].'<span>':'').'</b>
									'.$_items['m_products_attributes_list_hint'].'</noindex>
									<span class="icon icon-close"></span>
								</span>
							</div>':'').
						'</div>'.
						'<div class="main_products_filters_body'.(isset($_items['m_products_attributes_list_site_open'])&&$_items['m_products_attributes_list_site_open']?' open':'').'">';
				
				switch($_items['m_products_attributes_list_type']){
					//текстовый (чекбоксы)
					case 1:
						$print_res.= '<ul class="main_products_filters_list_parent"'.(sizeof($attr_range[$_attr_id]['values'])>10?' data-simplebar data-simplebar-auto-hide="false" ':'').'>';
						//пробегаемся по объединённым значениям текущего атрибута для товаров
						foreach($attr_range[$_attr_id]['values'] as $_value_id=>$_values){
							//фильтруется ли по этому варианту атрибута
							$checked=isset($data['FILTER[]'][$_attr_id])&&in_array($_value_id,$data['FILTER[]'][$_attr_id])?1:0;
							//кол-во товаров, если выбрать этот вариант атрибута
							$attr_count_goods=!$checked
								//если не выбран
								?'&nbsp;<span>('.
									//проверяем не выбран ли другой вариант этого атрибута
									($min_items_value_id!=$_attr_id&&isset($attr_range_f[$_attr_id]['values'][$_value_id])
										//если выбран совсем другой атрибут - выводим кол-во в пересечении с другими выбранными атрибутами
										?sizeof($attr_range_f[$_attr_id]['values'][$_value_id])
										//если выбран вариант этого атрибута - выводим общее кол-во по этому варинату без учета фильтров
										:sizeof($attr_range[$_attr_id]['values'][$_value_id])
									).')</span>'
								//если выбран и кол-во по нему относительно других атрибутов не нулевое
								:(sizeof($attr_range_f[$_attr_id]['values'][$_value_id])
									//не показываем кол-во по этому атрибуту
									?''
									//выводим 0
									:'&nbsp;<span>(0)</span>');
							$print_res.= '<li>
									<input name="FILTER['.$_attr_id.'][]" id="cb'.$_values[0].'" type="checkbox" class="main_products_filters_checkbox"'.($checked?' checked':'').' value="'.$_value_id.'" />
									<label for="cb'.$_values[0].'" onselectstart="return false">'.$attr_values_all[$_value_id][0]['m_products_attributes_values_value'].$attr_count_goods.'</label>
								</li>';
						}
						$print_res.= '</ul>';
						break;
					//числовой (диапазон)
					case 2:
						$print_res.= '<div class="rangeinput">
								<div class="rangeinput_from">
									<input name="FILTER['.$_attr_id.'][from]" type="text" id="in'.$_attr_id.'[]" data-min-value="'.$attr_range[$_attr_id]['min'].'" data-max-value="'.$attr_range[$_attr_id]['max'].'" placeholder="'.$attr_range_f[$_attr_id]['min'].'"'.(isset($data['FILTER[]'][$_attr_id]['from'])?' value="'.$data['FILTER[]'][$_attr_id]['from'].'"':'').'/>
									<span class="icon icon-close"></span>
								</div>
								<div class="rangeinput_to">
									<input name="FILTER['.$_attr_id.'][to]" type="text" id="in'.$_attr_id.'[]" data-min-value="'.$attr_range[$_attr_id]['min'].'" data-max-value="'.$attr_range[$_attr_id]['max'].'" placeholder="'.$attr_range_f[$_attr_id]['max'].'"'.(isset($data['FILTER[]'][$_attr_id]['to'])?' value="'.$data['FILTER[]'][$_attr_id]['to'].'"':'').'/>
									<span class="icon icon-close"></span>'.
								'</div>
								<div class="rangeinput_slider"></div>
							</div>';
						break;
					//логический (радио)
					case 3:
						$print_res.= '<ul class="main_products_filters_list_parent"'.(sizeof($attr_range[$_attr_id]['values'])>10?' data-simplebar data-simplebar-auto-hide="false" ':'').'>';
						foreach($attr_range[$_attr_id]['values'] as $_value_id=>$_values){
							$checked=isset($data['FILTER[]'][$_attr_id])&&in_array($_value_id,$data['FILTER[]'][$_attr_id])?1:0;
							$attr_count_goods=!$checked
								?'&nbsp;<span>('.
									($min_items_value_id!=$_attr_id
										?sizeof($attr_range_f[$_attr_id]['values'][$_value_id])
										:sizeof($attr_range[$_attr_id]['values'][$_value_id])
									).')</span>'
								:(sizeof($attr_range_f[$_attr_id]['values'][$_value_id])
									?''
									:'&nbsp;<span>(0)</span>');
							$print_res.= '<li>
									<input name="FILTER['.$_attr_id.']" id="rb'.$_values[0].'" type="radio" class="main_products_filters_checkbox"'.($checked?' checked':'').' value="'.$_value_id.'" />
									<label onselectstart="return false" for="rb'.$_values[0].'">'.$attr_values_all[$_value_id][0]['m_products_attributes_values_value'].$attr_count_goods.'</label>
								</li>';
						}
						$print_res.= '</ul>';
						break;
				}
				$print_res.=		'</div>
					</div>';
			}
		}
		return $print_res;
	}
	
	//КАТЕГОРИИ КАРТИНКАМИ
	public function getCategories($open_cat=true){
		global $e,$sql,$current,$menu;
		
		if($open_cat){
			//выводим подкатегории открытой категории с информацией о товарах в них
			$cats=$cat_ids=array();
			$menu->displayLeftCat($cats,$cat_ids);
				
			/* $show_cat_ids=array();
			foreach($cats as $_cat)
				$show_cat_ids[]=$_cat['category'];
			$q='SELECT * FROM `formetoo_main`.`m_products_categories` WHERE `m_products_categories_id` IN ('.implode(',',$show_cat_ids).');';
			$cats=$sql->query($q);
			foreach($cats as $_cat){
				if($_cat['m_products_categories_products_count'])
					echo '<div class="main_products_list_items_item product_category">
							<div class="main_products_list_items_item_info">
								<div class="foto">
									<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.$_cat['m_products_categories_foto'].'_med.jpg" alt="'.$_cat['m_products_categories_name'].'"/>',
								'</div>
								<div class="title">
									<p>
										<a href="'.$_cat['m_products_categories_name_seo'].'/" title="'.$_cat['m_products_categories_name'].'">'.$_cat['m_products_categories_name'].'</a>
									</p>
								</div>
								<div class="clr"></div>
								<div class="count">
									<p>'.$_cat['m_products_categories_products_count'].'&nbsp;'.transform::word_ending($_cat['m_products_categories_products_count'],array('товар','товара','товаров')).'</p>
								</div>
							</div>
						</div>';
			} */
			
			
			
			
			
			$q='SELECT `m_products_id`,`m_products_id_isolux`,`m_products_categories_id`,`m_products_foto`,`m_products_foto_category` FROM `formetoo_main`.`m_products` WHERE `m_products_categories_id` IN('.implode(',',$cat_ids).') AND `m_products_show_site`=1 ORDER BY `m_products_foto_category` DESC;';
			if($res=$sql->query($q,'m_products_categories_id')){
				function _rec_count_cats(&$cats,$res){
					global $current,$menu;
					//пробегаемся по дереву категорий, в res - товары, сгруппированные по категориям
					foreach($cats as &$_cat){
						//подкатегории данной категории
						$childs=array();
						$menu->childs($_cat['id'],$childs);
						//фото
						$foto='';
						if(!$_cat['child']){
							if(isset($res[$_cat['category']][0]['m_products_foto'])&&$json_foto=json_decode($res[$_cat['category']][0]['m_products_foto'])){
								foreach($json_foto as $_foto)
									if($_foto->main)
										$foto=$_foto->file;
								if(!$foto)
									foreach($json_foto as $_foto){
										$foto=$_foto->file;
										break;
									}
							}
						}
						$_cat['goods_id_foto']=$foto
							?	($res[$_cat['category']][0]['m_products_id_isolux']
									? '<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($res[$_cat['category']][0]['m_products_id_isolux'],0,2).'/SN'.$res[$_cat['category']][0]['m_products_id_isolux'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_cat['name']).'"/>'
									: '<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/v/'.$res[$_cat['category']][0]['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_cat['name']).'"/>'
								)
							:	'';
						if($_cat['child']){
							foreach($childs as $_child)
								if(isset($res[$_child['category']][0]['m_products_foto'])&&$json_foto=json_decode($res[$_child['category']][0]['m_products_foto'])){				
									foreach($json_foto as $_foto)
										if($_foto->main)
											$foto=$_foto->file;
									if(!$foto)
										foreach($json_foto as $_foto){
											$foto=$_foto->file;
											break;
										}
									$_cat['goods_id_foto']=$res[$_child['category']][0]['m_products_id_isolux']
										? '<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.substr($res[$_child['category']][0]['m_products_id_isolux'],0,2).'/SN'.$res[$_child['category']][0]['m_products_id_isolux'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_cat['name']).'"/>'
										: '<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/v/'.$res[$_child['category']][0]['m_products_id'].'/'.$foto.'_med.jpg" alt="'.htmlspecialchars($_cat['name']).'"/>';
								}
						}
						//полное кол-во товаров категории
						$_cat['goods_count']=isset($res[$_cat['category']])?sizeof($res[$_cat['category']]):0;
						foreach($childs as $_child)
							$_cat['goods_count']+=isset($res[$_child['category']])?sizeof($res[$_child['category']]):0;
						//если есть подкатегории - пробегаемся по ним
						if($_cat['child'])
							_rec_count_cats($_cat['child'],$res);
					}
					
				}
				_rec_count_cats($cats,$res);
			}
			
			foreach($cats as $k=>$_cat){
				echo '<div class="main_products_list_items_item product_category">
						<div class="main_products_list_items_item_info">
							<div class="foto">',
								$_cat['goods_id_foto'],
							'</div>
							<div class="title">
								<p>
									<a href="'.$_cat['url'].'/" title="'.htmlspecialchars($_cat['namefull']).'">'.$_cat['name'].'</a>
								</p>
							</div>
							<div class="clr"></div>
							<div class="count">
								<p>'.$_cat['goods_count'].'&nbsp;'.transform::word_ending($_cat['goods_count'],array('товар','товара','товаров')).'</p>
							</div>
						</div>
					</div>';
			}
		}
		//показ категории на странице, не связанной с этой категорией
		else{
			$q='SELECT * FROM `formetoo_main`.`m_products_categories` WHERE `m_products_categories_parent`=2000000000 ORDER BY `m_products_categories_name`,`m_products_categories_order` DESC;';
			$cats=$sql->query($q);
			foreach($cats as $_cat){
				if($_cat['m_products_categories_products_count'])
					echo '<div class="main_products_list_items_item product_category">
							<div class="main_products_list_items_item_info">
								<div class="foto">
									<img src="//'.$_SERVER['G_VARS']['SERV_ST'].'/'.$_cat['m_products_categories_foto'].'_med.jpg" alt="'.htmlspecialchars($_cat['m_products_categories_name']).'"/>',
								'</div>
								<div class="title">
									<p>
										<a href="/catalog/'.$_cat['m_products_categories_name_seo'].'/" title="'.htmlspecialchars($_cat['m_products_categories_name']).'">'.$_cat['m_products_categories_name'].'</a>
									</p>
								</div>
								<div class="clr"></div>
								<div class="count">
									<p>'.$_cat['m_products_categories_products_count'].'&nbsp;'.transform::word_ending($_cat['m_products_categories_products_count'],array('товар','товара','товаров')).'</p>
								</div>
							</div>
						</div>';
			}
		}
		
	}
	
	public function getGoodPrice(){
		global $e,$sql,$current_product;
		
		$price=$current_product['m_products_price_general']*$this->ec[$current_product['m_products_price_currency']];
		$bonus=$current_product['m_products_price_general']*$this->ec[$current_product['m_products_price_currency']]*$current_product['m_products_price_bonus']*.01;
		
		return array(
			'price'=>$price,
			'bonus'=>$bonus
		);
	}
	
	public function getGoodUnit(){
		global $current_product;
		return $this->units[$current_product['m_products_unit']][0];
	}
	
	//атрибуты товара в карточке товара
	public function getGoodAttributes(){
		global $e,$sql,$current,$menu;
		
		$data['m_products_id']=array(1,null,null,10,1);
		$data['m_products_main_product']=array(1,null,null,null,1);
		array_walk($data,'check',true);
		
		if(!$e){
			$data['m_products_main_product']=$data['m_products_main_product']?$data['m_products_main_product']:0;

			$q='SELECT m_products_attributes_groups_list_id FROM `formetoo_main`.`m_products_attributes_groups` 
			LEFT JOIN `formetoo_main`.`m_products`
				ON `m_products`.`products_attributes_groups_id`=`m_products_attributes_groups`.`m_products_attributes_groups_id`
			WHERE 
			(`m_products`.`m_products_id`='.$data['m_products_id'].');';

			if($res=$sql->query($q)) {
				$attrsGroup = explode('|', $res[0]['m_products_attributes_groups_list_id']);
			}

			$q='SELECT * FROM `formetoo_main`.`m_products_attributes_list` 
				RIGHT JOIN `formetoo_main`.`m_products_attributes`
					ON `m_products_attributes`.`m_products_attributes_list_id`=`m_products_attributes_list`.`m_products_attributes_list_id`
				WHERE
					(
						`m_products_attributes`.`m_products_attributes_product_id`='.$data['m_products_id'].' OR 
						`m_products_attributes`.`m_products_attributes_product_id`='.$data['m_products_main_product'].') AND 
					`m_products_attributes_list_active`=1;';

			if($res=$sql->query($q)) {
				if(!empty($attrsGroup)) {
					$tempResult = array();
					foreach($res as $attrId) {
						$index = array_search($attrId['m_products_attributes_list_id'], $attrsGroup);
						if ($index) $tempResult[$index] = $attrId;
					}
					ksort($tempResult);
					return $tempResult;
				}
				
				return $res;
			}
			return false;
		}
		return false;
	}
	//все варианты товара (id)
	public function getGoodVariants(){
		global $e,$sql;

		if($this->goodVariants) return $this->goodVariants;
		
		$data['m_products_id']=array(1,null,null,10,1);
		$data['m_products_main_product']=array(1,null,null,null,1);
		array_walk($data,'check',true);

		if(!$e){
			$q='SELECT `m_products_id`,`m_products_name`,`m_products_name_full` FROM `formetoo_main`.`m_products` WHERE
					`m_products_id`='.$data['m_products_id'].' OR 
					`m_products_main_product`='.$data['m_products_id'].' 
					'.($data['m_products_main_product']
						?' OR
							`m_products_id`='.$data['m_products_main_product'].' OR 
							`m_products_main_product`='.$data['m_products_main_product']
						:''
					).';';
			if($res=$sql->query($q))
				return $this->goodVariants=$res;
		}
		return false;
	}
	public function getGoodText(){
		global $e,$sql;
		
		$data['m_products_id']=array(1,null,null,10,1);
		$data['m_products_main_product']=array(1,null,null,null,1);
		array_walk($data,'check',true);
		
		if(!$e){
			$q='SELECT * FROM `formetoo_main`.`m_products_desc` WHERE
					`m_products_desc_id`='.$data['m_products_id'].' LIMIT 1;';
			if($res=$sql->query($q))
				return $res;
			//если у дубля нет своего текста - показываем текст основного товара
			else{
				$q='SELECT * FROM `formetoo_main`.`m_products_desc` WHERE
					`m_products_desc_id`='.$data['m_products_main_product'].' LIMIT 1;';
				if($res=$sql->query($q))
					return $res;
			}
			return false;
		}
		return false;
	}
	
	public function getGoodReviews(){
		global $e,$sql,$current,$menu;
				
		$data['m_products_id']=array(1,null,null,10,1);
		array_walk($data,'check',true);
		
		if(!$e){
			//варианты товара
			$variants=array($data['m_products_id']);
			if($goodVariants=$this->getGoodVariants())
				foreach($goodVariants as $_variant)
					$variants[]=$_variant['m_products_id'];
			$q='SELECT `m_products_feedbacks`.*, `m_users`.*
					FROM `formetoo_main`.`m_products_feedbacks`
					LEFT JOIN `formetoo_main`.`m_users` 
						ON `m_users`.`m_users_id`=`m_products_feedbacks`.`m_products_feedbacks_users_id`
					WHERE `m_products_feedbacks_products_id` IN ('.implode(',',$variants).') AND 
						`m_products_feedbacks_active`=1 
					ORDER BY `m_products_feedbacks`.`m_products_feedbacks_date` DESC;';
			if($res=$sql->query($q))
				return $res;
			return false;
		}
		return false;
	}
	
	public function getGoodQNA(){
		global $e,$sql,$current,$menu;
		
		$data['m_products_id']=array(1,null,null,10,1);
		array_walk($data,'check',true);
		
		if(!$e){
			//варианты товара
			$variants=array($data['m_products_id']);
			if($goodVariants=$this->getGoodVariants())
				foreach($goodVariants as $_variant)
					$variants[]=$_variant['m_products_id'];
			$q='SELECT `m_products_qna`.*, `m_users`.*
					FROM `formetoo_main`.`m_products_qna`
					LEFT JOIN `formetoo_main`.`m_users` 
						ON `m_users`.`m_users_id`=`m_products_qna`.`m_products_qna_users_id`
					WHERE `m_products_qna_products_id` IN ('.implode(',',$variants).') AND 
						`m_products_qna_active`=1 
					ORDER BY `m_products_qna`.`m_products_qna_date` DESC;';
			if($res=$sql->query($q,'m_products_qna_question_id'))
				return $res;
			return false;
		}
		return false;
	}
	
	public static function search($search){
		global $sql,$current,$menu;
		
		function tag_b($text,$b){
			$text=is_array($b)?preg_replace('/(\w+)?('.implode('|',$b).')(\w+)?/ui','<b>$1$2$3</b>',$text):preg_replace('/(\w+)?('.$b.')(\w+)?/ui','<b>$1$2$3</b>',$text);
			return $text;
		}

		function t_sort($a,$b){
			$a_s=sizeof(explode('<b>',$a));
			$b_s=sizeof(explode('<b>',$b));
			if($a_s==$b_s)
				return 0;
			return ($a_s>$b_s)?-1:1;
		}
		
		function snippets($content,$words,$s_count=4,$envir=5,$envir_type='words'){
			$positions=array();
			$snippets=array();
			$content_size=mb_strlen($content);
			foreach($words as $word){
				$word_size=mb_strlen($word);
				//текущая позиция найденной строки
				$t_pos=0;
				$t=false;
				for($i=0;$i<20;$i++){
					//находим позицию вхождения строки в контенте
					$t=mb_stripos($content,$word,$t_pos,'utf-8');
					//если вхождение есть
					if($t!==false){
						//запоминаем позицию конца слова (чтобы дальше искать с нее) и позицию начала слова
						$t_pos=$t+$word_size;
						$positions[]=$t;
					}
					//если вхождений нет - выходим из цикла
					else
						break;
				}
				//для каждой найденной позиции
				foreach($positions as $pos)
					if($envir_type=='symbols'){
						//стартовый символ
						$start=($pos-$envir)<0?0:$pos-$envir;
						//количество символов
						$count=($start+$word_size+$envir*2)>$content_size?($content_size-1):($word_size+$envir*2);
						//получаем сниппет и разбиваем его в массив по словам
						$snippet=mb_substr($content,$start,$count,'utf-8');
						//разбиваем его на предложения
						$clauses=explode('.',$snippet);
						//разбиваем на слова
						$snippet=explode(' ',$snippet);
						//если запроса нет в первом предложении, удаляем первое предложение
						if($start&&mb_stripos($clauses[0],$word,0,'utf-8')===false&&sizeof($clauses)>1){
							array_shift($clauses);
							$snippet=explode(' ',implode('.',$clauses));
						}
						//если стартовый символ не нулевой - убираем первое слово (чтобы не было обрезков слов)
						elseif($start)
							array_shift($snippet);
						//заново разбиваем получившийся сниппет на предложения
						$clauses=explode('.',implode(' ',$snippet));
						//если запроса нет в последнем предложении, удаляем последнее предложение
						if($count==$word_size+$envir*2&&mb_stripos($clauses[sizeof($clauses)-1],$word,0,'utf-8')===false&&sizeof($clauses)>1){
							array_pop($clauses);
							$snippet=explode(' ',implode('.',$clauses));
						}
						//если количество символов в сниппете берется из параметра кол-ва символов, а не до конца контента, - убираем последнее слово (в другом случае сниппет идет до конца текста)
						elseif($count==$word_size+$envir*2)
							array_pop($snippet);
						//удаляем пустые элементы
						$snippets[]=array_filter($snippet);
					}
					elseif($envir_type=='words'){
						//стартовый символ
						$start=($pos-$envir*15)<0?0:$pos-$envir*15;
						//количество символов
						$count=($start+$word_size+$envir*2*15)>$content_size?($content_size-1):($word_size+$envir*2*15);
						//получаем сниппет
						$snippet=mb_substr($content,$start,$count,'utf-8');
						//разбиваем на слова
						$snippet=explode(' ',$snippet);
						//если стартовый символ не нулевой - убираем первое слово (чтобы не было обрезков слов)
						if($start)
							array_shift($snippet);
						//если количество символов в сниппете берется из параметра кол-ва символов, а не до конца контента, - убираем последнее слово (в другом случае сниппет идет до конца текста)
						if($count==$word_size+$envir*2*15)
							array_pop($snippet);
						//находим номер элемента с ключевым словом в массиве сниппета
						$i=0;
						foreach($snippet as $t_word){
							if(mb_stripos($t_word,$word,0,'utf-8')!==false)
								break;
							$i++;
						}
						//выбираем нужное кол-во слов до и после ключевого слова
						$snippet=array_slice($snippet,(($i-$envir)<0?0:$i-$envir),$envir*2+1);
						//удаляем пустые элементы
						$snippets[]=array_filter($snippet);
					}
			}
			//удаление похожих (>50% совпадений) сниппетов
			$snippets_size=sizeof($snippets);
			for($i=0;$i<$snippets_size;$i++)
				if(isset($snippets[$i]))
					for($j=0;$j<$snippets_size;$j++)
						if(isset($snippets[$i])&&$snippets[$i]&&isset($snippets[$j])&&$snippets[$j]&&$i!=$j)
							//удаляем совпадения 30% и больше
							if((sizeof(array_intersect($snippets[$i],$snippets[$j]))/min(sizeof($snippets[$i]),sizeof($snippets[$j])))>=0.4)
								if($snippets[$i]>=$snippets[$j])
									unset($snippets[$j]);
								else
									unset($snippets[$i]);
			//собираем слова в предложения, выделяя слова запроса
			foreach($snippets as &$snippet)
				$snippet=tag_b(implode(' ',$snippet),$words);
			//сортировка от количества выделенных слов
			usort($snippets,'t_sort');
			return array_slice(array_filter($snippets),0,$s_count);
		}
		
		if(mb_strlen($search,'utf-8')<3||mb_strlen($search,'utf-8')>50){
			$current['content']='Поисковый запрос должен составлять от 3-х до 50-ти символов.';
			return false;
		}
		$search=preg_replace('/[^a-zA-ZА-Яа-я0-9\s]/ui','',$search);
		//запоминаем фразу для поиска по всем словам сразу
		$search_aw=$search;
		//удаляем из запроса слова меньше 3 символов
		do
			$search=preg_replace('/(^|\s)[а-яА-Я]{1,2}($|\s)/ui',' ',$search,-1,$r_count);
		while($r_count);
		//если после удаления запрос меньше 3 символов
		if(mb_strlen($search,'utf-8')<3)
			//добавляем в массив поиска всю поисковыю фразу, декодированную в соответствии с контентом и фразу в том виде, в котором она была задана
			$words=array(transform::typography(trim($search_aw),0),trim($search_aw));
		else
			//разбиваем запрос по словам
			$words=explode(' ',trim($search));
		$q='SELECT * FROM `formetoo_main`.`content` WHERE (`h1` LIKE \'<>\'';
		foreach($words as $word)
			$q.=' OR `h1` LIKE \'%'.$word.'%\'';
		$q.=') OR (`title` LIKE \'<>\'';
		foreach($words as $word)
			$q.=' OR `title` LIKE \'%'.$word.'%\'';
		$q.=') OR (`content` LIKE \'<><>\'';	
		foreach($words as $word)
			$q.=' OR `content` LIKE \'%'.$word.'%\'';
		$q.=') LIMIT 20;';
		if($res=$sql->query($q)){
			$results=array();
			$current['content']='<ol>';
			foreach($res as $record){
				//удаляем теги в контенте
				$record['content']=iconv('cp1251','utf-8',str_replace(array("\r\n","\t","\n"),' ',strip_tags(html_entity_decode(htmlspecialchars_decode(iconv('utf-8','cp1251',$record['content']))))));
				$result['title']=tag_b($record['title'],$words);
				$result['href']='/?result_id='.$record['id'];
				//получаем сниппеты
				$result['snippets']=implode('<span class="three-dots"> … </span>',snippets($record['content'],$words));
				//получаем ссылку
				$result['link']='<span class="search-results-link"><a href="/" target="_blank">membrana.pro<span class="underline"></span></a>';
				$parents=array();
				$menu->parents($record['id'],$parents);
				$url='';
				$parents=array_reverse($parents);
				foreach($parents as $parent){
					$url.=$parent['url'].'/';
					$result['link'].=' > <a href="/'.$url.'" target="_blank">'.$parent['name'].'<span class="underline"></span></a>';
				}
				$result['link'].=' > <a href="/'.$url.$menu->nodes_id[$record['id']]['url'].'/" target="_blank">'.$menu->nodes_id[$record['id']]['name'].'<span class="underline"></span></a></span>';
				$results[]='<li><p><a target="_blank" href="'.$url.$menu->nodes_id[$record['id']]['url'].'/">'.$result['title'].'<span class="underline"></span></a>'.($result['snippets']?'<br/>'.$result['snippets']:'').'<br/>'.$result['link'].'</p></li>';			
			}
			//сортируем результаты по количеству тегов <b>
			usort($results,'t_sort');
			$current['content'].=implode('',$results).'</ol>';
		}
		else
			$current['content']='Поиск не дал результатов.';
	}
}

//временный класс для сортировки массива в цикле по ключу с передачей дополнительного параметра
class attrname_ab_ord_mc{
	private $meta;
	function __construct($meta=null){
		$this->meta=$meta;
	}
	function s_ord($a,$b){
		if ($a[0]['m_products_attributes_list_site_open']==$b[0]['m_products_attributes_list_site_open']) return 0;
		return ($a[0]['m_products_attributes_list_site_open']<$b[0]['m_products_attributes_list_site_open'])?1:-1;
	}
	function ab_ord($a,$b,$attr_values_all=array()){
		if(!isset($attr_values_all[$a])||!isset($attr_values_all[$b])) return 0;
		if(strcasecmp(mb_strtolower($attr_values_all[$a][0]['m_products_attributes_values_value']),mb_strtolower($attr_values_all[$b][0]['m_products_attributes_values_value']))==0) return 0;
		return strcasecmp(mb_strtolower($attr_values_all[$a][0]['m_products_attributes_values_value']),mb_strtolower($attr_values_all[$b][0]['m_products_attributes_values_value']))>0?1:-1;
	}
	function call($a,$b){
		return $this->ab_ord($a,$b,$this->meta);
	}
}
?>