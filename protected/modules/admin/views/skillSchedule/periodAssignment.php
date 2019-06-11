<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<?php 
$week = array(
	// 1 =>'Monday',
	// 2 => 'Tuesday',
	// 3 => 'Wednesday',
	// 4 => 'Thursday',
	// 5 => 'Friday',
	// 6 => 'Saturday',
	// 7 =>'Sunday',
	8 =>'Period A',
	9 =>'Period B',
	10 =>'Period C',
);
?>
<h1>Period Assignment <small><?php echo $skill->skill_name; ?></small></h1>


<br>

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
		<?php //echo CHtml::dropDownList('SkillSchedule[skill_id]',$skill->id, CHtml::listData(Skill::model()->byIsDeletedNot()->findAll(),'id','skill_name'),array('empty'=>'-Select Skill-')); ?>
		<?php echo CHtml::hiddenField('SkillSchedule[skill_id]',$skill->id); ?>
		</div>
	</div>

	<?php
	for($day = 8; $day <= 10; $day++){
			
	?>
			
		<div class="row">	
			<div class="col-md-5">
				<div class="widget-box">
					<div class="widget-header">
						<h4 class="widget-title">
							<div class="row">
								<div class="col-md-6">
									<?php echo $week[$day]; ?>
								</div>
								<div class="col-md-6 text-right">
									<?php //echo CHtml::button('Add Schedule',array('class'=>'btn btn-xs btn-info btn-add-schedule', 'data-day'=> $day)); ?>
								</div>
							</div>
						</h4>
					</div>
					
					<div class="widget-body">
						<div class="widget-main no-padding">	
							<?php if(isset($skill->skillSchedulesArray[$day])){ ?>
							
								<?php foreach($skill->skillSchedulesArray[$day] as $modelAttr){ ?>
								<?php $this->renderPartial('_formScheduleAppointment',array(
									'model' => $model,
									'day' => $day,
									'skill' => $skill,
									'name' => $modelAttr['id'],
									'modelValue' => $modelAttr,
								)); ?>
								<?php } ?>
							<?php }else{ ?>
							
								<?php $this->forward('/admin/skillSchedule/addNewSchedule/day/'.$day.'/ctr/1/type/2', false); ?>
							<?php } ?>
							<div class="new-schedule-container"></div>
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