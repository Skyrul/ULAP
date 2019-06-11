<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<?php echo CHtml::beginForm(CHtml::normalizeUrl(array('leadSearch')), 'get', array('id'=>'lead-filter-form')); ?>
			
				<div class="col-sm-12">
					<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; margin-left:12px;">
						<span class="input-icon">
							<input type="text" autocomplete="off" class="nav-search-input lead-search-input" placeholder="Search Leads..." style="width:300px;">
							<i class="ace-icon fa fa-search nav-search-icon"></i>
						</span>
						
						<button class="btn btn-minier btn-info lead-search-submit">Search</button>
					</div>
				</div>
				
			<?php echo CHtml::endForm(); ?>
		</div>
		
		<div class="space-6"></div>
		
		<table id="leadSearchTable" class="table table-hover table-striped table-bordered table-condensed">
			<thead>
				<th>Name</th>
				<th>Home</th>
				<th>Mobile</th>
				<th>Office</th>
				<th>Customer Name</th>
				<th>Calling Time</th>
				<th>Company</th>
				<th>Skill</th>
				<th width="15%">Options</th>
			</thead>

			<tbody>
				<?php
					/*
					$models = Lead::model()->findAll(array(
						'with' => 'list',
						'condition' => 't.type=1 AND t.status !=4 AND list.status=1',
						'limit' => 25,
					));
					
					if( $models )
					{
						foreach( $models as $model )
						{
							$skillsTxt = '';

							if( $model->list )
							{
								$customerSkill = CustomerSkill::model()->find(array(
									'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
									'params' => array(
										':customer_id' => $model->list->customer_id,
										':skill_id' => $model->list->skill_id,
									),
								));
								
								// if( $customerSkills )
								// {
									// foreach( $customerSkills as $customerSkill )
									// {
										// $skillsTxt .= $customerSkill->skill->skill_name.', ';
									// }
									
									// $skillsTxt = rtrim($skillsTxt, ', ');
								// }
							}
						?>
							<tr>
								<td><?php echo $model->first_name.' '.$model->last_name; ?></td>
								
								<td><?php echo !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : ''; ?></td>
								
								<td><?php echo !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : ''; ?></td>
								
								<td><?php echo !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : ''; ?></td>
								
								<td><?php echo isset($model->list) ? $model->list->customer->firstname.' '.$model->list->customer->lastname : ''; ?></td>
				
								<td><?php $callingTime =  $this->getLeadCallingTime($model); ?></td>
								
								<td><?php echo isset($model->list) ? $model->list->customer->company->company_name : ''; ?></td>
								
								<td>
									<?php
										if( $customerSkill && isset($customerSkill->skill) )
										{
											echo $customerSkill->skill->skill_name;
										}
									?>
								</td>
								
								<td class="center">
									<select style="width:103px;">
										<option value="1" selected>Contact</option>
										<?php 
											if( $customerSkill )
											{
												$childSkills = CustomerSkillChild::model()->findAll(array(
													'with' => 'skillChild',
													'condition' => 't.customer_skill_id = :customer_skill_id AND t.is_enabled=1 AND skillChild.is_deleted=0 AND skillChild.type IN (1,2)',
													'params' => array(
														':customer_skill_id' => $customerSkill->id,
													)
												));
												
												if( $childSkills )
												{
													foreach( $childSkills as $childSkill )
													{
														if( isset($childSkill->skillChild) )
														{
															if( $childSkill->skillChild->type == SkillChild::TYPE_CONFIRM )
															{
																echo '<option value="3">Confirm</option>';
															}
															
															if( $childSkill->skillChild->type == SkillChild::TYPE_RESCHEDULE )
															{
																echo '<option value="6">Reschedule</option>';
															}
														}
													}
												}
											}
										?>
									</select>
									
									<?php echo CHtml::link('Load <i class="fa fa-arrow-right"></i>', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-info btn-minier load-lead-to-hopper', 'data-calling-time'=> $callingTime)); ?>
								</td>	
							</tr>
						<?php
						}
					}
					else
					{
					?>
						<tr><td colspan="2">No models found.</td></tr>
					<?php
					}
					*/
				?>
				
				<tr><td colspan="9">Search a Lead...</td></tr>
			</tbody>

		</table>
	</div>
</div>