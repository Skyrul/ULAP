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
													echo CHtml::link('<i class="fa fa-edit"></i> Edit', array('customerOfficeStaff/update', 'id'=>$officeStaff->id));
													
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

									</td>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
