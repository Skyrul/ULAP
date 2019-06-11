<?php
$this->pageTitle=Yii::app()->name . ' - Change Password';
$this->breadcrumbs=array(
    'Change Password',
);
?>


<div class="space-6"></div>

<div class="col-sm-10 col-sm-offset-1">
	<div class="login-container">
		<div class="page-header center">
			<h2>
				<span class="red">Change Password</span>
			</h2>
		</div>
		
		<div class="space-6"></div>
		
		<div class="position-relative">
			<div class="forgot-box widget-box no-border visible" id="forgot-box">
				<div class="widget-body">
					<div class="widget-main">
						
						<div class="form">
							<h4>Hello! <?php echo $model->getFullName();?>, enter your new password below.</h4>
							<?php $form=$this->beginWidget('CActiveForm', array(
								'id'=>'Ganti-form',
							)); ?>
							
							<?php echo $form->errorSummary($model); ?>
							
							<div class="row">
									New Password : <input name="Ganti[password]" id="ContactForm_email" type="password"><br/>
									Confirm Password : <input name="Ganti[confirmPassword]" id="ContactForm_email" type="password">
									
									<input name="Ganti[tokenhid]" id="ContactForm_email" type="hidden" value="<?php echo $model->token?>">
							</div>
						 
							<div class="row buttons">
								<?php echo CHtml::submitButton('Submit', array('class'=>'btn btn-success')); ?>
							</div>
							 
							<?php $this->endWidget(); ?>
						 
						</div><!-- form -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

