<?php 
	$staffStep = 'active';
	$calendarStep = '';
	
	if( count($officeStaffs) > 0 )
	{
		$staffStep = 'complete';
		$calendarStep = 'active';
	}
	
	if( count($calendars) > 0 )
	{
		$calendarStep = 'complete';
	}
?>

	<div class="steps-wrapper">
		<ul class="steps">
			<li class="complete" data-step="1">
				<span class="step">1</span>
				<span class="title">Create Office</span>
			</li>

			<li class="add-staff-step <?php echo $staffStep; ?>" data-step="2">
				<span class="step">2</span>
				<span class="title">Add Staff</span>
			</li>

			<li class="add-calendar-step <?php echo $calendarStep; ?>" data-step="3">
				<span class="step">3</span>
				<span class="title">Create Calendar</span>
			</li>
		</ul>
		
		<div class="hr hr-18 hr-double dotted"></div>
	</div>

	
	<!-- START OF GET STARTED STAFF -->
	
	<div class="get-started-staff-wrapper">
		<div class="row">
			<div class="col-sm-12">
				<div class="office-staff-wrapper">
					<div class="col-sm-12">
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
											<a href="#officeStaffList<?php echo $office->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle" aria-expanded="false">
												<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
												&nbsp;Staff List
											</a>
										</h4>
									</div>

									<div id="officeStaffList<?php echo $office->id; ?>" class="panel-collapse" aria-expanded="true" style="">
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
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
																Add New Staff
															</a>
															
															&nbsp;&nbsp;&nbsp;&nbsp;
															
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-xs btn-success add-existing-staff-btn" style="border-radius:3px;">
																Add Existing Staff
															</a>
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
		
		<div class="row form-actions center">
			<?php 
				if( count($officeStaffs) > 0 )
				{
					echo '<button class="btn btn-success get-started-staff-next-btn">Next <i class="fa fa-arrow-right"></i></button>';
				}
				else
				{
					echo '<button class="btn btn-grey" style="cursor:not-allowed;">Next <i class="fa fa-arrow-right"></i></button>';
				}
			?>
		</div>
	</div>
	
	<!-- END OF GET STARTED STAFF -->
	
	
	
	<!-- START OF GET STARTED CALENDAR -->
	
	<div class="get-started-calendar-wrapper" style="display:none;">
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
									<a href="#officeCalendars<?php echo $office->id; ?>" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle" aria-expanded="false">
										<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="bigger-110 ace-icon fa fa-angle-right"></i>
										&nbsp;Calendars
									</a>
								</h4>
							</div>

							<div id="officeCalendars<?php echo $office->id; ?>" class="panel-collapse" aria-expanded="true" style="">
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
																echo CHtml::link('<i class="fa fa-search"></i> View', array('//calendar/index', 'calendar_id'=>$calendar->id, 'customer_id'=>$customer->id));
																
																echo '&nbsp;&nbsp;&nbsp;&nbsp;';
																
																echo CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$calendar->id, 'class'=>'edit-calendar-btn'));
																
																echo '&nbsp;&nbsp;&nbsp;&nbsp;';
															
																echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$calendar->id, 'class'=>'delete-calendar-btn', 'hasList'=>$hasList));
																
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
														if( count($officeStaffs) > 0 )
														{
														?>
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-primary btn-xs add-calendar-btn" style="border-radius:3px;">
																Add Calendar
															</a>
														<?php 
														}
														else
														{
														?>
															<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-grey btn-xs add-calendar-btn disabled" style="border-radius:3px;">
																<i class="fa fa-ban"></i>
																Add Calendar
															</a>
														<?php
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
		
		<div class="hr hr-18 hr-double dotted"></div>
		
		<div class="row form-actions center">
			<?php 
				if( count($calendars) > 0 )
				{
					echo '<button office_id="'.$office->id.'" class="btn btn-success get-started-calendar-next-btn">Next <i class="fa fa-arrow-right"></i></button>';
				}
				else
				{
					echo '<button class="btn btn-grey" style="cursor:not-allowed;">Next <i class="fa fa-arrow-right"></i></button>';
				}
			?>
		</div>
	</div>
	
	<!-- END OF GET STARTED CALENDAR -->
	