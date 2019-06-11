<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-user"></i> Reassign Calendars and Delete Staff</h4>
			</div>
			
			<div class="modal-body">
				<?php 
					if( $calendars )
					{
						echo '<p><b>'.$model->staff_name.'</b> is assigned to the following calendars: </p>';
					?>
						<form>
							<table class="table table-bordered table-hover">
								<thead>
									<th>Calendar Name</th>
									<th class="center">Available Staffs</th>
								</thead>
								
								<?php
									foreach( $calendars as $calendar )
									{
										echo '<tr>';
											echo '<td>'.$calendar->calendar->name.'</td>';
											
											echo '<td class="center">';
											
												if( $staffOptions )
												{
													echo '<select name="reassign_calendar['.$calendar->calendar_id.']">';
													
														foreach($staffOptions as $staffOption)
														{
															echo '<option value="'.$staffOption->id.'">'.$staffOption->staff_name.'</option>';
														}
														
													echo '<select>';
												}
												else
												{
													echo 'No available staff found.';
												}
												
											echo '</td>';
											
										echo '</tr>';
									}
								?>									
							</table>
							
							<div class="space-12"></div>
							
							<div class="center">
								<?php 
									if( $staffOptions )
									{
										echo '<button type="button" class="btn btn-sm btn-info" data-action="save">Save and Delete Staff</button>';
									}
									else
									{
										echo '<button type="button" class="btn btn-sm btn-grey disabled">Save and Delete Staff</button>';
									}
								?>
							</div>
						</form>
					<?php	
					}
					else
					{
						echo 'No assigned calendar found.';
					}
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>