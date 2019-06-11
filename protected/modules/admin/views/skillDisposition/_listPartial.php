<tr>
	<td><?php echo $data->skill_name; ?></td>
	<td><?php echo $data->status; ?></td>
	<td><?php echo CHtml::link('Disposition',array('skillDisposition/index','skill_id'=>$data->id)); ?></td>
	<td><?php echo CHtml::link('Schedule','#'); ?></td>
	<td><?php echo CHtml::link('Child Skills','#'); ?></td>
	<td><?php echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?></td>
</tr>