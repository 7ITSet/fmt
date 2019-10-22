<?
define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/menu.php');
require_once(__DIR__.'/../../functions/content.php');
require_once(__DIR__.'/../../functions/user.php');

$sql=new sql;
$user=new user(false);
if(!$user->getInfo('m_users_id')){
	unset($user);
	unset($sql);
	echo '<div class="main_products_list_items_item_count" data-count="0"></div><p class="main_products_list_null">Фильтрация отключена.</p>';
	exit;
};
$menu=new menu;
$settings=new settings;
$_SERVER['G_VARS']['SERV_ST']=$settings->getSetting('server_st_formetoo');
$content=new content;
global $e;

$data['FILTER[]']=array();
$data['current']=array(1,null,null,null,1);
$data['p']=array(null,1,500,null,1);
$data['sort']=array();
$data['limit']=array(1,null,null,null,1);
$data['view']=array();
array_walk($data,'check',true);

if(!$e){
	$data['ajax']=true;
	$content->filterGoods($data);
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
}

unset($sql);
?>