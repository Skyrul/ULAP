<?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/surveyForm.css'); ?>

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
			url: yii.urls.baseUrl+"/index.php/admin/surveyQuestion/getChildQuestion",
			dataType: "json",
			data: {
					"survey_question_id" : ssq_id,
					"is_child_answer_condition" : objectValue
				},
			success: function(data)
			{
				$(".class_ssq_parent_id-"+ssq_id).remove();
				
				if(data.checkChildHtml != false)
					object.parent().parent().parent().after(data.childHtml);
				
				if(data.checkExtraValue == true)
				{
					//console.log(data.extraValue);
					
					if(data.extraValue == "goto")
					{
						//console.log(data.question_order_from);
						//console.log(data.question_order);
						
						//undo the hidden question from the highest order
						for(var x = parseInt(data.question_order_from)+1; x < parseInt(data.highest_order); x++)
						{
							//console.log(x);
							$("div[ssq-order-id=\'"+x+"\']" ).show();
						}
						
						for(var x = parseInt(data.question_order_from)+1; x < parseInt(data.question_order); x++)
						{
							//console.log(x);
							$("div[ssq-order-id=\'"+x+"\']" ).hide();
						}
						
					}
					
					if(data.extraValue == "undo_goto")
					{
						
						for(var x = parseInt(data.question_order_from); x < parseInt(data.question_order); x++)
						{
							console.log(x);
							$("div[ssq-order-id=\'"+x+"\']" ).show();
						}
					}
					
					if(data.extraValue == "email")
					{
						alert("Email will be sent to: "+data.email_address);
					}
					
				}
				
			}
		});
	}
	
',CClientScript::POS_END); ?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'survey'
	));
?>

<?php echo CHtml::link('Back to Survey Questions', array('surveyQuestion/index','survey_id' => $survey->id), array('class'=>'btn btn-default')); ?>
<br>
<br>

<div class="page-header">
	<h1>
		<?php echo $survey->survey_name; ?> - View Survey Form
	</h1>
</div>

<div id="viewSurveyForm" class="form">
	<?php echo CHtml::beginForm(); ?>
	
		<?php if(!empty($surveyQuestions)){ ?>
			
				<?php 
					foreach($surveyQuestions as $surveyQuestion){
						$parentSsq = (empty($surveyQuestion->is_child_of_id)) ? 0 : $surveyQuestion->is_child_of_id;
						
						echo '<div style="border-top:1px solid #ddd">';
							echo '<div ssq-parent-id="'.$parentSsq.'" ssq-id="'.$surveyQuestion->id.'" ssq-order-id="'.$surveyQuestion->question_order.'" id="skill_survey_question-'.$surveyQuestion->id.'" class="form-group row">';
							
							echo CHtml::label($surveyQuestion->question_order.'. '.$surveyQuestion->getSurveyQuestionPreview(), 'name_'.$surveyQuestion->id, array('class'=>'col-sm-12')); 
						
								echo '<div class="col-sm-12">';
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_TEXT)
								{
									echo CHtml::textArea('name_'.$surveyQuestion->id,"",array('class'=>'form-control'));
								}
								
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_RADIO)
								{
									echo CHtml::radioButtonList('name_'.$surveyQuestion->id, null, $surveyQuestion->getHtmlOptions(), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;' )); 
								}
								
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_CHECKBOX)
								{
									echo CHtml::checkBoxList('name_'.$surveyQuestion->id, null, $surveyQuestion->getHtmlOptions(), array('template' => '{input} {label}', 'separator' => '&nbsp;&nbsp;&nbsp;&nbsp;'));
								}
								
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_DROPDOWN)
								{
									echo CHtml::dropDownList('name_'.$surveyQuestion->id, null, $surveyQuestion->getHtmlOptions(), array('empty'=>'-Select-'));
								}
								
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_DROPDOWN_MULTIPLE)
								{
									echo CHtml::dropDownList('name_'.$surveyQuestion->id, null, $surveyQuestion->getHtmlOptions(), array('multiple'=> true));
								}
				
								if($surveyQuestion->input_type == $surveyQuestion::TYPE_LIMITER)
								{
									echo CHtml::dropDownList('name_'.$surveyQuestion->id, null, $surveyQuestion->getLimiterList(true), array('empty'=>'-Select-'));
								}
				
								echo '</div>';
							echo '</div>';
						echo '</div>';
					} 
				?>
		<?php } ?>
		
		<?php 
			if(empty($surveyQuestions)){
				echo 'No survey questions created.';
			}
			else
			{
				echo CHtml::submitButton('Submit',array('class'=>'btn btn-primary btn-xs'));
			}
		?>
		
		
	<?php echo CHtml::endForm(); ?>
</div>

<?php
/* remove free text in other options if(!empty($surveyQuestion)){
	
		foreach($surveyQuestions as $surveyQuestion){
			Yii::app()->clientScript->registerScript('otherScript_'.$surveyQuestion->id,'
			var modelId = "'.$surveyQuestion->id.'";
			
			$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'radio\']").on("change", function () {
				
				if (this.checked && this.value === "other") {
					$(this).next("label").after("<input id=\'other_"+$(this).prop("name")+"\' name=\'other_"+$(this).prop("name")+"\' placeholder=\'Please specify\' type=\'text\'/>")
				} else {
					$("#other_"+$(this).prop("name")).remove();
				}
			});
			
			$("#viewSurveyForm input[name^=\'name_"+modelId+"\'][type=\'checkbox\']").on("change", function () {
				
				if (this.value === "other") {
					
					res = $(this).prop("name");
					var res = res.split("{}");
					var res = res[0].split("()");
					var res = res[0].split("[]");
					
					otherName = res[0];
					
					if(this.checked){
						$(this).next("label").after("<input id=\'other_"+otherName+"\' name=\'other_"+otherName+"\' placeholder=\'Please specify\' type=\'text\'/>")
					} else {
						$("#other_"+otherName).remove();
					}
				}
			});
			
		',CClientScript::POS_END);
		}
	}
	*/
?>