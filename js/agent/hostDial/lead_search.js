$( function(){
	
	$(document).ready( function(){
		
		$(document).on("click", ".lead-search-submit", function(e) {
	
			e.preventDefault();
			
			var this_button = $(this);
			
			var lead_search_query = $(".lead-search-input").val();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/leadSearch",
				type: "post",
				dataType: "json",
				data: { "lead_search_query":lead_search_query },
				beforeSend: function(){
					this_button.text("Searching...");
				},
				success: function(response) {
					
					if(response.status  == "success")
					{
						$("#leadSearchTable").html(response.html);
					}
					else
					{
						alert(response.message);
					}
					
					this_button.text("Search");
				}
			});
		});
		
		$(document).on("click", ".load-lead-to-hopper", function(e) {
	
			e.preventDefault();
			
			var id = $(this).prop("id");
			var type = $(this).parent().find("select").val();
			var continueLoadToHopper = true;
			var is_emailSupervisor = false;
			
			var this_button = $(this);
			
			if( $(this).data("calling-time") == "Next Shift" && (is_host_dialer == "" || is_host_dialer == 0) )
			{
				continueLoadToHopper = confirm("This lead is not scheduled to be called yet. \n If you proceed with calling, an email will be sent to your supervisor.");
				is_emailSupervisor = true;
			}
			
			if(continueLoadToHopper)
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/webphone/loadLeadToHopper",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id, "type":type, "is_emailSupervisor": is_emailSupervisor },
					beforeSend: function(){
						this_button.html("Loading...");
						$("div.alert").remove();
					},
					success: function(response) {
						
						this_button.html("Load <i class=\"fa fa-arrow-right\"></i>");
						
						if(response.status  == "success")
						{ 
							// $(location).attr("href", yii.urls.absoluteUrl + "/agent/webphone/index?action=nextLead&search_lead_id=" + response.search_lead_id);
							
							current_skill_id = response.current_skill_id;
							current_lead_id = response.current_lead_id;
							current_calendar_id = response.current_calendar_id;
							customer_id = response.customer_id;
							current_call_history_id = response.current_call_history_id;
		
							//update caller ID
							if( response.caller_id != webphone_api.getparameter("displayname") )
							{
								webphone_api.setparameter('displayname', response.caller_id);
								webphone_api.start();
							}
							
							$(".dialingAs").text( response.caller_id );
							
							//main tabs
							if( response.html.tabs != "" )
							{ 
								$(".nav-tabs").html( response.html.tabs );
							}
							
							if( response.html.tab_contents != "" )
							{ 
								$(".tab-content div:eq(0)").after( response.html.tab_contents );
							}
							
							//lead info
							if( response.html.title != "" )
							{ 
								$(".lead-info-title-container").html( response.html.title );
							}
							
							if( response.html.lead_info_fields != "" )
							{ 
								$(".lead-info-fields-container").html( response.html.lead_info_fields );
							}
							
							if( response.html.lead_info_dialer_buttons != "" )
							{ 
								$(".dialer-function-btn-container").html( response.html.lead_info_dialer_buttons );
							}
							
							if( response.html.lead_info_lead_phone_numbers != "" )
							{ 
								$(".lead-info-phone-numbers-container").html( response.html.lead_info_lead_phone_numbers );
							}
							
							//customer info
							if( response.html.customer_info_fields != "" )
							{ 
								$(".customer-info-fields-container").html( response.html.customer_info_fields );
							}
							
							//lead history
							if( response.html.lead_history != "" )
							{ 
								$(".lead-history-container").html( response.html.lead_history );
							}
							
							$("#dialerTab").click();
						}

					}
				});
			}
		});
		
	});
	
});