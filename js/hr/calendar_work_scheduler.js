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
			allDayText: '',
			// minTime: '00:00:00',
			// maxTime: '24:00:00',
			// axisFormat: 'H', //,'h(:mm)tt',
			timeFormat: {
				agenda: 'h(:mm)t' //h:mm{ - h:mm}'
			},
			selectable: true,
			editable: true,
			droppable: true,
			weekends: true,
			firstDay: 1,
			// theme: true,
			defaultView: 'agendaWeek',
			editable: true,
			eventOverlap: true,
			nextDayThreshold: "00:00:00",
			header: {
				// left: ' today',
				left: '',
				center: 'prev, title, next',
				// right: 'month,agendaWeek,agendaDay'
				right: ''
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
					url: yii.urls.absoluteUrl + '/hr/accountUser/workSchedule',
					type: 'post',
					dataType: 'json',
					data: {
						"loadEvents": 1,
						"account_id": account_id,
						"currentDate": current_date,
						"today": today,
					},
					success: function(response) {
						
						$(".total-schedule-work-hours").text(response.total_scheduled_work_hours);
						
						if( response.status == "success" )
						{					
							var events = [];
							
							 $.each(response.events, function(i, item) {
								events.push({
									id: item.id,
									title: item.title,
									start: item.start_date,
									end: item.end_date,
									color: item.color,
									allDay: item.allDay,
									type: item.is_custom,
								});
							});
							
							callback(events);
						}
					}
				});
			},
			select: function(start, end, jsEvent, view) {	
				
			},
			eventResize: function(calEvent, delta, revertFunc) {
				
				if( calEvent.color == '#D6487E' )
				{
					revertFunc();
					return false;
				}
				
				var start_date = moment(calEvent.start);
				start_date = start_date.format("YYYY-MM-DD HH:mm:ss"); 
				
				var end_date = moment(calEvent.end);
				end_date = end_date.format("YYYY-MM-DD HH:mm:ss"); 
			
				$.ajax({
					url: yii.urls.absoluteUrl + '/hr/accountUser/updateWorkSchedule',
					type: 'post',
					dataType: 'json',
					data: { 
						"event_id": calEvent.id, 
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
			
				if( calEvent.type == 3 || calEvent.color == '#D6487E' )
				{
					revertFunc();
					return false;
				}
				
				var start_date = moment(calEvent.start);
				start_date = start_date.format("YYYY-MM-DD HH:mm:ss"); 
				
				var end_date = moment(calEvent.end);
				end_date = end_date.format("YYYY-MM-DD HH:mm:ss"); 
							
				$.ajax({
					url: yii.urls.absoluteUrl + '/hr/accountUser/updateWorkSchedule',
					type: 'post',
					dataType: 'json',
					data: { 
						"event_id": calEvent.id, 
						"start_date": start_date, 
						"end_date": end_date,
						"type": "eventDrop",
					},
					success: function(response) {
					
						$('#calendar').fullCalendar('refetchEvents');
					}
				});

				return true;
			},			
			eventDragStart: function( calEvent, delta, revertFunc, jsEvent, ui, view ) { 
			},
			drop: function(date, allDay, resource, event) { 
				// retrieve the dropped element's stored Event Object
				var originalEventObject = $(this).data('eventObject');

				var formattedDate = moment(date);
				start_date = formattedDate.format("YYYY-MM-DD HH:mm:ss"); 
				
				$.ajax({
					url: yii.urls.absoluteUrl + '/hr/accountUser/addWorkSchedule',
					type: 'post',
					dataType: 'json',
					data: { 
						"account_id": account_id,
						"title": originalEventObject.title,
						"start_date": start_date, 
					},
					success: function(response) {
							
						$('#calendar').fullCalendar('refetchEvents');	
					}
				});
			},
			eventRender: function (event, element, icon) {
			
				if( !event.allDay && (event.color == '#82AF6F' || event.color == '#9585BF') )
				{
					var color;
					
					if( event.color == '#82AF6F' )
					{
						color = '#A1C393';
					}
					
					if( event.color == '#9585BF' )
					{
						color = '#B0A4CF';
					}
					
					var current_zindex = element.css("z-index");
					
					element.find('.fc-event-inner').prepend('<span class="pull-right delete-slot" style="z-index:100; margin-right:3px; color:' + color + '"><i class="fa fa-times fa-lg"></i></span>');
						
					element.find(".fc-event-inner").hover(function() {
						
						element.css("z-index", "10");
							
						element.find(".delete-slot").css("color", "#D15B47");
						
					},function(){
						
						element.css("z-index", current_zindex);
						
						element.find(".delete-slot").css("color", color);
						
					});
					
					element.find(".delete-slot").click(function() {
						
						if( confirm("Are you sure you want to remove this?") )
						{
							$.ajax({
								url: yii.urls.absoluteUrl + '/hr/accountUser/deleteWorkSchedule',
								type: 'post',
								dataType: 'json',
								data: { 
									"account_id": account_id,
									"event_id": event.id,
								},
								success: function(response) {
										
									$('#calendar').fullCalendar('refetchEvents');	
								}
							});
						}
						
						return false;
					});
				}
			},	
			eventClick: function(calEvent, jsEvent, view) {
		
			}
			
		});

		
	});
	
});