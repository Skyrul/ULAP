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
			height: '350',
			allDayText: '',
			// minTime: '00:00:00',
			// maxTime: '24:00:00',
			// axisFormat: 'H', //,'h(:mm)tt',
			timeFormat: {
				agenda: 'h(:mm)t' //h:mm{ - h:mm}'
			},
			selectable: false,
			editable: false,
			droppable: false,
			weekends: true,
			firstDay: 1,
			// theme: true,
			defaultView: 'agendaWeek',
			editable: false,
			eventOverlap: true,
			nextDayThreshold: "00:00:00",
			header: {
				// left: ' today',
				left: '',
				center: '',
				// right: 'month,agendaWeek,agendaDay'
				right: ''
			},	
			buttonHtml: {
				prev: '',
				next: ''
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
				return false;
			},
			eventResize: function(calEvent, delta, revertFunc) {
				
				revertFunc();
				return false;

			},			
			eventDrop: function( calEvent, delta, revertFunc, jsEvent, ui, view ) {
			
				revertFunc();
				return false;
			},			
			eventDragStart: function( calEvent, delta, revertFunc, jsEvent, ui, view ) { 
				revertFunc();
				return false;
			},
			drop: function(date, allDay, resource, event) { 
				return false;
			},
			eventRender: function (event, element, icon) {
				
			},	
			eventClick: function(calEvent, jsEvent, view) {
				return false;
			}
			
		});

		
	});
	
});