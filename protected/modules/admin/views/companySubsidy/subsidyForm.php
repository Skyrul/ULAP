<?php 
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

Yii::app()->clientScript->registerScript(uniqid(), "
 $.mask.definitions['~']='[+-]';
 $('.input-mask-phone').mask('(999) 999-9999');
 
 $('.date-picker').datepicker({
       autoclose: true,
       todayHighlight: true
      });
	  
", CClientScript::POS_END);



?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'companySubsidy-form',
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
							
							subsidyList();
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

	<p class="note">Fields with <span class="required">*</span> are required.</p>
	
	<div class="form-group">
		<?php echo $form->hiddenField($model, 'company_id'); ?>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'subsidy_name'); ?>
		<?php echo $form->textField($model,'subsidy_name'); ?>
		<?php echo $form->error($model,'subsidy_name'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'skill_id'); ?>
		<?php echo $form->dropDownList($model,'skill_id', CHtml::listData(Skill::model()->byCompanyId($model->company_id)->findAll(),'id', 'skill_name'), array('empty' => '-Select Skill-')); ?>
		<?php echo $form->error($model,'skill_id'); ?>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'contract_id'); ?>
		
		<?php 
			$htmlOptions['empty'] = '-Select Contract-';
			
			if( $model->contract_id !== null )
			{
				$htmlOptions['options'] = array( $model->contract_id => array('selected'=>true));
			}
			
			echo $form->dropDownList($model,'contract_id', $contractOptions, $htmlOptions);
		?>
		
		<?php echo $form->error($model,'contract_id'); ?>
	</div>
	
	<div class="form-group">
		<?php echo $form->labelEx($model,'start_date'); ?>
		<?php echo $form->textField($model,'start_date',array('class'=>'date-picker')); ?>
		<?php echo $form->error($model,'start_date'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'end_date'); ?>
		<?php echo $form->textField($model,'end_date',array('class'=>'date-picker')); ?>
		<?php echo $form->error($model,'end_date'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'exclude_from_company_file_update'); ?>
		<?php echo $form->dropDownList($model,'exclude_from_company_file_update',array(1=>'YES', 0=>'NO'), array('style'=>'width:auto;')); ?>
		<span class="lbl"> </span>
	</div>
	
	<div class="form-group">
		<h2>
			Subsidy Levels 
			<button type="button" class="btn btn-minier btn-info btn-add-subsidy-level"><i class="fa fa-plus"></i> Add Level</button>
		</h2>

		<?php foreach($model->companySubsidyLevels as $companySubsidyLevel){
				$this->renderPartial('_subsidyLevels',array(
					'name' => $companySubsidyLevel->id,
					'companySubsidyLevel' => $companySubsidyLevel,
				));
		}
		?>
		
		<div class="subsidyLevel-container"></div>
	</div>

	<div class="form-group buttons center">
		<button type="submit" class="btn btn-sm btn-success">Save <i class="fa fa-check"></i></button>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
