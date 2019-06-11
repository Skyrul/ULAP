<?php
/* @var $this ContractController */
/* @var $model Contract */
/* @var $form CActiveForm */


Yii::app()->clientScript->registerScript('volume_type_js','

	$("#contract-form").on("change", "#Contract_fulfillment_type", function(){
		selfVal = $(this).val();
		$(".subsidy-containers").hide();
		$("#btn-add-level").hide();
		
		if(selfVal != ""){
			$("#btn-add-level").show();
			
			if(selfVal == '.Contract::TYPE_FULFILLMENT_GOAL_VOLUME.')
			{
				$("#goal-volume-container").show();
			}
			
			if(selfVal == '.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.')
			{
				$("#lead-volume-container").show();
			}
		}
		
	});
	
	$("#Contract_fulfillment_type").trigger("change");
	
	
	var volTypeGoalCtr = '.(count($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) + 1).';
	var volTypeLeadCtr = '.(count($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) + 1).';
	
	$("#btn-add-level").on("click",function(){
		contractFulfillmentType = $("#Contract_fulfillment_type").val();
		
		if(contractFulfillmentType == '.Contract::TYPE_FULFILLMENT_GOAL_VOLUME.')
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/contract/addGoalVolume').'",
				data: {
				  "id" : "new-"+volTypeGoalCtr,				  
				  "goal" : null,				  
				  "amount" : null,				  
				  "type" : null,				  
				  "subsidy" : null,				  
				},
			}).success(function(response) {
				$("#goal-volume-container").append(response);
			});
			
			volTypeGoalCtr++;
		}
		
		if(contractFulfillmentType == '.Contract::TYPE_FULFILLMENT_LEAD_VOLUME.')
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/contract/addLeadVolume').'",
				data: {
				  "id" : "new-"+volTypeLeadCtr,				  
				  "goal" : null,				  
				  "amount" : null,				  
				  "type" : null,				  
				  "subsidy" : null,				  
				},
			}).success(function(response) {
				$("#lead-volume-container").append(response);
			});
			
			volTypeLeadCtr++;
		}

		
		
	});
	
	
	$(".btn-remove-volume").on("click",function(){
		$(this).closest( "table" ).remove();
	});
', CClientScript::POS_END);
?>

<?php 
	Yii::app()->clientScript->registerScript('start-fee-js','
	
		$("#'.CHtml::activeId($model,'is_fee_start_activate').'").on("change", function(){
			selfVal = $(this).val();
			if(selfVal == 1)
			{
				$(".start-fee-amount-container").show();
			}
			else
			{
				$(".start-fee-amount-container").hide();
			}
		});
		
		$("#'.CHtml::activeId($model,'is_fee_start_activate').'").trigger("change");
	',CClientScript::POS_END);
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'contract-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>
	
	<div class="row">
		<div class="col-md-12">
		
			<div class="form-group">
				<?php echo $form->labelEx($model,'status'); ?>
				<?php echo $form->dropDownList($model,'status',Contract::listStatus(), array('class'=>'form-control form-control-inline')); ?>
				<?php echo $form->error($model,'status'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'company_id'); ?>
				<?php echo $form->dropDownList($model,'company_id', CHtml::listData(Company::model()->findAll(),'id','company_name'), array('empty'=>'-Select Company-', 'class'=>'form-control form-control-inline') ); ?>
				<?php echo $form->error($model,'company_id'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'skill_id'); ?>
				<?php echo $form->dropDownList($model,'skill_id', CHtml::listData(Skill::model()->findAll(),'id','skill_name'), array('empty'=>'-Select Skill-', 'class'=>'form-control form-control-inline') ); ?>
				<?php echo $form->error($model,'skill_id'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'contract_name'); ?>
				<?php echo $form->textField($model,'contract_name',array('size'=>60,'maxlength'=>128, 'class'=>'')); ?>
				<?php echo $form->error($model,'contract_name'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>250, 'class'=>'')); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'billing_calculation'); ?>
				<?php echo $form->dropDownList($model,'billing_calculation', array('ONE-TIME'=>'ONE-TIME','BILLING CYCLE'=>'BILLING CYCLE'),array('empty'=>'-- --', 'class'=>'form-control form-control-inline')); ?>
				<?php echo $form->error($model,'billing_calculation'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'billing_date'); ?>
				<?php echo $form->dropDownList($model,'billing_date', array('1st Day of Month'=>'1st Day of Month', 'Immediately'=>'Immediately', 'Custom Date'=>'Custom Date'),array('class'=>'form-control form-control-inline')); ?>
				<?php echo $form->error($model,'billing_date'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'fulfillment_type'); ?>
				<?php echo $form->dropDownList($model,'fulfillment_type', Contract::listTypeFulfillment() ,array('empty' => '-- --', 'class'=>'form-control form-control-inline','style'=>'display:inline;')); ?>
				<span><?php echo CHtml::button('Add Level', array('class'=>'btn btn-xs btn-success','id'=>'btn-add-level')); ?></span>
				<?php echo $form->error($model,'fulfillment_type'); ?>
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-md-8">
				
					
					<?php echo $form->error($model,'subsidyLevelArray'); ?>
					
					<div class="subsidy-containers" id="goal-volume-container">
						<?php 
							if(!empty($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME])){
								foreach($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel){
									$this->renderPartial('_goalVolume',array('subsidyLevel' => $subsidyLevel));
								}
							} 
						?>
					</div>
					
					<div class="subsidy-containers" id="lead-volume-container">
						<?php 
							if(!empty($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME])){
								foreach($model->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel){
									$this->renderPartial('_leadVolume',array('subsidyLevel' => $subsidyLevel));
								}
							} 
						?>
					</div>
					
					</div>
				</div>
			</div>
			
			<?php /*
			
			<div class="row">
				<?php echo $form->labelEx($model,'is_subsidy'); ?>
				<?php echo $form->dropDownList($model,'is_subsidy',array(0 => 'No', 1=> 'Yes'),array('class'=>'form-control form-control-inline')); ?>
				<?php echo $form->error($model,'is_subsidy'); ?>
			</div>

			<div class="row">
				<?php echo $form->labelEx($model,'reference_subsidy_id_reference_id'); ?>
				<?php echo $form->dropDownList($model,'reference_subsidy_id_reference_id', Contract::subsidyList(),array('empty'=>'-Select Subsidy-')); ?>
				<?php echo $form->error($model,'reference_subsidy_id_reference_id'); ?>
			</div>
			
			
			<div class="row">
				<?php echo $form->labelEx($model,'subsidy_name'); ?>
				<?php echo $form->textField($model,'subsidy_name',array('size'=>60,'maxlength'=>128, 'class'=>'')); ?>
				<?php echo $form->error($model,'subsidy_name'); ?>
			</div>

			<div class="row">
				<?php echo $form->labelEx($model,'subsidy_expiration'); ?>
				<?php echo $form->textField($model,'subsidy_expiration', array('class' => '')); ?>
				<?php echo $form->error($model,'subsidy_expiration'); ?>
			</div>
			*/ ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'is_fee_start_activate'); ?>
				<?php echo $form->dropDownList($model,'is_fee_start_activate',array(0 => 'No', 1=> 'Yes' ),array('class'=>'form-control form-control-inline')); ?>
				<?php echo $form->error($model,'is_fee_start_activate'); ?>
			</div>

			<div class="start-fee-amount-container">
				<div class="form-group">
					<?php echo $form->labelEx($model,'start_fee_amount'); ?>
					<?php echo $form->textField($model,'start_fee_amount', array('class' => '')); ?>
					<?php echo $form->error($model,'start_fee_amount'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->labelEx($model,'start_fee_day'); ?>
					<?php echo $form->dropDownList($model,'start_fee_day',Contract::listStartFreeDay(),array('empty'=> '--', 'class'=>'form-control form-control-inline')); ?>
					<?php echo $form->error($model,'start_fee_day'); ?>
				</div>

				<div class="form-group">
					<?php echo $form->labelEx($model,'start_fee_billing_cycle'); ?>
					<?php echo $form->dropDownList($model,'start_fee_billing_cycle',Contract::listStartFeeBillingCycle(),array('empty'=> '--', 'class'=>'form-control form-control-inline')); ?>
					<?php echo $form->error($model,'start_fee_billing_cycle'); ?>
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-success')); ?>
		</div>
	
	</div>
	

<?php $this->endWidget(); ?>

</div><!-- form -->