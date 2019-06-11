<?php 
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
	
	$cs->registerScript(uniqid(), '
		
		var account_id = '.$account->id.';
		
		function updateCustomerList()
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/hr/accountUser/updateCustomerList",
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
				},
				beforeSend: function(){
					$("#customersLabel").html("Customers <i class=\"fa fa-cog fa-spin\"></i>");
					$(".btn-move-all-customers-to-assigned").prop("disabled", true);
					$(".btn-move-all-customers-to-available").prop("disabled", true);
				},
				complete: function(){
					$("#customersLabel").html("Customers");
				},
				success: function(response){ 
					console.log(response);

					$("#sortableCustomersAvailable").html(response.html);
					$("#sortableCustomersAssigned").html(response.html2);
					
					$(".btn-move-all-customers-to-assigned").prop("disabled", false);
					$(".btn-move-all-customers-to-available").prop("disabled", false);
				},
			});
		}
		
		
	', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(),'
		
		$( "#sortableLanguagesAvailable, #sortableLanguagesAssigned" ).sortable({
		  connectWith: ".languageSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableLanguagesAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountLanguage";
				type = "remove";
			}
			
			if(container_id == "sortableLanguagesAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountLanguage";
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
		
		
		
		$( "#sortableSkillsAvailable, #sortableSkillsTrained, #sortableSkillsAssigned" ).sortable({
		  connectWith: ".skillsSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableSkillsAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkill";
				type = "remove";
			}
			
			if(container_id == "sortableSkillsTrained")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkill";
				type = "addTrained";
			}
			
			if(container_id == "sortableSkillsAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkill";
				type = "addAssigned";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ console.log(response) },
			});
		}
		  
		}).disableSelection();
		
		$( "#sortableSkillChildsAvailable, #sortableSkillChildsTrained, #sortableSkillChildsAssigned" ).sortable({
		  connectWith: ".skillsSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableSkillChildsAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkillChild";
				type = "remove";
			}
			
			if(container_id == "sortableSkillChildsTrained")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkillChild";
				type = "addTrained";
			}
			
			if(container_id == "sortableSkillChildsAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountSkillChild";
				type = "addAssigned";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ console.log(response) },
			});
		}
		  
		}).disableSelection();
		
		
		$( "#sortableCompaniesAvailable, #sortableCompaniesAssigned" ).sortable({
		  connectWith: ".companiesSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableCompaniesAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountCompany";
				type = "remove";
			}
			
			if(container_id == "sortableCompaniesAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountCompany";
				type = "addAssigned";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ updateCustomerList(); },
			});
		}
		  
		}).disableSelection();
		
		$( "#sortableCustomersAvailable, #sortableCustomersAssigned" ).sortable({
		  connectWith: ".customersSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableCustomersAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountCustomer";
				type = "remove";
			}
			
			if(container_id == "sortableCustomersAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hr/accountUser/updateAccountCustomer";
				type = "addAssigned";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ console.log(response) },
			});
		}
		  
		}).disableSelection();
		
		$(".btn-move-all-customers-to-assigned").on("click", function(){
			
			var this_button = $(this);
			var account_id = this_button.attr("account_id");
			
			if( confirm("Are you sure you want to move all customers to assigned?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/moveCustomers",
					type: "post",
					dataType: "json",
					data: { 
						"ajax": 1, 
						"type": "toAssigned", 
						"account_id": account_id,
					},
					beforeSend: function(){
						this_button.prop("disabled", true);
						this_button.html("<i class=\"fa fa-cog fa-spin\"></i>");
					},
					success: function(response){ 

						$("#sortableCustomersAvailable").html(response.html);
						$("#sortableCustomersAssigned").html(response.html2);
						
						this_button.prop("disabled", false);
						$(".btn-move-all-customers-to-available").prop("disabled", false);
						
						this_button.html("<i class=\"fa fa-arrow-right\"></i>");
						
						updateCustomerList();
					},
				});
			}
			
		});
		
		$(".btn-move-all-customers-to-available").on("click", function(){
			
			var this_button = $(this);
			var account_id = this_button.attr("account_id");
			
			if( confirm("Are you sure you want to move all customers to available?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/moveCustomers",
					type: "post",
					dataType: "json",
					data: { 
						"ajax": 1, 
						"type": "toAvailable", 
						"account_id": account_id,
					},
					beforeSend: function(){
						this_button.prop("disabled", true);
						this_button.html("<i class=\"fa fa-cog fa-spin\"></i>");
					},
					success: function(response){ 

						$("#sortableCustomersAvailable").html(response.html);
						$("#sortableCustomersAssigned").html(response.html2);
						
						this_button.prop("disabled", false);
						$(".btn-move-all-customers-to-available").prop("disabled", false);
						
						this_button.html("<i class=\"fa fa-arrow-left\"></i>");
						
						updateCustomerList();
					},
				});
			}
			
		});
		
		updateCustomerList();
		
	',CClientScript::POS_END);
?>

<div class="tabbable tabs-left">

	<ul class="nav nav-tabs">
	
		<?php 
			if( Yii::app()->user->account->checkPermission('employees_employee_profile_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_tab','only_for_direct_reports') )
			{
				echo '<li class="">';
					
					if( $account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
					{
						echo CHtml::link('Host Profile', array('accountUser/employeeDetails', 'id'=>$account->id));
					}
					else
					{	
						echo CHtml::link('Employee Profile', array('accountUser/employeeDetails', 'id'=>$account->id));
					}
				echo '</li>';
			}
		?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Employee File', array('employeeFile', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_tab','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Time Keeping', array('timeKeeping', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports', $account->id) ){ ?>
			<li class="active"><?php echo CHtml::link('Assignments', array('assignments', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_performance_tab','visible') && Yii::app()->user->account->checkPermission('employees_performance_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Performance', array('performance', 'id'=>$account->id)); ?></li>
		<?php }?>
		
	</ul>
	
	<div class="tab-content" style="overflow:hidden;">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="col-sm-12">
				<div class="col-sm-3">
					<div class="row">
						<div class="col-sm-4">
							<div class="profile-picture">
								<?php 
									if( $accountUser->getImage() )
									{
										echo CHtml::image($accountUser->getImage(), '', array('class'=>'img-responsive'));
									}
									else
									{
										echo '<div style="height:100px; border:1px dashed #ccc; text-align:center;">No Image Uploaded.</div>';
									}
								?>
							</div>
						</div>
						<div class="col-sm-8 text-center">
							<h3><?php echo $accountUser->getFullName(); ?></h3>
						</div>
					</div>
					
					<br />
					
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

				<div class="col-sm-9">
					<?php if( Yii::app()->user->account->checkPermission('employees_assigments_skills','visible') && Yii::app()->user->account->checkPermission('employees_assigments_skills','only_for_direct_reports', $account->id) ){ ?>
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">Skills</h2>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="widget-box" style="min-height:310px;">
							<div class="widget-body">
								<div class="widget-main"> 
									
									<div class="row">
										<div class="col-sm-4" style="min-height:280px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Available</label>
											</div>

											<ul id="sortableSkillsAvailable" class="skillsSortable">
												<?php 
													$availableSkills = array();
													
													$skills = Skill::model()->findAll(array('condition' => 'status=1 AND is_deleted=0'));
													
													if( $skills )
													{
														foreach( $skills as $skill )
														{
															$availableSkills[$skill->id] = $skill->skill_name; 
														}
													}
													
													if( $availableSkills && $accountTrainedSkills )
													{
														foreach( $accountTrainedSkills as $accountTrainedSkill )
														{
															unset( $availableSkills[$accountTrainedSkill->skill_id] );
														}
													}
													
													if( $availableSkills && $accountAssignedSkills )
													{
														foreach( $accountAssignedSkills as $accountAssignedSkill )
														{
															unset( $availableSkills[$accountAssignedSkill->skill_id] );
														}
													}
													
													if( $availableSkills )
													{
														foreach( $availableSkills as $availableSkillId => $availableSkillName )
														{
															echo '<li class="ui-state-default" data-id="'.$availableSkillId.'" >'.$availableSkillName.'</li>';
														}
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-4" style="min-height:280px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Trained</label>
											</div>
											
											<ul id="sortableSkillsTrained" class="skillsSortable">
												<?php 
													if( $accountTrainedSkills )
													{
														foreach( $accountTrainedSkills as $accountTrainedSkill )
														{
															echo '<li class="ui-state-default" data-id="'.$accountTrainedSkill->skill_id.'" >'.$accountTrainedSkill->skill->skill_name.'</li>';
														}
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-4">
											<div class="text-center">
												<label>Assigned</label>
											</div>

											<ul id="sortableSkillsAssigned" class="skillsSortable">
												<?php 
													if( $accountAssignedSkills )
													{
														foreach( $accountAssignedSkills as $accountAssignedSkill )
														{
															echo '<li class="ui-state-default" data-id="'.$accountAssignedSkill->skill_id.'" >'.$accountAssignedSkill->skill->skill_name.'</li>';
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
			
			<?php if( Yii::app()->user->account->checkPermission('employees_assigments_child_skills','visible') && Yii::app()->user->account->checkPermission('employees_assigments_child_skills','only_for_direct_reports', $account->id) ){ ?>
			<div class="col-sm-12">
				<div class="col-sm-offset-3 col-sm-9">
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">Child Skills</h2>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="widget-box" style="min-height:310px;">
							<div class="widget-body">
								<div class="widget-main"> 
									
									<div class="row">
										<div class="col-sm-4" style="min-height:280px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Available</label>
											</div>

											<ul id="sortableSkillChildsAvailable" class="skillsSortable">
												<?php 
													$availableSkills = array();
													
													$skills = Skill::model()->findAll(array('condition' => 'status=1 AND is_deleted=0'));
													
													if( $skills )
													{
														foreach( $skills as $skill )
														{
															foreach($skill->skillChilds as $skillChild)
															{
																$availableSkills[$skillChild->id] = $skill->skill_name.'-'.$skillChild->child_name;
															}
														}
													}
													
													if( $availableSkills && $accountTrainedSkillChilds )
													{
														foreach( $accountTrainedSkillChilds as $accountTrainedSkillChild )
														{
															unset( $availableSkills[$accountTrainedSkillChild->skill_child_id] );
														}
													}
													
													if( $availableSkills && $accountAssignedSkillChilds )
													{
														foreach( $accountAssignedSkillChilds as $accountAssignedSkillChild )
														{
															unset( $availableSkills[$accountAssignedSkillChild->skill_child_id] );
														}
													}
													
													if( $availableSkills )
													{
														foreach( $availableSkills as $availableSkillId => $availableSkillName )
														{
															echo '<li class="ui-state-default" data-id="'.$availableSkillId.'" >'.$availableSkillName.'</li>';
														}
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-4" style="min-height:280px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Trained</label>
											</div>
											
											<ul id="sortableSkillChildsTrained" class="skillsSortable">
												<?php 
													if( $accountTrainedSkillChilds )
													{
														foreach( $accountTrainedSkillChilds as $accountTrainedSkillChild )
														{
															echo '<li class="ui-state-default" data-id="'.$accountTrainedSkillChild->skill_child_id.'" >'.$accountTrainedSkillChild->skillChild->skill->skill_name.'-'.$accountTrainedSkillChild->skillChild->child_name.'</li>';
														}
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-4">
											<div class="text-center">
												<label>Assigned</label>
											</div>

											<ul id="sortableSkillChildsAssigned" class="skillsSortable">
												<?php 
													if( $accountAssignedSkillChilds )
													{
														foreach( $accountAssignedSkillChilds as $accountAssignedSkillChild )
														{
															echo '<li class="ui-state-default" data-id="'.$accountAssignedSkillChild->skill_child_id.'" >'.$accountAssignedSkillChild->skillChild->skill->skill_name.'-'.$accountAssignedSkillChild->skillChild->child_name.'</li>';
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
				
				</div>
			</div>
			<?php } ?>
			
			<?php if( $account->getIsAdmin() || in_array($account->account_type_id, array($account::TYPE_HOSTDIAL_AGENT, $account::TYPE_GRATON_AGENT, $account::TYPE_AGENT, $account::TYPE_GAMING_PROJECT_MANAGER, $account::TYPE_SALES, $account::TYPE_CUSTOMER_SERVICE, $account::TYPE_SUPERVISOR, $account::TYPE_TEAM_LEAD)) ): ?>
			 
				<div class="space-12"></div>
				
				<div class="col-sm-12">
					<div class="col-sm-4 col-sm-offset-3">
						<div class="row">
							<div class="col-sm-5">
								<h2 class="lighter blue">Companies</h2>
							</div>
						</div>
						
						<div class="row-fluid">
							<div class="widget-box" style="min-height:310px;">
								<div class="widget-body">
									<div class="widget-main"> 
										
										<div class="row">
											<div class="col-sm-6" style="min-height:280px; border-right:1px solid #e3e3e3;">
												<div class="text-center">
													<label>Available</label>
												</div>

												<ul id="sortableCompaniesAvailable" class="companiesSortable">
													<?php 
														$availableCompanies = array();
														
														$companies = Company::model()->findAll(array(
															'condition' => 'status=1 AND is_deleted=0',
															'order' => 'company_name ASC',
														));
														
														if( $companies )
														{
															foreach( $companies as $company )
															{
																$availableCompanies[$company->id] = $company->company_name; 
															}
														}
														
														if( $availableCompanies && $accountAssignedCompanies )
														{
															foreach( $accountAssignedCompanies as $accountAssignedCompany )
															{
																unset( $availableCompanies[$accountAssignedCompany->company_id] );
															}
														}
														
														if( $availableCompanies )
														{
															foreach( $availableCompanies as $accountAssignedCompanyId => $accountAssignedCompanyName )
															{
																echo '<li class="ui-state-default" data-id="'.$accountAssignedCompanyId.'" >'.$accountAssignedCompanyName.'</li>';
															}
														}
													?>
												</ul>
											</div>
											
											<div class="col-sm-6">
												<div class="text-center">
													<label>Assigned</label>
												</div>

												<ul id="sortableCompaniesAssigned" class="companiesSortable">
													<?php 
														if( $accountAssignedCompanies )
														{
															foreach( $accountAssignedCompanies as $accountAssignedCompany )
															{
																echo '<li class="ui-state-default" data-id="'.$accountAssignedCompany->company_id.'" >'.$accountAssignedCompany->company->company_name.'</li>';
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

					</div>
					
					<div class="col-sm-5">
						<div class="row">
							<div class="col-sm-5">
								<h2 id="customersLabel" class="lighter blue">Customers</h2>
							</div>
						</div>
						
						<div class="row-fluid">
							<div class="widget-box" style="min-height:310px;">
								<div class="widget-body">
									<div class="widget-main"> 
										
										<div class="row">
											<div class="col-sm-6" style="min-height:280px; border-right:1px solid #e3e3e3;">
												<div class="text-center">
													<label>
														Available
														<button class="btn btn-primary btn-minier btn-move-all-customers-to-assigned" style="width:40px;" account_id="<?php echo $account->id; ?>" disabled><i class="fa fa-arrow-right"></i></button>
													</label>
												</div>

												<ul id="sortableCustomersAvailable" class="customersSortable"></ul>
											</div>
											
											<div class="col-sm-6">
												<div class="text-center">
													<label>
														<button class="btn btn-primary btn-minier btn-move-all-customers-to-available" style="width:40px;" account_id="<?php echo $account->id; ?>" disabled><i class="fa fa-arrow-left"></i></button>
														Assigned
													</label>
												</div>

												<ul id="sortableCustomersAssigned" class="customersSortable"></ul>
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
				
				<?php endif; ?>
				
		</div>

	</div>
</div>