
<?php 
$parentSsq = (empty($model->is_child_of_id)) ? 0 : $model->is_child_of_id;
?>
<?php echo '<div ssq-parent-id="'.$parentSsq.'" ssq-id="'.$model->id.'" id="survey_question-'.$model->id.'" class="form-group row class_ssq_parent_id-'.$model->is_child_of_id.'" style="background-color:#428bca;">'; ?>
	<div class="col-sm-12">
		<label for=""><?php echo $model->getSurveyQuestionPreview(); ?></label>						
	</div>
	
	<div class="col-sm-12">
		<?php
			if($model->input_type == $model::TYPE_TEXT)
			{
				echo CHtml::textArea('name_'.$model->id,"");
			}
			
			if($model->input_type == $model::TYPE_RADIO)
			{
				echo CHtml::radioButtonList('name_'.$model->id, null, $model->getHtmlOptions(), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;' )); 
			}
			
			if($model->input_type == $model::TYPE_CHECKBOX)
			{
				echo CHtml::checkBoxList('name_'.$model->id, null, $model->getHtmlOptions(), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;'));
			}
			
			if($model->input_type == $model::TYPE_DROPDOWN)
			{
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getHtmlOptions(), array('empty'=>'-Select-'));
			}
			
			if($model->input_type == $model::TYPE_DROPDOWN_MULTIPLE)
			{
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getHtmlOptions(), array('multiple'=> true));
			}
			
			if($model->input_type == $model::TYPE_LIMITER)
			{
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getLimiterList(), array('empty'=>'-Select-'));
			}
			
		?>
	</div>
<?php echo '</div>'; ?>

<?php Yii::app()->clientScript->registerScript('otherScript_'.$model->id,'
	var modelId = '.$model->id.';
	$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'radio\']").on("change", function () {
		
		if (this.checked && this.value === "other") {
			$(this).next("label").after("<input id=\'other_"+$(this).prop("name")+"\' placeholder=\'Please specify\' type=\'text\'/>")
		} else {
			$("#other_"+$(this).prop("name")).remove();
		}
	});
	
	$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'checkbox\']").on("change", function () {
		
		if (this.value === "other") {
			if(this.checked){
				$(this).next("label").after("<input id=\'other_"+$(this).prop("name")+"\' placeholder=\'Please specify\' type=\'text\'/>")
			} else {
				$("#other_"+$(this).prop("name")).remove();
			}
		}
	});
',CClientScript::POS_END); ?>	
