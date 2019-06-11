<?php
	if($model->isNewRecord){ 
		Yii::app()->clientScript->registerScript('showHide','
			$("#createNewOrCloneExisting").on("change",function(){
				$(".js-container-toggle").addClass("hidden");
				
				if($(this).val() == 1)
				{
					$(".js-container-toggle").removeClass("hidden");
					$("#clone-existing-dropdown-container").addClass("hidden");
					
					$("#submit-btn-container").removeClass("hidden");
				}
				else if($(this).val() == 2)
				{
					$(".js-container-toggle").addClass("hidden");
					$("#clone-existing-container").removeClass("hidden");
					$("#clone-existing-dropdown-container").removeClass("hidden");
					
					$("#submit-btn-container").removeClass("hidden");
				}
				else
				{
					$(".js-container-toggle").addClass("hidden");
					$("#submit-btn-container").addClass("hidden");
				}
			});
			
			$("#createNewOrCloneExisting").trigger("change");
			
		',CClientScript::POS_END); 
	}
	
	Yii::app()->clientScript->registerScript(uniqid(), '
		
		$("#SkillDisposition_retry_interval_type").on("change", function (){
			
			var type = $(this).val();
			var retry_interval_dropdown = $("#SkillDisposition_retry_interval");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/skillDisposition/ajaxUpdateRetryIntervalOptions",
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
		'htmlOptions' => array(
			'class'=>'form'
		),
	)); ?>

		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<br />
		
		<?php echo $form->errorSummary($model); ?>
		
		<?php if($model->isNewRecord){ ?>
		<div class="form-group">
			<?php echo CHtml::label('Create New or Clone Existing', '', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-9">
				<?php echo CHtml::dropDownList('createNewOrCloneExisting',@$_REQUEST['createNewOrCloneExisting'],array('1' => 'Create New', '2'=> 'Clone Existing'), array('empty' => '-Select an option-')); ?>
			</div>
		</div>
		<?php } ?>
		
		<div id="clone-existing-container" class="js-container-toggle">
			<?php
				$skillAndDispositionsArray = array();
				$skills = Skill::model()->findAll();
				foreach($skills as $skill)
				{
					foreach($skill->skillDispositions as $skillDisposition)
					{
						$skillAndDispositionsArray[$skill->skill_name][$skillDisposition->id] = $skillDisposition->skill_disposition_name;
					}
				}
			?>
			
			<?php if($model->isNewRecord){ ?>
			<div id="clone-existing-dropdown-container" class="js-container-toggle">
				<div class="form-group">
					<?php echo $form->labelEx($model,'existingId', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'existingId', $skillAndDispositionsArray,array('empty'=>'-Select Existing Disposition-') ); ?>
					<?php echo $form->error($model,'existingId'); ?>
					
					</div>
				</div>
			</div>
			<?php } ?>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'skill_id', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'skill_id',CHtml::listData(Skill::model()->byIsDeletedNot()->findAll(),'id','skill_name'),array('empty'=>'-Select Skill-', 'disabled'=> $model->isNewRecord ? false : true) ); ?>
					<?php echo $form->error($model,'skill_id'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'skill_disposition_name', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->textField($model,'skill_disposition_name',array('size'=>60,'maxlength'=>128)); ?>
					<?php echo $form->error($model,'skill_disposition_name'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'description', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>128)); ?>
					<?php echo $form->error($model,'description'); ?>
				</div>
			</div>
		
		</div>
		
		<div class="js-container-toggle">
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
				<?php echo $form->labelEx($model,'is_voice_contact', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'is_voice_contact',array(1=> 'Yes', 0 => 'No')); ?>
					<?php echo $form->error($model,'is_voice_contact'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'is_complete_leads', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
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
				<?php echo $form->labelEx($model,'retry_interval', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php 
						$retryIntervalOptions = $model->retry_interval_type == 1 ? SkillDisposition::listRetryIntervals() : SkillDisposition::listRetryDayIntervals();
						
						echo $form->dropDownList($model, 'retry_interval', $retryIntervalOptions, array('empty'=>'--')); 
					?>
					
					<?php echo $form->error($model,'retry_interval'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'is_send_email', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
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
				<?php echo $form->labelEx($model,'is_visible_on_report', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'is_visible_on_report',array(1=> 'Yes', 0 => 'No')); ?>
					<?php echo $form->error($model,'is_visible_on_report'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'recycle_interval', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'recycle_interval',SkillDisposition::listRecycleIntervals(),array('empty'=>'--')); ?>
					<?php echo $form->error($model,'recycle_interval'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'is_agent_ownership', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'is_agent_ownership',array(1=> 'Yes', 0 => 'No')); ?>
					<?php echo $form->error($model,'is_agent_ownership'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'ownership_reassignment', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->textField($model,'ownership_reassignment',array('size'=>5,'maxlength'=>2)); ?>
					<?php echo $form->error($model,'ownership_reassignment'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'skill_child_id', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'skill_child_id', CHtml::listData(SkillChild::model()->bySkillId($model->skill_id)->findAll(),'id','child_name'),array('empty'=>'--')); ?>
					<?php echo $form->error($model,'skill_child_id'); ?>
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
				<?php echo $form->labelEx($model,'is_survey_complete', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'is_survey_complete',array(1=> 'Yes', 0 => 'No')); ?>
					<?php echo $form->error($model,'is_survey_complete'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'is_email_require', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->dropDownList($model,'is_email_require',array(1=> 'Yes', 0 => 'No')); ?>
					<?php echo $form->error($model,'is_email_require'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'notes_prefill', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-9">
					<?php echo $form->textArea($model,'notes_prefill',array('cols'=>60)); ?>
					<?php echo $form->error($model,'notes_prefill'); ?>
				</div>
			</div>
		</div>
		
		<div class="clearfix"></div>
		
		<div id="submit-btn-container" class="clearfix form-actions text-center js-container-toggle">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
		</div>

	<?php $this->endWidget(); ?>

</div><!-- form -->