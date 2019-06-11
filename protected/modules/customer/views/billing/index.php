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
	", CClientScript::POS_END);

	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("click", ".add-new-cc-btn", function(){
				
				var customer_id = "'.$customer->id.'";

				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/createCreditCard",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "customer_id": customer_id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find(".input-mask-phone").mask("(999) 999-9999");
						modal.find(".input-mask-zip").mask("99999");
						
						modal.find("#CustomerCreditCard_credit_card_number").on("keyup", function(){
							
							if (this.value != this.value.replace(/[^0-9\.]/g, "")) {
								this.value = this.value.replace(/[^0-9\.]/g, "");
							}
							
							cc_number = modal.find("#CustomerCreditCard_credit_card_number").val().slice(-4);
							
							// cc_number = modal.find("#CustomerCreditCard_credit_card_number").val();
							modal.find(".cc-show-last-4").text(cc_number);
							
						});
						
						modal.find("#CustomerCreditCard_credit_card_type").on("change", function(){
							
							setTimeout(function() { 
								modal.find("#CustomerCreditCard_credit_card_number").focus(); 
								modal.find("#CustomerCreditCard_credit_card_number").blur();
								modal.find("#CustomerCreditCard_credit_card_number").trigger("keyup");
							}, 100);
							
							if( $(this).val() == "Amex" )
							{
								modal.find("#CustomerCreditCard_credit_card_number").attr("maxlength", "15");
								
								if( modal.find("#CustomerCreditCard_credit_card_number").val().length > 15 )
								{
									modal.find("#CustomerCreditCard_credit_card_number").val( modal.find("#CustomerCreditCard_credit_card_number").val().slice(0, -1) );
								}
								
								modal.find("#CustomerCreditCard_security_code").attr("maxlength", "4");
							}
							else
							{
								modal.find("#CustomerCreditCard_credit_card_number").attr("maxlength", "16");
								
								modal.find("#CustomerCreditCard_security_code").attr("maxlength", "3");
								
								if( modal.find("#CustomerCreditCard_credit_card_number").val().length > 16 )
								{
									modal.find("#CustomerCreditCard_credit_card_number").val( modal.find("#CustomerCreditCard_credit_card_number").val().slice(0, -1) );
								}
								
								if( modal.find("#CustomerCreditCard_security_code").val().length > 3 )
								{
									modal.find("#CustomerCreditCard_security_code").val( modal.find("#CustomerCreditCard_security_code").val().slice(0, -1) );
								}
							}
							
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
						
							$.each(modal.find("form input,select"), function(){
								
								if( $(this).val() == "" && $(this).prop("id") != "CustomerCreditCard_nick_name" )
								{
									errors++;								
								}
								
							});
							
							if( errors > 0 )
							{
								alert("Please fill in all the required fields.");
								return false;
							}
							
							if( modal.find("#CustomerCreditCard_credit_card_type").val() == "Amex" )
							{
								if( modal.find("#CustomerCreditCard_security_code").val().length < 4 )
								{
									errors++;
									
									alert("Security Code must be 4 characters long.");
									return false;	
								}
							}
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize() + "&customer_id=" + customer_id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/createCreditCard",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Saving Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("creditCardList", {});
										modal.find("button[data-action=save]").html("Save");
										
										if( response.status == "error" )
										{
											alert(response.message);
										}
										else
										{
											modal.find("form input,select").val("");
		
											modal.modal("hide");
										}
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal ", function(){
							modal.remove();
						});
					}
				});
			});
			
			$(document).on("click", ".view-cc-btn", function(){
			
				var id = $(this).prop("id");
			
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/viewCreditCard",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
				
			});
			
			//edit credit card
				
			$(document).on("click", ".edit-cc-btn", function(){
			
				var id = $(this).prop("id");
			
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/updateCreditCard",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("#CustomerCreditCard_credit_card_number").on("keyup", function(){
							
							if (this.value != this.value.replace(/[^0-9\.]/g, "")) {
								this.value = this.value.replace(/[^0-9\.]/g, "");
							}
							
							cc_number = modal.find("#CustomerCreditCard_credit_card_number").val().slice(-4);
							
							// cc_number = modal.find("#CustomerCreditCard_credit_card_number").val();
							modal.find(".cc-show-last-4").text(cc_number);
							
						});
						
						setTimeout(function(){ 
							modal.find("#CustomerCreditCard_credit_card_type").trigger("change"); 
							$("#CustomerCreditCard_credit_card_number").focus();
							$("#CustomerCreditCard_credit_card_number").blur(); 
						}, 100);
						
						modal.find("#CustomerCreditCard_credit_card_type").on("change", function(){
							
							setTimeout(function() { 
								modal.find("#CustomerCreditCard_credit_card_number").focus(); 
								modal.find("#CustomerCreditCard_credit_card_number").blur();
								modal.find("#CustomerCreditCard_credit_card_number").trigger("keyup");
							}, 100);
							
							if( $(this).val() == "Amex" )
							{
								modal.find("#CustomerCreditCard_credit_card_number").attr("maxlength", "15");
								
								if( modal.find("#CustomerCreditCard_credit_card_number").val().length > 15 )
								{
									modal.find("#CustomerCreditCard_credit_card_number").val( modal.find("#CustomerCreditCard_credit_card_number").val().slice(0, -1) );
								}
								
								modal.find("#CustomerCreditCard_security_code").attr("maxlength", "4");
							}
							else
							{
								modal.find("#CustomerCreditCard_credit_card_number").attr("maxlength", "16");
								
								modal.find("#CustomerCreditCard_security_code").attr("maxlength", "3");
								
								if( modal.find("#CustomerCreditCard_credit_card_number").val().length > 16 )
								{
									modal.find("#CustomerCreditCard_credit_card_number").val( modal.find("#CustomerCreditCard_credit_card_number").val().slice(0, -1) );
								}
								
								if( modal.find("#CustomerCreditCard_security_code").val().length > 3 )
								{
									modal.find("#CustomerCreditCard_security_code").val( modal.find("#CustomerCreditCard_security_code").val().slice(0, -1) );
								}
							}
							
						});
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
						
							$.each(modal.find("form input,select"), function(){
								
								if( $(this).val() == "" && $(this).prop("id") != "CustomerCreditCard_nick_name" )
								{
									errors++;								
								}
								
							});
	
							
							if( errors > 0 )
							{
								alert("Please fill in all the required fields.");
								return false;
							}
							
							if( modal.find("#CustomerCreditCard_credit_card_type").val() == "Amex" )
							{
								if( modal.find("#CustomerCreditCard_security_code").val().length < 4 )
								{
									errors++;
									
									alert("Security Code must be 4 characters long.");
									return false;	
								}
							}
							
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/updateCreditCard",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Saving Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("creditCardList", {});
	
										modal.find("form input,select").val("");
	
										modal.find("button[data-action=save]").html("Save");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
				
			});
			
			
			//set default credit card
			$(document).on("click", ".set-default-cc-btn", function(){
			
				var id = $(this).prop("id");
			
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/setDefaultCreditCard",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
					
						$.fn.yiiListView.update("creditCardList", {});
						$.fn.yiiListView.update("echeckList", {});
						
					}
				});
			});
			
			
			//delete credit card
			$(document).on("click", ".delete-cc-btn", function(){
			
				var id = $(this).prop("id");
				
				if( confirm("Are you sure you want to delete this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/billing/deleteCreditCard",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "id": id },
						success: function(response) {
						
							$.fn.yiiListView.update("creditCardList", {});
							
						}
					});
				}
			});
			
			
			
			// process transaction
			$(document).on("click", ".process-transaction-btn", function(){
				
				var customer_id = "'.$customer->id.'";
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/processTransaction",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "customer_id": customer_id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {
	
							var errors = 0;
						
							$.each(modal.find("form input,select"), function(){
								
								if( $(this).val() == "" )
								{
									errors++;								
								}
								
							});
							
							if( errors > 0 )
							{
								alert("Please fill in all the required fields.");
								return false;
							}
							
							if( !$.isNumeric( modal.find("#CustomerBilling_amount").val()) )
							{
								alert("Amount must be numeric.");
								return false;
							}
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize() + "&customer_id=" + customer_id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/processTransaction",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Processing Please Wait...");
									},
									success: function(response){
										
										modal.find("button[data-action=save]").html("Process");
										
										if( response.status == "success" )
										{
											$.fn.yiiListView.update("transactionList", {});
										

											modal.find("form input").val("");
											
											modal.modal("hide");
										}
										else
										{
											alert(response.message);
										}
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
			});
			
			
			//void transaction
			$(document).on("click", ".void-transaction-btn", function(){
			
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/voidTransaction",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
					
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {
	
							var errors = "";

							if( modal.find("#voidMemo").val() == "" )
							{
								errors += "Memo is required. \n\n";
							}
	
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{
								data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/voidTransaction",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Processing Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("transactionList", {});

										modal.find("form input").val("");

										modal.find("button[data-action=save]").html("Process");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					}
				});
			});
			
			
			//refund transaction
			$(document).on("click", ".refund-transaction-btn", function(){
			
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/refundTransaction",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
					
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {
	
							var errors = "";

							if( modal.find("#refundMemo").val() == "" )
							{
								errors += "Memo is required. \n\n";
							}
	
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{
								data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/refundTransaction",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Processing Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("transactionList", {});

										modal.find("form input").val("");

										modal.find("button[data-action=save]").html("Process");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
						
					}
				});
			});
			
			
			//partial refund transaction
			
			$(document).on("click", ".partial-refund-btn", function(){
			
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/partialRefundTransaction",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id},
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {
	
							var errors = "";

							if( !$.isNumeric( modal.find("#partialRefund_amount").val() ) )
							{
								errors += "Amount must be numeric. \n\n";
							}
							else
							{
								if( modal.find("#partialRefund_amount").val() == 0 )
								{
									errors += "Amount must be greater than 0. \n\n";
								}
								else
								{
									if( parseFloat(modal.find("#partialRefund_amount").val()) > parseFloat($(this).attr("transaction_amount")) )
									{
										errors += "Amount must not be greater than the amount billed. \n\n";
									}
								}
							}
							
							if( modal.find("#partialRefund_memo").val() == "" )
							{
								errors += "Memo is required. \n\n";
							}
	
							if( errors != "" )
							{
								alert(errors);
								return false;
							}
							else
							{
								data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/partialRefundTransaction",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Processing Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("transactionList", {});

										modal.find("form input").val("");

										modal.find("button[data-action=save]").html("Process");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
				
			});
			
			
			
			//add echeck
			$(document).on("click", ".add-new-echeck-btn", function(){
				
				var customer_id = "'.$customer->id.'";

				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/createEcheck",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "customer_id": customer_id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
							
							$.each(modal.find("form input,select"), function(){
								
								if( $(this).val() == "" && $(this).prop("id") != "CustomerEcheck_entity_name" )
								{
									errors++;								
								}
								
							});
							
							if( errors > 0 )
							{
								alert("Please fill in all the required fields.");
								return false;
							}
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize() + "&customer_id=" + customer_id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/createEcheck",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Saving Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("echeckList", {});
	
										modal.find("form input,select").val("");
	
										modal.find("button[data-action=save]").html("Save");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal ", function(){
							modal.remove();
						});
					}
				});
			});
			
			
			//edit echeck
			$(document).on("click", ".edit-echeck-btn", function(){
			
				var id = $(this).prop("id");
			
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/updateEcheck",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
							
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
							
							$.each(modal.find("form input,select"), function(){
								
								if( $(this).val() == "" && $(this).prop("id") != "CustomerEcheck_entity_name" )
								{
									errors++;								
								}
								
							});
							
							if( errors > 0 )
							{
								alert("Please fill in all the required fields.");
								return false;
							}
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/customer/billing/updateEcheck",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){							
										modal.find("button[data-action=save]").html("Saving Please Wait...");
									},
									success: function(response){
										
										$.fn.yiiListView.update("echeckList", {});
	
										modal.find("form input,select").val("");
	
										modal.find("button[data-action=save]").html("Save");
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal", function(){
							modal.remove();
						});
					}
				});
				
			});
			
			
			//delete echeck
			$(document).on("click", ".delete-echeck-btn", function(){
			
				var id = $(this).prop("id");
				
				if( confirm("Are you sure you want to delete this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/billing/deleteEcheck",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "id": id },
						success: function(response) {
						
							$.fn.yiiListView.update("echeckList", {});
							
						}
					});
				}
			});
			
			//set default echeck
			$(document).on("click", ".set-default-echeck-btn", function(){
			
				var id = $(this).prop("id");
			
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/setDefaultEcheck",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
					
						$.fn.yiiListView.update("creditCardList", {});
						$.fn.yiiListView.update("echeckList", {});
						
					}
				});
			});
		});
		
		
		//add credit
		$(document).on("click", ".add-new-credit-btn", function(){
				
			var customer_id = "'.$customer->id.'";

			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/billing/createCredit",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "customer_id": customer_id },
				success: function(response) {
						
					if(response.status  == "success")
					{
						modal = response.html;
					}
					else
					{
						return false;
					}
					
					var modal = $(modal).appendTo("body");
					
					modal.find("#CustomerCredit_type").on("change", function(){
						
						if( modal.find("#CustomerCredit_type").val() == 2 )
						{
							modal.find(".end-month-range").show();
							modal.find("#CustomerCredit_end_month").prop("disabled", false);
							modal.find("#CustomerCredit_end_year").prop("disabled", false);
						}
						else
						{
							modal.find(".end-month-range").hide();
							modal.find("#CustomerCredit_end_month").val("");
							modal.find("#CustomerCredit_end_month").prop("disabled", true);
							
							modal.find("#CustomerCredit_end_year").val("");
							modal.find("#CustomerCredit_end_year").prop("disabled", true);
						}
					});
					
					modal.find("button[data-action=save]").on("click", function() {

						var errors = 0;
						
						$.each(modal.find("form input,select:visible"), function(){

							if( $(this).val() == "" )
							{
								errors++;								
							}							
							
						});
						
						if( errors > 0 )
						{
							alert("Please fill in all the required fields.");
							return false;
						}
						
						if( !$.isNumeric( modal.find("#CustomerCredit_amount").val()) )
						{
							alert("Amount must be numeric.");
							return false;
						}
						
						if( errors == 0 )
						{
							data = modal.find("form").serialize() + "&customer_id=" + customer_id;
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/customer/billing/createCredit",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){							
									modal.find("button[data-action=save]").html("Saving Please Wait...");
								},
								success: function(response){
									
									if(response.status == "error")
									{
										modal.find("button[data-action=save]").html("Save");
										alert(response.message);
									}
									else
									{
										$.fn.yiiListView.update("creditList", {});

										modal.find("form input,select").val("");

										modal.find("button[data-action=save]").html("Save");
										
										modal.modal("hide");
									}
								},
							});
						}
					});
					
					modal.modal("show").on("hidden.bs.modal ", function(){
						modal.remove();
					});
				}
			});
		});
		
		
		//edit credit
		$(document).on("click", ".edit-credit-btn", function() {
			
			var id = $(this).prop("id");
		
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/billing/updateCredit",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id": id },
				success: function(response) {
						
					if(response.status  == "success")
					{
						modal = response.html;
					}
					else
					{
						return false;
					}
					
					var modal = $(modal).appendTo("body");
					
					modal.find("#CustomerCredit_type").on("change", function(){
						
						if( modal.find("#CustomerCredit_type").val() == 2 )
						{
							modal.find(".end-month-range").show();
							modal.find("#CustomerCredit_end_month").prop("disabled", false);
							modal.find("#CustomerCredit_end_year").prop("disabled", false);
						}
						else
						{
							modal.find(".end-month-range").hide();
							modal.find("#CustomerCredit_end_month").val("");
							modal.find("#CustomerCredit_end_month").prop("disabled", true);
							
							modal.find("#CustomerCredit_end_year").val("");
							modal.find("#CustomerCredit_end_year").prop("disabled", true);
						}
					});
					
					modal.find("button[data-action=save]").on("click", function() {

						var errors = 0;
						
						$.each(modal.find("form input,select:visible"), function(){
							
							if( $(this).val() == "" )
							{
								errors++;								
							}
							
						});
						
						if( errors > 0 )
						{
							alert("Please fill in all the required fields.");
							return false;
						}

						if( !$.isNumeric( modal.find("#CustomerCredit_amount").val()) )
						{
							alert("Amount must be numeric.");
							return false;
						}
						
						if( errors == 0 )
						{
							data = modal.find("form").serialize() + "&id=" + id;
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/customer/billing/updateCredit",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){							
									modal.find("button[data-action=save]").html("Saving Please Wait...");
								},
								success: function(response){
									
									
									if(response.status == "error")
									{
										modal.find("button[data-action=save]").html("Save");
										alert(response.message);
									}
									else
									{
										$.fn.yiiListView.update("creditList", {});

										modal.find("form input,select").val("");

										modal.find("button[data-action=save]").html("Save");
										
										modal.modal("hide");
									}
								},
							});
						}
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
			
		});
		
		
		//delete credit
		$(document).on("click", ".delete-credit-btn", function(){
		
			var id = $(this).prop("id");
			
			if( confirm("Are you sure you want to delete this?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/deleteCredit",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
					
						$.fn.yiiListView.update("creditList", {});
						
					}
				});
			}
		});
		
		
		// $(document).on("change", "#'.CHtml::activeId(new CustomerBilling,'contract_id').'", function(){
		
			// var id = $(this).val();
			
			// $("#contract-subsidy-credit-table").addClass("hidden");
			// $(".contract-subsidy-credit").addClass("hidden");
			// if(id > 0)
			// {
				// $("#contract-subsidy-credit-table").removeClass("hidden");
				// $("#contract-subsidy-credit-id-"+id).removeClass("hidden");
			// }
		// });
		
		$(document).on("change keyup", ".billing-subsidy-table", function(){
		
			var billingPeriod = $("#'.CHtml::activeId(new CustomerBilling, 'billing_period').'").val();
			var contractId = $("#'.CHtml::activeId(new CustomerBilling, 'contract_id').'").val();
			var customerId = "'.$customer->id.'";
			var amount =  $("#'.CHtml::activeId(new CustomerBilling, 'amount').'").val();
			var billing_type =  $("#'.CHtml::activeId(new CustomerBilling, 'billing_type').'").val();
			
			$("#contract-subsidy-credit-table-container").empty();
			
			if(billingPeriod != "" && contractId != "")
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/billing/getBillingContractCreditSubsidy",
					type: "get",
					dataType: "json",
					data: { 
					
						"ajax":1, 
						"customer_id": customerId,
						"contract_id": contractId,
						"billing_period": billingPeriod,
						"amount": amount,
						"billing_type": billing_type,
						
					},
					success: function(response) {
					
						if(response.status == "success")
						{
							$("#contract-subsidy-credit-table-container").html(response.content);
							$("#CustomerBilling_description").val(response.creditDescriptions);
						}
					}
				});
			}
		});
		
		
		
	', CClientScript::POS_END)
?>


<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'billing',
		'customer' => $customer,
	));
?>

<div class="page-header">
	<h1>Billing</h1>
</div>

<div class="row">
	<div class="col-sm-12">
	
		<div class="col-sm-4">	
		
			<?php if( Yii::app()->user->account->checkPermission('customer_billing_add_new_credit_card_button','visible') ){ ?>
				
				<button class="btn btn-primary btn-minier add-new-cc-btn"><i class="fa fa-plus"></i> Add New Credit Card</button>
			
			<?php } ?>
			
			<?php if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() ){ ?>
			
				<?php if( Yii::app()->user->account->checkPermission('customer_billing_add_new_echeck_button','visible') ){ ?>
					<button class="btn btn-primary btn-minier add-new-echeck-btn"><i class="fa fa-plus"></i> Add New eCheck</button>
				<?php } ?>
				
				<?php if( Yii::app()->user->account->checkPermission('customer_billing_add_new_credit_button','visible') ){ ?>
					<button class="btn btn-primary btn-minier add-new-credit-btn"><i class="fa fa-plus"></i> Add New Credit</button>
				<?php } ?>
			<?php } ?>
			
			<div class="space-12"></div>
			
			<div class="widget-box widget-color-blue2 light-border">
				<div class="widget-header widget-header-small">
					<h5 class="widget-title">Credit Cards</h5>

					<div class="widget-toolbar"></div>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<?php 
							$this->widget('zii.widgets.CListView', array(
								'id'=>'creditCardList',
								'dataProvider'=>$creditCardDataProvider,
								'itemView'=>'_credit_card_list',
								'template'=>'<table class="table table-bordered table-condensed table-hover">{items}</table>',
							)); 
						?>						
					</div>
				</div>
			</div>
			
			<div class="space-6"></div>
			
			<?php if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff()){ ?>
			<div class="widget-box widget-color-blue2 light-border">
				<div class="widget-header widget-header-small">
					<h5 class="widget-title">eCheck</h5>

					<div class="widget-toolbar"></div>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<?php 
							$this->widget('zii.widgets.CListView', array(
								'id'=>'echeckList',
								'dataProvider'=>$echeckDataProvider,
								'itemView'=>'_echeck_list',
								'template'=>'<table class="table table-bordered table-condensed table-hover">{items}</table>',
							)); 
						?>						
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="space-6"></div>
			
			<div class="widget-box widget-color-blue2 light-border">
				<div class="widget-header widget-header-small">
					<h5 class="widget-title">Credits</h5>

					<div class="widget-toolbar"></div>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<?php 
							$this->widget('zii.widgets.CListView', array(
								'id'=>'creditList',
								'dataProvider'=>$creditDataProvider,
								'itemView'=>'_credit_list',
								'template'=>'<table class="table table-bordered table-condensed table-hover">{items}</table>',
							)); 
						?>						
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-sm-8">	
			<?php if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('customer_billing_process_charge_button','visible') ){ ?>
			
				<button class="btn btn-primary btn-minier process-transaction-btn"><i class="fa fa-cog"></i> Process Charge</button>

			<?php }else { echo '<br>'; } ?>
			
			<div class="space-12"></div>
			
			<div class="widget-box widget-color-blue2 light-border">
				<div class="widget-header widget-header-small">
					<h5 class="widget-title">Transaction History</h5>

					<div class="widget-toolbar"></div>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<?php 
							$this->widget('zii.widgets.CListView', array(
								'id'=>'transactionList',
								'dataProvider'=>$transactionDataProvider,
								'itemView'=>'_transaction_list',
								'template'=>'<table class="table table-bordered table-condensed table-hover">{items}</table>',
							)); 
						?>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>