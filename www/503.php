<?
defined ('_DSITE') or die ('Access denied');

global $settings_503;

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header("Retry-After: ".dtu(dtc($settings_503->dateStart,'+'.$settings_503->dateLength),'r'));
?>
<!DOCTYPE html>
503