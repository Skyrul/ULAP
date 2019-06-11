

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
		
			<?php echo $form->errorSummary($customerSkillEmailTemplate); ?>

			<?php echo $form->hiddenField($customerSkillEmailTemplate,'customer_skill_id'); ?>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'template_name', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'template_name',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'template_name'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'is_sending_option_default', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->dropDownList($customerSkillEmailTemplate,'is_sending_option_default',array('1' => 'Default Server', '0'=>'Custom Server'), array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'is_sending_option_default'); ?>
				</div>
			</div>
			
			<div id="default-server-container" style="display:none;">
				<div class="form-group">
					<?php echo $form->labelEx($customerSkillEmailTemplate,'smtp_host', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($customerSkillEmailTemplate,'smtp_host',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($customerSkillEmailTemplate,'smtp_host'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($customerSkillEmailTemplate,'smtp_username', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($customerSkillEmailTemplate,'smtp_username',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($customerSkillEmailTemplate,'smtp_username'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($customerSkillEmailTemplate,'smtp_password', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
					
					<div class="col-sm-10">
						<?php echo $form->textField($customerSkillEmailTemplate,'smtp_password',array('class'=>'col-sm-8')); ?>
						<?php echo $form->error($customerSkillEmailTemplate,'smtp_password'); ?>
					</div>
				</div>
			
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'from', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'from',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'from'); ?>
				</div>
			</div>
			
			<?php /*<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'to', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'to',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'to'); ?>
				</div>
			</div>*/ ?>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'cc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'cc',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'cc'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'bcc', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'bcc',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'bcc'); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'subject', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textField($customerSkillEmailTemplate,'subject',array('class'=>'col-sm-8')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'subject'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'html_header', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($customerSkillEmailTemplate,'html_header',array('class'=>'redactor')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'html_header'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'html_body', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($customerSkillEmailTemplate,'html_body',array('class'=>'redactorBody')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'html_body'); ?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($customerSkillEmailTemplate,'html_footer', array('class'=>'col-sm-2 control-label no-padding-right')); ?>
				
				<div class="col-sm-10">
					<?php echo $form->textArea($customerSkillEmailTemplate,'html_footer',array('class'=>'redactor')); ?>
					<?php echo $form->error($customerSkillEmailTemplate,'html_footer'); ?>
				</div>
			</div>

			<div class="clearfix"></div>
			
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<div id="sources" class="fileupload-source">
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
				<?php echo CHtml::submitButton($customerSkillEmailTemplate->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-sm')); ?>
				<?php echo CHtml::link('Cancel',array('update','id'=>$model->id,'tab'=>'emailSetting'),array('class'=>'btn btn-danger btn-sm')); ?>
			</div>

		<?php $this->endWidget(); ?>

	</div><!-- form -->
</div>

<?php 
	// Yii::app()->registerScript->clientScript(uniqid(), '
		
		
		// uploader = new plupload.Uploader({
			// runtimes : "html5, html4",
			// browse_button : "plupload-select-files+'.$customerSkillEmailTemplate->customer_skill_id.'",
			// container: "sources",
			// max_file_size : maxFileSizeAllowed,
			// multi_selection: true,
			// chunk_size: "1mb",
			// unique_names: true,
			// file_data_name : "FileUpload[filename]",
			// url : yii.urls.absoluteUrl + "/admin/skillDisposition/upload",
			// flash_swf_url : yii.urls.baseUrl + "/js/plupload/plupload.flash.swf",
			// filters : [
				// {
					// title : "Select Files", 
					// extensions : "jpg,jpeg,gif,png,doc,docx,pdf,xls,xlsx,txt,zip"
				// }
			// ]
		// });
		
	// ',CClientScript::POS_END);
?>	