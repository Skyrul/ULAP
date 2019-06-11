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
	'id'=>'tierSubsidy-form',
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
							
							tierSubsidyList();
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
	
	<div class="row">
		<?php echo $form->labelEx($model,'tier_id'); ?>
		<?php echo $form->dropDownList($model,'tier_id',CHtml::listData(Tier::model()->byCompanyId($company->id)->findAll(),'id','tier_name'), array('empty'=> '-Select Tier-', 'disabled'=> true )); ?>
		<?php echo $form->hiddenField($model,'tier_id'); ?>
		<?php echo $form->error($model,'tier_id'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'subsidy_name'); ?>
		<?php echo $form->textField($model,'subsidy_name'); ?>
		<?php echo $form->error($model,'subsidy_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'skill_id'); ?>
		<?php echo $form->dropDownList($model,'skill_id', CHtml::listData(Skill::model()->byCompanyId($company->id)->findAll(),'id', 'skill_name'), array('empty' => '-Select Skill-')); ?>
		<?php echo $form->error($model,'skill_id'); ?>
	</div>
	
	<div class="row">
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
	
	<div class="row">
		<?php echo $form->labelEx($model,'start_date'); ?>
		<?php echo $form->textField($model,'start_date',array('class'=>'date-picker')); ?>
		<?php echo $form->error($model,'start_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'end_date'); ?>
		<?php echo $form->textField($model,'end_date',array('class'=>'date-picker')); ?>
		<?php echo $form->error($model,'end_date'); ?>
	</div>

	<div class="row">
		<h2>Tier Subsidy Levels <?php echo CHtml::button('Add Level',array('class'=>'btn-minier btn-info btn-add-tier-subsidy-level')); ?> </h2>

		<?php foreach($model->tierSubsidyLevels as $tierSubsidyLevel){
				$this->renderPartial('_subsidyLevels',array(
					'name' => $tierSubsidyLevel->id,
					'tierSubsidyLevel' => $tierSubsidyLevel,
				));
		}
		?>
		
		<div class="tierSubsidyLevel-container"></div>
	</div>
	
	<div class="row buttons">
		<?php echo CHtml::submitButton('Submit',array('class'=>'btn btn-success')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->