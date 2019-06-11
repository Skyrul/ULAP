<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'clone-skill-disposition-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// See class documentation of CActiveForm for details on this,
	// you need to use the performAjaxValidation()-method described there.
	'enableAjaxValidation'=>true,
	'enableClientValidation' => true,
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
							window.location.reload();
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

	<div class="row">
		<?php //echo $form->errorSummary($model); ?>
		
		<div class="col-md-12">
			<?php echo CHtml::label('Clone Disposition from Skill', null); ?>
			<?php echo $form->dropDownList($skill, '_clone_skill_id', CHtml::listData(Skill::model()->findAll(), 'id', 'skill_name'), array('empty'=>'-Select Skill-')); ?>
			<?php echo $form->error($skill,'_clone_skill_id'); ?>
		</div>
	
		
		<div class="col-md-12 buttons">
			<?php echo CHtml::submitButton('Submit',array('class'=>'btn btn-success')); ?>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->