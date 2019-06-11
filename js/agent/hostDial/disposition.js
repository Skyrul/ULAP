$( function(){
	
	$(document).ready( function(){
			
		$(document).on("change", ".dispo-select", function() {

			var element_container = $(this).closest("td").find(".dispo-detail-container");
			var value = $(this).val();
				
			if( value != "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/updateDisposition",
					type: "post",
					dataType: "json",
					data: { "ajax": 1, "value": value, "call_history_id":current_call_history_id },
					beforeSend: function(){
						
						element_container.html("<div class=\"text-center\"><br><br> Loading...</div>");
						
					},
					success: function(response) { 
					
						if( response.status == "success" )
						{
							element_container.html(response.html);
							initializeDatePicker();
						}
						else
						{
							element_container.empty();
						}
					}
				});
			}
			else
			{
				element_container.empty();
			}
		});
		
		var dispositionSending = false;
		
		$(document).on("click", ".disposition-submit-btn", function() {
			
			var this_element = $(this);
			
			var selected_option = this_element.closest("td").find("select:eq(0) option:selected");
			
			var dispo_id = this_element.closest("td").find("select:eq(0)").val();
			var dispo_text = this_element.closest("td").find("select:eq(0) option:selected").text();
			
			var dispo_detail_id = this_element.closest("td").find("select:eq(1)").val();
			var dispo_detail_text = this_element.closest("td").find("select:eq(1) option:selected").text();
			var dispo_detail_is_required = this_element.closest("td").find("select:eq(1)").attr("sub_dispo_is_required");
			
			var note = this_element.closest("td").find("textarea").val();
			
			var callback_date = this_element.closest("td").find(".callback-date").val();
			var callback_date_hour = this_element.closest("td").find(".callback-date-hour").val();
			var callback_date_minute = this_element.closest("td").find(".callback-date-minute").val();
			var callback_date_time = this_element.closest("td").find(".callback-date-time").val();
			
			var callback_time = callback_date_hour + ":" + callback_date_minute + " " + callback_date_time;
			
			var phone_type = this_element.closest("tr").find("td:last-child a").attr("phone_type");
			
			var lead_phone_number = this_element.closest("tr").find("td:last-child a").attr("lead_phone_number");

			var errors  = "";
			
			if( dispo_id == "" )
			{
				errors += "Disposition is required. \n";
			}
			
			if( dispo_detail_is_required == 1 && dispo_detail_id == "" )
			{
				errors += "Disposition Detail is required. \n";
			}
			
			if( this_element.closest("td").find(".callback-date").length > 0 && callback_date == "" )
			{
				errors += "Callback date is required. \n";
			}
			
			if( this_element.closest("td").find(".callback-time").length > 0 && callback_time == "" )
			{
				errors += "Callback time is required.\n";
			}
			
			if( selected_option.attr("is_schedule_conflict") == 1 || selected_option.attr("is_location_conflict") == 1 || selected_option.attr("is_appointment_set") == 1 )
			{
				if( hasMadeCalendarChanges == 0 )
				{
					errors += "No changes made on calendar.";
				}
			}
				
			
			if( errors != "" )
			{
				alert("Please fix the following: \n\n" + errors);
			}
			else
			{
				if( !dispositionSending )
				{
					dispositionSending = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/agent/default/saveDisposition",
						type: "post",
						dataType: "json",
						data: { 
							"ajax": 1,
							"dispo_text": dispo_text,
							"dispo_id": dispo_id,
							"dispo_detail_text": dispo_detail_text,
							"dispo_detail_id": dispo_detail_id,
							"call_history_id": current_call_history_id,
							"note": note,
							"phone_type": phone_type,
							"lead_phone_number": lead_phone_number,
							"callback_date": callback_date,
							"callback_time": callback_time,
						},
						success: function(response) { 

							dispositionSending = false;
						
							if( response.status == "success" )
							{
								// $(".next-lead-btn").attr("href", yii.urls.absoluteUrl + "/agent/webphone/index?action=nextLead&lead_hopper_id=" + response.lead_hopper_id);
								$(".next-lead-btn").attr("lead_hopper_id", response.lead_hopper_id);
								
								this_element.closest("td").find("textarea").prop("disabled", true);
								this_element.closest("td").find("select:eq(0)").prop("disabled", true);
								
								this_element.prop("disabled", true);
								
								enableNextLeadButton();
								
								if( response.html != "" )
								{
									$("#leadHistoryList .items").prepend(response.html);
								}
							}
							else
							{
								alert(response.message);
							}
						
							// $.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
						}
					});
				}
			}
		});
		
	});
	
});