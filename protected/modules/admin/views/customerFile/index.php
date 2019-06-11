<?php 
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	##already added in Setup Tab##
	//$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	//$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/companyCustomerFile.js');
	$cs->registerScript(uniqid(), "
	
		var event_id = '".$company->id."';
	",CClientScript::POS_END);
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
		Customer ID Files
	</h1>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'customer-file-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array(
		'class'=> 'form-horizontal',
		'enctype' => 'multipart/form-data',
	),
)); ?>

	<div id="sourcesCustomerFile" class="padded" style="margin: 10px 0;">
		<div style="margin-top: 7px;" id="uploadCustomerFile">		
			<div>
				<a id="plupload-select-customer-files" class="btn btn-mini btn-primary bigger" href="#"> 
					<i class="icon-plus"></i>
					Initializing uploader, please wait...
				</a>
			</div>
			
			<br style="clear:both;">
		</div>

		<div class="filelist"> </div>
	</div>
	
	<div class="form-actions buttons center">
		<?php echo CHtml::submitButton($company->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary', 'id'=>'submitBtn')); ?>
	</div>
	
<?php $this->endWidget(); ?>
