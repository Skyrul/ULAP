<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-calendar"></i> 
					You have the following appointments scheduled during this blackout what do you want to do with them?
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
				<?php echo CHtml::hiddenField('formAction', $formAction); ?>
				<?php echo CHtml::hiddenField('calendar_id', $calendar_id); ?>
				<?php echo CHtml::hiddenField('appointment_id', $appointment_id); ?>
				<?php echo CHtml::hiddenField('start_date', $start_date); ?>
				<?php echo CHtml::hiddenField('end_date', $end_date); ?>
				<?php echo CHtml::hiddenField('submitBlackoutForm', 1); ?>
				
				<?php 
					if( $existingRecords )
					{
						echo '<table class="table table-striped table-hover table-condensed">';

							echo '<tr>';
								echo '<th>Lead Name</th>';
								echo '<th>Appointment Date/Time</th>';
								echo '<th class="center">Options</th>';
							echo '</tr>';
								
							foreach( $existingRecords as $existingRecord )
							{
								echo '<tr>';
								
									echo '<td>'.$existingRecord->lead->first_name.' '.$existingRecord->lead->last_name.'</td>';
								
									echo '<td>'.date('m/d/Y g:i A', strtotime($existingRecord->start_date)).'</td>';
									
									//'<option value="2">Cancel Appointment</option>';
									echo '
										<td class="center">
											<select name="existingRecordOptions['.$existingRecord->id.']">
												<option value="1" selected>Keep Appointment</option>
												<option value="3">Reschedule Appointment</option>
											</select>
										</td>';
								
								echo '</tr>';
							}
						
						echo '</table>';
					}
				?>
				
				<div class="space-12"></div>
					
				<div class="center">
					<button type="button" class="btn btn-sm btn-info" data-action="save">Save</button>
				</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>