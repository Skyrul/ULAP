$(function() {
	
	$(document).ready(function(){
		
		$(document).on("click", ".btn-add-state-holiday", function(){
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/dncHoliday/addStateHoliday",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				beforeSend: function(){},
				success: function( response ){
					
					if(response.html  != "" )
					{
						modal = response.html;
					}
			
					var modal = $(modal).appendTo("body");
					
					modal.find(".datepicker").datepicker({
						// minDate: 0,
						autoclose: true,
						todayHighlight: true
					});
					
					modal.find("button[data-action=save]").on("click", function() {

						var errors = "";
						
						if( modal.find("#DncHolidayState_state").val() == "" )
						{
							errors += "State is required. \n\n"	
						}
						
						if( modal.find("#DncHolidayState_name").val() == "" )
						{
							errors += "Name is required. \n\n"	
						}
						
						if( modal.find("#DncHolidayState_date").val() == "" )
						{
							errors += "Date is required. \n\n"	
						}
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{					
							data = modal.find("form").serialize() + "&ajax=1";
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/admin/dncHoliday/addStateHoliday",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){
									modal.find("button[data-action=save]").html("Saving Please Wait...");
									modal.find("button[data-action=save]").prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{													
										modal.modal("hide");
									}
									
									if( response.html != "" )
									{
										$(".table > tbody").html(response.html);
									}
									
									$("div.alert-message").html(response.message);
									$("div.alert").fadeIn();
									
									modal.find("button[data-action=save]").html("Save");
									modal.find("button[data-action=save]").prop("disabled", false);
									
									$.fn.yiiListView.update("stateHolidayList");
								}
							});
						}
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
					
				},
			});
			
		});
		
		$(document).on("click", ".btn-edit-state-holiday", function(){
			
			var id = $(this).prop("id");
			var this_row = $(this).closest("tr"); 
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/dncHoliday/editStateHoliday",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id":id },
				beforeSend: function(){},
				success: function( response ){
					
					if(response.html  != "" )
					{
						modal = response.html;
					}
			
					var modal = $(modal).appendTo("body");
					
					modal.find(".datepicker").datepicker({
						// minDate: 0,
						autoclose: true,
						todayHighlight: true
					});
					
					modal.find("button[data-action=save]").on("click", function() {

						var errors = "";
						
						if( modal.find("#DncHolidayState_state").val() == "" )
						{
							errors += "State is required. \n\n"	
						}
						
						if( modal.find("#DncHolidayState_name").val() == "" )
						{
							errors += "Name is required. \n\n"	
						}
						
						if( modal.find("#DncHolidayState_date").val() == "" )
						{
							errors += "Date is required. \n\n"	
						}
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{					
							data = modal.find("form").serialize() + "&ajax=1&id="+id;
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/admin/dncHoliday/editStateHoliday",
								type: "post",
								dataType: "json",
								data: data,
								beforeSend: function(){
									modal.find("button[data-action=save]").html("Saving Please Wait...");
									modal.find("button[data-action=save]").prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{													
										modal.modal("hide");
									}
									
									if( response.updated_name != "" )
									{
										this_row.find(".model_name").text(response.updated_name);
									}
									
									$("div.alert-message").html(response.message);
									$("div.alert").fadeIn();
									
									modal.find("button[data-action=save]").html("Save");
									modal.find("button[data-action=save]").prop("disabled", false);
									
									$.fn.yiiListView.update("stateHolidayList");
								}
							});
						}
					});
					
					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
					
				},
			});
			
		});

		$(document).on("click", ".btn-delete-state-holiday", function(){
				
			var id = $(this).prop("id");
			var this_button = $(this);
			var this_row = this_button.closest("tr");
			
			if( confirm("Are you sure you want to delete this?") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/dncHoliday/deleteStateHoliday",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id":id },
					beforeSend: function(){ 
						this_button.html("Deleting..."); 
						this_button.prop("disabled", true);
					},
					success: function( response ){
						
						this_row.fadeOut(300, function(){ 
							$(this).remove();
						});
						
					},
				});
			}
		});
		
		$(document).on("click", ".alert-close", function(){
			
			$("div.alert-message").html("");
			$("div.alert").hide();
			
		});
	});
	
});