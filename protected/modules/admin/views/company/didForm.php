<?php 
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

Yii::app()->clientScript->registerScript(uniqid(), "
 $.mask.definitions['~']='[+-]';
 $('.input-mask-phone').mask('(999) 999-9999');
", CClientScript::POS_END);

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'companyDid-form',
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
							
							didList();
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

	<?php //echo $form->errorSummary($model); ?>
	<?php /*
	<div class="row">
		<?php echo $form->labelEx($model, 'company_id'); ?>
		<?php echo $form->dropDownList($model, 'company_id', CHtml::listData(Company::model()->findAll(),'id','company_name'), array('empty'=>'-Select Company-')); ?>
		<?php echo $form->error($model,'company_id'); ?>
	</div>
	*/
	?>
	
	<div class="row">
		<?php echo $form->hiddenField($model, 'company_id'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'area_code'); ?>
		<?php echo $form->textField($model,'area_code'); ?>
		<?php echo $form->error($model,'area_code'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'prefix'); ?>
		<?php echo $form->textField($model,'prefix'); ?>
		<?php echo $form->error($model,'prefix'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'number'); ?>
		<?php echo $form->textField($model,'number',array('class'=> 'input-mask-phone')); ?>
		<?php echo $form->error($model,'number'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'caller_option'); ?>
		<?php echo $form->textField($model,'caller_option'); ?>
		<?php echo $form->error($model,'caller_option'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Submit',array('class'=>'btn btn-success')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->