<?php 
$statusName = '';
if(isset($statuses[$list->status]))
	$statusName = $statuses[$list->status];

echo '<tr>';
	echo '<td>'.CHtml::link($list->name,array('leads/index','customer_id' => $customer_id,'list_id'=>$list->id)).'</td>';
	echo '<td>'.$statusName.'</td>';
	echo '<td>'.$list->skill->skill_name.'</td>';
	echo '<td>0</td>';
	echo '<td>'.$list->leadCount.'</td>';
	echo '<td>'.$list->leadCallablesCount.'</td>';
echo '</tr>';
?>