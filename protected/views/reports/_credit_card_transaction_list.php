<?php

	$customerSkills = CustomerSkill::model()->findAll(array(
		'condition' => 'customer_id = :customer_id AND status=1',
		'params' => array(
			'customer_id' => $data->customer_id,
		),
	));
	
	$skillArray = array();
	$contractArray = array();
	
	if( $customerSkills )
	{
		foreach( $customerSkills as $customerSkill )
		{
			if( !in_array($customerSkill->skill->skill_name, $skillArray) )
			{
				$skillArray[] = $customerSkill->skill->skill_name;
			}
			
			if( !in_array($customerSkill->contract->contract_name, $contractArray) )
			{
				$contractArray[] = $customerSkill->contract->contract_name;
			}
		}
	}

	if( $data->transaction_type == 'Void' && $data->reference_transaction_id != null )
	{
		$chargeRecord = CustomerBilling::model()->findByPk($data->reference_transaction_id);
		
		if( $chargeRecord )
		{
			$data->credit_amount = $chargeRecord->credit_amount;
		}
	}
?>


<?php if( $index == 0 ): ?>

<thead>
	<th>Date/Time</th>
	<th>Agent ID</th>
	<th>Customer Name</th>
	<th>Company</th>
	<th>Skill</th>
	<th>Contract</th>
	<th>Billing Cycle</th>
	<th>Memo</th>
	<th>Payment Method</th>
	<th>Credit Card Type</th>
	<th>Transaction Type</th>
	<th>Action</th>
	<th>Original Amount</th>
	<th>Billing Credit</th>
	<th>Subsidy</th>
	<th>Reduced Amount</th>
	<th>Authorize Transaction ID</th>
	<th>User</th>
	<th>Result</th>
</thead>

<?php endif; ?>

<tr>
	<td>
		<?php 
			$dateTime = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));
			$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
			
			echo $dateTime->format('m/d/Y g:i A');
		?>
	</td>
	
	<td><?php echo $data->customer->custom_customer_id;  ?></td>
	
	<td><?php echo $data->customer->firstname . ', '. $data->customer->lastname;  ?></td>
	
	<td><?php echo isset($data->customer->company) ? $data->customer->company->company_name : ''; ?></td>
	
	<td><?php echo !empty($skillArray) ? implode('<br />', $skillArray) : ''; ?></td>
	
	<td><?php echo !empty($contractArray) ? implode('<br />', $contractArray) : ''; ?></td>
	
	<td><?php echo $data->billing_period;  ?></td>
	
	<td><?php echo $data->description;  ?></td>
	
	<td>
		<?php 
			if( $data->payment_method == 'echeck' )
			{
				echo 'eCheck';
			}
			else
			{
				echo 'Credit Card';
			}
		?>
	</td>
	
	<td><?php echo $data->credit_card_type; ?></td>
	
	<td><?php echo $data->billing_type; ?></td>
	
	<td><?php echo $data->transaction_type; ?></td>
	
	<td><?php echo '$' . number_format( ($data->amount + $data->credit_amount + $data->subsidy_amount), 2); ?></td>
	
	<td><?php echo '$' . number_format($data->credit_amount, 2); ?></td>
	
	<td><?php echo '$' . number_format($data->subsidy_amount, 2); ?></td>
	
	<td><?php echo '$' . number_format($data->amount, 2); ?></td>
	
	<td><?php echo $data->anet_transId; ?></td>
	
	<td><?php echo isset($data->account) ? $data->account->getFullName() : ''; ?></td>
	
	<td>
		<?php 
			if( $data->anet_responseCode == 1 )
			{
				echo '<span class="label label-success">Success</span>';
			}
			else
			{
				echo '<span class="label label-danger">Decline</span>';
				
				if( !empty($data->anet_responseReasonDescription) )
				{
					echo ' - ' . $data->anet_responseReasonDescription;
				}
			}
		?>
	</td>
</tr>