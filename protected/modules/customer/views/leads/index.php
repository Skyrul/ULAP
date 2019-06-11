<?php 
	$listName = str_replace("'", '', $model->name);

	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerCss(uniqid(), '
	
		.pager-container{ border-top:1px solid #ccc; padding:15px; margin-bottom:15px; }
		.pagination { margin:0 !important; }
		.summary { text-align:left !important; }
	');
	
	$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');
	
	$cs->registerScriptFile($baseUrl . '/js/leads/leads.js?'.time());
	
	$cs->registerScript(uniqid(), '
	
		var list_id = "'.$model->id.'";
		var customer_id = '.$customer_id.';
		
	', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(), "
		$.mask.definitions['~']='[+-]';
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-zip').mask('99999');
		
		setInterval(function(){ 
		
			var listPerformanceSending = false;
		
			if( !listPerformanceSending )
			{
				listPerformanceSending = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + '/customer/leads/listPerformance',
					type: 'post',
					dataType: 'json',
					data: { 'ajax':1, 'list_id':list_id, 'customer_id':customer_id },
					success: function(response){
						
						listPerformanceSending = false;
						
						$('.list-performance-wrapper .callables').text(response.callables);
						$('.list-performance-wrapper .appointments').text(response.appointments);
						$('.list-performance-wrapper .wrong-numbers').text(response.wrong_numbers);
						$('.list-performance-wrapper .completed-leads').text(response.completed_leads);
						
					},
				});
			}
		
			if( $('.input-mask-phone').val() == '' && !$('.input-mask-phone').is(':focus') )
			{
				$('.input-mask-phone').mask('(999) 999-9999'); 
			}
			
			if( $.trim($('.nav-search-input').val()) != '' )
			{
				$('.nav-search-input').closest('.widget-header').find('.widget-title').text('Searching All list...');
			}
			else
			{
				$('.nav-search-input').closest('.widget-header').find('.widget-title').text('".$listName."');
			}
			
		}, 10000);
		
		$(document).on('change', 'form#updateListForm select', function(){
			
			data = $('form#updateListForm').serialize() + '&ajax=1&id=' + list_id + '&customer_id=' + customer_id;
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/lists/update',
				type: 'post',
				dataType: 'json',
				data: data,
				success: function(response){ console.log(response); },
			});
			
		});
		
		$(document).on('change', '.data-tab-dropdown', function(){
			
			var list_id = $(this).val();
			var lead_id = $(this).attr('lead_id');
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/leads/ajaxLoadCustomData',
				type: 'post',
				dataType: 'json',
				beforeSend: function(){ 
					$('.data-tab-dropdown-container').append('<span class=\'loader\'>Loading please wait...</span>');
				},
				data: { 		
					'ajax' : 1,	
					'list_id' : list_id,	
					'lead_id' : lead_id,	
				},
				success: function(r){
					$('.modal .data-fields-tab').html(r.html);
					$('.loader').remove();
				},
			});
		});
		
	", CClientScript::POS_END);
?>


<?php 
	Yii::app()->clientScript->registerScript('modal-recyle-moduleJS','
		$(".modal-recyle-module").on("click",function(){
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/insight/recycleLeads/",
				type: "GET",	
				data: { 
					"customer_id" : "'.$customer_id.'",		
					"list_id" : "'.$model->id.'",	
					"page" : "lead"	
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "Recertify Leads";
					$("#myModalLg #myModalLabel").html(header);
					$("#myModalLg .modal-body").html(r);
					$("#myModalLg").modal();
					
				},
			});
		});
	',CClientScript::POS_END);
?>

<?php 
	Yii::app()->clientScript->registerScript('lead-history-submit-btn-JS','
		
		$(document).on("click", ".lead-history-submit-btn", function(){
		
			if( $("#LeadHistory_note").val() != "" )
			{
				data = $("#leadHistoryForm").serialize();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/leads/createLeadHistory",
					type: "post",
					dataType: "json",
					data: data,
					success: function(result) { 

						$("#LeadHistory_note").val("");
						$(".lead-history-table-wrapper").html(result.html);
					}
				});
			}
			
		});
		
		$(document).on("click", ".delete-list", function(){
		
			var id = $(this).prop("id");
		
			if( confirm("Are you sure you want to delete this list?") )
			{
				window.location.replace("'.Yii::app()->createUrl('customer/list/delete', array('id'=>$model->id)).'");
			}
			
		});
		
	',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('list-add-survey','

	$("body").on("change", "#'.CHtml::activeId($model,'skill_id').'", function(){
		
		thisVal = $(this).val();
		
		var options = $("#'.CHtml::activeId($model,'survey_id').'");
		options.empty();
		options.append($("<option />").val("").text("No Skill Selected"));
		
		$("#viewSurvey").hide();
		$("#viewSurveyPDF").hide();
		
		
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/customer/leads/getSurveyBySkill').'",
			method: "GET",
			dataType: "json",
			data: {				  
			  "skill_id" : thisVal,					  
			}
		}).success(function(response) {
			
			var options = $("#'.CHtml::activeId($model,'survey_id').'");
			options.empty();
			
			if (response.length === 0) {
				options.append($("<option />").val("").text("No Survey found"));
			}
			else{
				options.append($("<option />").val("").text("-Select Survey-"));
				$.each(response, function() {
					options.append($("<option />").val(this.id).text(this.survey_name));
				});
				
				$("#viewSurvey").prop("href","'.Yii::app()->createUrl('/customer/survey/export/customer_id/'.$model->customer_id.'/id/'.$model->survey_id.'/list_id/'.$model->id).'").show();
				$("#viewSurveyPDF").prop("href","'.Yii::app()->createUrl('/customer/survey/exportPDF/customer_id/'.$model->customer_id.'/id/'.$model->survey_id.'/list_id/'.$model->id).'").show();
			}
			
		});
	});
	
	$("#viewSurvey").on("click",function(e){
		var surveyId = $("#'.CHtml::activeId($model,'survey_id').'").val();
		
		if(surveyId > 0)
		{
			$(this).prop("href","'.Yii::app()->createUrl('/customer/survey/export',array('customer_id'=>$model->customer_id)).'/id/"+surveyId+"/list_id/'.$model->id.'");
		}
		else
		{
			alert("Select Survey");
			e.preventDefault();
		}
	});
	
	$("#viewSurveyPDF").on("click",function(e){
		var surveyId = $("#'.CHtml::activeId($model,'survey_id').'").val();
		
		if(surveyId > 0)
		{
			$(this).prop("href","'.Yii::app()->createUrl('/customer/survey/exportPDF',array('customer_id'=>$model->customer_id)).'/id/"+surveyId+"/list_id/'.$model->id.'");
		}
		else
		{
			alert("Select Survey");
			e.preventDefault();
		}
	});
',CClientScript::POS_END); 	


?>

<?php Yii::app()->clientScript->registerScript('survey-report-settings','

	$(document).ready( function(){ 
	
		$(document).on("click", ".delivery-settings-btn", function(){
					
			var this_button = $(this);
			var original_text = this_button.html();
			
			var report_name = $(this).attr("report_name");
			var skill_id = $("#'.CHtml::activeId($model,'skill_id').'").val();
			var customer_id = "'.$customer_id.'";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/survey/ajaxSurveyReportDeliverySettings",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "report_name":report_name, "skill_id":skill_id, "customer_id":customer_id },
				beforeSend: function(){
					
					this_button.html("Loading...");
					
				},
				success: function(response) {
					
					this_button.html(original_text);
					
					if(response.html  != "" )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo("body");
					
					modal.find("#ReportDeliverySettings_type").on("change", function(){
						
						if( $(this).val() == 2 ) //send to email
						{
							$(".auto_email_recipients_container").show();
							
						}
						else
						{
							$("#ReportDeliverySettings_auto_email_recipients").val("");
							$(".auto_email_recipients_container").hide();
						}
						
					});
					
					modal.find("button[data-action=save]").on("click", function() {
								
						var this_button = $(this);
								
						var data = modal.find("form").serialize();
						
						var errors = "";
						
						if( $("#ReportDeliverySettings_type").val() != "" )
						{
							if( $("#ReportDeliverySettings_type").val() == 2 && $("#ReportDeliverySettings_auto_email_recipients").val() == "" )
							{
								errors += "Email address is required."
							}
						}
						
						if( errors != "" )
						{
							alert("Please fix the following: \n\n" + errors);
						}
						else
						{
							$.ajax({
								url: yii.urls.absoluteUrl + "/customer/survey/ajaxSurveyReportDeliverySettings/",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){
									
									this_button.html("Saving...");
								},
								success: function(response) {

									modal.modal("hide");
								}
							});
						}
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
		});
		
		$(document).on("click", ".delivery-settings-btn-remove", function(){
					
			var id = $(this).prop("id");
			var this_button = $(this);
			var this_row = this_button.closest("tr");
			
			if( confirm("Are you sure you want to remove this?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/survey/ajaxSurveyReportDeliveryDeleteSettings",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					beforeSend: function(){
						
						this_button.html("Deleting...");
						
					},
					success: function(response) {
						
						this_row.fadeOut("fast", function() {
							$(this).remove();
						});
						
					}
				});
			}
		});
	});
	
',CClientScript::POS_END); 	


?>


<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModalLg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id ? Customer::model()->findByPk($customer_id) : null,
	));
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

<div class="page-header">
	<?php 
		if( Yii::app()->user->account->checkPermission('customer_leads_import_settings_button','visible')  )
		{
			echo CHtml::link('<i class="fa fa-cogs"></i> Import Settings', array('lists/importSettings', 'customer_id'=>$customer_id), array('class'=>'btn btn-primary btn-sm pull-right')); 
		}
	?>
	<h1>Lead Lists</h1>
</div>

<div class="row">
	<div class="col-xs-12">
	
		<select id="select-list" customer_id="<?php echo $customer_id; ?>">
			<optgroup>
				<option value="">- Select -</option>
				
				<?php if( Yii::app()->user->account->checkPermission('customer_leads_create_new_list_dropdown','visible') ){ ?>
					<option value="Create New">Create New</option>
				<?php } ?>
			</optgroup>
			
			<optgroup label="Existing Lists">
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

		<div class="hr hr-18 hr-double dotted"></div>
	
		<?php 
			if(!$model->isNewRecord)
			{
			?>
		
			<div class="list-details">
				<div class="row">
				
					<?php if( Yii::app()->user->account->checkPermission('customer_leads_list_details_page','visible') ){ ?>
					
					<div class="col-xs-7">
						<div class="widget-box widget-color-blue2 light-border">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter">List Details - <?php echo $model->name; ?></h4>

								<div class="widget-toolbar">
									<?php 
										echo CHtml::link('<i class="ace-icon fa fa-cog"></i>', array('lists/update', 'id'=>$model->id, 'customer_id'=>$customer_id), array('class'=>'white', 'title'=>'List Settings'))
									?>

									<a data-action="collapse" href="#">
										<i class="ace-icon fa fa-chevron-up"></i>
									</a>
								</div>
								
								<div class="widget-toolbar no-border">
									
								</div>
							</div>

							<div class="widget-body">
								<div class="widget-main no-padding">
									
									<?php $form=$this->beginWidget('CActiveForm', array(
										'id' => 'updateListForm',
										'enableAjaxValidation'=>false,
										'htmlOptions' => array(
											'class' => 'form-horizontal',
										),
									)); ?>
						
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> Status </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'status', $model::getStatusOptions(), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> # of Leads </div>

											<div class="profile-info-value">
												<span><?php echo $model->leadCount; ?></span>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Call Order </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'lead_ordering', $model->getOrderingOptions(), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										<?php if($model->skill->enable_survey_tab == 1): ?>
										
											<div class="profile-info-row">
												<div class="profile-info-name"> Skill Assignment </div>

												<div class="profile-info-value">
													<div class="col-sm-4">
														<?php echo $form->dropDownList($model, 'skill_id', CustomerSkill::items($customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
													</div>
													<div class="col-sm-8">
														<?php if( $totalLeads == 0 ): ?>
														
														<div class="alert alert-danger">
															No contract level found. <a href="<?php echo $this->createUrl('customerSkill/index', array('customer_id'=>$customer_id)); ?>">Click here</a> to start adding.
														
														</div>
														<?php endif; ?>
													</div>
												</div>
											</div>

											<div class="profile-info-row">
												<div class="profile-info-name"> Survey Assignment </div>

												<div class="profile-info-value">
													<?php echo $form->dropDownList($model, 'survey_id', Survey::items($model->skill_id, $customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
												</div>
											</div>
											
											<div class="profile-info-row">
												<div class="profile-info-name"> Survey Report </div>

												<div class="profile-info-value">
													
													<table class="table table-striped table-hover table-bordered" style="width:60%">
														<tr>
															<td>csv</td>
															<td>
																<?php echo CHtml::link('Download survey results', array('survey/export','customer_id'=>$model->customer_id,'id'=>$model->survey_id,'list_id'=>$model->id),array('target'=>'_blank','id'=>'viewSurvey')); ?>
															</td>
															
															<?php if( Yii::app()->user->account->getIsAdmin() ): ?>																
																<td>
																	<?php echo CHtml::link('Delivery Settings', '#',array('report_name'=>'surveyXlsx', 'class'=>'delivery-settings-btn')); ?>
																</td>														
															<?php endif; ?>
														</tr>
														
														<tr>
															<td>pdf</td>
															<td>
																<?php echo CHtml::link('Download survey analytic', array('survey/exportPDF','customer_id'=>$model->customer_id,'id'=>$model->survey_id,'list_id'=>$model->id),array('target'=>'_blank','id'=>'viewSurveyPDF')); ?>
															</td>
															
															<?php if( Yii::app()->user->account->getIsAdmin() ): ?>
																<td>
																	<?php echo CHtml::link('Delivery Settings', '#',array('report_name'=>'surveyPdf', 'class'=>'delivery-settings-btn')); ?>
																</td>
															<?php endif; ?>
														</tr>
													</table>
													
												</div>
											</div>
											
										<?php endif; ?>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Calendar Assignment </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'calendar_id', Calendar::items($customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
											</div>
										</div>
										
										
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Language </div>

											<div class="profile-info-value">
												<?php echo $form->dropDownList($model, 'language', $model::getLanguageOptions(), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
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
										
										<div class="profile-info-row">
											<div class="profile-info-name"></div>
											
											<div class="profile-info-value text-center">
												
												<?php 
													if( $model->skill->enable_list_custom_mapping == 1 )
													{
														echo CHtml::link('<i class="fa fa-edit"></i> Custom Mapping', array('lists/customMapping', 'id'=>$model->id, 'customer_id'=>$customer_id), array('class'=>'btn btn-purple btn-minier'));
													}
												?>
												
												<?php 
													if( !$model->isNewRecord && Yii::app()->user->account->checkPermission('customer_leads_add_additional_leads_button','visible') )
													{
														echo CHtml::link('<i class="fa fa-plus"></i> Add Additional Leads', array('lists/update', 'id'=>$model->id, 'customer_id'=>$customer_id, 'simpleView'=>true), array('class'=>'btn btn-minier btn-success')); 
													}
												?>
												
												<?php 
													if( Yii::app()->user->account->checkPermission('customer_leads_download_list_button','visible') )
													{
														echo CHtml::link('<i class="fa fa-download"></i> Download List', array('lists/downloadList', 'id'=>$model->id), array('class'=>'btn btn-primary btn-minier'));
													}
												?>
												
												<?php 
													if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('customer_leads_delete_list_button','visible') )
													{
														echo CHtml::link('<i class="fa fa-times"></i> Delete List', array('lists/delete', 'id'=>$model->id), array('class'=>'btn btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this list?'));
													}
												?>
												
											</div>
										</div>
									</div>
									
									<?php $this->endWidget(); ?>
									
								</div>
							</div>
						</div>
					</div>
					
					<?php } ?>

					<div class="col-xs-5 list-performance-wrapper">
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
												<span class="callables">0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of appointments </div>

											<div class="profile-info-value">
												<span class="appointments">0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of wrong numbers </div>

											<div class="profile-info-value">
												<span class="wrong-numbers">0</span>
											</div>
										</div>
									</div>
									
									<div class="profile-user-info profile-user-info-striped">
										<div class="profile-info-row">
											<div class="profile-info-name"> # of completed leads </div>

											<div class="profile-info-value">
												<span class="completed-leads">0</span>
											</div>
										</div>
									</div>
									
								</div>
							</div>
						</div>
					</div>
					
				</div>
				
				<div class="hr hr-18 hr-double dotted"></div>
				
				<?php 
					if( Yii::app()->user->account->checkPermission('customer_leads_recertify_button','visible') )
					{
						echo CHtml::button('Recertify',array('class'=>'btn btn-info modal-recyle-module')); 
					}
				?>
				
				<br/>
				
				<div class="row">
					<div class="col-xs-12">
						<div class="widget-box widget-color-blue2 ">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter">
									<?php echo $model->name; ?> 
								</h4> 

								<div class="widget-toolbar no-border">									
									<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
										<div class="form-search">
											<span class="input-icon">
												<input type="text" name="leadSearchQuery" autocomplete="off" id="lead-search-input" class="nav-search-input" placeholder="Search Leads..." style="width:200px;">
												<i class="ace-icon fa fa-search nav-search-icon"></i>
											</span>
										</div>
									</div>
								</div>
								
								<!--<div class="widget-toolbar no-border">
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
								</div>-->
							</div>

							<div class="widget-body">
								<div class="widget-main no-padding clearfix">
									
									<form method="post" id="leadsManualEnter"></form>
									
									<?php 
									
										$leadManualEntry = '';
									
										if( $model->manually_enter == 1 )
										{
											$leadManualEntry = '
												<tr>
													<td class="center"></td>
													
													<td class="center"><input type="text" name="Lead[office_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
													
													<td class="center"><input type="text" name="Lead[mobile_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
													
													<td class="center"><input type="text" name="Lead[home_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
													
													<td class="center"><input type="text" name="Lead[first_name]" class="col-sm-12 manual-lead-input" form="leadsManualEnter"></td>
													
													<td class="center"><input type="text" name="Lead[last_name]" class="col-sm-12 manual-lead-input" form="leadsManualEnter"></td>
													
													<td class="center"></td>
													
													<td class="center"></td>
													
													<td class="center"><input type="text" name="Lead[number_of_dials]" class="col-sm-12  manual-lead-input" form="leadsManualEnter" value="0"></td>
													
													<td class="center">'.CHtml::dropDownList('Lead[status]', 1, Lead::statusOptions(), array('class'=>'col-sm-12 manual-lead-input', 'form'=>'leadsManualEnter')).'</td>
												</tr>
											';
										}
									
										$this->widget('zii.widgets.CListView', array(
											'id'=>'leadList',
											'dataProvider'=>$dataProvider,
											'itemView'=>'_lead_list',
											'summaryText' => '{start} - {end} of {count}',
											'emptyText' => '',
											'template'=>'
												<table id="leadsTbl" class="table table-bordered table-condensed table-hover">
													<thead>
														<th></th>
														<th class="center">Office Number</th>
														<th class="center">Mobile Number</th>
														<th class="center">Phone Number</th>
														<th class="center">First Name</th>
														<th class="center">Last Name</th>
														<th class="center">List Name</th>
														<th class="center">Creation Date</th>
														<th class="center"># of Dials</th>
														<th class="center">Status</th>
													</thead>
													'.$leadManualEntry.'
													{items}  
												</table> 
												<div class="col-sm-12"> 
													<div class="pager-container"> 
														<div class="col-sm-6">{summary}</div> 
														<div class="col-sm-6 text-right">{pager}</div>
													</div>
												</div>
											',
											'pagerCssClass' => 'pagination', 
											'pager' => array(
												'header' => '',
											),
										)); 
									?>
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

