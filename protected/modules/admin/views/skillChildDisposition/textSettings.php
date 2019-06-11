<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	$cs->registerScriptFile($baseUrl . '/js/jquery.simplyCountable.js', CClientScript::POS_END);

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
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready(function() {

			$("#SkillChildDisposition_text_body").simplyCountable({
				counter: "#counter",
				countType: "characters",
				maxCount: 160,
				strictMax: true,
				countDirection: "up"
			});
		});
	
	', CClientScript::POS_END);
	
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>
		Text Settings <small>&raquo; <?php echo $model->skill_child_disposition_name; ?></small>
		
		<button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button>
	</h1>
</div>

<div class="form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'skill-disposition-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class'=>'form'
		),
	)); ?>
	
		<?php echo $form->errorSummary($model); ?>

		
		<div class="form-group">
			<?php echo $form->labelEx($model,'text_body', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textArea($model,'text_body',array('class'=>'redactorBody', 'style'=>'height:150px; width:600px;')); ?>
				
				<p><span id="counter"></span> characters /160</p>
				
				<?php echo $form->error($model,'text_body'); ?>
			</div>
		</div>
		
		<div class="clearfix"></div>
		
		<div class="clearfix form-actions text-center">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-sm')); ?>
		</div>

	<?php $this->endWidget(); ?>

</div><!-- form -->