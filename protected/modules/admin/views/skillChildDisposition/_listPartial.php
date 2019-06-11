<tr>
	<td><?php echo $data->child_name; ?></td>
	<td><?php echo $data->status; ?></td>
	<td><?php echo CHtml::link('Child Disposition',array('skillChildDisposition/index','skill_id'=>$data->id)); ?></td>
	<td><?php echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?></td>
</tr>