$( function(){

	function isValidEmail(email) {
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	}

	$(document).ready( function(){
		
		//start of calendar events
		
		$(document).on("click", ".save-settings", function(){
			
			var this_button = $(this);
			
			var schedule_container = this_button.closest(".panel-collapse").find(".manage-schedule-wrapper");
			
			var data = this_button.closest(".panel-collapse #form-calendar-settings").serialize();
			
			var calendar_name_container = this_button.closest(".panel").find(".panel-title a");
			
			var calendar_name = this_button.closest(".panel-collapse #form-calendar-settings").find("input[name='Calendar[name]']").val();
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/calendar/update',
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					
					calendar_name_container.html('<i class="bigger-110 ace-icon fa fa-angle-down" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i> ' + calendar_name);
					
					schedule_container.html("<center>Updating Please Wait...</center>");
					
					this_button.html("Saving Please Wait...");
					
				},
				success: function(response) {
					
					if( response.status == "success" )
					{
						schedule_container.html( response.html );
					}
					else
					{
						this_button.html("Database Error.");
					}
					
					this_button.html("Save");
				}
			});
			
			return false;
		});
		
		
		$(document).on("click", ".apply-holiday-settings", function(){
						
			var this_button = $(this);
			
			var data = $(this).closest(".panel-collapse #form-holidays-settings").serialize();
				
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/applyHolidays',
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					this_button.html("Saving Please Wait...");
				},
				success: function(response) {
					
					if(response.status  == "error")
					{
						this_button.html("Database Error.");
					}
					
					this_button.html("<i class=\"ace-icon fa fa-check\"></i> Apply");
				}
			});
		});
		
		
		
		$(document).on("click", ".apply-default-schedule", function(){
			
			var this_button = $(this);
			
			var data = this_button.closest(".panel-collapse").find(".manage-schedule-wrapper #form-schedule-settings").serialize() + "&use_default_schedule=1";
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/manageSchedule',
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					this_button.html("Saving Please Wait...");
				},
				success: function(response) {
					
					if( response.status == "error" )
					{
						this_button.html("Database Error.");
					}
					
					this_button.html("Apply Default");
				}
			});
		});
		
		
		$(document).on("click", ".apply-custom-schedule", function(){
			
			var this_button = $(this);
			
			var data = this_button.closest(".panel-collapse").find(".manage-schedule-wrapper #form-schedule-settings").serialize() + "&use_default_schedule=0";
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/calendar/manageSchedule',
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					this_button.html("Saving Please Wait...");
				},
				success: function(response) {
					
					if( response.status == "error" )
					{
						this_button.html("Database Error.");
					}
					
					this_button.html("<i class=\"ace-icon fa fa-check\"></i> Apply");
				}
			});
		});
		
		
		
		//start of calendar events
		
		$(document).on("click", ".add-calendar-btn", function(){
			
			var this_button = $(this);
			
			var office_id = $(this).attr("customer_office_id");
			
			var customer_id = $(this).attr("customer_id");
			
			var tab_content_container = $(".office-tab-content");
			
			var calendar_list_table = tab_content_container.find(".active .office-calendar-wrapper table > tbody");
					
			var createFormRequestOngoing = false;
			
			if( !createFormRequestOngoing )
			{
				createFormRequestOngoing = true;
		
				$.ajax({
					url: yii.urls.absoluteUrl + '/customer/calendar/create',
					type: 'post',
					dataType: 'json',
					data: { "ajax": 1, "office_id": office_id, "customer_id": customer_id },
					success: function(response) {

						createFormRequestOngoing = false;
						
						calendarCreateFormSaving = false;
						
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
							
							calendarCreateFormSaving = true;
							modal.find('button[data-action=save]').addClass("disabled");
							modal.find('button[data-action=save]').html("Saving Please Wait...");
							
							if( this_button.closest('.get-started-calendar-wrapper').length )
							{
								this_button.closest('.get-started-calendar-wrapper').find('.form-actions').html('<button office_id="' + office_id + '" class="btn btn-success get-started-calendar-next-btn">Next <i class="fa fa-arrow-right"></i></button>');
							}
							
							data = modal.find('form').serialize();
							
							$.ajax({
								url: yii.urls.absoluteUrl + '/customer/calendar/create',
								type: 'post',
								dataType: 'json',
								data: data,
								success: function(response) {
									
									if( response.status == "success" )
									{									
										calendar_list_table.html(response.html);
										
										modal.modal("hide");
									}
									
									calendarCreateFormSaving = false;
									modal.find('button[data-action=save]').removeClass("disabled");
									modal.find('button[data-action=save]').html('<i class="ace-icon fa fa-check"></i> Save');
								}
							});
						});
						
						modal.modal('show').on('hidden.bs.modal', function(){
							modal.remove();
						});
						
					}
				});
			}
			
		});
	
		
		$(document).on("click", ".edit-calendar-btn", function(){
			
			var calendar_id = $(this).prop("id");
			
			var tab_content_container = $(".office-tab-content");
			
			var calendar_list_table = tab_content_container.find(".active .office-calendar-wrapper table > tbody");
			
			var createFormRequestOngoing = false;
			
			if( !createFormRequestOngoing )
			{
				createFormRequestOngoing = true;
		
				$.ajax({
					url: yii.urls.absoluteUrl + '/customer/calendar/update',
					type: 'post',
					dataType: 'json',
					data: { "ajax": 1, "calendar_id": calendar_id },
					success: function(response) {

						createFormRequestOngoing = false;
						
						calendarEditFormSaving = false;
						
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
							
							calendarEditFormSaving = true;
							modal.find('button[data-action=save]').addClass("disabled");
							modal.find('button[data-action=save]').html("Saving Please Wait...");
							
							data = modal.find('form').serialize();
							
							$.ajax({
								url: yii.urls.absoluteUrl + '/customer/calendar/update',
								type: 'post',
								dataType: 'json',
								data: data,
								success: function(response) {
									
									if( response.status == "success" )
									{									
										calendar_list_table.html(response.html);
										
										modal.modal("hide");
									}
									
									calendarEditFormSaving = false;
									modal.find('button[data-action=save]').removeClass("disabled");
									modal.find('button[data-action=save]').html('<i class="ace-icon fa fa-check"></i> Save');
								}
							});
						});
						
						modal.modal('show').on('hidden.bs.modal', function(){
							modal.remove();
						});
						
					}
				});
			}
			
		});

		
		$(document).on("click", ".delete-calendar-btn", function(){
			
			var this_button = $(this);
			
			var row_container = this_button.closest("tr");
		
			var calendar_id = this_button.prop("id");
			
			var tab_content_container = $(".office-tab-content");
			
			var calendar_list_table = tab_content_container.find(".active .office-calendar-wrapper table > tbody");
			
			var deleteRequestOngoing = false;
			
			var hasList = $(this).attr("hasList");
			
			if( hasList > 0 )
			{
				alert("This calendar is currently associated with a lead list. Please reassign the lead list before deleting this calendar.");
				return false;
			}
			
			if( confirm("Are you sure you want to delete this calendar?") )
			{
				if( !deleteRequestOngoing )
				{
					deleteRequestOngoing = true;
			
					$.ajax({
						url: yii.urls.absoluteUrl + '/customer/calendar/delete',
						type: 'post',
						dataType: 'json',
						data: { "ajax": 1, "calendar_id": calendar_id },
						success: function(response) {
							
							if( response.status == "success" )
							{
								row_container.fadeOut( function() { 
									$(this).remove(); 
								});
							}
							
							deleteRequestOngoing = false;
							
						}
					});
				}
			}
		});
		
		//end of calendar events
		
		
		
		//start of office events 
	
		$(document).on("click", ".add-office-btn", function(){
			
			var customer_id = $(this).attr("customer_id");
			
			var office_tab_container = $("#myTab3");
			var tab_content_container = $(".office-tab-content");
			

			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/customerOffice/create',
				type: 'post',
				dataType: 'json',
				data: { "ajax":1, 'customer_id':customer_id },
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
					
					modal.find('.input-mask-phone').mask('(999) 999-9999');
					modal.find('.input-mask-zip').mask('99999');
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = '';
						
						if( modal.find("#CustomerOffice_office_name").val() == "" )
						{
							errors += "Name is required. \n \n";
						}
						
						if( modal.find("#CustomerOffice_address").val() == "" )
						{
							errors += "Address is required. \n \n";
						}
						
						if( modal.find("#CustomerOffice_phone").val() == "" )
						{
							errors += "Phone is required. \n \n";
						}
						
						if( modal.find("#CustomerOffice_city").val() == "" )
						{
							errors += "City is required. \n \n";
						}
						
						if( modal.find("#CustomerOffice_state").val() == "" )
						{
							errors += "State is required. \n \n";
						}
						
						if( modal.find("#CustomerOffice_zip").val() == "" )
						{
							errors += "Zip is required. \n \n";
						}
						
						
						if(errors != '')
						{
							alert(errors);
							return false;
						}
						
						
						var data = modal.find('form').serialize() + "&ajax=1";
							
						$.ajax({
							url: yii.urls.absoluteUrl + '/customer/customerOffice/create',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									$("#myTab3 li").removeClass("active");
									$(".office-tab-content .tab-pane").removeClass("active");
									
									$("#myTab3 li:last").before("<li class=\"active\"><a data-toggle=\"tab\" href=\"#office" + response.office.id + "\">" + response.office.name + "</a></li>");
									
									tab_content_container.append(response.html);
									
									tab_content_container.show();
									
									modal.modal("hide");
								}
							}
						});
					});
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});

		});
		
		
		$(document).on("click", ".office-settings-wrapper .save-office-btn", function(){
			
			var this_button = $(this);
			
			var office_id = this_button.closest(".panel-body").find(".update-office-id").val();
			
			var office_tab_container = $("#myTab3");
			
			var office_tab = office_tab_container.find("li#" + office_id + " > a");
			
			var data = this_button.closest("form").serialize() + "&office_id=" + office_id + "&ajax=1";			
						
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/customerOffice/update',
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					this_button.html("Saving Please Wait...");
				},
				success: function(response) {
					
					if( response.status == "success" )
					{
						office_tab.text(response.office_name);
					}
					
					this_button.html("Save");
				}
			});
			
		});
		
		//end of office events
		
		
		//start of staff events
		
		$(document).on("click", ".add-staff-btn", function(){
					
			var this_button = $(this);
			
			var customer_id = $(this).attr("customer_id");
			var customer_office_id = $(this).attr("customer_office_id");
			
			var tab_content_container = $(".office-tab-content");
			
			var staff_list_table = tab_content_container.find(".active .office-staff-wrapper table > tbody");
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/create',
				type: 'post',
				dataType: 'json',
				data: { "ajax":1, "customer_id":customer_id, "customer_office_id":customer_office_id },
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
					
					modal.find('.input-mask-phone').mask('(999) 999-9999');
					modal.find('.input-mask-zip').mask('99999');
					
					modal.find('.chosen').chosen({
						width: "100%",
						no_results_text: "No available calendar found."
					}); 
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = '';
						
						if( $("#CustomerOfficeStaff_staff_name").val() == "" )
						{
							errors += "Name is required. \n \n";
						}
						
						if( $("#CustomerOfficeStaff_email_address").val() == "" )
						{
							errors += "Email is required. \n \n";
						}
						else
						{
							if( !isValidEmail($("#CustomerOfficeStaff_email_address").val()) )
							{
								 errors += "Please enter a valid Email Address. \n \n";
							}
						}
						
						
						if(errors != '')
						{
							alert(errors);
							return false;
						}
						
						//updated get started staff next button
						if( this_button.closest('.get-started-staff-wrapper').length )
						{
							this_button.closest('.get-started-staff-wrapper').find('.form-actions').html('<button class="btn btn-success get-started-staff-next-btn">Next <i class="fa fa-arrow-right"></i></button>');
						}
						
						data = modal.find('form').serialize() + "&ajax=1";
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/create',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									tab_content_container.find(".active .add-calendar-btn").removeClass("btn-grey disabled").addClass("btn-primary");
									
									staff_list_table.html(response.html);
									
									modal.modal("hide");
								}
							}
						});
					});
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});

		});
		
		$(document).on("click", ".resend-staff-email", function(e){
			
			e.preventDefault();
			
			var hUrl = $(this).prop("href");
			$.ajax({
				url: hUrl,
				type: 'post',
				dataType: 'json',
				data: { "ajax":1},
				success: function(response) {
					
					if(response.status  == "success")
					{
						alert("An email has been sent to the staff");
					}
					
				}
			});

		});
		
		$(document).on("click", ".edit-staff-btn", function(){
			
			var id = $(this).prop("id");
			
			var office_tab_container = $("#myTab3");
			var tab_content_container = $(".office-tab-content");
			
			var staff_list_table = tab_content_container.find(".active .office-staff-wrapper table > tbody");

			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/update',
				type: 'post',
				dataType: 'json',
				data: { "ajax":1, "id":id },
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
					
					modal.find('.chosen').chosen({
						width: "100%",
						no_results_text: "No available calendar found."
					}); 
					
					modal.find('.input-mask-phone').mask('(999) 999-9999');

					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = '';
						
						if( $("#CustomerOfficeStaff_staff_name").val() == "" )
						{
							errors += "Name is required. \n \n";
						}
						
						if( $("#CustomerOfficeStaff_email_address").val() == "" )
						{
							errors += "Email is required. \n \n";
						}
						else
						{
							if( !isValidEmail($("#CustomerOfficeStaff_email_address").val()) )
							{
								 errors += "Please enter a valid Email Address. \n \n";
							}
						}
						
						
						if(errors != '')
						{
							alert(errors);
							return false;
						}
						
						data = modal.find('form').serialize() + "&id=" + id + "&ajax=1";
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/update',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									tab_content_container.find(".active .add-calendar-btn").removeClass("btn-grey disabled").addClass("btn-primary");
									
									staff_list_table.html(response.html);
									
									modal.modal("hide");
								}
							}
						});
					});
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});

		});
		
		
		$(document).on("click", ".delete-staff-btn", function(){
			
			var id = $(this).prop("id");
			
			var office_tab_container = $("#myTab3");
			var tab_content_container = $(".office-tab-content");
			
			var staff_list_table = tab_content_container.find(".active .office-staff-wrapper table > tbody");
			
			if( confirm("Are you sure you want to remove this staff?") )
			{
				if( $(this).attr("has_calendar_assigned") == 0 )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/delete',
						type: 'post',
						dataType: 'json',
						data: { 
							"ajax": 1, 
							"id": id, 
							"show_reassign_form": 0 
						},
						success: function(response) {
							
							if(response.status  == "success")
							{
								staff_list_table.html(response.html);
							}
						}
					});
				}
				else
				{
					$.ajax({
						url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/delete',
						type: 'post',
						dataType: 'json',
						data: { 
							"ajax": 1, 
							"id": id, 
							"show_reassign_form": 1 
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
								
								data = modal.find('form').serialize() + "&id=" + id;
								
								$.ajax({
									url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/delete',
									type: 'post',
									dataType: 'json',
									data: data,
									success: function(response) {
										
										if( response.status == "success" )
										{
											staff_list_table.html(response.html);
											
											modal.modal("hide");
										}
									}
								});
							});
							
							modal.modal('show').on('hidden.bs.modal', function(){
								modal.remove();
							});
						}
					});
				}
			}

		});
		
		
		$(document).on("click", ".add-existing-staff-btn", function(){
			
			var office_id = $(this).attr("customer_office_id");
			
			var office_tab_container = $("#myTab3");
			var tab_content_container = $(".office-tab-content");
			
			var staff_list_table = tab_content_container.find(".active .office-staff-wrapper table > tbody");
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/addExistingStaff',
				type: 'post',
				dataType: 'json',
				data: { 
					"ajax": 1, 
					"office_id": office_id,  
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
						
						data = modal.find('form').serialize() + "&office_id=" + office_id;
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/customer/customerOfficeStaff/addExistingStaff',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									staff_list_table.html(response.html);
									
									modal.modal("hide");
								}
							}
						});
					});
					
					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
				}
			});
			
		});
		//end of staff events
		
		
		
		//start of get started events
				
		$(document).on("click", ".get-started-staff-next-btn", function(){
			
 			//update step progress
			$(this).closest(".tab-pane").find(".steps > .add-staff-step").removeClass("active").addClass("complete");
			
			$(this).closest(".tab-pane").find(".steps > .add-calendar-step ").addClass("active");
			
			//hide staff
			$(this).closest(".get-started-staff-wrapper").hide();
			
			//show calendar
			$(this).closest(".tab-pane").find(".get-started-calendar-wrapper").fadeIn();
			
		});
		
		
		$(document).on("click", ".get-started-calendar-next-btn", function(){
			
			var this_button = $(this);
			
			var office_id = this_button.attr("office_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/customer/calendar/getStandardTabLayout',
				type: 'post',
				dataType: 'json',
				data: { "ajax":1, "office_id": office_id },
				success: function(response) {
					this_button.closest(".tab-pane").html( response.html );
				}
			});
			
		});
		
		//end of get started events
	});
	
});