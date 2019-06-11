<?php
$this->pageTitle=Yii::app()->name . ' - Forgot Password';
$this->breadcrumbs=array(
    'Forgot Password',
);
?>

<div class="space-6"></div>

<div class="col-sm-10 col-sm-offset-1">
	<div class="login-container">
	
		<?php /*if(Yii::app()->user->hasFlash('forgot')): ?>

			<div class="space-6"></div>
			 
			<div class="alert alert-success center">
				<?php echo Yii::app()->user->getFlash('forgot'); ?>
			</div>

		<?php endif; */ ?>
		
		
		<div class="page-header center">
			<h2>
				<span class="red">Forgot Password</span>
			</h2>
		</div>
		
		<div class="space-6"></div>
		
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

		<div class="position-relative">
			<div class="forgot-box widget-box no-border visible" id="forgot-box">
				<div class="widget-body">
					<div class="widget-main">
						<h4 class="header red lighter bigger">
							<i class="ace-icon fa fa-key"></i>
							Retrieve Password 											
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
							
								<p>Enter your email and to receive instructions</p>
								
								<label class="block clearfix">
									<span class="block input-icon input-icon-right">
										<input name="Lupa[email]" id="ContactForm_email" type="email" class="form-control" placeholder="Email">
										
										<i class="ace-icon fa fa-envelope"></i>
									</span>
								</label>
								
								<div class="clearfix">
									<button class="width-35 pull-right btn btn-sm btn-danger">
										<i class="ace-icon fa fa-lightbulb-o"></i>
										<span class="bigger-110">Send Me!</span>
									</button>
								</div>

								<div class="space-4"></div>
								
							<?php $this->endWidget(); ?>
						</div><!-- form -->
						
					</div><!-- /.widget-main -->

					<div class="toolbar clearfix center">
						<div>
							<?php echo CHtml::link('Back to login <i class="ace-icon fa fa-arrow-right"></i>', array('site/login'), array('class'=>'back-to-login-link')); ?>
						</div>
					</div>
				</div><!-- /.widget-body -->
			</div><!-- /.login-box -->
		</div>
	</div>
</div>

