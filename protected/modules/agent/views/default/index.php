<?php 

$this->pageTitle = Yii::app()->name . ' - Dialer';

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/css/dialer.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
$cs->registerCssFile($baseUrl . '/template_assets/css/datepicker.css');

$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } .timeline-container::before { content:none !important; }');

$cs->registerCss(uniqid(), '
    .spinner-up, .spinner-down{ 
		font-size: 10px !important;
		height: 16px !important;
		line-height: 8px !important;
		margin-left: 0 !important;
		padding: 0 !important;
		width: 22px !important;
	}
	
	.row{ margin:3px 0; }
	
	.external-event:hover{
		background-color: #5B5B5B;
	}
	
	
	.dropdown-submenu {
		position: relative;
	}

	.dropdown-submenu>.dropdown-menu {
		top: 0;
		left: 100%;
		margin-top: -6px;
		margin-left: -1px;
		-webkit-border-radius: 0 6px 6px 6px;
		-moz-border-radius: 0 6px 6px;
		border-radius: 0 6px 6px 6px;
	}

	.dropdown-submenu:hover >.dropdown-menu {
		display: block;
	}

	.dropdown-submenu > div:after {
		display: block;
		content: " ";
		float: right;
		width: 0;
		height: 0;
		border-color: transparent;
		border-style: solid;
		border-width: 5px 0 5px 5px;
		border-left-color: #ccc;
		margin-top: 5px;
		margin-right: -10px;
	}
	
	.dropdown-submenu.pull-left {
		float: none;
	}

	.dropdown-submenu.pull-left >.dropdown-menu {
		left: -100%;
		margin-left: 10px;
		-webkit-border-radius: 6px 0 6px 6px;
		-moz-border-radius: 6px 0 6px 6px;
		border-radius: 6px 0 6px 6px;
	}
	
	.dropdown-menu div {
		text-align:center;
	}
	
	.dropdown-menu div span { 
		padding: 0 10px;
	}
	
	.dropdown-menu div span:hover { 
		cursor:pointer;
		color:#FFFFFF;
		background-color:#4F99C6;
	}
');

$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScriptFile($baseUrl.'/js/calendar/calendar.js',  CClientScript::POS_END);


$leadId = $lead != null ? $lead->id : '';
$calendarId = $calendar != null ? $calendar->id : '';
$customerId = $customer != null ? $customer->id : '';
$skillId = $list != null ? $list->skill_id : '';

$hasMadeCalendarChanges = 0;

$leadCallHistory = LeadCallHistory::model()->findByPk($leadCallHistoryId);

if( $leadCallHistory && $leadCallHistory->calendar_appointment_id != null )
{
	$hasMadeCalendarChanges = 1;
}

$cs->registerScript(uniqid(), '

	var current_skill_id = "'.$skillId.'";
	var current_lead_id = "'.$leadId.'";
	var current_calendar_id = "'.$calendarId.'";
	var customer_id = "'.$customerId.'";
	var viewer = "agent";
	var is_host_dialer = "'.Yii::app()->user->account->getIsHostDialer().'";
	
	var current_call_history_id = "'.$leadCallHistoryId.'";
	
	var hasMadeCalendarChanges = "'.$hasMadeCalendarChanges.'";
	
	function initializeDatePicker()
	{
		$(".datepicker").datepicker({
			autoclose: true,
			todayHighlight: true
		});
	}
	
	function enableNextLeadButton()
	{
		if( $(".end-call-btn").hasClass("disabled") && $("#leadPhoneNumbers").find("tr td select.dispo-select:enabled").length == 0 )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/updateLeadHopper",
				type: "post",
				dataType: "json",
				data: { "ajax": 1, "current_lead_id":current_lead_id },
				success: function(response) { 
					
					$(".next-lead-btn").removeClass("next-lead-btn-disabled");	
					
				}
			});
		}
	}
	
	function validateEmail(email) 
	{
		var re = /\S+@\S+\.\S+/;
		return re.test(email);
	}
	
', CClientScript::POS_HEAD);

$cs->registerScript(uniqid(), '
	$(document).ready(function () {
	
		initializeDatePicker();
		
		setInterval(function(){ 
		
			$("#calendar").fullCalendar("render"); 
		
			//lead hangup listener
			if( current_call_history_id != "" && !$(".end-call-btn").hasClass("disabled") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/hangupListener",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "call_history_id":current_call_history_id },
					success: function(response) { 

						if( response.call_status == 1 )
						{
							$(".end-call-btn").click();
						}
					}
				});
			}
		
		}, 1000);

		$(".scrollable").each(function () {
			var $this = $(this);
			$(this).ace_scroll({
				size: $this.data("size") || 380,
			});
		});
		
		$(".num").click(function () {
			var num = $(this);
			var text = $.trim(num.find(".txt").clone().children().remove().end().text());
			var telNumber = $("#manualDialInput");
			$(telNumber).val(telNumber.val() + text);
		});
	
	
		$(document).on("click", ".calendar-details-btn", function(){
			
			$("#calendar-details").animate({width: "toggle"},350);
			
			if( $(".calendar-details-btn > i").hasClass("fa-arrow-right") )
			{
				$(".calendar-details-btn").html("<i class=\"ace-icon fa fa-arrow-left bigger-110 icon-only\"></i>");
				
				$("#calendar-wrapper").removeClass("col-sm-12").addClass("col-sm-8");
				
				$("#calendar").fullCalendar("render");
			}
			else
			{
				$(".calendar-details-btn").html("<i class=\"ace-icon fa fa-arrow-right bigger-110 icon-only\"></i>");
				
				$("#calendar-wrapper").removeClass("col-sm-8").addClass("col-sm-12");

				$("#calendar").fullCalendar("render");
			}
			
		});
		
		$(document).on("change", "#office-select", function(){
			
			var office_id = $(this).val();
			
			if( office_id != "" )
			{
				// $(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id);
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/ajaxUpdateCalendarOptions",
					type: "post",
					dataType: "json",
					data: {"ajax":1, "office_id":office_id, "current_lead_id":current_lead_id },
					success: function(response) { 

						if( response.calendar_html != "" )
						{
							$(".calendar-info-wrapper").html(response.calendar_html);
							
							current_calendar_id = response.first_calendar_id;
						}
						
						if( response.office_html != "" )
						{
							$(".office-info-wrapper").html(response.office_html);
						}
					}
				});
			}
		});
		
		$(document).on("click", ".load-calendar-btn", function(){

			var office_id = $("#office-select").val();
			var calendar_id = $("#calendar-select").val();
			
			$(this).html("Loading <i class=\"fa fa-cog fa-spin fa-lg\"></i>");
			$(this).prop("disabled", true);
			
			if( calendar_id != "" )
			{
				current_calendar_id = calendar_id;
				$("#calendar").fullCalendar("refetchEvents");
				
				// $(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id +  "&calendar_id=" + calendar_id);
			}
		});
		
		$(document).on("click", ".lead-history-submit-btn", function(){
		
			if( $("#LeadHistory_note").val() != "" )
			{
				data = $("#leadHistoryForm").serialize();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/createLeadHistory",
					type: "post",
					dataType: "json",
					data: data,
					success: function() { 

						$("#LeadHistory_note").val("");
					
						$.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
					}
				});
			}
			
		});
		
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
			
			if( this_element.hasClass("popup-delay-disabled") )
			{
				return false;
			}
			
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
					url: yii.urls.absoluteUrl + "/agent/default/dial",
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
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/endCall",
				type: "post",
				dataType: "json",
				data: { "ajax": 1, "call_history_id": current_call_history_id, "current_lead_id":current_lead_id },
				success: function(response) { 

					if( response.status == "success" )
					{
						$(".dialer-function-btn-container .btn").addClass("disabled");
						$("table tr.success").removeClass("success");

						$("#leadPhoneNumbers").find("a.green, a.grey").addClass("dial-phonenumber-btn");
						
						enableNextLeadButton();
					}
				
					// $.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
				}
			});
			
		});
		
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
							"callback_date": callback_date,
							"callback_time": callback_time,
						},
						success: function(response) { 

							dispositionSending = false;
						
							if( response.status == "success" )
							{
								$(".next-lead-btn").attr("href", yii.urls.absoluteUrl + "/agent/default/index?action=nextLead&lead_hopper_id=" + response.lead_hopper_id);
								
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
		
		
		$(document).on("click", "a.edit-lead-info", function(){
			
			var this_element = $(this);
			var field_name = $(this).attr("field_name");
			var lead_id = $(this).attr("lead_id");
			var phone_number = this_element.closest("tr").find("a.green").attr("lead_phone_number");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/editLeadInfo",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "lead_id":lead_id, "field_name":field_name, "phone_number":phone_number },
				success: function(response) {
					
					if(response.status  == "success")
					{
						modal = response.html;
					}
					else
					{
						var modal = 
						\'<div class="modal fade">\
						  <div class="modal-dialog">\
						   <div class="modal-content">\
							 <div class="modal-body">\
							   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
								<p>Sorry but an error occured. Please try again later.</p> \
							 </div>\
						  </div>\
						 </div>\
						</div>\';
					}
					
					var modal = $(modal).appendTo("body");
					
					modal.find(".input-mask-phone").mask("(999) 999-9999");
					
					//prevent enter key form submit unless its the email field
					modal.find("form").on("keypress", function(e){
						
						if( e.keyCode == 13 )
						{
							e.preventDefault();
							
							if( field_name == "email_address" )  
							{
								modal.find(\'button[data-action=save]\').trigger("click");
							}
							else
							{
								return false;
							}
						}
						
						return true;
					});
					
					modal.find(\'button[data-action=save]\').on("click", function() {
						
						errors = "";
						
						if( field_name == "email_address" && !validateEmail( modal.find("#leadEmailAddressInput").val() ) )
						{
							errors += "Please enter a valid email address.";
						}
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						
						data = modal.find("form").serialize() + "&ajax=1";
						
						$.ajax({
							url: yii.urls.absoluteUrl + "/agent/default/editLeadInfo",
							type: "post",
							dataType: "json",
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									if( response.updated_field_name == "lead_name" )
									{
										this_element.closest(".profile-info-value").find("span").text(response.updated_values.first_name + " " + response.updated_values.last_name);
									}
									
									if( response.updated_field_name == "partner_name" )
									{
										this_element.closest(".profile-info-value").find("span").text(response.updated_values.partner_first_name + " " + response.updated_values.partner_last_name);
									}
									
									if( response.updated_field_name == "email_address" )
									{
										this_element.closest(".profile-info-value").find("span").text(response.updated_values.email_address);
									}
									
									if( response.updated_field_name == "address" )
									{
										this_element.closest(".profile-info-value").find("span").text(response.updated_values.address);
									}
									
									
									if( (response.updated_field_name == "home_phone_number" || response.updated_field_name == "mobile_phone_number" || response.updated_field_name == "office_phone_number" ) &&  response.html != "" )
									{
										$("#leadPhoneNumbers").parent().html(response.html);
									}
						
									modal.modal("hide");
								}
							}
						});
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
		});
		
		$(document).on("change", ".edit-lead-info", function() {
			
			var this_element = $(this);
			var field_name = $(this).attr("field_name");
			var field_value = $(this).val();
			var lead_id = $(this).attr("lead_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/editLeadInfo",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "lead_id":lead_id, "field_name":field_name, "field_value":field_value },
				success: function(response) {
					
					if( response.updated_field_name == "timezone" )
					{
						this_element.closest(".profile-info-value").find("div.text-right").text(response.timezone_date_time);
						
						$(".dialpad-time").text(response.timezone_time);
					}
					
				}
			});
			
		});
		
		
		$("a[data-toggle=\'tab\']").on("shown.bs.tab", function (e) { 
		
			if( typeof initMap != "undefined" ) 
			{
				initMap(); 
				
				google.maps.event.trigger(map, "resize");
			}
			
		});
		
		$(document).on("click", ".get-map-directions", function(){
			
			$("#mapTab").fadeIn();
			$("#googlemap").fadeIn();
		});

		$(document).on("click", ".close-map", function(){
			
			$("#dialerTab").click();
			$("#mapTab").fadeOut();
			$("#googlemap").fadeOut();
			
		});
		
		
		//LEAD SEARCH FUNCTIONS
		
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
			
			if( $(this).data("calling-time") == "Next Shift" && (is_host_dialer == "" || is_host_dialer == 0) )
			{
				continueLoadToHopper = confirm("This lead is not scheduled to be called yet. \n If you proceed with calling, an email will be sent to your supervisor.");
				is_emailSupervisor = true;
			}
			
			if(continueLoadToHopper)
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/loadLeadToHopper",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id, "type":type, "is_emailSupervisor": is_emailSupervisor },
					success: function(response) {
						
						if(response.status  == "success")
						{
							$(location).attr("href", yii.urls.absoluteUrl + "/agent/default/index?action=nextLead&search_lead_id=" + response.search_lead_id);
						}

					}
				});
			}
		});
		
		
		//Manual Dial

		$(".manual-dial-btn").on("click", function() {
			
			var dialed_phone_number = $("#manualDialInput").val();
			
			var skill_id = $(this).attr("skill_id");
			var company_id = $(this).attr("company_id");
			var customer_id = $(this).attr("customer_id");
			var list_id = $(this).attr("list_id");
			var lead_id = $(this).attr("lead_id");
			
			if( dialed_phone_number != "" && dialed_phone_number.length >= 10 && $("[lead_phone_number=" + dialed_phone_number + "]").length == 0 )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/manualDial",
					type: "post",
					dataType: "json",
					data: {
						"ajax": 1,
						"dialed_phone_number": dialed_phone_number,
						"skill_id": skill_id,
						"company_id": company_id,
						"customer_id": customer_id,
						"list_id": list_id,
						"lead_id": lead_id,
					},
					success: function(response) { 

						if( response.status == "success" )
						{
							$("#leadPhoneNumbers").append(response.html);
							
							$(".dialer-function-btn-container .btn").removeClass("disabled");
							$(".dialer-function-btn-container .btn").attr("call_history_id", response.call_history_id);
							
							$("#myModal").modal("hide");
						}

					}
				});
			
				$(".next-lead-btn").addClass("next-lead-btn-disabled");
			}
			else
			{
				$("[lead_phone_number=" + dialed_phone_number + "]").click();
				$("#myModal").modal("hide");
				
				$(".next-lead-btn").addClass("next-lead-btn-disabled");
			}
			
		});
		
		$("#myTab li:eq(1)").on("click", function(){

			$("#calendar").fullCalendar("refetchEvents");
			
		});
		
		$("#agentStatsTab").on("click", function(){

			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/loadAgentStats",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				beforeSend: function(){ 
					$("#agentStats").html("<h1 class=\"blue center lighter\">Loading agent stats please wait...</h1>");
				},
				error: function(){
					$("#agentStats").html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
				},
				success: function(response) {
					
					if(response.html != "")
					{
						$("#agentStats").html(response.html);
					}
					else
					{
						$("#agentStats").html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
					}
				},
			});
			
		});
		
		$(document).on("click", ".data-tab-submit-btn", function(){
			
			var this_button = $(this);
			
			var formData = $("#dataTabForm").serialize() + "&ajax=1";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/updateDataTab",
				type: "post",
				dataType: "json",
				data: formData,
				beforeSend: function(){ 
					this_button.html("Saving please wait...");
				},
				error: function(){
					this_button.html("Database Error <i class=\"fa fa-arrow-right\"></i>");
				},
				success: function(response) {
					
					alert(response.message);
					
					this_button.html("Save <i class=\"fa fa-arrow-right\"></i>");
				},
			});
			
		});
		
		$(document).on("change", ".data-tab-dropdown", function(){
			
			var list_id = $(this).val();
			var lead_id = $(this).attr("lead_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/ajaxLoadCustomData",
				type: "post",
				dataType: "json",
				data: { 		
					"ajax" : 1,	
					"list_id" : list_id,	
					"lead_id" : lead_id,	
				},
				success: function(r){
					$(".data-fields-tab").html(r.html);
					
				},
			});
		});
		
		var agentCallLogSending = false;
		
		$(document).on("click", ".export-agent-call-log-btn", function(){

			if( !agentCallLogSending )
			{
				var this_button = $(this);
				
				agentCallLogSending = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/exportAgentCallLog",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					beforeSend: function(){ 
						this_button.text("Exporting. Please wait...");
					},
					error: function(){
						this_button.text("Export Error. Please try again later...");
					},
					complete: function() {

						agentCallLogSending = false;
					
						this_button.html("File is sent to your email <i class=\"fa fa-check\"></i>");
					},
				});
			}
		});
	});
	
', CClientScript::POS_END);
?>		

<?php 
	$cs->registerScript(uniqid(), '
		var delayTime = "'.$leadHopperEntry->skill->customer_popup_delay.'";
	
		$(document).ready( function(){ 
		
			$(window).load(function()
			{
				$("#customerPopupModal").modal("show");
				
				$("#customerPopupModal .popupDelayCtr").text(delayTime);
					
				setInterval(function(){
					delayTime--;

					if( delayTime >= 0 )
					{
						$(".popupDelayCtr").text(delayTime);
					}
					
				},1000);
				
				setTimeout(function(){
					
					var button = $("#customerPopupModal").find(".modal-footer :button");
				
					button.removeClass("btn-default");
					button.addClass("btn-info");
					button.prop("disabled", false);
					button.text("Close");
					
					$(".dial-phonenumber-btn").removeClass("popup-delay-disabled");
					
				}, delayTime * 1000);
			});
		});
	',CClientScript::POS_END);
?>

<?php 
	if( $accountQueuePopup && $accountQueuePopup->show_popup == 1 && isset($leadHopperEntry->customer) )
	{
		$this->renderPartial('customerQueuePopup', array(
			'leadHopperEntry' => $leadHopperEntry,
		)); 
	}
?>

<?php 
	$showAppointmentTab = false;
	$showSurveyTab = false;
	$showScriptTab = false;
	$showDataTab = false;
	$showEmailSettingTab = false;
	
	//check appointment tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_appointment_tab == 1 )
	{
		$showAppointmentTab = true;
	}
	
	//check survey tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
	{
		$showSurveyTab = true;
	}
	
	//check email setting tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_email_setting == 1 )
	{
		$showEmailSettingTab = true;
	}
	
			
	//check script tab settings
	if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
	{
		if( $leadHopperEntry->confirmChildSkill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	elseif( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
	{
		if( $leadHopperEntry->rescheduleChildSkill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	else
	{
		if( $leadHopperEntry->skill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	
	//check data tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_data_tab == 1 )
	{
		$showDataTab = true;
	}
?>			
		
<div class="tabbable">
	<ul id="myTab" class="nav nav-tabs padding-12 tab-color-blue background-blue">
		<li class="active">
			<a id="dialerTab" href="#dialer" data-toggle="tab">
				<i class="ace-icon fa fa-phone bigger-120"></i>
				DIALER
			</a>
		</li>
		
		<?php if($showDataTab): ?>
		<li>
			<a id="dataTab" href="#data" data-toggle="tab">
				<i class="ace-icon fa fa-edit bigger-120"></i>
				DATA
			</a>
		</li>
		<?php endif; ?>

		<?php if( isset($list->skill) && $list->skill->enable_dialer_appointment_tab == 1): ?>
		<li>
			<a href="#appointments" data-toggle="tab">
				<i class="ace-icon fa fa-calendar bigger-120"></i>
				APPOINTMENTS
			</a>
		</li>
		<?php endif; ?>
		
		<?php if( isset($list->skill) && $list->skill->enable_survey_tab == 1): ?>
		<li>
			<a href="#surveys" data-toggle="tab">
				<i class="ace-icon fa fa-question-circle bigger-120"></i>
				SURVEY
			</a>
		</li>
		<?php endif; ?>
		
		<?php if( isset($list->skill) && $list->skill->enable_email_setting == 1): ?>
		<li>
			<a href="#surveys" data-toggle="tab">
				<i class="ace-icon fa fa-question-circle bigger-120"></i>
				EMAIL SETTING
			</a>
		</li>
		<?php endif; ?>
		
		
		<li>
			<a href="#leadSearch" data-toggle="tab">
				<i class="ace-icon fa fa-search bigger-120"></i>
				LEAD SEARCH
			</a>
		</li>
		
		<li>
			<a id="agentStatsTab" href="#agentStats" data-toggle="tab">
				<i class="ace-icon fa fa-user bigger-120"></i>
				AGENT STATS
			</a>
		</li>
		
		<li>
			<a id="mapTab" href="#googlemap" data-toggle="tab" style="display:none;">
				<i class="ace-icon fa fa-map-marker bigger-120"></i>
				MAP DIRECTIONS
				
				<span class="close close-map">×</span>
			</a>
		</li>
		
		<?php if($showScriptTab): ?>
		<li>
			<a id="scriptTab" href="#script" data-toggle="tab">
				<i class="ace-icon fa fa-file bigger-120"></i>
				SCRIPT
			</a>
		</li>
		<?php endif; ?>
	</ul>
	
	<div class="tab-content">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
	
		<div id="dialer" class="tab-pane fade in active">		
			<?php 
				if( $lead != null )
				{
					echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn next-lead-btn-disabled'));
					
					$this->renderPartial('leadInfo', array(
						'lead' => $lead,
						'list' => $list,
						'calendar' => $calendar,
						'customer' => $customer,
						'office' => $office,
						'officeOptions' => $officeOptions,
						'dispositionOptions' => $dispositionOptions,
						'dispositionHtmlOptions' => $dispositionHtmlOptions,
						'leadHistoryDataProvider' => $leadHistoryDataProvider,
						'leadHopperEntry' => $leadHopperEntry,
						'leadCallHistoryId' => $leadCallHistoryId,
						'accountQueuePopup' => $accountQueuePopup,
						'callerID' => $callerID,
					)); 
				}
				else
				{
					echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn'));
					
					$this->renderPartial('_empty_leadInfo');
				}
			?>
		</div>
	
		<?php if( $showAppointmentTab ): ?>
		<div id="appointments" class="tab-pane fade">	
			<?php
				if( $lead != null )
				{
					$this->renderPartial('appointmentsTab', array(
						'lead' => $lead,
						'list' => $list,
						'calendar' => $calendar,
						'customer' => $customer,
						'office' => $office,
						'calendarOptions' => $calendarOptions,
					)); 
				}
				else
				{
					$this->renderPartial('_empty_appointmentsTab');
				}
			?>
		</div>
		<?php endif; ?>
		
		<?php if( $showSurveyTab ): ?>
		<div id="surveys" class="tab-pane fade">	
			<?php
				if( $lead != null )
				{
					$this->renderPartial('_surveyTab', array(
						'lead' => $lead,
						'list' => $list,
						'customer' => $customer
					)); 
				}
			?>
		</div>
		<?php endif; ?>
		
		<?php if( $showEmailSettingTab ): ?>
		<div id="emailSetting" class="tab-pane fade">	
			<?php
				if( $lead != null )
				{
					$this->renderPartial('_emailSettingTab', array(
						'lead' => $lead,
						'list' => $list,
						'customer' => $customer
					)); 
				}
			?>
		</div>
		<?php endif; ?>
		
		<div id="googlemap" class="tab-pane fade in">		
			<?php
				if( $lead != null )
				{
					$this->renderPartial('_google_map', array(
						'cs' => $cs, 
						'lead' => $lead,
						'office' => $office,
					)); 
				}
			?>
		</div>
		
		<div id="leadSearch" class="tab-pane fade in">		
			<?php
				$this->renderPartial('leadSearchTab', array(
					'customer' => $customer, 
				)); 
			?>
		</div>
		
		<div id="agentStats" class="tab-pane fade in"></div>
		
		<?php if( $showScriptTab ): ?>
		<div id="script" class="tab-pane fade in">		
			<?php
				$this->renderPartial('scriptTab', array(
					'leadHopperEntry' => $leadHopperEntry, 
				)); 
			?>
		</div>		
		<?php endif; ?>
		
		<?php if( $showDataTab ): ?>
		<div id="data" class="tab-pane fade in">		
			<?php
				$this->renderPartial('dataTab', array(
					'lead' => $lead, 
				)); 
			?>
		</div>	
		<?php endif; ?>
	</div>

</div>


<button type="button" class="btn btn-info btn-xs calendar-details-btn" style="position: absolute; top: 0px; left: 0px; width: 30px; border-radius: 0px 10px 10px 0px; margin-top: 70px; padding: 5px;">
	<i class="ace-icon fa fa-arrow-left bigger-110 icon-only"></i>
</button>

<?php 
	$this->renderPartial('dialPad', array(
		'lead' => $lead,
		'list' => $list,
		'customer' => $customer,
	)); 
?>

<?php 
	if( $accountQueuePopup && $accountQueuePopup->show_popup == 1 )
	{
		$accountQueuePopup->show_popup = 0;
		$accountQueuePopup->save(false);
	}
?>