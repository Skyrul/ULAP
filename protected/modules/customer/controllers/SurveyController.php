<?php

ini_set('memory_limit', '4000M');
set_time_limit(0);

class SurveyController extends Controller
{
	public $pdfFilename;
	
	public function actionResult($id, $customer_id)
	{
		$survey = $this->loadModel($id);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if($customer===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->compare('customer_id', $customer_id);
		$criteria->group = 'lead_id';
		
		$surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		$this->render('result',array(
			'survey' => $survey,
			'customer_id' => $customer->id,
			'surveyAnswers' => $surveyAnswers,
			
		));
	}
	
	public function actionAnswer($id = 2, $lead_id = 1500744)
	{
		$survey = $this->loadModel($id);
		
		$lead = Lead::model()->findByPk($lead_id);
		
		if($lead===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$customer = $lead->list->customer;
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey->id);
		$criteria->compare('lead_id', $lead_id);
		
		$surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey->id);
		$criteria->order = 'question_order ASC';
		
		$surveyQuestions = SurveyQuestion::model()->findAll($criteria);
		
		
		$formOrder = $this->getFormOrders($surveyQuestions, $surveyAnswers);
		
		
		$this->render('answer',array(
			'survey' => $survey,
			'lead' => $lead,
			'customer_id' => $customer->id,
			'surveyAnswers' => $surveyAnswers,
			'surveyQuestions' => $surveyQuestions,
			'formOrder' => $formOrder,
		));
		
	}
	
	public function getFormOrders($surveyQuestions, $surveyAnswers)
	{
		$formOrder = array();
		
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(empty($surveyQuestion->is_child_of_id))
				$formOrder[$surveyQuestion->id]['question'] = $surveyQuestion->survey_question;
		}
		
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(!empty($surveyQuestion->is_child_of_id))
				$formOrder[$surveyQuestion->is_child_of_id]['child'][$surveyQuestion->id]['question']= $surveyQuestion->survey_question;
		}
		
		
		foreach($surveyAnswers as $surveyAnswer)
		{
			$surveyQuestionId = $surveyAnswer->surveyQuestion->id;
			$isChildOfId = $surveyAnswer->surveyQuestion->is_child_of_id;
			
			if(empty($isChildOfId))
			{
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder[$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder[$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder[$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
					
			}
		}
		
		foreach($surveyAnswers as $surveyAnswer)
		{
			$surveyQuestionId = $surveyAnswer->surveyQuestion->id;
			$isChildOfId = $surveyAnswer->surveyQuestion->is_child_of_id;
			
			if(!empty($isChildOfId))
			{
				
				
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder[$isChildOfId]['child'][$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder[$isChildOfId]['child'][$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder[$isChildOfId]['child'][$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
			}
		}
		
		return $formOrder;
	}
	
	public function renderFormOrdersExport($surveyModel, $formOrder)
	{
		$fp = fopen('php://temp', 'w');
		// $row = array();
		
		#headers
		$head = array("Call Date/Time", "Agent Name", "Disposition", "Customer", "List Name", "Lead Name", "Survey Name");
		$header = array();
		
		foreach($formOrder['question'] as $key => $survey)
		{
			$header[$key] = $survey['question'];
			
			if(isset($survey['child']))
			{
				foreach($survey['child'] as $key => $child)
				{
					$header[$key] = $child['question'];
				}
			}
		}
		
		
		
		## 1st row
		$column_header =  array();
		for($skipCount = 0; count($head) > $skipCount; $skipCount++)
		{
			$column_header[] = "";
		}
		
		foreach($header as $h)
		{
			$column_header[] = $h;
		}
		
		fputcsv($fp,$column_header);
		
		##2nd row
		$column_header =  array();
		for($skipCount = 0; count($head) > $skipCount; $skipCount++)
		{
			$column_header[] = "";
		}
		
		foreach($header as $key => $h)
		{
			$column_header[] = $formOrder['inputOptions'][$key];
		}
		
		fputcsv($fp,$column_header);
		
		##3rd row
		$column_header =  array();
		foreach($head as $h)
		{
			$column_header[] = $h;
		}
		
		$questionCount = 1;
		foreach($header as $h)
		{
			$column_header[] = 'Question '.$questionCount;
			$questionCount++;
		}
		fputcsv($fp,$column_header);
		
		#values 
		foreach($formOrder['answer'] as $lead_id => $lead)
		{
			// $leadModel = Lead::model()->findByPk($lead_id);
			// if($leadModel === null)
			// {
				// throw new CHttpException('404', 'Page error.');
			// }
			
			$column_value = array();
			
			$column_value[] = $formOrder['lead_info'][$lead_id]['call_date'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['agent_name'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['disposition'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['customer_name'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['list_name'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['lead_name'];
			$column_value[] = $formOrder['lead_info'][$lead_id]['survey_name'];
			
			// $column_value[] = ''; // call date
			// $column_value[] = ''; // agent_name
			// $column_value[] = ''; // disposition
			// $column_value[] = $leadModel->list->customer->getFullName(); 
			// $column_value[] = $leadModel->list->name; 
			// $column_value[] = $leadModel->getFullName(); 
			// $column_value[] = $surveyModel->survey_name; 
			
			$row = array();
			foreach($lead as $question => $survey)
			{
				$answer = $survey['answer'];
				if(is_array($survey['answer']))
					$answer = implode(', ',$survey['answer']);
						
						if(isset($survey['answer_other']))
							$value = $answer.': '.$survey['answer_other'];
						else
							$value = $answer;
				
				if(isset($header[$question]))
					$row[$question] = $value;
							
			
				if(isset($survey['child']))
				{
					foreach($survey['child'] as $question => $child)
					{
						$answer = $child['answer'];
						if(is_array($child['answer']))
							$answer = implode(', ',$child['answer']);
						
						if(isset($child['answer_other']))
							$value = $answer.': '.$child['answer_other'];
						else
							$value = $answer;
						
						if(isset($header[$question]))
							$row[$question] = $value;
					}
				}
				
				
			}
			
			// ##to make sure that the answer is going to the right question
			
			foreach($formOrder['question'] as $key => $survey)
			{
				$column_value[] = $row[$key];
				
				if(isset($survey['child']))
				{
					foreach($survey['child'] as $key => $child)
					{
						$column_value[] = $row[$key];
					}
				}
			}
		
			fputcsv($fp,$column_value);
		
		}
		
		
		rewind($fp);
		Yii::app()->user->setState('export',stream_get_contents($fp));
		fclose($fp);
		
		Yii::app()->request->sendFile('Survey Results-'.$surveyModel->survey_name.'.csv',Yii::app()->user->getState('export'));
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Company the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Survey::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Company $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='survey-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionExport($id, $customer_id, $list_id)
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
		
		$survey = $this->loadModel($id);
		$list = Lists::model()->findByPk($list_id);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if($customer===null || $list === null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$formOrder = $this->getFormOrdersExport($survey->id, $customer->id, $list->id);
		$this->renderFormOrdersExport($survey, $formOrder);
	}

	public function getFormOrdersExport($survey_id, $customer_id, $list_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('list_id', $list_id);
		
		$surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->order = 'question_order ASC';
		
		$surveyQuestions = SurveyQuestion::model()->findAll($criteria);
		
		$formOrder = array();
		
		
		
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(empty($surveyQuestion->is_child_of_id))
			{
				$formOrder['question'][$surveyQuestion->id]['question'] = $surveyQuestion->survey_question;
				$formOrder['inputOptions'][$surveyQuestion->id] = $surveyQuestion->input_options;
			}
			
			if(!empty($surveyQuestion->is_child_of_id))
			{
				$formOrder['question'][$surveyQuestion->is_child_of_id]['child'][$surveyQuestion->id]['question']= $surveyQuestion->survey_question;
				$formOrder['inputOptions'][$surveyQuestion->id] = $surveyQuestion->input_options;
			}
		}
		
		$agentData = array();
		$customerData = array();
		$listData = array();
		$surveyData = array();
		$leadData = array();
		
		foreach($surveyAnswers as $surveyAnswer)
		{
			$lead_id = $surveyAnswer->lead_id;
			$leadCallHistory = $surveyAnswer->leadCallHistory;
			
			##storing related data to minimize memory issue
			if(!isset($agentData[$leadCallHistory->agent_account_id]))
				$agentData[$leadCallHistory->agent_account_id] = $leadCallHistory->agentAccount->getFullName();
			
			$agentName = $agentData[$leadCallHistory->agent_account_id];
			
			if(!isset($customerData[$surveyAnswer->customer_id]))
				$customerData[$surveyAnswer->customer_id] = $surveyAnswer->customer->getFullName();
			
			$customerName = $customerData[$surveyAnswer->customer_id];
			
			if(!isset($listData[$surveyAnswer->list_id]))
				$listData[$surveyAnswer->list_id] = $surveyAnswer->list->name;
			
			$listName = $listData[$surveyAnswer->list_id];
			
			if(!isset($surveyData[$surveyAnswer->survey_id]))
				$surveyData[$surveyAnswer->survey_id] = $surveyAnswer->survey->survey_name;
			
			$surveyName = $surveyData[$surveyAnswer->survey_id];
			
			if(!isset($leadData[$surveyAnswer->lead_id]))
				$leadData[$surveyAnswer->lead_id] = $surveyAnswer->lead->getFullName();
			
			$leadName = $leadData[$surveyAnswer->lead_id];
			
			##lead_info
			$formOrder['lead_info'][$lead_id]['call_date'] = $leadCallHistory->start_call_time;
			$formOrder['lead_info'][$lead_id]['agent_name'] = $agentName;
			$formOrder['lead_info'][$lead_id]['disposition'] = $leadCallHistory->disposition;
			
			$formOrder['lead_info'][$lead_id]['customer_name'] = $customerName; 
			$formOrder['lead_info'][$lead_id]['list_name'] = $listName; 
			$formOrder['lead_info'][$lead_id]['lead_name'] = $leadName; 
			$formOrder['lead_info'][$lead_id]['survey_name'] = $surveyName; 
			
			$surveyQuestionId = $surveyAnswer->surveyQuestion->id;
			$isChildOfId = $surveyAnswer->surveyQuestion->is_child_of_id;
			
			if(empty($isChildOfId))
			{
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
					
			}
			
			if(!empty($isChildOfId))
			{
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
			}
		}
		
		return $formOrder;
	}
	
	public function getFormOrdersExportOld($survey_id, $customer_id, $list_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('list_id', $list_id);
		
		$surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->order = 'question_order ASC';
		
		$surveyQuestions = SurveyQuestion::model()->findAll($criteria);
		
		$formOrder = array();
		
		
		
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(empty($surveyQuestion->is_child_of_id))
			{
				$formOrder['question'][$surveyQuestion->id]['question'] = $surveyQuestion->survey_question;
				$formOrder['inputOptions'][$surveyQuestion->id] = $surveyQuestion->input_options;
			}
		}
		
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(!empty($surveyQuestion->is_child_of_id))
			{
				$formOrder['question'][$surveyQuestion->is_child_of_id]['child'][$surveyQuestion->id]['question']= $surveyQuestion->survey_question;
				$formOrder['inputOptions'][$surveyQuestion->id] = $surveyQuestion->input_options;
			}
		}
		
		
		foreach($surveyAnswers as $surveyAnswer)
		{
			$lead_id = $surveyAnswer->lead_id;
			
			
			$surveyQuestionId = $surveyAnswer->surveyQuestion->id;
			$isChildOfId = $surveyAnswer->surveyQuestion->is_child_of_id;
			
			##lead_info
			$formOrder['lead_info'][$lead_id]['call_date'] = $surveyAnswer->leadCallHistory->start_call_time;
			$formOrder['lead_info'][$lead_id]['agent_name'] = $surveyAnswer->leadCallHistory->agentAccount->getFullName();
			$formOrder['lead_info'][$lead_id]['disposition'] = $surveyAnswer->leadCallHistory->disposition;
			
			$formOrder['lead_info'][$lead_id]['customer_name'] = $surveyAnswer->lead->list->customer->getFullName(); 
			$formOrder['lead_info'][$lead_id]['list_name'] = $surveyAnswer->lead->list->name; 
			$formOrder['lead_info'][$lead_id]['lead_name'] = $surveyAnswer->lead->getFullName(); 
			$formOrder['lead_info'][$lead_id]['survey_name'] = $surveyAnswer->survey->survey_name; 
			
			if(empty($isChildOfId))
			{
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder['answer'][$lead_id][$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
					
			}
			
			
		}
		
		
		foreach($surveyAnswers as $surveyAnswer)
		{
			$lead_id = $surveyAnswer->lead_id;
			$surveyQuestionId = $surveyAnswer->surveyQuestion->id;
			$isChildOfId = $surveyAnswer->surveyQuestion->is_child_of_id;
			
			##lead_info
			$formOrder['lead_info'][$lead_id]['call_date'] = $surveyAnswer->leadCallHistory->start_call_time;
			$formOrder['lead_info'][$lead_id]['agent_name'] = $surveyAnswer->leadCallHistory->agentAccount->getFullName();
			$formOrder['lead_info'][$lead_id]['disposition'] = $surveyAnswer->leadCallHistory->disposition;
			
			$formOrder['lead_info'][$lead_id]['customer_name'] = $surveyAnswer->lead->list->customer->getFullName(); 
			$formOrder['lead_info'][$lead_id]['list_name'] = $surveyAnswer->lead->list->name; 
			$formOrder['lead_info'][$lead_id]['lead_name'] = $surveyAnswer->lead->getFullName(); 
			$formOrder['lead_info'][$lead_id]['survey_name'] = $surveyAnswer->survey->survey_name; 
			
			if(!empty($isChildOfId))
			{
				if($surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_CHECKBOX
				|| $surveyAnswer->surveyQuestion->input_type == SurveyQuestion::TYPE_DROPDOWN_MULTIPLE)
				{
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer'][] = $surveyAnswer->answer;
				}
				else
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer'] = $surveyAnswer->answer;
				
				if($surveyAnswer->answer_key == 'other')
					$formOrder['answer'][$lead_id][$isChildOfId]['child'][$surveyQuestionId]['answer_other'] = $surveyAnswer->answer;
			}
		}
		
		return $formOrder;
	}

	public function actionEmailSurveyForm($id = 2, $lead_id = 1500744)
	{
		$survey = $this->loadModel($id);
		
		$lead = Lead::model()->findByPk($lead_id);
		
		if($lead===null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$customer = $lead->list->customer;
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey->id);
		$criteria->compare('lead_id', $lead_id);
		
		$surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey->id);
		$criteria->order = 'question_order ASC';
		
		$surveyQuestions = SurveyQuestion::model()->findAll($criteria);
		
		$formOrder = $this->getFormOrders($surveyQuestions, $surveyAnswers);
		
		echo '<pre>';
		print_r($formOrder);
		echo '</pre>';
		
		exit;
		
	}

	public function actionExportPDF($id, $customer_id, $list_id)
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
		
		$survey = $this->loadModel($id);
		
		$list = Lists::model()->findByPk($list_id);
		
		$customer = Customer::model()->findByPk($customer_id);
		
		
		if($customer===null || $list === null)
			throw new CHttpException(404,'The requested page does not exist.');
		
		$formOrder = $this->getFormOrdersExportPDF($survey->id, $customer->id, $list->id);
		$this->renderFormOrdersExportPDF($survey, $formOrder, $list);
	}
	
	public function getFormOrdersExportPDF($survey_id, $customer_id, $list_id)
	{
		// $criteria = new CDbCriteria;
		// $criteria->compare('survey_id', $survey_id);
		// $criteria->compare('customer_id', $customer_id);
		// $criteria->compare('list_id', $list_id);
		
		// $surveyAnswers = SurveyAnswer::model()->findAll($criteria);
		
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		$criteria->order = 'question_order ASC';
		
		$surveyQuestions = SurveyQuestion::model()->findAll($criteria);
		
		##query and grouped survey answers
		$answerData = $this->getSurveyAnswerData($survey_id, $customer_id, $list_id);
		
		$formOrder = array();
		foreach($surveyQuestions as $surveyQuestion)
		{
			if(empty($surveyQuestion->is_child_of_id))
			{
				$formOrder[$surveyQuestion->id]['question'] = $surveyQuestion;
				$formOrder[$surveyQuestion->id]['options'] = $this->formatAnswerData($surveyQuestion);
				$formOrder[$surveyQuestion->id]['answers'] = $answerData[$surveyQuestion->id];
			}
		}
		
		// foreach($surveyQuestions as $surveyQuestion)
		// {
			// if(!empty($surveyQuestion->is_child_of_id))
			// {
				// $formOrder['question'][$surveyQuestion->is_child_of_id]['child'][$surveyQuestion->id]['question']= $surveyQuestion->survey_question;
				// $formOrder['inputOptions'][$surveyQuestion->id] = $surveyQuestion->input_options;
			// }
		// }
		
		return $formOrder;
		
		
	}

	public function renderFormOrdersExportPDF($surveyModel, $formOrder, $listModel)
	{
		
		$this->pdfFilename = $surveyModel->survey_name.'-'.$listModel->name.'-'.date('mdy').'.pdf';
		$ctr = 1;
		$html .= date('M d, Y g:ia');
		$html .= '<br>';
		
		$html .= '<h1>'.$surveyModel->survey_name.'-'.$listModel->name.'</h1>';
		$html .= '<br>';
		$html .= '<br>';
		$html .= '<table style="width:900px;">';
		
		foreach($formOrder as $questionSurveyData)
		{
		
			$surveyQuestion = $questionSurveyData['question'];
			$options = $questionSurveyData['options'];
			$answers = $questionSurveyData['answers'];
			
			
			if($surveyQuestion->input_type == $surveyQuestion::TYPE_TEXT)
			{
				$html .= '<tr><td colspan="2" style="color:red;font-weight:bold;"><i><strong>'.$ctr.'. '.$surveyQuestion->survey_question.' [Free Type]</strong></i></td></tr>';
			}
			else
			{
				$html .= '<tr><td colspan="2" style="font-weight:bold;">'.$ctr.'. '.$surveyQuestion->survey_question.'</td></tr>';
				
				$html .= '<tr><td colspan="2">&nbsp;</td></tr>';
				
				$html .= '<tr>
					<td style="font-weight:bold;text-decoration:underline;">Response</td>
					<td style="font-weight:bold;text-decoration:underline;">Count</td>
					<td style="font-weight:bold;text-decoration:underline;">Percent</td>
				</tr>';
			
			}
			
			
			// foreach($answers as $keyAnswer => $answerCtr)
			if(!empty($answers))
			{
				
				$totalCtr = 0;
				if($surveyQuestion->input_type == $surveyQuestion::TYPE_TEXT)
				{
					
				}
				else
				{
					$aCtr = 0;
					
					
					foreach($answers as $answerKey => $answerCtr)
					{
						if(isset($options[$answerKey]))
						{
							unset($options[$answerKey]);
						}
						else
						{
							unset($answers[$answerKey]);
						}
					}
					
					##get totalCtr
					foreach($answers as $answerKey => $answerCtr)
					{
						$totalCtr = $totalCtr + $answerCtr;
					}
					
					foreach($answers as $answerKey => $answerCtr)
					{
						$percentCtr = number_format( ($answerCtr / $totalCtr) * 100, 2);
						
						if($aCtr == 0)
						{
							$html .= '<tr>';
								$html .= '<td style="width:600px;font-weight:bold;">'.$answerKey.'</td>';
								$html .= '<td style="width:200px;font-weight:bold;">'.$answerCtr.'</td>';
								$html .= '<td style="width:200px;font-weight:bold;">'.$percentCtr.' %</td>';
							$html .= '</tr>';
						}
						else
						{
							$html .= '<tr>';
								$html .= '<td style="width:600px;">'.$answerKey.'</td>';
								$html .= '<td style="width:200px;">'.$answerCtr.'</td>';
								$html .= '<td style="width:200px;">'.$percentCtr.' %</td>';
							$html .= '</tr>';
						}
						
						$aCtr++;
					}
					
					foreach($options as $answerKey => $answerCtr)
					{
						$percentCtr = number_format( ($answerCtr / $totalCtr) * 100, 2);
						
						$html .= '<tr>';
							$html .= '<td>'.$answerKey.'</td>';
							$html .= '<td>'.$answerCtr.'</td>';
							$html .= '<td>'.$percentCtr.'%</td>';
						$html .= '</tr>';
						
					}
					
					$html .= '<tr>';
							$html .= '<td style="font-weight:bold;">Total Response:</td>';
							$html .= '<td style="font-weight:bold;">'.$totalCtr.'</td>';
							$html .= '<td style="font-weight:bold;">&nbsp;</td>';
					$html .= '</tr>';
				}
			}
			
			$html .= '<tr><td colspan="2">&nbsp;</td></tr>';
			$html .= '<tr><td colspan="2">&nbsp;</td></tr>';
			
			$ctr++;
			
			
		}
		
		$html .= '</table>';
		$this->generatePdf($html);
	}
	
	public function formatAnswerData($surveyQuestion)
	{
		$answers = '';
		
		if($surveyQuestion->input_type == $surveyQuestion::TYPE_TEXT)
		{
			$answers = '';
		}
		
		if($surveyQuestion->input_type == $surveyQuestion::TYPE_RADIO)
		{
			$answers = $surveyQuestion->getHtmlOptions();
		}
		
		if($surveyQuestion->input_type == $surveyQuestion::TYPE_CHECKBOX)
		{
			$answers = $surveyQuestion->getHtmlOptions();
		}
		
		if($surveyQuestion->input_type == $surveyQuestion::TYPE_DROPDOWN)
		{
			
			$answers = $surveyQuestion->getHtmlOptions();
		}
		
		if($surveyQuestion->input_type == $surveyQuestion::TYPE_DROPDOWN_MULTIPLE)
		{
			$answers = $surveyQuestion->getHtmlOptions();
		}

		if($surveyQuestion->input_type == $surveyQuestion::TYPE_LIMITER)
		{
			$answers = $surveyQuestion->getLimiterList(true);
		}
		
		$data = array();
		
		if(is_array($answers) && !empty($answers))
		{
			foreach($answers as $answer)
			{
				$data[$answer] = 0;
			}
		}
		
		##list of question options
		return $data;
		
	}
	
	public function getSurveyAnswerData($survey_id, $customer_id, $list_id)
	{
		$command = Yii::app()->db->createCommand('SELECT COUNT(answer) as ctr, answer, question_id, survey_id FROM {{survey_answer}} WHERE customer_id = :customer_id AND survey_id = :survey_id AND list_id = :list_id GROUP BY answer, question_id ORDER BY count(answer) DESC, question_id ASC');
		
		$command->bindParam(":survey_id",$survey_id,PDO::PARAM_INT); 
		$command->bindParam(":customer_id",$customer_id,PDO::PARAM_INT); 
		$command->bindParam(":list_id",$list_id,PDO::PARAM_INT); 
		
		$rows = $command->queryAll();
		
		$answerData = array();
		
		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				$answerData[$row['question_id']][$row['answer']] = $row['ctr'];
			}
		}
		
		return $answerData;
	}

	private function generatePdf($html)
	{
		$mPDF1 = Yii::app()->ePdf->mpdf();

		$mpdf = Yii::app()->ePdf->mpdf('utf-8', 'Letter-L');
		$mpdf->ignore_invalid_utf8 = true;

		$mPDF1 = Yii::app()->ePdf->mpdf('', 'A4');

		$mPDF1->pagenumPrefix = 'Page ';
		$mPDF1->pagenumSuffix = '';
		$mPDF1->nbpgPrefix = ' of ';
		$mPDF1->nbpgSuffix = ' pages';

		$mPDF1->SetFooter('{PAGENO}{nbpg}');

		$stylesheet = file_get_contents( Yii::app()->basePath.'/../css/form.css');
		$stylesheet = file_get_contents( Yii::app()->basePath.'/../template_assets/css/bootstrap.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath. '/../template_assets/css/font-awesome.min.css');
		
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-fonts.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-skins.min.css');
		$stylesheet .= file_get_contents( Yii::app()->basePath . '/../template_assets/css/ace-rtl.min.css');
		
		$mPDF1->WriteHTML($stylesheet, 1);
		
		
		$mPDF1->WriteHTML($html);
		$fileName = $this->pdfFilename;
		
		#$mPDF1->Output(Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName, EYiiPdf::OUTPUT_TO_FILE);
		$mPDF1->Output($fileName, 'D');
		// $mPDF1->Output($fileName, 'I');
		
	}
	
	
	public function actionAjaxSurveyReportDeliverySettings()
	{
		$authAccount = Yii::app()->user->account;
		
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		$model = new ReportDeliverySettings;

		if( isset($_POST['ReportDeliverySettings']) )
		{
			$existingModel = ReportDeliverySettings::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND skill_id = :skill_id
					AND customer_id = :customer_id
					AND report_name = :report_name
				',
				'params' => array(
					':account_id' => $authAccount->id,
					':skill_id' => $_POST['ReportDeliverySettings']['skill_id'],
					':customer_id' => $_POST['ReportDeliverySettings']['customer_id'],
					':report_name' => $_POST['ReportDeliverySettings']['report_name'],
				),
			));
			
			if( $existingModel )
			{
				$model = $existingModel;
			}
			
			$model->attributes = $_POST['ReportDeliverySettings'];
			$model->account_id = $authAccount->id;

			if( $model->save(false) )
			{
				$result['status'] = 'success';
			}
		}
		else
		{
			$skills = Skill::model()->findAll(array(
				'condition' => 'status=1',
				'order' => 'skill_name ASC',
			));
			
			$existingModel = ReportDeliverySettings::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND skill_id = :skill_id
					AND customer_id = :customer_id
					AND report_name = :report_name
				',
				'params' => array(
					':account_id' => $authAccount->id,
					':skill_id' => $_POST['skill_id'],
					':customer_id' => $_POST['customer_id'],
					':report_name' => $_POST['report_name'],
				),
			));
			
			if( $existingModel )
			{
				$model = $existingModel;
			}
			
			$model->customer_id = $_POST['customer_id'];
			$model->skill_id = $_POST['skill_id'];
			$model->report_name = $_POST['report_name'];
			
			$deliveries = ReportDeliverySettings::model()->findAll(array(
				'condition' => '
					report_name = :report_name
					AND customer_id = :customer_id
				',
				'params' => array(
					':customer_id' => $model->customer_id,
					':report_name' => $model->report_name,
				),
			));

			$html = $this->renderPartial('ajaxReportDeliverySettings', array(
				'skills' => $skills,
				'model' => $model,
				'deliveries' => $deliveries,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}

		echo json_encode($result);
	}

	public function actionAjaxSurveyReportDeliveryDeleteSettings()
	{
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && $_POST['id'] )
		{
			$model = ReportDeliverySettings::model()->findByPk($_POST['id']);
			
			if( $model->delete() )
			{
				$result['status'] = 'success';
			}
		}

		echo json_encode($result);
	}	
}
