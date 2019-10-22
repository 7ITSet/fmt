<?
defined ('_DSITE') or die ('Access denied');
global $sql;
header('HTTP/1.1 403 Forbidden');
header('Status: 403 Forbidden');

function checkAntibot(){
	global $sql,$e;
	
	$data['captcha']=array(1,5,7);
	array_walk($data,'check');
	
	if(!isset($_SESSION['code'])||$data['captcha']!=$_SESSION['code'])
		$e[]='Код с капчи введён неправильно';
	$_SESSION['code']=rand(10000,999999);
	
	if(!$e){	
		$q='INSERT INTO `formetoo_cdb`.`m_info_ip_blacklist` SET 
			`m_info_ip_blacklis_ipv4`=\''.user_info::ip().'\';';
		if($res=$sql->query($q)){
			header('Location: '.$_SERVER['HTTP_REFERER']);
		}
	}
	else elogs(__FILE__,__FUNCTION__,$data);
}
if(post('handler')=='antibot')
	checkAntibot();
?>
<!DOCTYPE html>
<html id="html404">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="/css/style.css" />
	<style>
		* { 
			font-size:100%;
			margin:0px;
			padding:0px;
			outline:none;
			border:none;
			background:none;}
		html,body{ 
			height:100%;
			width:100%;}
		body{
			background:#2d424b;}
		#main{
			position:absolute;
			top:30%;
			left:30%;
			margin-left:-275px;
			overflow:hidden;
			margin:0 auto;}
		a,a:hover,a:visited{
			text-decoration:none;
			color:#fff;}
		#logo{
			display:block;
			width:267px;
			height:120px;
			background:url(/img/logo_503.png) 0 0 no-repeat;}
		p{
			font-family:'Lucida Console','Consolas','Courier New','Monaco';
			font-size:1.375em;
			line-height:1.5em;
			padding-left:10px;
			margin-bottom:.5em;
			color:#fff;
			position:relative;}
		p.header{
			font-size:3em;}
		p input{
			border:none;
			cursor:none;
			width:30px;
			padding-left:3px;
			height:1.375em;
			caret-color: transparent;
			color:#fff;
			float:right;}
		#caret{
			display:inline-block;
			float:right;
			background:#fff;
			width:0.15625em;
			height:1.375em;}
		#tel{
			font-family:'Lucida Console','Consolas','Courier New','Monaco';
			font-size:2em;
			color:#fff;
			padding-left:10px;
			margin-top:50px;}
		form img{
			padding:0!important;}
		form input{
			caret-color: #fff;}
	</style>
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('p input').focus();
			var t=setInterval(function(){
				if($('#caret:visible').length)
					$('#caret').hide();
				else $('#caret').show();
			},600);
		});
	</script>
	
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#b91d47">
	<meta name="theme-color" content="#ffffff">

	<title>403 — Доступ запрещён</title>
</head>
<body>
	<div id="main">
		<a id="logo" href="/"></a>
		<p class="header">403.</p>
		<p>Ваш IP адрес не прошёл антиспам-проверку.<br/>Пожалуйста, введите код, чтобы продолжить.</p>
		<p><br/></p>
		<form action="/" method="post">
			<div class="login_authorization_form_input_container">
				<input type="text" name="captcha" placeholder="текст с картинки *"/>
			</div>
			<div class="login_authorization_form_sep">
				<span class="icon icon-arrow-left"></span>
			</div>
			<div class="login_authorization_form_input_container">
				<img src="/ajax/captcha/captcha.php?<?rand(1,10000);?>" class="captcha_img" title="Нажмите, чтобы сменить изображение"/>
			</div>
			<input type="hidden" name="handler" value="antibot"/>
		</form>
		<div class="clr"></div>
		<div id="tel"><a href="tel:+78005503599">8 800 550-35-99</a></div>
	</div>
</body>
</html>