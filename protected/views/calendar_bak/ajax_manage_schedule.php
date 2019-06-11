<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-calendar"></i> Manage Schedule</h4>
			</div>
			<div class="modal-body">
				<div class="tabbable">
					<ul id="myTab4" class="nav nav-tabs padding-12 tab-color-blue background-blue">
						<li class="active">
							<a href="#home4" data-toggle="tab">Schedule</a>
						</li>

						<li>
							<a href="#dropdown14" data-toggle="tab">US Holidays</a>
						</li>
					</ul>

					<div class="tab-content">
						<div class="tab-pane in active" id="home4">
							<div class="row">		
								<div class="col-xs-12">
									<form id="no-margin">
									
										<?php echo CHtml::hiddenField('CalendarCustomSchedule[calendar_id]', $model->id); ?>
										
										<table class="table table-bordered table-striped table-condensed">
											<?php 
												$days = array(
													'Monday',
													'Tuesday',
													'Wednesday',
													'Thursday',
													'Friday',
													'Saturday',
													'Sunday',
												);
												
												echo '<tr>';
												
													foreach( $days as $day )
													{
														echo '<th class="text-center">'.$day.'</th>';
													}
												
												echo '</tr>';
												
												foreach (Calendar::createTimeRange($model->appointment_start_time, $model->appointment_end_time, $model->appointment_length) as $time) 
												{
													$time = date('g:i A', $time);
													
													echo '<tr>';
														foreach( $days as $day )
														{	
															// if( in_array($time, array('10:00 AM', '2:00 PM', '4:00 PM')) && !in_array($day, array('Saturday', 'Sunday')) )
															// {
																// $checked = 'checked';
															// }
															// else
															// {
																// $checked = '';
															// }
															
															$existingCustomSchedule = CalendarCustomSchedule::model()->find(array(
																'condition' => 'calendar_id = :calendar_id AND day = :day AND time = :time',
																'params' => array(
																	':calendar_id' => $model->id,
																	':day' => $day,
																	':time' => $time,
																),
															));
															
															if($existingCustomSchedule)
															{
																$checked = 'checked';
															}
															else
															{
																$checked = '';
															}
															
															echo '<td>';
																echo '<input type="checkbox" class="ace" name="CalendarAppointmentSchedule['.$day.'][]" value="'.$time.'" '.$checked.'><span class="lbl">&nbsp;'.$time.'</span>';
															echo '</td>';
														}
													echo '</tr>';
												}
											?>
										</table>
										
										<div class="center">
											<button type="button" class="btn btn-xs hide" data-action="default">Apply Default</button>
											<button type="button" class="btn btn-xs btn-info" data-action="custom"><i class="ace-icon fa fa-check"></i> Apply</button>
										</div>
										
									</form>
								</div>
							</div>
						</div>

						<div class="tab-pane" id="dropdown14">
							<form id="holidays" class="no-margin">

								<?php echo CHtml::hiddenField('CalendarCustomSchedule[calendar_id]', $model->id); ?>

								<table class="table table-striped table-hover table-bordered compress">
									<?php
										$holidays = new US_Federal_Holidays;
										
										foreach ($holidays->get_list() as $key => $holiday)
										{
											$existingHolidaySettings = CalendarHoliday::model()->find(array(
												'condition' => 'calendar_id = :calendar_id AND name = :name',
												'params' => array(
													':calendar_id' => $model->id,
													':name' => $holiday["name"],
												),
											));
											
											if($existingHolidaySettings)
											{
												$checked = 'checked';
											}
											else
											{
												$checked = '';
											}
											
											echo "<tr>";
											
												echo "<td>".$holiday["name"]."</td>";
												echo "<td>".date("F j, Y", $holiday["timestamp"])."</td>";
												echo '<td><input type="checkbox" class="ace" name="holidays[]" value="'.$key.'" '.$checked.'><span class="lbl">&nbsp; Enable</span></td>';
											
											echo "</tr>";
										}
									?>
								</table>

								<div class="center">
									<button type="button" class="btn btn-xs btn-info" data-action="applyHolidays"><i class="ace-icon fa fa-check"></i> Apply</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer hide">
				
			</div>
		</div>
	</div>
</div>