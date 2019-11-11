<?
define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/user.php');
$sql=new sql();
$user=new user(false);
if(!$user->getInfo('m_users_id')){
	unset($user);
	unset($sql);
	exit;
};

function tag_b($text,$b,$bl){
	foreach($b as &$_b)
		$_b='/('.preg_quote($_b,'/').')/iu';
	foreach($bl as &$_bl)
		$_bl='/('.preg_quote($_bl,'/').')/iu';	
	$text=preg_replace($b,'<b>$1</b>',$text);
	$text=preg_replace($bl,'<b>$1</b>',$text);
	return $text;
}
/* function tag_b($text,$b){
	@$text=is_array($b)?preg_replace('/(\w+)?('.implode('|',$b).')(\w+)?/ui','<b>$1$2$3</b>',$text):preg_replace('/(\w+)?('.$b.')(\w+)?/ui','<b>$1$2$3</b>',$text);
	return $text;
} */
	
$w=trim(get('w',array('\\','%','_','\'','*','/','[',']'),array('\\\\','\%','\_','\\\'','','','','')));
$w=preg_replace('/( )+/',' ',$w);
if(mb_strpos($w,'-')==4&&mb_strlen($w)==11)
	$w=str_replace('-','',$w);
if(mb_strlen($w,'utf-8')<3) exit;
if (stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))//другая кодировка для IE
	$w=trim(w_only(iconv('cp1251','utf-8',$w)));
	
if ($w){
	$wl=transform::layout($w);
	$w=explode(' ',$w);
	$wl=explode(' ',$wl);
	$q='SELECT * FROM `formetoo_main`.`m_products` WHERE `m_products_id` LIKE \'%'.implode(' ',$w).'%\' OR `m_products_name_full` LIKE \'%'.implode(' ',$w).'%\' OR `m_products_name_full` LIKE \'%'.implode(' ',$wl).'%\' ORDER BY `m_products_name_full` LIMIT 1000;';
	if($res=$sql->query($q)){
		$i=0;
		echo '<p class="result-title">Товары</p>';
		foreach($res as $record){
			if($i<=10)
				echo '<a data-id="',
					$record['m_products_id'],
					'" rel="',
					$record['m_products_name_full'],
					'" href="/product/',
					$record['m_products_id'],
					// '/" data-category="',
					// $record['m_products_categories_id'],
					'"><span class="grey">[',
					tag_b($record['m_products_id'],$w,$wl),
					']</span>&nbsp;',
					tag_b($record['m_products_name_full'],$w,$wl),
				'</a>';
			$i++;
		}
	}
	else{
		$like=[];
		foreach($w as $_w){
			if (mb_strlen($_w,'utf-8')<3) continue;
			$like[]='`m_products_name_full` LIKE \'%'.$_w.'%\'';
		}
		foreach($wl as $_wl){
			if (mb_strlen($_wl,'utf-8')<3) continue;
			$like[]='`m_products_name_full` LIKE \'%'.$_wl.'%\'';
		}
		$like=implode(' OR ',$like);
		$q='SELECT * FROM `formetoo_main`.`m_products` WHERE '.$like.' ORDER BY `m_products_name_full` LIMIT 1000;';
		if($res=$sql->query($q)){
			foreach($res as &$record)
				$record['m_products_name_full']=tag_b($record['m_products_name_full'],$w,$wl);
			function cmp($a,$b){
				$c_a=mb_substr_count($a['m_products_name_full'],'<b>','utf-8');
				$c_b=mb_substr_count($b['m_products_name_full'],'<b>','utf-8');
				if($c_a==$c_b)
					return 0;
				return ($c_a>$c_b)?-1:1;
			}
			uasort($res,'cmp');
			$i=0;
			echo '<p class="result-title">Товары</p>';
			foreach($res as $_record){
				if($i<=10)
					echo '<a data-id="',
						$_record['m_products_id'],
						'" rel="',
						$_record['m_products_name_full'],
						'" href="/product/',
						$_record['m_products_id'],
						// '/"  data-category="',
						// $_record['m_products_categories_id'],
						'"><span class="grey">[',
						tag_b($_record['m_products_id'],$w,$wl),
						']</span>&nbsp;',
						$_record['m_products_name_full'],
						'</a>';
				$i++;
			}
		}
	}
}
unset($sql);
?>