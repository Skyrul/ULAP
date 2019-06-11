<tr>
	<td><?php echo $data->skill_name; ?></td>
	<td><?php echo isset($data->company) ? $data->company->company_name : null; ?></td>
	<td><?php echo $data->getStatusLabel(); ?></td>

	<?php 
		if( Yii::app()->user->account->checkPermission('structure_skills_disposition_link','visible') )
		{
			echo '<td>'.CHtml::link('Disposition',array('skillDisposition/index','skill_id'=>$data->id)).'</td>'; 
		}
	?>
	
	<td><?php echo CHtml::link('Schedule',array('skillSchedule/update','skill_id'=>$data->id)); ?></td>
	<td><?php echo CHtml::link('Period Assignment',array('skillSchedule/periodAssignment','skill_id'=>$data->id)); ?></td>
	<td><?php echo CHtml::link('Child Skills',array('skillChild/index','skill_id'=>$data->id)); ?></td>
	
	
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		
		<?php
			if( Yii::app()->user->account->checkPermission('structure_skills_edit_button','visible') )
			{
				echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-minier btn-info')); 
			}
		?> 
		
		<?php 
			if( Yii::app()->user->account->checkPermission('structure_skills_delete_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-times"></i> Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-minier btn-danger')); 
			}
		?>
	</td>
</tr>