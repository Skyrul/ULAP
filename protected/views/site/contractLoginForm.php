<div class="alert alert-danger">To add this service to your existing account please provide your login information.<br>Otherwise if you would like to create a new account for this service please enter a different email address.</div>

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'contract-login-form',
		'enableClientValidation'=>true,
		'clientOptions'=>array(
			'validateOnSubmit'=>true,
			'validateOnChange' => false,
			'beforeValidate' => 'js:function(form){
				return true;
			}',
			'afterValidate'=>'js:function(form, data, hasError){
				if(!hasError)
				{
					formData = [];
					var contractForm =  $( "#contract-form" ).serializeArray() ;
					var contractLoginForm =  $( "#contract-login-form" ).serializeArray() ;
					
					var emailAddress = {
						  name: "email_address",
						  value: $("#'.CHtml::activeId(new CustomerEnrollment,'email_address').'").val()
					};
					
					var uniqueId = {
						  name: "contract_id",
						  value: "'.$contract->id.'"
					};
					
					formData.push(emailAddress);
					formData.push(uniqueId);
					
					$.each(contractForm, function( index, value ) {
						formData.push(value);
					});

					$.each(contractLoginForm, function( index, value ) {
						formData.push(value);
					});
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/site/checkEmailAddressIfAlreadyHaveAccount?contractLoginForm=",
						type: "GET",	
						data: formData,
						dataType: "json",
						
						beforeSend: function(){
							$(".btn-primary").prop("disabled",true);
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
							$(".btn-primary").prop("disabled",false);
							alert(r.message);
							
							if(r.status == 100)
							{
								window.location = yii.urls.absoluteUrl + "/site/thankYou";
							}
						},
					});
				}
				// Always return false so that Yii will never do a traditional form submit
				return false;
			}', // Your JS function to submit form
		),
	)); ?>
	
		<label class="block clearfix">
			<span class="block input-icon input-icon-right">
				<?php //echo $form->labelEx($model,'username'); ?>
				<?php echo $form->textField($model,'username', array('class'=>'form-control', 'placeholder'=>'Username')); ?>
				<i class="ace-icon fa fa-user"></i>
			</span>
			
			<?php echo $form->error($model,'username'); ?>
		</label>

		<label class="block clearfix">
			<span class="block input-icon input-icon-right">
				<?php //echo $form->labelEx($model,'password'); ?>
				<?php echo $form->passwordField($model,'password', array('class'=>'form-control', 'placeholder'=>'Password')); ?>
															
				<i class="ace-icon fa fa-lock"></i>
			</span>
			
			<?php echo $form->error($model,'password'); ?>
		</label>

		<div class="space"></div>

		<div class="clearfix">
			<button class="width-35 pull-right btn btn-sm btn-primary">
				<i class="ace-icon fa fa-key"></i>
				<span class="bigger-110">Login</span>
			</button>
			
			<button class="width-35 pull-right btn btn-sm btn-primary" style="margin-right:5px;" id="changeEmailAddress">
				<span class="bigger-110">Change Email Address</span>
			</button>
			
			
		
		</div>
		
		<div class="space-4"></div>		
		<div class="space-4"></div>		
		<div class="space-4"></div>		
		<?php echo CHtml::link('<i class="ace-icon fa fa-arrow-left"></i> I forgot my password',array('site/forgot'),array('target'=>'_blank','class'=>'forgot-password-link width-35 pull-right btn btn-sm btn-primary')); ?>
			
		<div class="clearfix"></div>
			
	<?php $this->endWidget(); ?>
</div><!-- form -->