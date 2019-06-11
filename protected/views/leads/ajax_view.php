<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><?php echo $model->first_name.' '.$model->last_name; ?></h4>
			</div>
			<div class="modal-body no-padding">
				<div class="tabbable">
					<ul id="myTab4" class="nav nav-tabs padding-12 tab-color-blue background-blue">
						<li class="active">
							<a href="#info" data-toggle="tab">Lead Info</a>
						</li>

						<li>
							<a href="#history" data-toggle="tab">Call History</a>
						</li>
					</ul>

					<div class="tab-content">
						<div class="tab-pane in active" id="info">
							<div class="row">		
								<div class="col-xs-12">
								
									<?php $form=$this->beginWidget('CActiveForm', array(
										'enableAjaxValidation'=>false,
										'htmlOptions' => array(
											'class' => 'form-horizontal',
										),
									)); ?>
									
									<?php echo $form->hiddenField($model, 'id'); ?>
									
									<div class="profile-user-info profile-user-info-striped">
										<?php 
											$fields = array(
												'list_id' => 'List Name',
												'office_phone_number' => 'Office Phone',
												'mobile_phone_number' => 'Mobile Phone',
												'home_phone_number' => 'Home Phone',
												'first_name' => 'First Name',
												'last_name' => 'Last Name',
												'partner_first_name' => 'Partner\'s First name',
												'partner_last_name' => 'Partner\'s Last name',
												'email_address' => 'Email',
												'address' => 'Address',
												'address2' => 'Address 2',
												'city' => 'City',
												'state' => 'State',
												'zip_code' => 'Zip Code',
												'language' => 'Language',
												'custom_date' => 'Custom Date',
												'number_of_dials' => 'Number of dials',
												'status' => 'Status',
											);
											
											foreach( $fields as $dbField => $fieldLabel )
											{
												$inputClass = '';
												
												if( $dbField == 'zip_code' )
												{
													$inputClass = 'input-mask-zip';
												}
												
												if( $dbField == 'custom_date' )
												{
													$inputClass = 'date-picker';
												}
												
												if( in_array($dbField, array('office_phone_number', 'mobile_phone_number', 'home_phone_number')) )
												{
													$inputClass = 'input-mask-phone';
												}
												
											?>
											
												<div class="profile-info-row">
													<div class="profile-info-name"> <?php echo $fieldLabel; ?> </div>

													<div class="profile-info-value">
														<?php 
															if( $dbField == 'status')
															{
																echo $form->dropDownList($model, 'status', $model::statusOptions(), array('style'=>'width:auto;')); 
															}
															elseif( $dbField == 'list_id')
															{
																echo $form->dropDownList($model, 'list_id', Lists::items($model->list->customer_id), array('style'=>'width:auto;')); 
															}
															elseif( $dbField == 'language')
															{
																echo $form->dropDownList($model, 'language', Lists::getLanguageOptions(), array('style'=>'width:auto;')); 
															}
															else
															{	
																echo $form->textField($model, $dbField, array('class' => $inputClass . ' col-sm-12'));
															}
														?>
													</div>
												</div>
											
											<?php
											}
										?>
									</div>
									
									<div class="form-actions text-center">
										<button type="button" class="btn btn-xs btn-primary" data-action="save">Save <i class="fa fa-arrow-right"></i></button>
									</div>

								<?php $this->endWidget(); ?>
	
								</div>
							</div>
						</div>

						<div class="tab-pane" id="history">
							<form id="no-margin">
							
								<table class="table table-striped table-hover table-bordered compress">
									<thead>
										<tr>
											<th>Date/Time (lead local)</th>
											<th>Appointment Date/Time</th>
											<th>Phone Number</th>
											<th>Agent Name</th>
											<th>Disposition</th>
											<th>Dial #</th>
											<th>Recording Link</th>
										</tr>
									</thead>
									<?php
										if( $leadHistories )
										{
											foreach( $leadHistories as $leadHistory )
											{
											?>
												<tr>
													<td><?php echo date('m/d/Y G i:a', strtotime($leadHistory->date_created)); ?></td>
													<td><?php echo date('m/d/Y G i:a', strtotime($leadHistory->appointment_date)); ?></td>
													<td><?php echo $leadHistory->lead_phone_number != '' ? "(".substr($leadHistory->lead_phone_number, 0, 3).") ".substr($leadHistory->lead_phone_number, 3, 3)."-".substr($leadHistory->lead_phone_number,6) : ''; ?></td>
													<td><?php echo $leadHistory->agent_name; ?></td>
													<td><?php echo $leadHistory->disposition; ?></td>
													<td><?php echo $leadHistory->dial_number; ?></td>
													<td></td>
												</tr>
											<?php
											}
										}
										else
										{
											echo '<tr><td colspan="6"></td></tr>';
										}
									?>
								</table>

							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>