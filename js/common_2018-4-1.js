$( function(){
	
	//validate current users login session
	$(document).ready( function(){
		
		var current_login_token = '';
		var ajaxLoginSending = false;
		
		setInterval(function(){ 
			
			if( !ajaxLoginSending )
			{
				ajaxLoginSending = true;
				
				var getUrlParameter = function getUrlParameter(sParam) {
					var sPageURL = decodeURIComponent(window.location.search.substring(1)),
						sURLVariables = sPageURL.split('&'),
						sParameterName,
						i;

					for (i = 0; i < sURLVariables.length; i++) {
						sParameterName = sURLVariables[i].split('=');

						if (sParameterName[0] === sParam) {
							return sParameterName[1] === undefined ? true : sParameterName[1];
						}
					}
				};
				
				var loginAuth = getUrlParameter('loginAuth');
				
				
				
				$.ajax({
					
					url: yii.urls.absoluteUrl + "/site/validateLoginSession",
					type: "post",
					dataType: "json",
					data:{ "ajax":1, "loginAuth":loginAuth },
					complete: function(){ ajaxLoginSending = false; },
					success: function(response){
						
						if( response.status == 'success' )
						{
							if( current_login_token == '' )
							{
								current_login_token = response.login_session_token;
							}
							else
							{
								if( current_login_token != response.login_session_token)
								{
									$(location).attr("href", yii.urls.absoluteUrl + "/site/logout?loginAuth=expired");
								}
							}
						}
						
					},
				});
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/news/checkNewPosts",
					type: "post",
					dataType: "json",
					data:{ "ajax":1 },
					success: function(response){
						
						if( response.count > 0 )
						{
							if( $("#nav_news_main .badge").length == 0 )
							{
								$("#nav_news_main a").append('<span class="badge badge-danger">New</span>');
							}
						}
						else
						{
							$("#nav_news_main").find(".badge").remove();
						}
					},
				});
			}
			
		}, 10000);
		
		
		$(document).on("click", ".replacement-codes-modal", function(){
			
			var modal = ' \
				<div class="modal fade">\
					<div class="modal-dialog">\
						<div class="modal-content">\
							<div class="modal-header"> \
								<button data-dismiss="modal" class="close" type="button">×</button> \
								<h4 class="blue bigger">Replacement Codes</h4> \
							</div> \
							<div class="modal-body">\
								<table class="table table-bordered table-striped table-hover"> \
									<tr> \
										<td>[first_name]</td> \
										<td>Lead First Name</td> \
									</tr> \
									<tr> \
										<td>[last_name]</td> \
										<td>Lead Last Name</td> \
									</tr> \
									<tr> \
										<td>[partner_first_name]</td> \
										<td>Partner First Name</td> \
									</tr> \
									<tr> \
										<td>[partner_last_name]</td> \
										<td>Partner Last Name</td> \
									</tr> \
									<tr> \
										<td>[office_phone_number]</td> \
										<td>Office Phone Number</td> \
									</tr> \
									<tr> \
										<td>[mobile_phone_number]</td> \
										<td>Mobile Phone Number</td> \
									</tr> \
									<tr> \
										<td>[home_phone_number]</td> \
										<td>Home Phone Number</td> \
									</tr> \
									<tr> \
										<td>[city]</td> \
										<td>City</td> \
									</tr> \
									<tr> \
										<td>[state]</td> \
										<td>State</td> \
									</tr> \
									<tr> \
										<td>[zip_code]</td> \
										<td>Zip Code</td> \
									</tr> \
									<tr> \
										<td>[address]</td> \
										<td>Address</td> \
									</tr> \
									<tr> \
										<td>[address2]</td> \
										<td>Address2</td> \
									</tr> \
									<tr> \
										<td>[email_address]</td> \
										<td>Email Address</td> \
									</tr> \
									<tr> \
										<td>[customer_first_name]</td> \
										<td>Customer First Name</td> \
									</tr> \
									<tr> \
										<td>[customer_last_name]</td> \
										<td>Customer Last Name</td> \
									</tr> \
									<tr> \
										<td>[customer_phone]</td> \
										<td>Customer Phone</td> \
									</tr> \
									<tr> \
										<td>[calendar_name]</td> \
										<td>Calendar Name</td> \
									</tr> \
									<tr> \
										<td>[agent_dispo_note]</td> \
										<td>Agent Disposition Note</td> \
									</tr> \
									<tr> \
										<td>[agent_dispo_note_sms]</td> \
										<td>Agent Disposition Note SMS</td> \
									</tr> \
									<tr> \
										<td>[sub_disposition_name]</td> \
										<td>Sub Disposition Name</td> \
									</tr> \
									<tr> \
										<td>[sub_disposition_note]</td> \
										<td>Sub Disposition Note</td> \
									</tr> \
									<tr> \
										<td>[appointment_location]</td> \
										<td>Appointment Location</td> \
									</tr> \
									<tr> \
										<td>[appointment_date]</td> \
										<td>Appointment Date</td> \
									</tr> \
									<tr> \
										<td>[appointment_time]</td> \
										<td>Appointment Time</td> \
									</tr> \
									<tr> \
										<td>[changed_appointment_date]</td> \
										<td>Changed Appointment Date</td> \
									</tr> \
									<tr> \
										<td>[changed_appointment_time]</td> \
										<td>Changed Appointment Time</td> \
									</tr> \
									<tr> \
										<td>[office_assigned_to_calendar]</td> \
										<td>Office assigned to Calendar</td> \
									</tr> \
									<tr> \
										<td>[staff_assigned_to_calendar]</td> \
										<td>Staff assigned to Calendar</td> \
									</tr> \
									<tr> \
										<td>[dialed_number]</td> \
										<td>Dialed Number</td> \
									</tr> \
									<tr> \
										<td>[dialed_number_last_4_digits]</td> \
										<td>Dialed Number - Last 4 Digits Only</td> \
									</tr> \
									<tr> \
										<td>[ics_file_link]</td> \
										<td>ICS File Link</td> \
									</tr> \
									<tr> \
										<td>[ics_file_link_non_html]</td> \
										<td>ICS File Link SMS</td> \
									</tr> \
									<tr> \
										<td>[my_portal_login_button]</td> \
										<td>My Portal Login Button</td> \
									</tr> \
									<tr> \
										<td>[my_portal_login_button_non_html]</td> \
										<td>My Portal Login Button SMS</td> \
									</tr> \
									<tr> \
										<td>[customer_reply_link_sms]</td> \
										<td>Customer Reply Link SMS</td> \
									</tr> \
								</table> \
							</div>\
						</div>\
					</div>\
				</div> \
			';
 
			var modal = $(modal).appendTo('body');
			
			modal.modal('show').on('hidden.bs.modal', function(){
				modal.remove();
			});
			
		});
		
		
		$(document).on("click", ".update-account-state", function(){

			var type = $(this).attr("type");
			
			$(".update-account-state").find("i").remove();
			$(this).append(' <i class="fa fa-check"></i>');
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/site/updateLoginState",
				type: "post",
				dataType: "json",
				data:{ "ajax":1, "type":type },
				success: function(response){
					
					if( response.status == 'success' )
					{
						$(".account-state-container").html(response.html);
						
						if( response.login_state_type != '' && response.login_state_type != 1 ) //if not login state available redirect to idle page
						{
							$(location).attr("href", yii.urls.absoluteUrl + "/agent/idle");
						}
					}
					
				}
			});
			
		});
		
	});
	
});