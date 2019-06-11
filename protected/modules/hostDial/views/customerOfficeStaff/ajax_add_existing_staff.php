<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-user"></i> Add Existing Staff</h4>
			</div>
			
			<div class="modal-body">
				<?php 
					if( $staffOptions )
					{
					?>
						<form>
							<table class="table table-bordered table-hover">
								<thead>
									<th style="width: 7%;" class="center"></th>
									<th>Office Name</th>
									<th>Available Staffs</th>
								</thead>
								<?php
									foreach( $staffOptions as $staffOption )
									{
										echo '<tr>';
											echo '<td class="center"><label><input type="checkbox" name="addExistingStaff['.$staffOption->id.']" class="ace"> <span class="lbl">&nbsp;</span></label></td>';
											echo '<td>'.$staffOption->customerOffice->office_name.'</td>';
											echo '<td>'.$staffOption->staff_name.'</td>';
										echo '</tr>';
									}
								?>
							</table>
							
							<div class="space-12"></div>
							
							<div class="center">
								<?php 
									if( $staffOptions )
									{
										echo '<button type="button" class="btn btn-sm btn-info" data-action="save">Add Selected Staff</button>';
									}
									else
									{
										echo '<button type="button" class="btn btn-sm btn-grey disabled">Add Selected Staff</button>';
									}
								?>
							</div>
						</form>
					<?php
					}
					else
					{
						echo 'No available staff found.';
					}
				?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>