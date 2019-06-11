<tr>

	<td><?php echo $data->skill->skill_name; ?></td>
	
	<td><?php echo $data->customer->getFullName(); ?></td>
	
	<td><?php echo $data->auto_email_frequency; ?></td>
	
	<td><?php echo $data->type == 1 ? 'Send to customer files' : 'Send to email'; ?></td>
	
	<td><?php echo $data->auto_email_recipients; ?></td>
	
	<td class="center">
		<button id="<?php echo $data->id; ?>" class="btn btn-mini btn-danger gskill-btn-remove"><i class="fa fa-times"></i> Remove</button>
	</td>
	
</tr>