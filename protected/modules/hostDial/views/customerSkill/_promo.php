<h3>Promo</h3>

<?php
	$criteria = new CDbCriteria;
	$criteria->compare('contract_id', $customerSkill->contract_id);
	$criteria->compare('status',Promo::STATUS_ACTIVE);
	$criteria->compare('is_deleted', 0);
	$criteria->order= 'promo_name ASC';
	
	$promo = Promo::model()->findAll($criteria);
	
	if(!empty($promo)){
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	// 'id'=>'customer-skill-schedule-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

<div class="row">
	<div class="col-md-5">
		<?php //echo $form->labelEx($customerSkill,'promo_id'); ?>
		
		<div class="input-group">
			<?php 
				
				
				
				echo $form->dropDownList($customerSkill,'promo_id', CHtml::listData($promo,'id', 'promo_name'), array('empty' =>'-Select Promo-'));
			?>
		</div>
	</div>
</div>

<div class="space-6"></div>

<div class="row buttons">
	<div class="col-md-5 center">
			<button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Save</button>
	</div>
</div>

<?php echo CHtml::hiddenField('submitted_customer_skill_id', $customerSkill->id); ?>

<?php $this->endWidget(); ?>

	<?php }else{ ?>
	<p>No promo available for this contract yet.</p>
	<?php } ?>
<div class="space-12"></div>
<div class="space-12"></div>