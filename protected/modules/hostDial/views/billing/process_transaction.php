<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="form">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						
						<div class="row">
							<div class="col-md-4">
								<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> Process Charge <span class="customer-name"></span></h4>
							</div>
							<div class="col-md-7">
								<div id="contract-subsidy-credit-table-container">
									
								</div>
							</div>
						</div>
					</div>
				
					<div class="modal-body">
					
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Billing Period <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->dropDownList($model,'billing_period', $billingPeriodOptions, array('prompt'=>'- Select -','class' => 'billing-subsidy-table')); ?>
								</div>
							</div>
						</div>
					
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Amount <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textField($model, 'amount', array('class'=>'form-control billing-subsidy-table')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Method <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo CHtml::dropDownList('method', $model->getDefaultMethod($model->customer_id), $model->getPaymentMethods($model->customer_id), array('prompt'=>'- Select -')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Contract <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->dropDownList($model,'contract_id', $contractOptions, array('prompt'=>'- Select -', 'class'=> 'billing-subsidy-table')); ?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Transaction Type <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php
										$billingTypeOptions = array(
											'Service Fee' => 'Service Fee',
											'Enrollment Fee' => 'Enrollment Fee',
											'Termination Fee' => 'Termination Fee'
										);
										
										echo $form->dropDownList($model,'billing_type', $billingTypeOptions); 
									?>
								</div>
							</div>
						</div>
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Note <span class="required">*</span></div>

								<div class="profile-info-value">
									<?php echo $form->textArea($model, 'description', array('class'=>'form-control col-xs-12')); ?>
								</div>
							</div>
						</div>
						
						<div class="space-12"></div>
						
						<div class="center">
							<button type="button" class="btn btn-sm btn-info" data-action="save">Process</button>
						</div>
					
					</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>