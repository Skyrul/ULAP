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
			// 'imageUpload' => $this->createUrl('redactorUpload'),
			// 'dragImageUpload' => true,
			'minHeight' => 200,
			'buttons'=>array(
				'formatting', 'fontcolor', 'fontsize', 'fontfamily', '|', 
				'bold', 'italic', 'deleted', 'alignment', '|',
				'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
				'link', '|', 'html', '|', 'table'
			),
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
				<?php echo $form->dropDownList($model,'status', array(1=>'Yes', 2=>'No')); ?>
				<?php echo $form->error($model,'status'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'enrollment_url'); ?>
				<?php 
					$urlOptions = array(
						'StateFarmLeads2016' => 'https://enroll.engagexapp.com/index.php/StateFarmLeads2016',
						'StateFarmGoal2016' => 'https://enroll.engagexapp.com/index.php/StateFarmGoal2016',
						'Win-Back' => 'https://enroll.engagexapp.com/index.php/Win-Back',
						'PolicyReview' => 'https://enroll.engagexapp.com/index.php/PolicyReview',
						'PolicyReviewPerName' => 'https://enroll.engagexapp.com/index.php/PolicyReviewPerName',
					);
					
					echo $form->dropDownList($model,'enrollment_url', $urlOptions, array('class'=>'form-control', 'maxlength'=>255, 'style'=>'width:auto;')); 
				?>
				<?php echo $form->error($model,'enrollment_url'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'html_content'); ?>

				<?php echo $form->textArea($model,'html_content',array('class'=>'redactor')); ?>
				<?php echo $form->error($model,'html_content'); ?>
			</div>
			
			<br style="clear:both;">
			
			<div class="form-actions center">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
			</div>

		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->