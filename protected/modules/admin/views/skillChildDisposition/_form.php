<?php
/* @var $this SkillDispositionController */
/* @var $model SkillDisposition */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScript(uniqid(), '
		
	$("#SkillChildDisposition_retry_interval_type").on("change", function (){
		
		var type = $(this).val();
		var retry_interval_dropdown = $("#SkillChildDisposition_retry_interval");
		
		$.ajax({
			url: yii.urls.baseUrl + "/admin/skillChildDisposition/ajaxUpdateRetryIntervalOptions",
			type: "post",
			dataType: "json",
			data: { "ajax":1, "type":type },
			beforeSend: function(){ 
				retry_interval_dropdown.prop("disabled", true);
				retry_interval_dropdown.html("<option value=\"\" selected>Updating...</option>");
			},
			success: function( response ){
				
				retry_interval_dropdown.html(response.html);
				
				retry_interval_dropdown.prop("disabled", false);
			}
		});
		
	});
	
', CClientScript::POS_END);
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'skill-disposition-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('class'=>'form'),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>


	<div class="form-group">
		<?php echo $form->labelEx($model,'skill_child_disposition_name', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->textField($model,'skill_child_disposition_name',array('size'=>60,'maxlength'=>128)); ?>
			<?php echo $form->error($model,'skill_child_disposition_name'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'description'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'mark_as_goal', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'mark_as_goal',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'mark_as_goal'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'sub_dispo_is_required', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'sub_dispo_is_required',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'sub_dispo_is_required'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'is_voice_contact', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->dropDownList($model,'is_voice_contact',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_voice_contact'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'is_complete_leads', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->dropDownList($model,'is_complete_leads',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_complete_leads'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_callback', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->dropDownList($model,'is_callback',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_callback'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'retry_interval_type', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'retry_interval_type',array(1=> 'HR', 2 => 'Days')); ?>
			<?php echo $form->error($model,'retry_interval_type'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'retry_interval', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->dropDownList($model,'retry_interval',SkillChildDisposition::listIntervals(),array('empty'=>'--')); ?>
			<?php echo $form->error($model,'retry_interval'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'is_send_email', array('class'=>'control-label col-sm-2')); ?>
		
		<div class="col-sm-10">
			<?php echo $form->dropDownList($model,'is_send_email',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_send_email'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_send_text', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_send_text',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_send_text'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_do_not_call', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_do_not_call',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_do_not_call'); ?>
		</div>
	</div>
		
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_bad_phone_number', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_bad_phone_number',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_bad_phone_number'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_appointment_set', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_appointment_set',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_appointment_set'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_location_conflict', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_location_conflict',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_location_conflict'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_schedule_conflict', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_schedule_conflict',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_schedule_conflict'); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'is_appointment_cancelled', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_appointment_cancelled',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_appointment_cancelled'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'is_appointment_reschedule', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->dropDownList($model,'is_appointment_reschedule',array(1=> 'Yes', 0 => 'No')); ?>
			<?php echo $form->error($model,'is_appointment_reschedule'); ?>
		</div>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'notes_prefill', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
		
		<div class="col-sm-9">
			<?php echo $form->textArea($model,'notes_prefill',array('cols'=>60)); ?>
			<?php echo $form->error($model,'notes_prefill'); ?>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
	<div class="form-actions text-center">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->