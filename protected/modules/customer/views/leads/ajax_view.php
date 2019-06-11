<div class="modal fade">
	<div class="modal-dialog" style="width:60%;">
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
						
						<li>
							<a href="#lead-update-history" data-toggle="tab">Update History</a>
						</li>
						
						<li>
							<a href="#data-fields" data-toggle="tab">Data Fields</a>
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
										
										
										<div class="profile-info-row">
											<div class="profile-info-name"> <?php echo $model->getAttributeLabel('recycle_date'); ?> </div>
											<div class="profile-info-value">
												<?php if(!empty($model->recycle_lead_call_history_disposition_id)){ ?>
													<?php echo $form->textField($model, 'recycle_date', array('class' =>'col-sm-12','disabled'=>true)); ?>
													<?php //echo $form->textField($model, 'recycle_date', array('class' =>'col-sm-12')); ?>
												<?php }else{ ?>
													No disposition set.
												<?php } ?>
											</div>
										</div>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> <?php echo $model->getAttributeLabel('recertify_date'); ?> </div>
											<div class="profile-info-value">
												<?php echo $form->textField($model, 'recertify_date', array('class' => ' col-sm-12','disabled'=>true)); ?>
												<?php //echo $form->textField($model, 'recertify_date', array('class' => ' col-sm-12')); ?>
											</div>
										</div>
										
										<?php if( isset($model->list->skill) && $model->list->skill->enable_specific_date_calling == 1 ): ?>
										
										<div class="profile-info-row">
											<div class="profile-info-name"> Date to Call </div>
											<div class="profile-info-value">
												<?php echo $form->textField($model, 'specific_date', array('class' => ' col-sm-12','disabled'=>true)); ?>
											</div>
										</div>
										
										<?php endif; ?>
										
									</div>
									
									<div class="form-actions text-center">
										<button type="button" class="btn btn-xs btn-primary" data-action="save">Save <i class="fa fa-arrow-right"></i></button>
									</div>

								<?php $this->endWidget(); ?>
	
								</div>
							</div>
						</div>

						<div class="tab-pane" id="history">

							<div class="lead-history-table-wrapper" style="height:300px; overflow-y:auto;">
								<?php 
									echo $this->renderPartial('_lead_history_table', array('leadHistories'=>$leadHistories)); 
								?>
							</div>
							
							<form id="leadHistoryForm">
								
								<input type="hidden" name="LeadHistory[lead_id]" value="<?php echo $model->id; ?>">
								<input type="hidden" name="LeadHistory[lead_name]" value="<?php echo $model->first_name.' '.$model->last_name; ?>">
								<input type="hidden" name="LeadHistory[lead_phone_number]" value="<?php echo $model->office_phone_number; ?>">
								<input type="hidden" name="LeadHistory[type]" value="1">
							
								<div class="form-actions clearfix">
									<div class="row-fluid clearfix">							
										<textarea class="col-xs-12" id="LeadHistory_note" name="LeadHistory[note]"></textarea>
									</div>

									<div class="space-6"></div>

									<button class="btn btn-sm btn-info no-radius pull-right lead-history-submit-btn" type="button">
										SUBMIT NOTE
									</button>
								</div>
							</form>
						</div>
						
						<div class="tab-pane" id="lead-update-history">
							<div class="lead-udpdate-history-table-wrapper" style="height:300px; overflow-y:auto;">
								<?php 
									echo $this->renderPartial('_lead_update_history_table', array('leadUpdateHistories'=>$leadUpdateHistories)); 
								?>
							</div>
						</div>
						
						<div class="tab-pane" id="data-fields">
														
							<?php if( $listsArray ): ?>
								<div class="row-fluid">
									<div class="col-sm-12 data-tab-dropdown-container">
										Previous List Data Points: <?php echo CHtml::dropDownList('dataTabListId', $model->list_id, $listsArray, array('lead_id'=>$model->id, 'class'=>'data-tab-dropdown', 'style'=>'width:auto;')); ?>
									</div>
								</div>
							<?php else: ?>
							<div class="row-fluid">
								<div class="col-sm-12">
									No Previous List Data Points Found: 
									<select disabled>
										<option selected>- Select -</option>
									</select>
								</div>
							</div>
							
							<?php endif; ?>
							
							<div class="space-12"></div>
							<div class="space-12"></div>
							<div class="space-12"></div>
								
							<div class="profile-user-info profile-user-info-striped data-fields-tab">
								<?php 
									$listCustomDatas = ListCustomData::model()->findAll(array(
										'condition' => 'list_id = :list_id AND status=1',
										'params' => array(
											':list_id' => $model->list_id,
										),
										'order' => 'ordering ASC',
									));

									if( $listCustomDatas )
									{
										foreach( $listCustomDatas as $listCustomData )
										{
											$leadCustomData = LeadCustomData::model()->find(array(
												'condition' => 'lead_id = :lead_id AND list_id = :list_id AND field_name = :field_name',
												'params' => array(
													':lead_id' => $model->id,
													':list_id' => $model->list_id,
													':field_name' => $listCustomData->original_name
												),
											));
											
											if( $leadCustomData )
											{
											?>
												<div class="profile-info-row">
													<div class="profile-info-name" style="width:200px;"> <?php echo $listCustomData->custom_name; ?> </div>
													<div class="profile-info-value">
														<?php echo $leadCustomData->value; ?>
													</div>
												</div>
											<?php
											}
										}
									}
									else
									{
										echo '<tr><td colspan="6">No custom fields found.</td></tr>';
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>