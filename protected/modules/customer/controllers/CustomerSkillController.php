<?php

class CustomerSkillController extends Controller
{
	public function accessRules()
	{
		return array(
			// array('allow',  // allow all users to perform 'index' and 'view' actions
				// 'actions'=>array('index','view'),
				// 'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index','ajaxAddSkill', 'delete', 'toggleSkillChild','toggleCustomerSkillIsCustomSchedule','customScheduleUpdate','dialingSetting', 
					'startEndDate', 'addNewSchedule', 'getContractByCompanyAndSkill', 'toggleCustomerSkillLevel', 'toggleCustomerSkillSubsidyLevel', 
					'toggleCustomerSkillSubsidy', 'toggleCustomerSkillIsContractHold', 'customerContractSubsidy', 'cancel', 'promo', 'download'
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex($customer_id = null, $tab = null, $customer_skill_id = null)
	{
		
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		else
		{
			$customer = Customer::model()->findByPk($customer_id);
		}
		
		if($customer === null)
		{
			throw new CHttpException('403', 'Page not found.');
		}
		
		if( isset($_POST['CustomerSkill']) )
		{
			$model = CustomerSkill::model()->findByPk($_POST['CustomerSkill']['id']);
			
			if( $model )
			{
				$uploadedFile = CUploadedFile::getInstance($model,'fileUpload');
				
				if( $uploadedFile )
				{
					if( $uploadedFile->type == 'application/pdf' )
					{
						$originalFileName = $uploadedFile->name;

						$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
						$fileName = $rnd.'-'.$originalFileName;
						
						$targetDir = 'fileupload' . DIRECTORY_SEPARATOR . $fileName;
						
						$uploadedFile->saveAs($targetDir);
						
						
						$fileupload = new Fileupload;
						
						$fileupload->setAttributes(array(
							'original_filename' => $originalFileName,
							'generated_filename' => $fileName,
						));
						
						if( $fileupload->save(false) )
						{
							$model->script_tab_fileupload_id = $fileupload->id;
							
							if( $model->save(false) )
							{
								$status = 'success';
								$message = 'Script file was saved successfully.';
							}
							else
							{
								$status = 'error';
								$message = 'File upload error.';
							}
						}
						else
						{
							$status = 'error';
							$message = 'File upload error.';
						}
					}
					else
					{
						$status = 'error';
						$message = 'Please attach a pdf file.';
					}
					
					Yii::app()->user->setFlash($status, $message);
				}
			}
		}
		
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$customer->id);
		$criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
		$selectedCustomerSkills = CustomerSkill::model()->findAll($criteria);
		
		$this->render('index',array(
			'customer'=>$customer,
			'customer_skill_id'=>$customer_skill_id,
			'selectedCustomerSkills'=>$selectedCustomerSkills,
			'tab'=>$tab,
		));
	}

	public function actionAjaxAddSkill($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		if($customer === null)
			throw new CHttpException('403', 'Page not found.');
		
		$customerSkill = new CustomerSkill;
		$customerSkill->customer_id = $customer->id;
		
		$this->performAjaxValidation($customerSkill);
		
		if(isset($_POST['CustomerSkill']))
		{
			$customerSkill->attributes = $_POST['CustomerSkill'];
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				$criteria = new CDbCriteria;
				$criteria->compare('customer_id', $customerSkill->customer_id);
				$criteria->compare('skill_id', $customerSkill->skill_id);
				$criteria->compare('contract_id', $customerSkill->contract_id);
				
				$cs = CustomerSkill::model()->find($criteria);
					
				if($cs === null)
				{
					$cs = new CustomerSkill;
					$cs->customer_id = $customerSkill->customer_id;
					$cs->skill_id = $customerSkill->skill_id;
					$cs->contract_id = $customerSkill->contract_id;
				}
				
				//predefined customer settings for each skill - asked before updating this attributes
				$cs->is_custom_call_schedule = 0;
				$cs->skill_caller_option_customer_choice = CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM; 
				$cs->status = CustomerSkill::STATUS_ACTIVE;
				
				if(!$cs->save())
				{
					print_r($cs->getErrors());
				}
				
				$transaction->commit();
				// $this->redirect(array('customerSkill/index','customer_id' => $customer->id));
			}
			catch(Exception $e)
			{
				print_r($e);
				$transaction->rollback();
			}
			
			
			Yii::app()->end();
		}
		
		// if(isset($_POST['skill_submit_btn']))
		// {
			 // $this->redirect(array('customerSkill/index','customer_id' => $customer->id));
		// }
		
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$customer->id);
		$criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
		$selectedCustomerSkill = CHtml::listData(CustomerSkill::model()->findAll($criteria),'skill_id','skill_id');
		
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		
		$this->renderPartial('skillsForm',array(
			'customer'=>$customer,
			'selectedCustomerSkill'=>$selectedCustomerSkill,
			'customerSkill'=>$customerSkill,
		),false, true);
		
	}
	
	public function actionAjaxAddSkill2($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		if($customer === null)
			throw new CHttpException('403', 'Page not found.');
		
		
		if(isset($_POST['CustomerTierSkill']))
		{
			
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				$cts = $_POST['CustomerTierSkill'];
				
				if(isset($cts['tier_id']))
				{
					$customer->tier_id = $cts['tier_id'];
					if(!$customer->save())
					{
						
					}
				}
				
				// $criteria = new CDbCriteria;
				// $criteria->compare('customer_id',$customer->id);
				// $criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
				// $ccs = CustomerSkill::model()->findAll($criteria);
					
				if(isset($cts['skillIdArrays']))
				{
					
					// foreach($ccs as $_ccs)
					// {
						// if(!in_array($_ccs->skill_id, $cts['skillIdArrays']) )
						// {
							// $_ccs->status = CustomerSkill::STATUS_INACTIVE;
							// $_ccs->save(false);
						// }
						
					// }
					
					foreach($cts['skillIdArrays'] as $skill_id)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('skill_id', $skill_id);
						$criteria->compare('customer_id', $customer->id);
						
						$cs = CustomerSkill::model()->find($criteria);
						
						if($cs === null)
						{
							$cs = new CustomerSkill;
							$cs->customer_id = $customer->id;
							$cs->skill_id = $skill_id;
						}
						
						
						$cs->contract_id = (isset($_POST['CustomerTierSkill']['contract_id'])) ? $_POST['CustomerTierSkill']['contract_id'] : null;
						
						//predefined customer settings for each skill - asked before updating this attributes
						$cs->is_custom_call_schedule = false;
						$cs->skill_caller_option_customer_choice = 0; 
						
						$cs->status = CustomerSkill::STATUS_ACTIVE;
						$cs->save(false);
					}
				}
				else 
				{
					// foreach($ccs as $_ccs)
					// {
							// $_ccs->status = CustomerSkill::STATUS_INACTIVE;
							// $_ccs->save(false);
					// }
				}
				
				$transaction->commit();
				$this->redirect(array('customerSkill/index','customer_id' => $customer->id));
			}
			catch(Exception $e)
			{
				$transaction->rollback();
			}
		}
		
		// if(empty($_POST['CustomerTierSkill']) && isset($_POST['skill_submit_btn']))
		// {
			// $criteria = new CDbCriteria;
			// $criteria->compare('customer_id',$customer->id);
			// $criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
			// $ccs = CustomerSkill::model()->findAll($criteria);
			
			// foreach($ccs as $_ccs)
			// {
				// $_ccs->status = CustomerSkill::STATUS_INACTIVE;
				// $_ccs->save(false);
			// }
			
			
			// $this->redirect(array('customerSkill/index','customer_id' => $customer->id));
		// }
		
		if(isset($_POST['skill_submit_btn']))
		{
			 $this->redirect(array('customerSkill/index','customer_id' => $customer->id));
		}
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id',$customer->id);
		$criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
		$selectedCustomerSkill = CHtml::listData(CustomerSkill::model()->findAll($criteria),'skill_id','skill_id');
		
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		
		$this->renderPartial('skillsForm',array(
			'customer'=>$customer,
			'selectedCustomerSkill'=>$selectedCustomerSkill,
		),false, true);
		
	}
	
	public function actionDelete($customer_id, $customer_skill_id)
	{
		
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);

		if($customerSkill !== null && $customerSkill->customer_id == $customer_id)
		{
			if(date('Y',strtotime($customerSkill->end_month)) < '1995')
			{
				echo CJSON::encode(array('status'=>'error','message'=>'End Date is empty or not valid.'));
				Yii::app()->end();
			}
			
			$customerSkill->status = CustomerSkill::STATUS_INACTIVE;
			$customerSkill->delete();
			
			$criteria = new CDbCriteria;
			$criteria->compare('customer_skill_id', $customerSkill->id);
			$criteria->compare('customer_id', $customer_id);
			
			$csls = CustomerSkillLevel::model()->findAll($criteria);
			$cssls = CustomerSkillSubsidyLevel::model()->findAll($criteria);
			
			foreach($csls as $csl)
			{
				$csl->delete();
			 }
			
			 foreach($cssls as $cssl)
			 {
				 $cssl->delete();
			 }
			
			##create customer history note##
			
			$historyNote = array();
			$historyNote['contract_name'] = $customerSkill->contract->contract_name;
			$historyNote['start_month'] = $customerSkill->start_month;
			$historyNote['end_month'] = $customerSkill->end_month;
			
			
			$historyNote['is_contract_hold'] = $customerSkill->is_contract_hold;
			$historyNote['is_contract_hold_start_date'] = $customerSkill->is_contract_hold_start_date;
			$historyNote['is_contract_hold_end_date'] = $customerSkill->is_contract_hold_end_date;
			
			##creating customer skill level
			$criteria = new CDbCriteria;
			$criteria->compare('customer_id', $customerSkill->customer_id);
			$criteria->compare('customer_skill_contract_id', $customerSkill->contract_id);
			$criteria->compare('status', 1);
			
			$customerSkillLevels = CustomerSkillLevel::model()->findAll($criteria);
			
			if(!empty($customerSkillLevels))
			{
				foreach($customerSkillLevels as $customerSkillLevel)
				{
					
					$historyNote['customerSkillLevel']['quantity'] = $customerSkillLevel->quantity;
					
					
					$criteria = new CDbCriteria;
					$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
					$cslparent = ContractSubsidyLevel::model()->find($criteria);
					
					if(!empty($cslparent))
					{
						if($cslparent->type == 1)
						{
							$criteria = new CDbCriteria;
							$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
							$criteria->compare('column_name', 'amount');
							$csl = ContractSubsidyLevel::model()->find($criteria);
							
							if(!empty($csl))
							{
								$historyNote['customerSkillLevel']['amount'] = $csl->column_value;
							}
							
							$criteria = new CDbCriteria;
							$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
							$criteria->compare('column_name', 'goal');
							$csl = ContractSubsidyLevel::model()->find($criteria);
							
							if(!empty($csl))
							{
								$historyNote['customerSkillLevel']['goal'] = $csl->column_value;
							}
						}
						
						if($cslparent->type == 2)
						{
							$criteria = new CDbCriteria;
							$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
							$criteria->compare('column_name', 'amount');
							$csl = ContractSubsidyLevel::model()->find($criteria);
							
							if(!empty($csl))
							{
								$historyNote['customerSkillLevel']['amount'] = $csl->column_value;
							}
							
							$criteria = new CDbCriteria;
							$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
							$criteria->compare('column_name', 'low');
							$csl = ContractSubsidyLevel::model()->find($criteria);
							
							if(!empty($csl))
							{
								$historyNote['customerSkillLevel']['low'] = $csl->column_value;
							}
							
							$criteria = new CDbCriteria;
							$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
							$criteria->compare('column_name', 'high');
							$csl = ContractSubsidyLevel::model()->find($criteria);
							
							if(!empty($csl))
							{
								$historyNote['customerSkillLevel']['high'] = $csl->column_value;
							}
						}
						
					}
				}
			}
			
			$skill = $customerSkill->skill;
			
			if(!empty($skill->skillChilds) )
			{
				foreach($skill->skillChilds as $skillChild)
				{
					
					if($skillChild->id == 5 || $skillChild->id == 8 || $skillChild->id == 19 || $skillChild->id == 21)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customerSkill->customer_id);
						$criteria->compare('skill_id', $skillChild->skill_id);
						$criteria->compare('customer_skill_id', $customerSkill->id);
						$criteria->compare('skill_child_id', $skillChild->id);
						$customerSkillChild = CustomerSkillChild::model()->find($criteria);
						
						if($customerSkillChild !== null)
						{
							$historyNote['child_skill']['confirm'] = 'On';
						}
						else
							$historyNote['child_skill']['confirm'] = 'Off';
					}
					
					if($skillChild->id == 6 || $skillChild->id == 9 || $skillChild->id == 20 || $skillChild->id == 22)
					{
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customerSkill->customer_id);
						$criteria->compare('skill_id', $skillChild->skill_id);
						$criteria->compare('customer_skill_id', $customerSkill->id);
						$criteria->compare('skill_child_id', $skillChild->id);
						$customerSkillChild = CustomerSkillChild::model()->find($criteria);
						
						if($customerSkillChild !== null)
						{
							$historyNote['child_skill']['reschedule'] = 'On';
						}
						else
							$historyNote['child_skill']['reschedule'] = 'Off';
						
					}
				}
			}
			
			if($customerSkill->is_custom_call_schedule)
				$historyNote['custom_call_schedule'] = 'On';
			else
				$historyNote['custom_call_schedule'] = 'Off';
			
			if($customerSkill->skill_caller_option_customer_choice == CustomerSkill::CUSTOMER_CHOICE_PHONE)
			{
				$historyNote['dial_setting'] = 'Dial As Office Phone number';
			}
			
			if($customerSkill->skill_caller_option_customer_choice == CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM)
			{
				$historyNote['dial_setting'] = 'Dial As Office Area Code & Company Name';
			}

			$customerExras = CustomerExtra::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
				'params' => array(
					':customer_id' => $customerSkill->customer_id,
					':contract_id' => $customerSkill->contract->id,
					':skill_id' => $customerSkill->skill->id,
				),
			));
			
			$historyNote['extra'] = array();
			if( $customerExras ) 
			{
				foreach( $customerExras as $customerExra )
				{
					if($customerExra->year == '2017')
					{
						$historyNote['extra'][$customerExra->id]['description'] =$customerExra->description;
						$historyNote['extra'][$customerExra->id]['year'] =$customerExra->year;
						$historyNote['extra'][$customerExra->id]['month'] =$customerExra->month;
						$historyNote['extra'][$customerExra->id]['quantity'] =$customerExra->quantity;
					}
				}
			}
			
			
			$criteria = new CDbCriteria;
			$criteria->compare('skill_id', $customerSkill->skill_id);
			$criteria->compare('contract_id', $customerSkill->contract_id);
		
			$companySubsidy = CompanySubsidy::model()->find($criteria);
			if(!empty($companySubsidy))
			{
				$criteria = new CDbCriteria;
				$criteria->compare('customer_id', $customerSkill->customer_id);
				$criteria->compare('customer_skill_id', $customerSkill->id);
				$customerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find($criteria);
				
				if(!empty($customerSkillSubsidyLevel))
				{
					$csl = CompanySubsidyLevel::model()->findByPk($customerSkillSubsidyLevel->subsidy_level_id);
					
					if(!empty($csl))
					{
						$historyNote['subsidy']['name'] = $csl->name;
						$historyNote['subsidy']['type'] = $csl->type;
						$historyNote['subsidy']['value'] = $csl->value;
					}
				}
			}
				
			// echo '<pre>';
			// print_r($historyNote);
			// echo '</pre>';
			
			$content = '';
			$content .= $customerSkill->contract->contract_name.' contract deleted. Settings when deleted below:<br>';
			
			$content .= '<br><b>Contract</b><br>';
			$content .=  'Start/End Dates: ['.date('m/d/Y',strtotime($customerSkill->start_month)).' to '.date('m/d/Y',strtotime($customerSkill->end_month)).']<br>';
			
			if($cslparent->type == 1)
			{
				$content .=  $customerSkill->contract->contract_name.': [Quantity: '.$historyNote['customerSkillLevel']['quantity'].', Goal: '.$historyNote['customerSkillLevel']['goal'].', Amount: '.$historyNote['customerSkillLevel']['amount'].']<br>';
			}
			
			if($cslparent->type == 2)
			{
				$content .=  $customerSkill->contract->contract_name.': [Quantity: '.$historyNote['customerSkillLevel']['quantity'].', Low: '.$historyNote['customerSkillLevel']['low'].', High: '.$historyNote['customerSkillLevel']['high'].', Amount: '.$historyNote['customerSkillLevel']['amount'].']<br>';
			}
			
			$content .=  'Subsidies: [Name: '.$historyNote['subsidy']['name'].', Type: '.$historyNote['subsidy']['type'].', Value: '.$historyNote['subsidy']['value'].']<br>';
			$content .= 'Hold Period: ';
				if($historyNote['is_contract_hold'])
				{
					$content .= '[On, ';
					$content .= date('m/d/Y',strtotime($customerSkill->is_contract_hold_start_date)).', ';
					$content .= date('m/d/Y',strtotime($customerSkill->is_contract_hold_end_date)).']';
				}
				else
					$content .= '[Off]';
			
			$content .= '<br>';
			
			$content .= '<br><b>Child Skill</b><br>';
			$content .=  'Confirm: ['.$historyNote['child_skill']['confirm'].']<br>';
			$content .=  'Reschedule: ['.$historyNote['child_skill']['reschedule'].']<br>';
			
			$content .= '<br><b>Customer Call Schedule: </b> ['.$historyNote['custom_call_schedule'].']';
			$content .= '<br><b>Dialing Settings: </b> ['.$historyNote['dial_setting'].']';
			
			if(!empty($historyNote['extra']))
			{
				$content .= '<br><b>Extra</b><br>';
				
				foreach($historyNote['extra'] as $extra)
				{
					$content .=  $extra['description'].' [Year/Month:'.$extra['year'].'/'.$extra['month'].', Quantity:'.$extra['quantity'].']<br>';
				}
			}
			// echo $content;
							
			$history = new CustomerHistory;
				$history->setAttributes(array(
					'model_id' => $customerSkill->id, 
					'customer_id' => $customerSkill->customer_id,
					'user_account_id' => Yii::app()->user->account->id,
					'page_name' => 'Customer Skill',
					'content' => $content,
					'type' => $history::TYPE_DELETED,
				));
					
								$history->save(false);							
			echo CJSON::encode(array('status'=>'success','message'=>'Skill removed successfully!'));
			Yii::app()->end();	
			
		}
		
		$this->redirect(array('customerSkill/index','customer_id'=>$customer_id));
	}
	
	public function actionToggleSkillChild($boolType, $skill_child_id, $customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$skillChild = SkillChild::model()->findByPk($skill_child_id);
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($skillChild === null || $customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer->id);
		$criteria->compare('skill_id', $skillChild->skill_id);
		$criteria->compare('customer_skill_id', $customerSkill->id);
		$criteria->compare('skill_child_id', $skillChild->id);
		
		
		$customerSkillChild = CustomerSkillChild::model()->find($criteria);
		
		if($customerSkillChild === null)
		{
			$customerSkillChild = new CustomerSkillChild;
			$customerSkillChild->customer_id = $customer->id;
			$customerSkillChild->skill_id = $skillChild->skill_id;
			$customerSkillChild->customer_skill_id = $customerSkill->id;
			$customerSkillChild->skill_child_id = $skillChild->id;
		}
		
		$customerSkillChild->is_enabled = $boolType;
			
		if(!$customerSkillChild->save())
		{
			print_r($customerSkillChild->getErrors());
		}
		else
		{
			$content = '<b>'.$customerSkillChild->skillChild->child_name.'</b> child skill';
			
			if( $boolType == 1 )
			{
				$content .= ' was truned on';
			}
			else
			{
				$content .= ' was turned off';
			}
			
			$history = new CustomerHistory;
			
			$history->setAttributes(array(
				'model_id' => $customerSkillChild->id, 
				'customer_id' => $customer->id,
				'user_account_id' => Yii::app()->user->account->id,
				'page_name' => 'Child Skill',
				'content' => $content,
				'type' => $history::TYPE_UPDATED,
			));

			$history->save(false);						
		}
		
		Yii::app()->end();
	}
	
	public function actionToggleCustomerSkillIsCustomSchedule($boolType, $customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		$customerSkill->is_custom_call_schedule = $boolType;
			
		if(!$customerSkill->save())
		{
			print_r($customerSkill->getErrors());
		}
		
		Yii::app()->end();
	}
	
	public function actionToggleCustomerSkillIsContractHold($boolType, $customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		$customerSkill->is_contract_hold = $boolType;
			
		if(!$customerSkill->save())
		{
			print_r($customerSkill->getErrors());
		}
		
		Yii::app()->end();
	}
	
	public function actionCustomScheduleUpdate($customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		
		$model = new CustomerSkillSchedule;
		$model->customer_skill_id = $customerSkill->id;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		// echo '<pre>';
			// print_r($_REQUEST); exit;
			
		if(isset($_POST['CustomerSkillSchedule']))
		{
			
			// echo '<pre>';
			// print_r($_REQUEST); exit;
			
			$customerSkill = CustomerSkill::model()->findByPk($_POST['CustomerSkillSchedule']['customer_skill_id']);
			
			$deleteNotInSkillScheduleIds = array();
			if(isset($_POST['CustomerSkillSchedule']['schedule_day']))
			{
				foreach($_POST['CustomerSkillSchedule']['schedule_day'] as $schedule_day => $attributes)
				{
					foreach($attributes as $key => $attribute)
					{
						if (strpos($key,'new') !== false) {
							
							
							$customerSkillSchedule = new CustomerSkillSchedule;
							$customerSkillSchedule->customer_skill_id = $customerSkill->id;
							$customerSkillSchedule->schedule_day = $schedule_day;
							$customerSkillSchedule->schedule_start = $attribute['schedule_start'];
							$customerSkillSchedule->schedule_end = $attribute['schedule_end'];
							//$customerSkillSchedule->status = $attribute['status'];
							if(!$customerSkillSchedule->save(false))
							{
								print_r($customerSkillSchedule->getErrors()); exit;
							}
					
							// var_dump($customerSkillSchedule->save(false));
							// print_r($customerSkillSchedule->attributes); exit;
							$deleteNotInSkillScheduleIds[$customerSkillSchedule->id] = $customerSkillSchedule->id;
						}
						else
						{
							$deleteNotInSkillScheduleIds[$key] = $key;
							
							$customerSkillSchedule = CustomerSkillSchedule::model()->find(array(
								'condition'=> 'customer_skill_id = :customer_skill_id AND schedule_day = :schedule_day  AND id = :id_key',
								'params'=>array(
									':customer_skill_id' => $customerSkill->id,
									':schedule_day' => $schedule_day,
									':id_key' => $key,
								),
							));
							
							if($customerSkillSchedule === null)
								$customerSkillSchedule = new CustomerSkillSchedule;
							
							$customerSkillSchedule->customer_skill_id = $customerSkill->id;
							$customerSkillSchedule->schedule_day = $schedule_day;
							$customerSkillSchedule->schedule_start = $attribute['schedule_start'];
							$customerSkillSchedule->schedule_end = $attribute['schedule_end'];
							//$customerSkillSchedule->status = $attribute['status'];
							
							$customerSkillSchedule->save(false);
						}
					}
				}
			}
			
			if(isset($_POST['CustomerSkillSchedule']['customer_skill_id']))
			{
				if(!empty($deleteNotInSkillScheduleIds))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('customer_skill_id',$customerSkill->id);
					$criteria->addNotInCondition('id', $deleteNotInSkillScheduleIds);
					
					$skillScheduleToBeDeleted = CustomerSkillSchedule::model()->findAll($criteria);
					
					if(!empty($skillScheduleToBeDeleted))
					{
						foreach($skillScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
				else if(!isset($_POST['CustomerSkillSchedule']['schedule_day']))
				{
					$criteria = new CDbCriteria;
					$criteria->compare('customer_skill_id',$customerSkill->id);
					$skillScheduleToBeDeleted = CustomerSkillSchedule::model()->findAll($criteria);
					
					if(!empty($skillScheduleToBeDeleted))
					{
						foreach($skillScheduleToBeDeleted as $ssd)
						{
							$ssd->delete();
						}
					}
				}
			}
			
			
			$this->redirect(array('customerSkill/index','customer_id'=>$customer->id));
		}
		
		// echo '<pre>';
		// print_r($customerSkill->customerSkillSchedulesArray);
		// echo '</pre>';
		
		$this->renderPartial('_customCallSchedule',array(
			'model'=>$model,
			'selectedCustomerSkill'=>$customerSkill,
		));
	}
	
	public function actionAddNewSchedule($day,$ctr, $type = 1)
	{
		$nameCtr = 'new-'.$day.'-'.$ctr;
		
		$view = '_customFormSchedule';
		
		
		
		$this->renderPartial($view,array(
			'model' => new CustomerSkillSchedule,
			'day' => $day,
			'skill' => new Skill,
			'name' => $nameCtr,
		));
		
		if(isset($_REQUEST['ajax']))
			Yii::app()->end();
	}
	
	public function actionDialingSetting($customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		if(isset($_POST['enable_goal_disposition']))
		{
			$customerSkill->enable_goal_disposition = $_POST['enable_goal_disposition'];
			if(!$customerSkill->save())
			{
				print_r($customerSkill->getErrors());
			}
		}
		
		if(isset($_POST['dialing_setting']))
		{
			$customerSkill->skill_caller_option_customer_choice = $_POST['dialing_setting'];
			if(!$customerSkill->save())
			{
				print_r($customerSkill->getErrors());
			}
		}
		
		$xfrAddressBooks = CustomerSkillXfrAddressBook::model()->findAll(array(
			'condition' => 'customer_skill_id = :customer_skill_id AND status=1',
			'params' => array(
				':customer_skill_id' => $customerSkill->id
			),
		));
		
		$this->renderPartial('_dialingSetting',array(
			'customerSkill' => $customerSkill,
			'xfrAddressBooks' => $xfrAddressBooks,
		));
	}
	
	public function actionPromo($customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		
		if(isset($_POST['CustomerSkill']['promo_id']))
		{
			if(isset($_REQUEST['submitted_customer_skill_id'] ) && $_REQUEST['submitted_customer_skill_id'] == $customerSkill->id)
			{
				$customerSkill->promo_id = $_POST['CustomerSkill']['promo_id'];
				
				if( !$customerSkill->save() )
				{
					print_r($customerSkill->getErrors());
					exit;
				}
			}
		}
		
		$this->renderPartial('_promo',array(
				'customerSkill'=>$customerSkill,
		));
		
	}
	
	public function actionStartEndDate($customer_skill_id, $customer_id = null)
	{
		if(Yii::app()->user->account->getIsCustomer())
		{
			$customer_id = Yii::app()->user->account->customer->id;
		}
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		
		if(isset($_POST['CustomerSkill']))
		{
			if(isset($_REQUEST['submitted_customer_skill_id'] ) && $_REQUEST['submitted_customer_skill_id'] == $customerSkill->id)
			{
				$currentStartDate = date('m/d/Y', strtotime($customerSkill->start_month));
				$currentEndDate = date('m/d/Y', strtotime($customerSkill->end_month));
				
				$customerSkill->attributes = $_POST['CustomerSkill'];
				
				if( isset($_POST['CustomerSkill']['start_month']) || isset($_POST['CustomerSkill']['end_month']) || isset($_POST['CustomerSkill']['is_contract_hold_start_date']) || isset($_POST['CustomerSkill']['is_contract_hold_end_date']) )
				{
					$newStartDate = date('m/d/Y', strtotime($_POST['CustomerSkill']['start_month']));		
					$newEndDate = date('m/d/Y', strtotime($_POST['CustomerSkill']['end_month']));
					
					if( $currentStartDate == '11/30/-0001' || $currentStartDate == '12/31/1969' || $currentStartDate == '0000-00-00' )
					{
						$currentStartDate = 'blank';
					}
					
					if( $newStartDate == '11/30/-0001' || $newStartDate == '12/31/1969' || $newStartDate == '0000-00-00' )
					{
						$newStartDate = 'blank';
					}
					
					if( $currentEndDate == '11/30/-0001' || $currentEndDate == '12/31/1969' || $currentEndDate == '0000-00-00' )
					{
						$currentEndDate = 'blank';
					}
					
					if( $newEndDate == '11/30/-0001' || $newEndDate == '12/31/1969' || $newEndDate == '0000-00-00' )
					{
						$newEndDate = 'blank';
					}
					
					if( $currentStartDate != $newStartDate || $currentEndDate != $newEndDate )
					{
						$contractDateContent = $customerSkill->skill->skill_name.' - '; 
						
						if( $currentStartDate != $newStartDate )
						{
							$contractDateContent .= 'Start Date Changed from ' .$currentStartDate.' to ' .$newStartDate;
						}
						
						if( $currentEndDate != $newEndDate )
						{
							if( stristr($contractDateContent, 'Start Date') !== false )
							{
								$contractDateContent .= ' | ';
							}
						
							$contractDateContent .= 'End Date Changed from ' .$currentEndDate.' to '.$newEndDate;
						}

						$history = new CustomerHistory;
										
						$history->setAttributes(array(
							'model_id' => $customerSkill->id, 
							'customer_id' => $customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Customer Skill',
							'content' => $contractDateContent,
							'type' => $history::TYPE_UPDATED,
						));

						$history->save(false);
					}
				
					
					$content = '';
					
					$currentCustomerStatus = 'Active';
			
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$currentCustomerStatus = 'Hold';
							}
						}
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$currentCustomerStatus = 'Cancelled';
						}
					}


					if( $customerSkill->is_contract_hold == 1 && !empty($_POST['CustomerSkill']['is_contract_hold_start_date']) && !empty($_POST['CustomerSkill']['is_contract_hold_end_date']) )
					{
						if( $currentCustomerStatus != 'Hold' && time() >= strtotime($_POST['CustomerSkill']['is_contract_hold_start_date']) && time() <= strtotime($_POST['CustomerSkill']['is_contract_hold_end_date']) )
						{
							$content = 'Status Changed from '.$currentCustomerStatus.' to Hold';
						}
					}
					else
					{
						if( !empty($_POST['CustomerSkill']['end_month']) )
						{
							if( time() >= strtotime($_POST['CustomerSkill']['end_month']) )
							{
								if( $currentCustomerStatus != 'Cancelled' )
								{
									$content = 'Status Changed from '.$currentCustomerStatus.' to Cancelled';
								}
							}
							else
							{
								if( $currentCustomerStatus != 'Active' )
								{
									$content = 'Status Changed from '.$currentCustomerStatus.' to Active';
								}
							}
						}
					}
					
					if( $content != '' )
					{
						$history = new CustomerHistory;
											
						$history->setAttributes(array(
							'model_id' => $customerSkill->id, 
							'customer_id' => $customer_id,
							'user_account_id' => Yii::app()->user->account->id,
							'page_name' => 'Customer Skill',
							'content' => $customerSkill->skill->skill_name . ' - '. $content,
							'type' => $history::TYPE_UPDATED,
						));

						if( $history->save(false) )
						{
							$customerQueueViewer = CustomerQueueViewer::model()->find(array(
								'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
								'params' => array(
									':customer_id' => $customer_id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							if( $customerQueueViewer )
							{
								$customerQueueViewer->save(false);
							}
						}
					}
				}
			}
			
			if( !$customerSkill->save() )
			{
				print_r($customerSkill->getErrors());
				exit;
			}
			
			
		}
		
		if(!empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00')
			$customerSkill->start_month  = date("m/d/Y",strtotime($customerSkill->start_month) );
		else
			$customerSkill->start_month  = null;
		
		if(!empty($customerSkill->end_month) && $customerSkill->end_month != '0000-00-00')
			$customerSkill->end_month  = date("m/d/Y",strtotime($customerSkill->end_month) );
		else
			$customerSkill->end_month  = null;
		
		if(!empty($customerSkill->is_contract_hold_start_date) && $customerSkill->is_contract_hold_start_date != '0000-00-00')
			$customerSkill->is_contract_hold_start_date  = date("m/d/Y",strtotime($customerSkill->is_contract_hold_start_date) );
		else
			$customerSkill->is_contract_hold_start_date  = null;
		
		if(!empty($customerSkill->is_contract_hold_end_date) && $customerSkill->is_contract_hold_end_date != '0000-00-00')
			$customerSkill->is_contract_hold_end_date  = date("m/d/Y",strtotime($customerSkill->is_contract_hold_end_date) );
		else
			$customerSkill->is_contract_hold_end_date  = null;
		
		$this->renderPartial('_startEndDate',array(
			'customerSkill'=>$customerSkill,
		));
	}
	
	public function actionGetContractByCompanyAndSkill($company_id, $skill_id)
	{
		
		$company = Company::model()->findByPk($company_id);
		$skill = Skill::model()->findByPk($skill_id);
		
		if($company === null || $skill === null)
			throw new CHttpException('403', 'Page not found');
		
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		$criteria->compare('skill_id', $skill->id);
		$criteria->order = 'contract_name ASC';
		
		$contracts = Contract::model()->findAll($criteria);
		
		$contractList = array();
		foreach($contracts as $contract)
		{
			$contractList[$contract->id]['id'] = $contract->id;
			$contractList[$contract->id]['contract_name'] = $contract->contract_name;
		}
		
		echo CJSON::ENCODE($contractList);
		
		Yii::app()->end();
	}
	
	public function actionToggleCustomerSkillLevel($boolType, $customer_id, $customer_skill_id, $customer_skill_contract_id, $contract_subsidy_level_group_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('customer_skill_id', $customer_skill_id);
		$criteria->compare('customer_skill_contract_id', $customer_skill_contract_id);
		$criteria->compare('contract_subsidy_level_group_id', $contract_subsidy_level_group_id);
		
		$csl = CustomerSkillLevel::model()->find($criteria);
		
		
		if($csl === null)
		{
			$csl = new CustomerSkillLevel;
			$csl->customer_id = $customer_id;
			$csl->customer_skill_id = $customer_skill_id;
			$csl->customer_skill_contract_id = $customer_skill_contract_id;
			$csl->contract_subsidy_level_group_id = $contract_subsidy_level_group_id;
		}
			
		if(isset($_REQUEST['quantityVal']))
		{
			//audit record for contract quantity changes
			if( !$csl->isNewRecord && $_REQUEST['quantityVal'] != $csl->quantity )
			{
				$oldQuantity = 0;
				$newQuantity = 0;
				
				$contract = Contract::model()->findByPk($customer_skill_contract_id);
				$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
				
				if( $contract )
				{
					if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
					{
						if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
						{
							foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
							{
								$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
								$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

								if( $customerSkillLevelArrayGroup != null )
								{							
									if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
									{
										$oldQuantity += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
										$newQuantity += ( $subsidyLevel['goal'] * intval($_REQUEST['quantityVal']) );
									}
								}
							}
						}
					}
					else
					{
						foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
						{
							$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
							$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

							if( $customerSkillLevelArrayGroup != null )
							{							
								if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
								{
									$oldQuantity += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
									$newQuantity += ( $subsidyLevel['high'] * intval($_REQUEST['quantityVal']) );
								}
							}
						}
					}
				}
				
				if( intval($_REQUEST['quantityVal']) > $csl->quantity )
				{
					$updateType = 'Upgrade';
				}
				else
				{
					$updateType = 'Downgrade';
				}
				
				$history = new CustomerHistory;
										
				$history->setAttributes(array(
					'model_id' => $customerSkill->id, 
					'customer_id' => $customer_id,
					'user_account_id' => Yii::app()->user->account->id,
					'page_name' => 'Contract Skill', 
					// 'content' => 'Contract '.$updateType.' - '.$customerSkill->skill->skill_name.' quantity changed from ' .$csl->quantity.' to '.intval($_REQUEST['quantityVal']),
					'content' => 'Contract '.$updateType.' - '.$customerSkill->skill->skill_name.' quantity changed from ' .$oldQuantity.' to '.$newQuantity,
					'type' => $history::TYPE_UPDATED,
				));

				if( $history->save(false) )
				{
					$customerQueueViewer = CustomerQueueViewer::model()->find(array(
						'condition' => 'customer_id = :customer_id AND skill_id = :skill_id',
						'params' => array(
							':customer_id' => $customer_id,
							':skill_id' => $customerSkill->skill_id,
						),
					));
					
					if( $customerQueueViewer )
					{
						$customerQueueViewer->history_contract_quantity_change_date = date('Y-m-d H:i:s');
						$customerQueueViewer->history_contract_quantity_change_type = $updateType;
						$customerQueueViewer->history_contract_quantity_changer = Yii::app()->user->account->accountUser->first_name.' '.Yii::app()->user->account->accountUser->last_name;
						
						$customerQueueViewer->save(false);
					}
				}
			}
			
			$csl->quantity = intval	($_REQUEST['quantityVal']);
		}
		
		//ACtivate - create new row
		if($boolType == 1)
		{
			$csl->status = CustomerSkillLevel::STATUS_ACTIVE;
		}
		
		//Deactivate - delete
		if($boolType == 0)
		{
			$csl->status = CustomerSkillLevel::STATUS_INACTIVE;
		}
		
		if(!$csl->save(false))
		{
			print_r($csl->getErrors());
		}
		
		$csl->save(false);
	}
	
	public function actionCustomerContractSubsidy($customer_id, $customer_skill_id, $subsidy_level_id)
	{
		
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('customer_skill_id', $customer_skill_id);
		// $criteria->compare('subsidy_level_id', $subsidy_level_id);
		// $criteria->compare('type', $type);
		
		$originalValues = array();
		$originalTiers = CustomerSkillSubsidyLevel::model()->findAll($criteria);
		
		if( $originalTiers )
		{
			foreach( $originalTiers as $originalTier )
			{
				$originalValues[$originalTier->customer_skill_id] = $originalTier->subsidy_level_id;
			}
		}
		
		CustomerSkillSubsidyLevel::model()->deleteAll($criteria);
		
		if($subsidy_level_id != 0)
		{
			$cssl = CustomerSkillSubsidyLevel::model()->find($criteria);
			if($cssl === null)
			{
				$cssl = new CustomerSkillSubsidyLevel;
				$cssl->customer_id = $customer_id;
				$cssl->customer_skill_id = $customer_skill_id;
				$cssl->subsidy_level_id = $subsidy_level_id;
			}
			
			//STATIC FOR NOW
			$cssl->status = CustomerSkillSubsidyLevel::STATUS_ACTIVE;
			$cssl->type = 1; 
			
			
			if(!$cssl->save(false))
			{
				print_r($cssl->getErrors());
			}
			
			if( $cssl->save(false) )
			{
				$tierName = '';
				
				$tier = CompanySubsidyLevel::model()->findByPk($subsidy_level_id);
				
				if( $tier )
				{
					$tierName = $tier->name;
				}
				
				$content = 'Tier was changed to ' . $tierName;
			
				$history = new CustomerHistory;
										
				$history->setAttributes(array(
					'model_id' => $customer_skill_id, 
					'customer_id' => $customer_id,
					'user_account_id' => Yii::app()->user->account->id,
					'page_name' => 'Customer Skill',
					'content' => $content,
					'old_data' => json_encode($originalValues),
					'type' => $history::TYPE_UPDATED,
				));

				$history->save(false);
			}
		}
		
	}
	
	public function actionToggleCustomerSkillSubsidyLevel($boolType, $customer_id, $customer_skill_id, $subsidy_level_id, $type)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('customer_skill_id', $customer_skill_id);
		$criteria->compare('subsidy_level_id', $subsidy_level_id);
		// $criteria->compare('type', $type);
		
		$cssl = CustomerSkillSubsidyLevel::model()->find($criteria);
		
		
		if($cssl === null)
		{
			$cssl = new CustomerSkillSubsidyLevel;
			$cssl->customer_id = $customer_id;
			$cssl->customer_skill_id = $customer_skill_id;
			$cssl->subsidy_level_id = $subsidy_level_id;
		}
		
		$cssl->type = $type;
		
		//ACtivate - create new row
		if($boolType == 1)
		{
			$cssl->status = CustomerSkillSubsidyLevel::STATUS_ACTIVE;
		}
		
		//Deactivate - delete
		if($boolType == 0)
		{
			$cssl->status = CustomerSkillSubsidyLevel::STATUS_INACTIVE;
		}
		
		if(!$cssl->save(false))
		{
			print_r($cssl->getErrors());
		}
		
		$cssl->save(false);
	}
	
	public function actionToggleCustomerSkillSubsidy($boolType, $customer_id, $customer_skill_id, $subsidy_id)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $customer_id);
		$criteria->compare('customer_skill_id', $customer_skill_id);
		$criteria->compare('subsidy_id', $subsidy_id);
		
		$css = CustomerSkillSubsidy::model()->find($criteria);
		
		
		if($css === null)
		{
			$css = new CustomerSkillSubsidy;
			$css->customer_id = $customer_id;
			$css->customer_skill_id = $customer_skill_id;
			$css->subsidy_id = $subsidy_id;
		}
		
		//ACtivate - create new row
		if($boolType == 1)
		{
			$css->status = CustomerSkillSubsidy::STATUS_ACTIVE;
		}
		
		//Deactivate - delete
		if($boolType == 0)
		{
			$css->status = CustomerSkillSubsidy::STATUS_INACTIVE;
		}
		
		if(!$css->save(false))
		{
			print_r($css->getErrors());
		}
		
		if( $css->save(false) )
		{
			$subsidyName = '';
			
			$subsidy = CompanySubsidy::model()->findByPk($subsidy_id);
			
			if( $subsidy )
			{
				$subsidyName = '<b>'.$subsidy->subsidy_name.'</b> ';
			}
			
			if( $boolType == 1 )
			{
				$editType = ' on';
			}
			else
			{
				$editType = ' off';
			}
			
			$content = $subsidyName . 'subsidy was turned' . $editType;
			
			$history = new CustomerHistory;
									
			$history->setAttributes(array(
				'model_id' => $customer_skill_id, 
				'customer_id' => $customer_id,
				'user_account_id' => Yii::app()->user->account->id,
				'page_name' => 'Customer Skill',
				'content' => $content,
				'type' => $history::TYPE_UPDATED,
			));

			$history->save(false);
		}
	}
	
	public function actionCancel($customer_id, $contract_id, $skill_id, $start_month)
	{
		$token = sha1(time());
		$status = 'error';
		$message = '';
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if( $customer )
		{
			if( !empty($customer->email_address) )
			{
				$existingModel = CustomerCancellation::model()->find(array(
					'condition' => 'customer_id = :customer_id AND status=2',
					'params' => array(
						':customer_id' => $customer->id
					),
				));
				
				if( $existingModel )
				{
					$existingModel->status = 3;
					$existingModel->save(false);
				}
				
				$model = new CustomerCancellation;
					
				$model->setAttributes(array(
					'account_id' => Yii::app()->user->account->id, 
					'customer_id' => $customer->id,
					'contract_id' => $contract_id,
					'skill_id' => $skill_id,
					'start_date' => $start_month,
					'first_name' => $customer->firstname,
					'last_name' => $customer->lastname,
					'email' => $customer->email_address,
					'phone_number' => $customer->phone,
					'token' => $token
				));
				
				if( $model->save(false) )
				{
					$status = 'success';
					$message = 'Cancellation email was sent to the customer.';
					
					//Send Email
					Yii::import('application.extensions.phpmailer.JPhpMailer');
			
					$mail = new JPhpMailer;
				
					$mail->SMTPAuth = true;		
					$mail->SMTPSecure = 'tls';   		
					$mail->SMTPDebug = 2; 
					$mail->Port = 25;      
					$mail->Host = 'mail.engagex.com';	
					$mail->Username = 'service@engagex.com';  
					$mail->Password = "_T*8c>ja";           											
			
					$mail->SetFrom('customerservice@engagex.com', 'Customer Service');
					
					$mail->Subject = 'We are sad to see you go!';
					
					$mail->AddAddress( $customer->email_address );
					 
					$mail->AddBCC('erwin.datu@engagex.com');
					$mail->AddBCC('jim.campbell@engagex.com');

					// $mail->AddCC('rubyann.freo@engagex.com');
					
					$emailTemplate = '<p>'.$customer->getFullName() . ' we are sad to see you go!</p>';  
					$emailTemplate .= '<p>To complete the cancellation process simply click on the link below and provide a reason.</p>';
					$emailTemplate .= '<p>Thanks your Engagex Customer Service Team</p>';
					$emailTemplate .= '<p><a href="https://portal.engagexapp.com/index.php/cancellation/index?token='.$token.'">Click to Cancel</a></p>';
					
					$mail->MsgHTML( $emailTemplate );
											
					$mail->Send();
				}
				else
				{
					$message = 'Database error. Email not sent.';
				}
			}
			else
			{
				$message = 'Invalid customer email address';
			}
		}
		else
		{
			$message = 'Customer record not found.';
		}
		
		Yii::app()->user->setFlash($status, $message);
		$this->redirect(array('index', 'customer_id'=>$customer->id));
	}
	
	public function actionDownload($id = null)
	{
		if ($id == null)
		{
			throw new CHttpException(404,'The requested page does not exist.');
		}
		
		$fileUpload = Fileupload::model()->findByPk($id);
		
		if( $fileUpload )
		{
			$file = $fileUpload->generated_filename;
			
			$extension = strtolower(substr(strrchr($file,"."),1));
			
			$explodedFile = explode('/',$file);
			
			if(isset($explodedFile[1]) && $explodedFile[1] == 'pdfs')
				$filePath = Yii::getPathOfAlias('webroot') . $file;
			else
				$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $file;
			
			$customerFileDownloadName = null;
			$allowDownload = false;
			
			if(file_exists($filePath))
			{
				$allowDownload = true;
			}
			
			if ( $allowDownload )
			{
				// required for IE
				if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
				
				$ctype="application/force-download";
				
				header("Pragma: public"); 
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false); // required for certain browsers
				header("Content-Type: $ctype");

				# change, added quotes to allow spaces in filenames, 
				
				if($customerFileDownloadName !== null)
					header("Content-Disposition: attachment; filename=\"".basename($customerFileDownloadName)."\";" );
				else
					header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
				
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ".filesize($filePath));

				readfile("$filePath");
			} 
			else
			{
				// Do processing for invalid/non existing files here
				echo 'File not found.';
			}
		}
		else
		{
			echo 'File not found.';
		}
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return CustomerSkill the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=CustomerSkill::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CustomerSkill $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='customer-skill-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
