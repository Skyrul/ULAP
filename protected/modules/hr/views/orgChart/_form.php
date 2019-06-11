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
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'account_id'); ?>
				<?php echo $form->dropDownList($model,'account_id', AccountUser::items(), array('class'=>'form-control', 'style'=>'width:auto;', 'prompt' => '- SELECT -')); ?>
				<?php echo $form->error($model,'account_id'); ?>
			</div>
			
			<div class="form-group buttons">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Add' : 'Save',array('class'=>'btn btn-success')); ?>
			</div>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->