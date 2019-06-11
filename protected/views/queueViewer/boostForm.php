<?php
	Yii::app()->clientScript->registerScript('updateCancelBtnJs','
		$("form input[type=submit]").click(function() {
			$("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
			$(this).attr("clicked", "true");
		});
	
	',CClientScript::POS_END);
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'boost-form',
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
				var submitBtnValue = $("input[type=submit][clicked=true]").val();
				
				console.log(submitBtnValue);
				if(submitBtnValue == $("#cancelBtn").val())
				{
					$("#cancelBtnField").val("true");
				}
				else
					$("#cancelBtnField").val("false");
				
				
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
	
	<div class="col-md-12">
		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<?php //echo $form->errorSummary($model); ?>
		
		<div class="row">
			<?php echo $form->hiddenField($model, 'customer_id'); ?>
			<?php echo $form->hiddenField($model, 'skill_id'); ?>
			<?php echo $form->hiddenField($model, 'type'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>
			<?php echo CHtml::hiddenField('cancelBtnField',false); ?>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'customer_id'); ?>
			<?php echo CHtml::textField('customerName',$customer->getFullName(),array('disabled'=>true, 'class'=>'form-control')); ?>
			<?php echo $form->error($model,'customer_id'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'skill_id'); ?>
			<?php echo CHtml::textField('skillName',$skill->skill_name,array('disabled'=>true, 'class'=>'form-control')); ?>
			<?php echo $form->error($model,'skill_id'); ?>
		</div>
		
		<?php $isDisabled = $model->isNewRecord ? false : true;	?>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'type'); ?>
			<?php echo $form->dropDownList($model, 'type', array(1 => 'Now', 2 => 'Schedule'), array('disabled' => $isDisabled)); ?>
			<?php echo $form->error($model,'type'); ?>
		</div>
		
		<div class="form-group beginning-date-container hidden">
			<?php echo $form->labelEx($model,'beginning_date'); ?>
			<?php echo $form->textField($model,'beginning_date', array('disabled' => $isDisabled)); ?>
			<?php echo $form->error($model,'beginning_date'); ?>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'goal_value'); ?> 
			
			<?php 
				$goalOptions = array(1=>'1');
				
				$queueViewer = CustomerQueueViewer::model()->find(array(
					'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
					'params' => array(
						':customer_id' => $model->customer_id,
						':skill_id' => $model->skill_id,
					),
				));
			?>
		
			<?php
				
				if( $queueViewer )
				{
					for($i=1; $i <= $queueViewer->total_potential_dials; $i++ )
					{
						$goalOptions[$i] = $i; 
					}
				}
				
				echo $form->dropDownList($model,'goal_value', $goalOptions, array('disabled' => $isDisabled)); 
				
				if( $queueViewer  && $queueViewer->fulfillment_type == 'Goal' )
				{
					echo '<code style="font-size:100%; margin-left:15px;">' . $model->goal_value . ' Remaining</code>';
				}
			?>
			<?php echo $form->error($model,'goal_value'); ?>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'dial_value'); ?>
			
			<?php echo $form->dropDownList($model,'dial_value', $goalOptions, array('disabled' => $isDisabled)); ?>
			
			<?php 
				echo $form->error($model,'dial_value'); 
				
				if( $queueViewer  && $queueViewer->fulfillment_type == 'Lead' )
				{
					echo '<code style="font-size:100%;margin-left:15px;">' . $model->goal_value . ' Remaining</code>';
				}
			?>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'magnitude_value'); ?>
			<?php echo $form->dropDownList($model,'magnitude_value', array(1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5'), array('disabled' => $isDisabled)); ?>
			<?php echo $form->error($model,'magnitude_value'); ?>
		</div>


		<div class="form-group">
			<?php echo $model->isNewRecord ? CHtml::submitButton($model->isNewRecord ? 'Submit' : 'Update',array('class'=>'btn btn-success')) : ''; ?>
			
			<?php echo $model->isNewRecord ? '' : CHtml::submitButton('Cancel Boost',array('class'=>'btn btn-error', 'name'=>'cancelBtn','id'=>'cancelBtn')); ?>
		</div>
	</div>
</div>
<?php $this->endWidget(); ?>

</div><!-- form -->