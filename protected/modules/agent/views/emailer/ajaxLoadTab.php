<?php Yii::app()->clientScript->registerScript('emailerJs','

	var hasEmailSettingSetup = 0;
	
	$("#emailPreviewBtn").on("click",function(){
		
		var tab_content_div = $("#emailPreviewContent");
		
		if($("#email_template_id").val() > 0)
		{
			
			validateMultipleEmailsCommaSeparated($("#emailTabEmailAddress"), "\'")
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/emailer/emailPreview",
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1, 
					"email_template_id" : $("#email_template_id").val(),
					"personal_note" : $("#personal_note").val(),
					"current_lead_id" : current_lead_id,
				},
				beforeSend: function(){ 
					tab_content_div.html("<h3 class=\"blue center lighter\">Loading preview templates please wait...</h1>");
				},
				error: function(){
					tab_content_div.html("<h3 class=\"red center lighter\">Error on loading preview</h1>");
				},
				success: function(response) {
					
					if(response.html != "")
					{
						$("#emailPreviewContent").html(response.html);
						$("#addAttachmentBtn").show();
					}
					else
					{
						$("#emailPreviewContent").html("<h3 class=\"red center lighter\">Error on loading preview</h1>");
					}
				},
			});
		}
		else
		{
			alert("Select Email Template");
			$("#emailPreviewContent").html("");
			$("#addAttachmentBtn").hide();
		}
	});
	
	function validateEmail(field) {
		var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,5}$/;
		return (regex.test(field)) ? true : false;
	}
	
	function validateMultipleEmailsCommaSeparated(emailcntl, seperator) {
		
		var value = emailcntl.val();
		if (value != "") {
			var result = value.split(seperator);
			for (var i = 0; i < result.length; i++) {
				if (result[i] != "") {
					if (!validateEmail(result[i])) {
						emailcntl.focus();
						alert("Please check, `" + result[i] + "` email addresses not valid!");
						return false;
					}
				}
			}
		}
		
		return true;
	}

	$("#addAttachmentBtn").on("click",function(){
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/agent/emailer/emailAttachment",
			type: "POST",	
			data: { 
				"current_skill_id" : current_skill_id
			},
			beforeSend: function(){
			},
			complete: function(){
			},
			error: function(){
			},
			success: function(r){
			
				header = "Attachment";
				$("#myModalMd #myModalLabel").html(header);
				$("#myModalMd .modal-body").html(r);
				$("#myModalMd").modal();
				
			},
		});
	});
		
		
		
	$("body").on("click", ".btn-add-attachment", function (){
		
		attachment_id = $(this).attr("attachment_id");
		fileupload_id = $(this).attr("fileupload_id");
		original_filename = $(this).attr("original_filename");
		
        var emailAttachmentHtml = "<span class=\"label label-white label-inverse\">"+						
			"<span class=\"filename\" title=\""+original_filename+"\"><a href=\"javascript:void(0)\">"+original_filename+"</a></span>"+					
			"<span class=\"percentage\"></span>"+						
			"<a href=\"javascript:void(0);\" id=\""+attachment_id+"\" class=\"existing-remove-file-link\"><i class=\"fa fa-times red\"></i></a>"+					
			"<input type=\"hidden\" name=\"otherAttachment[]\" value=\""+fileupload_id+"\">"+					
		"</span>";
		
		$(".filelist").append(emailAttachmentHtml);
		$("#myModalMd").modal("hide");
    });
	
	$("body").on("click", ".existing-remove-file-link", function (){
		$(this).parent().remove();
		
	});
	
	$("body").on("click", "#testBtn", function (){
		
		
		if($("#email_template_id").val() > 0)
		{
			
			if(validateMultipleEmailsCommaSeparated($("#emailTabEmailAddress"), "\'"))
			{
				
				 var otherAttachment = $("input[name=\"otherAttachment[]\"]").map(function(){ 
                    return this.value; 
                }).get();

				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/emailer/testSubmit",
					type: "post",
					dataType: "json",
					data: { 
						"ajax": 1, 
						"current_skill_id" : current_skill_id,
						"current_lead_id" : current_lead_id,
						"personal_note" : $("#personal_note").val(),
						"email_template_id" : $("#email_template_id").val(),
						"emailTabEmailAddress" : $("#emailTabEmailAddress").val(),
						"otherAttachment" : otherAttachment
					},
					beforeSend: function(){ 
						
					},
					error: function(){
						
					},
					success: function(response) {
						
						if(response.status == "success")
						{
							alert("Test Email Sent");
						}
					},
				});
			}
		}
		else
		{
			alert("Select Email Template");
		}
		
		
	});
	
',CClientScript::POS_END); ?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/template_assets/js/bootstrap.min.js'); ?>
<?php 
	Yii::import('ext.redactor.ImperaviRedactorWidget');

	$this->widget('ImperaviRedactorWidget',array(
		'selector' => '.redactor',
		'plugins' => array(
			'fontfamily' => array('js' => array('fontfamily.js')),
			'fontcolor' => array('js' => array('fontcolor.js')),
			'fontsize' => array('js' => array('fontsize.js')),
			'table' => array('js' => array('table.js')),
		),
		'options' => array(
			'imageUpload' => $this->createUrl('redactorUpload'),
			'dragImageUpload' => true,
			'buttons'=>array(
				'formatting', '|', 'bold', 'italic', 'deleted', 'alignment','fontcolor', 'fontsize', 'fontfamily', '|',
				'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
				'link', '|', 'image', '|', 'html', '|', 'table'
			),
		)
	));
?>

<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<div class="row">
	<div class="col-sm-offset-1 col-sm-11">
		
		<div class="row">
			<div class="col-sm-12">
				<h2><?php echo $lead->fullName; ?> - <?php echo $lead->customer->fullName; ?></h2>
			</div>
		</div>
		
		
		<div class="row">
			<div class="col-sm-12">
				<label>Template</label>
			</div>
			
			<div class="col-sm-12">
				<?php echo CHtml::dropDownList('email_template_id', '', Skill::emailTemplateList($current_skill_id), array('empty'=>'-Select Email Template-')); ?><br><br>
			</div>
			
			<div class="col-sm-12">
				<label>Email Address</label>
			</div>
			
			<div class="col-sm-12">
				<?php echo CHtml::textField('email_address',$email_address,array('size'=>'70','id'=>'emailTabEmailAddress')); ?>
				<span><i>(Multiple email address should be separated by commas)</i></span>
				
				<br><br>
			</div>
			
			<div class="col-sm-12">
				<label>Personal Note</label>
			</div>
			
			
			
	
			<div class="col-sm-12">
				<?php echo CHtml::textArea('personal_note','',array('class'=>'redactor','style'=>'width:600px;min-height:100px;')); ?>
				<br><br>
			</div>
		</div>
		
		
		<?php echo CHtml::button('Test Submit',array('class'=>'btn btn-primary btn-sm','id'=>'testBtn')); ?>
		<?php echo CHtml::button('Verify and Preview',array('class'=>'btn btn-primary btn-sm','id'=>'emailPreviewBtn')); ?>
		<button id="addAttachmentBtn" class="btn btn-primary btn-sm" style="display:none;"><i class="fa fa-paperclip"></i> Add Attachment</button>
		
		<br>
		<br>
		
		<div id="emailPreviewContainer">
			<div class="row">
				<div class="col-sm-12">
					<label>Preview </label>
				</div>
				
				<div class="col-sm-12" id="emailPreviewContent">
					No preview available yet.
				</div>
			</div>
		
		</div>
		
		
	</div>
</div>