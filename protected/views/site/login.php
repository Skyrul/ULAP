<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<div class="space-6"></div>

<div class="col-sm-10 col-sm-offset-1">
	<div class="login-container">
	
		<div class="page-header center">
			<!--<h2>
				<span class="blue">Login</span>
			</h2>-->
			<?php echo CHtml::image(Yii::app()->request->baseUrl.'/images/engagex.png', 'Engagex',array('style'=>'width:300px;')); ?> 
		</div>
		
		<div class="space-6"></div>
		
		<div class="position-relative">
			<div class="login-box visible widget-box no-border" id="login-box">
				<div class="widget-body">
					<div class="widget-main">

						<h4 class="header blue lighter bigger">
							Please Enter Your Information
						</h4>

						<div class="space-6"></div>

						<div class="form">
							<?php $form=$this->beginWidget('CActiveForm', array(
								'id'=>'login-form',
								'enableClientValidation'=>true,
								'clientOptions'=>array(
									'validateOnSubmit'=>true,
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
									<?php if(!$displayLockMessage){ ?>
									<span class="block input-icon input-icon-right">
										<?php //echo $form->labelEx($model,'password'); ?>
										<?php echo $form->passwordField($model,'password', array('class'=>'form-control', 'placeholder'=>'Password')); ?>
																					
										<i class="ace-icon fa fa-lock"></i>
									</span>
									<?php echo $form->error($model,'password'); ?>
									<?php }else{ ?>
									<div class="errorMessage">
											<?php echo $lockMessage; ?>
									</div>
									<?php } ?>
								</label>

								<div class="space"></div>

								<?php if(!$displayLockMessage){ ?>
								<div class="clearfix">
									<button class="width-35 pull-right btn btn-sm btn-primary">
										<i class="ace-icon fa fa-key"></i>
										<span class="bigger-110">Login</span>
									</button>
								</div>
								<?php } ?>
								<div class="space-4"></div>
								
							<?php $this->endWidget(); ?>
						</div><!-- form -->
						
					</div><!-- /.widget-main -->

					<div class="toolbar clearfix">
						<div style="float:none; text-align:center; width:100%;">
							<?php echo CHtml::link('<i class="ace-icon fa fa-arrow-left"></i> I forgot my password', array('site/forgot'), array('class'=>'forgot-password-link')); ?>
						</div>
					</div>
				</div><!-- /.widget-body -->
			</div><!-- /.login-box -->
		</div>
	</div>
</div>
