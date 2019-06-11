<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/admin/skill_disposition_multiple_uploader.js');
	
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
	
		$(document).ready( function(){
			
			$(document).on("click", ".existing-remove-file-link", function(){
				var id = $(this).prop("id");
				var fileContainer = $(this).parent();
				
				if( confirm("Are you sure you want to remove this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/admin/skillChildDisposition/deleteEmailAttachment",
						type: "post",
						dataType: "json",
						data: { "id": id },
						success: function(response) { 
							
							if( response.status == "success" )
							{
								fileContainer.fadeOut(500, function() {
									$(this).remove();
								});
							}
							
						},
					});
				}
				
			});
			
		});
	
	', CClientScript::POS_END);
	
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
	
	$this->widget('ImperaviRedactorWidget',array(
		'selector' => '.redactorBody',
		'plugins' => array(
			'fontfamily' => array('js' => array('fontfamily.js')),
			'fontcolor' => array('js' => array('fontcolor.js')),
			'fontsize' => array('js' => array('fontsize.js')),
			'table' => array('js' => array('table.js')),
		),
		'options' => array(
			'imageUpload' => $this->createUrl('redactorUpload'),
			'dragImageUpload' => true,
			'minHeight' => 200,
			'buttons'=>array(
				'formatting', '|', 'bold', 'italic', 'deleted', 'alignment','fontcolor', 'fontsize', 'fontfamily', '|',
				'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
				'link', '|', 'image', '|', 'html', '|', 'table'
			),
		)
	));
	
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'skill'
	));
?>

<div class="page-header">
	<h1>Email Settings <small>&raquo; <?php echo $model->skill_child_disposition_name; ?></small> </small> <button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button></h1>
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
			<?php echo $form->labelEx($model,'from', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textField($model,'from',array('class'=>'col-sm-8')); ?>
				<?php echo $form->error($model,'from'); ?>
			</div>
		</div>
		
		<?php /*<div class="form-group">
			<?php echo $form->labelEx($model,'to', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textField($model,'to',array('class'=>'col-sm-8')); ?>
				<?php echo $form->error($model,'to'); ?>
			</div>
		</div>*/ ?>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'cc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textField($model,'cc',array('class'=>'col-sm-8')); ?>
				<?php echo $form->error($model,'cc'); ?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'bcc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textField($model,'bcc',array('class'=>'col-sm-8')); ?>
				<?php echo $form->error($model,'bcc'); ?>
			</div>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'subject', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textField($model,'subject',array('class'=>'col-sm-8')); ?>
				<?php echo $form->error($model,'subject'); ?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'html_header', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textArea($model,'html_header',array('class'=>'redactor')); ?>
				<?php echo $form->error($model,'html_header'); ?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'html_body', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textArea($model,'html_body',array('class'=>'redactorBody')); ?>
				<?php echo $form->error($model,'html_body'); ?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo $form->labelEx($model,'html_footer', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
			
			<div class="col-sm-10">
				<?php echo $form->textArea($model,'html_footer',array('class'=>'redactor')); ?>
				<?php echo $form->error($model,'html_footer'); ?>
			</div>
		</div>

		<div class="clearfix"></div>
		
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<div id="sources">
					<span class="filelist">
						<?php 
							if( $attachments )
							{
								foreach( $attachments as $attachment )
								{
									echo '
										<span class="label label-white label-inverse">						
											<span class="filename" title="'.$attachment->fileUpload->original_filename.'">'.CHtml::link($attachment->fileUpload->original_filename, array('/site/download', 'file'=>$attachment->fileUpload->original_filename), array('target'=>'_blank')).'</span>						
											<span class="percentage"></span>						
											<a href="javascript:void(0);" id="'.$attachment->id.'" class="existing-remove-file-link"><i class="fa fa-times red"></i></a>					
										</span>
									';
	
								}
							}
						?>
					</span>
				</div>
				
				<br />
				
				<button type="button" id="plupload-select-files" class="btn btn-info btn-minier">Initializing uploader, please wait...</button>
				
			</div>
		</div>
		
		<div class="clearfix"></div>
		
		<div class="clearfix form-actions text-center">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-sm')); ?>
		</div>

	<?php $this->endWidget(); ?>

</div><!-- form -->