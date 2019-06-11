<?php 
	$this->pageTitle = 'Engagex | Sales Management | Enrollment Listing';
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); 
	
	$cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); 
	
	$cs->registerScript('select2js', '

		$(".select2").css("width","200px").select2({allowClear:true});

	', CClientScript::POS_END);
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){

			$(document).on("click", ".delete-customer", function(){
				
				var this_row = $(this).closest("tr");
				var id = $(this).prop("id");
				
				if( confirm("Are you sure you want to delete this?") )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + "/salesManagement/delete",
						type: "post",
						dataType: "json",
						data: {
							"ajax": 1,
							"id" : id		
						},
						success: function(r){
							
							if(r.status == "success")
							{
								this_row.fadeOut("slow", function() { $(this).remove(); });
							}
							else
							{
								alert(r.message);
							}
						},
					});
				}
			});
			
			$(document).on("click", ".edit-customer", function(){
				
				var this_row = $(this).closest("tr");
				var commission_column = this_row.find("td:last");
				
				var id = $(this).prop("id");
				var customer_id = $(this).attr("customer_id");
				var sales_rep_account_id = this_row.find("select").val();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/salesManagement/edit",
					type: "post",
					dataType: "json",
					data: {
						"ajax": 1,
						"id" : id,			
						"sales_rep_account_id" : sales_rep_account_id,		
						"customer_id" : customer_id,		
					},
					success: function(r){
						
						alert(r.message);
						
						commission_column.text(r.commission_rate);
					},
				});
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'enrollmentListing'
	));
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				Enrollment Listing
			</h1>
		</div>
	</div>
</div>

<div class="rows">
	<div class="col-sm-12">
		
		<table class="table table-bordered table-striped table-hover">
			
			<tr>
				<th>#</th>
				<th width="12%">Options</th>
				<th>Date/Time</th>
				<th>Customer Name</th>
				<th>Company</th>
				<th>Skill</th>
				<th>Contract</th>
				<th>Quantity</th>
				<th>Value</th>
				<th>Sales Agent</th>
				<th>Commission</th>
			</tr>
			
			<?php 
				if( $models )
				{
					$ctr = 1;
					
					foreach( $models as $model )
					{
						$totalLeads = 0;
						$contractedAmount = 0;
						
						$customer = Customer::model()->find(array(
							'condition' => 'id = :customer_id',
							'params' => array(
								':customer_id' => $model->customer_id,
							),
						));

						if( $customer )
						{
							$selectedSalesReps = array();
							$commissionRate = 0;
							
							$salesReps = CustomerSalesRep::model()->findAll(array(
								'condition' => 'customer_id = :customer_id',
								'params' => array(
									':customer_id' => $customer->id,
								),
							));
							
							if( $salesReps )
							{
								foreach( $salesReps as $salesRep )
								{
									$selectedSalesReps[] = $salesRep->sales_rep_account_id;
									
									$userMonthlyGoal = SalesAccountMonthlyGoal::model()->find(array(
										'condition' => 'account_id = :account_id',
										'params' => array(
											':account_id' => $salesRep->sales_rep_account_id,
										),
									));
									
									if( $userMonthlyGoal )
									{
										$userCommissionRate = str_replace('%', '', $userMonthlyGoal->commission_rate);

										$commissionRate = ($userCommissionRate / 100);
									}
								}
							}
							
							
							$customerSkill = CustomerSkill::model()->find(array(
								'condition' => 'customer_id = :customer_id AND status=1',
								'params' => array(
									':customer_id' => $customer->id,
								),
							));
							
							if( $customerSkill )
							{
								$contract = $customerSkill->contract;
								
								if( $contract )
								{
									if($contract->fulfillment_type != null )
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
															$totalLeads += ( $subsidyLevel['goal'] * $customerSkillLevelArrayGroup->quantity );
															
															$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
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
															
															$contractedAmount += ( $subsidyLevel['amount'] * $customerSkillLevelArrayGroup->quantity );
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
									}
									
									$dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
									$dateTime->setTimezone(new DateTimeZone('America/Denver'));	
									
									
									//updates the enrollment_date column on queue viewer for change log report enrollment listing
									// $customerQueue = CustomerQueueViewer::model()->find(array(
										// 'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND skill_id = :skill_id',
										// 'params' => array(
											// ':customer_id' => $customerSkill->customer_id,
											// ':contract_id' => $customerSkill->contract_id,
											// ':skill_id' => $customerSkill->skill_id,
										// ),
									// ));
									
									// if( $customerQueue ) 
									// {
										// $customerQueue->enrollment_date = $model->date_created;
										// $customerQueue->save(false);
									// }
									// else
									// {
										// echo '<div class="row hide">';
											// echo $customerSkill->customer->getFullName();
										// echo '</div>';
									// }
									
									echo '<tr>';
									
										echo '<td>'.$ctr.'</td>';
										
										echo '<td>';
											echo '<button class="btn btn-minier btn-danger delete-customer" id="'.$model->id.'"><i class="fa fa-times"></i> Delete</button>';
											echo '<button class="btn btn-minier btn-primary edit-customer" id="'.$model->id.'" customer_id="'.$customer->id.'" style="margin-left:5px;"><i class="fa fa-check"></i> Save</button>';
										echo '</td>';
										
										echo '<td>'.$dateTime->format('m/d/Y g:i A').'</td>';
										
										echo '<td>'.$customer->getFullName().'</td>';

										echo '<td>'.$customer->company->company_name.'</td>';
										echo '<td>'.$customerSkill->skill->skill_name.'</td>';
										echo '<td>'.$contract->contract_name.'</td>';
										echo '<td class="center">'.$totalLeads.'</td>';
										echo '<td class="center">$'.$contractedAmount.'</td>';
										
										if( $selectedSalesReps )
										{
											echo '<td>';

												echo CHtml::dropDownList('salesRepIds', $selectedSalesReps, AccountUser::listSalesAgents(), array('class'=>'select2','multiple'=>true) );

											echo '</td>';
											
											echo '<td class="center">';
	
												if( $commissionRate > 0 )
												{
													echo '$'.number_format( ($commissionRate * $contractedAmount) / count($selectedSalesReps), 2);
												}
												else
												{
													echo '$0.00';
												}
												
											echo '</td>';
										}
										else
										{
											echo '<td>';

												echo CHtml::dropDownList('salesRepIds', null, AccountUser::listSalesAgents(), array('class'=>'select2','multiple'=>true) ); 

											echo '</td>';
											
											echo '<td></td>';
										}
									
									echo '</tr>';
									
									$ctr++;
								}
							}
						}
					}
				}
			?>
			
		</table>
		
	</div>
</div>