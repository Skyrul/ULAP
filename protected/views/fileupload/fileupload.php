<?php
/* @var $this FileuploadController */
/* @var $model Fileupload */

$this->breadcrumbs=array(
	'Fileuploads'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Fileupload', 'url'=>array('index')),
	array('label'=>'Manage Fileupload', 'url'=>array('admin')),
);
?>

<h1>Image Upload</h1>

<?php
/* @var $this FileuploadController */
/* @var $model Fileupload */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'fileupload-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => array(
        'enctype' => 'multipart/form-data',
    ),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
        <?php echo $form->labelEx($model,'original_filename'); ?>
        <?php echo CHtml::activeFileField($model, 'original_filename'); ?>
        <?php echo $form->error($model,'original_filename'); ?>
	</div>
	<?php if(!$model->isNewRecord){ ?>
	
	<div class="row">
		 <?php echo CHtml::image(Yii::app()->request->baseUrl.'/fileupload/'.$model->generated_filename, "image",array("width"=>200)); ?>
	
	</div>
	<?php } ?>
	
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->