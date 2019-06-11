<?php 

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCss(uniqid(), ' 
	.profile-info-name { width:150px !important; } 
	.profile-user-info { width:calc(100%) !important; }
');

$cs->registerScript(uniqid(), '

$(document).ready( function(){
	
	$(document).on("change", "#select-list", function(){
		
		if( $(this).val() == "Create New" )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/lists/create",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				success: function(response) {
					
					$("#select-list").val("");

					if(response.status  == "success")
					{
						modal = response.html;
						
						var modal = $(modal).appendTo("body");
					
						modal.find("button[data-action=save]").on("click", function() {
							
							data = modal.find("form").serialize() + "&ajax=1";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/lists/create",
								type: "post",
								dataType: "json",
								data: data,
								success: function(response){
									
									$.gritter.add({
										title: "List Creation",
										text: response.message,
										class_name: "gritter-" + response.status,
									});
					
									if( response.status == "success" )
									{
										window.history.pushState({}, "", yii.urls.absoluteUrl + "/leads/index/id/" + response.id);
										
										$.ajax({
											url: yii.urls.absoluteUrl + "/lists/view",
											type: "post",
											dataType: "json",
											data: { "ajax":1, "id":response.id },
											beforeSend: function(){
												$.gritter.removeAll();
												
												$.gritter.add({
													title: "List Details",
													text: "Loading Please Wait...",
													class_name: "",
												});
											},
											success: function(response){
												
												if( response.html != "" )
												{
													$(".list-details").html(response.html);
												}
											}
										});
									}
									
								},
							});
							
							modal.modal("hide");
						});
											
						modal.modal("show").on("hidden", function(){
							modal.remove();
						}); 
					}
				}
			});
		}
		else
		{
			var id = $(this).val();
			
			$(location).attr("href", yii.urls.absoluteUrl + "/leads/index/id/" + id);
		}
		
	});
	
	
	$(document).on("click", ".lead-details", function(){
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/leads/view",
			type: "post",
			dataType: "json",
			data: { "ajax":1 },
			success: function(response) {
				
				if(response.status  == "success")
				{
					modal = response.html;
				}
				
				var modal = $(modal).appendTo("body");
									
				modal.modal("show").on("hidden", function(){
					modal.remove();
				});
			}
		});
		
	});
	
	
	$(document).on("keyup", ".input", function(e) {
		
		if (e.which == 13) 
		{
			data = $("form#leadsManualEnter").serialize();
	
			$.ajax({
				url: yii.urls.absoluteUrl + "/leads/create",
				type: "post",
				dataType: "json",
				data: data,
				success: function(response) {
					
					if(response.status  == "success")
					{
						$("#leadList > tbody tr:first-child").after(response.html);
						
						$(".input").val("");
					}
					
				}
			});
			
		}
	});
	
});

', CClientScript::POS_END);

?>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'calendar_page',
		'customer' => isset($_REQUEST['customer_id']) ? Customer::model()->findByPk($_REQUEST['customer_id']) : null,
	));
?>

<div class="page-header">
	<h1>Lead Lists</h1>
</div>

<div class="row">
	<div class="col-xs-12">
	
		<div class="row">
			<div class="col-xs-3">
				<select id="select-list">
					<optgroup>
						<option value="">- Select -</option>
						<option value="Create New" class="text-center">Create New</option>
					</optgroup>
					<optgroup label="Lists">
						<?php 
							if( $lists )
							{
								foreach( $lists as $list )
								{
									$selected = $model->id == $list->id ? 'selected' : '';
									
									echo '<option value="'.$list->id.'" '.$selected.'>'.$list->name.'</option>';
								}
							}
							else
							{
								echo '<option value="" class="text-center" disabled>No list found.</option>';
							}
						?>
					</optgroup>
				</select>
			</div>
		</div>
		
		<div class="hr hr-18 hr-double dotted"></div>
	
		<?php 
			if(!$model->isNewRecord)
			{
			?>
		
			<div class="list-details">
				<div class="row">
				
					<div class="col-xs-4">
						<div class="widget-box widget-color-blue2 light-border">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter">List Details - <?php echo $model->name; ?></h4>

								<div class="widget-toolbar">
									<a data-action="collapse" href="#">
										<i class="ace-icon fa fa-chevron-up"></i>
									</a>
								</div>
							</div>

							<div class="widget-body">
								<div class="widget-main no-padding">
									
									<?php $form=$this->beginWidget('CActiveForm', array(
										'enableAjaxValidation'=>false,
										'htmlOptions' => array(
											'class' => 'form-horizontal',
										),
									)); ?>
						
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> Status </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'status', $model::getStatusOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> # of Leads </div>

											<div class="profile-info-value">
												<span><?php echo $model->leadCount; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Ordering Type </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'lead_ordering', $model::getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Skill Assignment </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'skill_id', CustomerSkill::items( isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : null), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Calendar Assignment </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'calendar_id', Calendar::items( isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : null), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Creation Date </div>

											<div class="profile-info-value">
												<span>
													<?php 
														if(!$model->isNewRecord)
														{
															echo date('m/d/Y', strtotime($model->date_created)); 
														}
													?>
												</span>
											</div>
										</div>
									</div>
									
									<?php $this->endWidget(); ?>
									
								</div>
							</div>
						</div>
					</div>

					<div class="col-xs-3 col-xs-offset-1">
						<div class="widget-box widget-color-blue2 light-border">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter">List Performance - <?php echo $model->name; ?></h4>

								<div class="widget-toolbar">
									<a data-action="collapse" href="#">
										<i class="ace-icon fa fa-chevron-up"></i>
									</a>
								</div>
							</div>

							<div class="widget-body">
								<div class="widget-main no-padding">
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of callable </div>

											<div class="profile-info-value">
												<span>0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of appointments </div>

											<div class="profile-info-value">
												<span>0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of wrong numbers </div>

											<div class="profile-info-value">
												<span>0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of completed leads </div>

											<div class="profile-info-value">
												<span>0</span>
											</div>
										</div>
									</div>
									
								</div>
							</div>
						</div>
					</div>
					
				</div>
				
				<div class="hr hr-18 hr-double dotted"></div>
				
				<div class="row">
					<div class="col-xs-12">
						<div class="widget-box widget-color-blue2 ">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter"><?php echo $model->name; ?></h4>

								<div class="widget-toolbar no-border">									
									<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
										<form class="form-search">
											<span class="input-icon">
												<input type="text" autocomplete="off" id="nav-search-input" class="nav-search-input" placeholder="Search ...">
												<i class="ace-icon fa fa-search nav-search-icon"></i>
											</span>
										</form>
									</div>
								</div>
								
								
								<div class="widget-toolbar no-border">
									<div class="btn-group btn-overlap btn-corner" data-toggle="buttons">
										<label class="btn btn-sm btn-white btn-info active">
											Current 
											<input type="radio" name="searchType" value="Current">											
										</label>

										<label class="btn btn-sm btn-white btn-info">
											All 
											<input type="radio" name="searchType" value="All">
										</label>
									</div>
								</div>
							</div>

							<div class="widget-body">
								<div class="widget-main padding-6 no-padding-left no-padding-right clearfix">
									<div class="col-xs-12">
										<table id="leadList" class="table table-striped table-bordered">
											<thead>
												<th>
													<label>
														<input type="checkbox" class="ace" name="form-field-checkbox">
														<span class="lbl">&nbsp;</span>
													</label>
												</th>
												
												<th>Home Phone</th>
												<th>Mobile Phone</th>
												<th>Office Phone</th>
												<th>First name</th>
												<th>Last Name</th>
												<th>Partner's First Name</th>
												<th>Partner's Last Name</th>
												<th>Email</th>
												<th>Custom Date</th>
												<th>Creation Date</th>
												<th># of Dials</th>
												<th>Status</th>
											</thead>
											
											<tbody>
												<tr>
													<form id="leadsManualEnter">
													
														<input type="hidden" name="Lead[list_id]" value="<?php echo $model->id; ?>">
													
														<td></td>

														<td><input type="text" name="Lead[home_phone_number]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[mobile_phone_number]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[office_phone_number]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[first_name]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[last_name]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[partner_first_name]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[partner_last_name]" class="input" style="width:74px;"></td>
														<td><input type="text" name="Lead[email_address]" class="input" style="width:74px;"></td>
														
														<td><input type="text" name="Lead[custom_date]" class="input" style="width:74px;"></td>
														
														<td></td>
														
														<td>0</td>
														
														<td>
															<select name="Lead[status]">
																<option>Active</option>
																<option>Inactive</option>
																<option>Complete</option>
															</select>
														</td>
													</form>
												</tr>

												<?php 
													if( $leads )
													{
														$ctr = 1;
														
														foreach( $leads as $lead )
														{
														?>
														
															<tr>
																<td>
																	<label>
																		<input type="checkbox" class="ace" name="form-field-checkbox">
																		<span class="lbl">&nbsp;</span>
																	</label>
																</td>
																
																<td><?php echo $lead->home_phone_number; ?></td>
																<td><?php echo $lead->mobile_phone_number; ?></td>
																<td><?php echo $lead->office_phone_number; ?></td>
																<td><?php echo $lead->first_name; ?></td>
																<td><?php echo $lead->last_name; ?></td>
																<td><?php echo $lead->partner_first_name; ?></td>
																<td><?php echo $lead->partner_last_name; ?></td>
																<td><?php echo $lead->email_address; ?></td>
																<td><?php echo $lead->custom_date; ?></td>
																<td><?php echo date('m/d/Y'); ?></td>
																<td><?php echo $lead->number_of_dials; ?></td>
																	
																<td>
																	<select name="Lead[status]">
																		<option>Active</option>
																		<option>Inactive</option>
																		<option>Complete</option>
																	</select>
																</td>
															</tr>
														
														<?php 
														$ctr++;
														}
													}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		
			<?php
			}
			else
			{
			?>
		
			<div class="row list-details"> 
				<div class="col-xs-12">No list found.</div>
			</div>
			
			<?php 
			}
		?>

	</div><!-- /.col -->
</div><!-- /.row -->

