<?php 

$this->pageTitle = Yii::app()->name . ' - Dialer';

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/css/dialer.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
$cs->registerCssFile($baseUrl . '/template_assets/css/datepicker.min.css');

$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } .timeline-container::before { content:none !important; }');

$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScriptFile($baseUrl.'/js/calendar/calendar.js',  CClientScript::POS_END);


$leadId = $lead != null ? $lead->id : '';
$calendarId = $calendar != null ? $calendar->id : '';
$customerId = $customer != null ? $customer->id : '';

$cs->registerScript(uniqid(), '

	var current_lead_id = "'.$leadId.'";
	var current_calendar_id = "'.$calendarId.'";
	var customer_id = "'.$customerId.'";
	var viewer = "agent";
	
	var hasMadeCalendarChanges = 0;
	
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
			if( typeof $(".end-call-btn").attr("call_history_id") != "undefined" && !$(".end-call-btn").hasClass("disabled") )
			{
				var call_history_id = $(".end-call-btn").attr("call_history_id");

				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/hangupListener",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "call_history_id":call_history_id },
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
				$(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id);
			}
		});
		
		$(document).on("click", ".load-calendar-btn", function(){

			var office_id = $("#office-select").val();
			var calendar_id = $("#calendar-select").val();
			
			if( calendar_id != "" )
			{
				$(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id +  "&calendar_id=" + calendar_id);
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
				success: function(response) { 

					if( response.status == "success" )
					{
						this_element.closest("tr").find("td select,textarea").attr("call_history_id", response.call_history_id);
						
						this_element.closest("tr").find("td select").prop("disabled", false);
						
						this_element.closest("tr").addClass("success");
						
						$(".dialer-function-btn-container .btn").removeClass("disabled");
						$(".dialer-function-btn-container .btn").attr("call_history_id", response.call_history_id);
						
						this_element.closest("tr").find("td:eq(2)").find("span:eq(1)").text( current_dial_count + 1 );
						
						$(".next-lead-btn").addClass("next-lead-btn-disabled");
					}
				
					$.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
				}
			});
			
		});
		
		$(document).on("click", ".end-call-btn", function(){
			
			var call_history_id = $(this).attr("call_history_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/endCall",
				type: "post",
				dataType: "json",
				data: { "ajax": 1, "call_history_id": call_history_id, "current_lead_id":current_lead_id },
				success: function(response) { 

					if( response.status == "success" )
					{
						$(".dialer-function-btn-container .btn").addClass("disabled");
						$("table tr.success").removeClass("success");

						$("#leadPhoneNumbers").find("a.green, a.grey").addClass("dial-phonenumber-btn");
						
						enableNextLeadButton();
					}
				
					$.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
				}
			});
			
		});
		
		$(document).on("change", ".dispo-select", function() {

			var element_container = $(this).closest("td").find(".dispo-detail-container");
			var call_history_id = $(this).attr("call_history_id");
			var value = $(this).val();
			
			
			if( value != "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/updateDisposition",
					type: "post",
					dataType: "json",
					data: { "ajax": 1, "value": value, "call_history_id":call_history_id },
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
		
		
		$(document).on("click", ".disposition-submit-btn", function() {
			
			var this_element = $(this);
			
			var selected_option = this_element.closest("td").find("select:eq(0) option:selected");
			
			var dispo_id = this_element.closest("td").find("select:eq(0)").val();
			var dispo_detail_id = this_element.closest("td").find("select:eq(1)").val();
			var call_history_id = this_element.closest("td").find("select:eq(0)").attr("call_history_id");
			var note = this_element.closest("td").find("textarea").val();
			
			var callback_date = this_element.closest("td").find(".callback-date").val();
			var callback_time = this_element.closest("td").find(".callback-time").val();
			
			var phone_type = this_element.closest("tr").find("td:last-child a").attr("phone_type");

			var errors  = "";
			
			if( dispo_id == "" )
			{
				errors += "Disposition is required. \n";
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
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/saveDisposition",
					type: "post",
					dataType: "json",
					data: { 
						"ajax": 1,
						"dispo_id": dispo_id,
						"dispo_detail_id": dispo_detail_id,
						"call_history_id": call_history_id,
						"note": note,
						"phone_type": phone_type,
						"callback_date": callback_date,
						"callback_time": callback_time,
					},
					success: function(response) { 

						if( response.status == "success" )
						{
							$(".next-lead-btn").attr("href", yii.urls.absoluteUrl + "/agent/default/index?action=nextLead&lead_hopper_id=" + response.lead_hopper_id);
							
							this_element.closest("td").find("textarea").prop("disabled", true);
							this_element.closest("td").find("select:eq(0)").prop("disabled", true);
							
							this_element.prop("disabled", true);
							
							enableNextLeadButton();
						}
					
						$.fn.yiiListView.update("leadHistoryList", { data: {current_lead_id: current_lead_id} });
					}
				});
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
									
									
									if(response.html != "" )
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
		
			initMap(); 
			
			google.maps.event.trigger(map, "resize");
			
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
		
		$(document).on("click", "#leadSearchTab", function(){
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/leadSearch",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
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

					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});			
		});
		
		$(document).on("keyup", ".lead-search-input", function(e) {
	
			e.preventDefault();
			
			var lead_search_query = $(".lead-search-input").val();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/leadSearch",
				type: "post",
				dataType: "json",
				data: { "lead_search_query":lead_search_query },
				success: function(response) {
					
					if(response.status  == "success")
					{
						$("#leadSearchTable tbody").html(response.html);
					}

				}
			});
		});
		
		$(document).on("click", ".load-lead-to-hopper", function(e) {
	
			e.preventDefault();
			
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/loadLeadToHopper",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id":id },
				success: function(response) {
					
					if(response.status  == "success")
					{
						$(location).attr("href", yii.urls.absoluteUrl + "/agent/default/index?action=nextLead&search_lead_id=" + response.search_lead_id);
					}

				}
			});
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
	});
	
', CClientScript::POS_END);
?>		
			
<div class="tabbable">
	<ul id="myTab" class="nav nav-tabs padding-12 tab-color-blue background-blue">
		<li class="active">
			<a id="dialerTab" href="#dialer" data-toggle="tab">
				<i class="ace-icon fa fa-phone bigger-120"></i>
				DIALER
			</a>
		</li>

		<li>
			<a href="#appointments" data-toggle="tab">
				<i class="ace-icon fa fa-calendar bigger-120"></i>
				APPOINTMENTS
			</a>
		</li>
		
		<li>
			<a id="leadSearchTab" href="javascript:void(0);">
				<i class="ace-icon fa fa-search bigger-120"></i>
				LEAD SEARCH
			</a>
		</li>
		
		<li>
			<a id="mapTab" href="#googlemap" data-toggle="tab" style="display:none;">
				<i class="ace-icon fa fa-map-marker bigger-120"></i>
				MAP DIRECTIONS
				
				<span class="close close-map">×</span>
			</a>
		</li>
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
					)); 
				}
				else
				{
					echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn'));
					
					$this->renderPartial('_empty_leadInfo');
				}
			?>
		</div>
	
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
	</div>

</div>


<button type="button" class="btn btn-info btn-xs calendar-details-btn" style="position: absolute; top: 0px; left: 0px; width: 40px; border-radius: 0px 15px 15px 0px; margin-top: 70px; padding: 8px;">
	<i class="ace-icon fa fa-arrow-left bigger-110 icon-only"></i>
</button>


<?php 
	$this->renderPartial('dialPad', array(
		'lead' => $lead,
		'list' => $list,
		'customer' => $customer,
	)); 
?>