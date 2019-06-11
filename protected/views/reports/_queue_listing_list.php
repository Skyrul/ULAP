<?php
	$dials = 0;
	
	$contractedLeads = 0;
	
	$customerSkill = CustomerSkill::model()->find(array(
		'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
		'params' => array(
			':customer_id' => $data->customer_id,
			':skill_id' => $data->skill_id
		),
	));
	
	$contract = $data->contract;
								
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
							$contractedLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
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
							$contractedLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
						}
					}
				}
			}
		}
	}
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Company</th>
	<th>Customer Name</th>
	<th>Skill</th>
	<th>Contract</th>
	<th>Total Callable</th>
	<th>Callable Today</th>
	<th>Future Callable</th>
	<th>Dials</th>
	<th>Cycle End Day</th>
	<th>Fulfilment Type (Goal or Lead)</th>
	<th>Contracted Leads/Goals</th>
	<th>Current Goal</th>
	<th>Pace</th>
	<th>Total Potential</th>
	<th>Priority</th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo isset($data->customer->company) ? $data->customer->company->company_name : ''; ?></td>
	
	<td><?php echo $data->customer->firstname . ', '. $data->customer->lastname;  ?></td>
	
	<td><?php echo $data->skill_name; ?></td>
	
	<td><?php echo $data->contract->contract_name; ?></td>
	
	<td><?php echo $data->available_leads; ?></td>
	
	<td><?php echo $data->available_leads; ?></td>
	
	<td><?php echo $data->available_leads; ?></td>
	
	<td><?php echo $data->current_dials; ?></td>
	
	<td>
		<?php echo $data->priority_reset_date; ?>
	</td>
	
	<td><?php echo $data->fulfillment_type; ?></td>
	
	<td><?php echo $contractedLeads; ?></td>
	
	<td>
		<?php echo $data->current_goals; ?>
	</td>
	
	<td><?php echo $data->pace; ?></td>
	
	<td><?php echo $data->total_potential_dials; ?></td>
	
	<td><?php echo $data->priority; ?></td>
</tr>