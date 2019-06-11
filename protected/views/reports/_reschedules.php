<?php 
	$callHistory = LeadCallHistory::model()->find(array(
		'condition' => 'lead_id	= :lead_id',
		'params' => array(
			':lead_id' => $data->lead_id,
		),
		'order' => 'date_created DESC', 
	));
	
	$customerSkill = CustomerSkill::model()->find(array(
		'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
		'params' => array(
			':customer_id' => $data->customer_id,
			':skill_id' => $data->skill_id,
		),
	));
	
	$status = 'Active';
		
		if( $customerSkill->is_contract_hold == 1 )
		{
			if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
			{
				if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
				{
					$status = 'Hold';
				}
			}
		}
		
		if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
		{
			if( time() >= strtotime($customerSkill->end_month) )
			{
				$status = 'Cancelled';
			}
		}
		
		if( $customerSkill->is_hold_for_billing == 1 )
		{
			$status = 'Hold';
		}
?>

<?php /*if( $index == 0 ): ?>

<thead>
	<th>Company</th>
	<th>Customer Name</th>
	<th>Lead Name</th>
	<th>Lead Phone</th>
</thead>

<?php endif; */?>

<?php if( $status == 'Active' ): ?>
<tr>

	<td><?php echo isset($data->customer->company) ? $data->customer->company->company_name : ''; ?></td>
	
	
	<td><?php echo CHtml::link($data->customer->firstname.' '.$data->customer->lastname, array('/customer/customerSkill/index', 'customer_id'=>$data->customer->id), array('target'=>'_blank')); ?></td>
	
	<td>
		<?php 
			echo $status;
		?>
	</td>
	
	<td><?php echo $data->lead->first_name.' '.$data->lead->last_name; ?></td>
	
	<td><?php echo !empty($callHistory) ? $callHistory->lead_phone_number : null; ?> <span class="hide"><?php echo $data->id; ?></span></td>
	
	<td><?php echo $data->lead_timezone; ?> <span class="hide"><?php echo $data->id; ?></span></td>
	
	<td>
		<?php 
			if( isset($data->calendarAppointment) )
			{ 
				$dateTime = new DateTime($data->calendarAppointment->date_updated, new DateTimeZone('America/Chicago'));
				$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				
				echo $dateTime->format('m/d/Y g:i A');
			}
		?>
		
		<span class="hidden"><?php echo $data->id; ?></span>
	</td>
</tr>

<?php endif; ?>