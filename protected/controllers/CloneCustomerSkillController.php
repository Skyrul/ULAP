<?php 

ini_set('memory_limit', '5000M');
set_time_limit(0);

class CloneCustomerSkillController extends Controller
{
	
	public function actionIndex()
	{ exit;
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
				t.contract_id IN (4) 
				AND t.skill_id IN (12) 
				AND t.status = 1
				 #AND customer.id = 1565
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
							$isActiveCustomer = false;
							$response = 'Cancelled customer';
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
							
							if( empty($existing2017Skill) )
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
								
								
								
								// continue;
								
								//create clone
								$cs = new CustomerSkill;
								$cs->customer_id = $customerSkill->customer_id;
								$cs->skill_id = $skillId;
								$cs->contract_id = $contractId;
								$cs->start_month = '2017-01-01';
								//predefined customer settings for each skill - asked before updating this attributes
								
								
								// $cs->is_custom_call_schedule = 0;
								// $cs->skill_caller_option_customer_choice = CustomerSkill::CUSTOMER_CHOICE_AREA_PREFIX_CNAM; 
								// $cs->status = CustomerSkill::STATUS_ACTIVE;
								
								##over writing predefined for cloning...
								$cs->is_custom_call_schedule = $customerSkill->is_custom_call_schedule;
								$cs->skill_caller_option_customer_choice = $customerSkill->skill_caller_option_customer_choice;
								$cs->status = $customerSkill->status;
								
								if( !$cs->save() )
								{
									print_r( $cs->getErrors() );
									break;
								}
								
								##creating customer skill level
								$criteria = new CDbCriteria;
								$criteria->compare('customer_id', $customerSkill->customer_id);
								$criteria->compare('customer_skill_contract_id', $customerSkill->contract_id);
								$criteria->compare('status', 1);
								
								$customerSkillLevels = CustomerSkillLevel::model()->findAll($criteria);
								
								if(!empty($customerSkillLevels))
								{ 
									// foreach($customerSkillLevels as $customerSkillLevel)
									// {
										/* //GOAL VOLUME
										$criteria = new CDbCriteria;
										$criteria->compare('contract_id', $customerSkillLevel->customer_skill_contract_id);
										$criteria->compare('type', 1);
										$criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
										$criteria->compare('column_name', 'goal');
										
										$contractSubsidyLevelGroup = ContractSubsidyLevel::model()->find($criteria);
										
										if(!empty($contractSubsidyLevelGroup))
										{
											$criteria = new CDbCriteria;
											$criteria->compare('contract_id', $cs->contract_id);
											$criteria->compare('type', 1);
											$criteria->compare('column_name', 'goal');
											$criteria->compare('column_value', $contractSubsidyLevelGroup->column_value);
											
											$newContractSubsidyLevelGroup = ContractSubsidyLevel::model()->find($criteria);
											
											if(!empty($newContractSubsidyLevelGroup))
											{
												
												$criteria = new CDbCriteria;
												$criteria->compare('customer_id', $cs->customer_id);
												$criteria->compare('customer_skill_id', $cs->id);
												$criteria->compare('customer_skill_contract_id', $cs->contract_id);
												$criteria->compare('contract_subsidy_level_group_id', $newContractSubsidyLevelGroup->group_id);
												
												$newCustomerSkillLevel = CustomerSkillLevel::model()->find($criteria);
												
												if(!empty($newCustomerSkillLevel))
												{
													$newCustomerSkillLevel->quantity = $customerSkillLevel->quantity;
													$newCustomerSkillLevel->status = $customerSkillLevel->status;
													
													if(!$newCustomerSkillLevel->save(false))
													{
														print_r($newCustomerSkillLevel->getErrors()); exit;
													}
												}
												else
												{
													$createCustomerSkillLevel = new CustomerSkillLevel;
													$createCustomerSkillLevel->quantity = $customerSkillLevel->quantity;
													$createCustomerSkillLevel->status = $customerSkillLevel->status;
													$createCustomerSkillLevel->customer_id = $cs->customer_id;
													$createCustomerSkillLevel->customer_skill_id = $cs->id;
													$createCustomerSkillLevel->customer_skill_contract_id = $cs->contract_id;
													$createCustomerSkillLevel->contract_subsidy_level_group_id = $newContractSubsidyLevelGroup->group_id;
													
													if(!$createCustomerSkillLevel->save(false))
													{
														print_r($createCustomerSkillLevel->getErrors()); exit;
													}
												}
											}
										} */
										
										
									// }
									
									foreach($customerSkillLevels as $customerSkillLevel)
									{
										//LEAD VOLUME
										// $criteria = new CDbCriteria;
										// $criteria->compare('contract_id', $customerSkillLevel->customer_skill_contract_id);
										// $criteria->compare('type', 2);
										// $criteria->compare('group_id', $customerSkillLevel->contract_subsidy_level_group_id);
										// $criteria->compare('column_name', 'goal');
										
										// $contractSubsidyLevelGroup = ContractSubsidyLevel::model()->find($criteria);
										
										// if(!empty($contractSubsidyLevelGroup))
										// {
											// $criteria = new CDbCriteria;
											// $criteria->compare('contract_id', $cs->contract_id);
											// $criteria->compare('type', 2);
											// $criteria->compare('column_name', 'goal');
											// $criteria->compare('column_value', $contractSubsidyLevelGroup->column_value);
											
											// $newContractSubsidyLevelGroup = ContractSubsidyLevel::model()->find($criteria);
											
											// if(!empty($newContractSubsidyLevelGroup))
											// {
												
												$criteria = new CDbCriteria;
												$criteria->compare('customer_id', $cs->customer_id);
												$criteria->compare('customer_skill_id', $cs->id);
												$criteria->compare('customer_skill_contract_id', $cs->contract_id);
												// $criteria->compare('contract_subsidy_level_group_id', $newContractSubsidyLevelGroup->group_id);
												$criteria->compare('contract_subsidy_level_group_id', 1166);
												
												$newCustomerSkillLevel = CustomerSkillLevel::model()->find($criteria);
												
												if(!empty($newCustomerSkillLevel))
												{
													$newCustomerSkillLevel->quantity = $customerSkillLevel->quantity;
													$newCustomerSkillLevel->status = $customerSkillLevel->status;
													
													if(!$newCustomerSkillLevel->save(false))
													{
														print_r($newCustomerSkillLevel->getErrors()); exit;
													}
												}
												else
												{
													$createCustomerSkillLevel = new CustomerSkillLevel;
													$createCustomerSkillLevel->quantity = $customerSkillLevel->quantity;
													$createCustomerSkillLevel->status = $customerSkillLevel->status;
													$createCustomerSkillLevel->customer_id = $cs->customer_id;
													$createCustomerSkillLevel->customer_skill_id = $cs->id;
													$createCustomerSkillLevel->customer_skill_contract_id = $cs->contract_id;
													$createCustomerSkillLevel->contract_subsidy_level_group_id = 1166;
													
													if(!$createCustomerSkillLevel->save(false))
													{
														print_r($createCustomerSkillLevel->getErrors()); exit;
													}
												}
											// }
										// }
									}
								}
								
								
								##creating customer company subsidy##
								$criteria = new CDbCriteria;
								$criteria->compare('customer_id', $customerSkill->customer_id);
								$criteria->compare('customer_skill_id', $customerSkill->id);
								
								$existingSkillSubsidy = CustomerSkillSubsidy::model()->find($criteria);
								
								if(!empty($existingSkillSubsidy))
								{
									
									$criteria = new CDbCriteria;
									$criteria->compare('skill_id', $cs->skill_id);
									$criteria->compare('contract_id', $cs->contract_id);
								
									$companySubsidy = CompanySubsidy::model()->find($criteria);
									
									
									if(!empty($companySubsidy))
									{
										$customerSkillSubsidy = new CustomerSkillSubsidy;
										$customerSkillSubsidy->customer_id = $cs->customer_id;
										$customerSkillSubsidy->customer_skill_id = $cs->id;
										$customerSkillSubsidy->type = 0;
										$customerSkillSubsidy->status = $existingSkillSubsidy->status;
										$customerSkillSubsidy->subsidy_id = $companySubsidy->id;
										
										
										if(!$customerSkillSubsidy->save(false))
										{
											print_r($customerSkillSubsidy->getErrors()); exit;
										}
										
									}
									
									##get selected subsidy level##
									$criteria = new CDbCriteria;
									$criteria->compare('customer_id', $customerSkill->customer_id);
									$criteria->compare('customer_skill_id', $customerSkill->id);
								
									$existingCustomerSkillSubsidyLevel = CustomerSkillSubsidyLevel::model()->find($criteria);
									
									if(!empty($existingCustomerSkillSubsidyLevel))
									{
										$existingCompanySubsidyLevel = CompanySubsidyLevel::model()->findByPk($existingCustomerSkillSubsidyLevel->subsidy_level_id);
										if(!empty($existingCompanySubsidyLevel))
										{
											
											$criteria = new CDbCriteria;
											$criteria->compare('subsidy_id', $companySubsidy->id);
											$criteria->compare('tier_link', $existingCompanySubsidyLevel->tier_link);
									
											$findCompanySubsidyLevel = CompanySubsidyLevel::model()->find($criteria);
											
											if(!empty($findCompanySubsidyLevel))
											{
												$newCustomerSkillSubsidyLevel = new CustomerSkillSubsidyLevel;
												$newCustomerSkillSubsidyLevel->customer_id = $cs->customer_id;
												$newCustomerSkillSubsidyLevel->customer_skill_id = $cs->id;
												$newCustomerSkillSubsidyLevel->subsidy_level_id = $findCompanySubsidyLevel->id;
												$newCustomerSkillSubsidyLevel->type = $existingCustomerSkillSubsidyLevel->type;
												$newCustomerSkillSubsidyLevel->status = $existingCustomerSkillSubsidyLevel->status;
												
												if(!$newCustomerSkillSubsidyLevel->save(false))
												{
													print_r($newCustomerSkillSubsidyLevel->getErrors()); exit;
												}
											}
										}
									}
									
									
								}
								
								##creating Child Skill Tab##
								
								$skill = $customerSkill->skill;
								$newSkill = Skill::model()->findByPk($skillId);
								
								if(!empty($skill->skillChilds) && !empty($newSkill->skillChilds) )
								{
									$isEnabledArray = array();
									$ctr = 0;
									
									foreach($skill->skillChilds as $skillChild)
									{
										$criteria = new CDbCriteria;
										$criteria->compare('customer_id', $customerSkill->customer_id);
										$criteria->compare('skill_id', $skillChild->skill_id);
										$criteria->compare('customer_skill_id', $customerSkill->id);
										$criteria->compare('skill_child_id', $skillChild->id);
										$customerSkillChild = CustomerSkillChild::model()->find($criteria);
										
										$isEnabledArray[$ctr] = 0;
										
										if($customerSkillChild !== null)
										{
											$isEnabledArray[$ctr] = $customerSkillChild->is_enabled;
										}
										
										$ctr++;
									}
									
									$ctr = 0;
									
									foreach($newSkill->skillChilds as $newChildSkill)
									{
										$newCustomerSkillChild = new CustomerSkillChild;
										$newCustomerSkillChild->customer_id = $customerSkill->customer_id;
										$newCustomerSkillChild->skill_id = $skillId;
										$newCustomerSkillChild->customer_skill_id = $cs->id;
										$newCustomerSkillChild->skill_child_id = $newChildSkill->id;
										
										$newCustomerSkillChild->is_enabled = $isEnabledArray[$ctr];
					
										if(!$newCustomerSkillChild->save())
										{
											print_r($newCustomerSkillChild->getErrors());  exit;
										}
										
										$ctr++;
									}
								}
								
								##creating Custom Call Schedule Tab##
								if($customerSkill->is_custom_call_schedule)
								{
									$criteria = new CDbCriteria;
									$criteria->compare('customer_skill_id', $customerSkill->id);
									$csss = CustomerSkillSchedule::model()->findAll($criteria);
									
									if(!empty($csss))
									{
										foreach($csss as $css)
										{
											$customerSkillSchedule = new CustomerSkillSchedule;
											$customerSkillSchedule->customer_skill_id = $cs->id;
											$customerSkillSchedule->schedule_day = $css->schedule_day;
											$customerSkillSchedule->schedule_start = $css->schedule_start;
											$customerSkillSchedule->schedule_end = $css->schedule_end;
											$customerSkillSchedule->status = $css->status;
											$customerSkillSchedule->is_deleted = $css->is_deleted;
											
											if(!$customerSkillSchedule->save(false))
											{
												print_r($customerSkillSchedule->getErrors()); exit;
											}
										}
									}
								}
								
								##creating Dialing settings tab == Already in predefined step##
								//.......
								
								##creating Extra Tab##
								$customerExras = CustomerExtra::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract->id,
										':skill_id' => $customerSkill->skill->id,
									),
								));
								
								if( $customerExras ) 
								{
									foreach( $customerExras as $customerExra )
									{
										if($customerExra->year == '2017')
										{
											$newCustomerExtra = new CustomerExtra;
											$newCustomerExtra->attributes = $customerExra->attributes;
											$newCustomerExtra->contract_id = $contractId;
											$newCustomerExtra->skill_id = $skillId;
											
											if(! $newCustomerExtra->save() )
											{
												print_r($newCustomerExtra->getErrors()); exit;
											}
										}
										
										
									}
								}
							
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
				
				
				// foreach($clones as $clone)
				// {
					// echo '"'.$clone['customer_id'].'", ';
					// echo '"'.$clone['customer_name'].'", ';
					// echo '"'.$clone['contract_id'].'", ';
					// echo '"'.$clone['contract_name'].'", ';
					// echo '"'.$clone['skill_id'].'", ';
					// echo '"'.$clone['skill_name'].'", ';
					// echo '"'.$clone['start_month'].'", ';
					// echo '"'.$clone['end_month'].'"';
					
					
					// echo '<br>';
				// }
								
								
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