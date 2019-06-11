<?php 

class IdleController extends Controller
{
	public $layout='//layouts/agent_dialer';
		
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
			
		$currentLoginState = AccountLoginState::model()->find(array(
			'condition' => 'account_id = :account_id AND end_time IS NULL',
			'params' => array(
				':account_id' => $authAccount->id,
			),
			'order' => 'date_created DESC',
		));  
		
		if( $currentLoginState->type == 1 ) 
		{
			$this->redirect(array('/agent'));
		}
		
		$this->render('index', array(
			'authAccount' => $authAccount,
			'currentLoginState' => $currentLoginState,
		));
	}
	
	public function actionAjaxRelogin()
	{
		$authAccount = Yii::app()->user->account;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'is_host_dialer' => $authAccount->getIsHostDialer()
		);
		
		if( isset($_POST['ajax']) && !empty($_POST['password']) )
		{
			if( $_POST['password'] == $authAccount->password )
			{
				$currentLoginState = AccountLoginState::model()->find(array(
					'condition' => 'account_id = :account_id AND end_time IS NULL',
					'params' => array(
						':account_id' => $authAccount->id,
					),
					'order' => 'date_created DESC',
				));
				
				if( $currentLoginState )
				{
					$currentLoginState->end_time = date('Y-m-d H:i:s');
					
					
					if( $currentLoginState->type == 4) // LUNCH STATE
					{
						
						$currentLoginTracker = AccountLoginTracker::model()->find(array(
							'condition' => 'account_id = :account_id',
							'params' => array(
								':account_id' => $authAccount->id,
							),
							'order' => 'date_created DESC',
						));
				
						$accountLoginTracker = new AccountLoginTracker;
						$accountLoginTracker->account_id = $authAccount->id;
						$accountLoginTracker->time_in = date('Y-m-d H:i:s');
						$accountLoginTracker->login_session_token = $currentLoginTracker->login_session_token;
						$accountLoginTracker->type = $currentLoginTracker->type;
						$accountLoginTracker->status = $currentLoginTracker->status;
						$accountLoginTracker->save(false);
					}
					
					if( $currentLoginState->save(false) )
					{
						$newLoginState = new AccountLoginState;
						
						$newLoginState->setAttributes(array(
							'account_id' => $authAccount->id,
							'start_time' => date('Y-m-d H:i:s'),
							'type' => AccountLoginState::TYPE_AVAILABLE,
						));
						
						if( $newLoginState->save(false) )
						{
							$result['status'] = 'success';
							$result['message'] = 'Account login state has been updated.';
						}
					}
				}
			}
			else
			{
				$result['message'] = 'You have entered an invalid password.';
			}
		}
		else
		{
			$result['message'] = 'Password is required.';
		}
		
		echo json_encode($result);
	}
	
}

?>