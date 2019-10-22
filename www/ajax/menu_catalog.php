<?
define ('_DSITE',1);

require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/menu.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/content.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/user.php');

$sql=new sql;
$menu=new menu;
$content=new content;
$user=new user(false);
// if(!$user->getInfo('m_users_id')){
// 	unset($user);
// 	unset($sql);
// 	unset($menu);
// 	unset($content);
// 	exit;
// };
global $e;

return $menu->display('top-catalog',0,true);

unset($sql);
?>