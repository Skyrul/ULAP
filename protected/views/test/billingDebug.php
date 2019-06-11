<?php 

echo '<table class="table table-striped table-hover-table-compressed">';

echo '<tr>';
	echo '<th>#</th>';
	echo '<th>Customer Name</th>';
	echo '<th class="center">Credit Amount</th>';
	echo '<th></th>';
echo '</tr>';

if( $customers )
{
	$ctr = 1;
	$grandTotalCreditAmount = 0;
	
	foreach( $customers as $customer )
	{
		$totalCreditAmount = 0;
		
		$existingBillingForCurrentMonth = CustomerBilling::model()->find(array(
			'condition' => '
				customer_id = :customer_id 
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
				echo '<td>'.$ctr.'</td>';
				echo '<td>'.CHtml::link($customer->customer->getFullName(), array('/customer/billing', 'customer_id'=>$customer->customer_id), array('target'=>'_blank')).'</td>';
				echo '<td class="center">';
				
					if( $febCredits )
					{
						foreach( $febCredits as $febCredit )
						{
							$totalCreditAmount += $febCredit->amount;
							$grandTotalCreditAmount += $totalCreditAmount;
						}
					}
					
					echo '$'.$totalCreditAmount;
				
				echo '</td>';
				
			echo '</tr>';
			
			$ctr++;
		}
	}
}

echo '<tr>';
	echo '<td><b>Total</b></td>';
	echo '<td></td>';
	echo '<td class="center"><b>$'.$grandTotalCreditAmount.'</b></td>';
echo '</tr>';

echo '</table>';

?>