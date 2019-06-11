<?php 

ini_set('memory_limit', '5000M');
set_time_limit(0);

class CloneCustomerSkillNoteController extends Controller
{
	
	public function actionIndex()
	{
		/* 
			> 2016 Contracts/Skills
				
				7 - State Farm Per Appointment 2016
				11 - SF Policy Review per Appointment 2016

				4 - State Farm Per Name 2016
				12 - SF Policy Review per Name 2016	
			
			-------------------------------------------------------------
			
			> 2017 Contracts/SKills
			
				33 - SF Policy Review per Appointment 2017
				52 - State Farm Per Appointment 2017	
			
				34 - SF Policy Review per Name 2017
				51 - State Farm Per Name 2017						
		*/
		
		$customerCtr = 1;
		$clones = array();
		
		// $customerSkills = CustomerSkill::model()->findAll(array(
			// 'with' => 'customer', 
			// 'condition' => '
				// t.contract_id IN (4,7) 
				// AND t.skill_id IN (11,12) 
				// AND t.status = 1
				// AND customer.id IS NOT NULL
				// AND customer.status = 1
				// AND customer.is_deleted = 0
			// ',
		// ));
		
		$customerSkills = CustomerSkill::model()->findAll(array(
			'with' => 'customer', 
			'condition' => '
				t.contract_id IN (4,7) 
				AND t.skill_id IN (11,12) 
				AND t.status = 1
				AND customer.id NOT IN (1565, 1133)
				#AND customer.id = 1565
				#AND customer.id = 1133
			',
		));
		
		echo 'customerSkills: ' . count($customerSkills);
		
		echo '<br><br>';
		
		
		if( $customerSkills )
		{
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{
				foreach( $customerSkills as $customerSkill )
				{
					$isActiveCustomer = true;
					
					//check if cancelled
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							// $isActiveCustomer = false;
							// $response = 'Cancelled customer';
						}
					}
					
					echo '#'.$customerCtr++;
					echo '<br>';
					echo 'Customer ID: ' . $customerSkill->customer_id;
					echo '<br>';
					echo 'Customer Name: ' . $customerSkill->customer->getFullName();
					echo '<br>';
					echo 'Contract: ' . $customerSkill->contract_id.' - '. $customerSkill->contract->contract_name;
					echo '<br>';
					echo 'Skill: ' .  $customerSkill->skill_id.' - '. $customerSkill->skill->skill_name;
					echo '<br>';
					echo 'Start Date: ' . $customerSkill->start_month;
					echo '<br>';
					echo 'End Date: ' . $customerSkill->end_month;
					
					echo '<br>';
					
					if( $isActiveCustomer )
					{
						$contractId = 0;
						$skillId = 0;
						
						if( $customerSkill->skill_id == 11 ) //SF Policy Review per Appointment 2016
						{
							$contractId = 52; //State Farm Per Appointment 2017	
							$skillId = 33; //SF Policy Review per Appointment 2017
						}
						
						if( $customerSkill->skill_id == 12 ) //SF Policy Review per Name 2016	
						{
							$contractId = 51; //State Farm Per Name 2017
							$skillId = 34; //SF Policy Review per Name 2017
						}
						
						if( $contractId && $skillId )
						{
							$existing2017Skill = CustomerSkill::model()->find(array(
								'condition' => '
									customer_id = :customer_id
									AND contract_id = :contract_id 
									AND skill_id = :skill_id
								',
								'params' => array(
									':customer_id' => $customerSkill->customer_id,
									':contract_id' => $contractId,
									':skill_id' => $skillId
								),
							));
							
							if( !empty($existing2017Skill) )
							{
								$clones[] = array(
									'customer_id' => $customerSkill->customer_id,
									'customer_name' => $customerSkill->customer->getFullName(),
									'contract_id' => $customerSkill->contract_id,
									'contract_name' => $customerSkill->contract->contract_name,
									'skill_id' => $customerSkill->skill_id,
									'skill_name' => $customerSkill->skill->skill_name,
									'start_month' => $customerSkill->start_month,
									'end_month' => $customerSkill->end_month,
								);

								// echo 'Response: Create 2017 clone.';
								
								
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
								
								if($existing2017Skill->is_custom_call_schedule)
									$historyNote['custom_call_schedule'] = 'On';
								else
									$historyNote['custom_call_schedule'] = 'Off';
								
								if($existing2017Skill->skill_caller_option_customer_choice == CustomerSkill::CUSTOMER_CHOICE_PHONE)
								{
									$historyNote['dial_setting'] = 'Dial As Office Phone number';
								}
								
								if($existing2017Skill->skill_caller_option_customer_choice == CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM)
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
									
								echo '<pre>';
								print_r($historyNote);
								echo '</pre>';
								
								$content = '';
								$content .= $customerSkill->contract->contract_name.' contract completed '.date('m/d/Y',strtotime($customerSkill->end_month)).'. '
										.$existing2017Skill->contract->contract_name.' contract created and activated '.date('m/d/Y',strtotime($existing2017Skill->start_month))
										.'. Cloned settings below:<br>';
								
								$content .= '<br><b>Contract</b><br>';
								$content .=  'Start/End Dates: ['.date('m/d/Y',strtotime($customerSkill->start_month)).']<br>';
								
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
								echo $content;
								
								$history = new CustomerHistory;
									
								$history->setAttributes(array(
									'model_id' => $customerSkill->id, 
									'customer_id' => $customerSkill->customer_id,
									'user_account_id' => null,
									'page_name' => 'Customer Skill',
									'content' => $content,
									'type' => $history::TYPE_UPDATED,
								));
								
								$history->save(false);
							}
							else
							{
								echo 'Response: 2017 skill already exists.';
								echo '<br>';
							}
						}
						else
						{
							echo 'Contract / Skill ID error.';
						}
					}
					else
					{
						echo 'Response: Cancelled customer';
						echo '<br>';
					}
					
					echo '<br><hr><br>';
				}
				
				
				foreach($clones as $clone)
				{
					echo '"'.$clone['customer_id'].'", ';
					echo '"'.$clone['customer_name'].'", ';
					echo '"'.$clone['contract_id'].'", ';
					echo '"'.$clone['contract_name'].'", ';
					echo '"'.$clone['skill_id'].'", ';
					echo '"'.$clone['skill_name'].'", ';
					echo '"'.$clone['start_month'].'", ';
					echo '"'.$clone['end_month'].'"';
					
					
					echo '<br>';
				}
								
								
				$transaction->commit();
			}
			catch(Exception $e)
			{
				print_r($e);
				$transaction->rollback();
			}
		}
		
		echo '<br><br>clones: ' . count($clones);
	}
	
}

?>