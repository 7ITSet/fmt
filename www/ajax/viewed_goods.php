<?
define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/menu.php');
require_once(__DIR__.'/../../functions/content.php');

$sql=new sql;
$menu=new menu;
$settings=new settings;
$_SERVER['G_VARS']['SERV_ST']=$settings->getSetting('server_st_formetoo');
$content=new content;
global $e;


$data['goods']=array(1,null,5000);
array_walk($data,'check');

if(!$e){
	echo $content->getGoods($data['goods'],'table',100);
}
else{
	echo 'ERROR';
}

unset($sql);
?>