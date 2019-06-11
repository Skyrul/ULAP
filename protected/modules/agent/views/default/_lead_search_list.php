<thead>
	<th>Name</th>
	<th>Home</th>
	<th>Mobile</th>
	<th>Office</th>
	<th>Customer Name</th>
	<th>Calling Time</th>
	<th>Company</th>
	<th>Skill</th>
	<th width="15%">Options</th>
</thead>

<tbody>
	<?php	
		if( $models )
		{
			foreach( $models as $model )
			{
				$skillsTxt = '';
				
				// if( $model->list )
				// {
					// $customerSkills = CustomerSkill::model()->findAll(array(
						// 'condition' => 'customer_id = :customer_id AND status=1',
						// 'params' => array(
							// ':customer_id' => $model->list->customer_id,
						// ),
					// ));
					
					// if( $customerSkills )
					// {
						// foreach( $customerSkills as $customerSkill )
						// {
							// $skillsTxt .= $customerSkill->skill->skill_name.', ';
						// }
						
						// $skillsTxt = rtrim($skillsTxt, ', ');
					// }
				// }
				
				$callingTime =  $this->getLeadCallingTime($model);
				
				$skillsTxt = $model->list->skill->skill_name;
				
				$loadSelectCss = '';
				
				if( Yii::app()->user->account->getIsHostDialer() )
				{
					$loadSelectCss = 'display:none;';
				}
				
			?>
				<tr>
					<td><?php echo $model->first_name.' '.$model->last_name; ?></td>
					
					<td><?php echo !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : ''; ?></td>
					
					<td><?php echo !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : ''; ?></td>
					
					<td><?php echo !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : ''; ?></td>
					
					<td><?php echo isset($model->list) ? $model->list->customer->firstname.' '.$model->list->customer->lastname : ''; ?></td>
					
					<td><?php echo $callingTime; ?></td>
					
					<td><?php echo isset($model->list) ? $model->list->customer->company->company_name : ''; ?></td>
					
					<td><?php echo $skillsTxt; ?></td>
					
					<td class="center">
						<select style="<?php echo $loadSelectCss; ?>">
							<option value="1" selected>Contact</option>
							<option value="3">Confirm</option>
							<option value="6">Reschedule</option>
						</select>
						
						<?php echo CHtml::link('Load <i class="fa fa-arrow-right"></i>', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-info btn-minier load-lead-to-hopper', 'data-calling-time'=> $callingTime)); ?>
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
</tbody>