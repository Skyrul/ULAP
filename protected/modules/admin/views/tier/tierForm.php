<?php
/* @var $this TierController */
/* @var $model Tier */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'tier-tierForm-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// See class documentation of CActiveForm for details on this,
	// you need to use the performAjaxValidation()-method described there.
	'enableAjaxValidation'=>true,
	'enableClientValidation' => false,
	'htmlOptions'=>array(
	   'onsubmit'=>"return false;",/* Disable normal form submit */
	),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
		'validateOnChange' => false,
		'beforeValidate' => 'js:function(form){
			return true;
		}',
        'validateOnSubmit'=>true, // Required to perform AJAX validation on form submit
        'afterValidate'=>'js:function(form, data, hasError){
			if(!hasError)
			{
				
				jQuery.ajax({
					url: "'.$actionController.'",
					type: "POST",
					data: jQuery(form).serialize(),
					dataType: "json",
					beforeSend: function(){
					},
					success: function(response){
						
						alert(response.message);
						if(response.success == true || response.success == "true"){
							jQuery(form).closest(".modal").modal("hide");
							
							r = response;
							
							if(response.scenario == "add")
							{
								if(r.html != "")
								{
									if(r.tier_ParentTier_Id == "")
									{
										if($("#tier-tree-'.$model->company_id.' .tree-branch").length > 0)
										{
											$("#tier-tree-'.$model->company_id.' ").append(r.html);
										}
										else
										{
											$("#tier-tree-'.$model->company_id.' ").html(r.html);
										}
									}
									else
									{
										if($("li#" + r.tier_ParentTier_Id + ".tree-branch  > .tree-branch-header > .tree-plus").length > 0)
										{
											$("li#" + r.tier_ParentTier_Id + ".tree-branch  > .tree-branch-header > .tree-plus").click();
										}
										else
										{
											console.log($("li#" + r.tier_ParentTier_Id + ".tree-branch > .tree-branch-children").append(r.html));
										}
									}
								}
							}
							
							if(response.scenario == "edit")
							{
								var tier_name_element = $("body").find("li#"+r.tier_Id+" .tree-label");
								tier_name_element.text(r.tier_Name);
							}
						}
					},
				});
			}
			// Always return false so that Yii will never do a traditional form submit
			return false;
		}', // Your JS function to submit form
    ),
	'action' => $actionController,
)); ?>

<div class="row">
	<div class="col-md-5">
		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<?php //echo $form->errorSummary($model); ?>
		<?php /*
		<div class="row">
			<?php echo $form->labelEx($model, 'company_id'); ?>
			<?php echo $form->dropDownList($model, 'company_id', CHtml::listData(Company::model()->findAll(),'id','company_name'), array('empty'=>'-Select Company-')); ?>
			<?php echo $form->error($model,'company_id'); ?>
		</div>
		*/
		?>
		<div class="row">
			<?php echo $form->hiddenField($model, 'parent_tier_id'); ?>
			<?php echo $form->hiddenField($model, 'parent_sub_tier_id'); ?>
			<?php echo $form->hiddenField($model, 'company_id'); ?>
			<?php echo $form->hiddenField($model, 'tier_level'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'tier_name'); ?>
			<?php echo $form->textField($model,'tier_name'); ?>
			<?php echo $form->error($model,'tier_name'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'description'); ?>
			<?php echo $form->textField($model,'description'); ?>
			<?php echo $form->error($model,'description'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'contact'); ?>
			<?php echo $form->textField($model,'contact'); ?>
			<?php echo $form->error($model,'contact'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'position'); ?>
			<?php echo $form->textField($model,'position'); ?>
			<?php echo $form->error($model,'position'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'email_address'); ?>
			<?php echo $form->textField($model,'email_address'); ?>
			<?php echo $form->error($model,'email_address'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'address'); ?>
			<?php echo $form->textField($model,'address'); ?>
			<?php echo $form->error($model,'address'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'phone_office'); ?>
			<?php echo $form->textField($model,'phone_office'); ?>
			<?php echo $form->error($model,'phone_office'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'mobile_phone'); ?>
			<?php echo $form->textField($model,'mobile_phone'); ?>
			<?php echo $form->error($model,'mobile_phone'); ?>
		</div>

		<div class="row buttons">
			<?php echo CHtml::submitButton('Submit',array('class'=>'btn btn-success')); ?>
		</div>
	</div>
	
	<?php if(!$model->isNewRecord){ ?>
	<div class="col-md-7">
		<div class="page-header">
			<h1>Tier Subsidy List <?php echo CHtml::button('Add Tier Subsidy',array('class'=>'btn btn-minier btn-primary btn-add-tier-subsidy','data-tier-id'=>$model->id)); ?></h1>
		</div>
		
		<div id="tier-subsidy-container">
			<?php $this->forward('/admin/companyTierSubsidy/subsidyList/company_id/'.$model->company_id.'/tier_id/'.$model->id,false); ?>
		</div>
			
	</div>
	<?php } ?>
</div>
<?php $this->endWidget(); ?>

</div><!-- form -->