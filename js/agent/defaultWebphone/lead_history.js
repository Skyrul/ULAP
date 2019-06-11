$( function(){
	
	$(document).ready( function(){

		$(document).on("click", ".lead-history-submit-btn", function(){
				
			if( $("#LeadHistory_note").val() != "" )
			{
				data = $("#leadHistoryForm").serialize();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/createLeadHistory",
					type: "post",
					dataType: "json",
					data: data,
					success: function(response) { 

						$("#LeadHistory_note").val("");
					
						if( response.html != "" )
						{
							$("#leadHistoryList .items").prepend(response.html);
						}
					}
				});
			}
			
		});
	});
	
});