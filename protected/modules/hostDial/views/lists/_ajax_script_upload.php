<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/template_assets/css/dropzone.css');
	$cs->registerScriptFile($baseUrl . '/template_assets/js/dropzone.min.js');

	
	$cs->registerScript(uniqid(), '

		Dropzone.autoDiscover = false;
		
		try 
		{
		  var myDropzone = new Dropzone("#dropzone" , {
			paramName: "file", 
			maxFilesize: 1, 
			// addRemoveLinks : true,
		  });
		} 
		catch(e) 
		{
		  alert("Dropzone.js does not support older browsers!");
		}
		
		myDropzone.on("complete", function(file) {
		});

	', CClientScript::POS_END);
?>

<div class="row">
	<div class="col-md-12">
	<?php
		if( $customerSkill->script_tab_fileupload_id != null )
		{
			echo '<small>'; 
				echo'<i class="fa fa-paperclip"></i> Current Script Tab File: ' . CHtml::link($customerSkill->scriptFileupload->original_filename, array('download', 'id'=>$customerSkill->script_tab_fileupload_id));
			echo '</small>';
		}
	?>
	</div>
</div>

<form action="<?php echo $actionController; ?>" id="dropzone" class="dropzone" method="post" enctype="multipart/form-data">
	<div class="fallback">
		<input name="FileUpload[filename]" type="file" multiple/>
	</div>
</form>

