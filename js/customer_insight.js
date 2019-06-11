$(document).ready( function(){

	var actionFormRequestOngoing = false;
	var viewer = "customer";
		
	$(document).on("click", ".action-form-btn", function(){
		
		var id = $(this).prop("id");
		var current_date = $(this).attr("current_date");
		var suggest_clicked = 0;

		if( !actionFormRequestOngoing )
		{
			actionFormRequestOngoing = true;
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/actionForm',
				type: 'post',
				dataType: 'json',
				data: { 
					"ajax":1, 
					"id":id,
					"viewer": viewer,						
					"current_date": current_date,						
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
					
					modal.find('.date-picker').datepicker({
						autoclose: true,
						todayHighlight: true
					});
					
					modal.find('button[data-action=approved]').on('click', function() {
						
						data = modal.find('form').serialize() + "&approved=1";

						$.ajax({
							url: yii.urls.absoluteUrl + '/calendar/actionForm',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								// $('#calendar').fullCalendar('refetchEvents');

								updateCounters();
								
								if( $("#customerList table tr td a#" + response.customer_id).length > 0 )
								{
									$("#customerList table tr td a#" + response.customer_id).click();
								}
								else
								{
									$.fn.yiiListView.update("scheduleConflictList", {});
									$.fn.yiiListView.update("locationConflictList", {});
								}
								
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
								
								// $('#calendar').fullCalendar('refetchEvents');
								
								updateCounters();
								
								if( $("#customerList table tr td a#" + response.customer_id).length > 0 )
								{
									$("#customerList table tr td a#" + response.customer_id).click();
								}
								else
								{
									$.fn.yiiListView.update("scheduleConflictList", {});
									$.fn.yiiListView.update("locationConflictList", {});
								}
								
								modal.modal("hide");
							}
						});
					});
					
					modal.find('button[data-action=suggest]').on('click', function() {
						
						suggest_clicked++;
						
						modal.find(".datepicker").prop("disabled", false);
						modal.find("#CalendarAppointment_start_date_time").prop("disabled", false);
						modal.find("#CalendarAppointment_end_date_time").prop("disabled", false);
						modal.find("#CalendarAppointment_location").prop("disabled", false);
						
						modal.find('button[data-action=approved]').hide();
						modal.find('button[data-action=declined]').hide();
								
						$(this).text("Submit Alt");
						
						if( modal.find("#CalendarAppointment_details").val() == "" )
						{
							alert("Details is required.");
							return false;
						}
						
						if( modal.find("#CalendarAppointment_customer_notes").val() == "" )
						{
							alert("Customer Note is required.");
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
								
									// $('#calendar').fullCalendar('refetchEvents');
									
									updateCounters();
									
									if( $("#customerList table tr td a#" + response.customer_id).length > 0 )
									{
										$("#customerList table tr td a#" + response.customer_id).click();
									}
									else
									{
										$.fn.yiiListView.update("scheduleConflictList", {});
										$.fn.yiiListView.update("locationConflictList", {});
									}
									
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
		
	});
	
});