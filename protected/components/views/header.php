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
				if( !Yii::app()->user->isGuest && Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
				{
					echo '<a href="'.Yii::app()->createUrl('agent/default/index').'" class="navbar-brand">'; 
						echo '<small>'; 
							echo CHtml::image($baseUrl.'/images/hostdial.png', 'HostDial',array('style'=>'width:100px;height:23px;'));
						echo '</small>';
					echo '</a>';
				}
				elseif( !Yii::app()->user->isGuest && Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
				{
					echo '<a href="'.Yii::app()->createUrl('customer/data/index').'" class="navbar-brand">'; 
						echo '<small>'; 
							echo CHtml::image($baseUrl.'/images/hostmanager.png', 'HostDial',array('style'=>'width:158px;height:23px;'));
						echo '</small>';
					echo '</a>';
				}
				else
				{
					echo '<a href="#" class="navbar-brand">'; 
						echo '<small>'; 
							echo CHtml::image($baseUrl.'/images/engagex-logo.png', 'Engagex',array('style'=>'width:100px;'));
						echo '</small>';
					echo '</a>';
				}
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
					
					<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_AGENT, Account::TYPE_COMPANY, Account::TYPE_GRATON_AGENT, Account::TYPE_HOSTDIAL_AGENT)) ){ ?>

						<?php if(UserAccess::hasRule('admin','Company','index')){ ?>
						
							<?php if( Yii::app()->user->account->checkPermission('structure_main_tab','visible') ){ ?>
							
								<li class="<?php echo ($module == 'admin') ? 'active' : 'transparent'; ?>">
									<a href="<?php echo Yii::app()->createUrl('admin'); ?>">
										<i class="menu-icon fa fa-building"></i>
										<span class="menu-text"> Structure</span>
									</a>
								</li>
								
							<?php } ?>
							
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('customers_main_tab','visible') && !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_GAMING_PROJECT_MANAGER)) ){ ?>
						
							<li class="<?php echo ($module == 'customer') ? 'active' : 'transparent'; ?>">
								<a href="<?php echo Yii::app()->createUrl('customer'); ?>">
									<i class="menu-icon fa fa-user"></i>
									<span class="menu-text"> 
										<?php 
											if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
											{
												echo 'Hosts';
											}
											else
											{
												echo 'Customers';
											}
										?>
									</span>
								</a>
							</li>
							
						<?php } ?>

						
						<?php if(UserAccess::hasRule('hr','Default','index')){ ?>
						
							<?php if( Yii::app()->user->account->checkPermission('employees_main_tab','visible') ){ ?>
							
								<li class="<?php echo ($module == 'hr') ? 'active' : 'transparent'; ?>">
									<a href="<?php echo Yii::app()->createUrl('hr/default/index'); ?>">
										<i class="menu-icon fa fa-group"></i>
										<span class="menu-text"> 
											<?php 
												if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
												{
													echo 'Users';
												}
												else
												{
													echo 'Employees';
												}
											?>											 
										</span>
									</a>
								</li>
								
							<?php } ?>
						<?php } ?>
					
						<?php if( Yii::app()->user->account->checkPermission('reports_main_tab','visible') ){ ?>
						
							<li class="<?php echo ($module != 'customer' && $controller == 'reports') ? 'active' : 'transparent'; ?>">
								<a href="<?php echo Yii::app()->createUrl('reports'); ?>">
									<i class="menu-icon fa fa-file-text"></i>
									<span class="menu-text"> Reports </span>
								</a>
							</li>
							
						<?php } ?>							
						
						<?php if( Yii::app()->user->account->checkPermission('accounting_main_tab','visible') ){ ?>
						
							<li class="<?php echo ($controller == 'accounting') ? 'active' : 'transparent'; ?>">
								<a href="<?php echo Yii::app()->createUrl('accounting/accounting/index'); ?>">
									<i class="menu-icon fa fa-book"></i>
									<span class="menu-text"> Accounting </span>
								</a>
							</li>
						<?php } ?>
						

					<?php } ?>
					
					<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_HOSTDIAL_AGENT)) ): ?>
							
						<?php if( Yii::app()->user->account->checkPermission('news_main_tab','visible') ){ ?>
						
							<li id="nav_news_main" class="<?php echo ($controller == 'news' && $action == 'main') ? 'active' : 'transparent'; ?>"> 
								<a href="<?php echo Yii::app()->createUrl('news/main'); ?>">
									<i class="menu-icon fa fa-newspaper-o"></i>
									<span class="menu-text"> News </span>
								</a>

								
							</li>
							
						<?php } ?>
						
					<?php endif; ?>
					
					<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_HOSTDIAL_AGENT)) ): ?>
							
						<?php if( Yii::app()->user->account->checkPermission('training_library_main_tab','visible') ){ ?>
						
							<li class="<?php echo ($module != 'hr' && $controller == 'trainingLibrary') ? 'active' : 'transparent'; ?>"> 
								<a href="<?php echo Yii::app()->createUrl('/trainingLibrary'); ?>">
									<i class="menu-icon fa fa-folder-open-o"></i>
									<span class="menu-text"> Training Library </span>
								</a>
							</li>
							
						<?php } ?>
						
					<?php endif; ?>
				</ul>
			</div>

			<div class="navbar-buttons navbar-header pull-right" role="navigation">
				<ul class="nav ace-nav">
					<!-- #section:basics/navbar.user_menu -->
					
					<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_GRATON_AGENT, Account::TYPE_HOSTDIAL_AGENT)) ): ?>
						<?php if( Yii::app()->user->account->checkPermission('dual_access_dialer_crm','visible') ){ ?> 
						
							<li>
								<?php echo CHtml::link('<i class="fa fa-phone"></i> Dialer', array('/agent'), array('style'=>'background-color:#1F4A74;')); ?>									
							</li>
							
							<li>
								<?php echo CHtml::link('<i class="fa fa-cogs"></i> CRM', array('/customer/data/index'), array('style'=>'background-color:#F78D22;')); ?>									
							</li>
						
						<?php } ?>
					<?php endif; ?>
					
					<li class="light-blue">
						<a data-toggle="dropdown" href="#" class="dropdown-toggle">
							<i class="fa fa-user"></i>
							
							<?php echo Yii::app()->user->name; ?>
							
							<span class="account-state-container">
								<?php echo $loginState; ?>
							</span>
						</a>

						<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
						
							<?php if($loginState != ''): ?>
							
								<li class="dropdown-header text-center">Login State</li>
								
								<li role="separator" class="divider"></li>
								 
								<?php if( $currentLoginState->type == 1 ): ?>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="1">
										<?php 
											if( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
											{
												echo 'Resume ';
											}
											else
											{
												echo 'Available ';
											}
										?>
										<?php echo $currentLoginState->type == 1 ? '<i class="fa fa-check"></i>' : ''; ?>
									</a>
								</li>
								
								<?php endif; ?>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="2">
										<?php 
											if( Yii::app()->user->account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
											{
												echo 'Pause ';
											}
											else
											{
												echo 'Not Available ';
											}
										?>
										<?php echo $currentLoginState->type == 2 ? '<i class="fa fa-check"></i>' : ''; ?>
									</a>
								</li>
								
								<?php if( isset(Yii::app()->user->account->accountUser) && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_AGENT, Account::TYPE_TEAM_LEAD, Account::TYPE_GRATON_AGENT)) ): ?>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="3">10 Minute Break <?php echo $currentLoginState->type == 3 ? '<i class="fa fa-check"></i>' : ''; ?></a>
								</li>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="4">Lunch <?php echo $currentLoginState->type == 4 ? '<i class="fa fa-check"></i>' : ''; ?></a>
								</li>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="5">Training <?php echo $currentLoginState->type == 5 ? '<i class="fa fa-check"></i>' : ''; ?></a>
								</li>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="6">1x1 <?php echo $currentLoginState->type == 6 ? '<i class="fa fa-check"></i>' : ''; ?></a>
								</li>
								
								<li>
									<a href="javascript:void(0);" class="update-account-state" type="7">Meeting <?php echo $currentLoginState->type == 7 ? '<i class="fa fa-check"></i>' : ''; ?></a>
								</li>
								
								<?php endif; ?>
								
								<li role="separator" class="divider"></li>
							
							<?php endif;  ?>
							
							<li>
								<?php
									if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
									{
										echo CHtml::link('<i class="ace-icon fa fa-key"></i>Change Password',array('/changePassword/index', 'customer_id'=>Yii::app()->user->account->customer->id)); 
									}
									else
									{
										echo CHtml::link('<i class="ace-icon fa fa-key"></i>Change Password',array('/changePassword')); 
									}
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
			
			<?php if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) ): ?>
				<div class="navbar-header pull-right" style="color: #ffffff; font-size:15px; line-height: 45px; text-align: right; margin-right: 5%;">
					Have questions? Contact Customer Service at &nbsp;&nbsp;<i class="fa fa-phone"></i> <a style="color:#ffffff; text-decoration:none;" href="tel:8005158734">(800) 515-8734</a> | 
					<i class="fa fa-envelope-o"></i> <a style="color:#ffffff; text-decoration:none;" href="mailto:customerservice@engagex.com">customerservice@engagex.com</a>
				</div>
			<?php endif; ?>
		
		<?php endif; ?>
		
	</div><!-- /.navbar-container -->
</div>

<?php else: ?>

<div class="space-12"></div>
<div class="space-12"></div>

<?php endif; ?>