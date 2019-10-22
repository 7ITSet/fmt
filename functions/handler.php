<?
defined ('_DSITE') or die ('Access denied');

$handler=post('handler');

if($handler)
	switch($handler){
		case 'user_registration':
			$user->register();
			break;
		case 'user_authorization':
			$user->login();
			break;
		case 'user_change_subscriptions':
			$user->changeUserSubscriptions();
			break;
		case 'user_change_account':
			$user->changeUserAccount();
			break;
		case 'user_change_password':
			$user->changeUserPassword();
			break;
		case 'user_feedback':
			$user->feedback();
			break;
		case 'user_feedbackReturn':
			$user->feedbackReturn();
			break;
		case 'user_review_im':
			$user->addReview(1);
			break;
		case 'user_subscribe':
			$user->subscribe();
			break;	
		case 'order_new':
			$order->create();
			break;	
	}
?>