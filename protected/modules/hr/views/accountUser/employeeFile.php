<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/hr/employee_notes_multiple_uploader.js');
	$cs->registerScriptFile($baseUrl . '/js/hr/employee_documents_multiple_uploader.js');
	
	$cs->registerCss(uniqid(), '
		.percentage { font-size:12px; font-weight:normal; }
	');
	
	$cs->registerScript(uniqid(), ' var user_account_id = "'.$accountUser->id.'"; ', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("click", ".employee-notes-submit-btn", function(){
				
				var this_button = $(this);
				
				var formSending = false;
				
				var data = $("form#employeeNotesForm").serialize() + "&ajax=1&id='.$account->id.'";
				
				if( !formSending && $.trim($("#employeeNotesTextArea").val()) != "" )
				{
					formSending = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/employeeFile",
						type: "post",
						dataType: "json",
						data: data,
						beforeSend: function(){							
							this_button.html("Saving Please Wait...");
						},
						success: function(response){
							
							$.fn.yiiListView.update("notesList", {});
							
							$("#employeeNotesTextArea").val("");
							$("#noteTypeSelect").val("");
							$("#noteCategorySelect").val("");
							$(".filelist").empty();
							
							formSending = false;
							this_button.html("Submit");
						},
					});
				}
				
			});
			
			$(document).on("click", ".document-delete-btn", function() {
				
				var id = $(this).prop("id");
				
				if( confirm("Are you sure you want to delete this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/employeeDeleteFile",
						type: "post",
						dataType: "json",
						data: {"ajax":1, "id": id},
						success: function(response){
							
							$.fn.yiiListView.update("docsList", {});
						},
					});
				}
				
				return false;
				
			});
			
			$(document).on("change", ".document-type-select", function() {
				
				var id = $(this).prop("id");
				var type = $(this).val();
				
				if( type != "" )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/employeeUpdateFile",
						type: "post",
						dataType: "json",
						data: {"ajax":1, "id": id, "type":type},
						success: function(response){
							
							$.fn.yiiListView.update("docsList", {});
						},
					});
				}
					
			});
			
			$(document).on("click", ".document-add-types", function() {
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hr/accountUser/employeeAddDocumentType",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					success: function(response) {

						ajaxLeadViewProcessing = false;
						
						if(response.html  != "" )
						{
							modal = response.html;
						}
											
						var modal = $(modal).appendTo("body");

						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
						modal.find(".tbl-document-type-list tbody input:text").on("keyup", function(){
								
							var id = $(this).closest("tr").prop("id");
							var value = $(this).val();
							var field = "name";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/hr/accountUser/employeeUpdateDocumentType",
								type: "post",
								dataType: "json",
								data: {"ajax":1, "id": id, value:value, "field":field},
								success: function(response){
									$.fn.yiiListView.update("docsList", {});
								},
							});

						});
						
						modal.find(".tbl-document-type-list tbody input:checkbox").on("change", function() {
							
							var id = $(this).closest("tr").prop("id");
							
							if( $(this).hasClass("checkbox-status") )
							{
								var field = "status";
								
								if( $(this).is(":checked") )
								{
									value = 1;
								}
								else
								{
									value = 2;
								}
							}
							else if( $(this).hasClass("checkbox-show-edit") )
							{
								var field = "show_edit_button";
								
								if( $(this).is(":checked") )
								{
									value = 1;
								}
								else
								{
									value = 0;
								}
							}
							else
							{
								var field = "show_delete_button";
								
								if( $(this).is(":checked") )
								{
									value = 1;
								}
								else
								{
									value = 0;
								}
							}

							$.ajax({
								url: yii.urls.absoluteUrl + "/hr/accountUser/employeeUpdateDocumentType",
								type: "post",
								dataType: "json",
								data: {"ajax":1, "id": id, value:value, "field":field},
								success: function(response){
									
									$.fn.yiiListView.update("docsList", {});
								},
							});
							
						});
						
						modal.find(".btn-delete-document-type").on("click", function(){
							
							if( confirm("Are you sure you want to delete this?") )
							{
								var id = $(this).prop("id");
								var container = $(this).closest("tr");
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/hr/accountUser/employeeDeleteDocumentType",
									type: "post",
									dataType: "json",
									data: {"ajax":1, "id": id },
									success: function(response){
										
										container.fadeOut("fast", function() {
											$(this).remove();
										});
										
										$.fn.yiiListView.update("docsList", {});
									},
								});
							}
						});
						
						modal.find("button[data-action=save]").on("click", function() {
							
							var errors = "";
							
							if( modal.find("#AccountUserDocumentType_name").val() == "" )
							{
								errors += "Type name is required \n\n";
							}
							
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{			
								data = modal.find("form").serialize();								

								$.ajax({
									url: yii.urls.absoluteUrl + "/hr/accountUser/employeeAddDocumentType",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
										modal.find("button[data-action=save]").html("Adding Please Wait...");
										modal.find("button[data-action=save]").prop("disabled", true);
									},
									success: function(response) {
											
										if( response.status = "success" && response.html != "" )
										{
											modal.find(".tbl-document-type-list tbody").html(response.html);
											// modal.modal("hide");
										}
										
										modal.find("button[data-action=save]").html("Add");
										modal.find("button[data-action=save]").prop("disabled", false);
									}
								});
							}
							
						});
					}
				});
					
			});
			
		});
		
	', CClientScript::POS_END);
?>

<?php 
	// $this->widget("application.components.HrSideMenu",array(
		// 'active'=> Yii::app()->controller->id
	// ));
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
		
		<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_tab','only_for_direct_reports') ){ ?>
			<li class="active"><?php echo CHtml::link('Employee File', array('employeeFile', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_tab','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Time Keeping', array('timeKeeping', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Assignments', array('assignments', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_performance_tab','visible') && Yii::app()->user->account->checkPermission('employees_performance_tab','only_for_direct_reports') ){ ?>
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
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-3 col-sm-offset-2">
							<?php 
								if($accountUser->getImage())
								{
									echo CHtml::image($accountUser->getImage(), '', array('class'=>'img-responsive'));
								}
								else
								{
									echo '<div style="height:180px; border:1px dashed #ccc; text-align:center; line-height: 180px;">No Image Uploaded.</div>';
								}
							?>
						</div>
						<div class="col-sm-6 text-center">
							<h3><?php echo $accountUser->getFullName(); ?></h3>
						</div>
					</div>
					
					<div class="hr hr-18 hr-double dotted"></div>
					
					<div class="row">
						<div class="col-sm-7" style="line-height:30px;">
							Documents 
						</div>
						<div class="col-sm-5 text-right">
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_upload_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_upload_button','only_for_direct_reports') ){ ?>
								<button id="plupload-document-select-files" class="btn btn-success btn-xs">Upload</button>
							<?php }  ?>
							
							<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_document_types_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_document_types_button','only_for_direct_reports') ){ ?>
								<button class="btn btn-primary btn-xs document-add-types">Types</button>
							<?php } ?>
						</div>
					</div>

					<div class="hr hr-18 hr-double dotted"></div>
					
					<div class="row">
						<div class="col-sm-12">
							<?php 
								$this->widget('zii.widgets.CListView', array(
									'id'=>'docsList',
									'dataProvider'=>$docsDataProvider,
									'itemView'=>'_doc_list',
									'template'=>'<table id="docummentsTbl" class="table table-bordered table-condensed table-hover">{items}</table>',
								)); 
							?>

							<div id="document-sources">
								<div class="document-filelist"></div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-sm-6">
					<div class="widget-box">
						<div class="widget-body">
							<div class="widget-main">
								
								Notes and Records
								
								<form id="employeeNotesForm">
									<div class="row">
										<div class="col-sm-12">
											<textarea id="employeeNotesTextArea" name="AccountUserNote[content]" class="col-sm-12" style="height:110px;"></textarea>
										</div>
									</div>
									
									<span id="sources">
										<span class="filelist"></span>
									</span>
									
									<div class="hr hr-18 hr-double dotted"></div>
									
									<div class="row">
										<div class="col-sm-2">
											<?php 
												if( Yii::app()->user->account->checkPermission('employees_employee_file_export_history_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_export_history_button','only_for_direct_reports') )
												{
													echo CHtml::link('<i class="fa fa-share"></i> Export History', array('accountUser/employeeExportHistory', 'id'=>$accountUser->id), array('class'=>'btn btn-yellow btn-xs'));
												}
											?>
										</div>
										
										<div class="col-sm-10 text-right">
											<?php
												//echo CHtml::dropDownList('AccountUserNote[type]', '', AccountUserNote::noteTypeOptions(), array('id'=>'noteTypeSelect', 'prompt'=>'- TYPE -')); 
											?>
											
											<?php
												echo CHtml::dropDownList('AccountUserNote[category_id]', '', AccountUserNote::noteCategoryOptions(), array('id'=>'noteCategorySelect', 'prompt'=>'- CATEGORY -')); 
											?>
											
											<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_attachments_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_attachments_button','only_for_direct_reports') ){ ?>
												<button type="button" id="plupload-select-files" class="btn btn-success btn-xs">Initializing uploader, please wait...</button>
											<?php } ?>
											
											<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_submit_button','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_submit_button','only_for_direct_reports') ){ ?>
												<button type="button" class="btn btn-primary btn-xs employee-notes-submit-btn">Submit</button>				
											<?php } ?>
										</div>
									</div>
								</form>
								
								<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_view_history_list','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_view_history_list','only_for_direct_reports') ){ ?>
									<div class="hr hr-18 hr-double dotted"></div>
									
									<div class="row">
										<div class="col-sm-12">
											<?php 
												$this->widget('zii.widgets.CListView', array(
													'id'=>'notesList',
													'dataProvider'=>$notesDataProvider,
													'itemView'=>'_note_list',
													'template'=>'<div class="profile-feed" style="height:300px; overflow:auto;">{items}</div>',
												)); 
											?>
										</div>
									</div>
								<?php } ?>
								
							</div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>