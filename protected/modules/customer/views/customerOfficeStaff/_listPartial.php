<tr>
	<td><?php echo $data->id; ?></td>
	<td><?php echo $data->customer->fullNameReverse; ?></td>
	<td><?php echo $data->customerOffice->office_name; ?></td>
	<td><?php echo $data->staff_name; ?></td>
	<td><?php echo $data->email_address; ?></td>
	<td><?php echo $data->position; ?></td>
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		<?php echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?> 
		<?php echo CHtml::link('Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
	</td>
</tr>