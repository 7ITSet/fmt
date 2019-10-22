<?
defined ('_DSITE') or die ('Access denied');

class message{
	
	public static function addQueueSMS($data=null){
		global $sql,$e;
		
		if($data&&$data['user_id']&&$data['data']['tel']&&$data['data']['message']){
			$q='INSERT INTO `formetoo_main`.`m_users_messages` SET 
				`m_users_messages_user_id`='.$data['user_id'].',
				`m_users_messages_data`=\''.json_encode($data['data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\',
				`m_users_messages_type`=2,
				`m_users_messages_date`=\''.dt().'\';';
			if($sql->query($q))
				return true;
			else return null;
		}
		return null;
	}
	
	public static function addQueueEmail($data=null){
		global $sql,$e,$G;
		
		if($data&&$data['user_id']&&$data['data']['email']&&$data['data']['message']){
			
			$q='INSERT INTO `formetoo_main`.`m_users_messages` SET 
				`m_users_messages_user_id`='.$data['user_id'].',
				`m_users_messages_data`=\''.json_encode($data['data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'\',
				`m_users_messages_type`=1,
				`m_users_messages_date`=\''.dt().'\';';
			if($sql->query($q))
				return true;
			else return null;
		}
		return null;
	}
}
?>