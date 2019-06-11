<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);

class InsightController extends Controller
{
	public function actionIndex($customer_id = null)
	{
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		
		// $customerSkills = CustomerSkill::model()->findAll(array(
			// 'condition' => 'customer_id = :customer_id AND status=1',
			// 'params' => array(
				// ':customer_id' => $customer->id,
			// ),
		// ));
		
	
		$lists = Lists::model()->findAll(array(
			// 'condition' => 'customer_id = :customer_id AND status !=3',
			'condition' => 'customer_id = :customer_id AND status = 1',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));
		
		$activeLeadsDialsCountList = array();
		$leadsDialsCountList = array();
		$completedLeadsList = array();
		$guestReached = array();
		$dphData = array();
		
		foreach($lists as $list)
		{
			##total active lead dials##
			$criteria = new CDbCriteria;
			$criteria->compare('status', 1);
			$criteria->compare('list_id', $list->id);
			$criteria->compare('customer_id', $customer->id);
			$criteria->select = 'SUM(number_of_dials) as number_of_dials';
			
			$leadsDialsCount = Lead::model()->find($criteria);
			
			$activeLeadsDialsCountList[$list->id] = $leadsDialsCount->number_of_dials;
			
			### total dials##
			$criteria = new CDbCriteria;
			$criteria->compare('list_id', $list->id);
			$criteria->compare('customer_id', $customer->id);
			$criteria->select = 'SUM(number_of_dials) as number_of_dials';
			
			$leadsDialsCount = Lead::model()->find($criteria);
			
			$leadsDialsCountList[$list->id] = $leadsDialsCount->number_of_dials;
			
			$completedLeads = Lead::model()->count(array(
				'with' => 'list',
				'condition' => 'list_id = :list_id AND t.type=1 AND t.status=3 AND list.status !=3',
				'params' => array(
					':list_id' => $list->id,
				), 
			));
			
			$completedLeadsList[$list->id] = $completedLeads;
			
			##get Appointment that has been scheduled ##
			$voiceReachedSql = "
				SELECT count(distinct lch.lead_id) AS totalCount 
				FROM ud_lead_call_history lch 
				INNER JOIN ud_lists ls ON ls.id = lch.list_id
				INNER JOIN ud_skill_disposition sd ON sd.id = lch.disposition_id
				WHERE lch.list_id = '".$list->id."'
				AND sd.is_voice_contact = 1
			";
			
			$command = Yii::app()->db->createCommand($voiceReachedSql);
			$voiceReached = $command->queryRow();
			
			$guestReached[$list->id] = $voiceReached['totalCount'];
			
			##get DPH ##
			$dphSQL = "
				SELECT
					SEC_TO_TIME(SUM(TIME_TO_SEC(timediff(end_call_time, start_call_time)))) AS totalHours,
					count(lch.id) as ctr,
					lch.list_id
				FROM ud_lead_call_history lch 
					LEFT JOIN ud_customer c ON lch.customer_id = c.id
					LEFT JOIN ud_lists ls ON ls.id = lch.list_id
					LEFT JOIN ud_lead ld ON ld.id = lch.lead_id
					LEFT JOIN ud_skill sk ON sk.id = ls.skill_id
				WHERE lch.list_id = '".$list->id."'
				AND lch.disposition IS NOT NULL 
				
			";
			
			$command = Yii::app()->db->createCommand($dphSQL);
			$dphRow = $command->queryRow();
			
			$totalHours = $dphRow['totalHours'];
			
			$decimalHours = $this->decimalHours($totalHours);

			if( $dphRow['ctr'] > 0 && $decimalHours > 0 )
			{
				$dphCalc = $dphRow['ctr'] / $decimalHours;
			}
			else
			{
				$dphCalc = 0;
			}
			
			$dphData[$list->id] = round($dphCalc, 1, PHP_ROUND_HALF_UP);
			
			// echo $decimalHours;
			// print_r($dphRow);
			// exit;
			
			// echo '<br>';
			// echo $dphCalc;
			// echo '<br>';
			// echo $list->id;
			// echo '<br>';
			// echo '--';
			// echo '<br>';
		}
		
		
		
		
		$this->render('index',array(
			'customer' => $customer,
			'lists' => $lists,
			'activeLeadsDialsCountList' => $activeLeadsDialsCountList,
			'leadsDialsCountList' => $leadsDialsCountList,
			'completedLeadsList' => $completedLeadsList,
			'guestReached' => $guestReached,
			'dphData' => $dphData,
			// 'customerSkills' => $customerSkills,
		));
		
	}
	
	public function decimalHours($time)
	{
		$hms = explode(":", $time);
		return ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
	}

	
}
