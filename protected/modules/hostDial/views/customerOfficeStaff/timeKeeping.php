<?php 
	$this->pageTitle = 'Engagex - Update User | Time Keeping';
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;

	$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } ');
		
	$cs->registerScript(uniqid(),'
		
		var account_id = "'.$account->id.'";
		var current_pay_period = "'.$currentPayPeriod.'";
	
	', CClientScript::POS_HEAD);

	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){		
			
			$(document).on("click", ".merge-selected-btn", function(){
				
				var Ids = $("#payPeriodList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					if( confirm("Are you sure you want to merge the selected records?") )
					{
						$.ajax({
							url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/mergeVariance",
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
							url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/deleteVariance",
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
					url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/payPeriodVarianceAction",
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
								url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/payPeriodVarianceAction",
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
								url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/payPeriodVarianceAction",
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
					url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/editVariance",
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
									url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/editVariance",
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
					url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/addVariance",
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
									url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/addVariance",
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
				
				$("#payperiodExportBtn").attr("href", yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/exportPayPeriod?filter=" + value + "&account_id=" + account_id);
				
				$.fn.yiiListView.update("payPeriodList",  { data: {filter: value} });
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/ajaxGetTotalLoginHours",
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
		User Settings 
		<small><i class="ace-icon fa fa-angle-double-right"></i> <?php echo $model->staff_name; ?></small>
	</h1>
</div>

<div class="tabbable">

	<ul id="myTab" class="nav nav-tabs">

		<li class="">
			<a href="<?php echo $this->createUrl('update', array('id'=>$model->id, 'customer_id'=>$model->customer_id)); ?>">
				Profile
			</a>
		</li>
		
		<li class="active">
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
	
	<div class="tab-content" style="overflow:hidden;">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="col-sm-8">
				<div class="row">
					<div class="col-sm-3">
						<h2 class="lighter blue">Time Punches</h2>
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
	</div>
</div>