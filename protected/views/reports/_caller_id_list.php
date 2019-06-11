<?php 
	$customersAssigned = CustomerSkill::model()->count(array(
		// 'group' => 't.customer_id',
		'with' => array('customer', 'customer.company'),
		'condition' => 't.skill_caller_option_customer_choice=2 AND LOWER(company.company_name) = :company_name AND SUBSTR(customer.phone,2,3) = :area_code',
		'params' => array(
			':company_name' => strtolower($data->company_name),
			':area_code' => $data->area_code,
		),
	));
?>

<tr>

	<td><?php echo $data->company_name; ?></td>
	
	<td><?php echo $data->area_code; ?></td>
	
	<td><?php echo $data->did; ?></td>
	
	<td><?php echo $customersAssigned > 0 ? CHtml::link($customersAssigned, array('viewDidAssignedCustomers', 'id'=>$data->id)) : $customersAssigned; ?></td>
	
	<td><?php echo CHtml::link('<i class="fa fa-times"></i> Remove', array('removeDid','id'=>$data->id), array('class'=>'btn btn-minier btn-danger', 'confirm' => 'Are you sure you want to remove this?')); ?></td>

</tr> 