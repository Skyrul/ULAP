<?php
/* @var $this CompanyController */
/* @var $model Company */
/* @var $form CActiveForm */
?>

<div class="form">


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'portal-access-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">
		<div class="col-md-12">
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
				<?php echo $form->labelEx($model,'is_portal_access'); ?>
				<?php echo $form->dropDownList($model,'is_portal_access', array('1'=> 'Yes', '0' => 'No')); ?>
				<?php echo $form->error($model,'is_portal_access'); ?>
			</div>
			
			<div class="form-group buttons">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-success')); ?>
			</div>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->