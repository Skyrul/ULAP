<tr>
	<td><?php echo $data->id; ?></td>
	<td><?php echo $data->survey_name; ?></td>
	<td><?php echo $data->description; ?></td>
	<td><?php echo date("F d, Y",strtotime($data->date_created)); ?></td>
	<td><?php echo $data->getStatusLabel(); ?></td>
	<td><?php echo CHtml::link('Survey Questions',array('surveyQuestion/index','survey_id'=>$data->id)); ?></td>
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		
		<?php
			if( Yii::app()->user->account->checkPermission('structure_survey_edit_button','visible') )
			{
				echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-minier btn-info')); 
			}
		?> 
		
		<?php 
			if( Yii::app()->user->account->checkPermission('structure_survey_delete_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-times"></i> Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-danger btn-minier')); 
			}
		?>
	</td>
</tr>