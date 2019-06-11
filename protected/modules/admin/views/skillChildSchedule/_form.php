<?php
/* @var $this SkillChildScheduleController */
/* @var $model SkillChildSchedule */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'skill-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">	
		<div class="col-md-12">
		<?php //echo CHtml::label($model->getAttributeLabel('skill_id'), ''); ?>
		<?php //echo CHtml::dropDownList('SkillChildSchedule[skill_id]',$skill->id, CHtml::listData(Skill::model()->byIsDeletedNot()->findAll(),'id','skill_name'),array('empty'=>'-Select Skill-')); ?>
		<?php echo CHtml::hiddenField('SkillChildSchedule[skill_child_id]',$skillChild->id); ?>
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
			<div class="col-md-8">
				<div class="widget-box">
					<div class="widget-header">
						<h4 class="widget-title"><?php echo $week[$day]; ?></h4>
					</div>
					
					<div class="widget-body">
						<div class="widget-main no-padding">		
							<div class="row">
								<div class="col-md-2">
									<?php echo CHtml::label($model->getAttributeLabel('schedule_start'),''); ?>
									<?php echo CHtml::dropDownList('SkillChildSchedule[schedule_day]['.$day.'][schedule_start]', @$skillChild->skillChildSchedulesArray[$day]['schedule_start'], SkillChildSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
									<?php //echo $form->error($model,'schedule_start'); ?>
								</div>

								<div class="col-md-2">
									<?php echo CHtml::label($model->getAttributeLabel('schedule_end'),''); ?>
									<?php echo CHtml::dropDownList('SkillChildSchedule[schedule_day]['.$day.'][schedule_end]', @$skillChild->skillChildSchedulesArray[$day]['schedule_end'], SkillChildSchedule::listScheduleTime(),array('empty'=>'-- : --')); ?>
									<?php //echo $form->error($model,'schedule_end'); ?>
								</div>

								<div class="col-md-4">
									<?php echo CHtml::label($model->getAttributeLabel('status'),''); ?>
									<?php echo CHtml::dropDownList('SkillChildSchedule[schedule_day]['.$day.'][status]', @$skillChild->skillChildSchedulesArray[$day]['status'],  SkillChildSchedule::listStatus()); ?>
									<?php ///echo $form->error($model,'status'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="space-6"></div>
			</div>
		</div>
	<?php } ?>
		
	
	<div class="row buttons">
		<div class="col-md-12">
		<?php echo CHtml::submitButton('Save' ,array('class'=>'btn btn-success')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->