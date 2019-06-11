<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id ? Customer::model()->findByPk($customer_id) : null,
	));
?>


<div class="page-header">
	<h1>
		<?php echo $survey->survey_name; ?>
	</h1>
</div>

<div class="col-sm-12">
	<table class="table table-striped table-condensed table-hover">	
		<tr>
			<th>Lead Name</th>
			<th>Date Created</th>
			<th>Action</th>
		</tr>
		<?php 
			foreach($surveyAnswers as $surveyAnswer)
			{
			?>
				<tr>
					<td><?php echo $surveyAnswer->lead->getFullName(); ?></td>
					<td><?php echo date("F d, Y",strtotime($surveyAnswer->lead->date_created)); ?></td>
					<td>
						<?php
							echo CHtml::link('<i class="fa fa-search"></i> View',array('survey/answer', 'lead_id'=>$surveyAnswer->lead_id, 'id'=>$survey->id),array('class'=>'btn btn-xs btn-info btn-minier')); 
				
				
				
				
							//echo CHtml::link('<i class="fa fa-times"></i> Delete',array('survey/answer/lead_id', 'lead_id'=>$surveyAnswer->lead_id, 'id'=>$survey->id),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
						?>
					</td>
					
				</tr>
			<?php
			}
		?>
	</table>
</div>