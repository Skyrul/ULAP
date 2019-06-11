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
	uploader2 = new plupload.Uploader({
		runtimes : "html5, html4",
		browse_button : "plupload-document-select-files",
		container: "document-sources",
		max_file_size : maxFileSizeAllowed,
		multi_selection: true,
		chunk_size: "1mb",
		unique_names: true,
		file_data_name : "FileUpload[filename]",
		url : yii.urls.absoluteUrl + "/hr/accountUser/documentUpload?user_account_id=" + user_account_id,
		// flash_swf_url : yii.urls.baseUrl + "/js/plupload/plupload.flash.swf",
		filters : [
			{
				title : "Select Files", 
				extensions : "jpg,jpeg,gif,png,doc,docx,pdf,xls,xlsx,txt,zip"
			}
		]
	});
	
	uploader2.bind("Init", function(up, params) {
		$("#plupload-document-select-files").html("Upload");
	});
	
	uploader2.init();

	uploader2.bind("FilesAdded", function(up, files) {
		var fileUploadCount = up.files.length;
		var error = "";
		
		if (maxFileCount > 0)
		{
			if ($(".document-filelist").children().length >= maxFileCount || fileUploadCount > maxFileCount)
			{
				error = "You may only select and upload a total of " + maxFileCount + " file(s)";
			}
		}
		
		if (error === "")
		{
			for (var i in files) {
				$("#document-sources .document-filelist").append("\
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
				file = uploader2.getFile(files[i].id);
				uploader2.removeFile(file);
			}
		}
	});
	
	uploader2.bind("UploadProgress", function(up, file) {
		$("#" + file.id).find(".percentage").html("(" + file.percent + "%)");
		$("#" + file.id).find(".progress .bar-wrapper .bar").css("width", file.percent + "%");
	});
	
	uploader2.bind("FileUploaded", function(up, file, info) {
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
			
			$("#" + file.id).remove();
			
			$.fn.yiiListView.update("docsList", {});
		}
	});
	
	uploader2.bind("QueueChanged", function(up) {
		if (up.files.length > 0 && (up.state == undefined || up.state != plupload.STARTED))
		{
			up.start();
		}
	});
	
	uploader2.bind("StateChanged", function(up) {
		switch (up.state)
		{
			case plupload.STARTED:
				uploadInProgress = true;
				break;
			case plupload.STOPPED:
				uploadInProgress = false;
				break;
		}
	});
	
	uploader2.bind("Error", function(up, args) {
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
	
	$("#document-sources").on("click", ".remove-file-link", function(e) {
		e.preventDefault();
		
		var fileContainer = $(this).parent();
		var fileContainerId = fileContainer.prop("id");
		
		if (uploader2)
		{
			var file = uploader2.getFile(fileContainerId);
			
			fileContainer.fadeOut(500, function() {
				$(this).remove();
				
				if (typeof(file) != "undefined")
				{
					if (file.status == 2) {
						uploader2.stop();
					}

					uploader2.removeFile(file);
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