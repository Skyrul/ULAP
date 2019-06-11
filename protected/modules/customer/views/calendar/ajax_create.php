<div class="modal fade">
	<div class="modal-dialog" style="width:80%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-calendar"></i> Add Calendar</h4>
			</div>
			
			<div class="modal-body">
				<?php 
					$this->renderPartial('_form', array(
						'calendar'=>$calendar,
						'calendarStaffAssignment'=>$calendarStaffAssignment,
						'models'=>$models,
						'office'=>$office,
					));
				?>
			</div>
			
			<div class="modal-footer center">
				<button class="btn btn-sm btn-primary" data-action="save">
					<i class="ace-icon fa fa-check"></i>
					Save
				</button>
			</div>
		</div>
	</div>
</div>