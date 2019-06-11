<?php 

$customerName = str_replace('"', '',$customer->getFullName());

?>


<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>
<?php Yii::app()->clientScript->registerScript('addSkillsJs','

	$("#add-skill-btn").on("click",function(){
		$.ajax({
			url: yii.urls.absoluteUrl + "/customer/customerSkill/ajaxAddSkill",
			type: "GET",	
			data: { 
				"customer_id" : '.$customer->id.',
				
			},
			beforeSend: function(){
				$("#div_ajaxLoader").empty().append("Loading...");
			},
			complete: function(){
				tierAjaxSending = false;
				$("#div_ajaxLoader").empty();
			},
			error: function(){
				$("#div_ajaxLoader").empty().append("Error...");
			},
			success: function(r){
				$("#myModal #myModalLabel").html("'.$customerName.' - Add Skills");
				$("#myModal .modal-body").html(r);
				$("#myModal").modal();
			},
		});
	});
	
	$(document).on("click", ".remove-skill", function(){
		var thisUrl = $(this).prop("href");
		
		if( confirm("Are you sure you want to remove this skill?") )
		{
			$.ajax({
				url: thisUrl,
				type: "get",
				dataType: "json",
				success: function(response) { 
					
					if( response.status == "success" )
					{
						alert(response.message)
						location.reload(true);
					}
					else
					{
						alert(response.message);
					}
					
				},
			});
		}
		
		return false;
	});
	
',CClientScript::POS_END);

?>

<?php Yii::app()->clientScript->registerScript('toggleChildSkillSwitch','
	$(document).on("change", ".toggle-skill-child", function(){
				
		skillChildId = $(this).val();	
		customerSkillId = $(this).parent().find(".customerSkill-id").val();
		
		if( $(this).is(":checked") == false )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleSkillChild",
				data: {
					"boolType" : 0, 
					"skill_child_id" : skillChildId, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});

		}
		else
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleSkillChild",
				data: {
					"boolType" : 1, 
					"skill_child_id" : skillChildId, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});
			
		}
		
	});
',CClientScript::POS_END);
?>
				
<?php Yii::app()->clientScript->registerScript('toggleCallScheduleSwitch','
	$(document).on("change", ".toggle-custom-call-schedule", function(){
				
		var customerSkillId = $(this).val();
		
		if( $(this).is(":checked") == false )
		{
			$(this).parent().parent().find(".custom-call-schedule-container").hide();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillIsCustomSchedule",
				data: {
					"boolType" : 0, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});
		}
		else
		{
			alert("Please note that by selecting a custom schedule you are disabling logic that is designed to call leads at different times of the day.  This may lead to decreased performance and non-contact of leads.");
			
			$(this).parent().parent().find(".custom-call-schedule-container").show();
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillIsCustomSchedule",
				data: {
					"boolType" : 1, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});
		}
		
	});
	
',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('toggleIsContractHoldSwitch','
	$(document).on("change", ".toggle-contract-hold", function(){
				
		var customerSkillId = $(this).val();
		
		if( $(this).is(":checked") == false )
		{
			$(this).parent().parent().find(".contract-hold-container").hide();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillIsContractHold",
				data: {
					"boolType" : 0, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});
		}
		else
		{
			$(this).parent().parent().find(".contract-hold-container").show();
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillIsContractHold",
				data: {
					"boolType" : 1, 
					"customer_skill_id" : customerSkillId, 
					"customer_id": "'.$customer->id.'"
				},
			}).success(function(result) {
				
			});
		}
		
	});
	
',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('btn-add-schedule-js','
		var ctr = 0;
		$(".btn-add-schedule").on("click",function(){
			
			var dayVal = $(this).data("day");		
			var containerObj = $(this).parent().parent().parent().parent().parent().find(".new-schedule-container");
			$.ajax({
				url: "'.Yii::app()->createUrl('/customer/customerSkill/addNewSchedule').'",
				method: "GET",
				data: {
				  "day" : dayVal,					  
				  "ctr" : ctr,					  
				}
			}).success(function(response) {
				containerObj.append(response);
				
				ctr++;
			});
			
			
		});
		
		$("body").on("click",".btn-remove-sched",function(){
			var containerObj = $(this).parent().parent().remove();
			
		});
	',CClientScript::POS_END); 
	
?>

<?php Yii::app()->clientScript->registerScript('customerSkill-AddSKillAjax','
		$("body").on("change", ".customerSkill-skill-dropdown", function(){
			
			thisVal = $(this).val();
			
			$.ajax({
				url: "'.Yii::app()->createUrl('/customer/customerSkill/getContractByCompanyAndSkill').'",
				method: "GET",
				dataType: "json",
				data: {
				  "company_id" : "'.$customer->company_id.'",					  
				  "skill_id" : thisVal,					  
				}
			}).success(function(response) {
				
				var options = $(".customerSkill-contract-dropdown");
				options.empty();
				
				if (response.length === 0) {
					options.append($("<option />").val("").text("No Contract found"));
				}
				else{
					options.append($("<option />").val("").text("-Select Contract-"));
					$.each(response, function() {
						options.append($("<option />").val(this.id).text(this.contract_name));
					});
				}
				
			});
			
			
			
		});
		
	',CClientScript::POS_END); 
?>

<?php Yii::app()->clientScript->registerScript('toggle-customerSkill-skill-contract-level','
	$(document).on("change", ".toggle-skill-contract-level", function(){
				
		var groupId = $(this).val();
		var customerId = $(this).data("customer_id");
		var customerSkillId = $(this).data("customer_skill_id");
		var customerSkillContractId = $(this).data("customer_skill_contract_id");
		var quantityObj = $(this).parent().parent().parent().find(".skill-level-contract-level-quantity");
		
		if( $(this).is(":checked") == false )
		{
			$(this).parent().parent().parent().parent().find(".spinner").ace_spinner({ disabled : true });
			
			quantityObj.prop("disabled",true);
			
			$(this).parent().parent().find(".custom-call-schedule-container").hide();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillLevel",
				data: {
					"boolType" : 0,
					"contract_subsidy_level_group_id" : groupId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
					"customer_skill_contract_id": customerSkillContractId,
					"quantityVal": quantityObj.val(),
				},
			}).success(function(result) {
				
			});
		}
		else
		{
			quantityObj.prop("disabled",false);
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillLevel",
				data: {
					"boolType" : 1,
					"contract_subsidy_level_group_id" : groupId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
					"customer_skill_contract_id": customerSkillContractId,
					"quantityVal": quantityObj.val(),
				},
			}).success(function(result) {
				
			});
		}
		
	});
	
',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('toggle-customerSkill-skill-subsidy-level','
	$(document).on("change", ".toggle-skill-subsidy-level", function(){
				
		var subsidyLevelId = $(this).val();
		var customerId = $(this).data("customer_id");
		var customerSkillId = $(this).data("customer_skill_id");
		var type = $(this).data("type");
		
		if( $(this).is(":checked") == false )
		{
			$(this).parent().parent().find(".custom-call-schedule-container").hide();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillSubsidyLevel",
				data: {
					"boolType" : 0,
					"subsidy_level_id" : subsidyLevelId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
					"type": type
				},
			}).success(function(result) {
				
			});
		}
		else
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillSubsidyLevel",
				data: {
					"boolType" : 1,
					"subsidy_level_id" : subsidyLevelId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
					"type": type
				},
			}).success(function(result) {
				
			});
		}
		
	});
	
',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('toggle-customerSkill-skill-subsidy','
	$(document).on("change", ".toggle-skill-subsidy", function(){
				
		var subsidyId = $(this).val();
		var customerId = $(this).data("customer_id");
		var customerSkillId = $(this).data("customer_skill_id");
		
		if( $(this).is(":checked") == false )
		{
			$(this).parent().parent().parent().find(".js-tierSubsidy, .js-companySubsidy").prop("disabled",true);
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillSubsidy",
				data: {
					"boolType" : 0,
					"subsidy_id" : subsidyId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
				},
			}).success(function(result) {
				
			});
		}
		else
		{
			$(this).parent().parent().parent().find(".js-tierSubsidy, .js-companySubsidy").prop("disabled",false);
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerSkill/toggleCustomerSkillSubsidy",
				data: {
					"boolType" : 1,
					"subsidy_id" : subsidyId, 
					"customer_id": customerId,
					"customer_skill_id": customerSkillId,
				},
			}).success(function(result) {
				
			});
		}
		
	});
	
',CClientScript::POS_END);
?>


<?php Yii::app()->clientScript->registerScript('subsidy-levels-dropdown-change','

$(".js-tierSubsidy").on("change",function(){
	
	var thisVal = $(this).val();
	
	$(this).parent().parent().parent().parent().find(".tier-subsidy-level-entries").hide();
	$("#customer-skill-tier-subsidy-level-"+thisVal).show();
	
});

$(".js-companySubsidy").on("change",function(){
	
	var thisVal = $(this).val();
	var customerId = $(this).data("customer_id");
	var customerSkillId = $(this).data("customer_skill_id");
	
	$(this).parent().parent().parent().parent().find(".subsidy-level-entries").hide();
	$("#customer-skill-subsidy-level-"+thisVal).show();
	
	$.ajax({
		url: yii.urls.absoluteUrl + "/customer/customerSkill/customerContractSubsidy",
		data: {
			"subsidy_level_id" : thisVal, 
			"customer_id": customerId,
			"customer_skill_id": customerSkillId,
		},
	}).success(function(result) {
		
	});
});


',CClientScript::POS_END); ?>

<style>

select:disabled {
    cursor: not-allowed;
    background-color: #eee;
    opacity: 1;
}

.spinner-up, .spinner-down{ 
  font-size: 10px !important;
  height: 16px !important;
  line-height: 8px !important;
  margin-left: 0 !important;
  padding: 0 !important;
  width: 22px !important;
 }
</style>
<?php 
//php include
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/template_assets/js/fuelux/fuelux.spinner.min.js',  CClientScript::POS_END);

Yii::app()->clientScript->registerScript('txtfield-number-spinner','


	$(".number-field").on("keyup", function(){
		
		var qtyVal = $(this).val();
		
		$(this).parent().parent().parent().find(".toggle-skill-contract-level").trigger("change");
		
		multiplyObjs = $(this).parent().parent().find(".js-qty-to-multiply");
		
		multiplyObjs.each(function( index ) {
		  $(this).val( $(this).data("original-value") * qtyVal )
		});
	});

	
	 $(".number-field").trigger("change");
	 
	 
	 $(".number-field").keypress(function (e) {
		if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
				   return false;
		}
	});
	
',CClientScript::POS_END);


// Customer Extra js
Yii::app()->clientScript->registerScript(uniqid(), '

	function updateExtrasTable( table_element, customer_id, contract_id, skill_id )
	{
		$.ajax({
			url: yii.urls.absoluteUrl + "/customer/customerExtra/updateExtrasTable",
			type: "post",
			dataType: "json",
			data: { "ajax":1, "customer_id":customer_id, "contract_id":contract_id, "skill_id":skill_id },
			success: function(result){
				
				if( result.html != "" )
				{
					table_element.html(result.html); 
				}
			}
		});
	}

	$(document).ready( function() {
		
		$(document).on("click", ".btn-add-extra", function(){
			
			var this_button = $(this);
			
			var table_element = $(this).closest(".tab-pane").find(".extras-tbl > tbody");
			
			var data =  $(this).closest(".tab-pane").find("#addExtraForm").serialize() + "&ajax=1";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerExtra/create",
				type: "post",
				dataType: "json",
				data: data,
				beforeSend: function(){							
					this_button.prop("disabled", true);
					this_button.html("Saving Please Wait...");
				},
				success: function(result){
					
					if( result.status == "success" )
					{
						$("#addExtraForm")[0].reset();
						
						updateExtrasTable( table_element, result.customer_id, result.contract_id, result.skill_id );
					}
					else
					{
						alert(result.message);
					}
					
					this_button.prop("disabled", false);
					this_button.html("<i class=\"fa fa-plus\"></i> Add");
				}
			});
			
		});
		
		$(document).on("click", ".btn-edit-extra", function(){

			var table_element = $(this).closest(".tab-pane").find(".extras-tbl > tbody");
		
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerExtra/update",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id":id },
				success: function(result) {
					
					if(result.html  != "")
					{
						modal = result.html;
					}
					else
					{
						alert(result.message);
						return false;
					}
					
					var modal = $(modal).appendTo("body");
					
					modal.find("button[data-action=save]").on("click", function() {

						data = modal.find("form").serialize() + "&id=" + id + "&ajax=1";
						
						$.ajax({
							url: yii.urls.absoluteUrl + "/customer/customerExtra/update",
							type: "post",
							dataType: "json",
							data: data,
							beforeSend: function(){							
								modal.find("button[data-action=save]").html("Saving Please Wait...");
							},
							success: function(result){
								
								if( result.status == "success" )
								{
									updateExtrasTable( table_element, result.customer_id, result.contract_id, result.skill_id );
								
									modal.modal("hide");
								}
								else
								{
									alert(result.message);
								}
								
								modal.find("button[data-action=save]").html("Save");
							},
						});
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
			
		});
		
		$(document).on("click", ".btn-delete-extra", function(){
			
			if( confirm("Are you sure you want to delete this?") )
			{
				var table_element = $(this).closest(".tab-pane").find(".extras-tbl > tbody");
		
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/customerExtra/delete",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id},
					success: function(result) {
						
						if( result.status == "success" )
						{
							updateExtrasTable( table_element, result.customer_id, result.contract_id, result.skill_id );
						}
						else
						{
							alert(result.message);
						}
					}
				});
			}
			
		});
		
	});

', CClientScript::POS_END);

?>

<?php 
	//Xfr Address Book js
	Yii::app()->clientScript->registerScript(uniqid(), '
		
		function updateXfrTable( table_element, customer_skill_id )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/customerXfrAddressBook/updateXfrTable",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "customer_skill_id":customer_skill_id },
				success: function(result){
					
					if( result.html != "" )
					{
						table_element.html(result.html); 
					}
				}
			});
		}
		
		$(document).ready( function() {
		
			$(document).on("click", ".btn-add-xfr", function(){
				
				var this_button = $(this);
				
				var table_element = $(this).closest(".tab-pane").find(".xfr-tbl > tbody");
				
				var data =  $(this).closest(".tab-pane").find("#addXfrForm").serialize() + "&ajax=1";
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/customerXfrAddressBook/create",
					type: "post",
					dataType: "json",
					data: data,
					beforeSend: function(){							
						this_button.prop("disabled", true);
						this_button.html("Saving Please Wait...");
					},
					success: function(result){
						
						if( result.status == "success" )
						{
							$("#addXfrForm")[0].reset();
							
							updateXfrTable( table_element, result.customer_skill_id );
						}
						else
						{
							alert(result.message);
						}
						
						this_button.prop("disabled", false);
						this_button.html("<i class=\"fa fa-plus\"></i> Add");
					}
				});
				
			});
			
			$(document).on("click", ".btn-edit-xfr", function(){

				var table_element = $(this).closest(".tab-pane").find(".xfr-tbl > tbody");
			
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/customerXfrAddressBook/update",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					success: function(result) {
						
						if(result.html  != "")
						{
							modal = result.html;
						}
						else
						{
							alert(result.message);
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {

							data = modal.find("form").serialize() + "&id=" + id + "&ajax=1";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/customer/customerXfrAddressBook/update",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){							
									modal.find("button[data-action=save]").html("Saving Please Wait...");
								},
								success: function(result){
									
									if( result.status == "success" )
									{
										updateXfrTable( table_element, result.customer_skill_id );
									
										modal.modal("hide");
									}
									else
									{
										alert(result.message);
									}
									
									modal.find("button[data-action=save]").html("Save");
								},
							});
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
				
			});
			
			$(document).on("click", ".btn-delete-xfr", function(){
				
				if( confirm("Are you sure you want to delete this?") )
				{
					var table_element = $(this).closest(".tab-pane").find(".xfr-tbl > tbody");
			
					var id = $(this).prop("id");
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/customerXfrAddressBook/delete",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "id":id},
						success: function(result) {
							
							if( result.status == "success" )
							{
								updateXfrTable( table_element, result.customer_skill_id );
							}
							else
							{
								alert(result.message);
							}
						}
					});
				}
				
			});
		});
	
	', CClientScript::POS_END);

?>

<?php
$this->widget("application.components.HostDialSideMenu",array(
	'active'=> Yii::app()->controller->id,
	'customer' => $customer,
));

?>

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

<style>
#CustomerTierSkill_skillIdArrays label{display:inline-block;}
</style>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<div class="page-header">
	<h1 class="bigger">Customer Skills</h1>
</div>

<?php
/* @var $this CustomerOfficeStaffController */
/* @var $model CustomerOfficeStaff */
/* @var $form CActiveForm */
?>


<div class="row">
	<div class="col-sm-12">
				
		<div class="tabbable tabs-left">
			<ul id="myTab3" class="nav nav-tabs">
				
				<?php						
					$ctr = 1;
					if( !empty($selectedCustomerSkills) )
					{
						
						foreach( $selectedCustomerSkills as $selectedCustomerSkill )
						{
							if($selectedCustomerSkill->skill_id == 11 || $selectedCustomerSkill->skill_id == 12)
								continue;
							
							
							$active = '';
							if(!empty($options['customerSkillTab']))
							{
								if($options['customerSkillTab'] == $selectedCustomerSkill->skill_id)
								{
									$active = 'active';
								}
							}
							else
							{
								if($ctr == 1)
									$active = 'active';
							}
						
							
							$isStartMonthStartedStyle = time() < strtotime($selectedCustomerSkill->start_month) ? 'background-color:#848484;':'';
						?>
							<li id="<?php echo $selectedCustomerSkill->id; ?>" class="<?php echo $active; ?>" >
								<a href="#customer-skill-<?php echo $selectedCustomerSkill->id; ?>" data-toggle="tab" style="<?php echo $isStartMonthStartedStyle; ?>">
									<?php echo $selectedCustomerSkill->skill->skill_name; ?>
								</a>
							</li>
						<?php
						
							$ctr++;
						}
					}
				?>
				
				<?php if(UserAccess::hasRule('customer','CustomerSkill','ajaxAddSkill') && !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('customer_skills_add_skills_tab','visible') ){ ?>
				
					<li data-customer_id="" id="add-skill-btn">
						<a href="javascript:void(0);">
							<i class="fa fa-plus"></i> Add Skill 
						</a>
					</li>
					
				<?php } ?>
			</ul>
			
			<?php 
				$isSkillContentDisplay = 'none';
				if ( count($selectedCustomerSkills) > 0 )
					$isSkillContentDisplay = 'block';

			?>
			
			<div class="tab-content office-tab-content" style="display:<?php echo $isSkillContentDisplay; ?>;">
			
				<?php
					$ctr = 1;
					
					if( !empty($selectedCustomerSkills) )
					{
						
						$isCustomerDisabled = "";
														
						if(Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff())
							$isCustomerDisabled = 'disabled';
														
						foreach( $selectedCustomerSkills as $selectedCustomerSkill )
						{
							$active = '';
							if($selectedCustomerSkill->skill_id == 11 || $selectedCustomerSkill->skill_id == 12)
								continue;
							
							$skill = $selectedCustomerSkill->skill;

							if(!empty($options['customerSkillTab']))
							{
								if($options['customerSkillTab'] == $selectedCustomerSkill->skill_id)
								{
									$active = 'active';
								}
							}
							else
							{
								if($ctr == 1)
									$active = 'active';
							}
							
							?>

							<div style="min-height:200px;" class="tab-pane fade in <?php echo $active; ?>" id="customer-skill-<?php echo $selectedCustomerSkill->id; ?>">
								
								<div class="tabbable tabs-left">
									<ul class="nav nav-tabs" role="tablist">
										
										<?php if(Yii::app()->user->account->checkPermission('customer_skills_contract_tab','visible')){ ?>
										<li role="presentation" class="<?php echo (empty($options['customerSkillSubTab'])) ? "active" : ""; ?>">
											<a href="#customerSkill-contract-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Contract</a>
										</li>
										<?php } ?>
										
										<?php if(Yii::app()->user->account->checkPermission('customer_skills_skill_child_tab','visible')){ ?>
										<li role="presentation" class="">
											<a href="#customerSkill-skillChild-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Child Skill</a>
										</li>
										<?php } ?>
										
										<?php if(Yii::app()->user->account->checkPermission('customer_skills_custom_call_schedule_tab','visible')){ ?>
										<li role="presentation" class="<?php echo (($options['customerSkillSubTab']) == "customer_skills_custom_call_schedule_tab") ? "active" : ""; ?>">
											<a href="#customerSkill-customerCallSchedule-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Custom Call Schedule</a>
										</li>
										<?php } ?>
										
										<?php if(Yii::app()->user->account->checkPermission('customer_skills_dialing_settings_tab','visible')){ ?>
										<li role="presentation" class="">
											<a href="#customerSkill-dialingSettings-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Dialing Settings</a>
										</li>
										<?php } ?>
										
										<?php if(Yii::app()->user->account->checkPermission('customer_skills_extra_tab','visible')){ ?>
										<li role="presentation" class="">
											<a href="#customerSkill-extra-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Extra</a>
										</li>
										<?php } ?>
										
										<?php if( Yii::app()->user->account->checkPermission('customer_skills_script_tab','visible') && $customer->company->customer_specific_skill_scripts == 1 ){ ?>
										<li role="presentation" class="">
											<a href="#customerSkill-script-<?php echo $selectedCustomerSkill->id; ?>" role="tab" data-toggle="tab">Script</a>
										</li>
										<?php } ?>
									</ul>
									
									<!-- Tab panes -->
									<div class="tab-content">
										<div role="tabpanel" class="tab-pane" id="customerSkill-skillChild-<?php echo $selectedCustomerSkill->id; ?>">
											<div class="col-md-12">
												<h3>Child Skill</h3>
												<table class="table-striped table">
												<?php 
													if(!empty($skill->skillChilds))
													{
														foreach($skill->skillChilds as $skillChild)
														{
															$isChecked = "";
															
															
															$criteria = new CDbCriteria;
															$criteria->compare('customer_id', $customer->id);
															$criteria->compare('skill_id', $skillChild->skill_id);
															$criteria->compare('customer_skill_id', $selectedCustomerSkill->id);
															$criteria->compare('skill_child_id', $skillChild->id);
															$customerSkillChild = CustomerSkillChild::model()->find($criteria);
															
															if($customerSkillChild !== null && $customerSkillChild->is_enabled == 1)
																$isChecked = "checked";
															
															echo "<tr>";
																echo "<td>{$skillChild->child_name}</td>";
																
																if( Yii::app()->user->account->checkPermission('customer_skills_skill_child_on_off_button','visible') )
																{
																	echo "<td>
																		<label>
																				<small>
																					<input type=\"hidden\" class=\"customerSkill-id\" value=\"{$selectedCustomerSkill->id}\">
																					<input type=\"checkbox\" class=\"toggle-skill-child ace ace-switch ace-switch-1\" value=\"{$skillChild->id}\" ".$isChecked." ".$isCustomerDisabled.">
																					<span class=\"lbl middle\"></span>
																				</small>
																		</label>
																	</td>";
																}
															echo "</tr>";
														}
													}
												?>
												</table>
											</div>
										</div>
										
										<div role="tabpanel" class="tab-pane <?php echo (($options['customerSkillSubTab']) == "customer_skills_custom_call_schedule_tab") ? "active" : ""; ?>" id="customerSkill-customerCallSchedule-<?php echo $selectedCustomerSkill->id; ?>">
											<div class="col-md-12">
												<h3>Custom Call Schedule</h3>
												
												<?php if( Yii::app()->user->account->checkPermission('customer_skills_custom_call_schedule_on_off_button','visible') ){ ?>
													<small>
														<input type="checkbox" class="toggle-custom-call-schedule ace ace-switch ace-switch-1" value="<?php echo $selectedCustomerSkill->id; ?>"  <?php echo ($selectedCustomerSkill->is_custom_call_schedule == 1) ? "checked" : ""; ?> <?php echo $isCustomerDisabled;?>>
														<span class="lbl middle"></span>
													</small>
												<?php } ?>
												
												<div class="custom-call-schedule-container" style="<?php echo ($selectedCustomerSkill->is_custom_call_schedule) ? "" : "display:none"; ?>">
													<?php $this->forward('/customer/customerSkill/customScheduleUpdate/customer_skill_id/'.$selectedCustomerSkill->id.'/customer_id/'.$customer->id,false); ?>
													<?php 
													// $this->renderPartial('_customCallSchedule',array(
														// 'selectedCustomerSkill' => $selectedCustomerSkill,
														// 'skill' => $skill,
														// 'model' => isset($selectedCustomerSkill->customerSkillSchedule) ? $selectedCustomerSkill->customerSkillSchedule : new CustomerSkillSchedule,
													// )); 
													?>
												</div>
											</div>
										
										</div>
										
										<div role="tabpanel" class="tab-pane" id="customerSkill-dialingSettings-<?php echo $selectedCustomerSkill->id; ?>">
											<?php #only display this of skill's caller_option is customer Choice ?>
											<?php if($skill->caller_option == Skill::CALLER_OPTION_CUSTOMER_CHOICE){ ?>
												<div class="col-md-12">
													<?php $this->forward('/customer/customerSkill/dialingSetting/customer_skill_id/'.$selectedCustomerSkill->id.'/customer_id/'.$customer->id,false); ?>
												</div>
											<?php } ?>
										</div>
										
										
										<div role="tabpanel" class="tab-pane <?php echo (empty($options['customerSkillSubTab'])) ? "active" : ""; ?>" id="customerSkill-contract-<?php echo $selectedCustomerSkill->id; ?>" style="<?php echo Yii::app()->user->account->checkPermission('customer_skills_contract_tab','visible') ? '' : 'display:none;'; ?>">
											<?php $contract = $selectedCustomerSkill->contract;	?>
											
											
											<div class="row">
												<div class="col-md-12">
													<?php $this->forward('/customer/customerSkill/startEndDate/customer_skill_id/'.$selectedCustomerSkill->id.'/customer_id/'.$customer->id,false); ?>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<?php $this->forward('/customer/customerSkill/promo/customer_skill_id/'.$selectedCustomerSkill->id.'/customer_id/'.$customer->id,false); ?>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-8">
													
													<div class="page-header"><h1><?php echo $contract->contract_name; ?></h1></div>	
													
													
													<?php if($contract->is_fee_start_activate){ ?>
													
													<?php /*
													<strong>Start Fee Amount:</strong> <?php echo $contract->start_fee_amount; ?><br>
													<strong>Bill Start Fee How Many Days After Signup:</strong> <?php echo $contract->start_fee_day; ?><br>
													<strong>Number of Successful Billing Cycles to Remove Start Fee:</strong> <?php echo $contract->start_fee_billing_cycle; ?><br>
													<br><br>
													*/ ?>
													
													<?php } ?>
													<div class="row subsidy-containers" id="goal-volume-container">
														<?php 
															if($contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME)
															{
																if(!empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME])){
																	foreach($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel){
																		$this->renderPartial('_goalVolume',array(
																			'subsidyLevel' => $subsidyLevel,
																			'selectedCustomerSkill' => $selectedCustomerSkill,
																			'isCustomerDisabled' => $isCustomerDisabled,
																		));
																	}
																} 
															}
														?>
													</div>
													<div class="row subsidy-containers" id="lead-volume-container">
														<?php 
															if($contract->fulfillment_type == Contract::TYPE_FULFILLMENT_LEAD_VOLUME)
															{
																if(!empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME])){
																	foreach($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel){
																		$this->renderPartial('_leadVolume',array(
																			'subsidyLevel' => $subsidyLevel,
																			'selectedCustomerSkill' => $selectedCustomerSkill,
																			'isCustomerDisabled' => $isCustomerDisabled,
																		));
																	}
																} 
															}
														?>
													</div>
													
													<div class="page-header"><h1>Subsidies</h1></div>
													
													<?php if(!empty($contract->companySubsidies))
													{ 
														foreach($contract->companySubsidies as $companySubsidy)
														{ 
														
															$customerSkillSubsidyArray = $selectedCustomerSkill->getCustomerSkillSubsidyArray();
															$customerSkillSubsidy = isset($customerSkillSubsidyArray[$companySubsidy->id]) ? $customerSkillSubsidyArray[$companySubsidy->id] : null;
															$isToggleQuantityDisabled = ($customerSkillSubsidy !== null && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE) ? false : true;
															
															if( Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff() )
															{
																$isToggleQuantityDisabled = true;
															}
														?>
															<div class="company-subsidy-container">
																<div>
																	<h3 style="display:inline-block;">
																		<?php if( Yii::app()->user->account->checkPermission('customer_skills_contract_subsidy_on_off_button','visible') ){ ?>
																			<small>
																				<input 
																					type="checkbox" 
																					class="toggle-skill-subsidy ace ace-switch ace-switch-1" 
																					value="<?php echo $companySubsidy->id; ?>"  
																					data-customer_skill_id="<?php echo $selectedCustomerSkill->id; ?>" 
																					data-customer_id="<?php echo $selectedCustomerSkill->customer_id; ?>" 
																					<?php echo ($isToggleQuantityDisabled) ? "" : "checked"; ?>   
																					<?php echo $isCustomerDisabled; ?>
																				>
																				
																				<span class="lbl middle"></span>
																			</small>
																		<?php } ?>
																		
																		<?php echo $companySubsidy->subsidy_name; ?>
																	</h3>
																	
																	<?php
																	##CODE HACK :D ##
																	$criteria = new CDbCriteria;
																	$criteria->compare('customer_skill_id', $selectedCustomerSkill->id);
																	$criteria->compare('customer_id', $selectedCustomerSkill->customer_id);
																	$cksl = CustomerSkillSubsidyLevel::model()->find($criteria);
																	
																	?>
																	
																	<?php 
																		if( Yii::app()->user->account->checkPermission('customer_skills_contract_subsidy_level_dropdown','visible') )
																		{
																			echo CHtml::dropDownList('companySubsidy', !empty($cksl) ? $cksl->subsidy_level_id : null,CHtml::listData($companySubsidy->companySubsidyLevels,'id','name'),array(
																				'empty'=>'-Select Company Contract Subsidy-', 
																				'class'=>'js-companySubsidy', 
																				'disabled'=> $isToggleQuantityDisabled,
																				'data-customer_skill_id'=>$selectedCustomerSkill->id,
																				'data-customer_id'=> $selectedCustomerSkill->customer_id
																			)); 
																		}
																	?>
														
																</div>
																<br>
																
																<?php 
																if(!empty($companySubsidy->companySubsidyLevels))
																{
																	foreach($companySubsidy->companySubsidyLevels as $companySubsidyLevel){
																		$this->renderPartial('_subsidyLevelsCompany',array(
																			'name' => $companySubsidyLevel->id,
																			'companySubsidyLevel' => $companySubsidyLevel,
																			'selectedCustomerSkill' => $selectedCustomerSkill,
																			'cksl' => $cksl,
																		));
																	}
																}
																?>
															
															</div>
														<?php
														}
													}
													?>
													
													<?php if(!empty($contract->tierSubsidies))
													{ 
														foreach($contract->tierSubsidies as $tierSubsidy)
														{ 
															$customerSkillSubsidyArray = $selectedCustomerSkill->getCustomerSkillSubsidyArray();
															$customerSkillSubsidy = isset($customerSkillSubsidyArray[$tierSubsidy->id]) ? $customerSkillSubsidyArray[$tierSubsidy->id] : null;
															$isToggleQuantityDisabled = ($customerSkillSubsidy !== null && $customerSkillSubsidy->status == CustomerSkillSubsidy::STATUS_ACTIVE) ? false : true;
														?>
															<div class="tier-subsidy-container">
																<div>
																	<h3 style="display:inline-block;">
																		<small>
																			<input 
																				type="checkbox" 
																				class="toggle-skill-subsidy ace ace-switch ace-switch-1" 
																				value="<?php echo $tierSubsidy->id; ?>"  
																				data-customer_skill_id="<?php echo $selectedCustomerSkill->id; ?>" 
																				data-customer_id="<?php echo $selectedCustomerSkill->customer_id; ?>" 
																				<?php echo ($isToggleQuantityDisabled) ? "" : "checked"; ?> 
																				<?php echo $isCustomerDisabled; ?>
																			>
																			
																			<span class="lbl middle"></span>
																		</small>
																		<?php echo $tierSubsidy->subsidy_name; ?>
																	</h3>
																	<?php echo CHtml::dropDownList('tierSubsidy','',CHtml::listData($tierSubsidy->tierSubsidyLevels,'id','name'),array('empty'=>'-Select Tier Contract Subsidy-', 'class'=>'js-tierSubsidy', 'disabled'=> $isToggleQuantityDisabled)); ?>
																</div>
																
																<br>
																
																<?php
																if(!empty($tierSubsidy->tierSubsidyLevels))
																{
																	foreach($tierSubsidy->tierSubsidyLevels as $tierSubsidyLevel){
																		$this->renderPartial('_subsidyLevelsTier',array(
																			'name' => $tierSubsidyLevel->id,
																			'tierSubsidyLevel' => $tierSubsidyLevel,
																			'selectedCustomerSkill' => $selectedCustomerSkill,
																		));
																	}
																}
																?>
																
															</div>
														<?php
															
														}
													}
													?>
												</div>
											</div>
										</div>
										
										<div role="tabpanel" class="tab-pane" id="customerSkill-extra-<?php echo $selectedCustomerSkill->id; ?>">
											
											<div class="col-md-12">
												<h3>Extra</h3>
												
												<div class="row">
													<div class="col-sm-12">
														
														<form id="addExtraForm">	
	
															<?php 
																echo CHtml::hiddenField('CustomerExtra[account_id]', Yii::app()->user->account->id);
																
																echo CHtml::hiddenField('CustomerExtra[customer_id]', $customer->id);
																
																echo CHtml::hiddenField('CustomerExtra[contract_id]', $selectedCustomerSkill->contract->id);
																
																echo CHtml::hiddenField('CustomerExtra[skill_id]', $selectedCustomerSkill->skill->id);
															?>
														
															<?php echo CHtml::textField('CustomerExtra[description]', '', array('style'=>'width:350px;', 'placeholder'=>'Description')); ?>
															
															<?php echo CHtml::textField('CustomerExtra[quantity]', '', array('style'=>'width:70px;', 'maxLenght'=>4, 'placeholder'=>'Quantity')); ?>
															
															<?php echo CHtml::textField('CustomerExtra[year]', '', array('style'=>'width:42px;', 'maxLenght'=>4, 'placeholder'=>'Year')); ?>
															
															<?php echo CHtml::dropDownList('CustomerExtra[month]', '', CustomerCreditCard::cardExpirationMonths(), array('prompt'=>'-Month-', 'style'=>'width:auto; height:34px;')); ?>
															
															<?php if( Yii::app()->user->account->checkPermission('customer_skills_extra_add_button','visible') ){ ?>
																<button type="button" class="btn btn-success btn-sm btn-add-extra"><i class="fa fa-plus"></i> Add</button>
															<?php } ?>
														</form>
													</div>
												</div>
												
												<div class="space-12"></div>
												
												<div class="row">
													<div class="col-sm-12">
														<table class="table table-striped table-bordered table-condensed table-hover extras-tbl">
															<thead>
																<th>Description</th>
																<th>Date Added</th>
																<th>Year</th>
																<th>Month</th>
																<th>Quantity</th>
																
																<?php if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff()): ?>
																
																<th class="center">Options</th>
																
																<?php endif; ?>
																
															</thead>
															<tbody>
																<?php 
																	$customerExras = CustomerExtra::model()->findAll(array(
																		'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
																		'params' => array(
																			':customer_id' => $customer->id,
																			':contract_id' => $selectedCustomerSkill->contract->id,
																			':skill_id' => $selectedCustomerSkill->skill->id,
																		),
																	));
																	
																	if( $customerExras ) 
																	{
																		foreach( $customerExras as $customerExra )
																		{
																			$date = new DateTime($customerExra->date_created, new DateTimeZone('America/Chicago'));

																			$date->setTimezone(new DateTimeZone('America/Denver'));
																			
																			echo '<tr>';
																				echo '<td>'.$customerExra->description.'</td>';
																				
																				echo '<td>'.$date->format('m/d/Y g:i A').'</td>';

																				echo '<td>'.$customerExra->year.'</td>';
																				
																				echo '<td>'.date('F', mktime(0, 0, 0, $customerExra->month, 10)).'</td>';
																				
																				echo '<td>'.$customerExra->quantity.'</td>';
																				
																				if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff())
																				{
																					echo '<td class="center">';
																						
																						if( Yii::app()->user->account->checkPermission('customer_skills_extra_edit_button','visible') )
																						{
																							echo '<button id="'.$customerExra->id.'" class="btn btn-info btn-minier btn-edit-extra"><i class="fa fa-pencil"></i> Edit</button>';
																						}
																						
																						if( Yii::app()->user->account->checkPermission('customer_skills_extra_remove_button','visible') )
																						{
																							echo '<button id="'.$customerExra->id.'" style="margin-left:5px;"class="btn btn-danger btn-minier btn-delete-extra"><i class="fa fa-times"></i> Delete</button>';
																						}
																					echo '</td>';
																				}
																				
																			echo '</tr>';
																		}
																	}
																	else
																	{
																		echo '<tr><td colspan="6">No results found.</td></tr>';
																	}
																?>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
										
										<?php if( $customer->company->customer_specific_skill_scripts == 1 ){ ?>
										<div role="tabpanel" class="tab-pane" id="customerSkill-script-<?php echo $selectedCustomerSkill->id; ?>">
											
											<div class="col-md-12">
												<h3>Script</h3>
																									
												<?php $form=$this->beginWidget('CActiveForm', array(
													'id'=>'scriptTabForm',
													'enableAjaxValidation'=>false,
													'htmlOptions' => array('enctype' => 'multipart/form-data'),
												)); ?>	
												
													<?php echo $form->hiddenField($selectedCustomerSkill, 'id'); ?>
													
													<div class="row">
														<?php echo $form->fileField($selectedCustomerSkill,'fileUpload'); ?>
														<?php echo $form->error($selectedCustomerSkill,'fileUpload'); ?>
														
														<?php
															if( $selectedCustomerSkill->script_tab_fileupload_id != null )
															{
																echo '<small>'; 
																	echo'<i class="fa fa-paperclip"></i> Current Script Tab File: ' . CHtml::link($selectedCustomerSkill->scriptFileupload->original_filename, array('download', 'id'=>$selectedCustomerSkill->script_tab_fileupload_id));
																echo '</small>';
															}
														?>
													</div>
		
													<div class="space-12"></div>
													
													<div class="row">
														<button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Save Script File</button>
													</div>
													
												<?php $this->endWidget(); ?>
											</div>
										</div>
										<?php } ?>
									</div>
								</div>
							
								<div class="row">
									<div class="col-md-12">
										<hr>
										<div class="btn-group btn-corner">
											<?php if(UserAccess::hasRule('customer','CustomerSkill','delete') && !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('customer_skills_remove_skill_button','visible') ){ ?>
												<?php echo CHtml::link('<i class="fa fa-trash-o"></i> Remove Skill',array('customerSkill/delete','customer_id' => $customer->id, 'customer_skill_id' => $selectedCustomerSkill->id ), array('class'=> 'btn btn-sm btn-danger remove-skill')); ?> 
											<?php } ?>
											
											<?php if( !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('customer_skills_cancel_skill_button','visible') ){ ?>
											<?php 
												echo CHtml::link('<i class="fa fa-envelope"></i> Send Cancel Email',array('customerSkill/cancel','customer_id' => $customer->id, 'contract_id' => $selectedCustomerSkill->contract_id, 'skill_id'=>$selectedCustomerSkill->skill_id, 'start_month'=>$selectedCustomerSkill->start_month ), array('class'=> 'btn btn-sm btn-inverse', 'confirm'=>'Are you sure you want to cancel this?')); 
											?>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
							
						<?php
						$ctr++;
						}
					}
				?>

			</div>
		</div>
	</div>
</div>
