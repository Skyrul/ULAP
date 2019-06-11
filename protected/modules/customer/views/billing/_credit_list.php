<?php if ($index == 0){ ?>

<thead>
	<th>Description</th>
	<th class="center">Options</th>
</thead>

<?php } ?>

<tr>
	<td><?php echo $data->description; ?></td>
	
	<?php if(empty($data->customerCreditBillingHistory)){ ?>
	<td class="center">
		<?php 
			echo '&nbsp;&nbsp;&nbsp;&nbsp;' . CHtml::link( UserAccess::hasRule('customer','Billing','updateCreditCard') ? '<i class="fa fa-edit"></i> Edit' : '<i class="fa fa-search"></i> View', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'edit-credit-btn')); 
		?>
		
		
		<?php if( (Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService()) && Yii::app()->user->account->checkPermission('customer_billing_credits_delete_link','visible') ){ ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php 
			echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'delete-credit-btn')); 
		?>
		<?php } ?>
	</td>
	<?php }else{ ?>
	<td class="center">
		<?php 
			echo '&nbsp;&nbsp;&nbsp;&nbsp;' . CHtml::link('<i class="fa fa-search"></i> View History', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'edit-credit-btn')); 
		?>
		<?php if( (Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService()) && Yii::app()->user->account->checkPermission('customer_billing_credits_delete_link','visible') ){ ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php 
			echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'delete-credit-btn')); 
		?>
		<?php } ?>
	</td>
	<?php } ?>
</tr>