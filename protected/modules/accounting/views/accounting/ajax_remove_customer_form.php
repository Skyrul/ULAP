<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue"><i class="fa fa-credit-card"></i> <?php echo $transaction_type; ?> Customer: <?php echo $customer_name; ?></h4>
			</div>
			
			<div class="modal-body">
				<div class="form">
					<?php $form=$this->beginWidget('CActiveForm', array(
						'enableAjaxValidation'=>false,
						'htmlOptions' => array(
							'class' => 'form-horizontal',
						),
					)); ?>

					<?php echo $form->hiddenField($model, 'customer_id'); ?>
					<?php echo $form->hiddenField($model, 'skill_id'); ?>
					<?php echo $form->hiddenField($model, 'account_id'); ?>
					
					<?php echo CHtml::hiddenField('contract_id', $contract_id); ?>
					<?php echo CHtml::hiddenField('transaction_type', $transaction_type); ?>
					<?php echo CHtml::hiddenField('amount', $amount); ?>
					<?php echo CHtml::hiddenField('credit_amount', $credit_amount); ?>
					<?php echo CHtml::hiddenField('subsidy_amount', $subsidy_amount); ?>
					<?php echo CHtml::hiddenField('original_amount', $original_amount); ?>
					<?php echo CHtml::hiddenField('billing_period', $billing_period); ?>
					
					<?php 
						if( $transaction_type == 'Remove' )
						{
							$note = number_format($subsidy_amount, 2).' subsidy paid by '.$model->customer->company->company_name.'. '.$amount.' paid by '.$customer_name;
						}
						else
						{
							$note = 'Write Off ' . $customer_name;
						}
						
						echo CHtml::textArea('note', $note, array('class'=>'autosize-transition form-control'));
					?>
					
					<?php $this->endWidget(); ?>	
				</div>
			</div>
			
			<div class="modal-footer">
				<button class="btn btn-sm" data-dismiss="modal">
					<i class="ace-icon fa fa-times"></i>
					Cancel
				</button>

				<button data-action="save" class="btn btn-sm btn-primary">
					<i class="ace-icon fa fa-check"></i>
					OK
				</button>
			</div>
		</div>
	</div>
</div>