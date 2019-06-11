<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;

	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("click", ".merge-selected-btn", function(){
				
				var Ids = $("#payPeriodList input:checkbox:checked").map(function() {
				  return $(this).val();
				}).get();
				
				if (Ids.length !== 0) 
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/accounting/accounting/mergeVariance",
						type: "post",
						dataType: "json",
						data: { "Ids": Ids },
						success: function(response){
							
							$.fn.yiiListView.update("payPeriodList", {});
						}
					});
				}
				
			});
			
			$(document).on("click", ".variance-action-btn", function() {

				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/accounting/accounting/payPeriodVarianceAction",
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
								url: yii.urls.absoluteUrl + "/accounting/accounting/payPeriodVarianceAction",
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
								url: yii.urls.absoluteUrl + "/accounting/accounting/payPeriodVarianceAction",
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
					url: yii.urls.absoluteUrl + "/accounting/accounting/editVariance",
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
									url: yii.urls.absoluteUrl + "/accounting/accounting/editVariance",
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
					url: yii.urls.absoluteUrl + "/accounting/accounting/addVariance",
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
									url: yii.urls.absoluteUrl + "/accounting/accounting/addVariance",
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
				
				$("#payperiodExportBtn").attr("href", yii.urls.absoluteUrl + "/accounting/accounting/exportPayPeriod?filter=" + value + "&account_id=" + account_id);
				
				$.fn.yiiListView.update("payPeriodList",  { data: {filter: value} });
				
			});
			
		});
		
	');
	
	
?>

<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'timeKeeping'
	));
?>

<?php
	foreach(Yii::app()->user->getFlashes() as $key => $message) {
		echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
	}
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				Exception Punches
			</h1>
		</div>
	</div>
</div>

<div class="row">
	
	<div class="col-sm-12">
		
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
		
