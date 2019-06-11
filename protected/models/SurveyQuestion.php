<?php

/**
 * This is the model class for table "{{survey_question}}".
 *
 * The followings are the available columns in table '{{survey_question}}':
 * @property integer $id
 * @property integer $survey_id
 * @property string $survey_question
 * @property string $input_type
 * @property string $input_options
 */
class SurveyQuestion extends CActiveRecord
{
	
	const TYPE_TEXT = 'text';
	const TYPE_RADIO = 'radio';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_DROPDOWN = 'dropdown';
	const TYPE_DROPDOWN_MULTIPLE = 'dropdown_multiple';
	const TYPE_RANKING = 'ranking';
	const TYPE_LIMITER = 'limiter';
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_question}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('survey_id, survey_question, input_type', 'required'),
			array('input_options', 'required', 'on' => 'inputOptions'),
			array('input_options', 'validateInputOptionFunctionality'),
			
			array('is_child_answer_condition', 'isChildOfQuestionChecker'),
			
			array('survey_id, is_child_of_id', 'numerical', 'integerOnly'=>true),
			array('survey_question', 'length', 'max'=>255),
			array('input_type', 'length', 'max'=>40),
			array('input_options, is_child_answer_condition, question_order','safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, survey_id, survey_question, input_type, input_options', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'survey' => array(self::BELONGS_TO, 'Survey', 'survey_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'survey_id' => 'Survey ID',
			'survey_question' => 'Question',
			'input_type' => 'Question Type',
			'input_options' => 'Answer Options',
			'is_child_of_id' => 'Choose Question to be Conditional',
			'is_child_answer_condition' => 'Conditional Answer',
			'question_order' => 'Order',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('survey_id',$this->survey_id);
		$criteria->compare('survey_question',$this->survey_question,true);
		$criteria->compare('input_type',$this->input_type,true);
		$criteria->compare('input_options',$this->input_options,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SkillSurveyQuestion the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function bySurveyId($survey_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('survey_id', $survey_id);
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byNotChild()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('is_child_of_id IS NULL');
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}
	
	public function byNotSelf($id)
	{
		$criteria = new CDbCriteria;
		$criteria->condition = "id != $id";
		
		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
	}

	
	public static function listQuestionTypes()
	{
		return array(
			self::TYPE_TEXT => 'Text Field',
			self::TYPE_RADIO => 'Radio Field',
			self::TYPE_CHECKBOX => 'Check Box',
			self::TYPE_DROPDOWN => 'Dropdown',
			self::TYPE_DROPDOWN_MULTIPLE => 'Dropdown Multiple',
			// self::TYPE_RANKING => 'Rank Listing'
			self::TYPE_LIMITER => 'Limiter',
		);
	}

	public function setInputTypeScenario()
	{
		$scenario = $this->getScenario();
		
		
		if(
			$this->input_type != self::TYPE_TEXT
		)
		{
			$this->setScenario('inputOptions');
		}
		else
		{
			$this->setScenario($scenario);
		}
		
		if(empty($this->input_type))
		{
			$this->setScenario($scenario);
		}
	}

	public function getHtmlOptions()
	{
		$inputOptions = explode(',', $this->input_options);
		$htmlOptions = array();
		$other = array();
		foreach($inputOptions as $option)
		{
			$option = trim($option);
			
			
			if( strtolower($option) == "other")
			{
				$htmlOptions["other"] = $option;
			}
			else
			{
				$normalAnswer = true;
				$explodedOption = explode('[', $option);
		
				if(count($explodedOption) > 1)
				{
					$htmlOptions[$explodedOption[0]] = $explodedOption[0];
					$normalAnswer = false;
				}
				
				
				$explodedOption = explode('{', $option);
		
				if(count($explodedOption) > 1)
				{
					$htmlOptions[$explodedOption[0]] = $explodedOption[0];
					$normalAnswer = false;
				}
				
				
				if($normalAnswer)
					$htmlOptions[$option] = $option;
			}
		}
		
		return $htmlOptions;
	}
	
	
	public function getHtmlOptionsExtraValueForAnswer($answer)
	{
		$inputOptions = explode(',', $this->input_options);
		
		##fix for goto hiding previous questions
		##get the highest question_order from the options to undo the hide & show of question
		$highestGoToQuestionOrder = 0;
		foreach($inputOptions as $option)
		{
			$explodedOption = explode('{', $option);
			
			if(count($explodedOption) > 1)
			{
				$question_order = $this->getBetweenChar('{','}', $option);
				
				if($highestGoToQuestionOrder < $question_order)
					$highestGoToQuestionOrder = $question_order;
			}
		}
		
		##option query logic starts here...
		foreach($inputOptions as $option)
		{
			$option = trim($option);
			
			$explodedOption = explode('[', $option);
	
			if(count($explodedOption) > 1)
			{
				if($explodedOption[0] == $answer)
				{
					$emailAddress = $this->getBetweenChar('[',']', $option);
					return array('extraValue'=>'email', 'email_address'=> $emailAddress);
				}
				
			}
			
			$explodedOption = explode('{', $option);
	
			if(count($explodedOption) > 1)
			{
				if($explodedOption[0] == $answer)
				{
					$question_order = $this->getBetweenChar('{','}', $option);
					return array('extraValue'=>'goto', 'question_order'=> $question_order, 'highest_order' => $highestGoToQuestionOrder);
				}
			}
				
		}
		
		return null;
	}

	
	public function checkEmailAddressOption($option)
	{
		$explodedOption = explode('[', $option);
		
		if(count($explodedOption) > 1)
		{
			$emailAddress = $this->getBetweenChar('[',']', $option);
			
			if(!empty($emailAddress))
			{
				if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){ 
					return false;
				}
			}
		}
		return true;
	}
	
	
	
	public function isChildOfQuestionChecker($attribute,$params)
	{
		if(!empty($this->is_child_of_id) && empty($this->is_child_answer_condition))
		{
			$this->addError($attribute, 'Conditional Answer is required.');
		}
	}
	
	public function validateInputOptionFunctionality($attribute,$params)
	{
		if($this->scenario == 'inputOptions')
		{
			$this->getHtmlOptions();
		
		
			$inputOptions = explode(',', $this->input_options);
			
			foreach($inputOptions as $option)
			{
				$option = trim($option);
				
				$valid = $this->checkEmailAddressOption($option);
				
				if(!$valid)
				{
					$this->addError($attribute, 'Answer Options\' email address not valid.');
					// return false;
				}
			}
		}
	}
	
	
	
	public static function orderList()
	{
		
		$orderList = array();
		for($x = 0; $x <= 50; $x++)
		{
			$orderList[$x] = $x;
		}
		
		return $orderList;
	}
	
	public function getLimiterList($checkLimiterOption = false, $list_id = null)
	{
		/* We have answers 10am 12pm 2pm 4pm
			each answer would have a limiter
			Say 10am (5)  12pm (10)  2pm (15)  4pm (20)
			that means for the survey on that skill once 
			10am had been picked 5 times that answer disappears and can not be chosen anymore
		*/
		
		$options = array();
		
		$inputOptions = explode(',', $this->input_options);
		
		$htmlOptions = array();
		
		foreach($inputOptions as $option)
		{
			$option = trim($option);
			
			if(!$checkLimiterOption)
			{
				$htmlOptions[$option] = $option;
			}
			else
			{
				$limitMax = $this->getBetweenChar('(',')', $option);
				
				$option = explode('(', $option);
				$option = trim($option[0]);
				
				if(empty($limitMax))
				{
					$limitMax =  0;
				}
				
				
				$limitAnswered = 0;
				if(!empty($list_id))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('question_id',$this->id); 
					$criteria->compare('answer',$option); 
					$criteria->compare('list_id',$list_id); 
					
					$limitAnswered = SurveyAnswer::model()->count($criteria);
				}
				
				$difference = $limitMax - $limitAnswered;
				
				if($difference > 0)
					$htmlOptions[$option] = $option.' ['.$difference.' remaining]';
			}
			
			if( strtolower($option) == "other")
			{
				$htmlOptions["other"] = $option;
			}
		}
		
		
		return $htmlOptions;
	}
	
	public function getBetweenChar($var1="",$var2="",$pool){
		
		$temp1 = strpos($pool,$var1)+strlen($var1);
		$result = substr($pool,$temp1,strlen($pool));
		$dd=strpos($result,$var2);
		
		if($dd == 0){
			$dd = strlen($result);
		}

		return substr($result,0,$dd);
	}

	public function getSurveyQuestionPreview()
	{
		$questionLabel = $this->survey_question;
		
		if($this->input_type == self::TYPE_DROPDOWN_MULTIPLE)
		{
			$questionLabel = $this->survey_question.' <sub>(Use [ctrl] for multiple select)</sub>';
		}
		
		return $questionLabel;
	}
}
