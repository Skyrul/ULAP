<?php 

class NewsController extends Controller
{
	
	public function actionIndex()
	{
		if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$this->redirect(array('customer/data/index'));
		}
		
		if( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		{
			$this->redirect(array('/agent'));
		}
		
		$authAccount = Yii::app()->user->account;
		
		// $htmlNewsPosts = News::model()->findAll(array(
			// 'with' => 'hiddenSettings',
			// 'condition' => '
				// t.status = 1
				// AND t.type = 1 
				// AND (
					// hiddenSettings.id IS NULL
					// OR ( 
						// hiddenSettings.id IS NOT NULL
						// AND hiddenSettings.is_marked_hide = 0
						// AND hiddenSettings.account_id = :account_id
					// )
				// )
			// ',
			// 'params' => array(
				// ':account_id' => $authAccount->id,
			// ),
			// 'order' => 't.sort_order ASC',
			// 'limit' => 2
		// ));
		
		$htmlNewsPosts = array();
		
		$models = News::model()->findAll(array(
			'condition' => 't.status = 1 AND t.type = 1 AND t.date_created >= :account_date_created',
			'params' => array(
				':account_date_created' => $authAccount->date_created
			),
			// 'limit' => 2,
			'order' => 't.sort_order ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$existingSettings = NewsAccountSettings::model()->find(array(
					'condition' => 'account_id = :account_id AND news_id = :news_id',
					'params' => array(
						':account_id' => $authAccount->id,
						':news_id' => $model->id
					),
				));
				
				if( $existingSettings )
				{
					$newSettings = $existingSettings;
				}
				else
				{
					$newSettings = new NewsAccountSettings;
				}
				
				$newSettings->setAttributes(array(
					'account_id' => $authAccount->id,
					'news_id' => $model->id,
					'is_seen' => 1,
					'date_created' => date('Y-m-d H:i:s')
				));
				
				$newSettings->save(false);
				
				if( count($htmlNewsPosts) == 2 )
				{
					break;
				}
				
				$isHidden = NewsAccountSettings::model()->find(array(
					'condition' => 'news_id = :news_id AND account_id = :account_id AND is_marked_hide=1',
					'params' => array(
						'news_id' => $model->id,
						'account_id' => $authAccount->id
					),
				));
				
				if( empty($isHidden) )
				{
					$htmlNewsPosts[] = $model->attributes;
				}
			}
		}
		
		$this->render('index', array(
			'authAccount' => $authAccount,
			'htmlNewsPosts' => $htmlNewsPosts,
		));
	}

	public function actionClose()
	{
		if( Yii::app()->user->account->account_type_id == Account::TYPE_AGENT || Yii::app()->user->account->account_type_id == Account::TYPE_GRATON_AGENT || Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		{
			$this->redirect(array('/agent'));
		}
		else
		{
			$url = array('/news');
			$noPermission = true;
			
			if( Yii::app()->user->account->checkPermission('structure_main_tab','visible') )
			{
				$url = array('/admin/company/index');
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('accounting_main_tab','visible') )
			{
				$url = array('/accounting');
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('reports_main_tab','visible') )
			{
				$url = array('/reports');
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('employees_main_tab','visible') )
			{
				$url = array('/hr/accountUser/index');
				$noPermission = false;
			}
			
			if( Yii::app()->user->account->checkPermission('customers_main_tab','visible') )
			{
				$url = array('/customer/data/index');
				$noPermission = false;
			}
			
			if( $noPermission )
			{
				Yii::app()->user->setFlash('danger', 'Your security group has no access to other pages.');
			}
			
			$this->redirect($url);
		}
	}
	
	public function actionAjaxGetNews()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['fetch_type']) && isset($_POST['current_offset']) )
		{
			if( $_POST['fetch_type'] == 'next' )
			{
				$offset = $_POST['current_offset'] + 2;
			}
			else
			{
				$offset = $_POST['current_offset'] - 2;
			}
			
			if( $offset < 0 )
			{
				$offset = 0;
			}
			
			// $htmlNewsPosts = News::model()->findAll(array(
				// 'with' => 'hiddenSettings',
				// 'condition' => '
					// t.status = 1
					// AND t.type = 1 
					// AND (
						// hiddenSettings.id IS NULL
						// OR ( 
							// hiddenSettings.id IS NOT NULL
							// AND hiddenSettings.is_marked_hide = 0
							// AND hiddenSettings.account_id = :account_id
						// )
					// )
				// ',
				// 'params' => array(
					// ':account_id' => $authAccount->id,
				// ),
				// 'order' => 't.sort_order ASC',
				// 'limit' => 2,
				// 'offset' => $offset
			// ));
			
			$htmlNewsPosts = array();
		
			$models = News::model()->findAll(array(
				'condition' => 't.status = 1 AND t.type = 1 AND t.date_created >= :account_date_created',
				'order' => 't.sort_order ASC',
				'params' => array(
					':account_date_created' => $authAccount->date_created
				),
				// 'limit' => 2,
				'offset' => $offset,
			));
			
			if( $models )
			{
				foreach( $models as $model )
				{
					$existingSettings = NewsAccountSettings::model()->find(array(
						'condition' => 'account_id = :account_id AND news_id = :news_id',
						'params' => array(
							':account_id' => $authAccount->id,
							':news_id' => $model->id
						),
					));
					
					if( $existingSettings )
					{
						$newSettings = $existingSettings;
					}
					else
					{
						$newSettings = new NewsAccountSettings;
					}
					
					$newSettings->setAttributes(array(
						'account_id' => $authAccount->id,
						'news_id' => $model->id,
						'is_seen' => 1,
						'date_created' => date('Y-m-d H:i:s')
					));
					
					$newSettings->save(false);
					
					if( count($htmlNewsPosts) == 2 )
					{
						break;
					}
					
					if( isset($_POST['source']) && $_POST['source'] == 'main' )
					{
						$htmlNewsPosts[] = $model->attributes;
					}
					else
					{
						$isHidden = NewsAccountSettings::model()->find(array(
							'condition' => 'news_id = :news_id AND account_id = :account_id AND is_marked_hide=1',
							'params' => array(
								'news_id' => $model->id,
								'account_id' => $authAccount->id
							),
						));
						
						if( empty($isHidden) )
						{
							$htmlNewsPosts[] = $model->attributes;
						}
					}
				}
			}
			
			if( isset($_POST['source']) && $_POST['source'] == 'main' )
			{
				$page = 'ajax_news_entry_no_buttons';
			}
			else
			{
				$page = 'ajax_news_entry';
			}
			
			$html = $this->renderPartial($page, array(
				'authAccount' => $authAccount,
				'htmlNewsPosts' => $htmlNewsPosts,
				'offset' => $offset
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionAjaxMarkAsRead()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['current_news_id']))
		{
			$existingSettings = NewsAccountSettings::model()->find(array(
				'condition' => 'account_id = :account_id AND news_id = :news_id',
				'params' => array(
					':account_id' => $authAccount->id,
					':news_id' => $_POST['current_news_id']
				),
			));
			
			if( $existingSettings )
			{
				$newSettings = $existingSettings;
			}
			else
			{
				$newSettings = new NewsAccountSettings;
			}
			
			$newSettings->setAttributes(array(
				'account_id' => $authAccount->id,
				'news_id' => $_POST['current_news_id'],
				'is_marked_read' => 1,
				'date_created' => date('Y-m-d H:i:s')
			));
			
			if( $newSettings->save() )
			{
				$result['status'] = 'success';
			}
			else
			{
				$result['message'] = 'Databse error. Please try again later.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionAjaxHide()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['current_news_id']))
		{
			$existingSettings = NewsAccountSettings::model()->find(array(
				'condition' => 'account_id = :account_id AND news_id = :news_id',
				'params' => array(
					':account_id' => $authAccount->id,
					':news_id' => $_POST['current_news_id']
				),
			));
			
			if( $existingSettings )
			{
				$newSettings = $existingSettings;
			}
			else
			{
				$newSettings = new NewsAccountSettings;
			}
			
			$newSettings->setAttributes(array(
				'account_id' => $authAccount->id,
				'news_id' => $_POST['current_news_id'],
				'is_marked_hide' => 1,
				'date_created' => date('Y-m-d H:i:s')
			));
			
			if( $newSettings->save() )
			{
				$result['status'] = 'success';
			}
			else
			{
				$result['message'] = 'Databse error. Please try again later.';
			}
		}
		
		echo json_encode($result);
	}

	public function actionCheckNewPosts()
	{
		News::checkNewPosts();
	}
	
	public function actionMain()
	{
		if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$this->redirect(array('customer/data/index'));
		}
		
		if( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
		{
			$this->redirect(array('/agent'));
		}
		
		$authAccount = Yii::app()->user->account;
		
		// $htmlNewsPosts = News::model()->findAll(array(
			// 'with' => 'hiddenSettings',
			// 'condition' => '
				// t.status = 1
				// AND t.type = 1 
				// AND (
					// hiddenSettings.id IS NULL
					// OR ( 
						// hiddenSettings.id IS NOT NULL
						// AND hiddenSettings.is_marked_hide = 0
						// AND hiddenSettings.account_id = :account_id
					// )
				// )
			// ',
			// 'params' => array(
				// ':account_id' => $authAccount->id,
			// ),
			// 'order' => 't.sort_order ASC',
			// 'limit' => 2
		// ));
		
		$htmlNewsPosts = array();
		
		$models = News::model()->findAll(array(
			'condition' => 't.status = 1 AND t.type = 1 AND t.date_created >= :account_date_created',
			'params' => array(
				':account_date_created' => $authAccount->date_created
			),
			// 'limit' => 2,
			'order' => 't.sort_order ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$existingSettings = NewsAccountSettings::model()->find(array(
					'condition' => 'account_id = :account_id AND news_id = :news_id',
					'params' => array(
						':account_id' => $authAccount->id,
						':news_id' => $model->id
					),
				));
				
				if( $existingSettings )
				{
					$newSettings = $existingSettings;
				}
				else
				{
					$newSettings = new NewsAccountSettings;
				}
				
				$newSettings->setAttributes(array(
					'account_id' => $authAccount->id,
					'news_id' => $model->id,
					'is_seen' => 1,
					'date_created' => date('Y-m-d H:i:s')
				));
				
				$newSettings->save(false);
				
				if( count($htmlNewsPosts) == 2 )
				{
					break;
				}
				
				$isHidden = NewsAccountSettings::model()->find(array(
					'condition' => 'news_id = :news_id AND account_id = :account_id AND is_marked_hide=1',
					'params' => array(
						'news_id' => $model->id,
						'account_id' => $authAccount->id
					),
				));
				
				if( empty($isHidden) )
				{
					$htmlNewsPosts[] = $model->attributes;
				}
			}
		}
		
		$this->render('index', array(
			'authAccount' => $authAccount,
			'htmlNewsPosts' => $htmlNewsPosts,
		));
	}
	
	public function actionMain2()
	{
		if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$this->redirect(array('customer/data/index'));
		}
		
		$authAccount = Yii::app()->user->account;
		
		$htmlNewsPosts = array();
		
		$models = News::model()->findAll(array(
			'condition' => 't.status = 1 AND t.type = 1',
			// 'limit' => 2,
			'order' => 't.sort_order ASC',
		));
		
		if( $models )
		{
			foreach( $models as $model )
			{
				$existingSettings = NewsAccountSettings::model()->find(array(
					'condition' => 'account_id = :account_id AND news_id = :news_id',
					'params' => array(
						':account_id' => $authAccount->id,
						':news_id' => $model->id
					),
				));
				
				if( $existingSettings )
				{
					$newSettings = $existingSettings;
					
					$newSettings->setAttributes(array(
						'account_id' => $authAccount->id,
						'news_id' => $model->id,
						'is_seen' => 1,
					));

					$newSettings->save(false);
				}
				else
				{
					$newSettings = new NewsAccountSettings;
					
					$newSettings->setAttributes(array(
						'account_id' => $authAccount->id,
						'news_id' => $model->id,
						'is_seen' => 1,
						'date_created' => date('Y-m-d H:i:s')
					));

					$newSettings->save(false);
				}
			}
			
			foreach( $models as $model )
			{
				if( count($htmlNewsPosts) == 2 )
				{
					break;
				}
				
				$htmlNewsPosts[] = $model->attributes;
			}
		}
		
		$this->render('main', array(
			'htmlNewsPosts' => $htmlNewsPosts,
		));
	} 
}

?>