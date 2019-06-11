<tr>
	<td class="center">
		<button id="<?php echo $data->id; ?>" class="btn btn-minier btn-primary btn-edit-federal-holiday"><i class="fa fa-pencil"></i> Edit</button>
		<button id="<?php echo $data->id; ?>" class="btn btn-minier btn-danger btn-delete-federal-holiday"><i class="fa fa-times"></i> Delete</button>
	</td>
	<td><?php echo $data->name; ?></td>
	<td><?php echo date('m/d/Y', strtotime($data->date)); ?></td>
	<td class="center"> 
		<?php 
			$dialCount = LeadCallHistory::model()->count(array(
				'condition' => '
					status=1 
					AND DATE(date_created) = DATE(:date) 
					AND end_call_time > start_call_time 
					AND lead_id IS NOT NULL 
					AND disposition_id IS NOT NULL
				',
				'params' => array(
					':date' => $data->date
				),
			));
			
			echo number_format($dialCount);
		?>
	</td>
</tr>