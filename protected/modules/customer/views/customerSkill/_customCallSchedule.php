<?php 
$isCustomerDisabled = "";
														
if(Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff())
	$isCustomerDisabled = 'disabled';
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	// 'id'=>'customer-skill-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">	
		<div class="col-md-12">
		<?php echo CHtml::hiddenField('CustomerSkillSchedule[customer_skill_id]',$selectedCustomerSkill->id); ?>
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
									<?php if(empty($isCustomerDisabled)){ ?>
										<?php echo CHtml::button('Add Start Time',array('class'=>'btn btn-xs btn-info btn-add-schedule', 'data-day'=> $day)); ?>
									<?php } ?>
								</div>
							</div>
						</h4>
					</div>
					
					<div class="widget-body">
						<div class="widget-main no-padding">	
							<?php if(isset($selectedCustomerSkill->customerSkillSchedulesArray[$day])){ ?>
							
								<?php foreach($selectedCustomerSkill->customerSkillSchedulesArray[$day] as $modelAttr){ ?>
								<?php $this->renderPartial('_customFormSchedule',array(
									'model' => $model,
									'day' => $day,
									'selectedCustomerSkill' => $selectedCustomerSkill,
									'name' => $modelAttr['id'],
									'modelValue' => $modelAttr,
									'isCustomerDisabled' => $isCustomerDisabled,
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
		
	
	<div class="row buttons">
		<div class="col-md-12">
			<?php if(empty($isCustomerDisabled)){ ?>
				<?php echo CHtml::submitButton('Save Custom Schedule' ,array('class'=>'btn btn-success btn-xs')); ?>
			<?php } ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->