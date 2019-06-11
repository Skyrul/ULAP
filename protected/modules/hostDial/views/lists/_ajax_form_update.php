<?php Yii::app()->clientScript->registerScript('formJs','
	//datepicker plugin
	//link
	$(".date-picker").datepicker({	 
		autoclose: true,
		todayHighlight: true
	});


	//$( "#'.CHtml::activeId($model,'skill_id').'" ).trigger( "change" );		

	$("#viewSurvey").on("click",function(e){
		var surveyId = $("#'.CHtml::activeId($model,'survey_id').'").val();
	
		if(surveyId > 0)
		{
			$(this).prop("href","'.Yii::app()->createUrl('/hostDial/survey/export',array('customer_id'=>$model->customer_id)).'/id/"+surveyId+"/list_id/'.$model->id.'");
		}
		else
		{
			alert("Select Survey");
			e.preventDefault();
		}
	});
	
	$("#viewSurveyPDF").on("click",function(e){
		var surveyId = $("#'.CHtml::activeId($model,'survey_id').'").val();
	
		
		if(surveyId > 0)
		{
			$(this).prop("href","'.Yii::app()->createUrl('/hostDial/survey/exportPDF',array('customer_id'=>$model->customer_id)).'/id/"+surveyId+"/list_id/'.$model->id.'");
		}
		else
		{
			alert("Select Survey");
			e.preventDefault();
		}
	});	

	$("#'.CHtml::activeId($model,'survey_id').'").on("change", function(){
		var surveyId = $(this).val();
		if(surveyId > 0)
		{
			$("#viewSurvey").show();
			$("#viewSurveyPDF").show();
		}
		else
		{
			$("#viewSurvey").hide();
			$("#viewSurveyPDF").hide();
		}
	});
	
',CClientScript::POS_END); ?>

<style>
	div > label{font-weight:700}
	span > label{display:inline-block !important;}
</style>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<div class="form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'hostDial_lists-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// See class documentation of CActiveForm for details on this,
	// you need to use the performAjaxValidation()-method described there.
	'enableAjaxValidation'=>true,
	'enableClientValidation' => false,
	'htmlOptions'=>array(
	   'onsubmit'=>"return false;",/* Disable normal form submit */
	),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
		'validateOnChange' => false,
		'beforeValidate' => 'js:function(form){
			return true;
		}',
		'validateOnSubmit'=>true, // Required to perform AJAX validation on form submit
		'afterValidate'=>'js:function(form, data, hasError){
			if(!hasError)
			{
				
				jQuery.ajax({
					url: "'.$actionController.'",
					type: "POST",
					data: jQuery(form).serialize(),
					dataType: "json",
					beforeSend: function(){
					},
					success: function(response){
						
						alert(response.message);
						if(response.success == true || response.success == "true"){
							jQuery(form).closest(".modal").modal("hide");
							
							//$.fn.yiiListView.update("ptoFormList", {});
							 location.reload(); 
						}
					},
				});
			}
			// Always return false so that Yii will never do a traditional form submit
			return false;
		}', // Your JS function to submit form
	),
	'action' => $actionController,
)); ?>
	
	<?php echo $form->errorSummary($model); ?>
	<p class="note">Fields with <span class="required">*</span> are required.</p>
	<div class="row">
		<div class="col-md-12">

			<!-- FORM -->
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'name'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->textField($model,'name',array('class'=>'form-control')); ?>
						
						<?php echo $form->error($model,'name'); ?>
					</div>
				</div>
			</div>
			
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'status'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->dropDownList($model,'status', Lists::getStatusOptions(), array('class'=>'form-control')); ?>
						
						<?php echo $form->error($model,'status'); ?>
					</div>
				</div>
			</div>

	
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'skill_id'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->dropDownList($model,'skill_id', CustomerSkill::items($customer_id), array('class'=>'form-control', 'style'=>'')); ?>
						
						<?php echo $form->error($model,'skill_id'); ?>
					</div>
				</div>
			</div>


			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name"><?php echo $form->labelEx($model,'survey_id'); ?></div>

					<div class="profile-info-value">
						<?php echo $form->dropDownList($model, 'survey_id', Survey::items($model->skill_id, $model->customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
					</div>
				</div>
				
				<div class="profile-info-name"></div>
				<div class="profile-info-value">
					<?php echo CHtml::link('View survey results', array('survey/export','customer_id'=>$model->customer_id,'id'=>$model->survey_id,'list_id'=>$model->id),array('target'=>'_blank','id'=>'viewSurvey')); ?> &nbsp;
					<?php echo CHtml::link('View survey analytic pdf', array('survey/exportPDF','customer_id'=>$model->customer_id,'id'=>$model->survey_id,'list_id'=>$model->id),array('target'=>'_blank','id'=>'viewSurveyPDF')); ?> <br>
				</div>
			</div>
		
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'dialing_as_number'); ?></div>
					<div class="profile-info-value">
						
						<?php 
							$dialingAsOptions = array(
								1 => 'Dial As Office Phone number'
							);
							
							$hostManagerPhones = CustomerOfficeStaff::model()->findAll(array(
								'condition' => '
									customer_id = :customer_id
									AND use_phone_as_dial_as_option = 1
									AND phone IS NOT NULL
									AND phone != ""
								',
								'params' => array(
									':customer_id' => $model->customer_id
								)
							));
							
							if( $hostManagerPhones )
							{
								foreach( $hostManagerPhones as $hostManagerPhone )
								{
									$dialingAsOptions[$hostManagerPhone->id] = $hostManagerPhone->staff_name.' - '.$hostManagerPhone->phone;
								}
							}
						?>	
						
						<?php echo $form->dropDownList($model,'dialing_as_number', $dialingAsOptions, array('class'=>'form-control')); ?>
						
						<?php echo $form->error($model,'dialing_as_number'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'lead_ordering'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->dropDownList($model,'lead_ordering', $model->getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'')); ?>
						
						<?php echo $form->error($model,'lead_ordering'); ?>
					</div>
				</div>
			</div>
			
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'language'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->dropDownList($model,'language', $model::getLanguageOptions(), array('class'=>'form-control', 'style'=>'')); ?>
						
						<?php echo $form->error($model,'language'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'number_of_dials_per_guest'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->textField($model,'number_of_dials_per_guest',array('class'=>'form-control')); ?>
						
						<?php echo $form->error($model,'number_of_dials_per_guest'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'start_date'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->textField($model,'start_date',array('class'=>'form-control date-picker')); ?>
						
						<?php echo $form->error($model,'start_date'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'end_date'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->textField($model,'end_date',array('class'=>'form-control date-picker')); ?>
						
						<?php echo $form->error($model,'end_date'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'is_default_call_schedule'); ?></div>
					<div class="profile-info-value">
						
						<?php if( Yii::app()->user->account->checkPermission('customer_skills_custom_call_schedule_on_off_button','visible') ){ ?>
							<?php $defaultCallScheduleOptions = array('1'=>'Default Call Schedule','0'=>'Custom Call Schedule'); ?>
						<?php }else{ ?>
							<?php $defaultCallScheduleOptions = array('1'=>'Default Call Schedule'); ?>
						<?php } ?>
						
						<?php echo $form->dropDownList($model,'is_default_call_schedule', $defaultCallScheduleOptions, array('class'=>'form-control',)); ?>
						
						<?php if( Yii::app()->user->account->checkPermission('customer_skills_custom_call_schedule_on_off_button','visible') ){ ?>
						
						<?php echo CHtml::link('Edit Custom Schedule', array('customerSkill/index', 'customer_id'=>$customer_id, 'options[customerSkillTab]' => $model->skill_id, 'options[customerSkillSubTab]' => 'customer_skills_custom_call_schedule_tab'), array('class'=>'btn btn-minier btn-primary', 'style'=>'margin-top:8px;')); ?>
						<?php } ?>
						
						<?php echo $form->error($model,'is_default_call_schedule'); ?>
					</div>
				</div>
			</div>
			
			<?php if( $model->skill->enable_list_custom_mapping == 1 ): ?>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><label>Data Tab</label></div>
					<div class="profile-info-value">
						
						<?php 
							echo CHtml::link('Edit Custom Mapping', array('lists/customMapping', 'id'=>$model->id, 'customer_id'=>$customer_id), array('class'=>'btn btn-minier btn-purple'));
						?>
					</div>
				</div>
			</div>
			
			<?php endif; ?>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'time_zone_assignment'); ?></div>
					<div class="profile-info-value">
						
						<?php echo $form->dropDownList($model,'time_zone_assignment',array('area'=>'By Area Code','zip_code'=>'By Zip Code'), array('class'=>'form-control', 'empty'=>'-Select Time Zone Assignment-')); ?>
						
						
						
						<?php echo $form->error($model,'time_zone_assignment'); ?>
					</div>
				</div>
			</div>

			<div class="form-actions center">
				
				<button type="button" class="btn btn-sm btn-primary" onClick='$(this).closest("form").submit();'>
					Save <i class="fa fa-arrow-right"></i>
				</button>
			</div>

			<!-- END OF FORM -->
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->

<?php Yii::app()->clientScript->registerScript('toggleJs','

	// $("input[name=\"AccountPtoForm[is_full_shift]\"]").on("change",function(){
		
		
		// var thisVal = $("input[name=\"AccountPtoForm[is_full_shift]\"]:checked").val();
		
		// if(thisVal == 1)
		// {
			// $("#js-is_full_shift-container").hide();
		// }
		// else
		// {
			// $("#js-is_full_shift-container").show();
		// }
	// });
	
',CClientScript::POS_END); ?>