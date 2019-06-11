$(function() {
	var maxFileSizeAllowed = "100mb";
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
	uploader = new plupload.Uploader({
		runtimes : "html5, html4",
		browse_button : "plupload-select-files",
		container: "sources",
		max_file_size : maxFileSizeAllowed,
		multi_selection: true,
		chunk_size: "1mb",
		unique_names: true,
		file_data_name : "FileUpload[filename]",
		url : yii.urls.absoluteUrl + "/customer/history/uploadMyFile",
		// flash_swf_url : yii.urls.baseUrl + "/js/plupload/plupload.flash.swf",
		filters : [
			{
				title : "Select Files", 
				extensions : "jpg,jpeg,gif,png,doc,docx,pdf,xls,xlsx"
			}
		]
	});
	
	uploader.bind("Init", function(up, params) {
		$("#sources a#plupload-select-files").html("<i class=\"fa fa-upload\"></i> Upload");
	});
	
	uploader.init();

	uploader.bind("FilesAdded", function(up, files) {
		var fileUploadCount = up.files.length;
		var error = "";
		
		if (maxFileCount > 0)
		{
			if ($(".filelist").children().length >= maxFileCount || fileUploadCount > maxFileCount)
			{
				error = "You may only select and upload a total of " + maxFileCount + " file(s)";
			}
		}
		
		if (error === "")
		{
			for (var i in files) {
				$("#sources .filelist").append("\
					<span class=\"label label-white label-inverse\" id=\"" + files[i].id + "\">\
						<span title=\"" + files[i].name + "\" class=\"filename\">" + formatFilename(files[i].name, 20) + "</span>\
						<span class=\"percentage\"></span>\
						<a class=\"remove-file-link\" href=\"#\"><i class=\"fa fa-times red\"></i></a>\
					</span>\
				");
			}
			
			up.start();
		}
		else
		{
			alert(error);
			
			for (var i in files) {
				file = uploader.getFile(files[i].id);
				uploader.removeFile(file);
			}
		}
	});
	
	uploader.bind("UploadProgress", function(up, file) {
		$("#" + file.id).find(".percentage").html("(" + file.percent + "%)");
		$("#" + file.id).find(".progress .bar-wrapper .bar").css("width", file.percent + "%");
	});
	
	uploader.bind("FileUploaded", function(up, file, info) {
		var response = $.parseJSON(info.response);
		
		console.log(response);
		
		if (typeof response.error == "object")
		{
			var errorFileContainer = $("#" + file.id);
			var errorMessage = "(" + file.name + ")" + " " + response.error.message + " This file will not be uploaded.";
			var errorObj = $("<div>" + errorMessage + "</div>");
								
			// errorFileContainer.remove();
			up.removeFile(file);
			
			$("#errors").append(errorObj);
			errorObj.delay(2000).fadeOut(2000, function() {
				errorObj.remove();
			});
			
			up.refresh();
		}
		else
		{						
			// $("#" + file.id).find("span.filename").css("color", "green");
			
			$("#" + file.id).find("div.percentage").css("display", "none");
			
			$("#" + file.id).append('<input type=\"hidden\" name=\"fileUploads[]\" value=\"'+response.generatedFileUploadId+'\">');
			
			$("#" + file.id).remove();
		}
	});
	
	
	uploader.bind('BeforeUpload', function(up) {
		up.settings.url = yii.urls.absoluteUrl + "/customer/history/uploadMyFile?customer_id=" + customer_id;
	});
	
	uploader.bind("QueueChanged", function(up) {
		if (up.files.length > 0 && (up.state == undefined || up.state != plupload.STARTED))
		{
			up.start();
		}
	});
	
	uploader.bind("StateChanged", function(up) {
		switch (up.state)
		{
			case plupload.STARTED:
				uploadInProgress = true;
				break;
			case plupload.STOPPED:
				uploadInProgress = false;
				$.fn.yiiListView.update("myFilesList", {});
				break;
		}
	});
	
	uploader.bind("Error", function(up, args) {
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
	
	$("#sources").on("click", ".remove-file-link", function(e) {
		e.preventDefault();
		
		var fileContainer = $(this).parent();
		var fileContainerId = fileContainer.prop("id");
		
		if (uploader)
		{
			var file = uploader.getFile(fileContainerId);
			
			fileContainer.fadeOut(500, function() {
				$(this).remove();
				
				if (typeof(file) != "undefined")
				{
					if (file.status == 2) {
						uploader.stop();
					}

					uploader.removeFile(file);
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