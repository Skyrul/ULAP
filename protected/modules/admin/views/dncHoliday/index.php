<?php
	$this->pageTitle = 'Engagex - DNC Holidays';
?>

<?php 

	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerScript( uniqid(), '
	
		$(document).ready(function(){
			
			$(document).on("click", ".btn-add-federal-holiday", function(){
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/addFederalHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					beforeSend: function(){},
					success: function( response ){
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
				
						var modal = $(modal).appendTo("body");
						
						modal.find(".datepicker").datepicker({
							// minDate: 0,
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = "";
							
							if( modal.find("#DncHolidayFederal_name").val() == "" )
							{
								errors += "Name is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayFederal_date").val() == "" )
							{
								errors += "Date is required. \n\n"	
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{					
								data = modal.find("form").serialize() + "&ajax=1";
								
								$.ajax({
									url: yii.urls.absoluteUrl + "admin/dncHoliday/addFederalHoliday",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
										modal.find("button[data-action=save]").html("Saving Please Wait...");
										modal.find("button[data-action=save]").prop("disabled", true);
									},
									success: function(response) {
											
										if( response.status == "success" )
										{													
											alert(response.message);
											modal.modal("hide");
										}
										else
										{
											alert(response.message);
										}
										
										modal.find("button[data-action=save]").html("Save");
										modal.find("button[data-action=save]").prop("disabled", false);
										
										$.fn.yiiListView.update("federalHolidayList");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					},
				});
				
			});
			
			$(document).on("click", ".btn-edit-federal-holiday", function(){
				
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/editFederalHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					beforeSend: function(){},
					success: function( response ){
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
				
						var modal = $(modal).appendTo("body");
						
						modal.find(".datepicker").datepicker({
							// minDate: 0,
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = "";
							
							if( modal.find("#DncHolidayFederal_name").val() == "" )
							{
								errors += "Name is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayFederal_date").val() == "" )
							{
								errors += "Date is required. \n\n"	
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{					
								data = modal.find("form").serialize() + "&ajax=1&id="+id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "admin/dncHoliday/editFederalHoliday",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
										modal.find("button[data-action=save]").html("Saving Please Wait...");
										modal.find("button[data-action=save]").prop("disabled", true);
									},
									success: function(response) {
											
										if( response.status == "success" )
										{													
											alert(response.message);
											modal.modal("hide");
										}
										else
										{
											alert(response.message);
										}
										
										modal.find("button[data-action=save]").html("Save");
										modal.find("button[data-action=save]").prop("disabled", false);
										
										$.fn.yiiListView.update("federalHolidayList");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					},
				});
				
			});
			
			$(document).on("click", ".btn-delete-federal-holiday", function(){
				
				var id = $(this).prop("id");
				
				if( confirm("Are you sure you want to delete this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/admin/dncHoliday/deleteFederalHoliday",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "id":id },
						beforeSend: function(){},
						success: function( response ){
							
							$.fn.yiiListView.update("federalHolidayList");
							
						},
					});
				}
			});
			
			
			$(document).on("click", ".btn-add-state-holiday", function(){
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/addStateHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					beforeSend: function(){},
					success: function( response ){
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
				
						var modal = $(modal).appendTo("body");
						
						modal.find(".datepicker").datepicker({
							// minDate: 0,
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = "";
							
							if( modal.find("#DncHolidayState_state").val() == "" )
							{
								errors += "State is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayState_name").val() == "" )
							{
								errors += "Name is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayState_date").val() == "" )
							{
								errors += "Date is required. \n\n"	
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{					
								data = modal.find("form").serialize() + "&ajax=1";
								
								$.ajax({
									url: yii.urls.absoluteUrl + "admin/dncHoliday/addStateHoliday",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
										modal.find("button[data-action=save]").html("Saving Please Wait...");
										modal.find("button[data-action=save]").prop("disabled", true);
									},
									success: function(response) {
											
										if( response.status == "success" )
										{													
											alert(response.message);
											modal.modal("hide");
										}
										else
										{
											alert(response.message);
										}
										
										modal.find("button[data-action=save]").html("Save");
										modal.find("button[data-action=save]").prop("disabled", false);
										
										$.fn.yiiListView.update("stateHolidayList");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					},
				});
				
			});
			
			$(document).on("click", ".btn-edit-state-holiday", function(){
				
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/editStateHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					beforeSend: function(){},
					success: function( response ){
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
				
						var modal = $(modal).appendTo("body");
						
						modal.find(".datepicker").datepicker({
							// minDate: 0,
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = "";
							
							if( modal.find("#DncHolidayState_state").val() == "" )
							{
								errors += "State is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayState_name").val() == "" )
							{
								errors += "Name is required. \n\n"	
							}
							
							if( modal.find("#DncHolidayState_date").val() == "" )
							{
								errors += "Date is required. \n\n"	
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{					
								data = modal.find("form").serialize() + "&ajax=1&id="+id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "admin/dncHoliday/editStateHoliday",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
										modal.find("button[data-action=save]").html("Saving Please Wait...");
										modal.find("button[data-action=save]").prop("disabled", true);
									},
									success: function(response) {
											
										if( response.status == "success" )
										{													
											alert(response.message);
											modal.modal("hide");
										}
										else
										{
											alert(response.message);
										}
										
										modal.find("button[data-action=save]").html("Save");
										modal.find("button[data-action=save]").prop("disabled", false);
										
										$.fn.yiiListView.update("stateHolidayList");
									}
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					},
				});
				
			});
		});
	
		$(document).on("click", ".btn-delete-state-holiday", function(){
				
			var id = $(this).prop("id");
			
			if( confirm("Are you sure you want to delete this?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/deleteStateHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					beforeSend: function(){},
					success: function( response ){
						
						$.fn.yiiListView.update("stateHolidayList");
						
					},
				});
			}
		});
	
	', CClientScript::POS_END);
	
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<h1>DNC Holidays</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="col-sm-6">
		
			<div class="row-fluid">
				<button class="btn btn-minier btn-success btn-add-federal-holiday"><i class="fa fa-plus"></i> Add Federal Holiday</button>
			</div>
			
			<h3>FEDERAL HOLIDAYS</h3>
			
			<?php 
				$this->widget('zii.widgets.CListView', array(
					'id'=>'federalHolidayList',
					'dataProvider'=>$federalHolidayDataProvider,
					'itemView' => '_federal_holiday_list',
					'viewData' => array(),
					'summaryText' => '{start} - {end} of {count}',
					'emptyText' => '<tr><td colspan="4">No results found.</td></tr>',
					'template'=>'
						<table class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<th width="15%"></th>
								<th>Holiday</th>
								<th>Date</th>
								<th>Audit (Dials Made)</th>
							</thead>
							{items}  
						</table> 
					',
					'pagerCssClass' => 'pagination', 
					'pager' => array(
						'header' => '',
					),
				)); 
			?>
		</div>
		
		<div class="col-sm-6">
			<div class="row-fluid">
				<button class="btn btn-minier btn-success btn-add-state-holiday"><i class="fa fa-plus"></i> Add State Holiday</button>
			</div>
			
			<h3>STATE HOLIDAYS</h3>
			
			<?php 
				$this->widget('zii.widgets.CListView', array(
					'id'=>'stateHolidayList',
					'dataProvider'=>$stateHolidayDataProvider,
					'itemView' => '_state_holiday_list',
					'viewData' => array(),
					'summaryText' => '{start} - {end} of {count}',
					'emptyText' => '<tr><td colspan="5">No results found.</td></tr>',
					'template'=>'
						<table class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<th width="15%"></th>
								<th>State</th>
								<th>Holiday</th>
								<th>Date</th>
								<th>Audit (Dials Made)</th>
							</thead>
							{items}  
						</table> 
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

