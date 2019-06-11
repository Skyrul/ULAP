<?php 

class CronCallManagementController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index'),
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		if( !isset($_GET['debug']) )
		{
			exit;
		}
		
		$dbInserts = 0;
		
		$agentAssignedSkills = AccountSkillAssigned::model()->findAll(array(
			'group' => 't.account_id',
			'with' => 'account',
			'condition' => 'account.account_type_id = :account_type_id',
			'params' => array(
				':account_type_id' => Account::TYPE_AGENT,
			),
		));
		
		if ( $agentAssignedSkills )
		{
			foreach( $agentAssignedSkills as $agentAssignedSkill )
			{
				if( isset($agentAssignedSkill->account) )
				{
					$valid = false;
					
					$voiceContacts = 0;
					$appointments = 0;
					$conversionRate = 0;
					$totalDialsPerHour = 0;
					$appointmentsPerHour = 0;
					$statsPerHour = null;
					$oldStatsPerHour = null;
					$fulfillmentType = null;
					$trending = null;
					
					$trStyle = '#FFF';
					
					//APH / DPH
					
					$contract = Contract::model()->find(array(
						'condition' => 'skill_id = :skill_id',
						'params' => array(
							':skill_id' => $agentAssignedSkill->skill_id,
						),
					));
		
					$clockedHours = $agentAssignedSkill->account->getTotalLoginHours();

					if( $contract && $clockedHours > 0 )
					{
						$outboundDials = LeadCallHistory::model()->findAll(array(
							'condition' => 'agent_account_id = :agent_account_id',
							'params' => array(
								':agent_account_id' => $agentAssignedSkill->account_id,
							),
							'order' => 'start_call_time DESC',
						));

						if( $outboundDials )
						{
							foreach( $outboundDials as $outboundDial )
							{
								if( isset($outboundDial->skillDisposition) && $outboundDial->skillDisposition->is_voice_contact == 1 )
								{ 
									$voiceContacts++;
								}
							}
						}
						
						$appointments = LeadCallHistory::model()->count(array(
							'with' => array('skillDisposition'),
							'condition' => 'agent_account_id = :agent_account_id AND skillDisposition.is_appointment_set=1',
							'params' => array(
								':agent_account_id' => $agentAssignedSkill->account_id,
							),
						));
						
						if( $appointments > 0 && $voiceContacts > 0 )
						{
							$conversionRate = $appointments / $voiceContacts;
						}
						
						if( count($outboundDials) > 0 && $clockedHours > 0 )
						{
							$totalDialsPerHour = count($outboundDials) / $clockedHours;
						}
						
						if( $appointments > 0 && $clockedHours > 0 )
						{
							$appointmentsPerHour = $appointments / $clockedHours;
						}
						
						
						//account schedule
						$schedules = AccountLoginSchedule::model()->findAll(array(
							'condition' => 'account_id = :account_id AND day_name = :day_name',
							'params' => array(
								':account_id' => $agentAssignedSkill->account_id,
								':day_name' => date('l'),
							),
							'order' => 'date_created ASC',
						));
						
						
						// get stats per hour
						if( $contract )
						{
							if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
							{
								$statsPerHour = $appointmentsPerHour;
								$fulfillmentType = 'APH';
							}
							else
							{
								$statsPerHour = $totalDialsPerHour;
								$fulfillmentType = 'DPH';
							}
						}
						
						
						//get old stats and trending
						$oldStatModels = AgentCallManagementReport::model()->findAll(array(
							'condition' => 'agent_account_id = :agent_account_id AND skill_id = :skill_id',
							'params' => array(
								':agent_account_id' => $agentAssignedSkill->account_id,
								':skill_id' => $agentAssignedSkill->skill_id,
							)
						));
						
						if( $oldStatModels )
						{
							foreach( $oldStatModels as $oldStatModel )
							{
								$oldStatsPerHour += $oldStatModel->stats_per_hour;
							}
							
							if( $statsPerHour > $oldStatsPerHour )
							{
								$trending = 1;
							}
							else
							{
								$trending = 0;
							}
						}

						$existingCallManagement = AgentCallManagementReport::model()->find(array(
							'condition' => 'agent_account_id = :agent_account_id AND skill_id = :skill_id',
							'params' => array(
								':agent_account_id' => $agentAssignedSkill->account_id,
								':skill_id' => $agentAssignedSkill->skill_id,
							),
						));
						
						if( empty($existingCallManagement) )
						{
							$valid = true;
						}
						else
						{
							if( strtotime('-1 Hour') >= strtotime( $existingCallManagement->date_created ) )
							{
								$valid = true;
							}
						}
		
						if( $valid )
						{
							$agentCallManagement = new AgentCallManagementReport;
							
							$agentCallManagement->setAttributes(array(
								'agent_account_id' => $agentAssignedSkill->account_id,
								'skill_id' => $agentAssignedSkill->skill_id,
								'stats_per_hour' => round($statsPerHour, 3),
								'fulfillment_type' => $fulfillmentType,
								'trending' => $trending,
							));
							
							if( $agentCallManagement->save(false) )
							{
								$dbInserts++;
							}
						}
					}
				}
			}
		}
		
		echo '<br><br>';
		echo 'dbInserts: ' . $dbInserts;
		echo '<br><br>';
		echo 'end..';
	}
	
}

?>