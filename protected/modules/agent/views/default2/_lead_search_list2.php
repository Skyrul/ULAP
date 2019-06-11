<thead>
	<th>Member Number</th>
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
				$lead = $model->lead;
				
				$skillsTxt = '';
				
				$skillsTxt = $lead->list->skill->skill_name;
				
			?>
				<tr>
					<td><?php echo $model->value; ?></td>
					
					<td><?php echo $lead->first_name.' '.$lead->last_name; ?></td>
					
					<td><?php echo !empty($lead->home_phone_number) ? "(".substr($lead->home_phone_number, 0, 3).") ".substr($lead->home_phone_number, 3, 3)."-".substr($lead->home_phone_number,6) : ''; ?></td>
					
					<td><?php echo !empty($lead->mobile_phone_number) ? "(".substr($lead->mobile_phone_number, 0, 3).") ".substr($lead->mobile_phone_number, 3, 3)."-".substr($lead->mobile_phone_number,6) : ''; ?></td>
					
					<td><?php echo !empty($lead->office_phone_number) ? "(".substr($lead->office_phone_number, 0, 3).") ".substr($lead->office_phone_number, 3, 3)."-".substr($lead->office_phone_number,6) : ''; ?></td>
					
					<td><?php echo isset($lead->list) ? $lead->list->customer->firstname.' '.$lead->list->customer->lastname : ''; ?></td>
					
					<td><?php $callingTime =  $this->getLeadCallingTime($lead); ?></td>
					
					<td><?php echo isset($lead->list) ? $lead->list->customer->company->company_name : ''; ?></td>
					
					<td><?php echo $skillsTxt; ?></td>
					
					<td class="center">
						<select>
							<option value="1" selected>Contact</option>
							<option value="3">Confirm</option>
							<option value="6">Reschedule</option>
						</select>
						
						<?php echo CHtml::link('Load <i class="fa fa-arrow-right"></i>', 'javascript:void(0);', array('id'=>$lead->id, 'class'=>'btn btn-info btn-minier load-lead-to-hopper', 'data-calling-time'=> $callingTime)); ?>
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