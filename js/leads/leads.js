$( function(){
	
	$(document).on("change", "#select-list", function(){
		
		var value = $(this).val();
		var customer_id = $(this).attr("customer_id");
		
		if( value == "Create New" )
		{
			$(location).attr("href", yii.urls.absoluteUrl + "/customer/lists/create?customer_id=" + customer_id);
		}
		else
		{
			$(location).attr("href", yii.urls.absoluteUrl + "/customer/leads/index?id=" + value + "&customer_id=" + customer_id);
		}
		
	});
	
	
	var ajaxLeadViewProcessing = false;
	
	$(document).on("click", ".lead-details", function(){
		
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			var id = $(this).prop("id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/leads/view",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id": id },
				success: function(response) {
					
					ajaxLeadViewProcessing = false;
					
					if(response.html  != '' )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo('body');
					
					modal.find('.date-picker').datepicker({
						autoclose: true,
						todayHighlight: true
					});
					
					modal.find('.input-mask-phone').mask('(999) 999-9999');
					modal.find('.input-mask-zip').mask('99999');
					
					modal.find('button[data-action=save]').on('click', function() {
								
						modal.find('button[data-action=save]').addClass("disabled");
						modal.find('button[data-action=save]').html("Saving Please Wait...");

						data = modal.find('form').serialize();
						
						$.ajax({
							url: yii.urls.absoluteUrl + '/customer/leads/view',
							type: 'post',
							dataType: 'json',
							data: data,
							success: function(response) {
								
								$.fn.yiiListView.update("leadList", {});
								
								if( response.status == "success" )
								{									
									modal.modal("hide");
								}
								
								modal.find('button[data-action=save]').removeClass("disabled");
								modal.find('button[data-action=save]').html('Save');
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
	
	
	$(document).on("click", ".lead-delete", function(){
		
		var container = $(this).closest("tr");
		
		if( confirm("Are you sure you want to delete this lead?") )
		{
			if( !ajaxLeadViewProcessing )
			{
				ajaxLeadViewProcessing = true;
				
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/leads/delete",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
						
						ajaxLeadViewProcessing = false;
						
						if( response.status == "success" )
						{
							container.hide();
						}
					}
				});
			}
		}
		
	});
	
	
	$(document).on("keyup", ".manual-lead-input", function(e) {
		
		if (e.which == 13) 
		{
			var data = $("form#leadsManualEnter").serialize() + "&list_id=" + list_id + "&customer_id=" + customer_id;
	
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/leads/create",
				type: "post",
				dataType: "json",
				data: data,
				success: function(response) {
					
					$.fn.yiiListView.update("leadList", {});
					
					$(".manual-lead-input").val("");
					
				}
			});
			
		}
	});
	
	
	$(document).on("keyup", "#lead-search-input", function(e) {
		
		e.preventDefault();
		
		var search_query = $("#lead-search-input").val();
		
		$.fn.yiiListView.update("leadList", { data: {search_query: search_query} });
	});
	
	
	$(document).on("click", ".lead-remove", function(){
		
		var container = $(this).closest("tr");
		
		if( confirm("Are you sure you want to remove this lead?") )
		{
			if( !ajaxLeadViewProcessing )
			{
				ajaxLeadViewProcessing = true;
				
				var id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/leads/remove",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "id": id },
					success: function(response) {
						
						ajaxLeadViewProcessing = false;
						
						if( response.status == "success" )
						{
							container.hide();
						}
					}
				});
			}
		}
		
	});

});