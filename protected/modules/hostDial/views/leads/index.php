<?php 

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile($baseUrl.'/css/ludo-jquery-treetable/jquery.treetable.css');
$cs->registerCssFile($baseUrl.'/css/ludo-jquery-treetable/jquery.treetable.theme.default.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');
	
$cs->registerScriptFile($baseUrl.'/js/jquery.treetable.js');
$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScript(uniqid(), '
	
		var list_id = "'.$model->id.'";
		var customer_id = '.$customer_id.';
		
	', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(), "
	
		
		// $.mask.definitions['~']='[+-]';
		// $('.input-mask-phone').mask('(999) 999-9999');
		// $('.input-mask-zip').mask('99999');
		
		// setInterval(function(){ 
		
			// var listPerformanceSending = false;
		
			// if( !listPerformanceSending )
			// {
				// listPerformanceSending = true;
				
				// $.ajax({
					// url: yii.urls.absoluteUrl + '/customer/leads/listPerformance',
					// type: 'post',
					// dataType: 'json',
					// data: { 'ajax':1, 'list_id':list_id, 'customer_id':customer_id },
					// success: function(response){
						
						// listPerformanceSending = false;
						
						// $('.list-performance-wrapper .callables').text(response.callables);
						// $('.list-performance-wrapper .appointments').text(response.appointments);
						// $('.list-performance-wrapper .wrong-numbers').text(response.wrong_numbers);
						// $('.list-performance-wrapper .completed-leads').text(response.completed_leads);
						
					// },
				// });
			// }
		// }, 10000);	
		
		var ajaxLeadViewProcessing = false;
	
	
		$(document).on('change', '.data-tab-dropdown', function(){
			
			var list_id = $(this).val();
			var lead_id = $(this).attr('lead_id');
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/hostDial/leads/ajaxLoadCustomData',
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
	",CClientScript::POS_END); 
	
	
	//selected list top 3 buttons js
	$cs->registerScript(uniqid(), '
	
	//list settings
	$(".update-list-btn").on("click",function(){

		var list_id = $(this).prop("id");
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/hostDial/lists/AjaxUpdateForm",
			type: "GET",	
			data: { 
				"id" : list_id,		
				"customer_id" : '.$customer_id.'	
			},
			beforeSend: function(){
			},
			complete: function(){
			},
			error: function(){
			},
			success: function(r){
			
				header = "<i class=\"fa fa-gear\"></i> List Settings";
				$("#myModalMd #myModalLabel").html(header);
				$("#myModalMd .modal-body").html(r);
				$("#myModalMd").modal();
				
			},
		});
	});
	
	//upload-script
	$(".upload-script").on("click",function(){

		var list_id = $(this).prop("id");
		
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/hostDial/lists/uploadScript",
			type: "GET",	
			data: { 
				"id" : list_id,		
				"customer_id" : customer_id	
			},
			beforeSend: function(){
			},
			complete: function(){
			},
			error: function(){
			},
			success: function(r){
			
				header = "<i class=\"fa fa-file-text\"></i> Upload Script";
				$("#myModalMd #myModalLabel").html(header);
				$("#myModalMd .modal-body").html(r);
				$("#myModalMd").modal();
				
			},
		});
	});
	
	//agent assignment
	$(".list-agent-assignment").on("click",function(){

		var list_id = $(this).prop("id");
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/hostDial/lists/ajaxAgentAssignment",
			type: "GET",	
			data: { 
				"id" : list_id,		
				"customer_id" : '.$customer_id.'	
			},
			beforeSend: function(){
			},
			complete: function(){
			},
			error: function(){
			},
			success: function(r){
			
				header = "<i class=\"fa fa-group\"></i> Agent Assignment";
				$("#myModalMd #myModalLabel").html(header);
				$("#myModalMd .modal-body").html(r);
				$("#myModalMd").modal();
				
			},
		});
	});
	
	
	//Lead Search
	var ajaxLeadViewProcessing = false;
	$(document).on("click", ".lead-details", function(e){
		e.preventDefault();
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			//var id = $(this).prop("id");
			var thisUrl = $(this).prop("href");
			console.log(thisUrl);
			$.ajax({
				url: thisUrl,
				type: "POST",
				dataType: "json",
				data: { "ajax":1},
				success: function(response) {
					
					ajaxLeadViewProcessing = false;
					
					if(response.html  != "" )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo("body");
					
					modal.find(".date-picker").datepicker({
						autoclose: true,
						todayHighlight: true
					});
					
					modal.find(".input-mask-phone").mask("(999) 999-9999");
					modal.find(".input-mask-zip").mask("99999");
					
					modal.find("button[data-action=save]").on("click", function() {
								
						modal.find("button[data-action=save]").addClass("disabled");
						modal.find("button[data-action=save]").html("Saving Please Wait...");

						data = modal.find("form").serialize();
						
						$.ajax({
							url: thisUrl,
							type: "post",
							dataType: "json",
							data: data,
							success: function(response) {
								
								var search_query = response.id;
								console.log(search_query);
								$.ajax({
									url: yii.urls.absoluteUrl + "/hostDial/leads/index",
									type: "GET",
									// dataType: "json",
									data: {
										"customer_id": '.$customer_id.',
										"search_query": search_query,
										"leadSearch": 1
									},
									beforeSend: function() {
										$("#leadSearchLoader").show();
									},
									complete: function(){
										 $("#leadSearchLoader").hide();
									},
									success: function(result) {
										$("#leadSearch").empty().append(result);
									}
								});
				
								
								if( response.status == "success" )
								{									
									modal.modal("hide");
								}
								
								modal.find("button[data-action=save]").removeClass("disabled");
								modal.find("button[data-action=save]").html("Save");
							}
						});
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
		}	
	});

	
	var ajaxLeadStatusProcessing = false;
	
	//Lead Status
	$(document).on("click", ".lead-status", function(e){
		e.preventDefault();
		if( !ajaxLeadStatusProcessing )
		{
				ajaxLeadStatusProcessing = true;
				
				//var id = $(this).prop("id");
				var thisUrl = $(this).prop("href");
				console.log(thisUrl);
				$.ajax({
					url: thisUrl,
					type: "POST",
					dataType: "json",
					data: { "ajax":1},
					success: function(response) {
						
						ajaxLeadStatusProcessing = false;
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");
						
						
						modal.find("button[data-action=save]").on("click", function() {
									
							modal.find("button[data-action=save]").addClass("disabled");
							modal.find("button[data-action=save]").html("Saving Please Wait...");

							data = modal.find("form").serialize();
							
							$.ajax({
								url: thisUrl,
								type: "post",
								dataType: "json",
								data: data,
								success: function(response) {
									if( response.status == "success" )
									{				
										$.fn.yiiListView.update("leadList", {});								
										modal.modal("hide");
									}
									
									modal.find("button[data-action=save]").removeClass("disabled");
									modal.find("button[data-action=save]").html("Save");
								}
							});
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
		}
			
	});
	
	var ajaxLeadDataTabProcessing = false;
	
	//Lead Data Tab
	$(document).on("click", ".lead-data-tab", function(e){
		e.preventDefault();
		if( !ajaxLeadDataTabProcessing )
		{
				ajaxLeadDataTabProcessing = true;
				
				var thisUrl = $(this).prop("href");
				console.log(thisUrl);
				$.ajax({
					url: thisUrl,
					type: "POST",
					dataType: "json",
					data: { "ajax":1},
					success: function(response) {
						
						ajaxLeadDataTabProcessing = false;
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");
						
						modal.find(".date-picker").datepicker({
							autoclose: true,
							todayHighlight: true
						});
						
						modal.find(".input-mask-phone").mask("(999) 999-9999");
						modal.find(".input-mask-zip").mask("99999");
						
						modal.find("button[data-action=save]").on("click", function() {
									
							modal.find("button[data-action=save]").addClass("disabled");
							modal.find("button[data-action=save]").html("Saving Please Wait...");

							data = modal.find("form").serialize();
							
							$.ajax({
								url: thisUrl,
								type: "post",
								dataType: "json",
								data: data,
								success: function(response) {
									if( response.status == "success" )
									{				
										$.fn.yiiListView.update("leadList", {});								
										modal.modal("hide");
									}
									
									modal.find("button[data-action=save]").removeClass("disabled");
									modal.find("button[data-action=save]").html("Save");
								}
							});
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
		}
			
	});
	
	
	var ajaxLeadHistoryProcessing = false;
	
	//Lead History
	$(document).on("click", ".lead-history", function(e){
		e.preventDefault();
		if( !ajaxLeadHistoryProcessing )
		{
				ajaxLeadHistoryProcessing = true;
				
				var thisUrl = $(this).prop("href");
				console.log(thisUrl);
				$.ajax({
					url: thisUrl,
					type: "POST",
					dataType: "json",
					data: { "ajax":1},
					success: function(response) {
						
						ajaxLeadHistoryProcessing = false;
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {
									
							modal.find("button[data-action=save]").addClass("disabled");
							modal.find("button[data-action=save]").html("Saving Please Wait...");

							data = modal.find("form").serialize();
							
							$.ajax({
								url: thisUrl,
								type: "post",
								dataType: "json",
								data: data,
								success: function(response) {
									if( response.status == "success" )
									{				
										$.fn.yiiListView.update("leadList", {});								
										modal.modal("hide");
									}
									
									modal.find("button[data-action=save]").removeClass("disabled");
									modal.find("button[data-action=save]").html("Save");
								}
							});
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
		}
			
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
					url: yii.urls.absoluteUrl + "/hostDial/leads/createLeadHistory",
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
		
	',CClientScript::POS_END);
?>

<?php 
	Yii::app()->clientScript->registerScript('lead-remove-dnc-btn-JS','
		
		var ajaxLeadRemoveDncProcessing = false;
	
		//Lead remove from dnc 
		$(document).on("click", ".lead-remove-dnc", function(e){
			
			e.preventDefault();
			
			if( confirm("Are you sure you want to remove this lead from DNC?") )
			{
				if( !ajaxLeadRemoveDncProcessing )
				{
					ajaxLeadRemoveDncProcessing = true;
					
					var thisUrl = $(this).prop("href");

					$.ajax({
						url: thisUrl,
						type: "POST",
						dataType: "json",
						data: { "ajax":1},
						success: function(response) {
							
							alert(response);
						
						}
					});
				}
			}
				
		});
		
	',CClientScript::POS_END);
?>
		
<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#669ad8;"> 
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
      <div class="modal-header" style="background:#669ad8;">
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
	$this->widget("application.components.HostDialSideMenu",array(
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
	<h1 class="bigger">Leads</h1>
</div>



<div class="row">

	<div class="col-sm-12">
	
		
		<div class="tabbable tabs-left">
			
			<ul class="nav nav-tabs" role="tablist">
			<?php 
				$this->widget("application.components.HostDialLeadsMenu",array(
					'active'=> 'lead',
					'customer' => $customer_id ? Customer::model()->findByPk($customer_id) : null,
				));
			?>
			</ul>
		
			<style>
				.tab-pane{padding:5px;border:1px #c5d0dc solid;}
				.hostDial-subheader{ background-color:#669ad8;padding:4px;}
				.hostDial-subheader h3 { color:#0000009c; font-size:22px;margin:0px;font-weight:bolder; }
				
				.tabpanel-table{padding-left:15px;}
				.tabpanel-table thead tr { border: #ccc 1px solid; margin: 1px;}
				.tabpanel-table td { padding:2px;}
				
				.table-striped>tbody>tr:nth-child(odd)>td, .table-striped>tbody>tr:nth-child(odd)>th {
					background-color: #eaeaea;
				}
				
				.hostDial-list-action{color:#393939;}
			</style>
			
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane in active" id="list">
					<?php if(empty($listModel)){ ?>
					
								<div class="hostDial-subheader">
									<h3>All List</h3>
								</div>
								
								<br>
								
								<?php $this->renderPartial('_listTabAll',array(
									'customer_id' => $customer_id,
									'lists' => $lists
								)); ?>
					<?php }else{ ?>
								
							
								<?php $this->renderPartial('_listTabSelected',array(
									'listModel' => $listModel,
									'listLeadModel' => $listLeadModel,
									'listDataProvider' => $listDataProvider,
									'customer_id' => $customer_id,
									'lists' => $lists
								)); ?>
					<?php  } ?>
				</div>
				
				<div role="tabpanel" class="tab-pane" id="type">
				
					<div class="hostDial-subheader">
						<h3>List by Type</h3>
					</div>
					
					<br>
					
					<?php $this->renderPartial('_typeTab',array(
						'customer_id' => $customer_id,
						'lists' => $lists,
					)); ?>
				</div>
				
				<div role="tabpanel" class="tab-pane" id="month">
				
					<div class="hostDial-subheader">
						<h3>Month</h3>
					</div>
					
					<br>
					
					<?php $this->renderPartial('_monthTab',array(
						'customer_id' => $customer_id,
						'lists' => $lists
					)); ?>
					
				</div>
				
				<div role="tabpanel" class="tab-pane" id="status">
					<div class="hostDial-subheader">
						<h3>Status</h3>
					</div>
					
					<br>
					
					<?php $this->renderPartial('_statusTab',array(
						'customer_id' => $customer_id,
						'lists' => $lists
					)); ?>
				</div>
				
				<div role="tabpanel" class="tab-pane" id="lead">
					<div class="hostDial-subheader">
						<h3>Lead</h3>
					</div>
					
					<br>
					
					<?php $this->renderPartial('_leadTab',array(
						'customer_id' => $customer_id,
						'leads' => $leads,
						'leadsByMemberNumber' => $leadsByMemberNumber,
					)); ?>
				</div>
				
				<div role="tabpanel" class="tab-pane" id="add-new">
					<div class="hostDial-subheader">
						<h3>Create New List</h3>
					</div>
					
					<br>
					<?php $this->forward('/hostDial/lists/create/'.$customer_id,false); ?>
			
					
				</div>
			</div>
		</div>
	</div>
</div>

