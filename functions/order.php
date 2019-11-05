<?
defined ('_DSITE') or die ('Access denied');

class order{
	private $orders,
			$cart,
			$carts,
			$deferredCart;
	
	function __construct(){
		global $sql,$e,$G,$user;
		
		if($user->getInfo('m_users_id')){
			$q='SELECT * FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$user->getInfo('m_users_id').' ORDER BY `m_cart_date` DESC;';
			//если корзина уже создана
			if($res=$sql->query($q)){
				$this->cart=$res[0];
				if(sizeof($res)>1)
					$this->carts=$res;
				else
					$this->carts=null;
			}
		}
	}
	
	public function create(){
		global $sql,$e,$G,$user;
		
		$data['tel[]']=array(null,null,null,16,2);
		$data['tel_comment[]']=array(null,null,50);
		$data['customer']=array(1,null,null,10,1);
		$data['delivery']=array(1,null,null,1,1);
		$data['address']=array(1,null,8,null,1);
		$data['orderinfo']=array(null,null,1000);
		$data['pay_method']=array(1,null,null,1,1);
		$data['comment']=array(null,null,500);
		$data['quick']=array(null,null,null,1,1);
		array_walk($data,'check');
		
		if(!$e){
			$uact=$user->getUserActions();
			if(isset($uact[13])&&sizeof($uact[13])>50){
				$e[]='Превышен лимит отправки заказов';
			}
			else $user->setUserActions(13,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}
		if(!$e){
			$ca=$user->getUserContragents(false);			
			if(!isset($ca['contragents'][$data['customer']]))
				$e[]='Указанный контрагент не принадлежит пользователю';
		}
		
		if(!$e){
			
			$data['id']=get_id('m_orders');
			$nds=$ca['contragents'][$data['customer']][0]['m_contragents_c_nds'];
			//адреса доставки
			if(isset($ca['addresses'][$data['customer']]))
				foreach($ca['addresses'][$data['customer']] as $_addr)
					if($_addr['m_address_id']==$data['address'])
						$data['address']=json_encode($_addr,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			//контакты клиента
			$tel_count=is_array($data['tel[]'])?sizeof($data['tel[]']):0;
			if($tel_count){
				$q_tel=array();
				for($i=0;$i<$tel_count;$i++)
					if($data['tel[]'][$i])
						$q_tel[]=array('tel_numb'=>$data['tel[]'][$i],'tel_comment'=>$data['tel_comment[]'][$i]);
				$q_tel=json_encode($q_tel,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			}
			//при быстром заказе добавляем в заказ указанный при быстрой регистрации телефон
			if($data['quick']){
				$q_tel=array();
				$q_tel[]=array('tel_numb'=>$user->getInfo('m_users_tel'),'tel_comment'=>'');
				$q_tel=json_encode($q_tel,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			}
			
			$q='INSERT INTO `formetoo_cdb`.`m_orders` SET 
				`m_orders_id`='.$data['id'].',
				`m_orders_name`=\'Интернет-заказ № '.$data['id'].'\',
				`m_orders_performer`='.($nds!=-1&&$data['pay_method']==2?3363726835:3363726835).',
				`m_orders_customer`='.$data['customer'].',
				`m_orders_nds`='.$nds.',
				`m_orders_date`=\''.dt().'\',
				`m_orders_comment`=\''.$data['comment'].'\',
				`m_orders_delivery_type`=\''.$data['delivery'].'\',
				`m_orders_address`=\''.$data['address'].'\',
				`m_orders_pay_method`=\''.$data['pay_method'].'\',
				`m_orders_contacts`=\''.$q_tel.'\';';
			if($sql->query($q)){
				require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/img_codes.php');
				require_once($_SERVER['DOCUMENT_ROOT'].'/../functions/foto.php');
				
				//добавляем счёт на оплату для заказа
				$data['m_documents_id']=get_id('m_documents');
				$data['m_documents_performer']=($nds!=-1&&$data['pay_method']==2?3363726835:3363726835);
				$data['m_documents_customer']=$data['customer'];
				$data['m_documents_order']=$data['id'];
				$data['m_documents_templates_id']=2363374033;
				$data['m_documents_date']=dt();
				$data['m_documents_signature']=1;
				$data['m_documents_nds_itog']=0;
				$data['m_documents_pdf_none']=0;
				$data['m_documents_bar']=1;
				$data['m_documents_comment']=$data['comment'];
				$data['m_invoice_date_expire']=dtu(dtc('','+ 3 weekdays'),'d.m.Y');				
				
				$data['m_invoice_attention']=transform::typography('');
				
				$data['m_invoice_terms']='';
				
				$data['m_documents_numb']=$data['m_documents_id'];	
	
				/* $_address=$info->getAddress($data['m_documents_performer']);
				$_address=change_key($_address,'m_address_type',true); */
				$doc_header_org_address='г. Москва';

				//папка
				$foldername=md5(time().$data['m_documents_id']);
				mkdir(__DIR__.'/../www/files/invoice/'.$foldername);
				codes::getBAR(__DIR__.'/../www/files/invoice/'.$foldername.'/bar.png',$data['m_documents_id'].'00',false,20);
				
				$items=array();
				
				$cart_items=$this->getCart();
				
				$nds18=0;
				
				$items[1234567]['room']['name']='Раздел';
				$items[1234567]['services']=array();
				foreach($cart_items->items as $_item){
					$add_item=array();
					$add_item['id']=$_item->product_id;
					$add_item['count']=$_item->product_count;
					$add_item['manual_changed']=1;
					$add_item['price']=$_item->product_price;
					$add_item['sum']=$_item->product_count*$_item->product_price;
					$add_item['table']='products';
					$items[1234567]['services'][]=$add_item;
					$nds18+=$_item->product_count*($_item->product_price*.20/1.20);
				}
				
				$sum=$cart_items->sum;
				
				$items=json_encode($items,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
				
				$data['params']='{
						"org":"'.$data['m_documents_performer'].'",
						"client":"'.$data['m_documents_customer'].'",
						"order":"'.$data['m_documents_order'].'",
						"orderinfo":"'.$data['orderinfo'].'",
						"doc_template":"'.$data['m_documents_templates_id'].'",
						"doc_date":"'.$data['m_documents_date'].'",
						"doc_numb":"'.$data['m_documents_numb'].'",
						"doc_bar":"'.$data['m_documents_bar'].'",
						"doc_sum":"'.number_format($sum,2,'.','').'",
						"doc_date_expire":"'.$data['m_invoice_date_expire'].'",
						"doc_attention":"'.$data['m_invoice_attention'].'",
						"doc_terms":'.json_encode($data['m_invoice_terms'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).',
						"doc_nds18":"'.($nds!=-1?$nds18:0).'",
						"doc_signature":"'.$data['m_documents_signature'].'",
						"doc_logo":1,
						"doc_header_org_name":"formetoo",
						"doc_header_org_address":"'.$doc_header_org_address.'",
						"doc_header_org_tel":"+7 123 456-78-90",
						"doc_header_org_email":"info@formetoo.ru",
						"items":'.$items.'
					}';
				
				$q='INSERT INTO `formetoo_cdb`.`m_documents` SET 
					`m_documents_id`='.$data['m_documents_id'].',
					`m_documents_performer`='.$data['m_documents_performer'].',
					`m_documents_customer`='.$data['m_documents_customer'].',
					`m_documents_order`='.$data['m_documents_order'].',
					`m_documents_templates_id`='.$data['m_documents_templates_id'].',
					`m_documents_numb`=\''.$data['m_documents_numb'].'\',
					`m_documents_date`=\''.$data['m_documents_date'].'\',
					`m_documents_signature`='.$data['m_documents_signature'].',
					`m_documents_bar`='.$data['m_documents_bar'].',
					`m_documents_params`=\''.$data['params'].'\',
					`m_documents_update`=\''.dt().'\',
					`m_documents_comment`=\''.$data['m_documents_comment'].'\',
					`m_documents_pdf_none`='.$data['m_documents_pdf_none'].',
					`m_documents_nds_itog`='.$data['m_documents_nds_itog'].',
					`m_documents_filesize`=0,
					`m_documents_folder`=\''.$foldername.'\';';
				if($sql->query($q)){
						//отправляем письмо с заказом
						if(1||!$data['quick']){
							$user->sendNewOrderEmail($data['m_documents_id']);
							$user->sendNewOrderSMS($data['m_documents_id']);
						}
						//очищаем корзину
						$this->deleteAllCarts();
						if(!$data['quick'])
							header('Location: /my/orders/?new=success&id='.$data['id']);
						else return $data['id'];
				}
				else{
					if(!$data['quick'])
						header('Location: /my/orders/?new=error');
					else return 'ERROR';	
				}	
			}
			else{ 
				if(!$data['quick'])
					header('Location: /my/orders/?new=error');
				else return 'ERROR';
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			if(!$data['quick'])
				header('Location: /my/orders/?new=error');
			else return 'ERROR';
		}
		exit;
	}
	
	public function changeCartHolder($new=null){
		global $sql,$e,$user;
		
		$q='SELECT `m_cart_id` FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$new.';';
		$res=$sql->query($q);
		if(sizeof($res)>1)
			$sql->query('UPDATE `formetoo_main`.`m_cart` SET `m_cart_active`=0 WHERE `m_cart_user_id`='.$new.';');
		if($new&&$sql->query('UPDATE `formetoo_main`.`m_cart` SET `m_cart_user_id`='.$new.' WHERE `m_cart_user_id`='.$user->getInfo().' LIMIT 1;')) return true;
		return null;
	}
	public function mergeCarts(){
		global $sql,$e,$user;
		
		$q='SELECT * FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$user->getInfo().' ORDER BY `m_cart_data`;';
		if($res=$sql->query($q)){
			if(sizeof($res)>1){
				$mergeCartData=array();
				foreach($res as $_cart){
					$items=json_decode($_cart['m_cart_data'])->items;
					//группируем массив продуктов найденой корзины по id продукта
					$items_id=array();
					foreach($items as $_items)
						$items_id[$_items->product_id]=$_items;
					//ищем в новой корзине продукты из старой и суммируем их количество	
					foreach($mergeCartData as &$_mergeCartData)
						if(isset($items_id[$_mergeCartData->product_id])){
							$_mergeCartData->product_count+=$items_id[$_mergeCartData->product_id]->product_count;
							unset($items_id[$_mergeCartData->product_id]);
						}
					$items=array_values($items_id);
					$mergeCartData=$mergeCartData?array_merge($mergeCartData,$items):$items;
				}
				$_cart['m_cart_data']=json_decode($_cart['m_cart_data']);
				$_cart['m_cart_data']->items=$mergeCartData;
				$sum=0;
				foreach($_cart['m_cart_data']->items as $_item){
					$sum+=$_item->product_price*$_item->product_count;
				}
				$_cart['m_cart_data']->sum=$sum;
				$q='UPDATE `formetoo_main`.`m_cart` SET 
					`m_cart_active`=1,
					`m_cart_data`=\''.json_encode($_cart['m_cart_data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\' 
					WHERE `m_cart_id`='.$_cart['m_cart_id'].' LIMIT 1;';
				if($sql->query($q)){
					$q='DELETE FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$user->getInfo().' AND `m_cart_id`!='.$_cart['m_cart_id'].';';
					$sql->query($q);
					return true;
				}
				return false;
			}
			return false;
		}
		return false;
	}
	public function deleteOldCarts(){
		global $sql,$e,$user;

		$sql->query('DELETE FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$user->getInfo().' AND `m_cart_id`!='.$this->getCarts()[0]['m_cart_id'].';');
		return null;
	}
	
	public function deleteAllCarts(){
		global $sql,$e,$user;

		$sql->query('DELETE FROM `formetoo_main`.`m_cart` WHERE `m_cart_user_id`='.$user->getInfo().';');
		return null;
	}
	
	public function getCarts(){
		if($this->carts)
			return $this->carts;
		return null;
	}
	public function getCart($json=false){
		if($this->cart)
			if($json) return $this->cart['m_cart_data'];
			else return json_decode($this->cart['m_cart_data']);
				
		return null;
	}
	public function getCartSize(){
		if($this->cart){
			$cart=json_decode($this->cart['m_cart_data']);
			return sizeof($cart->items);
		}
		return null;
	}
	public function getCartSum(){
		if($this->cart){
			$cart=json_decode($this->cart['m_cart_data']);
			return $cart->sum;
		}
		return null;
	}
	
	private function cart_new($data){
		global $sql,$e,$G,$user;
	
		if($data=$this->cart_data_item($data)){
			$cart_data['sum']=$data['product_price']*$data['product_count'];
			$cart_data['items'][0]=$data;
			$cart_data=json_encode($cart_data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			$q='INSERT INTO `formetoo_main`.`m_cart` SET 
				`m_cart_id`='.get_id('m_cart').',
				`m_cart_user_id`='.$user->getInfo('m_users_id').',
				`m_cart_date`=\''.dt().'\',
				`m_cart_update`=\''.dt().'\',
				`m_cart_data`=\''.$cart_data.'\';';
			if($user->getInfo('m_users_id')&&$sql->query($q))
				return $cart_data;
			else return 'ERROR_SAVE_NEW_CART';
		}
		else return 'ERROR_LOAD_PRODUCT_DATA_NEW_CART';
	}
	
	//получение данных о товаре для добавления нового товара в корзину
	private function cart_data_item($data){
		global $sql,$e,$G,$user;
		
		//курс валют
		$ec_res=$sql->query('SELECT * FROM `formetoo_cdb`.`m_info_settings`;');
		$ec[1]=1;
		$ec[2]=$ec_res[0]['m_info_settings_exchange_usd'];
		$ec[3]=$ec_res[0]['m_info_settings_exchange_eur'];
		
		//находим цену и параметры товара
		$q='SELECT `m_products_id`,`slug`,`m_products_price_general`,`m_products_price_bonus`,`m_products_price_currency`,`m_products_multiplicity` FROM `formetoo_main`.`m_products` WHERE 
			`m_products_id`='.$data['product_id'].' LIMIT 1;';
		if($prod=$sql->query($q)){
			$prod=$prod[0];
			
			$price=round($prod['m_products_price_general']*$ec[$prod['m_products_price_currency']],2);
			$bonus=$prod['m_products_price_general']*$ec[$prod['m_products_price_currency']]*$prod['m_products_price_bonus']*.01;
			$volume=round($prod['m_products_multiplicity'],4);
			//проверка на кратность товара и минимально возможное количество
			$volume=$volume?$volume:1;
			if(fmod($data['product_count'],$volume)!=0)
				$data['product_count']=ceil($data['product_count']/$volume)*$volume;
			$data['product_count']=$data['product_count']>$volume?$data['product_count']*1:$volume;
			$data['product_price']=$price;
			$data['product_price_bonus']=$data['product_count']*$bonus;
			$data['product_volume']=$volume;
			
			return $data;
		}
		else return false;
	}
	
	public function cart_update_item($data){
		global $sql,$e,$G,$user;
		
		if($this->cart){
			//состав корзины
			$cart_data=json_decode($this->cart['m_cart_data']);
			$item=null;
			foreach($cart_data->items as &$_items)
				if($_items->product_id==$data['product_id'])
					$item=$_items;
			//если добаляемый товар уже есть в корзине
			if($item){
				//удаление позиции из корзины
				$delete=$data['product_count']==-1?true:false;
				//проверка на кратность товара и минимально возможное количество
				$item->product_volume=$item->product_volume?$item->product_volume:1;
				if(round(fmod($data['product_count'],$item->product_volume),5)!=0)
					$data['product_count']=ceil($data['product_count']/$item->product_volume)*$item->product_volume;
				$data['product_count']=$data['product_count']>$item->product_volume?$data['product_count']*1:$item->product_volume;
				//текущая сумма по позиции до обновления
				$sum_old=$data['update']?($item->product_price*$item->product_count):0;
				$sum_old=$delete?-$sum_old:$sum_old;
				//если есть метка обновления кол-ва в корзине, сохраняем новое значение кол-ва, иначе суммируем прежнее и новое значение
				$item->product_count=$data['update']?$data['product_count']:$item->product_count+$data['product_count'];
				//сумма по добавляемому товару
				$sum=$item->product_price*$data['product_count'];
			}
			//если нет - добавляем товар
			elseif(!$data['update'])
				if($data=$this->cart_data_item($data)){
					$sum=$data['product_price']*$data['product_count'];
					$cart_data->items[]=$data;
				}
				else{
					return 'ERROR_LOAD_PRODUCT_DATA_EXIST_CART';
				}
			else return 'ERROR_UPDATE_UNDEFINED_DATA';
			if(!$delete)
				$cart_data->sum+=$sum-$sum_old;
			else{
				//удаляем позицию и обновляем цену
				$cart_data->sum+=$sum_old;
				//временный массив, в который попадут только неудалённые позиции (баг с удалением unset в foreach со ссылкой)
				$a=array();
				foreach($cart_data->items as &$_items)
					if($_items->product_id!=$data['product_id'])
						$a[]=$_items;
				$cart_data->items=$a;
			}
			$cart_data=json_encode($cart_data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			$q='UPDATE `formetoo_main`.`m_cart` SET 
				`m_cart_data`=\''.$cart_data.'\',
				`m_cart_update`=\''.dt().'\' 
				WHERE `m_cart_user_id`='.$user->getInfo('m_users_id').' AND `m_cart_active`=1 LIMIT 1;';
			if($sql->query($q))
				return $cart_data;
			else return 'ERROR_UPDATE_CART';
		}
		//если корзина не создавалась
		else{
			return $this->cart_new($data);
		}
	}
	
	public function checkPromocode($data){
		global $sql,$e,$G,$user;
	
		$q='SELECT * FROM `formetoo_main`.`m_users_actions` WHERE 
			`m_users_actions_user_id`='.$user->getInfo('m_users_id').' AND 
			`m_users_actions_type`=6 AND 
			`m_users_actions_date`>\''.dtc(dt(),'-1 day').'\';';
		if(!($res=$sql->query($q))||sizeof($res=$sql->query($q))<20){
			$sql->query('INSERT INTO `formetoo_main`.`m_users_actions` SET 
				`m_users_actions_user_id`='.$user->getInfo('m_users_id').',
				`m_users_actions_date`=\''.dt().'\',
				`m_users_actions_type`=6,
				`m_users_actions_data`=\''.$data['code'].'\';');
			$q='SELECT `m_cart_promocodes_id`,`m_cart_promocodes_date_start`,`m_cart_promocodes_date_end` FROM `formetoo_main`.`m_cart_promocodes` WHERE 
				`m_cart_promocodes_code`=\''.$data['code'].'\' LIMIT 1;';
			if($res=$sql->query($q)){
				if($res[0]['m_cart_promocodes_date_start']>dt()||$res[0]['m_cart_promocodes_date_end']<dt())
					return 'ERROR_PROMOCODE_EXPIRED';
				else{
					$q='UPDATE `formetoo_main`.`m_cart` SET 
						`m_cart_promocode_id`=\''.$res[0]['m_cart_promocodes_id'].'\' 
						WHERE `m_cart_user_id`='.$user->getInfo('m_users_id').' AND `m_cart_active`=1 LIMIT 1;';
					if($sql->query($q))
						return 'SUCCESS';
					else return 'ERROR_UPDATE_CART';
				}
			}
			else return 'ERROR_UNKNOWN_PROMOCODE';
		}
		else return 'ERROR_LIMIT_TRY_CODE';
	}
	
	
	//ОТЛОЖЕННЫЕ ТОВАРЫ
	public function getDeferredCart($json=false){
		if($this->deferredCart)
			if($json) return $this->deferredCart['m_deferred_cart_data'];
			else return json_decode($this->deferredCart['m_deferred_cart_data']);
				
		return null;
	}
	public function getDeferredCartSize(){
		if($this->deferredCart){
			$deferredCart=json_decode($this->deferredCart['m_deferred_cart_data']);
			return sizeof($deferredCart->items);
		}
		return null;
	}
	public function getDeferredCartSum(){
		if($this->deferredCart){
			$deferredCart=json_decode($this->deferredCart['m_deferred_cart_data']);
			return $deferredCart->sum;
		}
		return null;
	}
	
	private function deferredCart_new($data){
		global $sql,$e,$G,$user;
	
		if($data=$this->cart_data_item($data)){
			$deferredCart_data['sum']=$data['product_price']*$data['product_count'];
			$deferredCart_data['items'][0]=$data;
			$deferredCart_data=json_encode($deferredCart_data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			$q='INSERT INTO `formetoo_main`.`m_deferred_cart` SET 
				`m_deferred_cart_id`='.get_id('m_deferred_cart').',
				`m_deferred_cart_user_id`='.$user->getInfo('m_users_id').',
				`m_deferred_cart_date`=\''.dt().'\',
				`m_deferred_cart_update`=\''.dt().'\',
				`m_deferred_cart_data`=\''.$deferredCart_data.'\';';
			if($sql->query($q))
				return $deferredCart_data;
			else return 'ERROR_SAVE_NEW_DEFERRED_CART';
		}
		else return 'ERROR_LOAD_PRODUCT_DATA_NEW_DEFERRED_CART';
	}
	
	public function deferredCart_update_item($data){
		global $sql,$e,$G,$user;
		
		if($this->deferredCart){
			//состав корзины
			$deferredCart_data=json_decode($this->deferredCart['m_deferred_cart_data']);
			$item=null;
			foreach($deferredCart_data->items as &$_items)
				if($_items->product_id==$data['product_id'])
					$item=$_items;
			//если добаляемый товар уже есть в корзине
			if($item){
				//удаление позиции из корзины
				$delete=$data['product_count']==-1?true:false;
				//проверка на кратность товара и минимально возможное количество
				$item->product_volume=$item->product_volume?$item->product_volume:1;
				if(fmod($data['product_count'],$item->product_volume)!=0)
					$data['product_count']=ceil($data['product_count']/$item->product_volume)*$item->product_volume;
				$data['product_count']=$data['product_count']>$item->product_volume?$data['product_count']*1:$item->product_volume;
				//текущая сумма по позиции до обновления
				$sum_old=$data['update']?($item->product_price*$item->product_count):0;
				$sum_old=$delete?-$sum_old:$sum_old;
				//если есть метка обновления кол-ва в корзине, сохраняем новое значение кол-ва, иначе суммируем прежнее и новое значение
				$item->product_count=$data['update']?$data['product_count']:$item->product_count+$data['product_count'];
				//сумма по добавляемому товару
				$sum=$item->product_price*$data['product_count'];
			}
			//если нет - добавляем товар
			elseif(!$data['update'])
				if($data=$this->cart_data_item($data)){
					$sum=$data['product_price']*$data['product_count'];
					$deferredCart_data->items[]=$data;
				}
				else{
					return 'ERROR_LOAD_PRODUCT_DATA_EXIST_DEFERRED_CART';
				}
			else return 'ERROR_UPDATE_UNDEFINED_DATA';
			if(!$delete)
				$deferredCart_data->sum+=$sum-$sum_old;
			else{
				//удаляем позицию и обновляем цену
				$deferredCart_data->sum+=$sum_old;
				//временный массив, в который попадут только неудалённые позиции (баг с удалением unset в foreach со ссылкой)
				$a=array();
				foreach($deferredCart_data->items as &$_items)
					if($_items->product_id!=$data['product_id'])
						$a[]=$_items;
				$deferredCart_data->items=$a;
			}
			$deferredCart_data=json_encode($deferredCart_data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
			$q='UPDATE `formetoo_main`.`m_deferred_cart` SET 
				`m_deferred_cart_data`=\''.$deferredCart_data.'\',
				`m_deferred_cart_update`=\''.dt().'\' 
				WHERE `m_deferred_cart_user_id`='.$user->getInfo('m_users_id').' LIMIT 1;';
			if($sql->query($q))
				return $deferredCart_data;
			else return 'ERROR_UPDATE_DEFERRED_CART';
		}
		//если корзина не создавалась
		else{
			return $this->deferredCart_new($data);
		}
	}
	
	public function getOrders(){
		global $sql,$e,$user;
		
		$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_customer` IN('.implode(',',$user->getUserContragentsId()).');';
		if($res=$sql->query($q)){
			return $res;
		}
		return null;
	}

}

?>