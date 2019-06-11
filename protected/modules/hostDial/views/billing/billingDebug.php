<?php 

echo '<table class="table table-striped table-hover-table-compressed">';

echo '<tr>';
	echo '<th>Customer Name</th>';
	echo '<th>Credit Amount</th>';
	echo '<th></th>';
echo '</tr>';

if( $customers )
{
	foreach( $customers as $customer )
	{
		$totalCreditAmount = 0;
		
		$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
			'condition' => '
				customer_id = :customer_id 
				AND contract_id = :contract_id
				AND transaction_type = "Charge"
				AND billing_period = :billing_period
				AND anet_responseCode = 1
			',
			'params' => array(
				':customer_id' => $customer->customer_id,
				':billing_period' => 'Mar 2017'
			),
			'order' => 'date_created DESC'
		));
		
		if( $existingBillingForCurrentMonth )
		{
			$febCredits = CustomerCredit::model()->findAll(array(
				'condition' => 'start_month="02" AND customer_id = :customer_id',
				'params' => array(
					':customer_id' => $customer->customer_id
				),
			));
			
			echo '<tr>';
				echo '<td>'.$customer->customer->getFullName().'</td>';
				echo '<td>';
				
					if( $febCredits )
					{
						foreach( $febCredits as $febCredit )
						{
							$totalCreditAmount += $febCredit->amount;
						}
					}
					
					echo '$'.$totalCreditAmount;
				
				echo '</td>';
				
			echo '</tr>';
		}
	}
}

echo '</table>';

?>