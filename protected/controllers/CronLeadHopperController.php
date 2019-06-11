<?php 

class CronLeadHopperController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$hopperRecords = LeadHopper::model()->count(array(
			'condition' => 'status != "DONE"',
		));

		//active customers and active lists
		$lists = Lists::model()->findAll(array(
			'with' => array('customer'),
			'together' => true,
			'condition' => 'customer.status=1 AND t.status=1',
		));
		
		$hopperCtr = 0;
		
		if( $lists )
		{
			foreach( $lists as $list )
			{
				$leadCtr = 0;
				
				$contractLevelMaxLead = 0;
				
				//get dial settings from skill
				$skill = Skill::model()->findByPk($list->skill_id);
				
				//get dial settings from contract and contract levels
				$selectedCustomerSkills = CustomerSkill::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND status = :status',
					'params' => array(
						':customer_id' => $list->customer_id,
						':status' => CustomerSkill::STATUS_ACTIVE,
					),
				));

				if( $selectedCustomerSkills )
				{
					foreach( $selectedCustomerSkills as $selectedCustomerSkill )
					{
						$contractLevels = ContractSubsidyLevel::model()->findAll(array(
							'condition' => 'contract_id = :contract_id',
							'params' => array(
								':contract_id' => $selectedCustomerSkill->contract_id,
							),
						));
						
						if( $contractLevels )
						{
							foreach( $contractLevels as $contractLevel )
							{
								if( $contractLevel->type == 2 && $contractLevel->column_name == 'high' )
								{
									$contractLevelMaxLead += $contractLevel->column_value;
								}
								
								if( $contractLevel->type == 1 && $contractLevel->column_name == 'amount' )
								{
									$contractLevelMaxLead += $contractLevel->column_value;
								}
							}
						}
					}

					if( $contractLevelMaxLead > 0 )
					{
						// Random
						// Alpha by last name
						// Custom Date
						
						if( $list->lead_ordering == 2 )
						{
							$order = 'last_name ASC';
						}
						elseif( $list->lead_ordering == 3 )
						{
							$order = 'custom_date DESC';
						}
						else
						{
							$order = 'RAND()';
						}
						
						//get callable leads
						$leads = Lead::model()->findAll(array(
							'condition' => 'list_id = :list_id AND type=1 AND t.status=1',
							'params' => array(
								':list_id' => $list->id,
							),
							'order' => $order,
						));
						
						if( $leads )
						{
							foreach( $leads as $lead )
							{
								if( $leadCtr < $contractLevelMaxLead )
								{
									$existingHopperEntry = LeadHopper::model()->find(array(
										'condition' => 'lead_id = :lead_id',
										'params' => array(
											':lead_id' => $lead->id,
										),
									));
									
									if( empty($existingHopperEntry) && $lead->number_of_dials < $skill->max_dials )
									{
										$hopperEntry = new LeadHopper;
										
										$hopperEntry->setAttributes(array(
											'lead_id' => $lead->id,
											'list_id' => $list->id,
											'skill_id' => $list->skill_id,
											'customer_id' => $list->customer_id,
											'lead_language' => $lead->language,
										));
										
										if( $hopperEntry->save(false) )
										{
											$leadCtr++;
											$hopperCtr++;
										}
									}
									
									if( $hopperCtr >= 200 )
									{
										break;
									}
								}
							}
						}
					}
				}
			}
		}
		
		echo $hopperCtr . ' end...';
	}
}

?>