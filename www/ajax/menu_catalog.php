<?
define ('_DSITE',1);

require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/menu.php');

$sql=new sql;
$menu=new menu;

global $e;
function createMenu() {
    global $menu;
    return $menu->display('top-catalog',0,true);
}
?>