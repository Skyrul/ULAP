<?php 
	if( $models )
	{
		foreach( $models as $model )
		{
			$skillsTxt = '';
			
			$customerSkills = CustomerSkill::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $model->list->customer_id,
				),
			));
			
			if( $customerSkills )
			{
				foreach( $customerSkills as $customerSkill )
				{
					$skillsTxt .= $customerSkill->skill->skill_name.', ';
				}
			}
			
			$skillsTxt = rtrim($skillsTxt, ', ');
			
		?>
			<tr>
				<td><?php echo $model->first_name.' '.$model->last_name; ?></td>
				
				<td><?php echo !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : ''; ?></td>
				
				<td><?php echo !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : ''; ?></td>
				
				<td><?php echo !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : ''; ?></td>
				
				<td><?php echo $model->list->customer->firstname.' '.$model->list->customer->lastname; ?></td>
				
				<td><?php echo $model->list->customer->company->company_name; ?></td>
				
				<td><?php echo $skillsTxt; ?></td>
				
				<td class="center">
					<?php echo CHtml::link('Load <i class="fa fa-arrow-right"></i>', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-info btn-minier load-lead-to-hopper')); ?>
				</td>	
			</tr>
		<?php
		}
	}
	else
	{
	?>
		<tr><td colspan="2">No models found.</td></tr>
	<?php
	}
?>