<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/setup_customer_tiers.js',CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/tier.css'); ?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>

<?php 
	Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END);
 ?>

<?php Yii::app()->clientScript->registerScript(uniqid(), "
	
		var customer_name = '".addslashes($model->getFullName())."';
	",CClientScript::POS_END);
	

?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/recorder.js?time='.time(),CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/jquery.voice.min.js?time='.time(),CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/record.js?time='.time(),CClientScript::POS_END); ?>

<style>
      .button{
        display: inline-block;
        vertical-align: middle;
        margin: 0px 5px;
        padding: 5px 12px;
        cursor: pointer;
        outline: none;
        font-size: 13px;
        text-decoration: none !important;
        text-align: center;
        color:#fff;
        background-color: #4D90FE;
        background-image: linear-gradient(top,#4D90FE, #4787ED);
        background-image: -ms-linear-gradient(top,#4D90FE, #4787ED);
        background-image: -o-linear-gradient(top,#4D90FE, #4787ED);
        background-image: linear-gradient(top,#4D90FE, #4787ED);
        border: 1px solid #4787ED;
        box-shadow: 0 1px 3px #BFBFBF;
      }
      a.button{
        color: #fff;
      }
      .button:hover{
        box-shadow: inset 0px 1px 1px #8C8C8C;
      }
      .button.disabled{
        box-shadow:none;
        opacity:0.7;
      }
	  
	  .profile-info-name{ width: 130px;}
	  select{width:300px}
      </style>	
	  
<?php 
	Yii::app()->clientScript->registerScript('getPhoneTimeZone','
		var timer;
		$(".phone-timezone").on("keyup",function(){
			
			var phone_number = $(this).val();
			
			var $timeZoneDropDown = $("#'.CHtml::activeId($model, '_phone_timezone_list').'");
			$timeZoneDropDown.empty();
			$timeZoneDropDown.append("<option value=\"\">-No time zone matched-</option>");
					
			clearTimeout(timer);
			timer = setTimeout(function() { 
			  
				$.ajax({
					url: "'.Yii::app()->createUrl('/customer/data/checkPhoneTimeZone').'",
					data: {"phone_number"  : phone_number},
					dataType : "json",
					method : "GET",
				}).success(function(result) {
					
					$("#'.CHtml::activeId($model,'_phone_timezone_list').'").prop("disabled",false);
					
					// if(result.selected == null)
						// $("#'.CHtml::activeId($model,'_phone_timezone_list').'").prop("disabled",false);
					// else
					// {
						// $("#'.CHtml::activeId($model,'_phone_timezone_list').'").prop("disabled",true);
					// }
						
					$.each(result.items, function( key, item )  {
						
						// if(key == result.selected)
							// $timeZoneDropDown.append("<option value=" + key + " selected>" + item + "</option>");
						// else if(key == "'.$model->phone_timezone.'")
						// {
							// $timeZoneDropDown.append("<option value=" + key + " selected>" + item + "</option>");
						// }
						// else
							// $timeZoneDropDown.append("<option value=" + key + ">" + item + "</option>");
						
						if(key == "'.$model->phone_timezone.'")
						{
							$timeZoneDropDown.append("<option value=" + key + " selected>" + item + "</option>");
						}
						else
						{
							$timeZoneDropDown.append("<option value=" + key + ">" + item + "</option>");
						}
					});
					
					
					$("#'.CHtml::activeId($model,'phone_timezone').'").val($timeZoneDropDown.val());
					
				});
			}, 500);
		});
		
		
		$("#'.CHtml::activeId($model,'_phone_timezone_list').'").on("change",function(){
			var thisVal = $(this).val();
			$("#'.CHtml::activeId($model,'phone_timezone').'").val(thisVal);
		});
		
		$(".phone-timezone").trigger("keyup");
	',CClientScript::POS_END);
?>

<?php 
Yii::app()->clientScript->registerScript('companyTierJs','
	$("#'.CHtml::activeId($model, 'company_id').'").on("change",function(){

		
		
		var tierObject = $("#'.CHtml::activeId($model, 'tier_id').'");
		
		//tierObject.empty();
		//tierObject.append("<option value=\"\">No tiers available.</option>");
		
		var tierObjectHidden = $("#'.CHtml::activeId($model, 'tier_id').'");
		
		
		$("#'.CHtml::activeId($model,'tier_id').'").val(null);
		$("#tier-btn-container").html("No tier selected.");
		
		
	});
	
	// $("#'.CHtml::activeId($model, 'company_id').'").trigger("change");
	
	$("#selectCompanyTier").on("click",function(){
		
		var thisVal = $("#'.CHtml::activeId($model, 'company_id').'").val();
		var thisText = $("#'.CHtml::activeId($model, 'company_id').'").find("option:selected").text();
		
		if(thisVal != "")
		{
			
			//tierObject.prop("disabled",false);
			
			$.ajax({
				// url: "'.Yii::app()->createUrl("customer/data/tiersJsonSearch").'",
				url: "'.Yii::app()->createUrl("customer/data/tiersModalSearch").'",
				data: {"companyId": thisVal},
				method: "GET",
				dataType: "html",
			}).success(function(result) {
				
				// tierObject.empty();
				
				// if(result.length > 0)
					// tierObject.append("<option value=\"\">-Select Tier-</option>");
				// else
					// tierObject.append("<option value=\"\">No tiers available.</option>");
				
				// $.each(result, function( key, obj )  {
					
					// if(obj.id == "'.$model->tier_id.'")
						// tierObject.append("<option value=" + obj.id + " selected>" + obj.tier_name + "</option>");
					// else
						// tierObject.append("<option value=" + obj.id + ">" + obj.tier_name + "</option>");
				// });
				
				$("#myModal #myModalLabel").html(thisText+" - Select Tiers");
				$("#myModal .modal-body").html(result);
			});
		}
		else
		{
			//tierObject.prop("disabled",true);
		}
		
		$("#myModal").modal();
	});
	
	
	$("body").on("click", ".select-tier" ,function(){
		var selectedId = $(this).prop("id");
		var selectedLabel = $(this).parent().find(".tree-label").html();
		
		$("#'.CHtml::activeId($model,'tier_id').'").val(selectedId);
		$("#tier-btn-container").html(selectedLabel);
		$("#myModal").modal("hide");
	});
	
',CClientScript::POS_END); 

	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerCss(uniqid(), '
		.redactor-toolbar {
			background: #438EB9;
			box-shadow: none;
		}
		.redactor-toolbar li a {
			color: rgba(255, 255, 255, .55);
		}
		.redactor-toolbar li a:hover {
			background: #2C5976;
			color: #fff;
		}
		
		.tab-content { overflow:hidden !important; }
		
		span.filename > a { text-decoration:none; }
	');
	
	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/profileFileupload.js');
	// $cs->registerScriptFile($baseUrl . '/js/voiceFileupload.js');
	$cs->registerScript(uniqid(), "
	
		var event_id = '".$model->id."';
	",CClientScript::POS_END);
	
	Yii::import('ext.redactor.ImperaviRedactorWidget');
	
	$this->widget('ImperaviRedactorWidget',array(
		'selector' => '.redactor',
		'plugins' => array(
			// 'fontfamily' => array('js' => array('fontfamily.js')),
			'fontcolor' => array('js' => array('fontcolor.js')),
			'fontsize' => array('js' => array('fontsize.js')),
			'table' => array('js' => array('table.js')),
		),
		'options' => array(
			'buttons'=>array(
				'formatting', '|', 'bold', 'italic', 'deleted', 'alignment', '|', 'fontsize', 'fontcolor', '|',
				'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
				'link', '|', 'table', '|', 'html'
			),
		)
	));
	
	$inputDisabled = !Yii::app()->user->account->checkPermission('customer_setup_all_fields','edit') ? true : false;
	$salesRepDisabled = !Yii::app()->user->account->checkPermission('customer_setup_sales_rep_dropdown','edit') ? true : false;
	$notesDisabled = !Yii::app()->user->account->checkPermission('customer_setup_customer_notes_field','edit') ? true : false;
?>


<!--- MODAL --->


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
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

<!-- END OF MODAL -->


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'customer-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array(
		'class'=> 'form-horizontal',
		'enctype' => 'multipart/form-data',
	),
)); ?>

	<div class="row">
		<div class="col-md-7">

			<?php echo $form->errorSummary($model); ?>
			
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
			
			<?php 
				if( Yii::app()->user->account->checkPermission('customer_setup_all_fields','visible') )
				{ 
				?>
			
				<?php 
					if( !$model->isNewRecord )
					{
					?>
					
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name ">Photo</div>
								
								<div class="profile-info-value">
									<div class="row">
										<div class="row">
											<div id="photo-container" class="col-sm-1 center">
												<?php 
													if($model->getImage())
													{
														echo CHtml::image($model->getImage(), '', array('style'=>'/* width:100%; */'));
													}
													else
													{
														echo 'No image uploaded yet.'; 
													}
												?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-sm-1 center">
												<?php if(!$model->isNewRecord){ ?>
													
													<?php if( $inputDisabled ){ ?>
																
														<div class="padded" style="margin: 10px 0;">
															<div style="margin-top: 7px;">		
																<div>
																	<a class="btn btn-mini btn-primary bigger disabled" href="#"> 
																		<i class="icon-plus"></i>
																		Attach a File
																	</a>
																</div>
																
																<br style="clear:both;">
															</div>
														</div>
														
														<?php } ?>

														<div id="sources" class="padded" style="margin: 10px 0;<?php echo $inputDisabled ? 'display:none;' : '';?>">
															<div style="margin-top: 7px;" id="uploadUserGuide">		
																<div>
																	<a id="plupload-select-files" class="btn btn-mini btn-primary bigger" href="#"> 
																		<i class="icon-plus"></i>
																		Initializing uploader, please wait...
																	</a>
																</div>
																
																<br style="clear:both;">
															</div>

															<div class="filelist"> </div>
														</div>
				
													<?php //echo CHtml::activeFileField($fileupload, 'original_filename'); ?>
													<?php //echo $form->error($fileupload,'original_filename'); ?>
													<?php //echo CHtml::link("Change", array('/customer/data/fileupload','id'=>$model->id)); ?>
												<?php } ?>
											</div>
											
											
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name ">Voice</div>
								
								<div class="profile-info-value">
									<div class="row">
										<div class="row">
											<div id="voice-container" class="col-sm-6 center">
												<?php 
													if($model->getVoice())
													{
														echo '<audio controls src="'.$model->getVoice().'" id="audio"></audio>';
													}
													else
													{
														echo 'No voice uploaded yet.'; 
													}
												?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-sm-1 center">
												<?php if(!$model->isNewRecord){ ?>
													
													<?php if( $inputDisabled ){ ?>
														<a class="btn btn-xs btn-success disabled" title="Please grant your browser access to your microphone when asked">Record</a>
													<?php }else{ ?>
														<a class="btn btn-xs btn-success" id="record" title="Please grant your browser access to your microphone when asked">Record</a>
													<?php } ?>
													
													<a class="hidden button one" id="download">Download</a>
													
													<?php /*
														<div id="voice-sources" class="padded" style="margin: 10px 0;">
														<div style="margin-top: 7px;" id="uploadUserGuide">		
															<div>
																<a id="plupload-select-voice" class="btn btn-mini btn-primary bigger" href="#"> 
																	<i class="icon-plus"></i>
																	Initializing uploader, please wait...
																</a>
															</div>
															
															<br style="clear:both;">
														</div>

														<div class="filelist"> </div>
													</div>
													*/?>
													<?php //echo CHtml::activeFileField($fileupload, 'original_filename'); ?>
													<?php //echo $form->error($fileupload,'original_filename'); ?>
													<?php //echo CHtml::link("Change", array('/customer/data/fileupload','id'=>$model->id)); ?>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php
					}
				?>
			
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'status'); ?></div>
							<div class="profile-info-value">

								<?php //echo $form->dropDownList($model,'status',Customer::listStatus(), array('disabled' => Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff() || $inputDisabled)); ?>
								
								<?php echo $form->dropDownList($model,'status',Customer::listStatus(), array('disabled' => true )); ?>
								
								<?php echo $form->error($model,'status'); ?>
							</div>
						</div>
					</div>
					
					
					
					<?php if( (!Yii::app()->user->account->getIsCustomer()) && FALSE){ ?>
					<?php 
					/* 
					b.	Company is hidden from Customer view
					c.	Tier is hidden from Customer View
					d.	Agent ID is hidden from Customer View
					*/
					?>
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name "><?php echo $form->labelEx($model,'company_id'); ?></div>
								<div class="profile-info-value">
									<?php echo $form->dropDownList($model,'company_id',Company::listCompanies(),array('empty'=>'-Select Company-', 'disabled' => Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff() || $inputDisabled )); ?>
									<?php echo $form->error($model,'company_id'); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name "><?php echo $form->labelEx($model,'tier_id'); ?></div>
								<div class="profile-info-value">
									<?php echo $form->hiddenField($model,'tier_id'); ?>
									
									<span id="tier-btn-container">
										<?php echo isset($model->tier) ? $model->tier->tier_name : 'No tier selected.'; ?>
									</span>
									&nbsp;&nbsp;
									
									<?php 
										if( $inputDisabled )
										{
											echo CHtml::button('Select Tier',array('class'=>'btn btn-xs btn-info disabled'));
										}
										else
										{
											echo CHtml::button('Select Tier',array('id'=>'selectCompanyTier', 'class'=>'btn btn-xs btn-info'));
										}
									?>
									
									<?php //echo $form->dropDownList($model, 'tier_id', array(), array('empty'=>'-Select Tier-','disabled'=> true)); ?>
									<?php //echo $form->error($customer,'tier_id'); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name "><?php echo $form->labelEx($model,'account_number'); ?></div>
								<div class="profile-info-value">
									<?php echo $form->textField($model,'account_number',array('disabled'=>true, 'size'=>'40',)); ?>
									<?php echo $form->error($model,'account_number'); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name "><?php echo $form->labelEx($model,'custom_customer_id'); ?></div>
								<div class="profile-info-value">
									<?php echo $form->textField($model,'custom_customer_id',array('maxlength'=>10, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
									<?php echo $form->error($model,'custom_customer_id'); ?>
								</div>
							</div>
						</div>
					
					<?php } ?>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'firstname'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'firstname',array('maxlength'=>120, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'firstname'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'lastname'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'lastname',array('maxlength'=>120, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'lastname'); ?>
							</div>
						</div>
					</div>

					<?php /*
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'name_alias'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'name_alias',array('maxlength'=>120, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'name_alias'); ?>
							</div>
						</div>
					</div>
					
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'gender'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->radioButtonList($model,'gender',array('Male' => 'Male', 'Female' => 'Female'), array('separator'=>' ','template'=>'{input} {label}','labelOptions'=>array('style'=>'display:inline-block;'), 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'gender'); ?>
							</div>
						</div>
					</div> */
					?>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'phone'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'phone',array('maxlength'=>60, 'class'=>'input-mask-phone phone-timezone', 'disabled'=>$inputDisabled)); ?>
								
								<?php $model->_phone_timezone_list = $model->phone_timezone; //para readable dito nalang :) ?>
								<?php echo $form->dropDownList($model, '_phone_timezone_list', AreacodeTimezoneLookup::items(), array('empty' =>'-No time zone matched-','disabled'=>true)); ?>
								<?php echo $form->hiddenField($model,'phone_timezone'); ?>
								<?php echo $form->error($model,'phone'); ?>
								<?php echo $form->error($model,'phone_timezone'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'fax'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'fax',array('maxlength'=>60, 'class'=>'input-mask-phone', 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'fax'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'mobile'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'mobile',array('maxlength'=>60, 'class'=>'input-mask-phone', 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'mobile'); ?>
							</div>
						</div>
					</div>
				<?php 
				}
			?>
			
			<?php if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() ){ ?>
			
				<?php if( Yii::app()->user->account->checkPermission('customer_setup_sales_rep_dropdown','visible') ){ ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo CHtml::label('Sales Reps',null); ?></div>
							<div class="profile-info-value">
								<?php echo CHtml::dropDownList('Customer[salesRepIds]', $selectedSalesReps, AccountUser::listSalesAgents(), array('class'=>'select2','multiple'=>true, 'disabled'=>$salesRepDisabled) ); ?>
							</div>
						</div>
					</div>
					
				<?php } ?>
			
			<?php } ?>
				
			<?php 
				if( Yii::app()->user->account->checkPermission('customer_setup_all_fields','visible') ) 
				{
				?>
				
					<?php if(!$model->isNewRecord){ ?>
						<?php if(isset($model->account) && !empty($model->account->username) ){ ?>
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name "><?php echo CHtml::label($model->account->getAttributeLabel('username'),''); ?></div>
								<div class="profile-info-value">
									<?php echo $form->textField($model->account,'username',array('maxlength'=>128, 'disabled'=>$inputDisabled)); ?>
								</div>
							</div>
						</div>
						
							<?php if( $model->account->login_attempt > 5 ){ ?>
							<div class="profile-user-info profile-user-info-striped">
								<div class="profile-info-row">
									<div class="profile-info-name "><label>Lock Reset</label></div>
									<div class="profile-info-value">
										<?php echo CHtml::link('Release Lock',array('data/releaseLock','id'=>$model->id),array('class' => 'btn btn-default btn-xs')); ?>
									</div>
								</div>
							</div>
							
							<?php } ?>
						<?php }else{ ?>

							<?php if( !in_array(Yii::app()->user->account->id, array(Yii::app()->user->account->getIsCustomer(), Yii::app()->user->account->getIsCustomerOfficeStaff(), Yii::app()->user->account->getIsCompany())) ){ ?>
							<div class="profile-user-info profile-user-info-striped">
								<div class="profile-info-row">
									<div class="profile-info-name ">Account Setup</div>
									<div class="profile-info-value">
										<?php echo CHtml::link('Resend Customer Email',array('data/regenerateToken','id'=>$model->id),array('class' => 'btn btn-default btn-xs')); ?>
									</div>
								</div>
							</div>
							<?php } ?>

						<?php } ?>
					<?php } ?>
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'email_address'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'email_address',array('maxlength'=>128, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'email_address'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'address1'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'address1',array('maxlength'=>250, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'address1'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'address2'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'address2',array('maxlength'=>250, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'address2'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'city'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'city',array('maxlength'=>64, 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'city'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'state'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->dropDownList($model,'state',State::listStates(),array('empty'=>'-Select State-', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'state'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'zip'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'zip',array('size'=>12,'maxlength'=>12, 'class'=>'input-mask-zip', 'size'=>'40', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'zip'); ?>
							</div>
						</div>
					</div>
					
				<?php
				}
			?>
			
			<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_AGENT, Account::TYPE_COMPANY)) && Yii::app()->user->account->checkPermission('customer_setup_customer_notes_field','visible') ){ ?>
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name "><?php echo $form->labelEx($model,'notes'); ?></div>
						<div class="profile-info-value">
							<?php 
								if( $notesDisabled )
								{ 
									echo $model->notes;
								}
								else
								{
									echo $form->textArea($model,'notes', array('class'=>'form-control redactor','style'=>'min-height:120px;')); 
								}
							?>
							<?php echo $form->error($model,'notes'); ?>
						</div>
					</div>
				</div>
			<?php } ?>
			
			<?php /* COLUMNS MOVED TO CALENDAR SETUP
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'direction'); ?></div>
					<div class="profile-info-value">
						<?php echo $form->textArea($model,'direction', array('class'=>'form-control','style'=>	'min-height:120px;')); ?>
						<?php echo $form->error($model,'direction'); ?>
					</div>
				</div>
			</div>
			
			<div class="profile-user-info profile-user-info-striped">
				<div class="profile-info-row">
					<div class="profile-info-name "><?php echo $form->labelEx($model,'landmark'); ?></div>
					<div class="profile-info-value">
						<?php echo $form->textArea($model,'landmark', array('class'=>'form-control','style'=>'min-height:120px;')); ?>
						<?php echo $form->error($model,'landmark'); ?>
					</div>
				</div>
			</div>
			*/ ?>
			
			<?php if( Yii::app()->user->account->checkPermission('customer_setup_customer_save_button','visible') ){ ?>
			
			<div class="form-actions buttons center">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
			</div>
			
			<?php } ?>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->

