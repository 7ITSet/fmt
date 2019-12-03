<?
defined ('_DSITE') or die ('Access denied');

class user{
	private $info,
			$isCrawler=0,
			$ip='0.0.0.0';

	function __construct($update=true){
		global $sql,$G;

		//распознаём ip посетителя
		$this->ip=user_info::ip();
		$t_time=dtc(dt(),'+30 day');
		//если существует cookie uid
		if(isset($_COOKIE['uid'])&&$cookie=val($_COOKIE['uid'])){
			//поиск залогиненного пользователя по cookie
			$q='SELECT * FROM `formetoo_main`.`cookies`,`formetoo_main`.`m_users`
				WHERE
					`cookies`.`cookies_cookie`=\''.$cookie.'\' AND
					`cookies`.`cookies_browser`=\''.md5($_SERVER['HTTP_USER_AGENT']).'\' AND
					`cookies`.`cookies_date`>\''.dt().'\' AND
					`m_users`.`m_users_id`=`cookies`.`cookies_m_users_id` AND
					`m_users_active`=1 LIMIT 1;';
			if($res=$sql->query($q)){
				session_name('ut');
				session_start(['cookie_httponly'=>true,'cookie_secure'=>true,'gc_probability'=>10,'gc_maxlifetime'=>600]);

				$this->info=$res[0];
				//можно не обновлять куку (для ajax запросов)
				if($update){
					//обновление cookie
					setcookie('uid',$cookie,dtu($t_time),'/','.'.$_SERVER['SERVER_NAME'],true,true);
					//обновление сессии
					$q='UPDATE `formetoo_main`.`cookies` SET `cookies_date`=\''.$t_time.'\' WHERE `cookies_cookie`=\''.$cookie.'\' LIMIT 1;';
					$sql->query($q);
				}
			}
			else setcookie('uid','',time()-259200,'/','.'.$_SERVER['SERVER_NAME'],true,true);
			//если пользователь не найден, делаем авто-регистрацию нового пользователя
			/* else{
				if($cookie=$this->registerAuto())
					setcookie('uid',$cookie,dtu($t_time),'/','.'.$_SERVER['SERVER_NAME'],true,false);
			} */
		}
		//если запись не найдена делаем авто-регистрацию нового пользователя
		/* else{
			if($cookie=$this->registerAuto())
				setcookie('uid',$cookie,dtu($t_time),'/','.'.$_SERVER['SERVER_NAME'],true,false);
		} */
		
		$this->settings = self::getMainSettings();
	}
	function __destruct(){
		session_write_close();
	}

	private function getMainSettings() {
		global $sql;
		$q='SELECT `key`,`value` FROM `formetoo_main`.`m_settings` WHERE `module_id`="main"';
		$res=$sql->query($q);
		
		$arr = array();
		foreach($res as $item) {
			$arr[$item['key']] = $item['value'];
		}

		return $arr;
	}

	public function isVisiblePrice() {
		if ($this->getInfo() || $this->settings['price_guest_visible']) {
			return true;
		}
		return false;
	}

	public function getInfo($field='m_users_id'){
		return !empty($this->info[$field])?$this->info[$field]:null;
	}

	public function checkCrawler(){
		return $this->isCrawler;
	}

	public function getHash($password) {
		$db_password=array();
		if (defined("CRYPT_BLOWFISH")&&CRYPT_BLOWFISH){
			$salt='$2y$10$'.substr(md5(uniqid(rand(),true)),0,22);
			for($i=0;$i<ceil(strlen($password)/8);$i++)
				$db_password[]=crypt(substr($password,$i*8),$salt);
		}
		return implode('|',$db_password);
	}
	private function verHash($password,$hashedPassword){
		$verify=1;
		$hashedPassword=explode('|',$hashedPassword);
		if(sizeof($hashedPassword)==ceil(strlen($password)/8)){
			for($i=0;$i<ceil(strlen($password)/8);$i++)
				$verify=$verify*(crypt(substr($password,$i*8),$hashedPassword[$i])==$hashedPassword[$i])?1:0;
			return $verify;
		}
		else
			return false;
	}

	public function checkCaptcha($captcha=null,$destroy=false){
		global $sql,$e;

		$uact=$this->getUserActions();
		if(isset($uact[6])&&sizeof($uact[6])>25){
			$e[]='Превышено число попыток ввода капчи';
			elogs();
			return false;
		}
		else{
			if($captcha){
				if(isset($_SESSION['code'])&&$captcha==$_SESSION['code']){
					if($destroy) $_SESSION['code']=rand(10000,999999);
					return true;
				}
				else{
					$this->setUserActions(6,$captcha);
					return false;
				}
			}
			return false;
		}
	}
	//проверка на дубли в базе e-mail и телефонов и ИНН+КПП
	public function checkEmail($email=null){
		global $sql,$e;

		if($email){
			$q='SELECT `m_users_id` FROM `formetoo_main`.`m_users` WHERE
				`m_users_email`=\''.$email.'\' AND
				`m_users_id`!='.$this->info['m_users_id'].' LIMIT 1;';
			if($res=$sql->query($q))
				return $res[0]['m_users_id'];
		}
		return null;
	}
	public function checkTel($tel=null){
		global $sql,$e;

		if($tel){
			$q='SELECT * FROM `formetoo_main`.`m_users` WHERE
				`m_users_tel`=\''.$tel.'\' AND
				`m_users_id`!='.$this->info['m_users_id'].' LIMIT 1;';
			if($res=$sql->query($q))
				return $res[0]['m_users_id'];
		}
		return null;
	}
	public function checkINN($inn=null,$kpp=null){
		global $sql,$e;

		if($inn){
			$q='SELECT * FROM `formetoo_cdb`.`m_contragents` WHERE
				`m_contragents_c_inn`='.$inn.'
				'.(strlen($inn)==10&&$kpp?'AND `m_contragents_c_kpp`='.$kpp:'').' LIMIT 1;';
			if($res=$sql->query($q))
				return true;
		}
		return null;
	}
	//получение информации о фирме по ИНН из dadata.ru
	public function getInnInfo($inn=null){
		if($inn){
			$headers = array
			(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Token aecb50049a606c2efc98246621d8527651bd84ea'
			);
			$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, '{
				"query": "'.$inn.'",
				"type": "'.(strlen($inn)==10?'LEGAL':'INDIVIDUAL').'"
			}');
			$result = curl_exec($ch);
			curl_close($ch);

			if(strpos($result,'suggestions')!==false)
				return $result;
			else
				return 'ERROR';
		}
		return null;
	}
	//получение информации об адресе из dadata.ru
	public function getAddressInfo($address=null,$count=10){
		if($address){
			$headers = array
			(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Token aecb50049a606c2efc98246621d8527651bd84ea'
			);
			$ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, '{
				"query": "'.$address.'",
				"count": "'.$count.'"
			}');
			$result = curl_exec($ch);
			curl_close($ch);

			if(strpos($result,'suggestions')!==false)
				return $result;
			else
				return 'ERROR';
		}
		return null;
	}

	//действия пользователя за последний час, группированные по типу
	public function getUserActions(){
		global $sql;

		if($this->info['m_users_id']){
			$q='SELECT * FROM `formetoo_main`.`m_users_actions` WHERE
				`m_users_actions_user_id`='.$this->info['m_users_id'].' AND
				`m_users_actions_date`>\''.dtc(dt(),'-1 hour').'\';';
			if($res=$sql->query($q,'m_users_actions_type'))
				return $res;
			else return null;
		}
		return null;
	}
	//добавление действия пользователя
	public function setUserActions($type=null,$data=null){
		global $sql;

		if(!$type) return false;

		$q='INSERT INTO `formetoo_main`.`m_users_actions` SET
			`m_users_actions_date`=\''.dt().'\',
			`m_users_actions_data`=\''.$data.'\',
			`m_users_actions_type`='.$type.',
			`m_users_actions_user_id`='.(isset($this->info['m_users_id'])?$this->info['m_users_id']:0).';';
		if($sql->query($q))
			return true;
		else return false;

	}

	//контрагенты пользователя
	public function getUserContragents($reset_keys=true){
		global $sql;

		$result=array();
		$q='SELECT * FROM `formetoo_cdb`.`m_contragents` WHERE
			`m_contragents_user_id`='.$this->info['m_users_id'].' ORDER BY `m_contragents_date` DESC,`m_contragents_default` DESC;';
		if($res=($reset_keys?$sql->query($q):$sql->query($q,'m_contragents_id'))){
			$ids=array();
			foreach($res as $_res){
				$ids[]=$reset_keys?$_res['m_contragents_id']:$_res[0]['m_contragents_id'];
			}
			if($ids)
				$result['contragents']=$res;
			if($res_addr=$sql->query('SELECT * FROM `formetoo_cdb`.`m_contragents_address` WHERE `m_address_contragents_id` IN ('.implode(',',$ids).') ORDER BY `m_address_type` DESC,`m_address_date` DESC;','m_address_contragents_id'))
				$result['addresses']=$res_addr;
			if($res_tel=$sql->query('SELECT * FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id` IN ('.implode(',',$ids).') ORDER BY `m_contragents_tel_type`,`m_contragents_tel_date` DESC;','m_contragents_tel_contragents_id'))
				$result['telephones']=$res_tel;
			return $result;
		}

		return null;
	}
	public function getUserContragentsId(){

		$data_contr=$this->getUserContragents(false);
		$id=array();
		foreach($data_contr['contragents'] as $_id=>$_contr)
			$id[]=$_id;
		return $id;
	}

	public function addContragentFirm(){
		global $sql,$e,$G,$user;

		$data['add_contragent_2_inn']=array(1,10,12,null,5);
		$data['add_contragent_2_kpp']=array(null,null,null,9,1);
		$data['add_contragent_2_name']=array(1,null,180);
		$data['add_contragent_2_nds']=array(1,null,null,1,1);
		$data['captcha']=array(null,5,7);
		array_walk($data,'check');

		if(!$e){
			$uact=$this->getUserActions();
			if(isset($uact[14])&&sizeof($uact[14])>3){
				if(!$this->checkCaptcha($data['captcha'],true))
					$e[]='Код с капчи введён неправильно';
			}
			else $this->setUserActions(14,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}

		if(!$e){
			$data_contr['m_contragents_c_inn']=$data['add_contragent_2_inn'];
			$data_contr['m_contragents_c_kpp']=$data['add_contragent_2_kpp'];
			$data_contr['m_contragents_c_name_short']=$data['add_contragent_2_name'];
			$data_contr['m_contragents_c_nds']=$data['add_contragent_2_nds']?1:0;
			//записываем нового контрагента
			$data_contr['m_contragents_id']=get_id('m_contragents');
			$data_contr['m_contragents_user_id']=$this->info['cookies_m_users_id'];
			$data_contr['m_contragents_email']=$this->info['m_users_email'];
			$data_contr['m_contragents_date']=$data_contr['m_contragents_update']=dt();
			$data_contr['m_contragents_type']=2;

			if($data_contr['m_contragents_c_inn']&&(strlen($data_contr['m_contragents_c_inn'])==10?$data_contr['m_contragents_c_kpp']:true)&&$data_contr['m_contragents_c_name_short']&&!$this->checkINN($data_contr['m_contragents_c_inn'],$data_contr['m_contragents_c_kpp'])){
				//если информация о фирме есть в базе - заполняем доп. поля
				if($org_info=$this->getInnInfo($data_contr['m_contragents_c_inn'])){
					$org_info=json_decode($org_info);
					if($org_info->suggestions){
						$org_info=$org_info->suggestions[0]->data;
						$data_contr['m_contragents_c_name_full']=val($org_info->name->full_with_opf);
						$data_contr['m_contragents_c_name_short']=val($org_info->name->short_with_opf);
						$data_contr['m_contragents_c_inn']=$org_info->inn;
						$data_contr['m_contragents_c_kpp']=strlen($data_contr['m_contragents_c_inn'])==10?$data['kpp']:0;
						$data_contr['m_contragents_c_ogrn']=$org_info->ogrn;
						$data_contr['m_contragents_c_okpo']=$org_info->okpo;
						$data_contr['m_contragents_c_okved']=$org_info->okved;
						$data_contr['m_contragents_c_director_post']=strlen($data_contr['m_contragents_c_inn'])==10?val($org_info->management->post):val($org_info->opf->full);
						$data_contr['m_contragents_c_director_name']=strlen($data_contr['m_contragents_c_inn'])==10?val($org_info->management->name):val($org_info->name->full);
						//добавляем адрес компании
						if($org_info->address->data){
							$data_contr['m_contragents_c_okato']=$org_info->address->data->okato;
							$data_contr['m_contragents_c_oktmo']=$org_info->address->data->oktmo;
							$data_addr['m_address_contragents_id']=$data_contr['m_contragents_id'];
							$data_addr['m_address_full']=val($org_info->address->data->postal_code.', '.($org_info->address->unrestricted_value?$org_info->address->unrestricted_value:$org_info->address->value));
							$data_addr['m_address_index']=$org_info->address->data->postal_code;
							$data_addr['m_address_area']=val($org_info->address->data->region_with_type);
							$data_addr['m_address_district']=val($org_info->address->data->area_with_type);
							$data_addr['m_address_city']=val($org_info->address->data->city_with_type);
							$data_addr['m_address_street']=val($org_info->address->data->street_with_type);
							$data_addr['m_address_house']=val($org_info->address->data->house);
							$data_addr['m_address_corp']=val($org_info->address->data->block);
							$data_addr['m_address_detail']=val($org_info->address->data->flat_type.' '.$org_info->address->data->flat);
							$data_addr['m_address_map_lat']=strlen($data_contr['m_contragents_c_inn'])==10?$org_info->address->data->geo_lat:'';
							$data_addr['m_address_map_lon']=strlen($data_contr['m_contragents_c_inn'])==10?$org_info->address->data->geo_lon:'';
							$data_addr['m_address_date']=dt();
							$q_addr='INSERT INTO `formetoo_cdb`.`m_contragents_address` SET ';
							$qa=array();
							foreach($data_addr as $field=>$val)
								$qa[]='`'.$field.'`=\''.$val.'\'';
							$q_addr.=implode(',',$qa).';';
						}
					}
				}
				$q_contr='INSERT INTO `formetoo_cdb`.`m_contragents` SET ';
				$qa=array();
				foreach($data_contr as $field=>$val)
					$qa[]='`'.$field.'`=\''.$val.'\'';
				$q_contr.=implode(',',$qa).';';

				if(isset($q_contr)&&$sql->query($q_contr))
					if(isset($q_addr))
						$sql->query($q_addr);
				return $this->getUserContragents();
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			return 'ERROR';
		}
	}

	public function addContragentTel(){
		global $sql,$e,$G,$user;

		$data['tel[]']=array(null,null,null,16,2);
		$data['tel_comment[]']=array(null,null,50);
		$data['customer']=array(1,null,null,10,1);
		array_walk($data,'check');

		if(!$e){
			$uact=$this->getUserActions();
			if(isset($uact[16])&&sizeof($uact[16])>30){
				$e[]='Превышен лимит указанных телефонов';
			}
			else $this->setUserActions(16,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}
		if(!$e){
			if(!($ca=$this->getUserContragents(false))||!isset($ca['contragents'][$data['customer']]))
				$e[]='Ошибка привязки телефона к контрагенту';
		}
		if(!$e){
			$q_tel=array();
			$tel_count=sizeof($data['tel[]']);
			if($tel_count){
				$sql->query('DELETE FROM `formetoo_cdb`.`m_contragents_tel` WHERE `m_contragents_tel_contragents_id`='.$data['customer'].';');
				for($i=0;$i<$tel_count;$i++)
					if($data['tel[]'][$i])
						$q_tel[]='('.$data['customer'].',\''.$data['tel[]'][$i].'\',\''.$data['tel_comment[]'][$i].'\',\''.dt().'\')';
				if($q_tel&&$sql->query('INSERT INTO `formetoo_cdb`.`m_contragents_tel` (`m_contragents_tel_contragents_id`,`m_contragents_tel_numb`,`m_contragents_tel_comment`,`m_contragents_tel_date`) VALUES '.implode(',',$q_tel).';'))
					return true;
				else
					return false;
			}
		}
	}

	public function addContragentPerson(){
		global $sql,$e,$G,$user;

		$data['add_contragent_3_name1']=array(1,null,50);
		$data['add_contragent_3_name2']=array(null,null,50);
		$data['add_contragent_3_name3']=array(null,null,50);
		$data['add_contragent_3_birtday']=$data['add_contragent_3_passport_date']=array(null,null,null,10);
		$data['add_contragent_3_sex']=array(1,null,null,1,1);
		$data['add_contragent_3_passport_sn']=array(null,null,null,12);
		$data['add_contragent_3_passport_v']=array(null,null,100);
		$data['captcha']=array(null,5,7);
		array_walk($data,'check');

		if(!$e){
			$uact=$this->getUserActions();
			if(isset($uact[14])&&sizeof($uact[14])>3){
				if(!$this->checkCaptcha($data['captcha'],true))
					$e[]='Код с капчи введён неправильно';
			}
			else $this->setUserActions(14,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}

		if(!$e){
			//записываем нового контрагента
			$data_contr['m_contragents_id']=get_id('m_contragents');
			$data_contr['m_contragents_p_fio']=implode(' ',array($data['add_contragent_3_name1'],$data['add_contragent_3_name2'],$data['add_contragent_3_name3']));
			$data_contr['m_contragents_p_birthday']=$data['add_contragent_3_birtday']?dtu($data['add_contragent_3_birtday'],'Y-m-d H:i:s'):'';
			$data_contr['m_contragents_p_passport_date']=$data['add_contragent_3_passport_date']?dtu($data['add_contragent_3_passport_date'],'Y-m-d H:i:s'):'';
			$data_contr['m_contragents_p_passport_v']=$data['add_contragent_3_passport_v'];
			$data_contr['m_contragents_p_passport_sn']=$data['add_contragent_3_passport_sn'];
			$data_contr['m_contragents_p_sex']=$data['add_contragent_3_sex']?1:0;
			$data_contr['m_contragents_user_id']=$this->info['cookies_m_users_id'];
			$data_contr['m_contragents_email']=$this->info['m_users_email'];
			$data_contr['m_contragents_date']=$data_contr['m_contragents_update']=dt();
			$data_contr['m_contragents_type']=3;

			$q_contr='INSERT INTO `formetoo_cdb`.`m_contragents` SET ';
			$qa=array();
			foreach($data_contr as $field=>$val)
				$qa[]='`'.$field.'`=\''.$val.'\'';
			$q_contr.=implode(',',$qa).';';

			if(isset($q_contr)&&$sql->query($q_contr))
				if(isset($q_addr))
					$sql->query($q_addr);
			return $this->getUserContragents();

		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			return 'ERROR';
		}
	}

	public function addContragentAddress(){
		global $sql,$e,$G,$user;

		$data['add_address_index']=array(null,null,null,6,1);
		$data['add_address_area']=array(1,null,80);
		$data['add_address_district']=array(null,null,80);
		$data['add_address_city']=array(null,null,80);
		$data['add_address_city_district']=array(null,null,80);
		$data['add_address_city_settlement']=array(null,null,80);
		$data['add_address_street']=array(null,null,80);
		$data['add_address_house']=array(null,null,10);
		$data['add_address_corp']=array(null,null,10);
		$data['add_address_build']=array(null,null,10);
		$data['add_address_mast']=array(null,null,10);
		$data['add_address_detail']=array(null,null,180);
		$data['add_address_additional']=array(null,null,180);
		$data['add_address_full']=array(1,null,480);
		$data['add_address_map_lat']=array(null,null,20);
		$data['add_address_map_lon']=array(null,null,20);
		$data['contragent']=array(1,null,null,10,1);
		$data['captcha']=array(null,5,7);
		array_walk($data,'check');

		if(!$e){
			$uact=$this->getUserActions();
			if(isset($uact[15])&&sizeof($uact[15])>5){
				if(!$this->checkCaptcha($data['captcha'],true))
					$e[]='Код с капчи введён неправильно';
			}
			else $this->setUserActions(15,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}

		if(!($ca=$this->getUserContragents(false))||!isset($ca['contragents'][$data['contragent']]))
			$e[]='Ошибка привязки адреса к контрагенту';

		if(!$e){

			$q='INSERT INTO `formetoo_cdb`.`m_contragents_address` SET
				`m_address_contragents_id`='.$data['contragent'].',
				`m_address_date`=\''.dt().'\',
				`m_address_full`=\''.$data['add_address_full'].'\',
				`m_address_index`=\''.$data['add_address_index'].'\',
				`m_address_area`=\''.$data['add_address_area'].'\',
				`m_address_district`=\''.$data['add_address_district'].'\',
				`m_address_city`=\''.$data['add_address_city'].'\',
				`m_address_city_district`=\''.$data['add_address_city_district'].'\',
				`m_address_city_settlement`=\''.$data['add_address_city_settlement'].'\',
				`m_address_street`=\''.$data['add_address_street'].'\',
				`m_address_house`=\''.$data['add_address_house'].'\',
				`m_address_corp`=\''.$data['add_address_corp'].'\',
				`m_address_build`=\''.$data['add_address_build'].'\',
				`m_address_mast`=\''.$data['add_address_mast'].'\',
				`m_address_additional`=\''.$data['add_address_additional'].'\',
				`m_address_map_lat`=\''.$data['add_address_map_lat'].'\',
				`m_address_map_lon`=\''.$data['add_address_map_lon'].'\',
				`m_address_detail`=\''.$data['add_address_detail'].'\';';
			if($sql->query($q))
				return $this->getUserContragents();
			else return 'ERROR_ADD_ADDRESS';
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			return 'ERROR';
		}
	}

	//регистрация нового пользователя
	public function register(){
		global $sql,$e,$G;

		//$data['token']=array(1,null,null,32);
		$data['captcha']=array(null,5,7);
		$data['name']=array(1,null,180);
		$data['email']=array(null,null,null,null,4);
		$data['tel']=array(null,null,null,16,2);
		$data['password']=array(1,null,50);
		$data['jur']=array(null,null,3);
		$data['from_cart']=array(null,null,null,1,1);
		$data['nds']=array(null,null,3);
		$data['inn']=array(null,10,12,null,5);
		$data['kpp']=array(null,null,null,9,1);
		$data['org_name']=array(null,null,90);
		$data['politic']=array(1,null,null,1,1);
		$data['newsletter']=array(null,null,3);
		$data['quick']=array(null,null,null,1,1);
		$data['autopass']=array(null,0,10);
		array_walk($data,'check');

		/* if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token']; */

		if(!$data['quick'])
			if(!$this->checkCaptcha($data['captcha'],true))
				$e[]='Код с капчи введён неправильно';

		if(!$data['email']&&!$data['tel'])
			$e[]='Телефон и email пустые';

		$data['politic']=$data['politic']?1:0;
		if(!$data['politic'])
			$e[]='Не принята оферта и политика конфиденциальности';

		if(!$e){
			$uact=$this->getUserActions();
			if(isset($uact[8])&&sizeof($uact[8])>15)
				$e[]='Превышен лимит регистраций пользователя';
			else $this->setUserActions(8,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
		}

		if(!$e&&!$data['quick']){
			if($this->checkEmail($data['email']))
				$e[]='Пользователь с e-mail '.$data['email'].' уже есть в системе';
			if($this->checkTel($data['tel']))
				$e[]='Пользователь с телефоном '.$data['tel'].' уже есть в системе';
		}

		if(!$e){
			$data['newsletter']=$data['newsletter']?1:0;
			$data['jur']=$data['jur']?1:0;
			$data['nds']=$data['nds']?1:0;
			//добавляем пользователя
			$q='UPDATE `formetoo_main`.`m_users` SET
				`m_users_password`=\''.$this->getHash($data['password']).'\',
				`m_users_city`=\''.$G['CITY']['m_info_city_id'].'\',
				`m_users_name`=\''.(!$data['quick']?$data['name']:'').'\',
				`m_users_tel`=\''.(!$data['quick']?$data['tel']:'').'\',
				`m_users_email`=\''.(!$data['quick']?$data['email']:'').'\',
				`m_users_date`=\''.dt().'\',
				`m_users_accept_politic`='.$data['politic'].',
				`m_users_accept_newsletter_sms`='.$data['newsletter'].',
				`m_users_accept_newsletter_email`='.$data['newsletter'].'
				WHERE `m_users_id`='.$this->info['cookies_m_users_id'].' LIMIT 1;';
			if($sql->query($q)){
				//записываем нового контрагента
				$data_contr['m_contragents_id']=get_id('m_contragents');
				$data_contr['m_contragents_user_id']=$this->info['cookies_m_users_id'];
				$data_contr['m_contragents_email']=$data['email'];
				$data_contr['m_contragents_date']=$data_contr['m_contragents_update']=dt();
				//добавляем телефон пользователя контрагенту
				if($data['tel'])
					$q_tel='INSERT INTO `formetoo_cdb`.`m_contragents_tel` SET
						`m_contragents_tel_contragents_id`='.$data_contr['m_contragents_id'].',
						`m_contragents_tel_date`=\''.dt().'\',
						`m_contragents_tel_numb`=\''.$data['tel'].'\',
						`m_contragents_tel_comment`=\''.$data['name'].'\';';
				//если пользователь - представитель юрлица или ип и есть инн, наименование, кпп в случае юрлица и если юрлицо еще не добавлялось
				if($data['jur']&&$data['inn']&&(strlen($data['inn'])==10?$data['kpp']:true)&&$data['org_name']&&!$this->checkINN($data['inn'],$data['kpp'])){
					//если информация о фирме есть в базе - заполняем доп. поля
					if($org_info=$this->getInnInfo($data['inn'])){
						$data_contr['m_contragents_type']=2;
						$data_contr['m_contragents_c_nds']=$data['nds'];
						$org_info=json_decode($org_info);
						if($org_info->suggestions){
							$org_info=$org_info->suggestions[0]->data;
							$data_contr['m_contragents_c_name_full']=$org_info->name->full_with_opf;
							$data_contr['m_contragents_c_name_short']=$org_info->name->short_with_opf;
							$data_contr['m_contragents_c_inn']=$org_info->inn;
							$data_contr['m_contragents_c_kpp']=strlen($data['inn'])==10?$data['kpp']:0;
							$data_contr['m_contragents_c_ogrn']=$org_info->ogrn;
							$data_contr['m_contragents_c_okpo']=$org_info->okpo;
							$data_contr['m_contragents_c_okved']=$org_info->okved;
							$data_contr['m_contragents_c_director_post']=strlen($data['inn'])==10?$org_info->management->post:$org_info->opf->full;
							$data_contr['m_contragents_c_director_name']=strlen($data['inn'])==10?$org_info->management->name:$org_info->name->full;
							//добавляем адрес компании
							if($org_info->address->data){
								$data_contr['m_contragents_c_okato']=$org_info->address->data->okato;
								$data_contr['m_contragents_c_oktmo']=$org_info->address->data->oktmo;
								$data_addr['m_address_contragents_id']=$data_contr['m_contragents_id'];
								$data_addr['m_address_full']=$org_info->address->data->postal_code.', '.($org_info->address->unrestricted_value?$org_info->address->unrestricted_value:$org_info->address->value);
								$data_addr['m_address_index']=$org_info->address->data->postal_code;
								$data_addr['m_address_area']=$org_info->address->data->region_with_type;
								$data_addr['m_address_district']=$org_info->address->data->area_with_type;
								$data_addr['m_address_city']=$org_info->address->data->city_with_type;
								$data_addr['m_address_street']=$org_info->address->data->street_with_type;
								$data_addr['m_address_house']=$org_info->address->data->house;
								$data_addr['m_address_corp']=$org_info->address->data->block;
								$data_addr['m_address_detail']=$org_info->address->data->flat_type.' '.$org_info->address->data->flat;
								$data_addr['m_address_map_lat']=strlen($data['inn'])==10?$org_info->address->data->geo_lat:'';
								$data_addr['m_address_map_lon']=strlen($data['inn'])==10?$org_info->address->data->geo_lon:'';
								$data_addr['m_address_date']=dt();
								$q_addr='INSERT INTO `formetoo_cdb`.`m_contragents_address` SET ';
								$qa=array();
								foreach($data_addr as $field=>$val)
									$qa[]='`'.$field.'`=\''.$val.'\'';
								$q_addr.=implode(',',$qa).';';
							}
						}
					}
					$q_contr='INSERT INTO `formetoo_cdb`.`m_contragents` SET ';
					$qa=array();
					foreach($data_contr as $field=>$val)
						$qa[]='`'.$field.'`=\''.$val.'\'';
					$q_contr.=implode(',',$qa).';';
				}
				//пользователь - физлицо
				else{
					$data_contr['m_contragents_type']=3;
					$data_contr['m_contragents_p_fio']=$data['name'];
					$q_contr='INSERT INTO `formetoo_cdb`.`m_contragents` SET ';
					$qa=array();
					foreach($data_contr as $field=>$val)
						$qa[]='`'.$field.'`=\''.$val.'\'';
					$q_contr.=implode(',',$qa).';';
				}
				if(isset($q_contr)&&$sql->query($q_contr))
					if(isset($q_addr))
						$sql->query($q_addr);
				if($data['tel']){
					$sql->query($q_tel);
					$this->info['m_users_tel']=$data['tel'];
					if(!$data['quick'])
						$this->sendConfirmTel();
				}

				if($data['email']){
					$this->info['m_users_email']=$data['email'];
					//отправляем письмо для подстверждения почты, если регистрация была не с быстрого заказа, в скобках пометка выслать сгенерированный пароль на почту
					if(!$data['quick'])
						$this->sendConfirmEmail($data['autopass']);
				}

				if(!$data['quick']&&!$data['autopass'])
					if($data['from_cart']) header('Location: /my/orders/');
					else header('Location: /my/profile/');
				else return $data_contr['m_contragents_id'];
			}
			else{
				elogs(__FILE__,__FUNCTION__,$data);
				if(!$data['quick']&&!$data['autopass'])
					header('Location: '.url().'?error');
				else return false;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			if(!$data['quick']&&!$data['autopass'])
				header('Location: '.url().'?error');
			else return false;
		}
		exit;
	}

	//АВТОДОБАВЛЕНИЕ НОВОГО НЕЗАЛОГИНЕННОГО ПОЛЬЗОВАТЕЛЯ
	public function registerAuto(){
		global $sql,$G;

		//исключения
		if($this->crawlerDetect()||!$_SERVER['HTTP_USER_AGENT']) return false;

		if(!$this->getInfo()){
			$data['m_users_id']=get_id('m_users');
			$cookie=get_id('cookies',0,'cookies_id',true);
			//генерация токена для форм
			$token=md5(mt_rand(10005372,99899758));

			$q='INSERT `formetoo_main`.`m_users` SET
				`m_users_id`='.$data['m_users_id'].',
				`m_users_group`=1054927507,
				`m_users_active`=1,
				`m_users_city`=\''.$G['CITY']['m_info_city_id'].'\',
				`m_users_date`=\''.dt().'\';';

			if($sql->query($q)){
				$q='INSERT INTO `formetoo_main`.`cookies` SET
					`cookies_m_users_id`='.$data['m_users_id'].',
					`cookies_cookie`=\''.$cookie.'\',
					`cookies_browser`=\''.md5($_SERVER['HTTP_USER_AGENT']).'\',
					`cookies_date`=\''.dtc(dt(),'+90 day').'\',
					`cookies_useragent`=\''.$_SERVER['HTTP_USER_AGENT'].'\',
					`cookies_ip`=\''.$this->ip.'\',
					`cookies_token`=\''.$token.'\';';
				if($sql->query($q))
					return $cookie;
				else{
					elogs(__FILE__,__FUNCTION__,$data);
					return false;
				}
			}
			else{
				elogs(__FILE__,__FUNCTION__,$data);
				return false;
			}
		}
		else return 0;
	}

	public function changeUserAccount(){
		global $sql,$e,$G;

		$data['token']=array(1,null,null,32);
		$data['captcha']=array(null,5,7);
		$data['password']=array(1,null,50);
		$data['name']=array(1,null,180);
		$data['email']=array(null,null,null,null,4);
		$data['tel']=array(null,null,null,16,2);
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		//читаем капчу, если превышен лимит изменений настроек профиля
		$uact=$this->getUserActions();
		if(isset($uact[9])&&sizeof($uact[9])>10){
			if(!$this->checkCaptcha($data['captcha'],true))
				$e[]='Код с капчи введён неправильно';
		}
		else $this->setUserActions(9,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		if(!$e){
			if(!$this->verHash($data['password'],$this->info['m_users_password'])){
				$e[]='Пароль для применения изменений не верный';
				elogs();
				header('Location: /my/profile/?change_account=error_password');
				exit;
			}
		}

		if(!$data['email']&&!$data['tel'])
			$e[]='Телефон и email пустые';

		if(!$e){
			if($this->checkEmail($data['email']))
				$e[]='Пользователь с e-mail '.$data['email'].' уже есть в системе';
			if($this->checkTel($data['tel']))
				$e[]='Пользователь с телефоном '.$data['tel'].' уже есть в системе';
		}

		if(!$e){
			$q='UPDATE `formetoo_main`.`m_users` SET
			`m_users_name`=\''.$data['name'].'\',
			`m_users_email`=\''.$data['email'].'\',
			`m_users_email_confirm`='.($this->info['m_users_email_confirm']==1&&$this->info['m_users_email']==$data['email']?1:0).',
			`m_users_tel`=\''.$data['tel'].'\',
			`m_users_tel_confirm`='.($this->info['m_users_tel_confirm']==1&&$this->info['m_users_tel']==$data['tel']?1:0).'
			WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
			if($sql->query($q)){
				$change_email=$this->info['m_users_email']!=$data['email']?true:false;
				$change_tel=$this->info['m_users_tel']!=$data['tel']?true:false;
				//перезагружаем данные аккаунта из базы (обновляем телефон для смс)
				if($change_email||$change_tel) $this->__construct();

				if($change_email) $this->reSendConfirmEmail();
				if($change_tel) $this->sendConfirmTel();

				header('Location: /my/profile/?change_account=success');
				exit;
			}
			else{
				header('Location: /my/profile/?change_account=error');
				exit;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /my/profile/?change_account=error');
			exit;
		}
	}

	public function changeUserPassword(){
		global $sql,$e;

		$data['token']=array(1,null,null,32);
		$data['password']=array(1,null,50);
		$data['new_password']=array(1,null,50);
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		$uact=$this->getUserActions();
		if(isset($uact[10])&&sizeof($uact[10])>10){
			$e[]='Превышен лимит смены пароля';
		}
		else $this->setUserActions(10,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));


		if(!$e){
			if(!$this->verHash($data['password'],$this->info['m_users_password']))
				$e[]='Старый пароль не верный';
		}

		if(!$e){
			$q='UPDATE `formetoo_main`.`m_users` SET
			`m_users_password`=\''.$this->getHash($data['new_password']).'\'
			WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
			if($sql->query($q)){
				header('Location: /my/profile/?change_password=success');
				exit;
			}
			else{
				header('Location: /my/profile/?change_password=error');
				exit;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /my/profile/?change_password=error');
			exit;
		}
	}

	//изменение подписок пользователя
	public function changeUserSubscriptions($no_redirect=null){
		global $sql,$e;

		$data['statusorder_email']=array(null,null,null,1,1);
		$data['statusorder_sms']=array(null,null,null,1,1);
		$data['newsletter_email']=array(null,null,null,1,1);
		$data['newsletter_sms']=array(null,null,null,1,1);
		$data['notification']=array(null,null,null,1,1);
		$data['token']=array(1,null,null,32);

		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		if(!$e){
			$this->setUserActions(9,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

			$data['statusorder_email']=$data['statusorder_email']?1:0;
			$data['statusorder_sms']=$data['statusorder_sms']?1:0;
			$data['newsletter_email']=$data['newsletter_email']?1:0;
			$data['newsletter_sms']=$data['newsletter_sms']?1:0;
			$data['notification']=$data['notification']?1:0;

			$q='UPDATE `formetoo_main`.`m_users` SET
				`m_users_accept_statusorder_email`='.$data['statusorder_email'].',
				`m_users_accept_statusorder_sms`='.$data['statusorder_sms'].',
				`m_users_accept_newsletter_email`='.$data['newsletter_email'].',
				`m_users_accept_newsletter_sms`='.$data['newsletter_sms'].',
				`m_users_accept_notification`='.$data['notification'].'
				WHERE `m_users_id`='.$this->getInfo().' LIMIT 1;';

			if($sql->query($q))
				if(!$no_redirect)
					header('Location: '.url().'?success');
				else return 'OK';
			else{
				elogs(__FILE__,__FUNCTION__,$data);
				if(!$no_redirect)
					header('Location: '.url().'?error');
				else return 'ERROR';
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			if(!$no_redirect)
					header('Location: '.url().'?error');
				else return 'ERROR';
		}
		exit;
	}

	public function addReview($im=0){
		global $sql,$e,$G;

		$data['token']=array(1,null,null,32);
		$data['comment']=array(1,null,900);

		if($im==1){
			$data['captcha']=array(null,5,7);
			$data['name']=array(1,null,180);
			$data['email']=array(1,null,null,null,4);
			$data['city']=array(1,null,80);
			$data['politic']=array(1,null,null,1,1);
		}
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		if($im==1){
			$data['politic']=$data['politic']?1:0;
			if(!$data['politic'])
				$e[]='Не принята оферта и политика конфиденциальности';
		}

		//читаем капчу
		if(!$this->checkCaptcha($data['captcha'],true)&&$im==1)
			$e[]='Код с капчи введён неправильно';
		else $this->setUserActions(19,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		if(!$e){
			$id=get_id('m_products_feedbacks');
			if($im==1){
				$message_data=array(
					'name'=>$data['name'],
					'email'=>$data['email'],
					'city'=>$data['city'],
					'comment'=>$data['comment']
				);
				$q='INSERT INTO `formetoo_main`.`m_products_feedbacks` SET
					`m_products_feedbacks_id`='.$id.',
					`m_products_feedbacks_products_id`=0,
					`m_products_feedbacks_users_id`='.$this->getInfo().',
					`m_products_feedbacks_date`=\''.dt().'\',
					`m_products_feedbacks_rating`=0,
					`m_products_feedbacks_text_total`=\''.json_encode($message_data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\';';
				if($sql->query($q))
					header('Location: /about/reviews/?send=success');
				else{
					header('Location: /about/reviews/?send=error');
					exit;
				}
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /about/reviews/?send=error');
			exit;
		}
	}

	public function feedback(){
		global $sql,$e,$G;

		$data['token']=array(1,null,null,32);
		$data['captcha']=array(null,5,7);
		$data['name']=array(null,null,180);
		$data['email']=array(null,null,null,null,4);
		$data['tel']=array(null,null,null,16,2);
		$data['comment']=array(1,null,900);
		$data['politic']=array(1,null,null,1,1);
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		$data['politic']=$data['politic']?1:0;
		if(!$data['politic'])
			$e[]='Не принята оферта и политика конфиденциальности';

		//читаем капчу, если превышен лимит изменений настроек профиля
		$uact=$this->getUserActions();
		if(isset($uact[17])&&sizeof($uact[17])>5){
			if(!$this->checkCaptcha($data['captcha'],true))
				$e[]='Код с капчи введён неправильно';
		}

		if(!$data['email']&&!$data['tel'])
			$e[]='Телефон и email пустые';

		if(!$e){
			$this->setUserActions(17,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
			//параметры письма
			$message_data['data']['email']='info@formetoo.ru';
			$message_data['data']['email_feedback']=$data['email']?$data['email']:'noreply@formetoo.ru';
			$message_data['data']['name_feedback']=$data['name'];
			$message_data['data']['city']=$G['CITY']['m_info_city_url'];
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['subject']='Письмо с формы обратной связи';
			$message_data['data']['name_to']='ИМ formetoo';
			$msg='
				<p>'.$data['comment'].'</p>
				<p></p>
				<p>'.($data['name']?$data['name'].', контакты: ':'Контакты: ').($data['email']?'email: '.$data['email'].' ':'').($data['tel']?'тел.: '.$data['tel']:'').'</p>';
			$message_data['data']['message']=base64_encode($msg);

			if(message::addQueueEmail($message_data))
				header('Location: /about/feedback/?send=success');
			else{
				header('Location: /about/feedback/?send=error');
				exit;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /about/feedback/?send=error');
			exit;
		}
	}

	public function feedbackReturn(){
		global $sql,$e,$G;

		$data['token']=array(1,null,null,32);
		$data['captcha']=array(null,5,7);
		$data['name']=array(1,null,180);
		$data['email']=array(1,null,null,null,4);
		$data['order']=array(1,null,null,10,1);
		$data['tel']=array(1,null,null,16,2);
		$data['address']=array(1,null,500);
		$data['comment']=array(1,null,4500);
		$data['politic']=array(1,null,null,1,1);
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		$data['politic']=$data['politic']?1:0;
		if(!$data['politic'])
			$e[]='Не принята оферта и политика конфиденциальности';

		//читаем капчу, если превышен лимит изменений настроек профиля
		$uact=$this->getUserActions();
		if(isset($uact[18])&&sizeof($uact[18])>5){
			if(!$this->checkCaptcha($data['captcha'],true))
				$e[]='Код с капчи введён неправильно';
		}
		else $this->setUserActions(18,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		if(!$e){
			//параметры письма
			$message_data['data']['email']='dzaleskin@gmail.com';
			$message_data['data']['email_feedback']=$data['email'];
			$message_data['data']['name_feedback']=$data['name'];
			$message_data['data']['city']=$G['CITY']['m_info_city_url'];
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['subject']='Завяление на возврат';
			$message_data['data']['name_to']='ИМ formetoo';
			$message_data['data']['order']=$data['order'];
			$message_data['data']['address']=$data['address'];
			$msg='
				<p>'.$data['comment'].'</p>
				<p></p>
				<p>Заказ № '.$data['order'].'</p>
				<p></p>
				<p>'.$data['name'].', контакты: email: '.$data['email'].'тел.: '.$data['tel'].'</p>';
			$message_data['data']['message']=base64_encode($msg);

			if(message::addQueueEmail($message_data))
				header('Location: /info/return/?send=success');
			else{
				header('Location: /info/return/?send=error');
				exit;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /info/return/?send=error');
			exit;
		}
	}

	//подписка из футера
	public function subscribe(){
		global $sql,$e,$G;

		$data['token']=array(1,null,null,32);
		$data['email']=array(1,null,null,null,4);
		$data['politic']=array(1,null,null,1,1);
		array_walk($data,'check');

		if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token'];

		$data['politic']=$data['politic']?1:0;
		if(!$data['politic'])
			$e[]='Не приняты условия рассылки и политика конфиденциальности';

		//читаем капчу, если превышен лимит изменений настроек профиля
		$uact=$this->getUserActions();
		if(isset($uact[19])&&sizeof($uact[19])>1){
			$e[]='Превышен лимит подписок';
		}
		else $this->setUserActions(19,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		if(!$e){
			//если кнопка подписки в футере нажата из ЛК
			if($this->getInfo('m_users_name')){
				$q='UPDATE `formetoo_main`.`m_users` SET `m_users_accept_newsletter_email`=1 WHERE `m_users_id`='.$this->getInfo().' LIMIT 1;';
				if($sql->query($q)){
					if(!$this->getInfo('m_users_email_confirm'))
						$this->reSendConfirmEmail();
					return true;
				}
				else return false;
			}

			//если нажата незарегистрированным пользователем - делаем регистрацию
			session_name('ut');
			session_start();
			$_POST['captcha']=$_SESSION['code']='captss';
			$_POST['name']=array_shift(explode('@',$data['email']));
			$_POST['email']=$data['email'];;
			$_POST['password']=$_POST['autopass']=get_pass();
			$_POST['jur']=0;
			$_POST['nds']=0;
			$_POST['politic']=$data['politic'];
			$_POST['newsletter']=1;
			$G['CITY']['m_info_city_url']=explode('.',$G['SUBDOMAIN']);
			$G['CITY']['m_info_city_url']=array_shift($G['CITY']['m_info_city_url']);
			if($res=$this->register())
				return $res;
			else{
				return false;
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			return false;
		}
	}

	public function getConfirmEmailCode(){
		global $sql,$G;

		$q='SELECT `m_users_email_confirm_code` FROM `formetoo_main`.`m_users` WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
		if($res=$sql->query($q))
			return $res[0]['m_users_email_confirm_code'];
		return null;
	}
	public function getConfirmTelCode(){
		global $sql,$G;

		$q='SELECT `m_users_tel_confirm_code` FROM `formetoo_main`.`m_users` WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
		if($res=$sql->query($q))
			return $res[0]['m_users_tel_confirm_code'];
		return null;
	}

	public function crawlerDetect(){
		global $sql;

		$block_all=0;
		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			$options = array(
				'YandexDirectDyn','YandexImages', 'YandexVideo', 'YandexVideoParser',
				'YandexMedia', 'YandexBlogs', 'YandexFavicons', 'YandexWebmaster',
				'YandexPagechecker', 'YandexImageResizer','YandexAdNet', 'YandexDirect',
				'YaDirectFetcher', 'YandexCalendar', 'YandexSitelinks', 'YandexMetrika',
				'YandexNews', 'YandexNewslinks', 'YandexCatalog', 'YandexAntivirus',
				'YandexMarket', 'YandexVertis', 'YandexForDomain', 'YandexSpravBot',
				'YandexSearchShop','YandexOntoDB', 'YandexOntoDBAPI',
				'Mediapartners-Google','Accoona', 'ia_archiver', 'Ask Jeeves',
				'W3C_Validator', 'WebAlta', 'YahooFeedSeeker','Ezooms','SiteStatus',
				'Nigma.ru', 'Baiduspider','SISTRIX','findlinks',
				'proximic', 'OpenindexSpider','statdom.ru','Spider','Snoopy', 'heritrix', 'Yeti',
				'DomainVader','Cuam', 'Zoo Tycoon 2 Client', 'JoBo', 'portalmmm',
				'Validator', 'GuzzleHttp', 'Scrapy', 'zgrab', 'ZipCommander', 'Activeworlds', 'AmigaVoyager', 'Aplix_SEGASATURN_browser',
				'Dillo', 'Hotzonu', 'JetBrains', '1Gold', 'HTTrack', 'Sylera', 'DreamPassport', 'ActiveBookmark', 'Advanced Browser',
				'Amiga-AWeb', 'Apache-HttpClient', 'Aplix_SEGASATURN', 'bluefish', 'Commerce Browser', 'eCatch',
				'ELinks', 'endo', 'Go 1.1 package http', 'iCab', 'iSiloX','dotnetdotcom','domaintools.com','liveinternet.ru','xml-sitemaps.com','agama','metadatalabs.com','h1.hrn.ru',
				'scoutjet','similarpages','shrinktheweb.com','followsite.com','dataparksearch','google-sitemaps',
				'appEngine-google','feedfetcher-google','megadownload.net','askpeter.info','igde.ru','ask.com','yanga.co.uk',
				'rambler','aport','yahoo','turtle','mail.ru','omsktele','picsearch','sape_context','alexa.com','NetcraftSurveyAgent','bot','crawler'
			);
			$options_block_all = array(
				'AhrefsBot','MJ12bot', 'Detectify', 'dotbot', 'Riddler', 'SemrushBot', 'LinkpadBot', 'BLEXBot',
				'FlipboardProxy', 'aiHitBot','trovitBot'
			);
			foreach($options_block_all as $row) {
				if (stripos($_SERVER['HTTP_USER_AGENT'],$row)!==false) {
					$block_all=1;
					return true;
				}
			}
			foreach($options as $row) {
				if (stripos($_SERVER['HTTP_USER_AGENT'],$row)!==false) {
					$this->isCrawler=true;
					return true;
				}
			}
		}
		else{
			$this->isCrawler=true;
			return true;
		}

		//выделяем диапазоны ip вида 0.0.*.* и 0.0.0.*
		$ip_d_0=explode('.',$this->ip);
		$id_d_4=$ip_d_0[0].'.'.$ip_d_0[1].'.'.$ip_d_0[2].'.*';
		$id_d_3=$ip_d_0[0].'.'.$ip_d_0[1].'.*.*';
		//ищем в блеклисте ip или его диапазон, сортируя по актуальности
		$q='SELECT * FROM `formetoo_cdb`.`m_info_ip_blacklist` WHERE
			`m_info_ip_blacklist_ipv4`=\''.$this->ip.'\' OR
			`m_info_ip_blacklist_ipv4`=\''.$id_d_4.'\' OR
			`m_info_ip_blacklist_ipv4`=\''.$id_d_3.'\'
			ORDER BY `m_info_ip_blacklist_id` DESC LIMIT 1;';
		$res=$sql->query($q);
		//если для ip стоит полный блок, показываем ему 403 страницу
		if($res&&$res[0]['m_info_ip_blacklist_rule_block_all']){
			require_once(__DIR__.'/../www/403.php');
			exit;
		}
		//если блок по cookies, возврпщаем результат проверки
		elseif($res&&$res[0]['m_info_ip_blacklist_rule_block_cookies']){
			$this->isCrawler=true;
			return true;
		}
		//добавляем в исключения ip, с которых авторегистрация срабатывала 10 раз за 10 минут
		$q='SELECT `cookies_id` FROM `formetoo_main`.`cookies` WHERE
			`cookies_ip`=\''.$this->ip.'\' AND
			`cookies_ip`!=\'127.0.0.1\' AND
			`cookies_date`>\''.dtc(dtc(dt(),'-10 min'),'+30 day').'\'
			LIMIT 10;';
		if($res=$sql->query($q)){
			if(sizeof($res)>=10&&$this->ip!='127.0.0.1'){
				$sql->query('INSERT INTO `formetoo_cdb`.`m_info_ip_blacklist` SET
					`m_info_ip_blacklist_ipv4`=\''.user_info::ip().'\',
					`m_info_ip_blacklist_rule_block_cookies`=1,
					`m_info_ip_blacklist_rule_block_all`='.$block_all.';');
				$this->isCrawler=true;
				return true;
			}
		}

		$this->isCrawler=false;
		return false;
	}

	public function login($ref=false){
		global $sql,$e,$G,$order;

		//$data['token']=array(1,null,null,32);
		$data['captcha']=array(null,5,7);
		$data['from_cart']=array(null,null,null,1,1);
		$data['email']=array(null,null,null,null,4);
		$data['tel']=array(null,null,null,16,2);
		$data['password']=array(1,null,50);
		array_walk($data,'check');

		$password=$data['password'];
		$data['password']='*****';

		/* if ($data['token']!=$this->info['cookies_token'])
			$e[]='Неправильный идентификатор формы cookies_token='.$this->info['cookies_token'].', site_token='.$data['token']; */

		$uact=$this->getUserActions();
		if(isset($uact[7])&&sizeof($uact[7])>5){
			if(!$this->checkCaptcha($data['captcha'],true))
				$e[]='Код с капчи введён неправильно';
		}
		else $this->setUserActions(7,json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

		if(!$data['email']&&!$data['tel'])
			$e[]='Телефон и email пустые';

		if(!$e){
			if($data['email'])
				$q='SELECT * FROM `formetoo_main`.`m_users` WHERE
				`m_users_email`=\''.$data['email'].'\'
				LIMIT 1;';
			if($data['tel'])
				$q='SELECT * FROM `formetoo_main`.`m_users` WHERE
				`m_users_tel`=\''.$data['tel'].'\'
				LIMIT 1;';
			if($res=$sql->query($q)){
				$res=$res[0];
				if(!$res['m_users_active']){
					$e[]='Пользователь заблокирован';
					elogs();
					header('Location: /login/?auth=error_block');
				}
				else{
					if($this->verHash($password,$res['m_users_password'])){
						//передаём корзину залогиненному пользователю и удаляем временного пользователя
						$order->changeCartHolder($res['m_users_id']);
						$q='DELETE FROM `formetoo_main`.`m_users` WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
						$sql->query($q);

						//устанавливаем cookie залогиненного пользователя
						$q='UPDATE `formetoo_main`.`cookies` SET
							`cookies_m_users_id`='.$res['m_users_id'].'
							WHERE `cookies_cookie`=\''.$this->info['cookies_cookie'].'\' LIMIT 1;';
						if($sql->query($q)){
							if($data['from_cart']) header('Location: /my/orders/');
							else header($ref?'Location: '.$_SERVER['HTTP_REFERER']:'Location: /');
						}
						else{
							elogs(__FILE__,__FUNCTION__,$data);
							header('Location: /login/?auth=error');
						}
					}
					else{
						$e[]='Неверный пароль';
						elogs(__FILE__,__FUNCTION__,$data);
						header('Location: /login/?auth=error_password');
					}
				}
			}
			else{
				$e[]='Неверный логин';
				elogs(__FILE__,__FUNCTION__,$data);
				header('Location: /login/?auth=error_login');
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: /login/?auth=error');
		}
		exit;
	}

	public function logout(){
		global $sql;

		//выход из аккаунта
		$q='DELETE FROM `formetoo_main`.`cookies` WHERE `cookies_cookie`=\''.$this->info['cookies_cookie'].'\';';
		$sql->query($q);
		//удаляем cookie
		setcookie('uid','',time()-259200,'/','.'.$_SERVER['SERVER_NAME'],true,true);
		header('Location: /');
		exit;
	}

	public function logoutAll(){
		global $sql;

		//выход из аккаунта
		$q='DELETE FROM `formetoo_main`.`cookies` WHERE `cookies_m_users_id`=\''.$this->getInfo('m_users_id').'\';';
		$sql->query($q);
		//удаляем cookie
		setcookie('uid','',time()-259200,'/','.'.$_SERVER['SERVER_NAME'],true,true);
		header('Location: /');
		exit;
	}

	public static function groupAdd(){
		global $sql,$e;
		$data['m_users_groups_name']=array(1,null,80);
		$data['m_users_groups_rights_read[]']=array();
		$data['m_users_groups_rights_change[]']=array();
		$data['m_users_groups_rights_delete[]']=array();
		$data['m_users_groups_rights_create[]']=array();
		$data['m_users_groups_rights_myself[]']=array();

		array_walk($data,'check');

		if(!$e){
			$data['m_users_groups_id']=get_id('m_users_groups');

			$q='INSERT `formetoo_main`.`m_users_groups` SET
				`m_users_groups_id`='.$data['m_users_groups_id'].',
				`m_users_groups_name`=\''.$data['m_users_groups_name'].'\',
				`m_users_groups_rights_read`=\''.($data['m_users_groups_rights_read[]']?implode('|',$data['m_users_groups_rights_read[]']):'').'\',
				`m_users_groups_rights_change`=\''.($data['m_users_groups_rights_change[]']?implode('|',$data['m_users_groups_rights_change[]']):'').'\',
				`m_users_groups_rights_delete`=\''.($data['m_users_groups_rights_delete[]']?implode('|',$data['m_users_groups_rights_delete[]']):'').'\',
				`m_users_groups_rights_create`=\''.($data['m_users_groups_rights_create[]']?implode('|',$data['m_users_groups_rights_create[]']):'').'\',
				`m_users_groups_rights_myself`=\''.($data['m_users_groups_rights_myself[]']?implode('|',$data['m_users_groups_rights_myself[]']):'').'\';';

			if($sql->query($q))
				header('Location: '.url().'?success');
			else{
				elogs(__FILE__,__FUNCTION__,$data);
				header('Location: '.url().'?error');
			}
		}
		else{
			elogs(__FILE__,__FUNCTION__,$data);
			header('Location: '.url().'?error');
		}
		exit;
	}

	public function getGroups(){
		global $sql,$e;
		return $sql->query('SELECT * FROM `formetoo_main`.`m_users_groups`;','m_users_groups_id');
	}

	public function getUsers(){
		global $sql,$e;
		return $sql->query('SELECT * FROM `formetoo_main`.`m_users`;','m_users_id');
	}


	//отзывы и оценки пользователя
	public function getReviews(){
		global $sql;

		$q='SELECT * FROM `formetoo_main`.`m_products_feedbacks` WHERE `m_products_feedbacks_users_id`='.$this->getInfo().';';
		if($res=$sql->query($q))
			return $res;
		return null;
	}
	//подтверждение e-mail и телефона
	public function confirmEmail($code){
		global $sql;
		//попытки ввода
		$uact=$this->getUserActions();
		if(isset($uact[11])&&sizeof($uact[11])>5){
			$e[]='Превышено число попыток подтверждения e-mail';
			elogs();
			return false;
		}
		else{
			$this->setUserActions(11,$code);
			$q='SELECT `m_users_id` FROM `formetoo_main`.`m_users` WHERE `m_users_email_confirm_code`=\''.$code.'\' LIMIT 1;';
			if($sql->query($q)){
				$q='UPDATE `formetoo_main`.`m_users` SET `m_users_email_confirm`=1 WHERE `m_users_email_confirm_code`=\''.$code.'\' LIMIT 1;';
				if($sql->query($q))
					return true;
			}
			else return false;
		}

	}
	public function confirmTel($code){
		global $sql;
		//попытки ввода
		$uact=$this->getUserActions();
		if(isset($uact[12])&&sizeof($uact[12])>5){
			$e[]='Превышено число попыток подтверждения телефона';
			elogs();
			return false;
		}
		else{
			$this->setUserActions(12,$code);
			if($this->info['m_users_tel_confirm_code']==$code){
				$q='UPDATE `formetoo_main`.`m_users` SET `m_users_tel_confirm`=1 WHERE `m_users_tel_confirm_code`='.$code.' AND `m_users_id`='.$this->getInfo().' LIMIT 1;';
				if($sql->query($q))
					return true;
			}
			else return false;
		}

	}

	//отправка EMAIL оповещения оформления заказа
	public function sendNewOrderEmail($doc_id){
		global $sql,$G,$order;

		$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$doc_id.' LIMIT 1;';
			$data_doc=$sql->query($q);

		if($this->info['m_users_email']&&$data_doc){
			$data_doc=$data_doc[0];
			$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\''.explode('.',$G['SUBDOMAIN'])[0].'\' LIMIT 1;','m_info_city_url');
			foreach($area_list as &$_area)
				$_area=$_area[0];
			$G['CITY']=$area_list[explode('.',$G['SUBDOMAIN'])[0]];

			//состав заказа
			$data_doc_params=json_decode($data_doc['m_documents_params']);
			$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_id`='.$data_doc['m_documents_order'].' LIMIT 1;';
			$data_order=$sql->query($q)[0];
			$data_order_tel=json_decode($data_order['m_orders_contacts']);
			$data_order_tel_=array();
			foreach($data_order_tel as $_tel)
				$data_order_tel_[]=$_tel->tel_numb.($_tel->tel_comment?' ('.$_tel->tel_comment.')':'');
			if($data_order_tel_)
				$data_order_tel=implode(', ',$data_order_tel_);
			if($data_order['m_orders_delivery_type']!=1)
				if($data_order_address=json_decode($data_order['m_orders_address']))
					$data_order_address=$data_order_address->m_address_full;
			//товары
			$data_cart=$order->getCart();
			$data_prod=array();
			foreach($data_cart->items as $_item)
				$data_prod[]=$_item->product_id;
			$q='SELECT `m_products_id`,`slug`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `m_products_id` IN('.implode(',',$data_prod).');';
			$data_prod=$sql->query($q,'m_products_id');
			//ед. измерения
			$data_units=array();
			foreach($data_prod as $_prod)
				$data_units[]=$_prod[0]['m_products_unit'];
			$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');
			//контрагент
			$data_contr=$this->getUserContragents(false);

			//параметры письма
			$message_data['data']['email']=$this->info['m_users_email'];
			$message_data['data']['city']=$G['CITY']['m_info_city_url'];
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['subject']='Заказ № '.$data_doc['m_documents_order'].' в интернет-магазине formetoo.ru';
			$message_data['data']['name_to']=$this->info['m_users_name']?$this->info['m_users_name']:$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_p_fio'];

			//письмо
			$msg='
				<table width="100%">
					<tr>
						<td width="100%" style="text-align:left;">
							<h1>Спасибо за заказ!</h1>
						</td>
					</tr>
					<tr>
						<td width="100%" style="text-align:left">
							<p>Вы успешно оформили заказ № <strong>'.$data_doc['m_documents_order'].'</strong> в интернет-магазине formetoo.ru.</p>
							<p>По указанным контактам с Вами свяжется наш сотрудник для подтверждения заказа. Ссылка для оплаты онлайн или счёт на оплату будет отправлен Вам автоматически после подтверждения заказа.</p>
							<p>Состав заказа:</p>
							<table class="info mini" width="100%">
								<tr>
									<td class="name" style="border-right:1px solid #ddd;" width="2%">№</td>
									<td class="name" style="border-right:1px solid #ddd;" width="5%">Арт.</td>
									<td class="name" style="border-right:1px solid #ddd;" width="61%">Наименование</td>
									<td class="name" style="border-right:1px solid #ddd;" width="10%">Цена</td>
									<td class="name" style="border-right:1px solid #ddd;" width="4%">Кол-во</td>
									<td class="name" style="border-right:1px solid #ddd;" width="6%"><nobr>Ед. изм.</nobr></td>
									<td class="name" width="12%">Сумма</td>
								</tr>';
				foreach($data_cart->items as $k=>$_item){
					$msg.='
								<tr>
									<td class="center" style="border-right:1px solid #ddd;">'.($k+1).'</td>
									<td style="border-right:1px solid #ddd;">'.$_item->product_id.'</td>
									<td class="left" style="border-right:1px solid #ddd;">'.$data_prod[$_item->product_id][0]['m_products_name_full'].'</td>
									<td class="right" style="border-right:1px solid #ddd;">'.transform::price_o($_item->product_price,true,true).'&nbsp;₽</td>
									<td class="center" style="border-right:1px solid #ddd;">'.transform::price_o($_item->product_count,true,true).'</td>
									<td class="center" style="border-right:1px solid #ddd;">'.$data_units[$data_prod[$_item->product_id][0]['m_products_unit']][0]['m_info_units_name'].'</td>
									<td class="right">'.transform::price_o(round($_item->product_price*$_item->product_count,2),true,true).'&nbsp;₽</td>
								</tr>';

				}
				$msg.=			'<tr>
									<td class="name" style="border-right:1px solid #ddd;text-align:right;" colspan="6">Итого'.($data_order['m_orders_delivery_type']!=1?' (без учёта доставки)':'').':</td>
									<td class="right">'.transform::price_o($data_cart->sum,true,true).'&nbsp;₽</td>
								</tr>
							</table>
							<p>Выбранный метод оплаты: <strong>'.($data_order['m_orders_pay_method']==2?'счёт на оплату для '.($data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']?$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_c_name_short']:$data_contr['contragents'][$data_order['m_orders_customer']][0]['m_contragents_p_fio']):'онлайн, банковской картой на сайте.').'</strong></p>
							<p>Доставка: <strong>'.($data_order['m_orders_delivery_type']==1?'самовывоз':'доставить по адресу: '.$data_order_address).'</strong></p>
							<p>Контактный телефон: <strong>'.$data_order_tel.'</strong></p>
							<p></p>
							<p>Мы всегда рады помочь Вам в выборе строительных материалов. Задайте вопрос через обратную связь, электронную почту или позвоните нам. Наши контакты в '.$G['CITY']['m_info_city_name_city_pr'].':</p>
							<p>телефон: <strong>'.$G['CITY']['m_info_city_tel_office'].'</strong><br/>email: <strong><a href="mailto:'.$G['CITY']['m_info_city_mail'].'">'.$G['CITY']['m_info_city_mail'].'</a></strong></p>
							<p class="last">С уважением, formetoo.ru</p>
						</td>
					</tr>
				</table>';
			$message_data['data']['message']=base64_encode($msg);

			message::addQueueEmail($message_data);

		}
		return false;
	}

	//отправка SMS оповещения оформления заказа
	public function sendNewOrderSMS($doc_id){
		global $sql,$G,$order;

		$q='SELECT * FROM `formetoo_cdb`.`m_documents` WHERE `m_documents_id`='.$doc_id.' LIMIT 1;';
			$data_doc=$sql->query($q);

		if($this->info['m_users_tel']&&$data_doc){
			$data_doc=$data_doc[0];
			$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\''.explode('.',$G['SUBDOMAIN'])[0].'\' LIMIT 1;','m_info_city_url');
			foreach($area_list as &$_area)
				$_area=$_area[0];
			$G['CITY']=$area_list[explode('.',$G['SUBDOMAIN'])[0]];

			//состав заказа
			$data_doc_params=json_decode($data_doc['m_documents_params']);
			$q='SELECT * FROM `formetoo_cdb`.`m_orders` WHERE `m_orders_id`='.$data_doc['m_documents_order'].' LIMIT 1;';
			$data_order=$sql->query($q)[0];
			$data_order_tel=json_decode($data_order['m_orders_contacts']);
			$data_order_tel_=array();
			foreach($data_order_tel as $_tel)
				$data_order_tel_[]=$_tel->tel_numb.($_tel->tel_comment?' ('.$_tel->tel_comment.')':'');
			if($data_order_tel_)
				$data_order_tel=implode(', ',$data_order_tel_);
			if($data_order['m_orders_delivery_type']!=1)
				if($data_order_address=json_decode($data_order['m_orders_address']))
					$data_order_address=$data_order_address->m_address_full;
			//товары
			$data_cart=$order->getCart();
			$data_prod=array();
			foreach($data_cart->items as $_item)
				$data_prod[]=$_item->product_id;
			$q='SELECT `m_products_id`,`slug`,`m_products_name_full`,`m_products_unit` FROM `formetoo_main`.`m_products` WHERE `m_products_id` IN('.implode(',',$data_prod).');';
			$data_prod=$sql->query($q,'m_products_id');
			//ед. измерения
			$data_units=array();
			foreach($data_prod as $_prod)
				$data_units[]=$_prod[0]['m_products_unit'];
			$q='SELECT * FROM `formetoo_cdb`.`m_info_units` WHERE `m_info_units_id` IN('.implode(',',$data_units).');';
			$data_units=$sql->query($q,'m_info_units_id');

			//параметры письма
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['tel']=$this->info['m_users_tel'];

			//письмо
			$message_data['data']['message']='Заказ №'.$data_doc['m_documents_order'].' на сумму '.transform::price_o($data_cart->sum,true).' ₽ успешно оформлен. Мы с Вами свяжемся для подтверждения заказа. Спасибо за сотрудничество!';

			message::addQueueSMS($message_data);

		}
		return false;
	}

	//отправка кодов подтверждения e-mail и телефона
	public function sendConfirmEmail($autopass=false){
		global $sql,$G;

		if($this->info['m_users_email']){
			$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\''.explode('.',$G['SUBDOMAIN'])[0].'\' LIMIT 1;','m_info_city_url');
			foreach($area_list as &$_area)
				$_area=$_area[0];
			$G['CITY']=$area_list[explode('.',$G['SUBDOMAIN'])[0]];

			$data['m_users_email_confirm_code']=md5(rand(10000,99999).$this->info['cookies_m_users_id'].dt());
			$message_data['data']['email']=$this->info['m_users_email'];
			$message_data['data']['city']=$G['CITY']['m_info_city_url'];
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['subject']='Подтверждение регистрации в интернет-магазине formetoo.ru';
			$message_data['data']['name_to']=$this->info['m_users_name'];
			//проверяем, привязаны ли к пользователю контрагенты-юрлица, если да - берём первого из них
			$contr=$this->getUserContragents();
			if($contr&&$contr['contragents'][0]['m_contragents_type']==2){
				$data_contr=$contr['contragents'][0];
				$data_addr=$contr['addresses'][$contr['contragents'][0]['m_contragents_id']][0];
			}

			$message_data['data']['message']=base64_encode('
				<table width="100%">
					<tr>
						<td width="100%" style="text-align:left;">
							<h1>Спасибо за регистрацию!</h1>
						</td>
					</tr>
					<tr>
						<td width="100%" style="text-align:left">
							<p>Вы успешно зарегистрировались в интернет-магазине formetoo.ru.</p>
							<p><strong>Для подтверждения вашей электронной почты, пожалуйста, перейдите по ссылке ниже:</strong></p>
							<p><strong><a href="https://'.$G['CITY']['m_info_city_url'].'.formetoo.ru/my/profile/?email_confirm='.$data['m_users_email_confirm_code'].'">https://'.$G['CITY']['m_info_city_url'].'.formetoo.ru/my/profile/?email_confirm='.$data['m_users_email_confirm_code'].'</a></strong></p>
							'.($data_contr?'<p>За Вашем аккаунтом закреплён слудующий контрагент:</p>
							<table class="info" width="100%">
								<tr>
									<td class="name" style="border-right:1px solid #ddd;">Наименование</td>
									<td class="value">'.($data_contr['m_contragents_c_name_short']?$data_contr['m_contragents_c_name_short']:$data_contr['m_contragents_c_name_full']).'</td>
								</tr>
								<tr>
									<td class="name">Юридич. адрес</td>
									<td class="value">'.$data_addr['m_address_full'].'</td>
								</tr>
								<tr>
									<td class="name">ИНН</td>
									<td class="value">'.$data_contr['m_contragents_c_inn'].'</td>
								</tr>'.($data_contr['m_contragents_c_kpp']?'
								<tr>
									<td class="name">КПП</td>
									<td class="value">'.$data_contr['m_contragents_c_kpp'].'</td>
								</tr>':'').'
								<tr>
									<td class="name">ОГРН</td>
									<td class="value">'.$data_contr['m_contragents_c_ogrn'].'</td>
								</tr>
								<tr>
									<td class="name">Руководитель</td>
									<td class="value">'.$data_contr['m_contragents_c_director_post'].' '.$data_contr['m_contragents_c_director_name'].'</td>
								</tr>
							</table>
							':'').'
							'.($autopass?'<p>Мы установили временный пароль для входа в личный кабинет (Вы можете сменить его в настройках профиля): <strong>'.$autopass.'</strong></p>':'').'
							<p>Мы всегда рады помочь Вам в выборе строительных материалов. Задайте вопрос через обратную связь, электронную почту или позвоните нам. Наши контакты в '.$G['CITY']['m_info_city_name_city_pr'].':</p>
							<p>телефон: <strong>'.$G['CITY']['m_info_city_tel_office'].'</strong><br/>email: <strong><a href="mailto:'.$G['CITY']['m_info_city_mail'].'">'.$G['CITY']['m_info_city_mail'].'</a></strong></p>
							<p class="last">С уважением,<br/>интернет-магазин formetoo.</p>
						</td>
					</tr>
				</table>
			');

			$q='UPDATE `formetoo_main`.`m_users` SET
				`m_users_email_confirm`=0,
				`m_users_email_confirm_code`=\''.$data['m_users_email_confirm_code'].'\'
				WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
			if($sql->query($q)){
				message::addQueueEmail($message_data);
				return true;
			}
		}
		return false;
	}
	public function sendConfirmTel(){
		global $sql;

		if($this->info['m_users_tel']){
			$data['m_users_tel_confirm_code']=rand(10000,99999);
			$message_data['data']['tel']=$this->info['m_users_tel'];
			$message_data['user_id']=$this->info['m_users_id'];
			$message_data['data']['message']=$data['m_users_tel_confirm_code'].' — код подтверждения для регистрации на сайте formetoo.ru';
			message::addQueueSMS($message_data);

			$q='UPDATE `formetoo_main`.`m_users` SET
				`m_users_tel_confirm`=0,
				`m_users_tel_confirm_code`='.$data['m_users_tel_confirm_code'].'
				WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
			if($sql->query($q))
				return true;
		}
		return false;
	}

	//отправка кодов подтверждения e-mail и телефона
	public function reSendConfirmEmail(){
		global $sql,$G;

		if($this->info['m_users_email']){
			$area_list=$sql->query('SELECT * FROM `formetoo_main`.`m_info_city` WHERE `m_info_city_url`=\''.explode('.',$G['SUBDOMAIN'])[0].'\' LIMIT 1;','m_info_city_url');
			foreach($area_list as &$_area)
				$_area=$_area[0];
			$G['CITY']=$area_list[explode('.',$G['SUBDOMAIN'])[0]];

			$data['m_users_email_confirm_code']=md5(rand(10000,99999).$this->info['cookies_m_users_id'].dt());
			$message_data['data']['email']=$this->info['m_users_email'];
			$message_data['data']['city']=$G['CITY']['m_info_city_url'];
			$message_data['user_id']=$this->info['cookies_m_users_id'];
			$message_data['data']['subject']='Подтверждение электронной почты в интернет-магазине formetoo.ru';
			$message_data['data']['name_to']=$this->info['m_users_name'];

			$message_data['data']['message']=base64_encode('
				<table width="100%">
					<tr>
						<td width="100%" style="text-align:left;">
							<h1>Смена электронной почты</h1>
						</td>
					</tr>
					<tr>
						<td width="100%" style="text-align:left">
							<p>Электронная почта Вашего аккаунта: '.$this->info['m_users_email'].'.</p>
							<p><strong>Для подтверждения электронной почты, пожалуйста, перейдите по ссылке ниже:</strong></p>
							<p><strong><a href="https://'.$G['CITY']['m_info_city_url'].'.formetoo.ru/my/profile/?email_confirm='.$data['m_users_email_confirm_code'].'">https://'.$G['CITY']['m_info_city_url'].'.formetoo.ru/my/profile/?email_confirm='.$data['m_users_email_confirm_code'].'</a></strong></p>
							<p>Мы всегда рады помочь Вам в выборе строительных материалов. Задайте вопрос через обратную связь, электронную почту или позвоните нам. Наши контакты в '.$G['CITY']['m_info_city_name_city_pr'].':</p>
							<p>телефон: <strong>'.$G['CITY']['m_info_city_tel_office'].'</strong><br/>email: <strong><a href="mailto:'.$G['CITY']['m_info_city_mail'].'">'.$G['CITY']['m_info_city_mail'].'</a></strong></p>
							<p class="last">С уважением,<br/>интернет-магазин formetoo.</p>
						</td>
					</tr>
				</table>
			');

			$q='UPDATE `formetoo_main`.`m_users` SET
				`m_users_email_confirm`=0,
				`m_users_email_confirm_code`=\''.$data['m_users_email_confirm_code'].'\'
				WHERE `m_users_id`='.$this->info['m_users_id'].' LIMIT 1;';
			if($sql->query($q)){
				message::addQueueEmail($message_data);
				return true;
			}
		}
		return false;
	}


}

?>
