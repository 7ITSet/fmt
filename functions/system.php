<?
defined ('_DSITE') or die ('Access denied');

$G['DOMAIN']=$_SERVER['HTTP_HOST'];
//<DEBUG
if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
	$_SERVER['SUBDOMAIN_PART']=substr($G['DOMAIN'],0,stripos($G['DOMAIN'],'.'));
	$_SERVER['SERVER_NAME']=$G['DOMAIN']=substr(strstr($G['DOMAIN'],'.'),1);
}
//DEBUG>
$G['DIR']=$_SERVER['DOCUMENT_ROOT'];
$G['SUBDOMAIN']=$_SERVER['SUBDOMAIN_PART'];

function url(){
	$uri=explode('?',$_SERVER['REQUEST_URI'],2);
	return $uri[0];
}

$products_sort=array(
	null=>'Рейтингу',
	'rate'=>'Рейтингу',
	'popular'=>'Популярности',
	'newest'=>'Новизне',
	'name'=>'Наименованию',
	'price'=>'Возрастанию цены',
	'price-'=>'Убыванию цены',
);
function val_a($param,$a=array(),$b=array(),$html=0){
	global $sql;
	if(is_array($param)){
		foreach($param as &$param_){
			if(is_array($param_))
				val_a($param_,$a,$b,$html);
			else{
				$s=array_merge(array('\\','..','¦',chr(0)),$a);
				$r=array_merge(array('','','',''),$b);
				$param_=str_replace($s,$r,$param_);
				if(!$html){
					$param_=htmlspecialchars($param_);
					$param_=strip_tags($param_);
				}
				$param_=$sql->real_escape($param_);
			}
		}
	}
}
	
function val($param,$a=array(),$b=array(),$html=0){
global $sql;
	//если a не пустой - дозаполняем b до размера a пустыми элементами для замены
	$b=($a)?array_pad($b,sizeof($a),''):$b;
	//соединяем массивы поиска и массивы замены
	$s=array_merge(array('\\','..','¦',chr(0)),$a);
	$r=array_merge(array('','','',''),$b);

	if(is_array($param))
		val_a($param,$a,$b,$html);
	else{
		$param=str_replace($s,$r,$param);
		if(!$html){
			$param=htmlspecialchars($param);
			$param=strip_tags($param);
		}
		$param=$sql->real_escape($param);
	}
	return $param;
}
function get($param,$a=array(),$b=array(),$html=0){
	if(isset($_GET[$param]))
		return val($_GET[$param],$a,$b,$html);
	else
		return '';
}
function post($param,$a=array(),$b=array(),$html=0){
	if(isset($_POST[$param]))
		return val($_POST[$param],$a,$b,$html);
	else
		return '';
}

function in_array_assoc($array,$key,$value){
	$within_array=false;
	foreach($array as $k=>$v)
		if(is_array($v)){
			$within_array=in_array_assoc($v,$key,$value);
			if($within_array==true)
				break;
		} 
		else
			if($v==$value&&$k==$key){
				$within_array=true;
				break;
			}
	return $within_array;
}

//текущая или заданная дата и время
function dt($time=''){
	$time=$time?$time:time();
	return date('Y-m-d H:i:s',$time);
}
//текущая дата и время в unix или другом формате
function dtu($time='',$format=''){
	$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
	if(!$format)
		return $date->getTimestamp();
	return $date->format($format);	
}
//смена формата даты и времени
function dtc($time='',$change=''){
	$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
	if($change)
		$date->modify($change);
	return $date->format('Y-m-d H:i:s');	
}
function dtru(){
	$translate = array(
		"am" => "дп",
		"pm" => "пп",
		"AM" => "ДП",
		"PM" => "ПП",
		"Monday" => "Понедельник",
		"Mon" => "Пн",
		"Tuesday" => "Вторник",
		"Tue" => "Вт",
		"Wednesday" => "Среда",
		"Wed" => "Ср",
		"Thursday" => "Четверг",
		"Thu" => "Чт",
		"Friday" => "Пятница",
		"Fri" => "Пт",
		"Saturday" => "Суббота",
		"Sat" => "Сб",
		"Sunday" => "Воскресенье",
		"Sun" => "Вс",
		"January" => "Января",
		"Jan" => "Янв",
		"February" => "Февраля",
		"Feb" => "Фев",
		"March" => "Марта",
		"Mar" => "Мар",
		"April" => "Апреля",
		"Apr" => "Апр",
		"May" => "Мая",
		"May" => "Мая",
		"June" => "Июня",
		"Jun" => "Июн",
		"July" => "Июля",
		"Jul" => "Июл",
		"August" => "Августа",
		"Aug" => "Авг",
		"September" => "Сентября",
		"Sep" => "Сен",
		"October" => "Октября",
		"Oct" => "Окт",
		"November" => "Ноября",
		"Nov" => "Ноя",
		"December" => "Декабря",
		"Dec" => "Дек",
		"st" => "ое",
		"nd" => "ое",
		"rd" => "е",
		"th" => "ое"
	);
	if (func_num_args()>1){
		$time=func_get_arg(1);
		$date = new DateTime($time,new DateTimeZone('Europe/Minsk'));
		$time=$date->getTimestamp();
		return strtr(date(func_get_arg(0),$time),$translate);
	}
	else return strtr(date(func_get_arg(0)),$translate);
}

//генерация уникального id
function get_id($table='',$count=0,$field='',$str=false){
global $sql;
	$a=array();
	$field=$field?$field:$table.'_id';
	if($str){
		$s_a=str_split('QWXZASERGDFCVTYHBNUOPILMKJ');
		$s_b=str_split('plmkoiujytgnhbvcrdfeqwsazx');
		$s_c=str_split('7896523014');
		//объединяем массивы
		$s=array_merge($s_a,$s_b,$s_c);
		shuffle($s);
		$s=implode('',$s);
	}
	
	if($table){
		$vcdb_tables=array(
			'm_buh',
			'm_buh_kassa',
			'm_contragents',
			'm_contragents_address',
			'm_contragents_rs',
			'm_contragents_tel',
			'm_delivery_transport',
			'm_documents',
			'm_documents_templates',
			'm_employees',
			'm_info_address_type',
			'm_info_contragents_type',
			'm_info_ip',
			'm_info_ip_city',
			'm_info_orders_status',
			'm_info_post',
			'm_info_settings',
			'm_info_tel_city_code',
			'm_info_tel_type',
			'm_info_units',
			'm_orders'			
		);
		$query=(in_array($table,$vcdb_tables))?'SELECT `'.$field.'` FROM `formetoo_cdb`.`'.$table.'`;':'SELECT `'.$field.'` FROM `formetoo_main`.`'.$table.'`;';
		if($res=$sql->query($query))
			foreach($res as $record)
				$a[]=$record[$field];
	}
	if($count==0){
		$id=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
		//пока id не будет уникальным, генерируем новый
		while(in_array($id,$a))
			$id=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
	}
	else{
		$id=array();
		for($i=0;$i<$count;$i++){
			$id_i=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
			//пока id не будет уникальным, генерируем новый
			while(in_array($id_i,$a))
				$id_i=$str?substr($s,mt_rand(0,(strlen($s)-13)),12):mt_rand(1000000000,9999999999);
			$id[$i]=$id_i;
		}
	}
	return $id;
}

function get_pass($length=10){
	//преобразуем строки в массивы
	$a=str_split('!#&:;$%)^*(@+,_-.');
	$b=str_split('QWXZASERGDFCVTYHBNUOPILMKJ');
	$c=str_split('plmkoiujytgnhbvcrdfeqwsazx');
	$d=str_split('7896523014');
	//объединяем массивы
	$a=array_merge($b,$c,$d);
	
	//перемешиваем массив
	shuffle($a);
	$t_a=implode('',$a);
	//собираем произвольную строку
	return substr($t_a,mt_rand(0,(strlen($t_a)-$length+1)),$length);
}

function pre($var,$vardump=false){
	echo '<pre>';
	if(!$vardump) print_r($var);
	else var_dump($var);		
	echo '</pre>';
}

function recursive_cast_to_array($o) {
	$a = (array)$o;
	foreach ($a as &$value) {
		if (is_object($value)) {
			$value = recursive_cast_to_array($value);
		}
	}
	return $a;
}

function change_key($array,$key_field,$unique=false){
	$result=array();
	if($array)
		foreach($array as $el)
			if($unique)
				$result[$el[$key_field]]=$el;
			else{
				$result[$el[$key_field]][]=$el;
			}
	return $result;
}

/* $data['tcity']=array(обязательный,минимум,максимум,число символов,(1 - число, 2 - телефонный номер, 3 - IP адрес, 4 - электронная почта, 5 - ИНН));
array_walk($data,'check'); */
//проверка переменных
function check(&$el,$key,$get=false){
global $e;
	//если переменная - массив
	if(strpos($key,'[]')!==false)
		$key=substr($key,0,-2);
	//читаем переменную, не очищая её
	if(!$get)
		$t_el=isset($_POST[$key])?$_POST[$key]:'';
	else
		$t_el=isset($_GET[$key])?$_GET[$key]:'';
	//если переменная не является массивом
	if(!is_array($t_el)){
		if (isset($el[0]))
			if ($t_el=='')
				$e[]='Не заполнено обязательное поле ['.$key.'], данные: «'.$t_el.'»';
		if ($t_el!=''&&isset($el[1])&&isset($el[2]))
			if (mb_strlen($t_el,'utf-8')<$el[1]||mb_strlen($t_el,'utf-8')>$el[2])
				$e[]='Длина строки не входит в допустимый диапазон от '.$el[1].' до '.$el[2].' символов в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el!=''&&isset($el[1])&&!isset($el[2]))
			if (mb_strlen($t_el,'utf-8')<$el[1])
				$e[]='Минимальное количество символов '.$el[1].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el!=''&&isset($el[2])&&!isset($el[1]))
			if (mb_strlen($t_el,'utf-8')>$el[2])
				$e[]='Максимальное количество символов '.$el[2].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el!=''&&isset($el[3]))
			if (mb_strlen($t_el,'utf-8')!=$el[3])
				$e[]='Количество символов должно быть '.$el[3].' в поле ['.$key.'], данные: «'.$t_el.'» ('.mb_strlen($t_el,'utf-8').' символов)';
		if ($t_el!=''&&isset($el[4]))
			switch($el[4]){
				case 1:
					if (!is_numeric($t_el))
						$e[]='Возможен ввод только цифр в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 2:
					if (!preg_match('/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i',$t_el)||strlen($t_el)!=16)
						$e[]='Телефон не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 3:
					if (!preg_match('/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',$t_el))
						$e[]='IP-адрес не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 4:
					if (!preg_match('/^[a-z0-9]+[a-z0-9_.-]*@[a-z0-9]+[a-z0-9.-]*\\.[a-z]{2,}$/is',$t_el))
						$e[]='E-mail не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
				case 5:
					if (strlen($t_el)==10){
						if(substr($t_el,-1)!=((2*substr($t_el,0,1)+4*substr($t_el,1,1)+10*substr($t_el,2,1)+3*substr($t_el,3,1)+5*substr($t_el,4,1)+9*substr($t_el,5,1)+4*substr($t_el,6,1)+6*substr($t_el,7,1)+8*substr($t_el,8,1))%11)%10)
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					}
					elseif (strlen($t_el)==12){
						if(substr($t_el,-2,1)!=((7*substr($t_el,0,1)+2*substr($t_el,1,1)+4*substr($t_el,2,1)+10*substr($t_el,3,1)+3*substr($t_el,4,1)+5*substr($t_el,5,1)+9*substr($t_el,6,1)+4*substr($t_el,7,1)+6*substr($t_el,8,1)+8*substr($t_el,9,1))%11)%10||substr($t_el,-1,1)!=((3*substr($t_el,0,1)+7*substr($t_el,1,1)+2*substr($t_el,2,1)+4*substr($t_el,3,1)+10*substr($t_el,4,1)+3*substr($t_el,5,1)+5*substr($t_el,6,1)+9*substr($t_el,7,1)+4*substr($t_el,8,1)+6*substr($t_el,9,1)+8*substr($t_el,10,1))%11)%10)
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					}
					else
						$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$t_el.'»';
					break;
			}
	}
	//если переменная - массив
	else{
		foreach($t_el as $_t_el){
			if (isset($el[0]))
				if ($_t_el=='')
					$e[]='Не заполнено обязательное поле ['.$key.'], данные: «'.$_t_el.'»';
			if ($_t_el!=''&&isset($el[1])&&isset($el[2]))
				if (mb_strlen($_t_el,'utf-8')<$el[1]||mb_strlen($_t_el,'utf-8')>$el[2])
					$e[]='Длина строки не входит в допустимый диапазон от '.$el[1].' до '.$el[2].' символов в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el!=''&&isset($el[1])&&!isset($el[2]))
				if (mb_strlen($_t_el,'utf-8')<$el[1])
					$e[]='Минимальное количество символов '.$el[1].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el!=''&&isset($el[2])&&!isset($el[1]))
				if (mb_strlen($_t_el,'utf-8')>$el[2])
					$e[]='Максимальное количество символов '.$el[2].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el!=''&&isset($el[3]))
				if (mb_strlen($_t_el,'utf-8')!=$el[3])
					$e[]='Количество символов должно быть '.$el[3].' в поле ['.$key.'], данные: «'.$_t_el.'» ('.mb_strlen($_t_el,'utf-8').' символов)';
			if ($_t_el!=''&&isset($el[4]))
				switch($el[4]){
					case 1:
						if (!is_numeric($_t_el))
							$e[]='Возможен ввод только цифр в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 2:
						if (!preg_match('/^\+7 \d{3,5} \d{1,3}-\d{2}-\d{2}$/i',$_t_el)||strlen($_t_el)!=16)
							$e[]='Телефон не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 3:
						if (!preg_match('/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',$_t_el))
							$e[]='IP-адрес не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 4:
						if (!preg_match('/^[a-z0-9]+[a-z0-9_.-]*@[a-z0-9]+[a-z0-9.-]*\\.[a-z]{2,}$/is',$_t_el))
							$e[]='E-mail не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
					case 5:
						if (strlen($_t_el)==10){
							if(substr($_t_el,-1)!=((2*substr($_t_el,0,1)+4*substr($_t_el,1,1)+10*substr($_t_el,2,1)+3*substr($_t_el,3,1)+5*substr($_t_el,4,1)+9*substr($_t_el,5,1)+4*substr($_t_el,6,1)+6*substr($_t_el,7,1)+8*substr($_t_el,8,1))%11)%10)
								$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						}
						elseif (strlen($_t_el)==12){
							if(substr($_t_el,-2,1)!=((7*substr($_t_el,0,1)+2*substr($_t_el,1,1)+4*substr($_t_el,2,1)+10*substr($_t_el,3,1)+3*substr($_t_el,4,1)+5*substr($_t_el,5,1)+9*substr($_t_el,6,1)+4*substr($_t_el,7,1)+6*substr($_t_el,8,1)+8*substr($_t_el,9,1))%11)%10||substr($_t_el,-1,1)!=((3*substr($_t_el,0,1)+7*substr($_t_el,1,1)+2*substr($_t_el,2,1)+4*substr($_t_el,3,1)+10*substr($_t_el,4,1)+3*substr($_t_el,5,1)+5*substr($_t_el,6,1)+9*substr($_t_el,7,1)+4*substr($_t_el,8,1)+6*substr($_t_el,9,1)+8*substr($_t_el,10,1))%11)%10)
								$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						}
						else
							$e[]='ИНН не верный в поле ['.$key.'], данные: «'.$_t_el.'»';
						break;
				}
		}
	}
	//если не было ошибок, то возвращаем очищенную переменную
	if (!is_array($e)||sizeof($e)==0)
		$el=val($t_el);
	else
		$el=null;
}

function elogs($file=null,$func=null,$data=null){
	global $e,$sql,$user;
	if(!$e)
		$e[]='Ошибка в запросе БД';
	$message=array('file'=>$file,'function'=>$func,'data'=>$data,'errors'=>$e);
	$message=json_encode($message,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	$q='INSERT INTO `formetoo_main`.`m_logs_error` (
			`m_logs_error_logs_m_users_id`,
			`m_logs_error_log_message`,
			`m_logs_error_logs_date`
		) VALUES (
			'.((isset($user)&&null!==$user->getInfo('m_users_id'))?$user->getInfo('m_users_id'):0).',
			\''.$message.'\',
			\''.dt().'\'
		);';
	$sql->query($q);
}

class transform{
	public static function typography($text='',$html=1){
		$pattern=array(
			//заменяем вывод параметров тегов ="значение" меняем на =[значение]
			'/=&quot;(.*?)&quot;/ui',
			//пробелы после знаков препинания
			'/([,:\!\?])(\w)/ui',
			//многоточие
			'/(\.{3,})/ui',
			//(",',`)слово -> «слово
			'/(^|\s)&quot;(\S)/ui',
			//слово(",',`) -> слово»
			'/(\S)&quot;($|\W|[ .,?!])/ui',
			//- -> —
			'/ - /',
			//ул. Советская -> ул.&nbsp;Советская
			'/(^|\s)(тер\.|ст\.|дор\.|наб\.|пер\.|пл\.|платф\.|стр\.|вл\.|корп\.|туп\.|ул\.|пр-кт|№|ТЦ|ТРЦ|оф.|оф|о-ва|в|во|без|до|из|к|на|по|а|о|обо|от|перед|при|через|с|у|за|над|для|об|под|про|не|ни|из-под|из-за|по-над|по-за|и|р\/с|к\/с|ИНН|КПП|БИК|тел\.)($|\s)/ui',
			//ул. Советская -> ул.&nbsp;Советская (не влияет на инициалы)
			'/(^|\s)(г\.|д\.|м\.|п\.|с\.|х\.)($|\s)/',
			//Московский мкр. -> Московский&nbsp;мкр.
			'/(\s)(Аобл\.|обл\.|мкр\.|р-н|ш\.|этаж|оф.|кг|г|м|см|км|бы|ли|же|ж)($|\s)/ui',
			//офис №100 -> офис&nbsp;№100
			'/(офис) ([№0-9])/ui',
			//20 офис -> 20&nbsp;офис
			'/([№0-9]) (офис)/ui',
			//+7 (123) 456-48-90 -> <nobr>+7 (123) 456-48-90</nobr>
			'/(\+7[- ]?\(?\d{3,5}\)?[- ]?\d{1,3}[- ]?\d{2}[- ]?\d{2})/ui',
			//обратно меняем значения тегов
			'/=\[(.*?)\]/ui',
			//удаляем лишние пробелы перед закрытием тегов
			'/( |&nbsp;)+<\//ui',
			//пробелы между неразрывными пробелами
			'/(\s{1,})?(&nbsp;)(\s{1,})?/ui',
			//лишние неразрывные пробелы
			'/(&nbsp;)+/ui',
			//лишние пробелы (в т.ч. неразрывные пробелы) перед знаками препинания
			'/( |&nbsp;)+([.,:;\!\?])/ui'
		);
		$replacement=array(	
			'=[$1]',
			'$1 $2',
			'…',
			'$1«$2',
			'$1»$2',
			'&nbsp;— ',
			'$1$2&nbsp;$3',
			'$1$2&nbsp;$3',
			'$1&nbsp;$2$3',
			'$1&nbsp;$2',
			'$1&nbsp;$2',
			'<nobr>$1</nobr>',
			'=\\"$1\\"',
			'<\/',
			'&nbsp;',
			'&nbsp;',
			'$2'
		);
		$text=preg_replace($pattern,$replacement,$text);
		if($html)
			return $text;
		else
			return html_entity_decode(htmlspecialchars_decode($text),ENT_COMPAT|ENT_HTML401,'utf-8');
	}
	
	//вывод определенного количества слов из текста	
	public static function some($text,$count,$symbols=false,$symbols_from_words=false){
		if(!$symbols&&!$symbols_from_words){
			$t=explode(' ',$text);
			$t=array_slice($t,0,$count);
			$t=implode(' ',$t);
			if ($t!=$text)
				$t.='&nbsp;…';
			return $t;
		}
		elseif($symbols_from_words){
			$t=explode(' ',$text);
			$t=array_slice($t,0,$count);
			foreach($t as &$_t)
				if(mb_strlen($_t,'utf-8')!=mb_strlen(mb_substr($_t,0,$symbols_from_words,'utf-8'),'utf-8'))
					$_t=mb_substr($_t,0,$symbols_from_words,'utf-8').'…';
			$t=implode(' ',$t);
			if ($t!=$text)
				$t.='&nbsp;…';
			return $t;
		}
		if(mb_strlen($text,'utf-8')!=mb_strlen(mb_substr($text,0,$count,'utf-8'),'utf-8'))
			return	mb_substr($text,0,$count,'utf-8').'…';
		return $text;
	}
	
	//фамилия и.о.
	public static function fio($text){
		$text=explode(' ',$text);
		$text[1]=isset($text[1])?mb_substr($text[1],0,1,'utf-8').'.':'';
		$text[2]=isset($text[2])?mb_substr($text[2],0,1,'utf-8').'.':'';
		return implode('&nbsp;',$text);
	}
	
	public static function price_o($price,$floats=true,$nbsp=false){
		$price=number_format($price,2,',',' ');
		if(substr($price,-2)=='00'&&!$floats)
			$price=substr($price,0,-3);
		if($nbsp)
			$price=str_replace(' ','&nbsp;',$price);
		return $price;
	}
	public static function stock_o($num){
		
		return rtrim(rtrim(number_format($num,4,',',' '),'0'),',');
	}
	
	public static function tel($t){
		$t=preg_replace('/D/i','',$t);
		if(strlen($t)==6)
			$t=preg_replace('/(\d{2})(\d{2})(\d{2})/i','+7 (4932) $1-$2-$3',$t);
		elseif(strlen($t)==10)
			$t=preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/i','+7 ($1) $2-$3-$4',$t);
		else
			$t='';
		return $t;
	}
	public static function telClean($t){
		$t=preg_replace('/[^0-9]/ui','',$t);
		return '+'.$t;
	}

	//преобразование даты в формат "дд месяц гггг", 1 - время (без параметра текущее), 2 - показывать "сегодня", "вчера" вместо текущей и прошедшей даты
	public static function date_f($t=false,$words=false,$month_only=false){
		$m=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(strlen($t)!=14){
			if(!$month_only){
				if ($t==false)
					$t=time();
				if ($words){
					if (date('Ymd',$t)==date('Ymd')+1)
						return 'завтра';
					if (date('Ymd',$t)==date('Ymd'))
						return 'сегодня';
					if (date('Ymd',$t)+1==date('Ymd'))
						return 'вчера';}
				return date('j',$t).' '.$m[date('m',$t)-1].' '.date('Y',$t);
			}
			else
				return $m[date('m',$t)-1];
		}
		else{
			$t=preg_split('//',$t,-1,PREG_SPLIT_NO_EMPTY);
			return $t[6].$t[7].' '.$m[($t[4].$t[5])*1-1].' '.$t[0].$t[1].$t[2].$t[3];
		}
	}
	
	//сумма прописью
	private static function morph($n,$f1,$f2,$f5){
		$n=abs(intval($n))%100;
		if($n>10&&$n<20)
			return $f5;
		$n=$n%10;
		if($n>1 && $n<5)
			return $f2;
		if($n==1)
			return $f1;
		return $f5;
	}
	public static function summ_text($summ,$rouble=true,$upper=true,$rp=false){
		$nul='ноль';
		$ten=array(
			array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
			array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
		);
		$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
		$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
		$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
		$unit=array(
			array('копейка','копейки','копеек',1),
			array('рубль','рубля','рублей',0),
			array('тысяча','тысячи','тысяч',1),
			array('миллион','миллиона','миллионов',0),
			array('миллиард','милиарда','миллиардов',0),
		);
		if($rp==true){
			$nul='ноля';
			$ten=array(
				array('','одного','двух','трёх','четырёх','пяти','шести','семи','восьми','девяти'),
				array('','одного','двух','трёх','четырёх','пяти','шести','семи','восьми','девяти'),
			);
			$a20=array('десяти','одиннадцати','двенадцати','тринадцати','четырнадцати' ,'пятнадцати','шестнадцати','семнадцати','восемнадцати','девятнадцати');
			$tens=array(2=>'двадцати','тридцати','сорока','пятьдесяти','шестьдесяти','семьдесяти' ,'восемьдесяти','девяноста');
			$hundred=array('','ста','двухсот','трёхсот','четырёхсот','пятисот','шестисот', 'семисот','восмьисот','девятисот');
		}
		list($rub,$kop)=explode('.',sprintf("%015.2f",floatval($summ)));
		$out=array();
		if (intval($rub)>0){
			foreach(str_split($rub,3) as $uk=>$v){
				if(!intval($v))
					continue;
				$uk=sizeof($unit)-$uk-1;
				$gender=$unit[$uk][3];
				list($i1,$i2,$i3)=array_map('intval',str_split($v,1));
				$out[]=$hundred[$i1];
				if ($i2>1) 
					$out[]=$tens[$i2].' '.$ten[$gender][$i3];
				else 
					$out[]=($i2>0)?$a20[$i3]:$ten[$gender][$i3];
				if ($uk>1)
					$out[]=transform::morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
			}
		}
		else 
			$out[]=$nul;
		$out[]=transform::morph(intval($rub),$unit[1][0],$unit[1][1],$unit[1][2]);
		if($rouble==true)
			$out[]=$kop.' '.transform::morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]);
		$result=trim(preg_replace('/ {2,}/',' ',implode(' ',$out)));
		if($upper==true)
			$result=mb_strtoupper(mb_substr($result,0,1,'utf-8'),'utf-8').mb_substr($result,1,mb_strlen($result),'utf-8');
		//если нужно написать число без рублей и копеек
		if($rouble!=true){
			$array_rouble=array('рубль','рубля','рублей');
			$result=str_replace($array_rouble,'',$result);
		}
		return trim($result);
	}
	
	function numberof($numberof, $value, $suffix){
		// не будем склонять отрицательные числа
		$numberof = abs($numberof);
		$keys = array(2, 0, 1, 1, 1, 2);
		$mod = $numberof % 100;
		$suffix_key = $mod > 4 && $mod < 20 ? 2 : $keys[min($mod%10, 5)];

		return $value . $suffix[$suffix_key];
	}
	
	private static function get_include_contents($filename) {
	global $user;	
		ob_start();
		include_once 'require/'.$filename[1].'.php';
		return ob_get_clean();
	}
	
	public static function translit($text){
		$replace=array('А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'e','Ж'=>'j','З'=>'z','И'=>'i','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'h','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh','Щ'=>'shch','Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya','а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'y','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',' '=>'-','.'=>'','/'=>'_','&quot;'=>'','&nbsp;'=>'','<br/>'=>'');
		$text=strtr($text,$replace);
		//убираем недопустимые символы
		$text=preg_replace('/[^A-Za-z0-9_\-]/', '', $text);
		//замена повторов
		$text=preg_replace('#(\_){1,}#', '\1',$text);
		return $text;
	}
	
	public static function layout($text){
		$lat_rus=array(
			"q"=>"й","w"=>"ц","e"=>"у","r"=>"к","t"=>"е","y"=>"н","u"=>"г","i"=>"ш","o"=>"щ","p"=>"з","["=>"х","{"=>"х","]"=>"ъ","}"=>"ъ",
			"a"=>"ф","s"=>"ы","d"=>"в","f"=>"а","g"=>"п","h"=>"р","j"=>"о","k"=>"л","l"=>"д",";"=>"ж",":"=>"ж","'"=>"э","\""=>"э",
			"z"=>"я","x"=>"ч","c"=>"с","v"=>"м","b"=>"и","n"=>"т","m"=>"ь",","=>"б","<"=>"б","."=>"ю",">"=>"ю","`"=>"ё","~"=>"ё","/"=>".","?"=>"."
		);
		$rus_lat=array(
			"й"=>"q","ц"=>"w","у"=>"e","к"=>"r","е"=>"t","н"=>"y","г"=>"u","ш"=>"i","щ"=>"o","з"=>"p","х"=>"[",
			"ф"=>"a","ы"=>"s","в"=>"d","а"=>"f","п"=>"g","р"=>"h","о"=>"j","л"=>"k","д"=>"l","ж"=>";",
			"я"=>"z","ч"=>"x","с"=>"c","м"=>"v","и"=>"b","т"=>"n","ь"=>"m","б"=>",","ю"=>".","."=>"/","ё"=>"`"
		);
		$matches=array(0);
		preg_match_all('/[а-яё]/iu',$text,$matches);
		if(sizeof($matches[0])>(mb_strlen($text)/2)){
			$text=preg_split('//u', $text, null, PREG_SPLIT_NO_EMPTY);
			foreach($text as &$_char){
				$_char=mb_strtolower($_char);
				$_char=isset($rus_lat[$_char])?$rus_lat[$_char]:$_char;
			}
		}
		else{
			$text=preg_split('//u', $text, null, PREG_SPLIT_NO_EMPTY);
			foreach($text as &$_char){
				$_char=mb_strtolower($_char);
				$_char=isset($lat_rus[$_char])?$lat_rus[$_char]:$_char;
			}
		}
		return implode('',$text);
	}
	
	public static function word_ending($num,$words){
		$num=$num%100;
		if ($num>19){
			$num=$num%10;
		}
		switch($num){
			case 1:
				return($words[0]);
				break;
			case 2:
			case 3:
			case 4:
				return($words[1]);
				break;
			default: 
				return($words[2]);
				break;
		}
	}
	
	public static function optimize($html){
		global $current_area,$current,$content;
		
		$html=preg_replace_callback('/\{require: ?(\w+)\}/ui','transform::get_include_contents',$html);	
		if(mb_strpos($html,'{attributes}',0,'utf-8')){
			$pattern=array('{products_count}','{attributes}','<ul></ul>','<ul class="nav_sublevel"></ul>','<div class="breadcrumbs">
			</div>
'/* ,"\r\n","\t","\n" */);
			$replacement=array((isset($current['products_count'])?$current['products_count']:0),$content->getAttributes(),'');
			$html=str_replace($pattern,$replacement,$html);
		}
		else{
			$pattern=array('<ul></ul>','<ul class="nav_sublevel"></ul>','<div class="breadcrumbs">
			</div>
'/* ,"\r\n","\t","\n" */);
			$replacement=array('');
			$html=str_replace($pattern,$replacement,$html);
		}
		$pattern=array('{ГОРОД}','{города}','{городу}','{город}','{городом}','{городе}','{РЕГИОН}','{региона}','{региону}','{регион}','{регионом}','{регионе}','{tel_mob}','{tel_office}','{tel_office_nobr}','{url}','{mail}','{address}','{map}');
		$replacement=array($current_area['m_info_city_name_city_im'],$current_area['m_info_city_name_city_rod'],$current_area['m_info_city_name_city_dat'],$current_area['m_info_city_name_city_tv'],$current_area['m_info_city_name_city_vin'],$current_area['m_info_city_name_city_pr'],$current_area['m_info_city_name_area_im'],$current_area['m_info_city_name_area_rod'],$current_area['m_info_city_name_area_dat'],$current_area['m_info_city_name_area_tv'],$current_area['m_info_city_name_area_vin'],$current_area['m_info_city_name_area_pr'],$current_area['m_info_city_tel_mob'],$current_area['m_info_city_tel_office'],str_replace(array(' ','-'),'',$current_area['m_info_city_tel_office']),$current_area['m_info_city_url'],$current_area['m_info_city_mail'],$current_area['m_info_city_address'],$current_area['m_info_city_map']);
		$html=str_replace($pattern,$replacement,$html);
		print_r($html);
	}
	
}

function get_filesize($f_name){
	$f_size=filesize($f_name);
	$f_size=($f_size)?$f_size:0;
	if ($f_size>1048576)
		$f_size=round($f_size/1048576,1).' МБ';
	else
		$f_size=round($f_size/1024,1).' КБ';
	return $f_size;
}

class user_info{
	//определение IP адреса
	public static function ip(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		else 
			$ip=$_SERVER['REMOTE_ADDR'];
		return $ip;}
	
	public static function city($capital=false){
	global $sql;
		$ip=self::ip();
		$query='SELECT * FROM `formetoo_cdb`.`m_info_ip_city` LEFT JOIN `formetoo_cdb`.`m_info_ip` ON (`m_info_ip_city`.`city_id`=`m_info_ip`.`city_id`) WHERE `ip_s`<=INET_ATON(\''.$ip.'\') AND `ip_e`>=INET_ATON(\''.$ip.'\');';
		if($res=$sql->query($query)){
			//если нужно получить сам город
			if(!$capital)
				return $res[0];
			//если нужно получить столицу
			else{
				$query='SELECT * FROM `formetoo_cdb`.`m_info_ip_city` WHERE `area`=\''.$res[0]['area'].'\' AND `capital`=\'1\';';
				if($res=$sql->query($query))
					return $res[0];
			}
		}
		else
			return false;
	}
}

class settings{
	private 
		$settingsAll,
		$settings;
	
	public function getSetting($name='',$all_fields=false){
		global $sql;
		
		if($all_fields&&$this->settingsAll) return $this->settingsAll;
		if($name&&isset($this->setting[$name])) return $this->setting[$name];
		
		$q='SELECT * FROM `formetoo_cdb`.`m_settings` WHERE `m_settings_name`=\''.$name.'\' AND `m_settings_date_start` < NOW() ORDER BY `m_settings_date` DESC LIMIT 1;';
		if($res=$sql->query($q))
			return $all_fields?($this->settingsAll=$res[0]):($this->settings[$name]=$res[0]['m_settings_value']);
		return null;
	}
	public function setSetting($name,$value,$date_start=null){
		global $sql;
		
		$date_start=$date_start?$date_start:dt();
		$q='INSERT INTO `formetoo_cdb`.`m_settings` SET
			`m_settings_name`=\''.$name.'\',
			`m_settings_value`=\''.$value.'\',
			`m_settings_date`=\''.dt().'\',
			`m_settings_date_start`=\''.dtc($date_start).'\';';
		if($res=$sql->query($q))
			return true;
		return false;
	}
	
}


?>