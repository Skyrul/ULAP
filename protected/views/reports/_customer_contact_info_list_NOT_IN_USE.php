<?php

	$state = !empty($data->state) ? State::model()->findByPk($data->state)->name : '';
						
	$customerSkills = CustomerSkill::model()->findAll(array(
		'condition' => 'customer_id = :customer_id AND status=1',
		'params' => array(
			'customer_id' => $data->id,
		),
	));
	
	$skillArray = array();
	$contractArray = array();
	
	$startDate = '';
	$endDate = '';
	$holdStartDate = '';
	$holdEndDate = '';
	$promo = '';
	$quantity = 0;
	
	if( $customerSkills )
	{
		foreach( $customerSkills as $customerSkill )
		{
			if( !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
			{
				$startDate .= date('m/d/Y', strtotime($customerSkill->start_month)) . '<br>';
			}
			
			if( !empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00' )
			{
				$endDate = date('m/d/Y', strtotime($customerSkill->end_month));
			}
			
			if( isset($customerSkill->skill) && !in_array($customerSkill->skill->skill_name, $skillArray) )
			{
				$skillArray[] = $customerSkill->skill->skill_name;
			}
			
			if( isset($customerSkill->contract) && !in_array($customerSkill->contract->contract_name, $contractArray) )
			{
				$contract = $customerSkill->contract;
				
				$contractArray[] = $contract->contract_name;
		
				if( isset($contract) && $contract->fulfillment_type != null )
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
										$quantity += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
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
										$quantity += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
									}
								}
							}
						}
					}
				}
			}
			
			$status = 'Inactive';
			
			if( !empty($customerSkills) && isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
			{
				$status = 'Active';
			
				if( !$customerIsCallable )
				{
					if( $customerSkill->start_month == '0000-00-00' )
					{
						$status = 'Blank Start Date';
					}
					
					if( $customerSkill->start_month != '0000-00-00' && strtotime($customerSkill->start_month) > time() )
					{
						$status = 'Future Start Date';
					}
				}
				
				if( $customerSkill->is_contract_hold == 1 )
				{
					if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
					{
						if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
						{
							$status = 'On Hold';
						}
					}
				}
				
				if( $customerSkill->is_hold_for_billing == 1 )
				{
					$status = 'Decline Hold';
				}
				
				if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
				{
					if( time() >= strtotime($customerSkill->end_month) )
					{
						$status = 'Cancelled';
					}
				}
				
				if( $status == 'On Hold' )
				{
					$holdStartDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_start_date)).'<br>';
					$holdEndDate .= date('m/d/Y', strtotime($customerSkill->is_contract_hold_end_date)).'<br>';
				}
				
				if(!empty($customerSkill->promo))
				{
					$promo = $customerSkill->promo->promo_name;
				}
			}
		}
	}
	
	$startDate = rtrim($startDate, '<br>');
	$endDate = rtrim($endDate, '<br>');
	$holdStartDate = rtrim($holdStartDate, '<br>');
	$holdEndDate = rtrim($holdEndDate, '<br>');
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Agent ID</th>
	<th>Last Name</th>
	<th>First Name</th>
	<th>Status</th>
	<th>Company</th>
	<th>Phone Number</th>
	<th>Email Address</th>
	<th>Address</th>
	<th>City</th>
	<th>State</th>
	<th>Zip</th>
	<th>Skills</th>
	<th>Contracts</th>
	<th>Promo</th>
	<th>Quantity</th>
	<th>Start Date</th>
	<th>End Date</th>
	<th>On hold</th>
	<th>Off hold</th>
	<th></th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo $data->custom_customer_id; ?></td>
		
	<td><?php echo CHtml::link($data->lastname, array('/customer/insight/index', 'customer_id'=>$data->id));  ?></td>
	
	<td><?php echo $data->firstname;  ?></td>
	
	<td>
		<?php 			
			echo $status;
		?>
	</td>
	
	<td><?php echo isset($data->company) ? $data->company->company_name : ''; ?></td>
	
	<td><?php echo $data->phone; ?></td>
	
	<td><?php echo $data->email_address; ?></td>
	
	<td><?php echo $data->address1; ?></td>
	
	<td><?php echo $data->city; ?></td>
	
	<td><?php echo $state; ?></td>
	
	<td><?php echo $data->zip; ?></td>
	
	<td><?php echo !empty($skillArray) ? implode('<br />', $skillArray) : ''; ?></td>
	
	<td><?php echo !empty($contractArray) ? implode('<br />', $contractArray) : ''; ?></td>
	
	<td><?php echo $promo; ?></td>
	
	<td><?php echo $quantity; ?></td>
	
	<td><?php echo $startDate; ?></td>
	
	<td><?php echo $endDate; ?></td>
	
	<td>
		<?php echo $holdStartDate; ?>
	</td>
	
	<td>
		<?php echo $holdEndDate; ?>
	</td>
</tr>