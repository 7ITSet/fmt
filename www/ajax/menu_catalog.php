<?
define ('_DSITE',1);

require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/menu.php');

$menu=new menu;

global $e;
function createMenu() {
  global $menu;
  $menu->displayMegaMenuCatalog();
}
?>