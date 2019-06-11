<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.bootstrap-duallistbox.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/template_assets/js/fuelux/fuelux.spinner.min.js',  CClientScript::POS_END); ?>

<style>
 .spinner-up, .spinner-down{ 
  font-size: 10px !important;
  height: 16px !important;
  line-height: 8px !important;
  margin-left: 0 !important;
  padding: 0 !important;
  width: 22px !important;
 }
</style>
<?php 
Yii::app()->clientScript->registerScript('spinnerJS', '

	$(".spinner").ace_spinner({value:0,min:1,max:9,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"}).on("change", function(){
		
		if( this.value < 1)
		{
			this.value = 1;
		}

		if( this.value > 9)
		{
			this.value = 9;
		}
	});
	 
	$(".spinner2digit").ace_spinner({value:0,min:1,max:99,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"}).on("change", function(){
		
		if( this.value < 1)
		{
			this.value = 1;
		}

		if( this.value > 99)
		{
			this.value = 99;
		}
	});
   
   $(".spinner3digit").ace_spinner({value:0,min:0,max:120,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"}).on("change", function(){
		
		if( this.value < 1)
		{
			this.value = 0;
		}

		if( this.value > 120)
		{
			this.value = 120;
		}
	});
   
', CClientScript::POS_END);
 ?>
 
<?php 
	Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END);
 ?>
 
 <?php 
	Yii::app()->clientScript->registerScript('caller-option-dial-as-container-script', '
		$("#'.CHtml::activeId($model,'caller_option').'").on("change",function(){
			var callerOptionVal = $(this).val();
			
			if(callerOptionVal == "'.Skill::CALLER_OPTION_DIAL_AS.'")
			{
				$(".caller-option-dial-as-container").show();
			}
			else
			{
				$(".caller-option-dial-as-container").hide();
			}
		}); 

		$("#'.CHtml::activeId($model,'caller_option').'").trigger("change");
		
', CClientScript::POS_END);
 ?>

  <?php 
	Yii::app()->clientScript->registerScript('has_inbound-script', '
		$("#'.CHtml::activeId($model,'has_inbound').'").on("change",function(){
			var hasInboundVal = $(this).val();
			
			if(hasInboundVal == 1)
			{
				$(".has-inbound-container").show();
			}
			else
			{
				$(".has-inbound-container").hide();
			}
		}); 

		$("#'.CHtml::activeId($model,'has_inbound').'").trigger("change");
		
', CClientScript::POS_END);
 ?>
 
 
 
 
<?php 
	Yii::app()->clientScript->registerScript('assignedAgenetDualListScript','
	
	// var demo1 = $(\'select[name="SkillAccount[trained][]"]\').bootstrapDualListbox({
				// nonSelectedListLabel: "Assigned Agents - Not Trained",
				// selectedListLabel: "Assigned Agents - Trained",
			// });
				// var container1 = demo1.bootstrapDualListbox("getContainer");
				// container1.find(".btn").addClass("btn-white btn-info btn-bold");
				
	// $(".bootstrap-duallistbox-container .btn-group").hide();
	
	',CClientScript::POS_END);
?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<?php 
	Yii::app()->clientScript->registerScript('sortable-script','
	
	$("#sortable1, #sortable2" ).sortable({
      connectWith: ".connectedSortable",
	  receive: function(event, ui) {
		  
		var containerId = $(this).attr("id");
		var listItem = ui.item;
		if(containerId == "sortable1")
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/skill/removeSkillAccount').'",
				type: "GET",	
				data: { 
					"skill_id" : "'.$model->id.'",
					"account_id" : listItem.data("id")
				},
				success: function(r){
					
				},
			});
		}
		
		if(containerId == "sortable2")
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/skill/addSkillAccount').'",
				type: "GET",	
				data: { 
					"skill_id" : "'.$model->id.'",
					"account_id" : listItem.data("id")
				},
				success: function(r){
					
				},
			});
		}
	}
	  
    }).disableSelection();
	
',CClientScript::POS_END);
 ?>
 
 <style>
#sortable1, #sortable2 {
	border: 1px solid #eee;
	width: 100%;
	min-height: 40px;
	list-style-type: none;
	margin: 0;
	padding: 5px 0 0 0;
	margin-right: 10px;
}
#sortable1 li, #sortable2 li {
	margin: 0 5px 5px 5px;
	padding: 5px;
	font-size: 1.2em;
	width: 95%;
}
</style>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'skill-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data'),
)); ?>
	
	<div class="col-md-6">
	
		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<?php echo $form->errorSummary($model); ?>

		<div class="row">
			<?php echo CHtml::label('Companies',null); ?>
			<?php echo CHtml::dropDownList('Skill[companyIds]', $selectedSkillCompany, CHtml::listData(Company::model()->findAll(),'id','company_name'), array('class'=>'select2','multiple'=>true) ); ?>
		</div>
		
		<div class="space-12"></div>

		<div class="row">
			<?php echo $form->labelEx($model,'status'); ?>
			<?php echo $form->dropDownList($model,'status',Skill::listStatus()); ?>
			<?php echo $form->error($model,'status'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'skill_name'); ?>
			<?php echo $form->textField($model,'skill_name',array('size'=>60,'maxlength'=>128)); ?>
			<?php echo $form->error($model,'skill_name'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'description'); ?>
			<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>255)); ?>
			<?php echo $form->error($model,'description'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_goal_disposition'); ?>
			<?php echo $form->dropDownList($model,'enable_goal_disposition', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_goal_disposition'); ?>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'max_numbers'); ?>
			<?php echo $form->textField($model,'max_numbers',array('size'=>5,'maxlength'=>250,'class'=>'spinner')); ?>
			<?php echo $form->error($model,'max_numbers'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'max_dials'); ?>
			<?php echo $form->textField($model,'max_dials',array('size'=>5,'maxlength'=>250,'class'=>'spinner')); ?>
			<?php echo $form->error($model,'max_dials'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'max_lead_life_before_recertify'); ?>
			<?php echo $form->textField($model,'max_lead_life_before_recertify',array('size'=>5,'maxlength'=>250,'class'=>'spinner2digit')); ?>
			<?php echo $form->error($model,'max_lead_life_before_recertify'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'customer_popup_delay'); ?>
			<?php echo $form->textField($model,'customer_popup_delay',array('size'=>5,'maxlength'=>250,'class'=>'spinner3digit')); ?>
			<?php echo $form->error($model,'customer_popup_delay'); ?>
		</div>
		
		<div class="space-12"></div>
				
		<div class="row">
			<?php echo $form->labelEx($model,'caller_option'); ?>
			<?php echo $form->dropDownList($model,'caller_option', Skill::listCallerOption()); ?>
			<?php echo $form->error($model,'caller_option'); ?>
		</div>
		
		<div class="caller-option-dial-as-container">
			<div class="row">
				<?php echo $form->labelEx($model,'phone_number'); ?>
				<?php echo $form->textField($model,'phone_number',array('size'=>20,'maxlength'=>250)); ?>
				<?php echo $form->error($model,'phone_number'); ?>
			</div>
			
			<div class="row">
				<?php echo $form->labelEx($model,'cnam'); ?>
				<?php echo $form->textField($model,'cnam',array('size'=>20,'maxlength'=>250)); ?>
				<?php echo $form->error($model,'cnam'); ?>
			</div>
		</div>
		
		<?php /* duplicate of has_inbound
		<div class="row">
			<?php echo $form->labelEx($model,'is_inbound_call'); ?>
			<?php echo $form->dropDownList($model,'is_inbound_call',array(0 => 'No', 1=> 'Yes'),array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'is_inbound_call'); ?>
		</div>
		*/ ?>
		
		<div class="row">
			<?php echo $form->labelEx($model,'has_inbound'); ?>
			<?php echo $form->dropDownList($model,'has_inbound',array(0 => 'No', 1=> 'Yes'),array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'has_inbound'); ?>
		</div>
		
		<div class="row has-inbound-container">
			<?php echo CHtml::link('<i class="fa fa-wrench"></i> IVR settings', 'javascript:void(0);', array('class'=>'btn btn-xs btn-default')); ?>
			
			<div class="space-6"></div>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'call_agent_lead_search_setting'); ?>
			<?php echo $form->dropDownList($model,'call_agent_lead_search_setting', Skill::listLeadSearchSetting(), array('empty' => '-Select-')); ?>
			<?php echo $form->error($model,'call_agent_lead_search_setting'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'max_agent_per_customer'); ?>
			<?php echo $form->dropDownList($model,'max_agent_per_customer', array('1'=>'Single', '999'=>'Unlimited')); ?>
			<?php echo $form->error($model,'max_agent_per_customer'); ?>
		</div>
		
		<?php /*<div class="row">
			<label>Active Agent Service Tabs</label>
			<?php echo CHtml::dropDownList('SkillServiceTab[tab_values][]', @$selectedServiceTab, SkillServiceTab::listServiceTab(),array('class' => 'select2','multiple'=>'multiple') ); ?>
		</div>*/ ?>
		
		<div class="space-6"></div>
		
		<div class="row">
			<h3 class="header smaller lighter blue">
				Active Agent Service Tabs
			</h3>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_dialer_appointment_tab'); ?>
			<?php echo $form->dropDownList($model,'enable_dialer_appointment_tab', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_dialer_appointment_tab'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_dialer_data_tab'); ?>
			<?php echo $form->dropDownList($model,'enable_dialer_data_tab', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_dialer_data_tab'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_dialer_script_tab'); ?>
			<?php echo $form->dropDownList($model,'enable_dialer_script_tab', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_dialer_script_tab'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_survey_tab'); ?>
			<?php echo $form->dropDownList($model,'enable_survey_tab', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_survey_tab'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_email_setting'); ?>
			<?php echo $form->dropDownList($model,'enable_email_setting', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_email_setting'); ?>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'fileUpload'); ?>
			<?php echo $form->fileField($model,'fileUpload'); ?>
			<?php echo $form->error($model,'fileUpload'); ?>
			
			<?php
				if( $model->script_tab_fileupload_id != null )
				{
					echo '<small>'; 
						echo'<i class="fa fa-paperclip"></i> Current Script Tab File: ' . CHtml::link($model->scriptFileupload->original_filename, array('download', 'id'=>$model->script_tab_fileupload_id));
					echo '</small>';
				}
			?>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<h3 class="header smaller lighter blue">
				Lead/List Settings
			</h3>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'use_system_default_list_settings'); ?>
			<?php echo $form->dropDownList($model,'use_system_default_list_settings', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'use_system_default_list_settings'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_list_custom_mapping'); ?>
			<?php echo $form->dropDownList($model,'enable_list_custom_mapping', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_list_custom_mapping'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_list_area_code_assignment'); ?>
			<?php echo $form->dropDownList($model,'enable_list_area_code_assignment', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_list_area_code_assignment'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'enable_specific_date_calling'); ?>
			<?php echo $form->dropDownList($model,'enable_specific_date_calling', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
			<?php echo $form->error($model,'enable_specific_date_calling'); ?>
		</div>
		
		<div class="space-6"></div>
		
		<div class="row">
			<h3 class="header smaller lighter blue">
				Workforce Management Fields
			</h3>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'workforce_dials'); ?>
			<?php echo $form->textField($model,'workforce_dials',array('size'=>20,'maxlength'=>250)); ?>
			<?php echo $form->error($model,'workforce_dials'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'workforce_appointments'); ?>
			<?php echo $form->textField($model,'workforce_appointments',array('size'=>20,'maxlength'=>250)); ?>
			<?php echo $form->error($model,'workforce_appointments'); ?>
		</div>
	</div>
	
	<?php if(!$model->isNewRecord){ ?>
	
	<div class="col-md-6">
		<div class="form-group">
			<div class="col-sm-12">
				<?php //echo CHtml::dropDownList('SkillAccount[trained][]', $skillAccountsArray['1'],CHtml::listData(Account::model()->byAccountTypeId(Account::TYPE_AGENT)->findAll(),'id','fullName'),array('multiple'=>'multiple') ); ?>
				
				<div class="row">
					<div class="col-sm-6">
						<label>Trained - Not Assigned</label>
						<ul id="sortable2" class="connectedSortable">
							<?php 
							
								$trainedAgents = AccountSkillTrained::model()->findAll(array(
									'with' => 'account', 
									'condition' => 'skill_id = :skill_id AND account.status=1',
									'params' => array(
										':skill_id' => $model->id,
									),
								));
							
								foreach($trainedAgents as $trainedAgent)
								{
									echo '<li class="ui-state-default" data-id="'.$trainedAgent->account->id.'" >'.$trainedAgent->account->accountUser->getfullName().'</li>';
								}
							?>
						</ul>
					</div>
					
					<div class="col-sm-6">
						<label>Trained - Assigned</label>
						<ul id="sortable1" class="connectedSortable">
							<?php 
							
							// $criteria = new CDbCriteria;
							// $criteria->compare('skill_id', $model->id);
							// $skillAccountList = CHtml::listData(SkillAccount::model()->findAll($criteria),'agent_id','agent_id');
							
							$assignedAgents = AccountSkillAssigned::model()->findAll(array(
								'with' => 'account', 
								'condition' => 'skill_id = :skill_id AND account.status=1',
								'params' => array(
									':skill_id' => $model->id,
								),
							));
							
							foreach( $assignedAgents as $assignedAgent)
							{
								if( isset($assignedAgent->account->accountUser) )
								{
									$agentName = $assignedAgent->account->accountUser->getfullName();
								}
								else
								{
									$agentName = $assignedAgent->account->customerOfficeStaff->staff_name;
								}
								
								echo '<li class="ui-state-default" data-id="'.$assignedAgent->account->id.'" >'.$agentName.'</li>';
							}
						?>
						</ul>
					</div>
					
				</div>
				<br style="clear:both">
				<div class="hr hr-16 hr-dotted"></div>
			</div>
		</div>
	</div>
				
	<?php /*
	<div class="col-md-6">
		<div class="row">
			<div id="not-agentContainer">
				<label>Assigned Agents - Not Trained</label>
				<div class="col-md-12">
					<div class="row">
					
						<?php echo CHtml::dropDownList('SkillAccount[not_trained][]', @$skillAccountsArray['2'],CHtml::listData(Account::model()->byAccountTypeId(Account::TYPE_AGENT)->findAll(),'id','fullName'),array('class' => 'select2','multiple'=>'multiple') ); ?>
					</div>
				</div>
			</div>
			
			<br class="clearfix">
			<br class="clearfix">
			
			<div id="agentContainer">
				<label>Assigned Agents - Trained</label>
				<div class="col-md-12">
					<div class="row">
					
						<?php echo CHtml::dropDownList('SkillAccount[trained][]', @$skillAccountsArray['1'],CHtml::listData(Account::model()->byAccountTypeId(Account::TYPE_AGENT)->findAll(),'id','fullName'),array('class' => 'select2','multiple'=>'multiple') ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	*/ ?>
	<?php } ?>
	
	<div class="clearfix"></div>
				
	<div class="form-actions text-center">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
	</div>
	
<?php $this->endWidget(); ?>

<?php  Yii::app()->clientScript->registerScript('select2js', '

	$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END); ?>

</div><!-- form -->