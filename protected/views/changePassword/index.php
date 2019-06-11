<?php
	$this->pageTitle=Yii::app()->name . ' - Change Password';
	$this->breadcrumbs=array(
		'Change Password',
	);
?>

<?php 
	if( in_array($authAccount->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
	{
		$this->widget("application.components.CustomerSideMenu",array(
			'active'=> Yii::app()->controller->id,
			'customer' => $authAccount->customer,
		));
	}
?>

<div class="space-6"></div>

<div class="col-sm-12">
	
	<div class="page-header">
		<h1><i class="ace-icon fa fa-key"></i> Change Password</h1>
	</div>
	
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

	<div class="col-sm-6 col-sm-offset-3">
		<div class="form">
			<?php $form=$this->beginWidget('CActiveForm', array(
				'id'=>'login-form',
				'enableClientValidation'=>true,
				'clientOptions'=>array(
					'validateOnSubmit'=>true,
				),
			)); ?>
			
				<p>Current Password</p>
				
				<label class="block clearfix">
					<span class="block input-icon input-icon-right">
						<input name="Account[password]" id="Account_password" type="password" class="form-control" value="<?php echo isset($_POST['Account']['password']) ? $_POST['Account']['password'] : ''; ?>">
					</span>
				</label>
				
				<p>New Password</p>
				
				<label class="block clearfix">
					<span class="block input-icon input-icon-right">
						<input name="Account[newpassword]" id="Account_newpassword" type="password" class="form-control" value="<?php echo isset($_POST['Account']['newpassword']) ? $_POST['Account']['newpassword'] : ''; ?>">
					</span>
				</label>
				
				<p>Confirm Password</p>
				
				<label class="block clearfix">
					<span class="block input-icon input-icon-right">
						<input name="Account[confirmpassword]" id="Account_confirmpassword" type="password" class="form-control" value="<?php echo isset($_POST['Account']['confirmpassword']) ? $_POST['Account']['confirmpassword'] : ''; ?>">
					</span>
				</label>
				
				<div class="clearfix">
					<button class="pull-right btn btn-sm btn-primary">
						<span class="bigger-110">Submit</span>
						<i class="ace-icon fa fa-check"></i>
					</button>
				</div>

				<div class="space-4"></div>
				
			<?php $this->endWidget(); ?>
		</div><!-- form -->
	</div>

</div>

