<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-calendar"></i> Calendar Action Form - <?php echo $currentDateSelected; ?></h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<?php echo $form->hiddenField($model, 'calendar_id'); ?>
					<?php echo $form->hiddenField($model, 'lead_id'); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Action </div>

							<div class="profile-info-value">
								<?php 
									if( $viewer == 'agent' )
									{
										$actionOptions = array('SCHEDULE CONFLICT' => 'SCHEDULE CONFLICT');
									}
									else
									{
										$actionOptions = array('AVAILABLE'=>'INSERT AVAILABLE', 'INSERT APPOINTMENT'=>'INSERT APPOINTMENT');
									}
									
									echo $form->dropDownList($model, 'title', $actionOptions, array('class'=>'form-control', 'style'=>'width:auto;')); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row lead-field-container" style="display:none;">
							<div class="profile-info-name"> Lead </div>

							<div class="profile-info-value">
								<div class="col-sm-9">
									<div class="row">
										<?php echo $form->dropDownList($model, 'lead_id', Lead::items2($model->calendar->customer_id), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
									</div>
								</div>
								
								<div class="col-sm-3">
									<?php echo CHtml::link('Add New Lead', array('/customer/leads', 'customer_id'=>$model->calendar->customer_id), array('class'=>'add-new-lead-link', 'target'=>'_blank', 'style'=>'line-height:35px; display:none;')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Start Time </div>

							<div class="profile-info-value">
								<?php echo $form->hiddenField($model, 'start_date_time'); ?>
									
								<div class="dropdown">
									<a href="" data-toggle="dropdown" class="btn btn-xs btn-white">
										SELECT <span class="fa fa-caret-down"></span>
									</a>
									<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
										<?php 
											// echo '<pre>';
												// print_r($calendar->timeOptions());
											// echo '</pre>';
											
											if( $calendar->timeOptions() )
											{
												foreach( $calendar->timeOptions() as $value => $label )
												{
												?>
													<li class="dropdown-submenu">
														<a tabindex="-1" href="javascript:void(0);" class="start-time-option" value="<?php echo $value; ?>" label="<?php echo $label; ?>"><?php echo $label; ?></a>
														
														<?php 
															if( !strpos($label, '30') ) 
															{
																$parentValue = explode(':', $value);
																$parentValueHours = $parentValue[0];
																$parentValueMinutes = $parentValue[1];
																$parentValueSeconds = $parentValue[2];
																
																$parentLabelHours = explode(':', $label);
																$parentLabelHours = $parentLabelHours[0];
																
																$parentLabelMeridian = explode(' ', $label);
																$parentLabelMeridian = $parentLabelMeridian[1];
																
															?>
																<ul class="dropdown-menu">
																	<li>
																		<div tabindex="-1">
																			<?php 
																				foreach( array(':15', ':30', ':45' ) as $subTime )
																				{
																					$subTimeValue = $parentValueHours.$subTime.':'.$parentValueSeconds;
																					$subTimeLabel = $parentLabelHours.$subTime.' '.$parentLabelMeridian;
																				?>
																					<span class="start-time-option" value="<?php echo $subTimeValue; ?>" label="<?php echo $subTimeLabel; ?>">
																						<?php echo $subTime; ?>
																					</span>
																				<?php																				
																				}
																			?>
																		</div>
																	</li>
																</ul>
															<?php 
															}
														?>
													</li>
													
												<?php
												}
											}
										?>									
									</ul>
								</div>
								
								<?php 
									// echo $form->dropDownList($model, 'start_date_time', $calendar->timeOptions(), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); 
								?>
								
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> End Time </div>

							<div class="profile-info-value">
								<?php echo $form->hiddenField($model, 'end_date_time'); ?>
									
								<div class="dropdown">
									<a href="" data-toggle="dropdown" class="btn btn-xs btn-white">
										SELECT <span class="fa fa-caret-down"></span>
									</a>
									<ul class="dropdown-menu multi-level end-date-dropdown" role="menu" aria-labelledby="dropdownMenu">
										<?php 
											// echo '<pre>';
												// print_r($calendar->timeOptions());
											// echo '</pre>';
											
											if( $calendar->timeOptions() )
											{
												foreach( $calendar->timeOptions() as $value => $label )
												{
												?>
													<li class="dropdown-submenu">
														<a tabindex="-1" href="javascript:void(0);" class="end-time-option" value="<?php echo $value; ?>" label="<?php echo $label; ?>"><?php echo $label; ?></a>
														
														<?php 
															if( !strpos($label, '30') ) 
															{
																$parentValue = explode(':', $value);
																$parentValueHours = $parentValue[0];
																$parentValueMinutes = $parentValue[1];
																$parentValueSeconds = $parentValue[2];
																
																$parentLabelHours = explode(':', $label);
																$parentLabelHours = $parentLabelHours[0];
																
																$parentLabelMeridian = explode(' ', $label);
																$parentLabelMeridian = $parentLabelMeridian[1];
																
															?>
																<ul class="dropdown-menu">
																	<li>
																		<div tabindex="-1">
																			<?php 
																				foreach( array(':15', ':30', ':45' ) as $subTime )
																				{
																					$subTimeValue = $parentValueHours.$subTime.':'.$parentValueSeconds;
																					$subTimeLabel = $parentLabelHours.$subTime.' '.$parentLabelMeridian;
																				?>
																					<span class="end-time-option" value="<?php echo $subTimeValue; ?>" label="<?php echo $subTimeLabel; ?>">
																						<?php echo $subTime; ?>
																					</span>
																				<?php																				
																				}
																			?>
																		</div>
																	</li>
																</ul>
															<?php 
															}
														?>
													</li>
													
												<?php
												}
											}
										?>									
									</ul>
								</div>
								
								<?php 
									// echo $form->dropDownList($model, 'end_date_time', $calendar->timeOptions(), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); 
								?>
							</div>
						</div>
						
						<?php /*<div class="profile-info-row details-field-container">
							<div class="profile-info-name"> Details </div>

							<div class="profile-info-value">
								<?php echo $form->textArea($model, 'details', array('class'=>'col-xs-12')); ?>
							</div>
						</div>*/?>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name"> Agent Notes </div>

							<div class="profile-info-value">
								<?php 
									echo $form->textArea($model, 'agent_notes', array('class'=>'col-xs-12', 'disabled'=>$viewer == 'agent' ? false : true)); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name"> Customer Notes </div>

							<div class="profile-info-value">
								<?php 
									echo $form->textArea($model, 'customer_notes', array('class'=>'col-xs-12', 'disabled'=>$viewer == 'customer' ? false : true)); 
								?>
							</div>
						</div>
						
						<div class="profile-info-row location-field-container">
							<div class="profile-info-name"> Location </div>

							<div class="profile-info-value">								
								<?php echo $form->dropDownList($model, 'location', $calendar->locationOptions('all'), array('class'=>'form-control', 'prompt'=>' - Select -', 'style'=>'width:auto;')); ?>
							</div>
						</div>
					</div>
					
					<?php /*<div class="space-12"></div>
					
					<div class="row-fluid clearfix">
						<label>Notes: </label>
						<?php echo $form->textArea($model, 'customer_notes', array('class'=>'col-xs-12')); ?>
					</div>*/?>
					
					<div class="space-12"></div>
					
					<div class="center">
						<button type="button" class="btn btn-sm btn-info" data-action="save" existingEvent="<?php echo $existingEvent; ?>">Save</button>
					</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>