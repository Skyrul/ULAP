<?php /*Yii::app()->clientScript->registerScript('search-by-agent-id','

	var delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	  };
	})();

	$("body").on("keyup", ".js-custom_customer_id", function(){
		
		thisVal = $(this).val();
		
		delay(function(){
			$.ajax({
				url: "'.Yii::app()->createUrl('/site/getCompanyCustomerFundingTierByAgentCode').'",
				method: "GET",
				dataType: "json",
				data: {			  
				  "agent_code" : thisVal,					  
				  "company_id" : $("#'.CHtml::activeId($model,'companyId').'").val(),					  
				},
				beforeSend: function() {
					$("#agent-id-search-container").html("Finding agent, please wait...");
				},
			}).success(function(response) {
				
				if(response.status == true)
				{
					var agent = response.agent;
					$("#'.CHtml::activeId($model,'firstname').'").val(agent.agent_firstname);
					$("#'.CHtml::activeId($model,'lastname').'").val(agent.agent_lastname);
					
					$("#agent-id-search-container").html("Agent ID matched");
				}
				else if(response.status == "notfound")
				{
					$("#agent-id-search-container").html(response.errorMessage);
				}
				else
					$("#agent-id-search-container").html("Agent ID not matching from \""+response.company_name+"\" Database");
			});
		
		}, 1000 );
	
	});
',CClientScript::POS_END);
*/ ?>

<?php Yii::app()->clientScript->registerScript('update-contract-level','
	$("body").on("change", "#'.CHtml::activeId($model,'companyId').'", function(){
		
		thisVal = $(this).val();
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/site/updateContractCompanyOptionForm').'",
			method: "GET",
			dataType: "json",
			data: {			  
			  "company_id" : thisVal,					  
			  "contract_id" : "'.$contract->id.'",					  
			}
		}).success(function(response) {
			
			$("#contract-level-container").empty().html(response);
		});
	});
',CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('form-populate-contract-dropdown','
	$("body").on("change", ".js-select-company", function(){
		
		thisVal = $(this).val();
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/site/getContractByCompany').'",
			method: "GET",
			dataType: "json",
			data: {			  
			  "company_id" : thisVal,					  
			}
		}).success(function(response) {
			
			
			var options = $(".js-select-contract");
			options.empty();
			options.append($("<option />").val("").text("-Select Contract-"));
			$.each(response, function() {
				options.append($("<option />").val(this.id).text(this.contract_name));
			});
		});
	});
',CClientScript::POS_END);
?>
 
<?php 

Yii::app()->clientScript->registerScript('form-populate-contract-level-container','
	$("body").on("change", ".js-select-contract", function(){
		thisVal = $(this).val();
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/site/getContractLevelById').'",
			method: "GET",
			data: {			  
			  "contract_id" : thisVal,					  
			}
		}).success(function(response) {
			
			$(".contract-level-container").html(response);
		});
	});
',CClientScript::POS_END);
?>


<?php Yii::app()->clientScript->registerScript('toggle-customerSkill-skill-contract-level','
	$("body").on("change", ".toggle-skill-contract-level", function(){
		
		var quantityObj = $(this).parent().parent().parent().find(".skill-level-contract-level-quantity");
		
		if( $(this).is(":checked") == false )
		{
			quantityObj.prop("disabled",true);
			quantityObj.val("");
		}
		else
		{
			quantityObj.prop("disabled",false);
		}
		
		$(".number-field").trigger("change");
	});
	
	
	$(".toggle-skill-contract-level").trigger("change");
',CClientScript::POS_END);
?>

<?php 

Yii::app()->clientScript->registerScript('txtfield-number-spinner','
	$("body").on("change", ".number-field", function(){
		computeTotalQty();
	});

	
	$(".number-field").trigger("change");
	 
	 
	 $(".number-field").keypress(function (e) {
		if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
				   return false;
		}
	});
	
	function computeTotalQty()
	{
		var totalQty = 0;
		multiplyObjs = $("body").find(".js-qty-to-multiply");
		
		multiplyObjs.each(function( index ) {
			// console.log(index);
			var qtyVal = $(this).parent().find(".number-field").val();
			
			var output = $(this).data("original-value") * qtyVal;
			
			totalQty += output;
		});
		
		$(".totalQty").html(totalQty);
	}
	
',CClientScript::POS_END);

?>

<?php 
Yii::app()->clientScript->registerScript('sameAsBillingAddress-js','
	$("#differentBillingAddress").on("click",function(){
		differentBillingAddress();
	});
	
	$(".js-address").on("keyup",function(){
		differentBillingAddress();
	});
	
	differentBillingAddress();
	
	function differentBillingAddress()
	{
		var address = $(".orig-address").val();
		var city = $(".orig-city").val();
		var state = $(".orig-state").val();
		var zip = $(".orig-zip").val();
		
		if( $("#differentBillingAddress").is(":checked") == true )
		{
			$(".copy-address").val("").prop("readOnly",false);
			$(".copy-city").val("").prop("readOnly",false);
			$(".copy-state").val("").prop("readOnly",false);
			$(".copy-zip").val("").prop("readOnly",false);
		}
		else
		{ 
			$(".copy-address").val(address).prop("readOnly",true);
			$(".copy-city").val(city).prop("readOnly",true);
			$(".copy-state").val(state).prop("readOnly",true);
			$(".copy-zip").val(zip).prop("readOnly",true);
		}
	}
	
',CClientScript::POS_END);
?>

<?php 
Yii::app()->clientScript->registerScript('paymentMethod-js','

	$("#CustomerEnrollment_payment_method").on("change", function(){
		
		if( $(this).val() == "Credit Card" )
		{
			$(".echeckFields").hide();
			$(".creditCardFields").fadeIn();
		}
		else
		{
			$(".creditCardFields").hide();
			$(".echeckFields").fadeIn();
		}
		
	});
	
',CClientScript::POS_END);
?>


<?php 
	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');
	// $cs->registerScriptFile($baseUrl . '/js/jquery-mobilePassword/js/jquery.mobilePassword.js?'.time(), CClientScript::POS_END);
	
	$cs->registerScript(uniqid(), "
		$.mask.definitions['~']='[+-]';
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-zip').mask('99999');
		$('.input-mask-amount').mask('9999.99');
		$('#CustomerEnrollment_custom_customer_id').mask('**-****',{
			completed:function(){ 
				$('#CustomerEnrollment_custom_customer_id').val(this.val().toUpperCase()); 
			}
		});
		
	", CClientScript::POS_END);

	$cs->registerScript(uniqid(), '

		$(document).ready( function() {
			
			$("#CustomerEnrollment_credit_card_number").on("keyup", function(){
							
				if (this.value != this.value.replace(/[^0-9\.]/g, "")) {
					this.value = this.value.replace(/[^0-9\.]/g, "");
				}
				
				cc_number = $("#CustomerEnrollment_credit_card_number").val().slice(-4);
				
				// cc_number = $("#CustomerEnrollment_credit_card_number").val();
				$(".cc-show-last-4").text(cc_number);
				
			});
			
			
			var creditCardType = $("#'.CHtml::activeId($model, 'credit_card_type').'");
			var creditCardSecurityCode = $("#'.CHtml::activeId($model, 'credit_card_security_code').'");

			creditCardType.on("change", function(){
				
				setTimeout(function() { 
					$("#CustomerEnrollment_credit_card_number").focus(); 
					$("#CustomerEnrollment_credit_card_number").blur();
					$("#CustomerEnrollment_credit_card_number").trigger("keyup");
				}, 100);
				
				if( $(this).val() == "Amex" )
				{
					$("#CustomerEnrollment_credit_card_number").attr("maxlength", "15");
					
					creditCardSecurityCode.attr("maxlength", "4");
					
					if( $("#CustomerEnrollment_credit_card_number").val().length > 15 )
					{
						$("#CustomerEnrollment_credit_card_number").val( $("#CustomerEnrollment_credit_card_number").val().slice(0, -1) );
					}
					
					if( creditCardSecurityCode.val().length > 4 )
					{
						creditCardSecurityCode.val( creditCardSecurityCode.val().slice(0, -1) );
					}
				}
				else
				{
					$("#CustomerEnrollment_credit_card_number").attr("maxlength", "16");
					
					creditCardSecurityCode.attr("maxlength", "3");
					
					if( $("#CustomerEnrollment_credit_card_number").val().length > 16 )
					{
						$("#CustomerEnrollment_credit_card_number").val( $("#CustomerEnrollment_credit_card_number").val().slice(0, -1) );
					}
					
					if( creditCardSecurityCode.val().length > 3 )
					{
						creditCardSecurityCode.val( creditCardSecurityCode.val().slice(0, -1) );
					}
				}
				
			});
			
			creditCardType.trigger("change");	
		});
	
	', CClientScript::POS_END);
?>

<?php Yii::app()->clientScript->registerScript('contractLoginScript','
	$("#contract-login").on("click",function(){
		verificationProcess();
	});
	
	
	$("#contractPassword").on("keypress",function(e){
		if (e.keyCode == 13) {
			verificationProcess();
            return false; // prevent the button click from happening
        }
	});
	
	function verificationProcess()
	{
		contractFieldVal = $("#contractPassword").val();
		
		if(contractFieldVal == "simpleconversations")
		{
			// alert("Password correct!");
			$("#login-container").addClass("hidden");
			$("#contract-form-container").removeClass("hidden");
			$("#navbar-container").removeClass("hidden");
		}
		else
		{
			alert("Password incorrect!");
			$("#login-container").removeClass("hidden");
			$("#contract-form-container").addClass("hidden");
		}
	}
	
',CClientScript::POS_END); ?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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

<div class="page-content">
	<div class="page-content-area">
		
		<div class="login-container  hidden" id="login-container">
			<div class="page-header center">
			</div>
			
			<div class="position-relative">
				<div class="login-layout">
					<div class="login-box visible widget-box no-border" id="contract-login-box">
						<div class="widget-body">
							<div class="widget-main">
								<div class="row">
									<div class="col-md-12">
									
										<h4 class="header blue lighter bigger">
											Please Enter Password
										</h4>
						
										<label class="block clearfix">
											<span class="block input-icon input-icon-right">
												<?php //echo $form->labelEx($model,'username'); ?>
												<?php echo CHtml::textField('contractPassword','', array('class'=>'form-control', 'placeholder'=>'Enter Password')); ?>
												<i class="ace-icon fa fa-lock"></i>
											</span>
										</label>
										
										<div class="space"></div>

										<div class="clearfix">
											<button id="contract-login" class="width-35 pull-right btn btn-sm btn-primary">
												<i class="ace-icon fa fa-key"></i>
												<span class="bigger-110">Login</span>
											</button>
										</div>
									</div>
								</div>
							</div>
							
							<div class="toolbar clearfix">
							<br/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="contract-form-container" class="row ">
			<div class="widget-container-col col-md-8 col-md-offset-2">
				<div class="widget-box">
					<div class="widget-header">
						<label>
							<h5 class="bigger">Enrollment Form</h5>
						</label>
					</div>
					
					<div class="widget-body">
						<div class="widget-main padding-6">	
							
							
							<div class="form">
								<?php $form=$this->beginWidget('CActiveForm', array(
									'id'=>'contract-form',
									'enableAjaxValidation'=>true,
									// 'enableClientValidation' => true,
									'clientOptions'=>array(
										'validateOnSubmit'=>true,
										'validateOnChange' => false,
										'beforeValidate' => 'js:function(form){
											return true;
										}',
										'afterValidate'=>'js:function(form, data, hasError){
											if(!hasError)
											{
												
												formData = [];
												var contractForm =  $( "#contract-form" ).serializeArray() ;
												
												var emailAddress = {
													  name: "email_address",
													  value: $("#'.CHtml::activeId(new CustomerEnrollment,'email_address').'").val()
												};
												
												var uniqueId = {
													  name: "contract_id",
													  value: "'.$contract->id.'"
												};
												
												formData.push(emailAddress);
												formData.push(uniqueId);
												
												$.each(contractForm, function( index, value ) {
													formData.push(value);
												});
												
												
												var submitHtml  = $("#submit-btn").html();
												$("#submit-btn").html("Please wait...").prop("disabled",true);
												
												$.ajax({
													url: yii.urls.absoluteUrl + "/site/checkEmailAddressIfAlreadyHaveAccount",
													type: "GET",	
													data: formData,
													dataType: "json",
													
													beforeSend: function(){
													},
													complete: function(){
													},
													error: function(err){
														 alert("Error in request: " + JSON.stringify(err, null, 2));
														 $("#submit-btn").html(submitHtml).prop("disabled",false);
													},
													success: function(r){
														
														if(r.status == 1)
														{
															header = "Account Creation";
															$("#myModal #myModalLabel").html(header);
															$("#myModal .modal-body").html(r.content);
															$("#myModal").modal();
															
															
															$("body").on("click","#changeEmailAddress",function(){
																$("#myModal").modal("hide");
															});
														}
														else if(r.status == 99 || r.status == 100)
														{
															if(r.status == 100)
															{
																window.location = yii.urls.absoluteUrl + "/site/thankYou";
															}
															
															
														}
														else
														{ 
															alert(r.message);
														}
														
														$("#submit-btn").html(submitHtml).prop("disabled",false);
													},
												});
												
												
											}
											
											return false;
										}', // Your JS function to submit form
									),
								)); ?>
								
								<?php echo $form->errorSummary($model); ?>
								
									<!-- Contract Information -->
								
								
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Contact Information
											</h3>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo CHtml::activeDropDownList($model, 'companyId', CHtml::listData($contractCompanies,'company.id','company.company_name'),array('placeholder'=>'Company', 'class'=>'form-control','empty'=>'-Select Company-')); ?>
										<?php echo $form->error($model,'companyId'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'custom_customer_id',array('placeholder'=>'Agent ID (Six Digit)', 'class'=>'form-control js-custom_customer_id','maxlength'=>7)); ?>
										<?php echo $form->error($model,'custom_customer_id'); ?>
										</div>
										
										<div id="agent-id-search-container" class="col-md-6" style="float:left; width:40%;">
											<?php if($this->pdfView === true ){
												
												$criteria = new CDbCriteria;
												$criteria->compare('agent_code',$model->custom_customer_id);
												$criteria->compare('company_id',$contract->company->id);
												$companyCustomerFundingTier = CompanyCustomerFundingTier::model()->find($criteria);
												
												if($companyCustomerFundingTier !== null)
												{
													echo "Agent ID matched";
												}
												else
												{
													echo 'Agent ID not matching from "'.$contract->company->company_name.'" Database';
												}
												
											} ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'firstname',array('placeholder'=>'First Name', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'firstname'); ?>
										</div>
										
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'lastname',array('placeholder'=>'Last Name', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'lastname'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'address',array('placeholder'=>'Address', 'class'=>'form-control orig-address js-address')); ?>
										<?php echo $form->error($model,'address'); ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'city',array('placeholder'=>'City', 'class'=>'form-control orig-city js-address')); ?>
										<?php echo $form->error($model,'city'); ?>
										</div>
										
										<div class="col-md-6" style="float:left; width:40%;">
											<div class="row">
												<div class="col-md-6" style="float:left; width:45%;">
													<?php echo $form->dropDownList($model,'state',State::listStates(),array('empty'=>'-Select State-', 'class'=>'form-control orig-state  js-address')); ?>
													<?php echo $form->error($model,'state'); ?>
												</div>
												<div class="col-md-offset-1 col-md-5" style="float:left; width:40%;">
													<?php echo $form->textField($model,'zip',array('placeholder'=>'Zip', 'class'=>'form-control orig-zip js-address input-mask-zip')); ?>
													<?php echo $form->error($model,'zip'); ?>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'phone',array('placeholder'=>'Phone Number', 'class'=>'form-control input-mask-phone')); ?>
										<?php echo $form->error($model,'phone'); ?>
										</div>
										
										<div class="col-md-6">
											<!--
											<small style="line-height:33px;">This is the number prospects will see on their caller ID</small>
											-->
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'email_address',array('placeholder'=>'Email Address', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'email_address'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'referral',array('placeholder'=>'Referral', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'referral'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->dropDownList($model,'sales_rep_account_id', AccountUser::listSalesAgents(),array('class'=>'form-control', 'empty'=>'-Select Sales Rep-')); ?>
											<?php echo $form->error($model,'sales_rep_account_id'); ?>
										</div>
									</div>
									
									<!-- Package Information -->
									
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Package
											</h3>
										</div>
									</div>
									
									<div class="row">
									
										<div class="col-md-12">
										
											<?php echo $form->hiddenField($model,'customerEnrollmentLevelValidation'); ?>
											<?php echo $form->error($model,'customerEnrollmentLevelValidation'); ?>
											
											<div id="contract-level-container">
												<?php $this->renderPartial('_contractLevel',array(
													'contract' => $contract,
													'model' => $model,
												)); ?>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
												<?php if($this->pdfView === true ){ ?>
												
													Total - $<span class="totalQty"><?php echo $this->totalContractValue; ?></span>
												<?php }else{ ?>
													Total - $<span class="totalQty">0</span>
												<?php } ?>
										</div>
									</div>
									
									<!-- Start Month Information -->
									
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Start Month
											</h3>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-5" style="float:left;  width:40%;">
										<?php echo $form->dropDownList($model,'start_month', CustomerCreditCard::cardExpirationMonths(), array('empty'=>'-Select Start Month-', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'start_month'); ?>
										</div>
									</div>
									
									<!-- BILLING Information -->
									
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Billing Information
											</h3>
										</div>
									</div>
									
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php 
										// $paymentMethodArray = array('Credit Card' => 'Credit Card' ,'eCheck'=>'eCheck');
											// if( !Yii::app()->user->isGuest && (Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff()))
												$paymentMethodArray = array('Credit Card' => 'Credit Card');
										?>
										<?php echo $form->dropDownList($model,'payment_method',$paymentMethodArray,array('class'=>'form-control')); ?>
										<?php echo $form->error($model,'payment_method'); ?>
										</div>
									</div>
									
									<div class="creditCardFields" style="display:<?php echo $model->payment_method == 'Credit Card' || $model->payment_method == '' ? 'block':'none'; ?>">
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
												<?php echo $form->dropDownList($model,'credit_card_type',CustomerCreditCard::cardTypes(), array('class'=>'form-control')); ?>
												<?php echo $form->error($model,'credit_card_type'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->textField($model,'credit_card_name',array('placeholder'=>'Name on Card', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'credit_card_name'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
												<?php echo $form->passwordField($model,'credit_card_number',array('placeholder'=>'Number', 'class'=>'form-control')); ?>
												<?php echo $form->error($model,'credit_card_number'); ?>
											</div>
											
											<div class="col-md-6" style="float:left; width:40%;">
												<span class="cc-show-last-4" style="line-height:40px; margin-left:15px;"></span>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-3" style="float:left; width:20%;">
											<?php echo $form->textField($model,'credit_card_security_code',array('placeholder'=>'Security Code', 'class'=>'form-control','maxLength'=>3)); ?>
											<?php echo $form->error($model,'credit_card_security_code'); ?>
											</div>
											
											<div class="col-md-3" style="float:left; width:40%;">
											<?php echo CHtml::image(Yii::app()->request->baseUrl.'/images/cvv2_sm.gif','',array('style'=>'margin:9px 0 0 0px;')); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-4" style="float:left; width:20%;">
											<?php echo $form->dropDownList($model,'credit_card_expiration_month', CustomerCreditCard::cardExpirationMonths(), array('empty'=>'Expiration Month', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'credit_card_expiration_month'); ?>
											</div>
											
											<div class="col-md-4" style="float:left; width:30%;">
											<?php echo $form->dropDownList($model,'credit_card_expiration_year', CustomerCreditCard::cardExpirationYears(), array('empty'=>'Expiration Year', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'credit_card_expiration_year'); ?>
											</div>
										</div>
									</div>
									
									<div class="echeckFields" style="display:<?php echo $model->payment_method == 'eCheck' ? 'block':'none'; ?>">
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->textField($model,'echeck_account_number',array('placeholder'=>'Account Number', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'echeck_account_number'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->textField($model,'echeck_routing_number',array('placeholder'=>'Routing Number', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'echeck_routing_number'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
												<?php 
													$echeckAccountTypes = array(
														''=>'- Account Type -',
														'CHECKING'=>'CHECKING',
														'SAVINGS'=>'SAVINGS',
													);
												?>
												<?php echo $form->dropDownList($model,'echeck_account_type', $echeckAccountTypes, array('class'=>'form-control')); ?>
												<?php echo $form->error($model,'echeck_account_type'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
												<?php 
													$echeckEntityTypes = array(
														'' => '- Entity Name -',
														'Business'=>'Business',
														'Personal'=>'Personal',
													); 
												?>
												<?php echo $form->dropDownList($model,'echeck_entity_name', $echeckEntityTypes, array('class'=>'form-control')); ?>
												<?php echo $form->error($model,'echeck_entity_name'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->textField($model,'echeck_account_name',array('placeholder'=>'Name on Account', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'echeck_account_name'); ?>
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6" style="float:left; width:40%;">
											<?php echo $form->textField($model,'echeck_institution_name',array('placeholder'=>'Institution', 'class'=>'form-control')); ?>
											<?php echo $form->error($model,'echeck_institution_name'); ?>
											</div>
										</div>
									</div>

									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<label><?php echo CHtml::checkBox('differentBillingAddress',''); ?> <span>Billing Address if different</span> </label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'cc_address',array('placeholder'=>'Address', 'class'=>'form-control copy-address')); ?>
										<?php echo $form->error($model,'cc_address'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'cc_city', array('placeholder'=>'City', 'class'=>'form-control copy-city')); ?>
										<?php echo $form->error($model,'cc_city'); ?>
										</div>
										
										<div class="col-md-6" style="float:left; width:40%;">
											<div class="row">
												<div class="col-md-6" style="float:left; width:45%;">
													<?php echo $form->dropDownList($model,'cc_state',State::listStates(),array('empty'=>'-Select State-', 'class'=>'form-control copy-state')); ?>
													<?php echo $form->error($model,'cc_state'); ?>
												</div>
												<div class="col-md-offset-1 col-md-5" style="float:left; width:30%;">
													<?php echo $form->textField($model,'cc_zip', array('placeholder'=>'Zip', 'class'=>'form-control copy-zip input-mask-zip')); ?>
													<?php echo $form->error($model,'cc_zip'); ?>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Notes
											</h3>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
										<?php 
											if($this->pdfView !== true)
											{
												echo $form->textArea($model,'notes',array('placeholder'=>'Enter notes here...', 'class'=>'form-control')); 
											}
											else
											{
												echo '<div style="border:1px solid #000; padding:5px 9px;">'.$model->notes.'</div>';
											}
										?>
										
										<?php echo $form->error($model,'notes'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<h3 class="header smaller lighter blue">
												Terms
											</h3>
										</div>
									</div>
									
									<?php if($this->pdfView !== true){ ?>
									
									<div class="row">
										<div class="col-md-12">
										<embed id="termsPdf" src="<?php echo $contractPdfFile; ?>" class="col-md-12" style="height:200px; border:3px solid #333;">
										</div>
									</div>
									
									<div class="space-12"></div>
									<?php } ?>
									
									<div class="row">
										<div class="col-md-6">
											<label>
												<?php echo CHtml::checkBox('is_agreed','', array('class'=>'ace')); ?> 
												<span class="lbl">I have read and agree to the terms and conditions of this service</span> 
											</label>
										</div>
										<div class="col-md-6">
											<label>
												<?php echo $form->checkBox($model, 'send_weekly_emails', array('class'=>'ace', 'uncheckValue'=>0)); ?> 
												<span class="lbl">Please send me weekly policy review program support emails</span> 
											</label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6" style="float:left; width:40%;">
										<?php echo $form->textField($model,'signature',array('placeholder'=>'Signature', 'class'=>'form-control')); ?>
										<?php echo $form->error($model,'signature'); ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<small>IP Address logged at <?php echo date('H:i:s'); ?>, <?php echo $_SERVER['REMOTE_ADDR']; ?></small>
										</div>
									</div>
												
									<br/>
									
									
									<?php if($this->pdfView !== true){ ?>
									<div class="row">
										<div class="col-md-12">
											<button id="submit-btn" class="btn btn-primary btn-xs pull-right">Purchase <i class="fa fa-arrow-right"></i></button>
										</div>
									</div>
									<?php } ?>
								<?php $this->endWidget(); ?>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>