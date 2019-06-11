<?php Yii::app()->clientScript->registerScript('portal-access-js','
	$("body").on("change","#'.CHtml::activeId( new CustomerOfficeStaff, 'is_portal_access').'", function(){
		
		if($(this).val() == 1)
			$("#portal-access-container").show();
		else
		{
			$("#portal-access-container").hide();
		}
	});
	
',CClientScript::POS_END); ?>
	
	<div class="row">
		<div class="col-sm-12">
			<div class="col-sm-6">
				<div class="row">
					<div class="col-sm-12">
						<div class="page-header">
							<h1><i class="fa fa-building fa-lg"></i></h1>
						</div>
					</div>
				</div>
				
				<div class="office-settings-wrapper">
					<div class="accordion accordion-style1 panel-group accordion-style2">
					
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title" style="position:relative;">
									<a href="#officeSettings<?php echo $office->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed" aria-expanded="false">
										<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
										&nbsp;Office Settings
									</a>
								</h4>
							</div>

							<div id="officeSettings<?php echo $office->id; ?>" class="panel-collapse collapse" aria-expanded="true" style="">
								<div class="panel-body">
									<?php 
										echo CHtml::hiddenField('officeId', $office->id, array('class'=>'update-office-id'));
										
										$this->renderPartial('/customerOffice/ajax_form', array(
											'model' => $office,
										));
									?>
								</div>
							</div>
						</div>
						
					</div>
				</div>
				
			</div>
			
			<div class="office-staff-wrapper">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<div class="page-header">
								<h1>
									<i class="fa fa-users fa-lg"></i>
								</h1>
							</div>
						</div>
					</div>
					
					<div class="office-staff-wrapper">
						<div class="accordion accordion-style1 panel-group accordion-style2">
						
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" style="position:relative;">
										<a href="#officeStaffList<?php echo $office->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed" aria-expanded="false">
											<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
											&nbsp;Staff List
										</a>
									</h4>
								</div>

								<div id="officeStaffList<?php echo $office->id; ?>" class="panel-collapse collapse" aria-expanded="true" style="">
									<div class="panel-body">
										
										<table class="table table-bordered table-condensed">
											<thead>
												<th>Name</th>
												<th width="25%" class="center">Options</th>
											</thead>
											<tbody>
												<?php 
													if($officeStaffs)
													{
														foreach( $officeStaffs as $officeStaff )
														{
															$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
																'condition' => 'staff_id = :staff_id',
																'params' => array(
																	':staff_id' => $officeStaff->id,
																),
															));
															
															echo '<tr>';
																echo '<td>'.$officeStaff->staff_name.'</td>';
																echo '<td class="center">';
																	echo CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$officeStaff->id, 'class'=>'edit-staff-btn'));
																	
																	echo '&nbsp;&nbsp;&nbsp;&nbsp;';
																	
																	if( $officeStaff->account_id != null )
																	{
																		echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$officeStaff->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
																	}
																	
																echo '</td>';
															echo '</tr>';
														}
														
													}
													else
													{
														echo '<tr><td colspan="2">No staff found.</td></tr>';
													}
												?>
												
												<tr>
													<td colspan="2" class="center">
														
														<?php if( Yii::app()->user->account->checkPermission('customer_offices_add_new_staff_button','visible') ){ ?>
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
																Add New Staff
															</a>
														<?php } ?>
														
														&nbsp;&nbsp;&nbsp;&nbsp;
														
														<?php if( Yii::app()->user->account->checkPermission('customer_offices_add_existing_staff_button','visible') ){ ?>
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-xs btn-success add-existing-staff-btn" style="border-radius:3px;">
																Add Existing Staff
															</a>
														<?php } ?>
													</td>
												</tr>
											</thead>
										</table>
										
									</div>
								</div>
							</div>													
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="hr hr-18 hr-double dotted"></div>

	<div class="row">
		<div class="col-sm-12">
			<div class="page-header">
				<h1><i class="fa fa-calendar fa-lg"></i></h1>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<div class="office-calendar-wrapper">
				<div class="accordion accordion-style1 panel-group accordion-style2">
				
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title" style="position:relative;">
								<a href="#officeCalendars<?php echo $office->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed" aria-expanded="false">
									<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
									&nbsp;Calendars
								</a>
							</h4>
						</div>

						<div id="officeCalendars<?php echo $office->id; ?>" class="panel-collapse collapse" aria-expanded="true" style="">
							<div class="panel-body">
							
								<table class="table table-bordered table-condensed">
									<thead>
										<th>Name</th>
										<th class="center" width="20%">Options</th>
									</thead>
									<tbody>
										<?php 
											if($calendars)
											{
												foreach( $calendars as $calendar )
												{
													$hasList = Lists::model()->count(array(
														'condition' => 'calendar_id = :calendar_id AND status=1',
														'params' => array(
															':calendar_id' => $calendar->id
														),
													));
													
													echo '<tr>';
														echo '<td>'.$calendar->name.'</td>';
														echo '<td class="center">';
														
															if( Yii::app()->user->account->checkPermission('customer_offices_calendar_view_link','visible') )
															{
																echo CHtml::link('<i class="fa fa-search"></i> View', array('//calendar/index', 'calendar_id'=>$calendar->id, 'customer_id'=>$customer->id));
																
																echo '&nbsp;&nbsp;&nbsp;&nbsp;';
															}
															
															if( Yii::app()->user->account->checkPermission('customer_offices_calendar_edit_link','visible') )
															{
																echo CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$calendar->id, 'class'=>'edit-calendar-btn'));
																
																echo '&nbsp;&nbsp;&nbsp;&nbsp;';
															}
															
															if( Yii::app()->user->account->checkPermission('customer_offices_calendar_delete_link','visible') )
															{
																echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$calendar->id, 'class'=>'delete-calendar-btn', 'hasList'=>$hasList));
															}
															
														echo '</td>';
													echo '</tr>';
												}
												
											}
											else
											{
												echo '<tr><td colspan="2">No calendar found.</td></tr>';
											}
										?>
										
										<tr>
											<td colspan="2" class="center">
												<?php 
													if( count($officeStaffs) > 0 && Yii::app()->user->account->checkPermission('customer_offices_add_calendar_button','visible') )
													{
													?>
														<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-primary btn-xs add-calendar-btn" style="border-radius:3px;">
															Add Calendar
														</a>
													<?php 
													}
													else
													{
														if( Yii::app()->user->account->checkPermission('customer_offices_add_calendar_button','visible') )
														{
													?>
														<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-grey btn-xs add-calendar-btn disabled" style="border-radius:3px;">
															<i class="fa fa-ban"></i>
															Add Calendar
														</a>
													<?php
														}													
													}													
												?>
											</td>
										</tr>
									</thead>
								</table>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>