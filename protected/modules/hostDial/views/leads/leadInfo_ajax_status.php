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
			
					<div class="row">		
						<div class="col-xs-12">
						
							<?php $form=$this->beginWidget('CActiveForm', array(
								'enableAjaxValidation'=>false,
								'htmlOptions' => array(
									'class' => 'form-horizontal',
									
								),
								'htmlOptions'=>array(
								   'class' => 'form-horizontal',
								),
							)); ?>
							
							<?php echo $form->hiddenField($model, 'id'); ?>
							
							<div class="profile-user-info profile-user-info-striped">
								<?php 
									$fields = array(
										'list_id' => 'List Name',
										// 'office_phone_number' => 'Office Phone',
										// 'mobile_phone_number' => 'Mobile Phone',
										// 'home_phone_number' => 'Home Phone',
										'first_name' => 'First Name',
										'last_name' => 'Last Name',
										// 'partner_first_name' => 'Partner\'s First name',
										// 'partner_last_name' => 'Partner\'s Last name',
										// 'email_address' => 'Email',
										// 'address' => 'Address',
										// 'address2' => 'Address 2',
										// 'city' => 'City',
										// 'state' => 'State',
										// 'zip_code' => 'Zip Code',
										// 'language' => 'Language',
										// 'custom_date' => 'Custom Date',
										// 'number_of_dials' => 'Number of dials',
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
														echo $form->error($model, 'status'); 
													}
													elseif( $dbField == 'list_id')
													{
														echo $model->list->name;
													}
													elseif( $dbField == 'language')
													{
														echo $form->dropDownList($model, 'language', Lists::getLanguageOptions(), array('style'=>'width:auto;')); 
													}
													else
													{	
														echo $model->{$dbField};
													}
												?>
											</div>
										</div>
									
									<?php
									}
								?>
								
								<div class="form-actions text-center">
									<button type="button" class="btn btn-xs btn-primary" data-action="save">Save <i class="fa fa-arrow-right"></i></button>
								</div>

								<?php $this->endWidget(); ?>

							</div>
						</div>
					</div>
							
			</div>
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>