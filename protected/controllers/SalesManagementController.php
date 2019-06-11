<?php 

class SalesManagementController extends Controller
{
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
		$authUser = $authAccount->accountUser;
		
		if( $authUser->job_title != "Manager" )
		{
			$this->redirect(array('/customer'));
		}
		
		// $models = CustomerEnrollment::model()->findAll(array(
			// 'condition' => 'sales_management_deleted = 0 AND date_created >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)',
			// 'order' => 'date_created DESC'
		// ));
		
		// $models = CustomerEnrollment::model()->findAll(array(
			// 'condition' => 'sales_management_deleted = 0 AND MONTH(date_created) = MONTH(CURDATE()) AND YEAR(date_created) = YEAR(CURDATE())',
			// 'order' => 'date_created DESC'
		// ));
		
		// $models = Account::model()->findAll(array(
			// 'condition' => 'sales_management_deleted = 0 AND MONTH(date_created) = MONTH(CURDATE()) AND YEAR(date_created) = YEAR(CURDATE())',
			// 'order' => 'date_created DESC'
		// ));
		
		$excludeCustomerSql = CustomerSkill::model()->removeFromSalesReports();
		
		$models = CustomerSkill::model()->findAll(array(
			'condition' => '
				MONTH(date_created) = MONTH(CURDATE()) 
				AND YEAR(date_created) = YEAR(CURDATE()) 
				AND date_created NOT BETWEEN "2016-12-22 05:03:14" 
				AND "2016-12-22 05:03:32" 
				'.$excludeCustomerSql.'
			', 
			'order' => 'date_created DESC'
		));
		
		$this->render('index', array(
			'models' => $models
		));
	}
		
	public function actionGoals($salesRepId=null)
	{
		$authAccount = Yii::app()->user->account;
		$authUser = $authAccount->accountUser;
		
		if( $authUser->job_title != "Manager" )
		{
			$this->redirect(array('/customer'));
		}
		
		$monthlyTeamGoal = SalesTeamMonthlyGoal::model()->findByPk(1);

		$monthlyUserGoal = SalesAccountMonthlyGoal::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $salesRepId
			),
		));
		
		if( !$monthlyUserGoal )
		{
			$monthlyUserGoal = new SalesAccountMonthlyGoal;
		}
		
		if( isset($_POST['SalesAccountMonthlyGoal']) ) 
		{
			$monthlyUserGoal->attributes = $_POST['SalesAccountMonthlyGoal'];
			
			if( $monthlyUserGoal->save(false) )
			{
				Yii::app()->user->setFlash('success', 'Database has been updated.');
			}
			else
			{
				Yii::app()->user->setFlash('danger', 'Saving error.');
			}
			
			$this->redirect(array('salesManagement/goals', 'salesRepId'=>$salesRepId));
		}
	
		if( isset($_POST['SalesTeamMonthlyGoal']) ) 
		{
			$monthlyTeamGoal->attributes = $_POST['SalesTeamMonthlyGoal'];
			
			if( $monthlyTeamGoal->save(false) )
			{
				Yii::app()->user->setFlash('success', 'Database has been updated.');
			}
			else
			{
				Yii::app()->user->setFlash('danger', 'Saving error.');
			}
			
			$this->redirect(array('salesManagement/goals'));
		}
		
		$this->render('goals', array(
			'monthlyTeamGoal' => $monthlyTeamGoal,
			'monthlyUserGoal' => $monthlyUserGoal,
			'salesRepId' => $salesRepId,
		));
	}

	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$model = Account::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$model->sales_management_deleted = 1;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
				}
			}
			else
			{
				$result['message'] = 'Record not found.';
			}
		}
		
		echo json_encode($result);
	}

	public function actionEdit()
	{
		$result = array(
			'status' => 'error',
			'message' => 'Saving error.',
			'commission_rate' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$model = Account::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				CustomerSalesRep::model()->deleteAll(array(
					'condition' => 'customer_id = :customer_id',
					'params' => array(
						':customer_id' => $_POST['customer_id'],
					),
				));
				
				if( !empty($_POST['sales_rep_account_id']) )
				{
					$commissionRate = 0;
					
					CustomerSalesRep::model()->deleteAll(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $_POST['customer_id'],
						),
					));
					
					$salesRepCtr = 0;
					$contractedAmount = 0;
					
					foreach( $_POST['sales_rep_account_id'] as $salesRepAccountId )
					{
						$salesRep = new CustomerSalesRep;
								
						$salesRep->setAttributes(array(
							'customer_id' => $_POST['customer_id'],
							'sales_rep_account_id' => $salesRepAccountId
						));
						
						if( $salesRep->save(false) )
						{	
							$salesRepCtr++;
							
							$userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
								'condition' => 'account_id = :account_id AND commission_rate IS NOT NULL AND commission_rate !="" AND commission_rate > 0',
								'params' => array(
									':account_id' => $salesRepAccountId,
								),
							));
							
							if( $userMonthlyGoal )
							{
								$userCommissionRate = str_replace('%', '', $userMonthlyGoal->commission_rate);

								$commissionRate = ($userCommissionRate / 100);
							}
						}
					}
				
					
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $_POST['customer_id'],
						),
					));
					
					if( $customerSkill )
					{
						$contract = $customerSkill->contract;
						
						if( $contract )
						{
							if($contract->fulfillment_type != null )
							{
								if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
								{
									if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
									{
										foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
										{
											$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
											$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

											if( $customerSkillLevelArrayGroup != null )
											{							
												if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
												{
													$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
													
													$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
												}
											}
										}
									}
									
									$customerExtras = CustomerExtra::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
										'params' => array(
											':customer_id' => $customerSkill->customer_id,
											':contract_id' => $customerSkill->contract_id,
											':skill_id' => $customerSkill->skill_id,
											':year' => date('Y'),
											':month' => date('m'),
										),
									));
									
									if( $customerExtras )
									{
										foreach( $customerExtras as $customerExtra )
										{
											$totalLeads += $customerExtra->quantity;
										}
									}
								}
								else
								{
									if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
									{
										foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
										{
											$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
											
											$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
											
											if( $customerSkillLevelArrayGroup != null )
											{
												if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
												{
													$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
													
													$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
												}
											}
										}
									}
									
									$customerExtras = CustomerExtra::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
										'params' => array(
											':customer_id' => $customerSkill->customer_id,
											':contract_id' => $customerSkill->contract_id,
											':skill_id' => $customerSkill->skill_id,
											':year' => date('Y'),
											':month' => date('m'),
										),
									));
									
									if( $customerExtras )
									{
										foreach( $customerExtras as $customerExtra )
										{
											$totalLeads += $customerExtra->quantity;
										}
									}
								}
							}
						}
					}
								
					if( $commissionRate > 0 )
					{
						$result['commission_rate'] = '$'.number_format( ($commissionRate * $contractedAmount) / $salesRepCtr, 2);
					}
					else
					{
						$result['commission_rate'] = '$0.00';
					}
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
			else
			{
				$result['message'] = 'Record not found.';
			}
		}
		
		echo json_encode($result);
	}
}

?>