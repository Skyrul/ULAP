$( function(){
	
	function formAjaxSubmit(url, data)
	{
		$.ajax({
			url: url,
			type: 'post',
			dataType: 'json',
			data: data,
			success: function(response) {
				
				return response.status;
			}
		});
	}
	
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	
	var createFormRequestOngoing = false;
	var actionFormRequestOngoing = false;
	
	$(document).ready( function(){ 
	
		/* initialize the external events
		-----------------------------------------------------------------*/
		$('#external-events div.external-event').each(function() {
			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			var eventObject = {
				title: $.trim($(this).text()) // use the element's text as the event title
			};
			// store the Event Object in the DOM element so we can get to it later
			$(this).data('eventObject', eventObject);
			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
		});
		/* initialize the calendar
		-----------------------------------------------------------------*/
		var calendar = $('#calendar').fullCalendar({
			selectable: true,
			editable: true,
			droppable: true,
			weekends: true,	
			theme: true,
			eventOverlap: false,
			nextDayThreshold: "00:00:00",
			header: {
				left: ' today',
				center: 'prev, title, next',
				right: 'month,agendaWeek,agendaDay'
			},	
			buttonHtml: {
				prev: '<i class=\"ace-icon fa fa-chevron-left\"></i>',
				next: '<i class=\"ace-icon fa fa-chevron-right\"></i>'
			},
			events: function(start, end, timezone, callback) {
				
				current_date = $('#calendar').fullCalendar('getDate');
				current_date = current_date.format('YYYY-MM-DD hh:mm:ss');
				
				today = moment();
				today = today.format("YYYY-MM-DD HH:mm:ss"); 
				
				$.ajax({
					url: yii.urls.absoluteUrl + '/calendar/index',
					type: 'post',
					dataType: 'json',
					data: {
						"loadEvents": 1,
						"currentDate": current_date,
						"today": today,
						"calendar_id": current_calendar_id,
						"customer_id": customer_id,
						"viewer": viewer,
					},
					success: function(response) {
						if( response.status == "success" )
						{					
							var events = [];
							
							 $.each(response.events, function(i, item) {
								events.push({
									id: item.id,
									title: item.title,
									start: item.start_date,
									end: item.end_date,
									details: item.details,
									color: item.color,
									allDay: item.allDay,
									custom: item.is_custom,
									status: item.status,
								});
							});
							
							callback(events);
						}
					}
				});
			},
			select: function(start, end, jsEvent, view) {	
				
				var check = new Date(start);
				var today = new Date();
			
				// var current_date = check.getMonth()+1 + '/' + check.getDate() + '/' + check.getFullYear();
				
				var current_date = moment(start).format('YYYY-MM-DD');
				
				if( !createFormRequestOngoing && check >= today )
				{			
					createFormRequestOngoing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + '/calendar/create',
						type: 'post',
						dataType: 'json',
						data: { "ajax":1, "calendar_id": current_calendar_id, "viewer": viewer, "current_date": current_date, "current_lead_id": current_lead_id },
						success: function(response) {
							
							createFormRequestOngoing = false; 
							
							if(response.status  == "success")
							{
								modal = response.html;
							}
							else
							{
								return false;
								
								var modal = 
								'<div class="modal fade">\
								  <div class="modal-dialog">\
								   <div class="modal-content">\
									 <div class="modal-body">\
									   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
										<p>Sorry but an error occured. Please try again later.</p> \
									 </div>\
								  </div>\
								 </div>\
								</div>';
							}
							
							var modal = $(modal).appendTo('body');
							
							modal.on("change", "#CalendarAppointment_title", function(){
								
								if( $(this).val() == "INSERT APPOINTMENT" )
								{
									modal.find('.location-field-container').show();
									modal.find('.lead-field-container').show();
									modal.find('.details-field-container').show();
								}
								else
								{
									modal.find('.location-field-container').hide();
									modal.find('.lead-field-container').hide();
									modal.find('.details-field-container').hide();
									
									modal.find('#CalendarAppointment_location').val("");
									modal.find('#CalendarAppointment_lead_id').val("");
									modal.find('#CalendarAppointment_details').val("");
								}
							});
							
							modal.on("click", ".start-time-option", function(){
			
								start_date_time = $(this).attr("value");
								start_date_time_label = $(this).attr("label");
								
								if( start_date_time != "" )
								{
									$(this).closest("div.dropdown").find("a.btn").html( start_date_time_label + ' <span class="fa fa-caret-down"></span>' );
									
									modal.find("#CalendarAppointment_start_date_time").val(start_date_time);
									
									$(this).closest("div.dropdown.open").removeClass("open");
									
									$.ajax({
										url: yii.urls.absoluteUrl + '/calendar/autoFillEndDate',
										type: 'post',
										dataType: 'json',
										data: { "ajax":1, "calendar_id": current_calendar_id, "start_date_time": start_date_time },
										success: function(response){
											
											if( response.status == "success" )
											{
												modal.find("#CalendarAppointment_end_date_time").val(response.end_date_time_value);
												modal.find(".end-date-dropdown").parent().find("a.btn").html( response.end_date_time_label + ' <span class="fa fa-caret-down"></span>' );
											}
										},
									});
								}
								
								return false;
							});
							
							
							modal.on("click", ".end-time-option", function(){
			
								end_date_time = $(this).attr("value");
								end_date_time_label = $(this).attr("label");
								
								if( end_date_time != "" )
								{
									$(this).closest("div.dropdown").find("a.btn").html( end_date_time_label + ' <span class="fa fa-caret-down"></span>' );
									
									modal.find("#CalendarAppointment_end_date_time").val(end_date_time);
									
									$(this).closest("div.dropdown.open").removeClass("open");
								}
								
								return false;
							});
							

							modal.find('button[data-action=save]').on('click', function() {

								var errors = "";
							
								if( modal.find("#CalendarAppointment_title").val() == "APPOINTMENT SET" )
								{
									if( modal.find("#CalendarAppointment_lead_id").val() == "" )
									{
										errors += "Lead is required \n\n";
									}
									
									if( modal.find("#CalendarAppointment_location").val() == "" )
									{
										errors += "Location is required \n\n";
									}
								}
								
								if( modal.find("#CalendarAppointment_title").val() == "SCHEDULE CONFLICT" )
								{
									if( modal.find("#CalendarAppointment_start_date_time").val() == "" )
									{
										errors += "Start time is required \n\n";
									}
									
									if( modal.find("#CalendarAppointment_end_date_time").val() == "" )
									{
										errors += "End time is required \n\n";
									}
									
									if( modal.find("#CalendarAppointment_details").val() == "" )
									{
										errors += "Details is required \n\n";
									}
								}
								
								if( errors != "" )
								{
									alert(errors);
									return false;
								}
								else
								{
									if( $(this).attr("existingEvent") > 0 )
									{
										if( !confirm("Lead has a pending appointment/conflict scheduled. Do you want to continue and remove the old occurrence?") )
										{
											return false;
										}
									}
									
									
									if( $("#leadPhoneNumbers").length > 0 )
									{
										if(  modal.find("#CalendarAppointment_title").val() == "APPOINTMENT SET" )
										{
											$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_appointment_set='1']").prop("selected", true);
										}
										
										if(  modal.find("#CalendarAppointment_title").val() == "LOCATION CONFLICT" )
										{
											$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_location_conflict='1']").prop("selected", true);
										}
										
										if(  modal.find("#CalendarAppointment_title").val() == "SCHEDULE CONFLICT" )
										{
											$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_schedule_conflict='1']").prop("selected", true);
										}
										
										setTimeout(function(){
											
											$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").trigger("change");
											
										}, 500);
									}
									
									
									data = modal.find('form').serialize() + "&current_date=" + current_date;
									
									$.ajax({
										url: yii.urls.absoluteUrl + '/calendar/create',
										type: 'post',
										dataType: 'json',
										data: data,
										success: function(response) {
											
											if( typeof hasMadeCalendarChanges != 'undefined' )
											{
												hasMadeCalendarChanges++;
											}
											
											$('#calendar').fullCalendar('refetchEvents');
											
											modal.modal("hide");
										}
									});
								}
							});
							
							modal.modal('show').on('hidden.bs.modal', function(){
								modal.remove();
							});

							// change the border color just for fun
							// $(this).css('border-color', 'red');
						}
					});
				}
				
				calendar.fullCalendar('unselect');
			},
			eventResize: function(calEvent, delta, revertFunc) {
			
				// var updateDays = delta._days;
			
				var start_date = moment(calEvent.start);
				start_date = start_date.format("YYYY-MM-DD HH:mm:ss"); 
				
				var end_date = moment(calEvent.end);
				end_date = end_date.format("YYYY-MM-DD HH:mm:ss"); 

				
				if( calEvent.title != 'BLACKOUT DAYS' )
				{
					revertFunc();
					return false;
				}				
				
				$.ajax({
					url: yii.urls.absoluteUrl + '/calendar/update',
					type: 'post',
					dataType: 'json',
					data: { 
						"appointment_id": calEvent.id, 
						// "updateDays": updateDays,
						"start_date": start_date, 
						"end_date": end_date,
						"type": "eventResize",
					},
					success: function(response) {
					
						$('#calendar').fullCalendar('refetchEvents');
					}
				});

				return true;

			},			
			eventDrop: function( calEvent, delta, revertFunc, jsEvent, ui, view ) {
				
				var start_date = moment(calEvent.start);
				start_date = start_date.format("YYYY-MM-DD HH:mm:ss"); 
				
				var end_date = moment(calEvent.end);
				end_date = end_date.format("YYYY-MM-DD HH:mm:ss"); 
				
				if( calEvent.title == 'PAST DATE' || calEvent.custom == 2 )
				{
					revertFunc();
					return false;
				}		

				if( viewer == 'agent' )
				{
					revertFunc();
					return false;
				}	
				
				$.ajax({
					url: yii.urls.absoluteUrl + '/calendar/update',
					type: 'post',
					dataType: 'json',
					data: { 
						"appointment_id": calEvent.id, 
						"start_date": start_date, 
						"end_date": end_date,
						"type": "eventDrop",						
					},
					success: function(response) 
					{
						$('#calendar').fullCalendar('refetchEvents');
					}
				});

				return true;
			},
			eventDragStop: function( event, jsEvent, ui, view ) { 
				
			},
			drop: function(date, allDay, resource, event) { 
				// retrieve the dropped element's stored Event Object
				var originalEventObject = $(this).data('eventObject');
				
				var formattedDate = moment(date);
				start_date = formattedDate.format("YYYY-MM-DD"); 
				
				//remove all events in current day
				$('#calendar').fullCalendar( 'removeEvents', function(event) {

					if( moment(event.start).format("YYYY-MM-DD") == start_date && event.title !='BLACKOUT DAYS' )
					{
						return true;
					}
					
				});

				
				$.ajax({
					url: yii.urls.absoluteUrl + '/calendar/blackoutDays',
					type: 'post',
					dataType: 'json',
					data: { 
						"calendar_id": current_calendar_id,
						"start_date": start_date, 
					},
					success: function(response) {
							
						$('#calendar').fullCalendar('refetchEvents');	
					}
				});
			},
			eventRender: function (event, element, icon) {
				
				var check = new Date(event.start);
				var today = new Date();
				
				if( event.custom == 2)
				{
					element.find('.fc-event-time').hide();
				}
				
				if( event.title == 'BLACKOUT DAYS')
				{
					element.find('.fc-event-time').hide();
				}
				
				if( event.title != 'BLACKOUT DAYS' && viewer == 'customer' )
				{
					if(check >= today)
						element.find('.fc-event-inner').append('<span class="pull-right delete-slot" style="z-index:100; color:'+event.color+'"><i class="fa fa-times fa-lg"></i></span>');
					
					element.find(".fc-event-inner").hover(function() {
						
						if( event.color == "#D15B47" )
						{
							element.find(".delete-slot").css("color", "#FFF");
						}
						else
						{
							element.find(".delete-slot").css("color", "#D15B47");
						}
						
					},function(){
						
						element.find(".delete-slot").css("color", event.color);
						
					});
					
					element.find(".delete-slot").click(function() {
						
						if( confirm("Are you sure you want to remove this?") )
						{
							$.ajax({
								url: yii.urls.absoluteUrl + '/calendar/deleteSlot',
								type: 'post',
								dataType: 'json',
								data: { "ajax": 1, "event_id": event.id },
								success: function(response) {
									if( response.status == "success" )
									{					
										$('#calendar').fullCalendar('removeEvents', event.id);
									}
								}
							});
						}
						
						return false;
					});
				}
			},	
			eventClick: function(calEvent, jsEvent, view) {
		
				var suggest_clicked = 0;
		
				var check = new Date(calEvent.start);
				var today = new Date();
				
				var current_date = moment(calEvent.start).format('YYYY-MM-DD');
				
				//check if holiday
				if( calEvent.custom == 2 )
				{
					return false;
				}
				
				if( viewer == 'agent' )
				{
					if( calEvent.title == 'INSERT APPOINTMENT' || calEvent.title == 'APPOINTMENT SET' || calEvent.title == 'LOCATION CONFLICT' || calEvent.title == 'SCHEDULE CONFLICT' )
					{
						return false;
					}		
					
					if( calEvent.custom == 3 && calEvent.color == '#FFB752' )
					{
						return false;
					}
						
					if( calEvent.custom == 3 && calEvent.color == '#D15B47' && (calEvent.status == 2 || calEvent.status == 3) )
					{
						return false;
					}
				}
				else
				{
					if( calEvent.title == 'PAST DATE' || calEvent.title == 'NO SHOW RESCHEDULE' )
					{
						return false;
					}
				}
				
				//&& check >= today
				
				if( !actionFormRequestOngoing && calEvent.title != 'BLACKOUT DAYS' )
				{		
					actionFormRequestOngoing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + '/calendar/actionForm',
						type: 'post',
						dataType: 'json',
						data: { 
							"ajax":1, 
							"id":calEvent.id,
							"viewer": viewer,						
							"current_date": current_date,						
							"current_lead_id": current_lead_id,						
						},
						success: function(response) {
							
							actionFormRequestOngoing = false; 
							
							if(response.status  == "success")
							{
								modal = response.html;
							}
							else
							{
								var modal = 
								'<div class="modal fade">\
								  <div class="modal-dialog">\
								   <div class="modal-content">\
									 <div class="modal-body">\
									   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
										<p>Sorry but an error occured. Please try again later.</p> \
									 </div>\
								  </div>\
								 </div>\
								</div>';
							}
							
							var modal = $(modal).appendTo('body');
							
							modal.on("change", "#CalendarAppointment_title", function(){
								
								if( viewer == 'customer' )
								{
									if( $(this).val() == "APPOINTMENT SET" || $(this).val() == "INSERT APPOINTMENT" )
									{
										modal.find('.location-field-container').show();
										modal.find('.lead-field-container').show();
										modal.find('.details-field-container').show();
									}
									else if( $(this).val() == "NO SHOW RESCHEDULE" )
									{
										modal.find('.start-time-field-container').hide();
										modal.find('.end-time-field-container').hide();
										
										modal.find('.location-field-container').hide();
										modal.find('.lead-field-container').hide();
										modal.find('.details-field-container').hide();
										
										modal.find('#CalendarAppointment_location').val("");
										modal.find('#CalendarAppointment_lead_id').val("");
										modal.find('#CalendarAppointment_details').val("");
									}
									else if( $(this).val() == "CHANGE APPOINTMENT" )
									{
										modal.find('#CalendarAppointment_start_date_time').prop("disabled", false);
										modal.find('#CalendarAppointment_end_date_time').prop("disabled", false);
										modal.find('#CalendarAppointment_location').prop("disabled", false);
										
										modal.find('.details-field-container').show();
										modal.find('#CalendarAppointment_details').prop("disabled", false);
									}
									else if( $(this).val() == "RESCHEDULE APPOINTMENT" )
									{
										modal.find('.details-field-container').show();
										modal.find('#CalendarAppointment_details').prop("disabled", false);
									}
									else
									{
										modal.find('.location-field-container').hide();
										modal.find('.lead-field-container').hide();
										modal.find('.details-field-container').hide();
										
										modal.find('#CalendarAppointment_location').val("");
										modal.find('#CalendarAppointment_lead_id').val("");
										modal.find('#CalendarAppointment_details').val("");
									}
								}
								else
								{
									if( $(this).val() == "APPOINTMENT SET" || $(this).val() == "LOCATION CONFLICT" || $(this).val() == "SCHEDULE CONFLICT" )
									{
										modal.find('.location-field-container').show();
										modal.find('.lead-field-container').show();
										modal.find('.details-field-container').show();
									}
									else
									{
										modal.find('.location-field-container').hide();
										modal.find('.lead-field-container').hide();
										modal.find('.details-field-container').hide();
										
										modal.find('#CalendarAppointment_location').val("");
										modal.find('#CalendarAppointment_lead_id').val("");
										modal.find('#CalendarAppointment_details').val("");
									}
									
									
									if( $(this).val() == "LOCATION CONFLICT" )
									{
										optionsHtml = modal.find("#allLocations").html();
										
										modal.find("#CalendarAppointment_location").html(optionsHtml);
									}
									else
									{
										optionsHtml = modal.find("#customerApprovedLocations").html();
										
										modal.find("#CalendarAppointment_location").html(optionsHtml);
									}
								}
								
								
								$.ajax({
									url: yii.urls.absoluteUrl + '/calendar/actionForm',
									type: 'post',
									dataType: 'json',
									data: {
										"ajax":1, 
										"id":calEvent.id,
										"viewer": viewer,						
										"current_lead_id": current_lead_id,
										"title": modal.find("#CalendarAppointment_title").val(),
										"getDisposition": 1,
									},
									success: function(response) {
										
										modal.find("#CalendarAppointment_details").val(response.notesPrefill);
										
									}
								});
							});
							
							modal.find('button[data-action=save]').on('click', function() {
								
								if( $(this).attr("existingEvent") > 0 )
								{
									if( !confirm("Lead has a pending appointment/conflict scheduled. Do you want to continue and remove the old occurrence?") )
									{
										return false;
									}
								}
								
							
								if( $("#leadPhoneNumbers").length > 0 )
								{
									if(  modal.find("#CalendarAppointment_title").val() == "APPOINTMENT SET" )
									{
										$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_appointment_set='1']").prop("selected", true);
									}
									
									if(  modal.find("#CalendarAppointment_title").val() == "LOCATION CONFLICT" )
									{
										$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_location_conflict='1']").prop("selected", true);
									}
									
									if(  modal.find("#CalendarAppointment_title").val() == "SCHEDULE CONFLICT" )
									{
										$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").find("option[is_schedule_conflict='1']").prop("selected", true);
									}
									
									setTimeout(function(){
										
										$("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").trigger("change");
										
									}, 500);
								}
							

								var errors = "";
								
								if( modal.find("#CalendarAppointment_title").val() == "APPOINTMENT SET" )
								{
									if( modal.find("#CalendarAppointment_lead_id").val() == "" )
									{
										errors += "Lead is required \n\n";
									}
									
									if( modal.find("#CalendarAppointment_location").val() == "" )
									{
										errors += "Location is required \n\n";
									}
								}
								
								if( errors != "" )
								{
									alert(errors);
									return false;
								}
								
								// if( $("#leadPhoneNumbers").length > 0 )
								// {
									// $("#leadPhoneNumbers").find("tr.success td:eq(3) select").prop("disabled", true);
									// $("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").prepend("<option selected>"+ modal.find("#CalendarAppointment_title").val() +"</option>");
									// $("#leadPhoneNumbers").find("tr.success td:eq(3) select:eq(0)").trigger("change");
								// }
								
								data = modal.find('form').serialize() + "&current_lead_id=" + current_lead_id;

								$.ajax({
									url: yii.urls.absoluteUrl + '/calendar/actionForm',
									type: 'post',
									dataType: 'json',
									data: data,
									success: function(response) {
										
										// calEvent.title = response.event.title;
										// calEvent.start = response.event.start_date;
										// calEvent.end = response.event.end_date;
										// calEvent.color = response.event.color;
										
										// $('#calendar').fullCalendar('updateEvent', calEvent);
										
										if( typeof hasMadeCalendarChanges != 'undefined' )
										{
											hasMadeCalendarChanges++;
										}
										
										$('#calendar').fullCalendar('refetchEvents');
										
										modal.modal("hide");
									}
								});
							});
							
							modal.find('button[data-action=approved]').on('click', function() {
								
								data = modal.find('form').serialize() + "&approved=1";

								$.ajax({
									url: yii.urls.absoluteUrl + '/calendar/actionForm',
									type: 'post',
									dataType: 'json',
									data: data,
									success: function(response) {
										
										// calEvent.title = response.event.title;
										// calEvent.start = response.event.start_date;
										// calEvent.end = response.event.end_date;
										// calEvent.color = response.event.color;
										
										// $('#calendar').fullCalendar('updateEvent', calEvent);
										
										$('#calendar').fullCalendar('refetchEvents');
										
										modal.modal("hide");
									}
								});
							});
							
							modal.find('button[data-action=declined]').on('click', function() {
								
								data = modal.find('form').serialize() + "&declined=1";

								$.ajax({
									url: yii.urls.absoluteUrl + '/calendar/actionForm',
									type: 'post',
									dataType: 'json',
									data: data,
									success: function(response) {
										
										// calEvent.title = response.event.title;
										// calEvent.start = response.event.start_date;
										// calEvent.end = response.event.end_date;
										// calEvent.color = response.event.color;
										
										// $('#calendar').fullCalendar('updateEvent', calEvent);
										
										$('#calendar').fullCalendar('refetchEvents');
										
										modal.modal("hide");
									}
								});
							});
							
							modal.find('button[data-action=suggest]').on('click', function() {
								
								suggest_clicked++;
								
								modal.find(".date-picker").prop("disabled", false);
								modal.find("#CalendarAppointment_start_date_time").prop("disabled", false);
								modal.find("#CalendarAppointment_end_date_time").prop("disabled", false);
								
								$(this).text("Submit Alt");
								
								if( modal.find("#CalendarAppointment_details").val() == "" )
								{
									alert("Details is required.");
									return false;
								}
								
								if( suggest_clicked == 2)
								{
									data = modal.find('form').serialize() + "&suggest=1";

									$.ajax({
										url: yii.urls.absoluteUrl + '/calendar/actionForm',
										type: 'post',
										dataType: 'json',
										data: data,
										success: function(response) {
										
											$('#calendar').fullCalendar('refetchEvents');
											
											modal.modal("hide");
										}
									});
								}
							});
							
							modal.modal('show').on('hidden.bs.modal', function(){
								
								modal.remove();
							});
							
							// change the border color just for fun
							//$(this).css('border-color', 'red');
						}
					});
				}
				
				
				if( calEvent.title == 'BLACKOUT DAYS' )
				{
					if( confirm("Are you sure you want to remove this?") )
					{
						var formattedDate = moment(date);
						start_date = formattedDate.format("YYYY-MM-DD"); 
						
						$('#calendar').fullCalendar( 'removeEvents', calEvent.id);
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/calendar/blackoutDays',
							type: 'post',
							dataType: 'json',
							data: { "remove":1, "appointment_id": calEvent.id, "start_date":start_date },
							success: function(response) {

								// $('#calendar').fullCalendar( 'removeEvents', function(event){

									// if(event.custom == 2)
									// {
										// return true;
									// }
								  
								// });
							
								// $.each(response.events, function(i, item) {
									
									// var calEvent = {};
									
									// calEvent.id = item.id;
									// calEvent.title = item.title;
									// calEvent.start = item.start_date;
									// calEvent.end = item.end_date;
									// calEvent.color = item.color;
									// calEvent.custom = item.is_custom;
									
									// $('#calendar').fullCalendar('renderEvent', calEvent);
								// });
								
								$('#calendar').fullCalendar('refetchEvents');
							}
						});
					}
				}
				
				return false;
			}
			
		});
		
		
		$(document).on("click", ".calendar-settings", function(){
			
			var id = $(this).prop("id");
			var this_button = $(this);
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/settings',
				type: 'post',
				dataType: 'json',
				data: {
						"ajax":1, 'id':id
				},
				success: function(response) {
					
					if(response.status  == "success")
					{
						modal = response.html;
					}
					else
					{
						var modal = 
						'<div class="modal fade">\
						  <div class="modal-dialog">\
						   <div class="modal-content">\
							 <div class="modal-body">\
							   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
								<p>Sorry but an error occured. Please try again later.</p> \
							 </div>\
						  </div>\
						 </div>\
						</div>';
					}
					
					var modal = $(modal).appendTo('body');
					
					modal.find('button[data-action=save]').on('click', function() {
						
						calenderSettingsSaving = false;
						
						if(!calenderSettingsSaving)
						{
							calenderSettingsSaving = true;
							
							data = modal.find('form').serialize();
							
							$.ajax({
								url: yii.urls.absoluteUrl + '/calendar/settings',
								type: 'post',
								dataType: 'json',
								data: data,
								beforeSend: function(){
									modal.find('button[data-action=save]').html("Saving Please Wait...");
									modal.find('button[data-action=save]').prop("disabled", true);
								},
								success: function(response) {
									
									if( response.status == "success" )
									{
										$('#calendar').fullCalendar('refetchEvents');
										
										modal.modal("hide");
									}
									
									calenderSettingsSaving = false;
									modal.find('button[data-action=save]').html('<i class="ace-icon fa fa-trash-o"></i> Save');
									modal.find('button[data-action=save]').prop("disabled", false);
								}
							});
						}
					});
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});

		});
		
		
		$(document).on("click", ".manage-schedule", function(){
			
			var id = $(this).prop("id");
			var this_button = $(this);
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/manageSchedule',
				type: 'post',
				dataType: 'json',
				data: { "ajax":1, 'id':id },
				success: function(response) {
					
					if(response.status  == "success")
					{
						modal = response.html;
					}
					else
					{
						var modal = 
						'<div class="modal fade">\
						  <div class="modal-dialog">\
						   <div class="modal-content">\
							 <div class="modal-body">\
							   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
								<p>Sorry but an error occured. Please try again later.</p> \
							 </div>\
						  </div>\
						 </div>\
						</div>';
					}
					
					var modal = $(modal).appendTo('body');
					
					modal.find('button[data-action=default]').on('click', function() {
						
						data = modal.find('.tab-content .active form:visible').serialize() + "&use_default_schedule=1";
						
						var this_button = $(this);
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/calendar/manageSchedule',
							type: 'post',
							dataType: 'json',
							data: data,
							beforeSend: function(){
								this_button.html("Loading Please Wait...");
							},
							success: function(response) {
								
								if( response.status == "success" )
								{
									$('#calendar').fullCalendar('refetchEvents');
									
									modal.modal("hide");
								}
								
								this_button.html("Apply Default");
							}
						});
					});
					
					modal.find('button[data-action=custom]').on('click', function() {
						
						data = modal.find('.tab-content .active form:visible').serialize() + "&use_default_schedule=0";
						
						var this_button = $(this);
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/calendar/manageSchedule',
							type: 'post',
							dataType: 'json',
							data: data,
							beforeSend: function(){
								this_button.html("Loading Please Wait...");
							},
							success: function(response) {
								
								if( response.status == "success" )
								{
									$('#calendar').fullCalendar('refetchEvents');
									
									modal.modal("hide");
								}
								
								this_button.html("<i class=\"ace-icon fa fa-check\"></i> Apply");
							}
						});
					});
					
					modal.find('button[data-action=applyHolidays]').on('click', function() {
						
						data = modal.find('#holidays').serialize();
						
						var this_button = $(this);
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/calendar/applyHolidays',
							type: 'post',
							dataType: 'json',
							data: data,
							beforeSend: function(){
								this_button.html("Loading Please Wait...");
							},
							success: function(response) {
								
								if( response.status == "success" )
								{
									$('#calendar').fullCalendar('refetchEvents');
									
									modal.modal("hide");
								}
								
								this_button.html("<i class=\"ace-icon fa fa-check\"></i> Apply");
							}
						});
					});
					
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});

		});
		
	});
	
});