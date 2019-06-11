<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;

	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
?>

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
						<?php echo $form->labelEx($model,'date_of_request_start'); ?>
						<?php /*<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="input-group">
								
									<input class="form-control date-picker" type="text" data-date-format="dd-mm-yyyy">
									<span class="input-group-addon">
										<i class="fa fa-calendar bigger-110"></i>
									</span>
								</div>
							</div>
						</div>
						*/ ?>
						<?php echo $form->textField($model,'date_of_request_start',array('class'=>'date-picker','placeholder'=>'mm/dd/yyyy')); ?>
						<?php //echo $form->error($model,'date_of_request'); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'date_of_request_end'); ?>
						<?php /*<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="input-group">
								
									<input class="form-control date-picker" type="text" data-date-format="dd-mm-yyyy">
									<span class="input-group-addon">
										<i class="fa fa-calendar bigger-110"></i>
									</span>
								</div>
							</div>
						</div>
						*/ ?>
						<?php echo $form->textField($model,'date_of_request_end',array('class'=>'date-picker','placeholder'=>'mm/dd/yyyy')); ?>
						<?php //echo $form->error($model,'date_of_request'); ?>
					</div>
					
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'is_full_shift'); ?>

						<div>
							<?php echo $form->radioButtonList($model,'is_full_shift',array('1'=>'Yes','2'=>'No'),array('separator'=>' &nbsp; &nbsp;')); ?>
							<?php //echo $form->error($model,'is_full_shift'); ?>
						</div>

					</div>
					
					<div class="space space-8"></div>
					
					<div id="js-is_full_shift-container">
						<div>
							<label for="form-field-mask-4">
								If No, How many hours?
							</label>

							<div>
								<span><label>From: </label></span>
								<div class="hidden-sm hidden-md hidden-lg space space-8"></div>
								<?php echo $form->dropDownList($model,'off_hour_from',AccountPtoForm::hoursList(),array('empty'=>'-hh-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'off_min_from',array('00'=>'00', '30'=>'30'),array('empty'=>'-mm-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'off_md_from',array('am'=>'am', 'pm'=>'pm'),array('empty'=>'-md-','separator'=>' &nbsp; &nbsp;')); ?>
								<div class="hidden-md hidden-lg space space-8"></div>
								<span><label>To: </label></span>
								<div class="hidden-sm hidden-md hidden-lg space space-8"></div>
								<?php echo $form->dropDownList($model,'off_hour_to',AccountPtoForm::hoursList(),array('empty'=>'-hh-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'off_min_to',array('00'=>'00', '30'=>'30'),array('empty'=>'-mm-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'off_md_to',array('am'=>'am', 'pm'=>'pm'),array('empty'=>'-md-','separator'=>' &nbsp; &nbsp;')); ?>
								
							</div>
						</div>
						
						<div class="space space-8"></div>
					</div>
					
					<div>
						<?php echo $form->labelEx($model,'is_make_time_up'); ?>

						<div>
							<?php echo $form->radioButtonList($model,'is_make_time_up',array('1'=>'Yes','2'=>'No'),array('separator'=>' &nbsp; &nbsp;')); ?>
							<?php //echo $form->error($model,'is_make_time_up'); ?>
						</div>
					</div>
					
					<div class="space space-8"></div>
					
					<div id="js-is_make_time_up-container">
						<div>
							<label for="form-field-mask-4">
								 If Yes, what date and time will you make it up?
							</label>
							
							<div>
								<label>
									Date Request Start
								</label>
								<?php echo $form->textField($model,'date_of_make_time_up_start',array('class'=>'date-picker','placeholder'=>'mm/dd/yyyy')); ?>
								<?php /*<div class="row">
									<div class="col-xs-12 col-sm-6">
										<div class="input-group">
											<input class="form-control date-picker" type="text" data-date-format="dd-mm-yyyy">
											<span class="input-group-addon">
												<i class="fa fa-calendar bigger-110"></i>
											</span>
										</div>
									</div>
								</div> */ ?>
							</div>
								
							<div class="space space-8"></div>
							
							<div>
								<label>
									Date Request End
								</label>
								<?php echo $form->textField($model,'date_of_make_time_up_end',array('class'=>'date-picker','placeholder'=>'mm/dd/yyyy')); ?>
								<?php /*<div class="row">
									<div class="col-xs-12 col-sm-6">
										<div class="input-group">
											<input class="form-control date-picker" type="text" data-date-format="dd-mm-yyyy">
											<span class="input-group-addon">
												<i class="fa fa-calendar bigger-110"></i>
											</span>
										</div>
									</div>
								</div> */ ?>
							</div>
							
							<div class="space space-8"></div>
							
							<div>
								<span><label>From: </label></span>
								<div class="hidden-sm hidden-md hidden-lg space space-8"></div>
								<?php echo $form->dropDownList($model,'make_time_up_hour_from',AccountPtoForm::hoursList(),array('empty'=>'-hh-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'make_time_up_min_from',array('00'=>'00', '30'=>'30'),array('empty'=>'-mm-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'make_time_up_md_from',array('am'=>'am', 'pm'=>'pm'),array('empty'=>'-md-','separator'=>' &nbsp; &nbsp;')); ?>
								<div class="hidden-md hidden-lg space space-8"></div>
								<span><label>To: </label></span>
								<div class="hidden-sm hidden-md hidden-lg space space-8"></div>
								<?php echo $form->dropDownList($model,'make_time_up_hour_to',AccountPtoForm::hoursList(),array('empty'=>'-hh-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'make_time_up_min_to',array('00'=>'00', '30'=>'30'),array('empty'=>'-mm-','separator'=>' &nbsp; &nbsp;')); ?>
								<?php echo $form->dropDownList($model,'make_time_up_md_to',array('am'=>'am', 'pm'=>'pm'),array('empty'=>'-md-','separator'=>' &nbsp; &nbsp;')); ?>
								
							</div>
						</div>		 
					
					
					
						<div class="space space-8"></div>
					</div>	 
					<div>
						<label for="form-field-8">Reason for Request</label>

						<?php echo $form->textArea($model,'reason_for_request',array('class'=>'form-control', 'placeholder'=>'Reason for request...', 'style'=>'min-height:90px;')); ?>
					</div>
						
					<div class="space space-8"></div>
					
					<div>
						<?php echo $form->labelEx($model,'is_pto'); ?>

						<div>
							<?php echo $form->radioButtonList($model,'is_pto',array('1'=>'Yes','2'=>'No'),array('separator'=>' &nbsp; &nbsp;')); ?>
							<?php //echo $form->error($model,'is_pto'); ?>
						</div>
					</div>
					
					<div class="clearfix form-actions">
						<div class="col-sm-12">
						
						
						</div>
					</div>
					
					
					<div class="row buttons">
						<div class="col-sm-12">
							<?php echo CHtml::submitButton('Submit',array('class'=>'btn btn-info')); ?>
						</div>
					</div>
	
					<!-- END OF FORM -->
				</div>
			</div>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php Yii::app()->clientScript->registerScript('toggleJs','

	$("input[name=\"AccountPtoForm[is_full_shift]\"]").on("change",function(){
		
		
		var thisVal = $("input[name=\"AccountPtoForm[is_full_shift]\"]:checked").val();
		
		if(thisVal == 1)
		{
			$("#js-is_full_shift-container").hide();
		}
		else
		{
			$("#js-is_full_shift-container").show();
		}
	});
	
	$("input[name=\"AccountPtoForm[is_full_shift]\"]").trigger("change");
	
	$("input[name=\"AccountPtoForm[is_make_time_up]\"]").on("change",function(){
		
		
		var thisVal = $("input[name=\"AccountPtoForm[is_make_time_up]\"]:checked").val();
		
		if(thisVal == 2)
		{
			$("#js-is_make_time_up-container").hide();
		}
		else
		{
			$("#js-is_make_time_up-container").show();
		}
	});
	
	$("input[name=\"AccountPtoForm[is_make_time_up]\"]").trigger("change");
	
',CClientScript::POS_END); ?>