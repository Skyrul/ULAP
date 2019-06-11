<?php 
	Yii::app()->clientScript->registerScript('btn-add-schedule-js','
		var ctr = 0;
		$(".btn-add-schedule").on("click",function(){
			
			var dayVal = $(this).data("day");		
			var containerObj = $(this).parent().parent().parent().parent().parent().find(".new-schedule-container");
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/stateSchedule/addNewSchedule').'",
				method: "GET",
				data: {
				  "day" : dayVal,					  
				  "ctr" : ctr,					  
				}
			}).success(function(response) {
				containerObj.append(response);
				
				ctr++;
			});
			
			
		});
		
		$("body").on("click",".btn-remove-sched",function(){
			var containerObj = $(this).parent().parent().remove();
			
		});
		
	',CClientScript::POS_END); 
	
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'state-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">	
		<div class="col-md-12">
		<?php echo CHtml::hiddenField('StateSchedule[state_id]',$state->id); ?>
		</div>
	</div>

	
	<?php 
		$week = array(
			1 =>'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday',
			7 =>'Sunday',
		);
			
		for($day = 1; $day <= 7; $day++){
			
	?>
			
		<div class="row">	
			<div class="col-md-7">
				<div class="widget-box">
					<div class="widget-header">
						<h4 class="widget-title">
							<div class="row">
								<div class="col-md-6">
									<?php echo $week[$day]; ?>
								</div>
								<div class="col-md-6 text-right">
									<?php echo CHtml::button('Add Start Time',array('class'=>'btn btn-xs btn-info btn-add-schedule', 'data-day'=> $day)); ?>
								</div>
							</div>
						</h4>
					</div>
					
					<div class="widget-body">
						<div class="widget-main">	
							<?php if(isset($state->stateSchedulesArray[$day])){ ?>
							
								<?php foreach($state->stateSchedulesArray[$day] as $modelAttr){ ?>
								<?php $this->renderPartial('_formSchedule',array(
									'model' => $model,
									'day' => $day,
									'state' => $state,
									'name' => $modelAttr['id'],
									'modelValue' => $modelAttr,
								)); ?>
								<?php } ?>
							<?php } ?>
							
							<div class="new-schedule-container"></div>
						</div>
					</div>
				</div>
				
				<div class="space-6"></div>
			</div>
		</div>
	<?php } ?>
		
	<div class="space-12"></div>
	
	<div class="clearfix form-actions center">
		<div class="col-sm-12">
			<?php echo CHtml::submitButton('Save' ,array('class'=>'btn btn-sm btn-primary')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->