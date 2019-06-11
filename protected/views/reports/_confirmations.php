<?php 
	$callHistory = LeadCallHistory::model()->find(array(
		'condition' => 'lead_id	= :lead_id AND calendar_appointment_id = :calendar_appointment_id',
		'params' => array(
			':lead_id' => $data->lead_id,
			':calendar_appointment_id' => $data->calendar_appointment_id,
		),
		'order' => 'date_created DESC', 
	));
	
	$leadPhoneNumber = '';
	
	if( !empty($callHistory) )
	{
		$leadPhoneNumber = $callHistory->lead_phone_number;
	}
	else
	{
		if( $leadPhoneNumber == '' && !empty($data->lead->home_phone_number) )
		{
			$leadPhoneNumber = $data->lead->home_phone_number;
		}
		
		if( $leadPhoneNumber == '' && !empty($data->lead->office_phone_number) )
		{
			$leadPhoneNumber = $data->lead->office_phone_number;
		}
		
		if( $leadPhoneNumber == '' && !empty($data->lead->mobile_phone_number) )
		{
			$leadPhoneNumber = $data->lead->mobile_phone_number;
		}
	}
	
	$showRecord = false;
	$trClass = '';
	
	if( time() > strtotime($data->calendarAppointment->start_date) )
	{
		$trClass = 'danger';
	}
	
	$style = 'none';
	
	$customerSkill = CustomerSkill::model()->find(array(
		'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
		'params' => array(
			':customer_id' => $data->customer_id,
			':skill_id' => $data->skill_id,
		),
	));
	
	if( $customerSkill )
	{
		$customerSkillChild = CustomerSkillChild::model()->find(array(
			'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND customer_skill_id = :customer_skill_id AND skill_child_id = :skill_child_id',
			'params' => array(
				':customer_id' => $data->customer_id,
				':skill_id' => $data->skill_id,
				':customer_skill_id' => $customerSkill->id,
				':skill_child_id' => $data->skill_child_confirmation_id
			),
		));

		if( $customerSkillChild )
		{
			if( $customerSkillChild->is_enabled == 1 )
			{
				$style = '';
			}	
		}
	}
	
	//temp code to force certain customers to get no dials 
	// $floridaAreaCodes = array('239', '305', '321', '352', '386', '407', '561', '727', '754', '772', '786', '813', '850', '863', '904', '941', '954');
		
	// $georgiaArecodeCodes = array('229', '404', '470', '478', '678', '706', '762', '770', '912');
	
	// $southCarolinaAreaCodes = array('803', '843', '864');
	
	// $deleteConfirm = false;
	
	// if( in_array(substr($leadPhoneNumber, 0, 3), $floridaAreaCodes) )
	// {
		// $deleteConfirm = true;
	// }
	
	// if( in_array(substr($leadPhoneNumber, 0, 3), $georgiaArecodeCodes) )
	// {
		// $deleteConfirm = true;
	// }
	
	// if( in_array(substr($leadPhoneNumber, 0, 3), $southCarolinaAreaCodes) )
	// {
		// $deleteConfirm = true;
	// }
	
	// if( $deleteConfirm )
	// {
		// $data->delete();
	// }
	
	//end of temp code
?>


<?php /* if( $index == 0 ): ?>

<thead>
	<th>Company</th>
	<th>Customer Name</th>
	<th>Lead Name</th>
	<th>Lead Phone</th>
	<th>Appointment Date/Time</th>
	<th>Time zone of lead</th>
</thead>

<?php endif; */ ?>



<tr class="<?php echo $trClass; ?>" style="display:<?php echo $style; ?>;">

	<td><?php echo isset($data->customer->company) ? $data->customer->company->company_name : ''; ?></td>
	
	<td><?php echo CHtml::link($data->customer->firstname.' '.$data->customer->lastname, array('/customer/customerSkill/index', 'customer_id'=>$data->customer->id), array('target'=>'_blank')); ?></td>
	
	<td>
		<?php 
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
							
			echo $status;
		?>
	</td>
	
	<td><?php echo $data->lead->first_name.' '.$data->lead->last_name; ?></td>
	
	<td><?php echo "(".substr($leadPhoneNumber, 0, 3).") ".substr($leadPhoneNumber, 3, 3)."-".substr($leadPhoneNumber,6); ?></td>
	
	<td><?php echo isset($data->calendarAppointment) ? date('Y-m-d g:i a', strtotime($data->calendarAppointment->start_date)) : null; ?></td>
	
	<td><?php echo $data->lead_timezone; ?> <span class="hide"><?php echo $data->id; ?></span></td>
	
	<td><?php echo checkTimeZone($customerSkill, 'lead', $data->lead, $data->skill_child_confirmation_id); ?></td>

	<td>
		<?php 
			if( isset($data->calendarAppointment) )
			{ 
				$dateTime = new DateTime($data->calendarAppointment->date_created, new DateTimeZone('America/Chicago'));
				$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
				
				echo $dateTime->format('m/d/Y g:i A');
			}
		?>
	</td>
</tr>

