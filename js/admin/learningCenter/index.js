$( function(){
	
	var ajaxLeadViewProcessing = false;
	
	$(document).on("click", ".add-category", function(){
		
		var company_id = $(this).attr("company_id");
	
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/addCategory",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "company_id": company_id },
				success: function(response) {

					ajaxLeadViewProcessing = false;
					
					if(response.html  != '' )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo('body');

					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = "";
						
						if( modal.find("#CompanyLearningCenterCategory_name").val() == "" )
						{
							errors += "Category name is required \n\n";
						}
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{			
							data = modal.find("form").serialize();								

							$.ajax({
								url: yii.urls.absoluteUrl + '/admin/learningCenter/addCategory',
								type: 'post',
								dataType: 'json',
								data: data,
								beforeSend: function(){
									modal.find('button[data-action=save]').html("Saving Please Wait...");
									modal.find('button[data-action=save]').prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{

										if( response.html != "" )
										{
											$(".category-wrapper").html(response.html);
										}
								
										alert(response.message);
										modal.modal("hide");
									}
									else
									{
										alert(response.message);
									}
									
									modal.find('button[data-action=save]').html('Save');
									modal.find('button[data-action=save]').prop("disabled", false);
								}
							});
						}
						
					});
				}
			});
		}
		
	});
	
	
	$(document).on("click", ".edit-category", function(){
		
		var id = $(this).attr("id");
	
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/editCategory",
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

					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = "";
						
						if( modal.find("#CompanyLearningCenterCategory_name").val() == "" )
						{
							errors += "Category name is required \n\n";
						}
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{			
							data = modal.find("form").serialize();								

							$.ajax({
								url: yii.urls.absoluteUrl + '/admin/learningCenter/editCategory',
								type: 'post',
								dataType: 'json',
								data: data,
								beforeSend: function(){
									modal.find('button[data-action=save]').html("Saving Please Wait...");
									modal.find('button[data-action=save]').prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{

										if( response.html != "" )
										{
											$(".category-wrapper").html(response.html);
										}
								
										alert(response.message);
										modal.modal("hide");
									}
									else
									{
										alert(response.message);
									}
									
									modal.find('button[data-action=save]').html('Save');
									modal.find('button[data-action=save]').prop("disabled", false);
								}
							});
						}
						
					});
				}
			});
		}
		
	});
	
	
	$(document).on("click", ".delete-category", function(){
		
		var this_button = $(this);
		var container = $(this).closest(".list-view");
		var category_id = $(this).attr("category_id");
		
		if( confirm("Are you sure you want to delete this category?") )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/deleteCategory",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "category_id": category_id },
				beforeSend: function(){
					this_button.html("Deleting...");
					this_button.addClass("disabled");
				},
				success: function(response) {
					container.fadeOut(300, function() { $(this).remove(); });
				}
			});
		}
	});
	
	
	$(document).on("click", ".add-file", function(){
		
		var categoryId = $(this).attr("category_id");
		var companyId = $(this).attr("company_id");
	
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/create",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "company_id": companyId, "category_id":categoryId },
				success: function(response) {

					ajaxLeadViewProcessing = false;
					
					if(response.html  != '' )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo('body');

					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = "";
						
						if( modal.find("#CompanyLearningCenterFile_title").val() == "" )
						{
							errors += "Title is required \n\n";
						}
						
						// if( modal.find("#CompanyLearningCenterFile_description").val() == "" )
						// {
							// errors += "Description is required \n\n";
						// }
						
						if( modal.find("#CompanyLearningCenterFile_fileupload_id").get(0).files.length == 0 )
						{
							errors += "File is required \n\n";
						}
						// else
						// {
							// if( modal.find('button[data-action=save]').attr("fileType") == 1 )
							// {
								// var fileExtensions = ['mp4', 'avi'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
							
							// if( modal.find('button[data-action=save]').attr("fileType") == 2 )
							// {
								// var fileExtensions = ['wav', 'mp3', 'aiff'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
							
							// if( modal.find('button[data-action=save]').attr("fileType") == 3 )
							// {
								// var fileExtensions = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'jpg', 'tiff', 'bmp'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
						// }
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{			
							var formData = new FormData();
							
							formData.append('file', modal.find("#CompanyLearningCenterFile_fileupload_id").get(0).files[0]);		

							if( modal.find("#CompanyLearningCenterFile_thumbnail_fileupload_id").get(0).files.length > 0 )
							{
								formData.append('thumbnailFile', modal.find("#CompanyLearningCenterFile_thumbnail_fileupload_id").get(0).files[0]);							
							}
							
							formData.append('CompanyLearningCenterFile[title]', modal.find("#CompanyLearningCenterFile_title").val());							
							formData.append('CompanyLearningCenterFile[description]', modal.find("#CompanyLearningCenterFile_description").val());													
							formData.append('CompanyLearningCenterFile[company_id]', modal.find("#CompanyLearningCenterFile_company_id").val());							
							formData.append('CompanyLearningCenterFile[type]', modal.find("#CompanyLearningCenterFile_type").val());							
							formData.append('CompanyLearningCenterFile[sort_order]', modal.find("#CompanyLearningCenterFile_sort_order").val());
							formData.append('CompanyLearningCenterFile[category_id]', categoryId);							
							formData.append('CompanyLearningCenterFile[company_id]', companyId);							

							$.ajax({
								url: yii.urls.absoluteUrl + '/admin/learningCenter/create',
								type: 'post',
								dataType: 'json',
								data: formData,
								processData: false,
								contentType: false,
								beforeSend: function(){
									modal.find('button[data-action=save]').html("Saving Please Wait...");
									modal.find('button[data-action=save]').prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{													
										alert(response.message);
										modal.modal("hide");
									}
									else
									{
										alert(response.message);
									}
									
									modal.find('button[data-action=save]').html('Save');
									modal.find('button[data-action=save]').prop("disabled", false);
									
									$.fn.yiiListView.update("learningCenter"+response.category+"List");
								}
							});
						}
						
					});
				}
			});
		}
		
	});
	
	
	$(document).on("click", ".edit-file", function(){
		
		var id = $(this).prop("id");
		
		var companyId = $(this).attr("company_id");
		
		if( !ajaxLeadViewProcessing )
		{
			ajaxLeadViewProcessing = true;
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/update",
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

					modal.modal('show').on('hidden.bs.modal', function(){
						modal.remove();
					});
					
					modal.find('button[data-action=save]').on('click', function() {
						
						var errors = "";
						
						if( modal.find("#CompanyLearningCenterFile_title").val() == "" )
						{
							errors += "Title is required \n\n";
						}
						
						// if( modal.find("#CompanyLearningCenterFile_description").val() == "" )
						// {
							// errors += "Description is required \n\n";
						// }
						
						// if( modal.find("#CompanyLearningCenterFile_fileupload_id").get(0).files.length > 0 )
						// {
							// if( modal.find('button[data-action=save]').attr("fileType") == 1 )
							// {
								// var fileExtensions = ['mp4', 'avi'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
							
							// if( modal.find('button[data-action=save]').attr("fileType") == 2 )
							// {
								// var fileExtensions = ['wav', 'mp3', 'aiff'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
							
							// if( modal.find('button[data-action=save]').attr("fileType") == 3 )
							// {
								// var fileExtensions = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'jpg', 'tiff', 'bmp'];
								
								// if( $.inArray( modal.find("#CompanyLearningCenterFile_fileupload_id").val().split('.').pop().toLowerCase(), fileExtensions) == -1 ) 
								// {
									// errors += "Formats allowed are: " + fileExtensions.join(', ');
								// }
							// }
						// }
						
						if( errors != "" )
						{
							alert(errors);
							return false;
						}
						else
						{			
							var formData = new FormData();
							
							if( modal.find("#CompanyLearningCenterFile_fileupload_id").get(0).files.length > 0 )
							{
								formData.append('file', modal.find("#CompanyLearningCenterFile_fileupload_id").get(0).files[0]);							
							}
							
							if( modal.find("#CompanyLearningCenterFile_thumbnail_fileupload_id").get(0).files.length > 0 )
							{
								formData.append('thumbnailFile', modal.find("#CompanyLearningCenterFile_thumbnail_fileupload_id").get(0).files[0]);							
							}
							
							formData.append('CompanyLearningCenterFile[id]', modal.find("#CompanyLearningCenterFile_id").val());							
							formData.append('CompanyLearningCenterFile[title]', modal.find("#CompanyLearningCenterFile_title").val());							
							formData.append('CompanyLearningCenterFile[description]', modal.find("#CompanyLearningCenterFile_description").val());
							formData.append('CompanyLearningCenterFile[sort_order]', modal.find("#CompanyLearningCenterFile_sort_order").val());							
							formData.append('CompanyLearningCenterFile[category_id]', modal.find("#CompanyLearningCenterFile_category_id").val());							
							formData.append('CompanyLearningCenterFile[company_id]', modal.find("#CompanyLearningCenterFile_company_id").val());							
							

							$.ajax({
								url: yii.urls.absoluteUrl + '/admin/learningCenter/update',
								type: 'post',
								dataType: 'json',
								data: formData,
								processData: false,
								contentType: false,
								beforeSend: function(){
									modal.find('button[data-action=save]').html("Saving Please Wait...");
									modal.find('button[data-action=save]').prop("disabled", true);
								},
								success: function(response) {
										
									if( response.status == "success" )
									{													
										alert(response.message);
										modal.modal("hide");
									}
									else
									{
										alert(response.message);
									}
									
									modal.find('button[data-action=save]').html('Save');
									modal.find('button[data-action=save]').prop("disabled", false);
									
									$.fn.yiiListView.update("learningCenter"+response.category+"List");
								}
							});
						}
						
					});
				}
			});
		}
		
	});
	
	
	$(document).on("click", ".delete-file", function(){
		
		var this_button = $(this);
		var id = $(this).prop("id");
		
		if( confirm("Are you sure you want to delete this file?") )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/delete",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id": id },
				beforeSend: function(){
					this_button.html("Deleting...");
					this_button.addClass("disabled");
				},
				success: function(response) {
					$.fn.yiiListView.update("learningCenter"+response.category+"List");
				}
			});
		}
	});
	
	$(document).on("change", ".toggle-learning-center-tab", function(){
				
		var company_id = $(this).attr("company_id");
		
		if( $(this).is(":checked") )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/toggleLearningCenter",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "value": 1, "company_id": company_id },
				success: function(response) {

				}
			});
		}
		else
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/toggleLearningCenter",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "value": 0, "company_id": company_id },
				success: function(response) {

				}
			});
		}
		
	});
	
	$(document).on("change", ".toggle-learning-center-category", function(){
				
		var category_id = $(this).attr("category_id");
		
		if( $(this).is(":checked") )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/toggleLearningCenterCategory",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "value": 1, "category_id":category_id },
				success: function(response) {

				}
			});
		}
		else
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/toggleLearningCenterCategory",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "value": 0, "category_id":category_id },
				success: function(response) {

				}
			});
		}
		
	});
});