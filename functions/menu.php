<?
class menu{
	public
		$current,
		$current_parents=array(),
		$nodes,
		$nodes_id,
		$nodes_parent;

	function __construct(){
		global $current,$sql,$user;

		//общее меню
		$this->current=$current['id'];
		$q='SELECT * FROM `formetoo_main`.`menu` WHERE `active`=1 ORDER BY `parent`,`order`;';
		$res=$sql->query($q);
		$this->nodes=$res;
		foreach($res as $item)
			$nodes_id[$item['id']]=$item;
		$this->nodes_id=$nodes_id;
		foreach($res as $item)
			$nodes_parent[$item['parent']][]=$item;
		$this->nodes_parent=$nodes_parent;
		if(isset($current['menu']))
			$this->parents($current['menu'],$this->current_parents);
	}

	public function parent($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если очередная категория является родительской для заданной
			if($t['id']==$this->nodes_id[$el]['parent']){
				//добавляем ее в массив
				$nodes[]=$t;
				break;
			}
	}

	public function parents($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если текущая категория является родительской для заданной
			if(isset($this->nodes_id[$el])&&$t['id']==$this->nodes_id[$el]['parent']){
				//добавляем ее в массив
				$nodes[$t['id']]=$t;
				//ищем родительскую категорию для найденной, если нужно найти все родительские
					$this->parents($t['id'],$nodes);
				break;
			}
	}

	public function child($el,&$nodes){
		foreach($this->nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['parent']==$el){
				//добавляем ее в массив
				$nodes[]=$t;
			}
	}

	public function isParent($el){
		foreach($this->nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if($t['parent']==$el)
				return true;
		return false;
	}

	public function childs($el,&$nodes,$tab=0,$level=0){
		foreach($this->nodes_id as $t)
			//если текущая категория является дочерней для заданной
			if ($t['parent']==$el){
				//добавляем ее в массив
				if($tab===1)
					for($i=0;$i<$level;$i++)
						$t['name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$t['name'];
				elseif($tab===2){
					$parents=array();
					$this->parents($t['id'],$parents);
					$parents=array_reverse($parents);
					$name='';
					foreach($parents as $parents_)
						$name.=$parents_['name'].'&nbsp;→&nbsp;';
					$t['name']=$name.$t['name'];
				}
				$nodes[]=$t;
				//ищем дочернюю категорию для найденной, если нужно найти все дочерние
					$this->childs($t['id'],$nodes,$tab,$level+1);
			}
		--$level;
	}

	//левое меню дочерних или соседних категорий
	public function displayLeftSubMenu(&$res=false,&$ids=false){
		global $current;
		$ch=array();
		//если есть дочерние категории, показывем их
		$this->childs($current['menu'],$ch);
		if($res===false)
			if($ch)
				$this->display('left',$current['menu']);
			//если нет - показывем соседей
			else{
				$this->display('left',array_shift($this->current_parents)['id']);
			}
		elseif(is_array($res))
			if($ch)
				$this->display('left',$current['menu'],true,false,'dynamic',false,$res,$ids);
			//если нет - показывем соседей
			else{
				$this->display('left',array_shift($this->current_parents)['id'],true,false,'dynamic',false,$res,$ids);
			}
	}

	//левое меню дочерних или соседних категорий
	public function displayLeftCat(&$res=false,&$ids=false){
		global $current;
		$ch=array();
		//если есть дочерние категории, показывем их
		$this->childs($current['menu'],$ch);
		if($res===false)
			if($ch)
				$this->display('top-catalog',$current['menu']);
			//если нет - показывем соседей
			else{
				$this->display('top-catalog',array_shift($this->current_parents)['id']);
			}
		elseif(is_array($res))
			if($ch)
				$this->display('top-catalog',$current['menu'],true,false,'dynamic',false,$res,$ids);
			//если нет - показывем соседей
			else{
				$this->display('top-catalog',array_shift($this->current_parents)['id'],true,false,'dynamic',false,$res,$ids);
			}
	}

	public function display($type='',$parent=0,$display_all=false,$aborder=false,$threedots=500,$recursion=false,&$result=false,&$result_ids=false,&$depth=3){
		global $menu,$current;
		//вывод пунктов меню
		if(isset($this->nodes_parent[$parent])&&$result===false){
			$depth=$recursion?$depth-1:$depth;
			if($depth>=0){
				echo '<ul'.($recursion?' class="nav_sublevel"':'').'>';
				//перебираем дочерние пункты текущего пункта
				foreach($this->nodes_parent[$parent] as $nodes_parent__){
					//если пункт меню первый - переходим к следующему (первая ссылка прописывается отдельно)
					//if($nodes_parent__['id']==1) continue;
					$nodes_parent__['type']=explode('|',$nodes_parent__['type']);
					if(in_array($type,$nodes_parent__['type'])===false) continue;
					//находим всех родителей текущего дочернего пункта и строим путь ссылки
					$parents=array();
					$this->parents($nodes_parent__['id'],$parents);
					$parents=array_reverse($parents);
					$url='';
					foreach($parents as $parents_)
						$url.=$parents_['url'].'/';
					//выводим пункт меню
					echo '<li'.($nodes_parent__['id']==$current['menu']?' class="current"':(in_array_assoc($this->current_parents,'id',$nodes_parent__['id'])?' class="active"':'')).' id="menu_id_'.$nodes_parent__['id'].'">',
							$nodes_parent__['id']!=$current['menu']?'<a href="'.(isset($nodes_parent__['no_link'])&&$nodes_parent__['no_link']?'#':('/'.$url.$nodes_parent__['url'].($nodes_parent__['url']?'/':''))).'" title="'.$nodes_parent__['name'].'">':'',
							$type=='top'?'<span class="menu-top-before-a"></span>':'',
							'<span class="menu-item-parent">',
							transform::some($nodes_parent__['name'],$threedots,1),
							'</span>',
							$aborder?($nodes_parent__['id']!=$current['menu']?'<span class="underline"></span>':''):'',
							$type=='top'?'<span class="menu-top-after-a"></span>':'',
							$nodes_parent__['id']!=$current['menu']?'</a>':'',
							//если у текущего дочернего пункта есть подпункты и он активен рекурсивно выводим их
							$display_all?$this->display($type,$nodes_parent__['id'],$display_all,$aborder,$threedots,true,$result,$result_ids,$depth):((isset($this->nodes_parent[$nodes_parent__['id']])&&($nodes_parent__['id']==$current['menu']||in_array_assoc($this->current_parents,'id',$nodes_parent__['id'])))?$this->display($type,$nodes_parent__['id'],$display_all,$aborder,$threedots,true,$result,$result_ids,$depth):''),
						'</li>';
				}

				echo '</ul>';
			}
			$depth++;
		}
		//вывод результата в массив
		elseif(is_array($result)&&isset($this->nodes_parent[$parent])){
			foreach($this->nodes_parent[$parent] as $nodes_parent__){
				//если пункт меню первый - переходим к следующему (первая ссылка прописывается отдельно)
				//if($nodes_parent__['id']==1) continue;
				$nodes_parent__['type']=explode('|',$nodes_parent__['type']);
				if(in_array($type,$nodes_parent__['type'])===false) continue;
				//находим всех родителей текущего дочернего пункта и строим путь ссылки
				$parents=array();
				$this->parents($nodes_parent__['id'],$parents);
				$parents=array_reverse($parents);
				$url='';
				foreach($parents as $parents_)
					$url.=$parents_['url'].'/';
				//сокращаем длинные названия, обрезая часть слов или всего названия
				if($threedots=='dynamic'){
					if(mb_strlen($nodes_parent__['name'],'utf-8')>=40)
						$threedots=40;
					else{
						$name=explode(' ',$nodes_parent__['name']);
						foreach($name as &$_word)
							if(mb_strlen($_word,'utf-8')>=23)
								$_word=transform::some($_word,20,1);
						$name=implode(' ',$name);
					}

				}
				//выводим пункт меню
				$child=array();
				$result_ids[]=$nodes_parent__['category'];
				$result[]=array(
					"current"=>$nodes_parent__['id']==$current['menu']?true:false,
					"active"=>in_array_assoc($this->current_parents,'id',$nodes_parent__['id'])?true:false,
					"id"=>$nodes_parent__['id'],
					"parent"=>$menu->nodes_id[$nodes_parent__['id']]['parent'],
					"category"=>$nodes_parent__['category'],
					"namefull"=>$nodes_parent__['name'],
					"name"=>(isset($name)?$name:transform::some($nodes_parent__['name'],$threedots,1)),
					"url"=>'/'.$url.$nodes_parent__['url'],
					"child"=>&$child
				);
					//если у текущего дочернего пункта есть подпункты и он активен рекурсивно выводим их
					$display_all
						?	$this->display($type,$nodes_parent__['id'],$display_all,$aborder,$threedots,true,$child,$result_ids)
						:	(
								(isset($this->nodes_parent[$nodes_parent__['id']])&&($nodes_parent__['id']==$current['menu']||in_array_assoc($this->current_parents,'id',$nodes_parent__['id'])))
								?	$this->display($type,$nodes_parent__['id'],$display_all,$aborder,$threedots,true,$child,$result_ids)
								:	null
							);
				unset($name);
				unset($child);
			}
		}
	}

	public function breadcrumbs($last_active=false){
		global $current;
		$this->parents($current['menu'],$parents);
		echo '<ul>';
		if(!$parents)
			;//echo '<li><span>'.$this->nodes_id[$this->current]['name'].'</span></li>';
		else{
			$parents_reverse=array_reverse($parents);
			$url='';
			foreach($parents_reverse as $p){
				$url.=$p['url'].'/';
				echo '<li><span><a href="/'.$url.'" class="underline">'.$p['name'].'</a></span></li>';
			}
			if(!$last_active)
				echo '<li><span>'.$this->nodes_id[$current['menu']]['name'].'</span></li>';
			else{
				echo '<li><span><a href="/'.$url.$this->nodes_id[$current['menu']]['url'].'/" class="underline">'.$this->nodes_id[$current['menu']]['name'].'</a></span></li>';
			}
		}
		echo '</ul>';
	}

}
?>
