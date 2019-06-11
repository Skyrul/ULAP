$(function() {
	var maxFileSizeAllowed = "200mb";
	var maxFileCount = 0;
	var submitCalled = false; 
	
	function formatFilename(filename, trim)
	{		
		if (trim > 0)
		{
			if (filename.length > trim)
			{
				filename = filename.substr(0, trim) + "..";
			}
		}
		
		return filename;
	}
	
	// Setup plupload
	learningCenterUploader = new plupload.Uploader({
		runtimes : "flash, html5, html4",
		browse_button : "learning-center-plupload-select-files",
		container: "sources",
		max_file_size : maxFileSizeAllowed,
		multi_selection: true,
		chunk_size: "1mb",
		unique_names: true,
		file_data_name : "FileUpload[filename]",
		url : yii.urls.absoluteUrl + "/admin/learningCenter/upload",
		flash_swf_url : yii.urls.baseUrl + "/js/plupload/plupload.flash.swf",
		filters : [
			{
				title : "Select Files", 
				// extensions : "jpg,jpeg,gif,png,doc,docx,pdf,xls,xlsx,txt,zip"
				// extensions : "jpg,jpeg,gif,png"
			}
		]
	});
	
	learningCenterUploader.bind("Init", function(up, params) {
		$("div#learningCenterSources a#learning-center-plupload-select-files").html("<i class=\"fa fa-upload\"></i> Upload a File");
	});
	
	learningCenterUploader.init();

	learningCenterUploader.bind("FilesAdded", function(up, files) {
		var fileUploadCount = up.files.length;
		var error = "";
		
		if (maxFileCount > 0)
		{
			if ($("div.filelist").children().length >= maxFileCount || fileUploadCount > maxFileCount)
			{
				error = "You may only select and upload a total of " + maxFileCount + " file(s)";
			}
		}
		
		if (error === "")
		{
			for (var i in files) {
				$("div#sources .filelist").append("\
					<div class=\"selected-file computer-upload\" id=\"" + files[i].id + "\">\
						<div class=\"preview-image\">&nbsp;</div>\
						<div class=\"upload-info well\">\
							<div class=\"progress\">\
								<div class=\"bar-wrapper\">\
									<div class=\"bar\">&nbsp;</div>\
								</div>\
							</div>\
							<a class=\"remove-file-link\" href=\"#\"><i class=\"icon-remove\"></i> Remove</a>\
							<div class=\"upload-info-filename\" id=\"tag_`field_" + files[i].id + "\">\
								<span title=\"" + files[i].name + "\" class=\"filename\">" + formatFilename(files[i].name, 20) + "</span>\
								<span class=\"percentage\"></span>\
							</div>\
							<div class=\"clear\"></div>\
						</div>\
						<br style=\"clear:both\">\
					</div>\
				");
			}
			
			up.start();
		}
		else
		{
			alert(error);
			
			for (var i in files) {
				file = learningCenterUploader.getFile(files[i].id);
				learningCenterUploader.removeFile(file);
			}
		}
	});
	
	learningCenterUploader.bind("UploadProgress", function(up, file) {
		$("#" + file.id).find(".percentage").html("(" + file.percent + "%)");
		$("#" + file.id).find(".progress .bar-wrapper div.bar").css("width", file.percent + "%");
	});
	
	learningCenterUploader.bind("FileUploaded", function(up, file, info) {
		var response = $.parseJSON(info.response);
		
		console.log(response);
		$("#photo-container").html("<img src='"+yii.urls.baseUrl+"/fileupload/thumb/"+response.generatedFilename+"'>");
		
		if (typeof response.error == "object")
		{
			
			var errorFileContainer = $("#" + file.id);
			var errorMessage = "(" + file.name + ")" + " " + response.error.message + " This file will not be uploaded.";
			var errorObj = $("<div>" + errorMessage + "</div>");
								
			errorFileContainer.remove();
			up.removeFile(file);
			
			$("div#errors").append(errorObj);
			errorObj.delay(2000).fadeOut(2000, function() {
				errorObj.remove();
			});
			
			up.refresh();
		}
		else
		{						
			var errorFileContainer = $("#" + file.id);
			
			errorFileContainer.fadeOut(1000, function() {
				errorFileContainer.remove();
			});
			
			$("#" + file.id).find("span.filename").css("color", "green");
			
			
			// $("#" + file.id).append('<input type=\"hidden\" name=\"fileUploads[]\" value=\"'+response.generatedFileUploadId+'\">');
		}
	});

	
	learningCenterUploader.bind('BeforeUpload', function(up) {
		up.settings.url = yii.urls.absoluteUrl + "/admin/company/upload?event_id=" + event_id;
	});
	
	
	learningCenterUploader.bind("QueueChanged", function(up) {
		if (up.files.length > 0 && (up.state == undefined || up.state != plupload.STARTED))
		{
			up.start();
		}
	});
	
	learningCenterUploader.bind("StateChanged", function(up) {
		switch (up.state)
		{
			case plupload.STARTED:
				uploadInProgress = true;
				$("#submitBtn").prop("disabled", true);
				break;
			case plupload.STOPPED:
				uploadInProgress = false;
				$("#submitBtn").prop("disabled", false);
				
				// bootbox.alert("File upload finish it may take about 30 mins+ to extract the data in each file.");
				
				break;
		}
	});
	
	learningCenterUploader.bind("Error", function(up, args) {
		var fileUploadCount = up.files.length;
		var errorFileContainer = $("#" + args.file.id);
		var errorMessage = "(" + args.file.name + ")" + " " + args.message + " This file will not be uploaded.";
		var errorObj = $("<div>" + errorMessage + "</div>");
							
		errorFileContainer.remove();
		
		$("div#errors").append(errorObj);
		errorObj.delay(2000).fadeOut(2000, function() {
			errorObj.remove();
		});
		
		up.refresh();
	});
	
	$("#learningCenterSources").on("click", ".remove-file-link", function(e) {
		e.preventDefault();
		
		var fileContainer = $(this).parent().parent();
		var fileContainerId = fileContainer.prop("id");
		
		if (learningCenterUploader)
		{
			var file = learningCenterUploader.getFile(fileContainerId);
			
			fileContainer.fadeOut(500, function() {
				$(this).remove();
				
				if (typeof(file) != "undefined")
				{
					if (file.status == 2) {
						learningCenterUploader.stop();
					}

					learningCenterUploader.removeFile(file);
				}
			});
		
		}
		else
		{
			fileContainer.fadeOut(500, function() {
				$(this).remove();
			});
		}
	
	});
});