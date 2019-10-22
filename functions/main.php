<?
defined ('_DSITE') or die ('Access denied');

//системные функции
require_once('system.php');
//подключаемся к основной БД
require_once('ccdb.php');
$sql=new sql;

//навигация по страницам
require_once('navigator.php');

//пользователи
require_once('user.php');
$user=new user;

//заказы, корзина
require_once('order.php');
$order=new order;

//email и смс сообщения
require_once('message.php');

//обработка форм
require_once('handler.php');

//меню
require_once('menu.php');
$menu=new menu;

//функции для генерирования тела страницы
require_once('content.php');
$content=new content;
?>