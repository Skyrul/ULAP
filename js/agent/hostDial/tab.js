$( function(){
	
	$(document).ready( function(){
		
		$("#agentStatsTab").on("click", function(){

			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/loadAgentStats",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				beforeSend: function(){ 
					$("#agentStats").html("<h1 class=\"blue center lighter\">Loading agent stats please wait...</h1>");
				},
				error: function(){
					$("#agentStats").html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
				},
				success: function(response) {
					
					if(response.html != "")
					{
						$("#agentStats").html(response.html);
					}
					else
					{
						$("#agentStats").html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
					}
				},
			});
			
		});
		
		$(document).on("click", ".data-tab-submit-btn", function(){
			
			var this_button = $(this);
			
			var formData = $("#dataTabForm").serialize() + "&ajax=1";
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/updateDataTab",
				type: "post",
				dataType: "json",
				data: formData,
				beforeSend: function(){ 
					this_button.html("Saving please wait...");
				},
				error: function(){
					this_button.html("Database Error <i class=\"fa fa-arrow-right\"></i>");
				},
				success: function(response) {
					
					alert(response.message);
					
					this_button.html("Save <i class=\"fa fa-arrow-right\"></i>");
				},
			});
			
		});
		
		$(document).on("change", ".data-tab-dropdown", function(){
			
			var list_id = $(this).val();
			var lead_id = $(this).attr("lead_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/ajaxLoadCustomData",
				type: "post",
				dataType: "json",
				data: { 		
					"ajax" : 1,	
					"list_id" : list_id,	
					"lead_id" : lead_id,	
				},
				success: function(r){
					$(".data-fields-tab").html(r.html);
					
				},
			});
		});
		
		//tabs on click ajax load scripts
		$(document).on("click", "#dataTab", function(){

			var tab_content_div = $("#data");
		
			if( tab_content_div.html() == "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/loadDataTab",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "lead_id":current_lead_id },
					beforeSend: function(){ 
						tab_content_div.html("<h1 class=\"blue center lighter\">Loading data please wait...</h1>");
					},
					error: function(){
						tab_content_div.html("<h1 class=\"red center lighter\">Error on loading data</h1>");
					},
					success: function(response) {
						
						if(response.html != "")
						{
							tab_content_div.html(response.html);
						}
						else
						{
							tab_content_div.html("<h1 class=\"red center lighter\">Error on loading data</h1>");
						}
					},
				});
			}
			
		});
		
		$(document).on("click", "#surveyTab", function(){

			var tab_content_div = $("#surveys");
		
			if( tab_content_div.html() == "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/loadSurveyTab",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "lead_id":current_lead_id },
					beforeSend: function(){ 
						tab_content_div.html("<h1 class=\"blue center lighter\">Loading survey please wait...</h1>");
					},
					error: function(){
						tab_content_div.html("<h1 class=\"red center lighter\">Error on loading survey</h1>");
					},
					success: function(response) {
						
						if(response.html != "")
						{
							tab_content_div.html(response.html);
						}
						else
						{
							tab_content_div.html("<h1 class=\"red center lighter\">Error on loading survey</h1>");
						}
					},
				});
			}
			
		});
		
		$(document).on("click", "#agentStatsTab", function(){

			var tab_content_div = $("#agentStats");
		
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/loadAgentStats",
				type: "post",
				dataType: "json",
				data: { "ajax":1 },
				beforeSend: function(){ 
					tab_content_div.html("<h1 class=\"blue center lighter\">Loading agent stats please wait...</h1>");
				},
				error: function(){
					tab_content_div.html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
				},
				success: function(response) {
					
					if(response.html != "")
					{
						tab_content_div.html(response.html);
					}
					else
					{
						tab_content_div.html("<h1 class=\"red center lighter\">Error on loading agent stats</h1>");
					}
				},
			});
		});
		
		$(document).on("click", "#scriptTab", function(){

			var tab_content_div = $("#script");
		
			if( tab_content_div.html() == "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/loadScriptTab",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "lead_id":current_lead_id },
					beforeSend: function(){ 
						tab_content_div.html("<h1 class=\"blue center lighter\">Loading script please wait...</h1>");
					},
					error: function(){
						tab_content_div.html("<h1 class=\"red center lighter\">Error on loading script</h1>");
					},
					success: function(response) {
						
						if(response.html != "")
						{
							tab_content_div.html(response.html);
						}
						else
						{
							tab_content_div.html("<h1 class=\"red center lighter\">Error on loading script</h1>");
						}
					},
				});
			}
			
		});
		
	});
	
});