
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
				//echo CHtml::textField('name_'.$model->id,"");
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
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getHtmlOptions(), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;','empty'=>'-Select-'));
			}
			
			if($model->input_type == $model::TYPE_DROPDOWN_MULTIPLE)
			{
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getHtmlOptions(), array('multiple'=> true, 'template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;'));
			}
			
			if($model->input_type == $model::TYPE_LIMITER)
			{
				echo CHtml::dropDownList('name_'.$model->id, null, $model->getLimiterList(true, $list_id), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;','empty'=>'-Select-'));
			}
			
		?>
	</div>
<?php echo '</div>'; ?>

<?php Yii::app()->clientScript->registerScript('otherScript_'.$model->id,'
	var modelId = '.$model->id.';
	$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'radio\']").on("change", function () {
		
		if (this.checked && this.value === "other") {
			$(this).next("label").after("<input id=\'other_"+$(this).prop("name")+"\' name=\'other_"+$(this).prop("name")+"\' placeholder=\'Please specify\' type=\'text\'/>")
		} else {
			$("#other_"+$(this).prop("name")).remove();
		}
	});
	
	$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'checkbox\']").on("change", function () {
		
		if (this.value === "other") {
			if(this.checked){
				$(this).next("label").after("<input id=\'other_"+$(this).prop("name")+"\' name=\'other_"+$(this).prop("name")+"\' placeholder=\'Please specify\' type=\'text\'/>")
			} else {
				$("#other_"+$(this).prop("name")).remove();
			}
		}
	});
',CClientScript::POS_END); ?>	


<?php Yii::app()->clientScript->registerScript('parentChildAnswerScript','

	var htmlContent = "";
	
	$("#viewSurveyForm input[name^=\'name_\']").on("change", function () {
		var thisObject = $(this);
		var ssq_id = $(this).parent().parent().parent().attr("ssq-id");
		
		answerAjax(ssq_id, this.value, thisObject);
	});
	
	$("#viewSurveyForm select[name^=\'name_\']").on("change", function () {
		var thisObject = $(this);
		var ssq_id = $(this).parent().parent().attr("ssq-id");
		
		answerAjax(ssq_id, this.value, thisObject)
		
	});
	
	function answerAjax(ssq_id, objectValue, object)
	{
		$.ajax({
			type: "GET",
			url: yii.urls.baseUrl+"/index.php/agent/survey/getChildQuestion",
			data: {
					"survey_question_id" : ssq_id,
					"is_child_answer_condition" : objectValue,
					"list_id" : '.$list_id.'
				},
			success: function(data)
			{
				$(".class_ssq_parent_id-"+ssq_id).remove();
				object.parent().parent().parent().after(data);
			}
		});
	}
	
',CClientScript::POS_END); ?>


