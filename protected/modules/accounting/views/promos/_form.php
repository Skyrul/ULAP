<?php
/* @var $this CampaignController */
/* @var $model Campaign */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'campaign-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>
	
	<div class="col-md-6">
		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<?php echo $form->errorSummary($model); ?>

		<div class="row">
			<?php echo $form->labelEx($model,'status'); ?>
			<?php echo $form->dropDownList($model,'status',Promo::listStatus()); ?>
			<?php echo $form->error($model,'status'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'promo_name'); ?>
			<?php echo $form->textField($model,'promo_name',array('size'=>60,'maxlength'=>128)); ?>
			<?php echo $form->error($model,'promo_name'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'contract_id'); ?>
			<?php 
				$criteria = new CDbCriteria;
				$criteria->compare('status',Contract::STATUS_ACTIVE);
				$criteria->compare('is_deleted', 0);
				$criteria->order= 'contract_name ASC';
			?>
			<?php echo $form->dropDownList($model,'contract_id', CHtml::listData(Contract::model()->findAll($criteria),'id', 'contract_name'), array('empty' =>'-Select Contract-')); ?>
			<?php echo $form->error($model,'contract_id'); ?>
		</div>

		<div class="row buttons">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=> 'btn btn-success')); ?>
		</div>
			
	
	</div>
	
	
	
<?php $this->endWidget(); ?>

</div><!-- form -->