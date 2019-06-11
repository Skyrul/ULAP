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
			<?php echo $form->dropDownList($model,'status',Company::listStatus()); ?>
			<?php echo $form->error($model,'status'); ?>
		</div>
		
		<div class="row">
			<?php echo $form->labelEx($model,'campaign_name'); ?>
			<?php echo $form->textField($model,'campaign_name',array('size'=>60,'maxlength'=>128)); ?>
			<?php echo $form->error($model,'campaign_name'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'description'); ?>
			<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>250)); ?>
			<?php echo $form->error($model,'description'); ?>
		</div>

		<div class="row buttons">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=> 'btn btn-success')); ?>
		</div>
			
	
	</div>
	
	<?php if(!$model->isNewRecord){ ?>
	<div id="skillsContainer">
		<h2>Assign Skills</h2>
		<div class="col-md-6">
			<div class="row">
				<?php /*foreach(Skill::model()->byIsDeletedNot()->findAll() as $skill){ ?>
					<div class="col-md-12">
						<label>
						<?php echo CHtml::checkBox('CampaignSkill['.$skill->id.'][is_active]',!empty($campaignSkillsArray[$skill->id]['is_active']) ? true : false ,array('uncheckValue'=> 0)); ?>
						<?php echo $skill->skill_name; ?>
						</label>
					</div>
				<?php } */ ?>
				
				<?php 
					// $selectedArray = array();
					// foreach(Skill::model()->byIsDeletedNot()->findAll() as $skill){
						// if(!empty($campaignSkillsArray[$skill->id]['is_active']))
							// $selectedArray[] = $skill->id;
					// }
				?>
				
				<?php echo CHtml::dropDownList('CampaignSkill[]', $campaignSkillsArray,CHtml::listData(Skill::model()->byIsDeletedNot()->findAll(),'id','skill_name'),array('class' => 'select2','multiple'=>'multiple') ); ?>
			</div>
		</div>
	</div>
	<?php } ?>
<?php  Yii::app()->clientScript->registerScript('select2js', '

	$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END); ?>
	
<?php $this->endWidget(); ?>

</div><!-- form -->