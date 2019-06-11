<?php 
	$statusBadge = '';
	
	if(isset($data->account) && isset($data->account->customerEnrollment) )
	{
		$dateCreated = strtotime($data->account->date_created. ' + 3 days');
		$dateToday = strtotime("now");
		
		
		if($dateCreated > $dateToday)
			$statusBadge = '<span class="btn btn-minier btn-danger" >New</span>';
		
		//FIX FOR multiple CustomerSKill being added to the Customer
		$criteria = new CDbCriteria;
		$criteria->compare('customer_id', $data->id);
		$criteria->order = 'date_created DESC';
		$customerSkill = CustomerSkill::model()->find($criteria);
		if($customerSkill !== null)
		{
			$dateCreated = strtotime($customerSkill->date_created. ' + 3 days');
			$dateToday = strtotime("now");
			
			if($dateCreated > $dateToday)
				$statusBadge = '<span class="btn btn-minier btn-danger" >New</span>';
		}
	}

	if( $data->status == $data::STATUS_INACTIVE)
	{
		if( $statusBadge != "" )
		{
			$statusBadge .= '<br />';
		}
		
		$statusBadge .= '<span class="btn btn-minier btn-warning" >Inactive</span>';
	}

	$latestCustomerFile = CustomerFile::model()->find(array(
		'condition' => 'customer_id = :customer_id AND status=1',
		'params' => array(
			':customer_id' => $data->id,
		),
		'order' => 'date_created DESC',
	));
	
	if( $latestCustomerFile && isset($latestCustomerFile->fileUpload) )
	{
		$dateCreated = strtotime($latestCustomerFile->fileUpload->date_created. ' + 72 hours');
		$dateToday = strtotime("now");
		
		if($dateCreated > $dateToday && $latestCustomerFile->is_new == 1)
		{
			if( $statusBadge != "" )
			{
				$statusBadge .= '<br />';
			}
		
			$statusBadge .= '<span class="btn btn-minier btn-primary">New File</span>';
		}
	}
?>


<?php if($index == 0 ): ?>

<tr>
	<th class="center">Company</th>
	<th class="center">Customer Name</th>
	<th class="center">Options</th>
</tr>

<?php endif; ?>

<tr>
	<td><?php echo isset($data->company) ? $data->company->company_name : ''; ?></td>
	<td>
		<div class="col-sm-3">
			<?php echo $statusBadge; ?> 
		</div>
		
		<div class="col-sm-9">
			<?php echo $data->fullName; ?>
		</div>
	</td>
	<td class="center">
		<?php 
			if( Yii::app()->user->account->checkPermission('customer_list_of_staff_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-users"></i> List of Staff', '',array('id'=> $data->id, 'class'=>'btn btn-minier btn-info customer-summary')); 
			}
		?>&nbsp;
		
		<?php 
			if( Yii::app()->user->account->checkPermission('customer_account_details_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-user"></i> Account Details',array('insight/index','customer_id'=> $data->id ),array('class'=>'btn btn-minier btn-info')); 
			}
		?>
		
		<?php //echo CHtml::link('<i class="fa fa-check"></i> Manage Skills',array('#'),array('class'=>'btn btn-minier btn-info btn-customer-manage')); ?>
	</td>
</tr>



