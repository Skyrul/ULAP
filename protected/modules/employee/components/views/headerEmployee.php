<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$controller = Yii::app()->controller->id;
	$action = Yii::app()->controller->action->id;
	$module = !empty(Yii::app()->controller->module->id) ? Yii::app()->controller->module->id : null;
?>

<?php if( !Yii::app()->user->isGuest ): ?>

<div id="navbar" class="navbar navbar-default navbar-collapse h-navbar">
	<script type="text/javascript">
		try{ace.settings.check('navbar', 'fixed')}catch(e){}
	</script>

	<div class="navbar-container" id="navbar-container">
		<div class="navbar-header pull-left">
			<!-- #section:basics/navbar.layout.brand -->

			<?php 
				// if( !Yii::app()->user->isGuest && Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
				// {
					// echo '<a href="'.Yii::app()->createUrl('agent/default/index').'" class="navbar-brand">'; 
						// echo '<small>'; 
							// echo CHtml::image($baseUrl.'/images/hostdial.png', 'HostDial',array('style'=>'width:100px;height:23px;'));
						// echo '</small>';
					// echo '</a>';
				// }
				// elseif( !Yii::app()->user->isGuest && Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
				// {
					// echo '<a href="'.Yii::app()->createUrl('customer/data/index').'" class="navbar-brand">'; 
						// echo '<small>'; 
							// echo CHtml::image($baseUrl.'/images/hostmanager.png', 'HostDial',array('style'=>'width:158px;height:23px;'));
						// echo '</small>';
					// echo '</a>';
				// }
				// else
				// {
					echo '<a href="#" class="navbar-brand">'; 
						echo '<small>'; 
							echo CHtml::image($baseUrl.'/images/engagex-logo.png', 'Engagex',array('style'=>'width:100px;'));
						echo '</small>';
					echo '</a>';
				// }
			?>

			<button class="pull-right navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".sidebar">
				<span class="sr-only">Toggle sidebar</span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>
			</button>

			<!-- /section:basics/navbar.toggle -->
		</div>

		<?php if( !Yii::app()->user->isGuest && !isset($_GET['loginAuth']) ): ?>
			
			<!-- #section:basics/navbar.dropdown -->
			<div class="navbar-buttons navbar-header pull-left  collapse navbar-collapse" role="navigation">
				<ul class="nav ace-nav">
							
					<li class="<?php echo ($controller == 'timeKeeping') ? 'active' : 'transparent'; ?>">
						<a href="<?php echo Yii::app()->createUrl('/employee/default/index'); ?>">
							<i class="menu-icon fa fa-building"></i>
							<span class="menu-text"> Time Keeping</span>
						</a>
					</li>
					
				</ul>
			</div>

			<div class="navbar-buttons navbar-header pull-right" role="navigation">
				<ul class="nav ace-nav">
					<!-- #section:basics/navbar.user_menu -->
				
					<li class="light-blue">
						<a data-toggle="dropdown" href="#" class="dropdown-toggle">
							<i class="fa fa-user"></i>
							
							<?php echo Yii::app()->user->name; ?>
							
							<span class="account-state-container">
								<?php echo $loginState; ?>
							</span>
						</a>

						<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
						
							<li>
								<?php
									echo CHtml::link('<i class="ace-icon fa fa-key"></i>Change Password',array('/changePassword')); 
								?>								
							</li>
							
							<li role="separator" class="divider"></li>
						
							<li>
								<?php echo CHtml::link('<i class="ace-icon fa fa-power-off"></i>Logout',array('/site/logout')); ?>								
							</li>
						</ul>
					</li>

					<!-- /section:basics/navbar.user_menu -->
				</ul>
			</div>
		
		<?php endif; ?>
		
	</div><!-- /.navbar-container -->
</div>

<?php else: ?>

<div class="space-12"></div>
<div class="space-12"></div>

<?php endif; ?>