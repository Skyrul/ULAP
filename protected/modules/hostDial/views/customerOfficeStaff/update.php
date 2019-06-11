<?php 
	$this->pageTitle = 'Engagex - Update User | Profile';
	
	$baseUrl = Yii::app()->request->baseUrl;
	 
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css'); 
	
	$cs->registerCss(uniqid(), '
		.ui-sortable {
			border: 1px solid #eee;
			width: 100%;
			min-height: 40px;
			list-style-type: none;
			margin: 0;
			padding: 5px 0 0 0;
			margin-right: 10px;
		}

		.ui-sortable li {
			margin: 0 5px 5px 5px;
			padding: 5px;
			font-size: 1.2em;
			width: 95%;
		}
	');
	
	$cs->registerScriptFile( $baseUrl.'/js/jquery.bootstrap-duallistbox.min.js' ); 

	$cs->registerScript(uniqid(),'
	
		var account_id = "'.$model->account_id.'";
		
		$( "#sortableLanguagesAvailable, #sortableLanguagesAssigned" ).sortable({
		  connectWith: ".languageSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableLanguagesAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/updateAccountLanguage";
				type = "remove";
			}
			
			if(container_id == "sortableLanguagesAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/updateAccountLanguage";
				type = "add";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",	
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ },
			});
		}
		  
		}).disableSelection();
		
	',CClientScript::POS_END);
?>

<?php 
	if(!empty($model->customer) && !$model->customer->isNewRecord)
	{
		
		$this->widget("application.components.HostDialSideMenu",array(
			'active'=> 'calendar',
			'customer' => $model->customer,
		));

	}
?>

<div class="page-header">
	<h1 class="bigger">
		User Settings <small><i class="ace-icon fa fa-angle-double-right"></i> <?php echo $model->staff_name; ?></small>
	</h1>
</div>

<div class="tabbable">
	<ul id="myTab" class="nav nav-tabs">

		<li class="active">
			<a href="<?php echo $this->createUrl('update', array('id'=>$model->id, 'customer_id'=>$model->customer_id)); ?>">
				Profile
			</a>
		</li>
		
		<li class="">
			<a href="<?php echo $this->createUrl('timeKeeping', array('id'=>$model->id, 'customer_id'=>$model->customer_id)); ?>">
				Time Keeping
			</a>
		</li>
		
		<li class="">
			<a href="<?php echo $this->createUrl('performance', array('id'=>$model->id, 'customer_id'=>$model->customer_id)); ?>">
				Performance
			</a>
		</li>
		
		<?php if( $model->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER && (Yii::app()->user->account->id == $customer->account_id || ( Yii::app()->user->account->account_type_id == null || Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() ))  ): ?>
		
		<li class="">
			<a href="<?php echo $this->createUrl('permissions', array('id'=>$model->id, 'customer_id'=>$model->customer_id)); ?>">
				Permissions
			</a>
		</li>
		
		<?php endif; ?>
		
	</ul>
	<div class="tab-content">
		<?php $form=$this->beginWidget('CActiveForm', array(
			'enableAjaxValidation'=>false,
			'htmlOptions' => array(
				'class' => 'form-horizontal',
			),
		)); ?>
		
		<div class="row">
		
			<div class="col-sm-5">
				
				<div class="row">
					<div class="col-sm-5">
						<h2 class="lighter blue">Profile</h2>
					</div>
				</div>
			
				<?php 
					// $inputDisabled = !Yii::app()->user->account->checkPermission('customer_offices_staff_list_settings_all_fields','edit') ? true : false;
					$inputDisabled = false;
					$originalInputDisabledValue = $inputDisabled;
				?>

				<div class="form">

					<?php echo $form->hiddenField($model, 'customer_id'); ?>
					<?php echo $form->hiddenField($model, 'customer_office_id'); ?>
									
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> User Type </div>

								<div class="profile-info-value">
									<?php echo $form->dropDownList($model->account, 'account_type_id', array(15=>'Host Dialer', 16=>'Host Manager'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Username <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model->account, 'username', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Password <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->passwordField($model->account, 'password', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Name <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'staff_name', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Email Address</div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'email_address', array('class'=>'form-control', 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>	
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Phone</div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'phone', array('class'=>'form-control input-mask-phone', 'disabled'=>$inputDisabled)); ?>
									
									<div class="row center">
										<div class="checkbox">
											<label>
												<?php
													$dialAsInputDisabled = false;
													
													if( Yii::app()->user->account->getIsHostDialer() || Yii::app()->user->account->getIsHostManager() )
													{
														$dialAsInputDisabled = true;
													}
													
													echo $form->checkBox($model, 'use_phone_as_dial_as_option', array('class'=>'ace', 'value'=>1, 'uncheckValue'=>0, 'disabled'=>$dialAsInputDisabled)); 
												?>
												<span class="lbl">Use this number as a dialing as option</span>
											</label>
										</div>
									</div>
									
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Webphone Extension</div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'sip_username', array('class'=>'form-control input-mask-phone', 'disabled'=>Yii::app()->user->account->getIsHostManager() ? true : false)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Webphone Password</div>

								<div class="profile-info-value">
									<?php echo $form->passwordField($model, 'sip_password', array('class'=>'form-control input-mask-phone', 'disabled'=>Yii::app()->user->account->getIsHostManager() ? true : false)); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Use Webphone </div>

								<div class="profile-info-value">
									<?php 
										echo $form->dropDownList($model->account, 'use_webphone', array(1=>'Yes', 0=>'No'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>Yii::app()->user->account->getIsHostManager() ? true : false)); 
									?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Status </div>

								<div class="profile-info-value">
									<?php 
										if( $model->account_id == null )
										{
											$inputDisabled = true;
										}
										
										echo $form->dropDownList($model, 'status', array(1=>'Active', 2=>'Inactive'), array('class'=>'form-control', 'style'=>'width:auto;', 'disabled'=>$inputDisabled)); 
									?>
								</div>
							</div>
						</div>
						
				</div>
			</div>
			
			<div class="col-sm-5 col-sm-offset-1">
				<?php if( Yii::app()->user->account->checkPermission('employees_assigments_languages','visible') && Yii::app()->user->account->checkPermission('employees_assigments_languages','only_for_direct_reports', $account->id) ){ ?>
					
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">Languages</h2>
						</div>
					</div>
					
					<div class="row">
						<div class="widget-box">
							<div class="widget-body">
								<div class="widget-main"> 
								
									<div class="row">
										<div class="col-sm-6" style="min-height:200px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Available</label>
											</div>

											<ul id="sortableLanguagesAvailable" class="languageSortable">
												<?php 
													$availableLanguages = AccountLanguageAssigned::items();
													
													if( $accountLanguages )
													{
														foreach( $accountLanguages as $accountLanguage )
														{
															unset( $availableLanguages[$accountLanguage->language_id] );
														}
													}
		
													foreach( $availableLanguages as $availableLanguageId => $availableLanguageLabel )
													{
														echo '<li class="ui-state-default" data-id="'.$availableLanguageId.'" >'.$availableLanguageLabel.'</li>';
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-6">
											<div class="text-center">
												<label>Assigned</label>
											</div>
											
											<ul id="sortableLanguagesAssigned" class="languageSortable">
												<?php 
													if( $accountLanguages )
													{
														foreach( $accountLanguages as $accountLanguage )
														{
															echo '<li class="ui-state-default" data-id="'.$accountLanguage->language_id.'" >'.AccountLanguageAssigned::items($accountLanguage->language_id).'</li>';
														}
													}
												?>											
											</ul>
										</div>
									</div>
		
								
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		
		</div>

		<div class="space-12"></div>
		
		<div class="row">

			<div class="form-actions center">
				<button type="submit" class="btn btn-sm btn-primary">Save</button>
			</div>

		</div>
		
		<?php $this->endWidget(); ?>
	</div>
</div>