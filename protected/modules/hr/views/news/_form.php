<?php 
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
?>

<?php
	foreach(Yii::app()->user->getFlashes() as $key => $message) {
		echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
	}
?>

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'news-form',
		'enableAjaxValidation'=>false,
	)); ?>

		<div class="col-sm-6">
			<p class="note">Fields with <span class="required">*</span> are required.</p>

			<?php echo $form->errorSummary($model); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'title'); ?>
				<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'title'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'body'); ?>
				<?php echo $form->textArea($model,'body',array('rows'=>6, 'cols'=>100, 'class'=>'redactor')); ?>
				<?php echo $form->error($model,'body'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'sort_order'); ?>
				<?php 	
					$newsCount = News::model()->count(array(
						'condition' => 'status != 3',
					));

					for($ctr = 1; $ctr <= ($newsCount + 1); $ctr++ )
					{
						$options[$ctr] = $ctr;
					}
					
					echo $form->dropDownList($model,'sort_order', $options); 
				?>
				<?php echo $form->error($model,'sort_order'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'type'); ?>
				<?php echo $form->dropDownList($model,'type', array(1=>'Html Message', 2=>'Image'), array('prompt'=>'- Select -')); ?>
				<?php echo $form->error($model,'type'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'status'); ?>
				<?php echo $form->dropDownList($model,'status', array(1=>'Active', 2=>'Inactive'), array('prompt'=>'- Select -')); ?>
				<?php echo $form->error($model,'status'); ?>
			</div>
		</div>
		
		<div class="clearfix"></div>
					
		<div class="form-actions text-center">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
		</div>
	
	<?php $this->endWidget(); ?>
	
</div><!-- form -->