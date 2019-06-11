$( function(){

	function isValidEmail(email) {
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	}

	$(document).ready( function(){
		
		//start of staff events
		
		$(document).on("click", ".add-staff-btn", function(){
					
			var this_button = $(this);
			
			var customer_id = $(this).attr("customer_id");
			var customer_office_id = $(this).attr("customer_office_id");
			
			// var tab_content_container = $(".office-tab-content");
			
			var staff_list_table = $(".office-staff-tbl > tbody");
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/create',
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
						
						if( $("#Account_username").val() == "" )
						{
							errors += "Username is required. \n \n";
						}
						
						if( $("#Account_password").val() == "" )
						{
							errors += "Password is required. \n \n";
						}
						
						if( $("#CustomerOfficeStaff_staff_name").val() == "" )
						{
							errors += "Name is required. \n \n";
						}
						
						if( $("#CustomerOfficeStaff_email_address").val() == "" )
						{
							// errors += "Email is required. \n \n";
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
							url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/create',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								if( response.status == "success" )
								{
									// tab_content_container.find(".active .add-calendar-btn").removeClass("btn-grey disabled").addClass("btn-primary");
									
									staff_list_table.html(response.html);
									
									modal.modal("hide");
								}
								else
								{
									alert(response.message);
									return false;
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
				url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/update',
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
							// errors += "Email is required. \n \n";
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
							url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/update',
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
			
			var staff_list_table = $(".office-staff-tbl > tbody");
			
			if( confirm("Are you sure you want to remove this staff?") )
			{
				if( $(this).attr("has_calendar_assigned") == 0 )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/delete',
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
						url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/delete',
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
									url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/delete',
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
			
			var staff_list_table = $(".office-staff-tbl > tbody");
			
			$.ajax({
				url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/addExistingStaff',
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
							url: yii.urls.absoluteUrl + '/hostDial/customerOfficeStaff/addExistingStaff',
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
				
	});
	
});