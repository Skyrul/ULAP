<div class="row">
	<div class="col-sm-12">
		
		<?php 
		
			if(!empty($list->survey_id))
			{
				echo $this->renderPartial('viewSurveyForm',array(
					'lead' => $lead,
					'list' => $list,
					'customer' => $customer,
					'survey' => $list->survey,
					'surveyQuestions' => $list->survey->surveyQuestionsParentOnly
				)); 
			}
			else
			{
				echo 'No Survey Form assigned for the list.';
			}
		?>
	</div>
</div>