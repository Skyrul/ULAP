<?php
	$baseUrl = Yii::app()->request->baseUrl;
?>
	
<div class="form">


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'company-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<div class="row">
		<div class="col-sm-6"> 
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
				<?php echo $form->labelEx($model,'status'); ?>
				<?php echo $form->dropDownList($model,'status',Company::listStatus()); ?>
				<?php echo $form->error($model,'status'); ?>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'survey_name'); ?>
				<?php echo $form->textField($model,'survey_name',array('class'=>'form-control', 'maxlength'=>250)); ?>
				<?php echo $form->error($model,'survey_name'); ?>
			</div>
			
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textField($model,'description',array('class'=>'form-control', 'maxlength'=>255)); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>
			
			<br style="clear:both;">
			
			<div class="form-actions center">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
			</div>

		</div>
		
		<?php if(!$model->isNewRecord){ ?>
		
		<div id="skillsContainer">
			<h2>Assign Skills</h2>
			<div class="col-md-6">
				<div class="row">
					
					<?php echo CHtml::dropDownList('SurveySkill[]', $surveySkillsArray,CHtml::listData(Skill::model()->byEnableSurveyTab()->byIsDeletedNot()->findAll(),'id','skill_name'),array('class' => 'select2','multiple'=>'multiple') ); ?>
				</div>
			</div>
		</div>
		
		<br><br>
		<br><br>
		
		<div id="customersContainer">
		
			<h2>Assign Customers</h2>
			<div class="col-md-6">
				<div class="row">
					
					<?php echo CHtml::dropDownList('SurveyCustomer[]', $surveyCustomersArray,CHtml::listData( $customers,'id','fullName'),array('class' => 'select2','multiple'=>'multiple') ); ?>
				</div>
			</div>
			(Customers will be updated after saving the Assign Skills)
		</div>
		
		<?php } ?>
	
	<?php  Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

	', CClientScript::POS_END); ?>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->