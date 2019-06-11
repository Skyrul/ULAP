<?php 
	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerCss(uniqid(), '
		.profile-info-name { width:150px !important; } 
		.profile-user-info { width:calc(100%) !important; }
		
		.percentage { font-size:12px; font-weight:normal; }
		.profile-info-name { width:150px !important; } 
		.profile-user-info { width:calc(100%) !important; }
	');

	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/leads/single_lead_list_uploader.js');
	
	$cs->registerScript(uniqid(), '
	
		$(".date-picker").datepicker({	 
			autoclose: true,
			todayHighlight: true
		});

		$(document).ready( function(){
				
			$(document).on("click", ".create-list-submit-btn", function(){
				
				var errors = "";
				
				if( $("#Lists_name").val() == "" )
				{
					errors += "List name is required \n \n";
				}
				
				if( $("#Lists_skill_id").val() == "" )
				{
					errors += "Skill is required \n \n";
				}
					
				//if( $("#Lists_calendar_id").val() == "" )
				//{
				//	errors += "Calendar is required \n \n";
				//}
				
				if( errors != "" )
				{
					alert(errors);
				}
				else
				{
					$(this).prop("disabled", true);	
					
					if( $(".computer-upload").length > 0 )
					{
						$(this).html("Importing Leads Please Wait...");
					}
					else
					{
						$(this).html("Saving Please Wait...");
					}
					
					$("form").submit();
				}
				
			});
			
		});
	
		
	
	', CClientScript::POS_END);
	
?>

<?php 
	Yii::app()->clientScript->registerScript('customer-file-js','
		
		$(".select-customerFile").on("click",function(){
			$.ajax({
				url: yii.urls.absoluteUrl + "/hostDial/lists/ajaxListCustomerFile/",
				type: "GET",	
				data: { 
					"customer_id" : "'.$customer_id.'"			
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "My Files";
					$("#myModalMd #myModalLabel").html(header);
					$("#myModalMd .modal-body").html(r);
					$("#myModalMd").modal();
					
				},
			});
		});
		
		$("body").on("click", ".selected-customer-file",function(){
			
			var fileContainer = $(".upload-info").parent();
			var fileContainerId = fileContainer.prop("id");
			
			if (uploader)
			{
				var file = uploader.getFile(fileContainerId);
				
				if (typeof(file) != "undefined")
				{
					if (file.status == 2) {
						uploader.stop();
					}

					uploader.removeFile(file);
				}
					
				fileContainer.fadeOut(500, function() {
					$(this).remove();
					
					
				});
			
			}
			else
			{
				fileContainer.fadeOut(500, function() {
					$(this).remove();
				});
			}
			
			var fileUploadId = $(this).data("fileupload_id");
			var fileUploadTitle = $(this).data("fileupload_title");
			
			$(".customer-filelist").removeClass("hidden");
			$(".customer-filename").html(fileUploadTitle).attr("title",fileUploadTitle);
			
			$(".customer-filelist").append("<input type=\"hidden\" name=\"fileUploadId\" value=\""+fileUploadId+"\">");
			
			$("#myModalMd").modal("toggle");
		});
		
		$("body").on("click",".remove-customer-file-link", function(){
			
			$(".customer-filelist").addClass("hidden");
			$(".customer-filename").html("").attr("title","");
			
			$(".customer-filelist").find("input").remove();
		});
	',CClientScript::POS_END);
?>

<?php 
	$cs->registerScript(uniqid(),'
		
		$("#Lists_skill_id").on("change",function(){
		
			skill_id = $(this).val();
			
			if( skill_id != "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/lists/ajaxCheckCustomMapping/",
					type: "POST",	
					dataType: "json",
					data: { "ajax":1, "skill_id" : skill_id },
					success: function( response ){		
						
						if( response.enable_list_custom_mapping == 1 )
						{
							$(".checkbox-custom-field-container").fadeIn();
						}
						else
						{
							$(".checkbox-custom-field-container").hide();
						}
						
						if( response.enable_specific_date_calling == 1 )
						{
							$("#Lists_lead_ordering").append("<option value=\"5\">Specific Date</option>");
						}
						else
						{
							$("#Lists_lead_ordering option[value=\"5\"]").remove();
						}
					
					},
				});
			}
			else
			{
				$("#Lists_allow_custom_fields").prop("checked", false);
				$(".checkbox-custom-field-container").hide();
			}
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

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		<?php if(!$simpleView): ?>
		
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'name'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->textField($model,'name',array('class'=>'form-control')); ?>
					
					<?php echo $form->error($model,'name'); ?>
				</div>
			</div>
		</div>
												
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'status'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->dropDownList($model,'status', Lists::getStatusOptions(), array('class'=>'form-control')); ?>
					
					<?php echo $form->error($model,'status'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'skill_id'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->dropDownList($model,'skill_id', CustomerSkill::items($customer_id), array('class'=>'form-control', 'style'=>'')); ?>
					
					<?php echo $form->error($model,'skill_id'); ?>
				</div>
			</div>
		</div>
		
		
		<?php //if($model->skill->enable_survey_tab == 1): ?>
		

		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"><?php echo $form->labelEx($model,'survey_id'); ?></div>

				<div class="profile-info-value">
					<?php //echo $form->dropDownList($model, 'survey_id', Survey::items($model->skill_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
					<?php echo $form->dropDownList($model, 'survey_id', array(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
				</div>
			</div>
		</div>
		
										
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'dialing_as_number'); ?></div>
				<div class="profile-info-value">

					<?php 
						$dialingAsOptions = array(
							1 => 'Dial As Office Phone number'
						);
						
						$hostManagerPhones = CustomerOfficeStaff::model()->findAll(array(
							'condition' => '
								customer_id = :customer_id
								AND use_phone_as_dial_as_option = 1
								AND phone IS NOT NULL
								AND phone != ""
							',
							'params' => array(
								':customer_id' => $customer_id
							)
						));
						
						if( $hostManagerPhones )
						{
							foreach( $hostManagerPhones as $hostManagerPhone )
							{
								$dialingAsOptions[$hostManagerPhone->id] = $hostManagerPhone->staff_name.' - '.$hostManagerPhone->phone;
							}
						}
					?>	
						
					<?php echo $form->dropDownList($model,'dialing_as_number', $dialingAsOptions, array('class'=>'form-control')); ?>
					
					<?php echo $form->error($model,'dialing_as_number'); ?>
				</div>
			</div>
		</div>
		
		<?php /*
		<div class="form-group">
			<div class="row">
				<div class="col-sm-3">
					<?php echo $form->labelEx($model,'calendar_id', array('class'=>'control-label no-padding-right')); ?>
				</div>
				
				<div class="col-sm-6">
					<?php echo $form->dropDownList($model,'calendar_id', Calendar::items($customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'')); ?>
					<?php echo $form->error($model,'calendar_id'); ?>
				</div>
			</div>
		</div>
		*/ ?>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'lead_ordering'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->dropDownList($model,'lead_ordering', $model->getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'')); ?>
					
					<?php echo $form->error($model,'lead_ordering'); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'language'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->dropDownList($model,'language', $model::getLanguageOptions(), array('class'=>'form-control', 'style'=>'')); ?>
					
					<?php echo $form->error($model,'language'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'number_of_dials_per_guest'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->textField($model,'number_of_dials_per_guest',array('class'=>'form-control')); ?>
					
					<?php echo $form->error($model,'number_of_dials_per_guest'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'start_date'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->textField($model,'start_date',array('class'=>'form-control date-picker')); ?>
					
					<?php echo $form->error($model,'start_date'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'end_date'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->textField($model,'end_date',array('class'=>'form-control date-picker')); ?>
					
					<?php echo $form->error($model,'end_date'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'is_default_call_schedule'); ?></div>
				<div class="profile-info-value">
					
					<?php echo $form->dropDownList($model,'is_default_call_schedule',array('1'=>'Default Call Schedule','0'=>'Custom Call Schedule'), array('class'=>'form-control',)); ?>
					
					<?php echo CHtml::link('Edit Custom Schedule', array('customerSkill/index', 'customer_id'=>$customer_id), array('class'=>'btn btn-minier btn-primary', 'style'=>'margin-top:8px;')); ?>
					
					<?php echo $form->error($model,'is_default_call_schedule'); ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name "><?php echo $form->labelEx($model,'time_zone_assignment'); ?></div>
				<div class="profile-info-value">
					
					<?php //echo $form->textField($model,'time_zone_assignment',array('class'=>'form-control')); ?>
					<?php echo $form->dropDownList($model,'time_zone_assignment',array('area'=>'By Area Code','zip_code'=>'By Zip Code'), array('class'=>'form-control',)); ?>
					
					
					
					<?php echo $form->error($model,'time_zone_assignment'); ?>
				</div>
			</div>
		</div>
		
		<?php /*
		<div class="form-group">
			<div class="row">
				<div class="col-sm-3">
				<?php echo $form->labelEx($model,'duplicate_action', array('class'=>'control-label no-padding-right')); ?>
				
				<?php echo $form->hiddenField($model, 'duplicate_action'); ?>
				</div>
				<div class="col-sm-6">
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::DUPLICATES_DO_NOT_IMPORT; ?>" <?php echo $model->duplicate_action == $model::DUPLICATES_DO_NOT_IMPORT ? 'checked' : ''; ?> >
							<span class="lbl"> Do not import</span>
						</label>
					</div>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::DUPLICATES_UPDATE_LEAD_INFO; ?>" <?php echo $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO ? 'checked' : ''; ?> >
							<span class="lbl"> Update lead info in database to match. Keep Call History</span>
						</label>
					</div>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS; ?>" <?php echo $model->duplicate_action == $model::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS ? 'checked' : ''; ?> >
							<span class="lbl"> Update lead info in database to match. Keep Call History. Reset Dial Count</span>
						</label>
					</div>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS; ?>" <?php echo $model->duplicate_action == $model::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS ? 'checked' : ''; ?> >
							<span class="lbl"> Move lead record to current list. Reset Dial Count</span>
						</label>
					</div>
					
					<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() || in_array(Yii::app()->user->account, array(1,2,3)) || (isset(Yii::app()->user->account->accountUser) && Yii::app()->user->account->accountUser->job_title == "Customer Service Agent") ): ?>
					 
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::CUSTOMER_SERVICE_OVERRIDE; ?>" <?php echo $model->duplicate_action == $model::CUSTOMER_SERVICE_OVERRIDE ? 'checked' : ''; ?> >
							<span class="lbl"> Move lead record to current list. Reset dial count and make callable for all leads except Do Not Call</span>
						</label>
					</div>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::CUSTOMER_SERVICE_ALLOW_DUPLICATES; ?>" <?php echo $model->duplicate_action == $model::CUSTOMER_SERVICE_ALLOW_DUPLICATES ? 'checked' : ''; ?> >
							<span class="lbl"> Allow Duplicates. Keep Call History. Reset Dial Count</span>
						</label>
					</div>
					
					<?php endif; ?>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST; ?>" <?php echo $model->duplicate_action == $model::MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST ? 'checked' : ''; ?> >
							<span class="lbl"> Import any leads that are currently recertifiable from other lists</span>
						</label>
					</div>
					
					<div class="radio">
						<label>
							<input type="radio" class="ace" name="Lists[duplicate_action]" value="<?php echo $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST; ?>" <?php echo $model->duplicate_action == $model::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST ? 'checked' : ''; ?> >
							<span class="lbl"> Import any leads that are currently recyclable from other lists</span>
						</label>
					</div>
				
					<?php echo $form->error($model,'duplicate_action'); ?>
				</div>
			</div>
		</div>
		
		
		<?php 
			$checkBoxCustomFieldStyle = 'none';
			
			if( !$model->isNewRecord && $model->skill->enable_list_custom_mapping == 1 ) 
			{
				$checkBoxCustomFieldStyle = '';
			}
			
			$checkBoxAreaCodeFieldStyle = 'none';
			
			if( $model->skill->enable_list_area_code_assignment == 0 )
			{
				$checkBoxAreaCodeFieldStyle = '';
			}
		?>
		
		<div class="form-group checkbox-custom-field-container" style="display:<?php echo $checkBoxCustomFieldStyle; ?>;">
			<div class="row">
				<div class="col-sm-3">
					<label for="form-field-1" class="control-label no-padding-right">Allow Custom Fields</label>
				</div>
				<div class="col-sm-6">
					<label style="margin:8px 0 0 8px;">
						<?php echo $form->checkBox($model,'allow_custom_fields', array('class'=>'ace')); ?>
						<span class="lbl"> </span>
					</label>
				</div>
			</div>
		</div>
		
		<div class="form-group checkbox-custom-field-container" style="display:<?php echo $checkBoxAreaCodeFieldStyle; ?>;">
			<div class="row">
				<div class="col-sm-3">
					<label for="form-field-1" class="control-label no-padding-right">Allow Area Code Assignment</label>
				</div>
				
				<div class="col-sm-6">
					<label style="margin:8px 0 0 8px;">
						<?php echo $form->checkBox($model,'allow_area_code_assignment', array('class'=>'ace')); ?>
						<span class="lbl"> </span>
					</label>
				</div>
			</div>
		</div>
		
		<?php //if(!$simpleView): ?>
		
		<div class="form-group">
			<div class="row">
				<div class="col-sm-3">
				<label for="form-field-1" class="control-label no-padding-right">Manually Enter</label>
				</div>
				
				<div class="col-sm-6">
					<label style="margin:8px 0 0 8px;">
						<?php echo $form->checkBox($model,'manually_enter', array('class'=>'ace')); ?>
						<span class="lbl"> </span>
					</label>
				</div>
			</div>
		</div>
		
		<?php //endif; ?>
		<?php */ ?>
		
		<?php /*
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name ">
				
					<label for="form-field-1" class="control-label no-padding-right">
						Import from leads waiting <small class="red">(<?php echo count($leadsWaiting); ?> Remaining)</small> 
						
						<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() ){ ?>
						
							<br />
							<?php echo CHtml::link('<i class="fa fa-times"></i> Delete', array('deleteNamesWaiting', 'customer_id'=>$customer_id), array('class'=>'btn btn-minier btn-danger', 'style'=>'margin-top:8px;', 'confirm'=>'Are you sure you want to delete this?')); ?>
						
						<?php } ?>
					</label>
					
				</div>
				<div class="profile-info-value">
				
					<label style="margin:8px 0 0 8px;">
						<?php echo CHtml::checkBox('import_from_leads_waiting', false, array('class'=>'ace')); ?>
						<span class="lbl"> </span>
					</label>
					
				</div>
			</div>
		</div>
		
		<br /><div class="hr hr32 hr-dotted"></div><br />
		*/ ?>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-value">
					
					<div class="row">
						<div class="col-sm-3">
							<span id="sources">	
								<a id="plupload-select-files" class="btn btn-info btn-minier" href="#"> 
									<i class="fa fa-file"></i>
									Initializing uploader, please wait...
								</a>

								<span class="filelist"> </span>
							</span>
						</div>
						
						<div class="col-sm-3">
							<?php echo CHtml::link('My Files', 'javascript:void(0)', array('class'=>'select-customerFile btn btn-xs btn-success','style'=>'line-height:12px;')); ?>
							
							<div class="customer-filelist hidden">
								<div class="progress progress-small progress-striped active">
									<div class="progress-bar" style="width: 100%;"></div>
								</div>
								<a class="remove-customer-file-link" href="#"><i class="icon-remove"></i> Remove</a>							
								<div class="upload-info-filename" id="tag_field_p1aokmm0ei1vtrbu19ipith13463">	
									<span title="" class="customer-filename" style="color: rgb(0, 128, 0);"></span>										
								</div>
								<div class="clear"></div>
							</div>
						</div>
						
						<div class="col-sm-3">
							<?php echo CHtml::link('Download list template', array('downloadStandardTemplate'), array('class'=>'btn btn-warning btn-xs')); ?>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		<div class="form-actions text-center">
			<button type="button" class="btn btn-xs btn-primary create-list-submit-btn">Save <i class="fa fa-arrow-right"></i></button>
		</div>

	<?php $this->endWidget(); ?>
</div>


<?php Yii::app()->clientScript->registerScript('list-add-survey','

	$("body").on("change", "#'.CHtml::activeId($model,'skill_id').'", function(){
	
		thisVal = $(this).val();
		
		var options = $("#'.CHtml::activeId($model,'survey_id').'");
		options.empty();
		options.append($("<option />").val("").text("No Skill Selected"));
		
		
		$("#viewSurvey").hide();
		$("#viewSurveyPDF").hide();
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/hostDial/lists/getSurveyBySkill').'",
			method: "GET",
			dataType: "json",
			data: {				  
				"skill_id" : thisVal,				
				"customer_id" : "'.$customer_id.'"				  
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
			}
			
		});
	});
	
	$( "#'.CHtml::activeId($model,'skill_id').'" ).trigger( "change" );
	
	
',CClientScript::POS_END); 	


?>