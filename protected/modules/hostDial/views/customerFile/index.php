<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	// $cs->registerCssFile($baseUrl . '/css/extra.css');
	$cs->registerCssFile($baseUrl . '/template_assets/css/dropzone.css');
	
	// $cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	// $cs->registerScriptFile($baseUrl . '/js/customer/customerFile/multiple_uploader.js');
	$cs->registerScriptFile($baseUrl . '/template_assets/js/dropzone.min.js');
	
	// $cs->registerCss(uniqid(), '
		// .percentage { font-size:12px; font-weight:normal; }
	// ');
	
	$cs->registerScript(uniqid(), '
		
		var customer_id = "'.$customer->id.'";
		
	', CClientScript::POS_HEAD);

	
	$cs->registerScript(uniqid(), '

		Dropzone.autoDiscover = false;
		
		try 
		{
		  var myDropzone = new Dropzone("#dropzone" , {
			paramName: "file", 
			maxFilesize: 100, 
			// addRemoveLinks : true,
			dictDefaultMessage: "Click here to upload"
		  });
		} 
		catch(e) 
		{
		  alert("Dropzone.js does not support older browsers!");
		}
		
		myDropzone.on("complete", function(file) {
			$.fn.yiiListView.update("fileList", {}); 
		});
 
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.HostDialSideMenu",array(
		'active'=> 'customerFile',
		'customer' => $customer,
	));
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<div class="page-header">
	<h1>
		My Files
		<?php /*<span id="sources">	
			<a id="plupload-select-files" class="btn btn-info btn-minier" href="#"> 
				<i class="icon-plus"></i>
				Initializing uploader, please wait...
			</a>

			<span class="filelist" style="margin-left:15px;">
				<?php //<span class="label label-white label-inverse">Test attached file 1.txt <a href="#" class="remove-file-link"><i class="fa fa-times red"></i></a></span> ?>
			</span>
		</span>*/ ?>	
	</h1>
</div>

<div class="row" style="display:<?php echo !Yii::app()->user->account->checkPermission('customer_my_files_click_here_to_upload','visible') ? 'none':''; ?>">
	<div class="col-sm-12">
		<form action="<?php echo $this->createUrl('upload', array('customer_id'=>$customer->id)); ?>" id="dropzone" class="dropzone" method="post" enctype="multipart/form-data">
			<div class="fallback">
				<input name="FileUpload[filename]" type="file" multiple/>
			</div>
		</form>
	</div>
</div>

<div class="space-12"></div>

<div class="row">
	<div class="col-sm-12">
		<?php 
			$this->widget('zii.widgets.CListView', array(
				'id'=>'fileList',
				'dataProvider'=>$dataProvider,
				'viewData' => array('customerId'=>$customer->id),
				'itemView'=>'_list',
				'template'=>'<table class="table table-striped table-condensed table-hover table-bordered">{items}</table>',
			)); 
		?>		
	</div>
</div>

</p><i>*All files will automatically be deleted ninety days after upload.</i></p>