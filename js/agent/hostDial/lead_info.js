$( function(){
	
	$(document).ready(function () {

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
					
					var modal = $(modal).appendTo("body");
					
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
	});
});