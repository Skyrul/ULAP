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
						url: yii.urls.absoluteUrl + "/admin/skill/deleteEmailAttachment",
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
			
			
			$(document).on("change", "#SkillEmailTemplate_is_sending_option_default", function(){
				$("#default-server-container").hide();
				thisVal = $(this).val();
				
				if(thisVal == "0")
				{
					$("#default-server-container").show();
				}
			});
			
			$("#SkillEmailTemplate_is_sending_option_default").trigger("change");
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
?>

<div class="col-sm-12">
	<div class="form">

		<?php $form=$this->beginWidget('CActiveForm', array(
			'id'=>'skill-email-template-form',
			// Please note: When you enable ajax validation, make sure the corresponding
			// controller action is handling ajax validation correctly.
			// There is a call to performAjaxValidation() commented in generated controller code.
			// See class documentation of CActiveForm for details on this.
			'enableAjaxValidation'=>false,
			'htmlOptions' => array(
				'class'=>'form'
			),
		)); ?>
		
			<?php echo $form->errorSummary($skillEmailTemplate); ?>

			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'template_name', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'template_name',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'template_name'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'is_sending_option_default', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->dropDownList($skillEmailTemplate,'is_sending_option_default',array('1' => 'Default Server', '0'=>'Custom Server'), array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'is_sending_option_default'); ?>
				</div>
			</div>
			
			<div id="default-server-container" style="display:none;">
				<div class="form-group">
					<?php echo $form->labelEx($skillEmailTemplate,'smtp_host', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($skillEmailTemplate,'smtp_host',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($skillEmailTemplate,'smtp_host'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($skillEmailTemplate,'smtp_username', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($skillEmailTemplate,'smtp_username',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($skillEmailTemplate,'smtp_username'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($skillEmailTemplate,'smtp_password', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($skillEmailTemplate,'smtp_password',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($skillEmailTemplate,'smtp_password'); ?>
					</div>
				</div>
			
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'from', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'from',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'from'); ?>
				</div>
			</div>
			
			<?php /*<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'to', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'to',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'to'); ?>
				</div>
			</div>*/ ?>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'cc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'cc',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'cc'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'bcc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'bcc',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'bcc'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'subject', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($skillEmailTemplate,'subject',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($skillEmailTemplate,'subject'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'html_header', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($skillEmailTemplate,'html_header',array('class'=>'redactor')); ?>
					<?php echo $form->error($skillEmailTemplate,'html_header'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'html_body', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($skillEmailTemplate,'html_body',array('class'=>'redactorBody')); ?>
					<?php echo $form->error($skillEmailTemplate,'html_body'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($skillEmailTemplate,'html_footer', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($skillEmailTemplate,'html_footer',array('class'=>'redactor')); ?>
					<?php echo $form->error($skillEmailTemplate,'html_footer'); ?>
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
				<?php echo CHtml::submitButton($skillEmailTemplate->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-sm')); ?>
				<?php echo CHtml::link('Cancel',array('update','id'=>$model->id,'tab'=>'emailSetting'),array('class'=>'btn btn-danger btn-sm')); ?>
			</div>

		<?php $this->endWidget(); ?>

	</div><!-- form -->
</div>