<?php
	class Header extends CWidget 
	{
		public function run() 
		{
			$currentLoginState = new AccountLoginState;
			
			$loginState = '';

			if( !Yii::app()->user->isGuest )
			{
				$authAccount = Yii::app()->user->account;

				if( (isset($authAccount->accountUser) && $authAccount->accountUser->salary_type == 'HOURLY') || $authAccount->getIsHostDialer() )
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
						if( $currentLoginState->type == AccountLoginState::TYPE_AVAILABLE )
						{
							$loginState = '<i class="ace-icon fa fa-circle light-green"></i>';
						}
						else
						{
							$loginState = '<i class="ace-icon fa fa-circle light-red"></i>';
						}
					}
				}
			}
		
			$this->render('header', array(
				'currentLoginState' => $currentLoginState,
				'loginState' => $loginState,
			));
		}
	} 
?>