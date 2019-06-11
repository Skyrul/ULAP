<tr>
	<td><?php echo $data->office_name; ?></td>
	<td><?php echo $data->email_address; ?></td>
	<td><?php echo $data->address; ?></td>
	<td><?php echo $data->phone; ?></td>
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		
		<?php echo CHtml::link('Staffs',array('customerOfficeStaff/index','customer_id'=>$data->customer_id, 'customer_office_id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		<?php echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?> 
		<?php echo CHtml::link('Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
	</td>
</tr>