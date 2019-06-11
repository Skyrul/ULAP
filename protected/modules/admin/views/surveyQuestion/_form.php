<?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/surveyForm.css'); ?>
<?php 

if(!$model->isNewRecord){
	Yii::app()->clientScript->registerScript('inputTypeandPreview','
		$("#'.CHtml::activeId($model,'input_type').'").change(function () {
			
			
			if($(this).val() == "")
			{
				$("#question-type-preview").hide();
			}
			else
			{
				$("#question-type-preview").show();
				inputTypePreview();
			}
		});
		
		function inputTypePreview() {
			$.ajax({
				type: "GET",
				url: yii.urls.baseUrl+"/index.php/admin/surveyQuestion/getQuestionPreview",
				data: {
					"survey_question_id" : '.$model->id.'
				},
				success: function(data)
				{
					$("#question-type-preview").html(data);
					$("#question-type-preview").show();
				}
			});
		}
		
		$("#'.CHtml::activeId($model,'input_type').'").trigger("change");
	',CClientScript::POS_END);

}

Yii::app()->clientScript->registerScript('childOfQuestionJs','
		$("#'.CHtml::activeId($model,'is_child_of_id').'").change(function () {
			
			if($(this).val() == "")
			{
				$("#'.CHtml::activeId($model, 'is_child_answer_condition').'").prop("readOnly", true).val("");
			}
			else
			{
				$("#'.CHtml::activeId($model, 'is_child_answer_condition').'").prop("readOnly", false);
			}
		});
		
		$("#'.CHtml::activeId($model,'is_child_of_id').'").trigger("change");
',CClientScript::POS_END);
	
?>

<div class="form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'survey-question-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class'=>'form'
		),
	)); ?>

		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<br />
		
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
			
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group row">
					<?php echo $form->labelEx($model,'survey_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $skill->survey_name; ?>
						<?php echo $form->hiddenField($model,'survey_id'); ?>
						<?php echo $form->error($model,'survey_id'); ?>
					</div>
				</div>
					
				<div class="form-group row">
					<?php echo $form->labelEx($model,'question_order', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'question_order', SurveyQuestion::orderList(), array('class'=>'form-control') ); ?>
						<?php echo $form->error($model,'question_order'); ?>
					</div>
				</div>
				
				<div class="form-group row">

					<?php echo $form->labelEx($model,'survey_question', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->textField($model,'survey_question',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'survey_question'); ?>
					</div>
				</div>
		
		
				<div class="form-group row">
					<?php echo $form->labelEx($model,'input_type', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'input_type', SurveyQuestion::listQuestionTypes() , array('class'=>'form-control', 'empty' => '-Select Type-') ); ?>
						<?php echo $form->error($model,'input_type'); ?>
					</div>
				</div>
				
				
				<div class="form-group row">
					
					<?php echo $form->labelEx($model,'input_options', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->textArea($model,'input_options',array('class'=>'form-control','style'=>'min-height:150px;')); ?>
						<small class="col-sm-12">
							(Comma Separated values: e.g. "option1, option2, other" )<br>
							{}Brace (Curly Brace, Curly Bracket) = Go To Question<br>
							[] Bracket = Email Address<br>
							() Parentheses = Limiter
						</small>
						<?php echo $form->error($model,'input_options'); ?>
					</div>
				</div>
				<hr>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'is_child_of_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'is_child_of_id', $ssqList, array('class'=>'form-control', 'empty' => '-None-') ); ?>
						<?php echo $form->error($model,'is_child_of_id'); ?>
					</div>
				</div>
				
				<div class="form-group row">
					<?php echo $form->labelEx($model,'is_child_answer_condition', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->textField($model,'is_child_answer_condition',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'is_child_answer_condition'); ?>
					</div>
				</div>
				
			</div>
			
			<div class="col-sm-6">
				
				<strong>Question/Answer Preview</strong>
				<br><br>
				
				<?php if(!$model->isNewRecord){ ?>
					<div id="viewSurveyForm">
						<div id="question-type-preview" style="display:none;">
							
							
						</div>
					</div>
				<?php }else{ echo 'No preview yet.';} ?>
			</div>
		
		</div>
		
		
		<div class="clearfix"></div>
		
		<div class="row">
			<div class="col-sm-12">
				<div id="submit-btn-container" class="clearfix form-actions text-center js-container-toggle">
					<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
				</div>
			</div>
		</div>
			

	<?php $this->endWidget(); ?>

</div><!-- form -->