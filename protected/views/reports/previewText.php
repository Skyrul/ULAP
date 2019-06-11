<?php 
	// Yii::import('ext.redactor.ImperaviRedactorWidget');
	
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCss(uniqid(), '
		.redactor-toolbar {
			background: #438EB9;
			box-shadow: none;
		}
		.redactor-toolbar li a {
			color: rgba(255, 255, 255, .55);
		}
		.redactor-toolbar li a:hover {
			background: #2C5976;
			color: #fff;
		}
		
		.tab-content { overflow:hidden !important; }
		
		span.filename > a { text-decoration:none; }
	');
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
	<h1>Email Monitoring &raquo; <small>Text Preview</small></h1>
</div>

<div class="row-fluid">
	<?php echo CHtml::link('&laquo; Back', array('reports/emailMonitor', 'filter'=>$filter) ); ?>
</div>

<br />

<div class="row-fluid">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'preview-form',
		'enableAjaxValidation'=>false,
	)); ?>
	
	<?php
		// $this->widget('ImperaviRedactorWidget',array(
			// 'model'=>$model,
			// 'attribute'=>'text_content',
			// 'options' => array(
				// 'imageUpload' => $this->createUrl('redactorUpload'),
				// 'dragImageUpload' => true,
				// 'buttons'=>array(
					// 'formatting', '|', 'bold', 'italic', 'deleted', 'alignment','fontcolor', '|',
					// 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
					// 'link', '|', 'image', '|', 'html', '|', 'table'
				// ),
			// )
		// ));
		
		echo $form->textArea($model, 'text_content', array('style'=>'width:100%; height:300px;'));
	?>
</div>

<div class="form-actions text-center">
	<button class="btn btn-primary btn-xs btn-submit">Save</button>
</div>

<?php $this->endWidget(); ?>
