<?php
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
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
	
	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/companyProfileFileupload.js');
	$cs->registerScriptFile($baseUrl . '/js/companyFlyerProfileFileupload.js');
	$cs->registerScript(uniqid(), "
	
		var event_id = '".$model->id."';
	",CClientScript::POS_END);
	
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
			'minHeight' => 200,
			'buttons'=>array(
				'formatting', '|', 'bold', 'italic', 'deleted', 'alignment','fontcolor', 'fontsize', 'fontfamily', '|',
				'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
				'link', '|', 'image', '|', 'html', '|', 'table'
			),
			'deniedTags' => array()
		)
	));
?>

<div class="form">


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'company-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">
		<div class="col-sm-12"> 
			<p class="note">Fields with <span class="required">*</span> are required.</p>

			<?php echo $form->errorSummary($model); ?>
			
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
			<div class="form-group">
				<?php echo $form->labelEx($model,'status'); ?>
				<?php echo $form->dropDownList($model,'status',Company::listStatus()); ?>
				<?php echo $form->error($model,'status'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'display_tab_on_customer'); ?>
				<?php echo $form->dropDownList($model,'display_tab_on_customer', array(1=>'Yes', 0=>'No')); ?>
				<?php echo $form->error($model,'display_tab_on_customer'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'is_host_dialer'); ?>
				<?php echo $form->dropDownList($model,'is_host_dialer', array(1=>'Yes', 0=>'No')); ?>
				<?php echo $form->error($model,'is_host_dialer'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'customer_specific_skill_scripts'); ?>
				<?php echo $form->dropDownList($model,'customer_specific_skill_scripts', array(1=>'Yes', 0=>'No')); ?>
				<?php echo $form->error($model,'customer_specific_skill_scripts'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'enable_manual_entry'); ?>
				<?php echo $form->dropDownList($model,'enable_manual_entry', array(1=>'Yes', 0=>'No')); ?>
				<?php echo $form->error($model,'enable_manual_entry'); ?>
			</div>
				
			<?php if(!$model->isNewRecord){ ?>
				<?php if(isset($model->account) && !empty($model->account->username) ){ ?>
						<div class="form-group">
							<?php echo CHtml::label($model->account->getAttributeLabel('username'),''); ?>
							<?php echo $form->textField($model->account,'username',array('maxlength'=>128,'disabled'=>true)); ?>
						</div>
				<?php }else{ ?>
						<?php if(Yii::app()->user->account->getIsAdmin()){ ?>
						<div class="form-group">
							<label>Account Setup </label>
							<?php echo CHtml::link('<i class="fa fa-envelope"></i> Resend Company Contact Email',array('company/regenerateToken','id'=>$model->id),array('class' => 'btn btn-inverse btn-xs')); ?>
						</div>
						<?php } ?>
				<?php } ?>
			<?php } ?>
						
			<div class="form-group">
				<?php echo $form->labelEx($model,'company_name'); ?>
				<?php echo $form->textField($model,'company_name',array('class'=>'form-control', 'maxlength'=>250)); ?>
				<?php echo $form->error($model,'company_name'); ?>
			</div>

			<div class="form-group">
				<label>Company Logo</label>
				<div id="photo-container">
					<?php 
						if($model->getImage())
						{
							echo CHtml::image($model->getImage(), '', array('style'=>'/* width:100%; */'));
						}
						else
						{
							echo 'No image uploaded yet.'; 
						}
					?>
				</div>
			</div>
									
			<div class="form-group">
			<?php if(!$model->isNewRecord){ ?>
				<div id="sources" class="padded" style="margin: 10px 0;">
					<div style="margin-top: 7px;" id="uploadUserGuide">		
						<div>
							<a id="plupload-select-files" class="btn btn-mini btn-info bigger" href="#"> 
								<i class="fa fa-upload"></i>
								Initializing uploader, please wait...
							</a>
						</div>
						
						<br style="clear:both;">
					</div>

					<div class="filelist"> </div>
				</div>
			<?php } ?>
			</div>
		
			<?php /*
			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textField($model,'description',array('class'=>'form-control', 'maxlength'=>255)); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>
			*/ ?>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'contact'); ?>
				<?php echo $form->textField($model,'contact',array('class'=>'form-control', 'maxlength'=>128)); ?>
				<?php echo $form->error($model,'contact'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'phone'); ?>
				<?php echo $form->textField($model,'phone',array('class'=>'form-control', 'maxlength'=>128)); ?>
				<?php echo $form->error($model,'phone'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'email_address'); ?>
				<?php echo $form->textField($model,'email_address',array('class'=>'form-control', 'maxlength'=>128)); ?>
				<?php echo $form->error($model,'email_address'); ?>
			</div>
			
			<div class="space-12"></div>
			
			<div class="page-header">
				<h1>Scrubbing</h1>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'scrub_settings'); ?>
				<?php echo $form->dropDownList($model,'scrub_settings', $model::getScrubOptions()); ?>
				<?php echo $form->error($model,'scrub_settings'); ?>
			</div>
			
			
			<div class="space-12"></div>
			
			<div class="page-header">
				<h1>Flyer</h1>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'display_flyer_image'); ?>
				<?php echo $form->dropDownList($model,'display_flyer_image', array(1=>'Image', 2=>'Html Message')); ?>
				<?php echo $form->error($model,'display_flyer_image'); ?>
			</div>
			
			<div class="form-group">
				<label>Image</label>
				<div id="photo-container-flyer" style="max-width:200px;">
					<?php 
						if($model->getFlyerImage())
						{
							echo CHtml::image($model->getFlyerImage(), '', array('style'=>'width:100%;'));
						}
						else
						{
							echo 'No image uploaded yet.'; 
						}
					?>
				</div>
			</div>
			
			<div class="form-group">
				<?php if(!$model->isNewRecord){ ?>
					<div id="sources-flyer" class="padded" style="margin: 10px 0;">
						<div style="margin-top: 7px;" id="uploadUserGuide">		
							<div>
								<a id="plupload-select-files-flyer" class="btn btn-mini btn-info bigger" href="#"> 
									<i class="fa fa-upload"></i>
									Initializing uploader, please wait...
								</a>
							</div>
							
							<br style="clear:both;">
						</div>

						<div class="filelist-flyer"> </div>
					</div>
				<?php } ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'flyer_message'); ?>

				<?php echo $form->textArea($model,'flyer_message',array('class'=>'redactor')); ?>
				<?php echo $form->error($model,'flyer_message'); ?>
			</div>
			
			<div class="space-12"></div>
			
			<div class="page-header">
				<h1>Popup</h1>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'popup_show'); ?>
				<?php echo $form->dropDownList($model,'popup_show', array(1=>'Yes', 0=>'No')); ?>
				<?php echo $form->error($model,'popup_show'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'popup_logins'); ?>
				<?php echo $form->textField($model,'popup_logins',array('class'=>'form-control', 'style'=>'width:50px; text-align:center;', 'maxlength'=>3)); ?>
				<?php echo $form->error($model,'popup_logins'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'popup_html_content'); ?>

				<?php echo $form->textArea($model,'popup_html_content',array('class'=>'redactor')); ?>
				<?php echo $form->error($model,'popup_html_content'); ?>
			</div>
			
			<div class="form-group">
				<?php echo CHtml::link('<i class="fa fa-refresh"></i> Reset Popup Login Views', array('resetPopupLogins', 'id'=>$model->id), array('class'=>'btn btn-info btn-minier', 'confirm'=>'Are you sure you want to reset the popup login views?')); ?>
			</div>
			
			<br style="clear:both;">
			
			<div class="form-actions center">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
			</div>

		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->