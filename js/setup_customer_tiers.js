$( function(){

	tierAjaxSending = false;

	//setup customer tier tab
	$(document).ready( function(){
		
		
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
						url: yii.urls.absoluteUrl + "/admin/tier/ajaxCustomerLoadChild",
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
		
		
	});
});