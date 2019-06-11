$( function(){

	tierAjaxSending = false;

	//setup admin tier tab
	$(document).ready( function(){
		
		$("body").on("click", "#settings_tier_view", function(){
		
			if(!tierAjaxSending)
			{
				tierAjaxSending = true;
				
				$.ajax({
					url: 'php/settings_tier/settings_tier.view.php',
					type: 'post',
					beforeSend: function(){
						$("#div_ajaxLoader").empty().append('Loading...');
					},
					complete: function(){
						tierAjaxSending = false;
						$("#div_ajaxLoader").empty();
					},
					error: function(){
						$("#div_ajaxLoader").empty().append('Error...');
					},
					success: function(r){
						$("#div_settings_tier_view_primary").html(r);
						
						tierAjaxSending = false;
					},
				});
			}
		});
		
		
		//add new tier
		$("body").on("click", ".add-new-tier, .add-child-tier", function(e){
			e.preventDefault();
		
			var tier_ParentTier_Id = $(this).attr("tier_ParentTier_Id");
			var tier_ParentSubTier_Id = $(this).attr("tier_ParentSubTier_Id");
			
			var tier_Company_Id = $(this).attr("tier_Company_Id");
			var tier_Company_Name = $(this).closest(".tab-content").find("input[name='company_Name']").val();
			
			var tier_Level = $(this).attr("tier_Level");
			var tier_Name = $(this).attr("tier_Name");
			
			if($.trim(tier_Name) != '')
			{
				header = tier_Company_Name + ' - Add tiers under ' + tier_Name;
			}
			else
			{
				header = tier_Company_Name + ' - Add new tier';
			}
			
			if(!tierAjaxSending)
			{
				tierAjaxSending = true;
				
				$.ajax({
					// url: 'php/settings_tier/settings_tier.insertsql.form.php',
					url: yii.urls.absoluteUrl + "/admin/tier/ajaxAddTier",
					type: 'post',	
					data: { 
						'tier_ParentTier_Id' : tier_ParentTier_Id,
						'tier_ParentSubTier_Id' : tier_ParentSubTier_Id,
						'tier_Company_Id': tier_Company_Id,						
						'tier_Level': tier_Level,						
					},
					beforeSend: function(){
						$("#div_ajaxLoader").empty().append('Loading...');
					},
					complete: function(){
						tierAjaxSending = false;
						$("#div_ajaxLoader").empty();
					},
					error: function(){
						$("#div_ajaxLoader").empty().append('Error...');
					},
					success: function(r){
						$("#myModal #myModalLabel").html(header);
						$("#myModal .modal-body").html(r);
						$('#myModal').modal();
					},
				});
			}
		});
		
		
		//edit tier
		$("body").on("click", ".edit-tier", function(){
		
			var tier_Id = $(this).prop("id");
			
			var tier_Company_Id = $(this).attr("tier_Company_Id");
			var tier_Company_Name = $(this).closest(".tab-content").find("input[name='company_Name']").val();
			
			var header = tier_Company_Name + ' - Edit ' + $(this).attr("tier_Name");
			
			var tier_name_element = $(this).parent().find(".tree-folder-name");
		
			if(!tierAjaxSending)
			{
				tierAjaxSending = true;
				
				$.ajax({
					// url: 'php/settings_tier/settings_tier.updatesql.form.php',
					url: yii.urls.absoluteUrl + "/admin/tier/ajaxEditTier",
					type: 'post',	
					data: { 
						'tier_Id': tier_Id,
						'tier_Company_Id': tier_Company_Id						
					},
					beforeSend: function(){
						$("#div_ajaxLoader").empty().append('Loading...');
					},
					complete: function(){
						tierAjaxSending = false;
						$("#div_ajaxLoader").empty();
					},
					error: function(){
						$("#div_ajaxLoader").empty().append('Error...');
					},
					success: function(r){
						$("#myModal #myModalLabel").html(header);
						$("#myModal .modal-body").html(r);
						$('#myModal').modal();
					},
				});
			}
		});
		
		
		//delete tier
		$("body").on("click", ".delete-tier", function(){
		
			this_row = $(this).closest(".tree-folder");
			
			var tier_Id = $(this).prop("id");
			
			if(!tierAjaxSending)
			{
				if(confirm("Are you sure you want to delete this?"))
				{
					tierAjaxSending = true;
					
					$.ajax({
						url: 'php/settings_tier/settings_tier.deletesql.php',
						type: 'post',
						dataType: 'json',
						data: { 'tier_Id': tier_Id },
						beforeSend: function(){
							$("#div_ajaxLoader").empty().append('Deleting...');
						},
						complete: function(){
							tierAjaxSending = false;
							$("#div_ajaxLoader").empty();
						},
						error: function(){
							$("#div_ajaxLoader").empty().append('Error...');
						},
						success: function(r){
							bootbox.hideAll();
							
							if(r.status == 'success')
							{
								_text = 'Database updated';
								_class_name = 'gritter-regular';
								
								this_row.fadeOut();
							}
							else
							{
								_text = 'Database error';
								_class_name = 'gritter-error';
							}
							
							$.gritter.add({
								title: 'Notice',
								text: _text,
								sticky: false,
								class_name: _class_name,
								time: 100,
							});
						},
					});
				}
			}
		});
		
		
		// collapse and load child tiers
		$("body").on("click", ".tree-minus, .tree-plus", function(){
	
			this_icon = $(this);
			this_tree_folder = $(this).closest(".tree-branch");
			tier_ParentSubTier_Id = this_tree_folder.prop("id");
			
			if(this_icon.hasClass("tree-plus"))
			{
				this_icon.removeClass("tree-plus").addClass("tree-minus");
				this_tree_folder.find(".tree-branch-content").slideDown();
				
				if(!tierAjaxSending)
				{
					tierAjaxSending = true;
					
					$.ajax({
						// url: 'php/settings_tier/settings_tier.ajaxLoadChild.php',
						url: yii.urls.absoluteUrl + "/admin/tier/ajaxLoadChild",
						type: 'post',
						dataType: 'json',
						data: { 'tier_ParentSubTier_Id':tier_ParentSubTier_Id },
						beforeSend: function(){
							$("#div_ajaxLoader").empty().append('Loading Tier Tree...');
						},
						complete: function(){
							tierAjaxSending = false;
							$("#div_ajaxLoader").empty();
						},
						error: function(){
							$("#div_ajaxLoader").empty().append('Error...');
						},
						success: function(r){
							if(r.status == 'success')
							{
								this_tree_folder.find(".tree-branch-children").html(r.html);
							}
						},
					});
				}
			}
			else
			{
				this_icon.removeClass("tree-minus").addClass("tree-plus");
				this_tree_folder.find(".tree-branch").slideUp();
			}
			
		});
		
		// collapse/uncollapse all
		$("body").on("click", "a.toggle-collapse-btn", function(){
			// if($(this).hasClass("open"))
			// {
				// $(this).text("Uncollapse All");
				// $(this).removeClass("open").addClass("close");
				// $("i.icon-plus").click();
			// }
			// else
			// {
				// $(this).text("Collapse All");
				// $(this).removeClass("close").addClass("open");
				// $("i.icon-minus").click();
			// }
			
			if(!tierAjaxSending)
			{
				tierAjaxSending = true;
				
				$.ajax({
					url: 'php/settings_tier/settings_tier.ajaxLoadTree.php',
					type: 'post',
					dataType: 'json',
					beforeSend: function(){
						$("#div_ajaxLoader").empty().append('Loading Tier Tree...');
					},
					complete: function(){
						tierAjaxSending = false;
						$("#div_ajaxLoader").empty();
					},
					error: function(){
						$("#div_ajaxLoader").empty().append('Error...');
					},
					success: function(r){
						if(r.status == 'success')
						{
							$("div.tree").html(r.html);
						}
						
						
					},
				});
			}
		});
		
		//company tier tab
		$('body').on("click", "ul#companyTierTabs a", function (e) {
		  e.preventDefault();

		  $(this).tab('show');
		})
	});
});