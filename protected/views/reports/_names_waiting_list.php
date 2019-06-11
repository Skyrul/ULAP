<?php 
	$leadCount = Lead::model()->count(array(
		'with' => 'customer',
		'condition' => 'customer.id IS NOT NULL AND t.status=1 AND t.type=1 AND t.list_id IS NULL AND t.customer_id = :customer_id',
		'params' => array(
			':customer_id' => $data->customer_id,
		),
	));
?>

<?php if( $index == 0 ): ?>

<thead>
	<th>Customer ID</th>
	<th>Customer Name</th>
	<th>Waiting Lead Count</th>
</thead>

<?php endif; ?>

<tr>
	<td><?php echo $data->customer->custom_customer_id; ?></td>
	
	<td><?php echo CHtml::link($data->customer->firstname.' '.$data->customer->lastname, array('/customer/insight/index', 'customer_id'=>$data->customer_id)); ?></td>
	
	<td class="center"><?php echo $leadCount; ?></td>
</tr>