<tr>
	<td class="center">
		<button id="<?php echo $data->id; ?>" class="btn btn-minier btn-primary btn-edit-state-holiday"><i class="fa fa-pencil"></i> Edit</button>
		<button id="<?php echo $data->id; ?>" class="btn btn-minier btn-danger btn-delete-state-holiday"><i class="fa fa-times"></i> Delete</button>
	</td>
	<td><?php echo State::model()->findByPk($data->state)->name; ?></td>
	<td><?php echo $data->name; ?></td>
	<td><?php echo date('m/d/Y', strtotime($data->date)); ?></td>
	<td class="center"> 
		<?php 
			$dialCount = LeadCallHistory::model()->count(array(
				'with' => 'customer',
				'condition' => '
					t.status=1 
					AND DATE(t.date_created) = DATE(:date) 
					AND t.end_call_time > t.start_call_time 
					AND t.lead_id IS NOT NULL 
					AND t.disposition_id IS NOT NULL
					AND t.disposition NOT IN ("Appointment Confirmed", "Appointment Confirm - No Answer", "Appointment Confirmed - Left Message")
					AND customer.state = :state
				',
				'params' => array(
					':date' => $data->date,
					':state' => $data->state
				),
			));
			
			echo number_format($dialCount);
		?>
	</td>
</tr>