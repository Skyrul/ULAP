<?php
/* @var $this CompanyController */
/* @var $model Company */
/* @var $form CActiveForm */
?>

<div class="form">


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'position-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array(
		// 'class'=> 'form-horizontal',
		'enctype' => 'multipart/form-data',
	),
)); ?>

	<div class="row">
		<div class="col-md-12">
			<p class="note">Fields with <span class="required">*</span> are required.</p>

			<?php echo $form->errorSummary($model); ?>
			
			<?php
				foreach(Yii::app()->user->getFlashes() as $key => $message) {
					echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
				}
			?>
			
			<?php /*<div class="form-group">
				<?php echo $form->labelEx($model,'parent_id'); ?>
				<?php echo $form->dropDownList($model,'parent_id', $model::items(), array('class'=>'form-control', 'style'=>'width:auto;', 'prompt' => '- SELECT -')); ?>
				<?php echo $form->error($model,'parent_id'); ?>
			</div*/?>
			
			<div class="form-group">
				<?php 
					if($model->getImage())
					{
						echo CHtml::image($model->getImage(), '', array('class'=>'img-responsive'));
					}
					else
					{
						echo '<div style="width:100px; height:74px; border:1px dashed #ccc; text-align:center;">No Image Uploaded.</div>';
					}
				?>
				
				<?php echo $form->fileField($fileupload, 'original_filename'); ?>
				<?php echo $form->error($fileupload,'original_filename'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'name'); ?>
				<?php echo $form->textField($model,'name',array('class'=>'form-control', 'maxlength'=>250)); ?>
				<?php echo $form->error($model,'name'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textArea($model,'description',array('class'=>'form-control')); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_name'); ?>
				<?php echo $form->textField($model,'employee_name',array('class'=>'form-control', 'maxlength'=>250)); ?>
				<?php echo $form->error($model,'employee_name'); ?>
			</div>
			
			<div class="form-group buttons">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-success')); ?>
			</div>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->