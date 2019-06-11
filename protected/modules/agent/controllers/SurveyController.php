<?php

class SurveyController extends Controller
{
	public $layout='//layouts/agent_dialer';
		
	public function actionSubmitAnswer()
	{
		$result = array('status' => 'error', 'message'=>'Saving error, try again');
		
		
		
		if(!empty($_POST['survey_id']) && !empty($_POST['lead_id']) && !empty($_POST['current_call_history_id']) )
		{
			$survey_id = $_POST['survey_id'];
			$lead_id = $_POST['lead_id'];
			$call_history_id = $_POST['current_call_history_id'];
			
			
			
			try
			{
				$lead = Lead::model()->findByPk($lead_id);
				if(empty($lead))
				{
					throw new CHttpException('Lead not found');
				}
				
				
				$transaction = Yii::app()->db->beginTransaction();
				
				$valid = true;
				
				foreach($_POST as $name => $value)
				{
					$questionId = 0;
					
					if (0 === strpos($name, 'name')) {
					   
						
						 $fieldName = explode('_',$name);
						
						if(isset($fieldName[1]))
							$questionId = $fieldName[1];
						
						$criteria = new CDbCriteria;
						$criteria->compare('survey_id', $survey_id);
						$criteria->compare('skill_id', $lead->list->skill_id);
						$criteria->compare('customer_id', $lead->list->customer_id);
						$criteria->compare('list_id', $lead->list->id);
						$criteria->compare('lead_id', $lead->id);
						$criteria->compare('lead_id', $lead->id);
						$criteria->compare('question_id', $questionId);
						$criteria->compare('answer_key', '');
						
						$findSA = SurveyAnswer::model()->find($criteria);
						
						if(!empty($findSA))
						{
							$del = SurveyAnswer::model()->deleteAll($criteria);
						}
						
						if(is_array($value))
						{
							foreach($value as $val)
							{
								$surveyAnswer = new SurveyAnswer;
								$surveyAnswer->survey_id = $survey_id;
								$surveyAnswer->skill_id = $lead->list->skill_id;
								// $surveyAnswer->contract_id = '';
								$surveyAnswer->customer_id = $lead->list->customer_id;
								$surveyAnswer->list_id = $lead->list->id;
								$surveyAnswer->lead_id = $lead->id;
								$surveyAnswer->call_history_id = $call_history_id;
								$surveyAnswer->question_id = $questionId;
								$surveyAnswer->answer_key = '';
								$surveyAnswer->answer = $val;
								
								if(!$surveyAnswer->save(false))
								{
									$valid = false;
									print_r($surveyAnswer->getErrors());
								}
							}
						}
						else
						{
							$surveyAnswer = new SurveyAnswer;
							$surveyAnswer->survey_id = $survey_id;
							$surveyAnswer->skill_id = $lead->list->skill_id;
							// $surveyAnswer->contract_id = '';
							$surveyAnswer->customer_id = $lead->list->customer_id;
							$surveyAnswer->list_id = $lead->list->id;
							$surveyAnswer->lead_id = $lead->id;
							$surveyAnswer->call_history_id = $call_history_id;
							$surveyAnswer->question_id = $questionId;
							$surveyAnswer->answer_key = '';
							$surveyAnswer->answer = $value;
							
							if(!$surveyAnswer->save(false))
							{
								$valid = false;
								print_r($surveyAnswer->getErrors());
							}
						} 
					}
					
					if (0 === strpos($name, 'other_name')) {
					   
						
						$fieldName = explode('other_name_',$name);
						
						if(isset($fieldName[1]))
							$questionId = $fieldName[1];
						
						
						$criteria = new CDbCriteria;
						$criteria->compare('survey_id', $survey_id);
						$criteria->compare('skill_id', $lead->list->skill_id);
						$criteria->compare('customer_id', $lead->list->customer_id);
						$criteria->compare('list_id', $lead->list->id);
						$criteria->compare('lead_id', $lead->id);
						$criteria->compare('question_id', $questionId);
						$criteria->compare('answer_key', 'other');
						
						$findSA = SurveyAnswer::model()->find($criteria);
						
						if(!empty($findSA))
						{
							$findSA->answer = $value;
							
							if(!$findSA->save(false))
							{
								$valid = false;
								print_r($findSA->getErrors());
							}
						}
						else
						{
							$surveyAnswerOther = new SurveyAnswer;
							$surveyAnswerOther->survey_id = $survey_id;
							$surveyAnswerOther->skill_id = $lead->list->skill_id;
							// $surveyAnswerOther->contract_id = '';
							$surveyAnswerOther->customer_id = $lead->list->customer_id;
							$surveyAnswerOther->list_id = $lead->list->id;
							$surveyAnswerOther->lead_id = $lead->id;
							$surveyAnswerOther->call_history_id = $call_history_id;
							$surveyAnswerOther->question_id = $questionId;
							$surveyAnswerOther->answer_key = 'other';
							$surveyAnswerOther->answer = $value;
							
							if(!$surveyAnswerOther->save(false))
							{
								$valid = false;
								print_r($surveyAnswerOther->getErrors());
							}
						}
					}
					
				}
				
				if($valid)
				{
					$transaction->commit();
					$result = array('status' => 'success', 'message'=>'Survey answers successfully saved.');
				}
				else
				{
					$transaction->rollback();
				}
				
			}
			catch(Exception $e)
			{
				$transaction->rollback();
			}
			

		}
		
		echo CJSON::encode($result);
		Yii::app()->end();
	}

	public function actionGetChildQuestion2($survey_question_id, $is_child_answer_condition, $list_id)
	{
		$model = SurveyQuestion::model()->find(array(
			'condition' => 'is_child_of_id = :ssq_id AND is_child_answer_condition = :icac',
			'params' => array(
				':ssq_id' => $survey_question_id, 
				':icac' => $is_child_answer_condition
			),
		));
			
		if($model !== null)
		{
			$this->renderPartial('_inputTypePreview',array(
				'model' => $model,
				'list_id' => $list_id,
			), false, true);
		}
		
		Yii::app()->end();
	}
	
	public function actionGetChildQuestion($survey_question_id, $is_child_answer_condition, $list_id)
	{
		//get survey question for email and go to option
		$model = SurveyQuestion::model()->findByPk($survey_question_id);
		
		$answerScenario = $model->getHtmlOptionsExtraValueForAnswer($is_child_answer_condition);
		if(!empty($answerScenario))
		{
			$answerScenario['checkExtraValue'] = true;
			
			if($answerScenario['extraValue'] == 'goto')
			{
				$answerScenario['question_order_from'] = $model->question_order;
				
				if($model->question_order > $answerScenario['question_order'])
				{
					$answerScenario['extraValue'] = 'undo_goto';
					$answerScenario['question_order_from'] = $answerScenario['question_order'];
					$answerScenario['question_order'] = $model->question_order;
				}
			}
			
			if($answerScenario['extraValue'] == 'email')
			{
				 // $answerScenario['email_address'];
			}
		}
		else
			$answerScenario['checkExtraValue'] = false;
		
		## get the child question
		$model = SurveyQuestion::model()->find(array(
			'condition' => 'is_child_of_id = :ssq_id AND is_child_answer_condition = :icac',
			'params' => array(
				':ssq_id' => $survey_question_id, 
				':icac' => $is_child_answer_condition
			),
		));
			
		if($model !== null)
		{
			$childHtml = $this->renderPartial('_inputTypePreview',array(
				'model' => $model,
				'list_id' => $list_id,
			), true);
			
			$answerScenario['childHtml'] = $childHtml;
			$answerScenario['checkChildHtml'] = true;
			
		}
		else
			$answerScenario['checkChildHtml'] = false;
		
		echo CJSON::encode($answerScenario);
		Yii::app()->end();
	}
}