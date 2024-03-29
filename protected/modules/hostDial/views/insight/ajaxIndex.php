<div class="text-center">
	<div class="row">
		<div class="col-sm-12 infobox-container">
			<?php 
				if( $customerSkills )
				{
					$skillColors = array('orange', 'orange2', 'blue2', 'green2', 'purple', 'black', 'grey', 'light-brown');
					
					foreach( $customerSkills as $customerSkill )
					{
						$skillColorsRandomKeys = array_rand($skillColors, 2);
						
						$totalLeads = 0;
						$contract = $customerSkill->contract;
						
						if($contract->fulfillment_type != null )
						{
							##get Appointment that has been scheduled ##
							$appointmentSetMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('INSERT APPOINTMENT', 'APPOINTMENT SET', 'CANCEL APPOINTMENT', 'RESCHEDULE APPOINTMENT') 
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
						
							
							$command = Yii::app()->db->createCommand($appointmentSetMTDSql);
							$appointmentSetMTD = $command->queryRow();
							
							$noShowMTDSql = "
								SELECT count(distinct lch.lead_id) AS totalCount 
								FROM ud_lead_call_history lch 
								LEFT JOIN ud_lists ls ON ls.id = lch.list_id 
								LEFT JOIN ud_calendar_appointment ca ON ca.id = lch.calendar_appointment_id 
								WHERE ca.title IN ('NO SHOW RESCHEDULE')
								AND lch.disposition = 'Appointment Set'
								AND lch.date_created >= '".date('Y-m-01 00:00:00')."' 
								AND ca.date_created <= '".date('Y-m-t 23:59:59')."'
								AND lch.customer_id = '".$customer->id."'
								AND ls.skill_id = '".$customerSkill->skill_id."' 
							";
							
							
							$command = Yii::app()->db->createCommand($noShowMTDSql);
							$noShowMTD = $command->queryRow();
							
							$appointmentSetCount = $appointmentSetMTD['totalCount'] + $noShowMTD['totalCount'];
							
							if( $noShowMTD['totalCount'] > 3 )
							{
								$appointmentSetCount = $appointmentSetMTD['totalCount']-3;
							}
							else
							{
								$appointmentSetCount = $appointmentSetCount-$noShowMTD['totalCount'];
							}
						
							//get callable leads
							##NOTE: when updating this query, kindly also check the controllers/CronQueueViewerController - Line:203
							$remainingCallableCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								'condition' => '
									list.customer_id = :customer_id AND list.status = 1 
									AND t.type=1 and t.status=1 AND t.number_of_dials < (skill.max_dials * 3) 
									AND (recertify_date != "0000-00-00" AND recertify_date IS NOT NULL 
									AND NOW() <= recertify_date)
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							//update line 42 && 122 && 154, if updating this query
							$leadRecycleCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								'condition' => '
									list.customer_id = :customer_id 
									AND list.status = 1 
									AND t.type=1 
									AND (recycle_date != "0000-00-00" 
									AND recycle_date IS NOT NULL) 
									AND NOW() >= recycle_date 
									AND recycle_lead_call_history_id IS NOT NULL
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
							//update line 53 && 135 && 187, if updating this query
							$leadRecertifyCount = Lead::model()->count(array(
								'with' => array('list', 'list.skill'),
								'together' => true,
								// 'condition' => 'list.customer_id = :customer_id AND (recertify_date = "0000-00-00" || recertify_date IS NULL) AND NOW() >= recertify_date',
								'condition' => '
									list.customer_id = :customer_id 
									AND list.status = 1 
									AND t.type = 1
									AND t.status = 1
									AND (recertify_date = "0000-00-00" || recertify_date IS NULL || NOW() >= recertify_date)
									AND skill.id = :skill_id
								',
								'params' => array(
									':customer_id' => $customer->id,
									':skill_id' => $customerSkill->skill_id,
								),
							));
							
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
												$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
								
								$customerExtras = CustomerExtra::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract_id,
										':skill_id' => $customerSkill->skill_id,
										':year' => date('Y'),
										':month' => date('m'),
									),
								));
								
								if( $customerExtras )
								{
									foreach( $customerExtras as $customerExtra )
									{
										$totalLeads += $customerExtra->quantity;
									}
								}
							}
							else
							{
								if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
								{
									foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
									{
										$customerSkillLevelArray = $customerSkill->getCustomerSkillLevelArray();
										
										$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
										
										if( $customerSkillLevelArrayGroup != null )
										{
											if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
											{
												$totalLeads += ( $subsidyLevel['high'] * $customerSkillLevelArrayGroup->quantity );
											}
										}
									}
								}
								
								$customerExtras = CustomerExtra::model()->findAll(array(
									'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id AND status=1 AND year = :year AND month = :month',
									'params' => array(
										':customer_id' => $customerSkill->customer_id,
										':contract_id' => $customerSkill->contract_id,
										':skill_id' => $customerSkill->skill_id,
										':year' => date('Y'),
										':month' => date('m'),
									),
								));
								
								if( $customerExtras )
								{
									foreach( $customerExtras as $customerExtra )
									{
										$totalLeads += $customerExtra->quantity;
									}
								}
							}
							
							echo '<div class="row">';
							
								echo '<div class="col-sm-1">';
									
									if( $customerSkill->is_hold_for_billing == 1 )
									{
										echo '<span style="margin-top: 15px;" class="label label-danger label-lg arrowed-right">';
											echo  '<i class="fa fa-ban"></i> Active - Decline Hold';
										echo '</span>';
									}
									else
									{
										$skillStatus = 'Active';
										$skillIcon = 'check';
										$skillClassLabel = 'success';
										
										if( $customerSkill->is_contract_hold == 1 )
										{
											if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
											{
												if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
												{
													$skillStatus = 'Active - On Hold';
													$skillIcon = 'ban';
													$skillClassLabel = 'warning';
												}
											}
										}
										
										echo '<span style="margin-top: 15px;" class="label label-'.$skillClassLabel.' label-lg arrowed-right">';										
											echo '<i class="fa fa-'.$skillIcon.'"></i> ' . $skillStatus;
										echo '</span>';
									}
									
								echo '</div>';
								
								echo '<div class="col-sm-3">';
									echo '<div class="infobox infobox-blue" style="border:none;">';
										echo '<div class="infobox-icon">';
											
										echo '</div>';

										echo '<div class="infobox-data">';											
											echo '<div class="infobox-content" style="width:100%;">'.$customerSkill->skill->skill_name.'</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								echo '<div class="col-sm-2">';
									echo '<div class="infobox infobox-blue" style="border:none;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-calendar"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											echo '<span class="infobox-data-number">';	
												if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
												{
													echo $appointmentSetCount.'/'.$totalLeads; 
												}
												else
												{
													echo $appointmentSetCount;
												}
											echo '</span>';
											echo '<div class="infobox-content">Appointments Set</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								echo '<div class="col-sm-2">';
									echo '<div class="modal-recyle-module infobox infobox-green" style="border:none; cursor:pointer;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-recycle"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											// echo '<span class="infobox-data-number">'.($leadRecycleCount + $leadRecertifyCount).'</span>';
											// echo '<div class="infobox-content">Recycle Names</div>';
											echo '<span class="infobox-data-number">'.($leadRecertifyCount).'</span>';
											echo '<div class="infobox-content">Recertify Names</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
								
								echo '<div class="col-sm-2">';
									echo '<div class="infobox infobox-red" style="border:none;">';
										echo '<div class="infobox-icon">';
											echo '<i class="ace-icon fa fa-phone"></i>';
										echo '</div>';

										echo '<div class="infobox-data">';
											echo '<span class="infobox-data-number">'.$remainingCallableCount.'</span>';
											echo '<div class="infobox-content">Remaining Callable</div>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
							echo '</div>';
						}
					}
				}
				else
				{
					echo '<tr><td colspan="2">No assigned skills found.</td></tr>';
				}
			?>
		</div>
	</div>
	
</div>

<br />
<br />

<div class="page-header">
	<h1>Action Center 
		<span class="action-center-count">
			<?php 
				echo ( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ) > 0 ? '<span class="red">('.( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ).')</span>' : ''; 
			?>
		</span>
	</h1>
</div>

<div class="accordion-style1 panel-group" id="accordion">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-down bigger-110"></i>
					&nbsp;
					Schedule Conflict 
					<span class="schedule-conflict-count">
						<?php echo $scheduleConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$scheduleConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseOne" class="panel-collapse collapse in">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'scheduleConflictList',
						'dataProvider'=>$scheduleConflictDataProvider,
						'itemView'=>'_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-right bigger-110"></i>
					&nbsp;
					Location Conflict 
					<span class="location-conflict-count">
						<?php echo $locationConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$locationConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseTwo" class="panel-collapse collapse">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'locationConflictList',
						'dataProvider'=>$locationConflictDataProvider,
						'itemView'=>'_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>
</div>