$( function(){
	
	$(document).ready( function(){ 
	
		$(document).on("click", ".next-lead-btn", function(e){
		
			e.preventDefault();

			if( $(this).hasClass("next-lead-btn-disabled") ) 
			{
				return false;
			}
			else
			{
				$(location).attr("href", $(this).prop("href"));
			}
			
		});
		
		//manual dial
		$(document).on("click", ".num", function() { 
			var num = $(this);
			var text = $.trim(num.find(".txt").clone().children().remove().end().text());
			var telNumber = $("#manualDialInput");
			
			$(telNumber).val(telNumber.val() + text);
		});

		$(document).on("click", ".manual-dial-btn", function() {
			
			var dialed_phone_number = $("#manualDialInput").val();
			
			if( $("#manualDialInput").val() != "" )
			{
				if( webphone_api.isincall() )
				{
					webphone_api.API_Dtmf(3, dialed_phone_number);
					$("#manualDialInput").val("");
					
					return true; 
				}
				else
				{
					Call($("#manualDialInput").val());
					$("#manualDialInput").val("");
					$("#dialPadModal .close-modal-btn").click();
				}
			}
		});

		$(document).on("click", ".dial-pad-btn", function(){
			
			$("#dialPadModal").modal("show");
			
		});

		//main dialer buttons
		$(document).on("click", ".dial-phonenumber-btn", function(){
				
			var this_element = $(this);
			
			var lead_id = $(this).attr("lead_id");
			var list_id = $(this).attr("list_id");
			var customer_id = $(this).attr("customer_id");
			var company_id = $(this).attr("company_id");
			var lead_phone_number = $(this).attr("lead_phone_number");
			var phone_type = $(this).attr("phone_type");
			var skill_id = $(this).attr("skill_id");
			
			var current_dial_count = parseInt( this_element.closest("tr").find("td:eq(2)").find("span:eq(1)").text() );
			
			var ajaxSending = false;
			
			//require agents to submit the disposition for the current call before allowing them to dial the next number
			
			if( $("#leadPhoneNumbers select.dispo-select:enabled").length > 0 && this_element.hasClass("dial-phonenumber-btn") )
			{
				alert("Please enter a disposition for the last number dialed before dialing another phone number");
				return false;
			}
			
			if( !ajaxSending )
			{
				ajaxSending = true;
				
				// this_element.removeClass("green");
				this_element.removeClass("dial-phonenumber-btn");
				// this_element.addClass("grey");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/webphone/dial",
					type: "post",
					dataType: "json",
					data: {
						"ajax": 1,	
						"lead_id": lead_id,
						"list_id": list_id,
						"customer_id": customer_id,
						"company_id": company_id,
						"lead_phone_number": lead_phone_number,
						"phone_type": phone_type,
						"skill_id": skill_id,
					},
					complete: function(){
						
						ajaxSending = false;
						
					},
					success: function(response) { 

						if( response.status == "success" )
						{
							this_element.closest("tr").find("td select,textarea").attr("call_history_id", response.call_history_id);

							this_element.closest("tr").find("td select").prop("disabled", false);
							
							this_element.closest("tr").addClass("success");
							
							$(".dialer-function-btn-container .btn").removeClass("disabled");
							
							current_call_history_id = response.call_history_id;
							
							this_element.closest("tr").find("td:eq(2)").find("span:eq(1)").text( current_dial_count + 1 );
							
							$(".next-lead-btn").addClass("next-lead-btn-disabled");
							
							Call('81' + lead_phone_number);
						}
						else
						{
							alert("Dial Failed...");
							this_element.addClass("dial-phonenumber-btn");
						}
					
						ajaxSending = false;
						// $.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
					}
				});
			}
		});
		

		$(document).on("click", ".end-call-btn", function(){
			
			Hangup();
			
		});
		
		$(document).on("click", ".hold-call-btn", function(){
			
			Hold();
			
		});
		
		$(document).on("click", ".transfer-call-btn", function(){
			
			var phone_number = prompt('Enter destination number', '');
			
            if( phone_number !== null )
            {
				Transfer(phone_number);
			}
			
		});
		
		$(document).on("click", ".transfer-list-btn", function(){
			
			var phone_number = $(this).attr("phone_number");
			
			if( phone_number !== null ) 
            {
				Transfer(phone_number);
			}
		});
		
		// $(".conference-call-btn").on("click", function(){
			
			// var phone_number = prompt('Enter destination number', '');
			
            // if( phone_number !== null )
            // {
				// Conference(phone_number);
			// }
			
		// });
	
		$(document).on("click", ".conference-list-btn", function(){
			
			var phone_number = $(this).attr("phone_number");
			
			if( phone_number !== null )
            {
				Conference(phone_number);
			}
			
		});
		
	});
}); 