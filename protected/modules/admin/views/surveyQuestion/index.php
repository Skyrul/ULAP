<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'survey'
	));
?>

<div class="page-header">
	<h1>
		<?php echo $survey->survey_name; ?> Survey Question
		
		<?php
			// if( Yii::app()->user->account->checkPermission('structure_skills_disposition_add_button','visible') )
			// {
				echo CHtml::link('<i class="fa fa-plus"></i> Add Survey Question',array('create','survey_id'=>$survey->id),array('class'=>'btn btn-primary btn-xs')); 
				echo '&nbsp;'.CHtml::link('<i class="fa fa-search"></i> View Survey Form',array('viewSurveyForm','survey_id'=>$survey->id),array('class'=>'btn btn-primary btn-xs')); 
			// }
		?>
		
		
	</h1>
</div>

<div class="col-sm-12">
	<table class="table table-striped table-condensed table-hover">	
		<tr>
			<th>Order</th>
			<th>Survey Question</th>
			<th>Is Conditional</th>
			<th>Type</th>
			<th>Option</th>
			<th>Action</th>
		</tr>
		<?php 
			foreach($surveyQuestions as $surveyQuestion)
			{
			?>
				<tr>
					<td><?php echo $surveyQuestion->question_order; ?> </td>
					<td><?php echo $surveyQuestion->survey_question; ?> </td>
					<td><?php echo !empty($surveyQuestion->is_child_of_id) ? 'Yes (SQ ID: '.$surveyQuestion->is_child_of_id.')' : 'No'; ?></td>
					<td><?php echo $surveyQuestion->input_type; ?> </td>
					<td style="width:500px;word-break:break-all;"><?php echo $surveyQuestion->input_options;?> </td>
					
					<td>
						<?php 
							// if( Yii::app()->user->account->checkPermission('structure_skills_disposition_edit_button','visible') )
							// {
								echo CHtml::link('<i class="fa fa-pencil"></i> Edit',array('surveyQuestion/update', 'id'=>$surveyQuestion->id, 'survey_id'=>$survey->id),array('class'=>'btn btn-xs btn-info btn-minier')); 
							// }
						?>
						
						
						
						<?php 
							// if( Yii::app()->user->account->checkPermission('structure_skills_disposition_delete_button','visible') )
							// {
								echo CHtml::link('<i class="fa fa-times"></i> Delete',array('surveyQuestion/delete', 'id'=>$surveyQuestion->id, 'survey_id'=>$survey->id),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
							// }
						?>
					</td>
					
				</tr>
			<?php
			}
		?>
	</table>
</div>