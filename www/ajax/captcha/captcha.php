<?
session_name('ut');
session_start(['cookie_httponly'=>true,'cookie_secure'=>true]);

function get_string(){
	$length=mt_rand(5,7);
	$chars='abcdefghkmnpqrstuvwxyz123456789';
	$chars='1234567890';
	$numChars=strlen($chars);
	$string='';
	for($i=0;$i<$length;$i++)
		$string.=substr($chars,mt_rand(1,$numChars)-1,1);
	return $string;
}
$id=$_SESSION['code']=get_string();
$length=strlen($id);

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');                   
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', 10000) . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');         
header('Cache-Control: post-check=0, pre-check=0', false);           
header('Pragma: no-cache');                                           
header('Content-Type:image/gif');

//шрифты
$fonts=array();
if ($objs=glob(__DIR__.'/captcha_fonts/*')) 
	foreach($objs as $obj)
		is_dir($obj)?1:$fonts[]=$obj;

//создаем изображение
$c_w=190;
$c_h=50;
$img=imagecreatetruecolor($c_w,$c_h); 
imagefill($img,0,0,imagecolorallocate($img,255,255,255));
$lines=imagecreatefrompng('captcha_img_lines.png');
$logo=imagecreatefrompng('captcha_img_logo.png');

$fonts_count=sizeof($fonts);
//накладываем код
for ($i=0;$i<$length;$i++)
	imagettftext($img,34,mt_rand(-10,10+$i),$i*20+($i+4)*2,40,imagecolorallocate($img,0,0,0),$fonts[mt_rand(0,$fonts_count-1)],$id[$i]);

//накладываем линии
$lines_x=mt_rand(0,339);
$lines_y=mt_rand(0,232);
imagecopy($img,$lines,0,0,$lines_x,$lines_y,$c_w,$c_h);

//искажаем изображение
$width=$c_w;
$height=$c_h;
$img2=imagecreatetruecolor($width,$height);
$rand1=$rand2=$rand3=$rand4=0.11;
$rand9=$rand10=2.0;
 
for($x=0;$x<$width;$x++){
	for($y=0;$y<$height;$y++){
		// координаты пикселя-первообраза.
		$sx=$x+(sin($x*$rand1)+sin($y*$rand3))*$rand9;
		$sy=$y+(sin($x*$rand2)+sin($y*$rand4))*$rand10;
	 
		// первообраз за пределами изображения
		if($sx<0||$sy<0||$sx>=$width-1||$sy>=$height-1){ 
			$color=255;
			$color_x=255;
			$color_y=255;
			$color_xy=255;
		}
		else{
		// цвета основного пикселя и его 3-х соседей для лучшего антиалиасинга
			$color=(imagecolorat($img,$sx,$sy)>>16)&0xFF;
			$color_x=(imagecolorat($img,$sx+1,$sy)>>16)&0xFF;
			$color_y=(imagecolorat($img,$sx,$sy+1)>>16)&0xFF;
			$color_xy=(imagecolorat($img,$sx+1,$sy+1)>>16)&0xFF;
		}
		// сглаживаем только точки, цвета соседей которых отличается
		if($color==$color_x&&$color==$color_y&&$color==$color_xy){
			$newcolor=$color;
		}
		else{
			$frsx=$sx-floor($sx); //отклонение координат первообраза от целого
			$frsy=$sy-floor($sy);
			$frsx1=1-$frsx;
			$frsy1=1-$frsy;
		  // вычисление цвета нового пикселя как пропорции от цвета основного пикселя и его соседей
			$newcolor=floor($color*$frsx1*$frsy1+$color_x*$frsx*$frsy1+$color_y*$frsx1*$frsy+$color_xy*$frsx*$frsy);
		}
		imagesetpixel($img2,$x,$y,imagecolorallocate($img2,$newcolor,$newcolor,$newcolor));
	}
}

//накладываем логотип
imagecopy($img2,$logo,140,0,0,0,51,17);
ImageGIF($img2);
ImageDestroy($img);
ImageDestroy($img2);
?>