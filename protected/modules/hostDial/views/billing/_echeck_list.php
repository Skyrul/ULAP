<?php if ($index == 0){ ?>

<thead>
	<th>Name on Account</th>
	<th class="center">Options</th>
</thead>

<?php } ?>

<tr>
	<td>
		<?php echo $data->account_name; ?>
		
		<?php
			if($data->is_preferred == 1)
			{
				echo ' <span class="label label-sm label-success arrowed-in arrowed-in-right">DEFAULT</span>';
			}
		?>
	</td>
	
	<td class="center">
		<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() ){ ?>
		
		<?php 
			echo CHtml::link('<i class="fa fa-check"></i> Set as Default', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'set-default-echeck-btn')); 
		?>
		
		<?php } ?>

		<?php if(UserAccess::hasRule('customer','Billing','viewCreditCard')){ ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php 
			echo CHtml::link('<i class="fa fa-search"></i> View', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'edit-echeck-btn')); 
		?>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() ){ ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php 
			echo CHtml::link('<i class="fa fa-edit"></i> Edit', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'edit-echeck-btn')); 
		?>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() ){ ?>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php 
			echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$data->id, 'class'=>'delete-echeck-btn')); 
		?>
		<?php } ?>
	</td>
</tr>