<h1>View - Schedule Change Request</h1>

<?php Yii::app()->clientScript->registerScript('formJs','
	//datepicker plugin
	//link
	$(".date-picker").datepicker({	 
		autoclose: true,
		todayHighlight: true
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
		'id'=>'accout_pto_form-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
	)); ?>
	
	<?php echo $form->errorSummary($model); ?>
	<p class="note">Fields with <span class="required">*</span> are required.</p>
	
	<div class="col-md-8 col-lg-6">
		<div class="widget-box">
			<div class="widget-header">
				<h4 class="widget-title">Request Time Off</h4>
			</div>
			
			<div class="widget-body">
				<div class="widget-main">
					<!-- FORM -->
					
					<div>
						<?php echo $form->labelEx($model,'account_id'); ?>
						<?php echo $model->account->fullNameReverse; ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'date_of_request'); ?>
						<?php echo $model->requestDateWithTime(); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'is_full_shift'); ?>
						<?php echo AccountPtoForm::YesNoName($model->is_full_shift); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'computed_off_hour'); ?>
						<?php echo $model->computed_off_hour; ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'is_make_time_up'); ?>

						<?php echo AccountPtoForm::YesNoName($data->is_make_time_up); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<label for="form-field-mask-4">
							 If Yes, what date and time will you make it up?
						</label>
						
						<?php echo $model->makeItUpWithTime(); ?> 
					</div>		 
							 
					
					
					<div class="space space-8"></div>
					
					<div>
						<label for="form-field-8">Reason for Request</label>
						<?php echo $model->reason_for_request; ?>
					</div>
						
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'is_pto'); ?>
						<?php echo AccountPtoForm::YesNoName($data->is_pto); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'status'); ?>
						<?php echo $model->statusName(); ?>
					</div>
					
					
					<div class="clearfix form-actions">
						<div class="col-sm-12">
						
							<?php 
								if($model->status == 2)
								{
									echo CHtml::link('<i class="fa fa-check"></i> Approved',array('approve','id'=>$model->id),array('class'=>'btn btn-success','confirm'=>'Click OK to continue'));
									echo '&nbsp;';
									echo CHtml::link('<i class="fa fa-times"></i> Deny',array('deny','id'=>$model->id),array('class'=>'btn btn-danger','confirm'=>'Click OK to continue')); 
								}
								
								if($model->status != 2)
								{
									echo CHtml::link('Set back to For Approval',array('pending','id'=>$model->id),array('class'=>'btn btn-warning','confirm'=>'Click OK to continue')); 
								}
							?>
						</div>
					</div>
	
					<!-- END OF FORM -->
				</div>
			</div>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->