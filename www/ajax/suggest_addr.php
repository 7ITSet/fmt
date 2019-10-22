<?
define ('_DSITE',1);
global $e;

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
$sql=new sql;
require_once(__DIR__.'/../../functions/user.php');
$user=new user(false);

$data['addr']=array(1,10,1500);
array_walk($data,'check',true);

function tag_b($text,$b){
	/* foreach($b as &$_b)
		$_b='/('.preg_quote($_b,'/').')/iu';
	$text=preg_replace($b,'<b>$1</b>',$text); */
	return $text;
}

if(!$e){
	$res=$user->getAddressInfo($data['addr']);
	$data['addr']=explode(' ',$data['addr']);
	if($res){
		$response=null;
		try{
			$response=json_decode($res);
		}
		catch(Exception $e){
			echo 'E_ERROR';
			exit;
		}
		if($response){
			echo '<div class="suggest-container">';
			$response=$response->suggestions;
			foreach($response as $item)
				echo '<a href="#" rel="',
					$item->unrestricted_value,
					'" data-response="',
					htmlspecialchars(json_encode($item,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)),
					'">',
					tag_b($item->unrestricted_value,$data['addr']),
					'</a>';
			echo '</div>';
		}
	}
	else echo 'NULL_RESULT_ERROR';
}
else{
	echo 'INPUT_DATA_ERROR';
}

unset($sql);
unset($user);
?>