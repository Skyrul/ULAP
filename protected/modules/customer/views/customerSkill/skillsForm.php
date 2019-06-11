
<?php 
	Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END);
 ?>
 
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'customer-skill-form',
	'enableAjaxValidation'=>true,
	'enableClientValidation' => true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
		'validateOnChange' => false,
		'beforeValidate' => 'js:function(form){
			return true;
		}',
		'afterValidate'=>'js:function(form, data, hasError){
			if(!hasError)
			{ 
				jQuery.ajax({
					url: $("#customer-skill-form").prop("action"),
					type: "POST",
					data: jQuery(form).serialize(),
					dataType: "json",
					beforeSend: function(){
					},
					complete: function(response){
						 window.location.reload();
					}
				});
			}
			
			return false;
		}',
	),

	)); ?>

	<?php echo $form->errorSummary($customer); ?>


	<?php $skills = Skill::model()->byCompanyId($customer->company_id)->byExcludedIds($selectedCustomerSkill)->findAll(); ?>
	<?php if(!empty($skills)){ ?>
		
		<?php /*
		<div class="row">
			<div class="col-md-12">
				<?php echo CHtml::label('Add Contract / Skill',null); ?>
				<?php echo CHtml::dropDownList('CustomerTierSkill[skillIdArrays][]', '', CHtml::listData(Skill::model()->byCompanyId($customer->company_id)->byExcludedIds($selectedCustomerSkill)->findAll(),'id','skill_name'),array('class' => 'select2 customerSkill-skill-dropdown')); ?>
			
			</div>
		</div>
		
		
		<div class="row">
			<div class="col-md-12">
				<?php echo CHtml::label('Contract',null); ?>
				<?php echo CHtml::dropDownList('CustomerTierSkill[contract_id]', array(), array(), array('class' => 'customerSkill-contract-dropdown', 'empty'=>'-Select Contract-')); ?>
				<?php //echo $form->error($customer,'tier_id'); ?>
			</div>
		</div> 
		*/ ?>
		
		<div class="row">
			<div class="col-md-12">
			
				<div class="form-group">
					<?php echo CHtml::label('Add Skill',null); ?>
					<?php echo $form->dropDownList($customerSkill, 'skill_id', CHtml::listData(Skill::model()->byCompanyId($customer->company_id)->byExcludedIds($selectedCustomerSkill)->active()->findAll(),'id','skill_name'),array('class' => 'select2 customerSkill-skill-dropdown','empty'=>'-Select Skill-')); ?>
					<?php echo $form->error($customerSkill,'skill_id'); ?>
				</div>
			
				<div class="form-group">
					<?php echo CHtml::label('Contract',null); ?>
					<?php echo $form->dropDownList($customerSkill, 'contract_id', array(), array('class' => 'customerSkill-contract-dropdown', 'empty'=>'-Select a skill-')); ?>
					<?php echo $form->error($customerSkill,'contract_id'); ?>
				</div>
			
				<div class="form-group">
					<?php echo CHtml::submitButton($customer->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-info','name'=>'skill_submit_btn')); ?>
				</div>
			
			</div>
		</div> 
		
	<?php }else{ ?>
		<?php echo $customer->getFullName(); ?> already has all skill available currently assigned.
	<?php } ?>
	
	
	
<?php $this->endWidget(); ?>

</div><!-- form -->