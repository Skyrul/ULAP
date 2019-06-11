<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;

	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

	$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

	$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } ');

	$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

	$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/hr/calendar_work_scheduler.js?t='.time(),  CClientScript::POS_END);
	
	
	$cs->registerScript(uniqid(),'
		
		var account_id = "'.$account->id.'";
		var current_pay_period = "'.$currentPayPeriod.'";
	
	', CClientScript::POS_HEAD);

	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("click", ".add-pto-btn", function(){
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/addPto",
					type: "post",
					dataType: "json",
					data: { "ajax": 1, "account_id": account_id },
					success: function(response){
						createFormRequestOngoing = false; 
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("#AccountPtoRequest_request_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("#AccountPtoRequest_request_date_end").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find(".pto-submit-btn").on("click", function(){
							
							var errors = ""
							
							if( $("#AccountPtoRequest_name").val() == "" )
							{
								errors += "Name is required. \n\n";
							}
							
							if( $("#AccountPtoRequest_request_date").val() == "" )
							{
								errors += "Request Start Date is required. \n\n";
							}
							
							if( $("#AccountPtoRequest_request_date_end").val() == "" )
							{
								errors += "Request End Date is required. \n\n";
							}
							
							if( $("#AccountPtoRequest_start_time").val() == "" )
							{
								errors += "Start Time is required. \n\n";
							}
							
							if( $("#AccountPtoRequest_end_time").val() == "" )
							{
								errors += "End Time is required. \n\n";
							}
							
							
							if( errors != "" )
							{
								alert(errors);
							}
							else
							{
								var data = modal.find("form").serialize() + "&account_id=" + account_id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/hr/accountUser/addPto",
									type: "post",
									dataType: "json",
									data: data,
									success: function(response){
										
										$.fn.yiiListView.update("ptoList", {});
										
										modal.modal("hide");
									}
								});
							}
							
						});
						
						modal.modal("show").on("hidden.bs.modal", function() {
							modal.remove();
						});
					},
				});
				
			});
			
			
			$(document).on("click", ".approve-pto-btn", function(){
				
				var Ids = $("#ptoList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/approvePto",
						type: "post",
						dataType: "json",
						data: { "Ids": Ids },
						success: function(response){
							
							$.fn.yiiListView.update("ptoList", {});
							
							$("#calendar").fullCalendar("refetchEvents");
						}
					});
				}
				
			});
			
			$(document).on("click", ".deny-pto-btn", function(){
				
				var Ids = $("#ptoList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/denyPto",
						type: "post",
						dataType: "json",
						data: { "Ids": Ids },
						success: function(response){
							
							$.fn.yiiListView.update("ptoList", {});
							
							$("#calendar").fullCalendar("refetchEvents");
						}
					});
				}
				
			});
			
			$(document).on("click", ".merge-selected-btn", function(){
				
				var Ids = $("#payPeriodList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					if( confirm("Are you sure you want to merge the selected records?") )
					{
						$.ajax({
							url: yii.urls.absoluteUrl + "/hr/accountUser/mergeVariance",
							type: "post",
							dataType: "json",
							data: { "Ids": Ids },
							success: function(response){
								
								$.fn.yiiListView.update("payPeriodList", {});
							}
						});
					}
				}
				
			});
			
			$(document).on("click", ".delete-selected-btn", function(){
						
				var Ids = $("#payPeriodList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					if( confirm("Are you sure you want to delete the selected records?") )
					{				
						$.ajax({
							url: yii.urls.absoluteUrl + "/hr/accountUser/deleteVariance",
							type: "post",
							dataType: "json",
							data: { "Ids": Ids },
							success: function(response){
								
								$.fn.yiiListView.update("payPeriodList", {});
							}
						});
					}
				}
			
			});
			
			$(document).on("click", ".variance-action-btn", function() {

				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/payPeriodVarianceAction",
					type: "post",
					dataType: "json",
					data: {"id": id},
					success: function(response){
			
						if(response.status  == "success")
						{
							modal = response.html;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("#AccountPtoRequest_request_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find(".approve-variance-btn").on("click", function(){

							var data = modal.find("form").serialize() + "&id=" + id + "&status=1";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/hr/accountUser/payPeriodVarianceAction",
								type: "post",
								dataType: "json",
								data: data,
								success: function(response){
									
									$.fn.yiiListView.update("payPeriodList", {});
									
									modal.modal("hide");
								}
							});
						});
						
						modal.find(".deny-variance-btn").on("click", function(){

							var data = modal.find("form").serialize() + "&id=" + id + "&status=3";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/hr/accountUser/payPeriodVarianceAction",
								type: "post",
								dataType: "json",
								data: data,
								success: function(response){
									
									$.fn.yiiListView.update("payPeriodList", {});
									
									modal.modal("hide");
								}
							});
						});
						
						modal.modal("show").on("hidden.bs.modal", function() {
							modal.remove();
						});
					}
				});
				
			});
			
			$(document).on("click", ".edit-variance-btn", function() {

				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/editVariance",
					type: "post",
					dataType: "json",
					data: {"id": id},
					success: function(response){
			
						if(response.status  == "success")
						{
							modal = response.html;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("#AccountLoginTracker_time_in_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("#AccountLoginTracker_time_out_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find(".save-btn").on("click", function(){
		
							errors = "";
		
							if( $("#AccountLoginTracker_time_in_date").val() == "" )
							{
								errors += "Login date is required \n \n";
							}
		
							if( $("#AccountLoginTracker_time_in").val() == "" )
							{
								errors += "Login time is required \n \n";
							}
							
							if( $("#AccountLoginTracker_time_out_date").val() == "" )
							{
								errors += "Logout date is required \n \n";
							}
			
							if( $("#AccountLoginTracker_time_out").val() == "" )
							{
								errors += "Logout time is required \n \n";
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{
								var data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/hr/accountUser/editVariance",
									type: "post",
									dataType: "json",
									data: data,
									success: function(response){
										
										$.fn.yiiListView.update("payPeriodList", {});
										
										modal.modal("hide");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function() {
							modal.remove();
						});
					}
				});
				
			});
			
			
			$(document).on("click", ".add-variance-btn", function() {
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/addVariance",
					type: "post",
					dataType: "json",
					data: {"ajax": 1},
					success: function(response){
			
						if(response.status  == "success")
						{
							modal = response.html;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("#AccountLoginTracker_time_in_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("#AccountLoginTracker_time_out_date").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find(".save-btn").on("click", function(){
		
							errors = "";
		
							if( $("#AccountLoginTracker_time_in_date").val() == "" )
							{
								errors += "Login date is required \n \n";
							}
		
							if( $("#AccountLoginTracker_time_in").val() == "" )
							{
								errors += "Login time is required \n \n";
							}
							
							if( $("#AccountLoginTracker_time_out_date").val() == "" )
							{
								errors += "Logout date is required \n \n";
							}
			
							if( $("#AccountLoginTracker_time_out").val() == "" )
							{
								errors += "Logout time is required \n \n";
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{
								var data = modal.find("form").serialize() + "&account_id=" + account_id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/hr/accountUser/addVariance",
									type: "post",
									dataType: "json",
									data: data,
									success: function(response){
										
										$.fn.yiiListView.update("payPeriodList", {});
										
										modal.modal("hide");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function() {
							modal.remove();
						});
					}
				});
				
			});
			
			
			$(document).on("change", "#payPeriodFilterSelect", function(){
				
				var value = $(this).val();
				
				$("#payperiodExportBtn").attr("href", yii.urls.absoluteUrl + "/hr/accountUser/exportPayPeriod?filter=" + value + "&account_id=" + account_id);
				
				$.fn.yiiListView.update("payPeriodList",  { data: {filter: value} });
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/ajaxGetTotalLoginHours",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "account_id": account_id, "filter": value },
					success: function(response) {
						$(".total-work-hours").text(response.value);
					}
				});
								
			});
			
			
			if( current_pay_period != "" )
			{
				$("#payPeriodFilterSelect").val(current_pay_period);
				$("#payPeriodFilterSelect").trigger("change");
			}
			
			
			$(document).on("click", ".approve-pto-form-btn", function(){
				
				var Ids = $("#ptoFormList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/approvePtoForm",
						type: "post",
						dataType: "json",
						data: { "Ids": Ids },
						success: function(response){
							
							$.fn.yiiListView.update("ptoFormList", {});
							
							//$("#calendar").fullCalendar("refetchEvents");
						}
					});
				}
				
			});
			
			$(document).on("click", ".deny-pto-form-btn", function(){
				
				var Ids = $("#ptoFormList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/denyPtoForm",
						type: "post",
						dataType: "json",
						data: { "Ids": Ids },
						success: function(response){
							
							$.fn.yiiListView.update("ptoFormList", {});
							
							//$("#calendar").fullCalendar("refetchEvents");
						}
					});
				}
				
			});
			
				ptoFormAjaxSending = false;
				
				$(".add-pto-form-btn").on("click",function(){
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/AjaxAddPtoForm",
						type: "GET",	
						data: { 
							"id" : "'.$account->id.'"			
						},
						beforeSend: function(){
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
						
							header = "Add Time-Off Request";
							$("#myModalMd #myModalLabel").html(header);
							$("#myModalMd .modal-body").html(r);
							$("#myModalMd").modal();
							
						},
					});
				});
		});
		
		
	');
	
	
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
			<li class="active"><?php echo CHtml::link('Time Keeping', array('timeKeeping', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports', $account->id) ){ ?>
			<li><?php echo CHtml::link('Assignments', array('assignments', 'id'=>$account->id)); ?></li>
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
					
					<div class="row">
						<div class="col-sm-12">
							<div id="document-sources">
								<div class="document-filelist"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="hr hr-18 hr-double dotted"></div>
		
		<div class="row">
			<div class="col-sm-6">
				<div class="col-sm-12">
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">Time Off Request</h2>
						</div>
						<div class="col-sm-7 text-right">
							<div style="margin-top:20px;">
								
								<?php //if( Yii::app()->user->account->checkPermission('employees_time_keeping_approve_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_approve_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-success approve-pto-form-btn"><i class="fa fa-check"></i> Approve</button>
								<?php //} ?>
								
								<?php //if( Yii::app()->user->account->checkPermission('employees_time_keeping_deny_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_deny_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-danger deny-pto-form-btn"><i class="fa fa-check"></i> Deny</button>
								<?php //} ?>
								
								<?php //if( Yii::app()->user->account->checkPermission('employees_time_keeping_add_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_add_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-primary add-pto-form-btn"><i class="fa fa-plus"></i> Add</button>
								<?php //}  ?>
								
							</div>
						</div>
					</div>
					
					<div class="widget-box">
						<div class="widget-body">
							<div class="widget-main no-padding"> 
								<?php 
									$this->widget('zii.widgets.CListView', array(
										'id'=>'ptoFormList',
										'dataProvider'=>$ptoFormDataProvider,
										'itemView'=>'_pto_form_list',
										'template'=>'<table class="table table-striped table-hover table-condensed">{items}</table> <div class="text-center">{pager}</div>',
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
				
				<div class="col-sm-12">
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">PTO Request</h2>
						</div>
						<div class="col-sm-7 text-right">
							<div style="margin-top:20px;">
								
								<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_approve_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_approve_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-success approve-pto-btn"><i class="fa fa-check"></i> Approve</button>
								<?php } ?>
								
								<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_deny_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_deny_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-danger deny-pto-btn"><i class="fa fa-check"></i> Deny</button>
								<?php } ?>
								
								<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_add_pto_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_add_pto_button','only_for_direct_reports', $account->id) ){ ?>
									<button type="button" class="btn btn-xs btn-primary add-pto-btn"><i class="fa fa-plus"></i> Add</button>
								<?php } ?>
								
							</div>
						</div>
					</div>
					
					<div class="widget-box">
						<div class="widget-body">
							<div class="widget-main no-padding"> 
								<?php 
									$this->widget('zii.widgets.CListView', array(
										'id'=>'ptoList',
										'dataProvider'=>$ptoDataProvider,
										'itemView'=>'_pto_list',
										'template'=>'<table class="table table-striped table-hover table-condensed">{items}</table> <div class="text-center">{pager}</div>',
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
			
			<div class="col-sm-6">
				<div class="row">
					<div class="col-sm-3">
						<h2 class="lighter blue">Pay Period</h2>
					</div>
					<div class="col-sm-2">
						<div class="infobox infobox-blue pull-left" style="border:none; margin-left:-35px;">
							<div class="infobox-icon">
								<i class="ace-icon fa fa-clock-o"></i>
							</div>

							<div class="infobox-data">
								<span class="infobox-data-number total-work-hours">0<?php //echo $account->getTotalLoginHours(); ?></span>
								<div class="infobox-content" style="margin-left:-8px; font-size:9px;">Total Hours Worked</div>
							</div>
						</div>
						
						<div class="clearfix"></div>
					</div>
					
					<div class="col-sm-7">
						<div style="margin-top:20px;">
							<div class="col-sm-5">
								<?php echo CHtml::dropDownList('', '', $payPeriodOptions, array('id'=>'payPeriodFilterSelect')); ?>
							</div>
							
							<div class="col-sm-7">
								<?php 
									if( Yii::app()->user->account->checkPermission('employees_time_keeping_export_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_export_button','only_for_direct_reports', $account->id) )
									{
										echo CHtml::link('<i class="fa fa-share"></i> Export', array('accountUser/exportPayPeriod', 'filter'=>0, 'account_id'=>$account->id), array('id'=>'payperiodExportBtn', 'class'=>'btn btn-yellow btn-xs'));
									}
								?>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-sm-12">
						<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_merge_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_merge_button','only_for_direct_reports', $account->id) ){ ?>
							<a class="btn btn-xs btn-purple merge-selected-btn"><i class="fa fa-compress"></i> Merge</a>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_add_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_add_button','only_for_direct_reports', $account->id) ){ ?>
							<a class="btn btn-xs btn-primary add-variance-btn"><i class="fa fa-plus"></i> Add</a>
						<?php } ?>
						
						<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_delete_button','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_delete_button','only_for_direct_reports', $account->id) ){ ?>
							<a class="btn btn-xs btn-danger delete-selected-btn"><i class="fa fa-times"></i> Delete</a>
						<?php } ?>
					</div>
				</div>
				
				<div class="widget-box">
					<div class="widget-body">
						<div class="widget-main no-padding"> 								
							<?php 
								$this->widget('zii.widgets.CListView', array(
									'id'=>'payPeriodList',
									'dataProvider'=>$payPeriodDataProvider,
									'itemView'=>'_pay_period_list',
									'template'=>'<table class="table table-striped table-hover table-condensed">{items}</table> <div class="text-center">{pager}</div>',
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
		
		<br />
		<br />
		
		<div class="hr hr-18 hr-double dotted"></div>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_calendar','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_calendar','only_for_direct_reports', $account->id) ){ ?>
		<div class="row">	
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-2">
						<h2 class="lighter blue" style="line-height: 20px;">Work Schedule</h2>
					</div>
					<div class="col-sm-5">
						<div class="infobox infobox-blue pull-left" style="border:none; margin-left:-35px;">
							<div class="infobox-icon">
								<i class="ace-icon fa fa-calendar"></i>
							</div>

							<div class="infobox-data">
								<span class="infobox-data-number total-schedule-work-hours">0</span>
								<div class="infobox-content">Total Scheduled</div>
							</div>
						</div>
						
						<div class="clearfix"></div>
					</div>
				</div>
			
				<div class="row">
					<div class="col-sm-2">
						<div id="external-events">
							<div data-rel="tooltip" data-placement="left" data-original-title="Drag and drop on the date you want to black out" style="cursor:grab; position: fixed; z-index: 2; bottom: auto; top: 320px; right: 0px; text-align: center; font-size: 11px; border-radius: 9px 0px 0px 9px; width: 61px; height: 40px; line-height: 20px;" class="external-event label-success ui-draggable ui-draggable-handle" data-class="label-success">
							   WORK HOURS
							</div>
						</div>
						
						<div id="external-events">
							<div data-rel="tooltip" data-placement="left" data-original-title="Drag and drop on the date you want to black out" style="cursor:grab; position: fixed; z-index: 2; bottom: auto; top: 380px; right: 0px; text-align: center; font-size: 11px; border-radius: 9px 0px 0px 9px; width: 61px; height: 40px; line-height: 20px;" class="external-event label-purple ui-draggable ui-draggable-handle" data-class="label-purple">
							   LUNCH HOURS
							</div>
						</div>
					</div>
				</div>
				
				<br />
				
				<div class="row">
					<div class="col-sm-12">
						<div id="calendar"></div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>