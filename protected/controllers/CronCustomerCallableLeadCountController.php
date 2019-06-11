<?php 

ini_set('memory_limit', '4000M');
set_time_limit(0);

class CronCustomerCallableLeadCountController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		echo 'first day: ' . date('Y-m-01');
		echo '<br>';
		echo 'today: ' . date('Y-m-d');
		echo '<br><br>';
		
		$dbUpdates = 0;
		
		$customerQueues = CustomerQueueViewer::model()->findAll();
		
		if( $customerQueues )
		{
			foreach( $customerQueues as $customerQueue ) 
			{
				$valid = false;
				
				$remainingCallableCount = Lead::model()->count(array(
					'with' => array('list', 'list.skill'),
					'together' => true,
					'condition' => '
						list.customer_id = :customer_id 
						AND list.status = 1 
						AND t.type=1 and t.status=1 
						AND t.number_of_dials < (skill.max_dials * 3) 
						AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL AND NOW() <= recertify_date)
					',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
					),
				));
				
				$existingCounter = CustomerCallableLeadCount::model()->find(array(
					'condition' => 'customer_id = :customer_id AND MONTH(date_created) = MONTH(NOW())',
					'params' => array(
						':customer_id' => $customerQueue->customer_id,
					),
				));
				
				if( empty($existingCounter) ) 
				{
					$valid = true;
					$counter = new CustomerCallableLeadCount;
				}
				else
				{
					if(date('Y-m-d') == date('Y-m-01'))
					{
						$valid = true;
						$counter = $existingCounter;
					}
				}
				
				if( $valid )
				{
					$counter->customer_id = $customerQueue->customer_id;
					$counter->callable_leads = $remainingCallableCount;
					
					if( $counter->save(false) )
					{
						echo $dbUpdates++;
						echo '<br>';
					}
				}
			}
		}
		
		echo '<br><br>dbUpdates: ' . $dbUpdates;
	}
}

?>