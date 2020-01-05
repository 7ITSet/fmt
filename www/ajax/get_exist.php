<?
define ('_DSITE',1);

require_once(__DIR__.'/../../functions/system.php');
require_once(__DIR__.'/../../functions/ccdb.php');
require_once(__DIR__.'/../../functions/user.php');

$sql=new sql;
$user=new user(false);
if(!$user->getInfo('m_users_id')){
	unset($user);
	unset($sql);
	echo 0;
	exit;
};
$sql_islx=new sql(2);

global $e;

$data['id']=array(1,null,null,10,1);
array_walk($data,'check',true);

//echo 0;exit;

if(!$e){

	$q='SELECT `id_isolux` FROM `formetoo_main`.`m_products` WHERE `id`='.$data['id'].' LIMIT 1;';
	if($res=$sql->query($q)){

		$q='SELECT `url` FROM `p-islx`.`ci_goods` WHERE `id`=\'СН'.$res[0]['id_isolux'].'\' LIMIT 1;';
		if($res=$sql_islx->query($q))
			if($html=file_get_contents($res[0]['url'])){
				libxml_use_internal_errors(TRUE);
				$node=new DOMDocument('1.0', 'utf-8');
				if($node->loadHTML($html)){
					$xp=new DomXPath($node);
					$attrs=$xp->query('//input[@class="change-qty__value"]/@data-content');
					foreach ($attrs as $_attr) {
						$result=json_decode($_attr->value);
						break;
					}

					if(isset($result->inStockCount))

                    echo transform::stock_o($result->inStockCount);
					else{
						echo 0;
					}
				}
			}
	}	
}
else{
	elogs(__FILE__,__FUNCTION__,$data);
}

unset($sql);
unset($sql_islx);
?>