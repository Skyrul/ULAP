<?php 

if(!empty($tierSubsidys))
{
	echo '<table class="table table-striped">';
	echo '<tr>';
		echo '<th>Tier</th>';
		echo '<th>Name</th>';
		echo '<th>Date</th>';
		/*
		echo '<th>Type</th>';
		echo '<th>Value</th>';
		*/
		echo '<th>Action</th>';
	echo '</tr>';
	
	foreach($tierSubsidys as $tierSubsidy)
	{
		echo '<tr>';
			echo '<td>'.$tierSubsidy->tier->tier_name.'</td>';
			echo '<td>'.$tierSubsidy->subsidy_name.'</td>';
			echo '<td>'.$tierSubsidy->start_date.'<br>'.$tierSubsidy->end_date.'</td>';
			/*
			echo '<td>'.$tierSubsidy->type.'</td>';
			echo '<td>'.$tierSubsidy->value.'</td>'; */
			echo '<td>';
				echo CHtml::link('Edit',array('companyTierSubsidy/ajaxEditSubsidy','tiersubsidy_id'=>$tierSubsidy->id,'company_id'=>$tierSubsidy->company_id),array('class'=>'btn btn-xs btn-info btn-edit-subsidy'));
				echo '&nbsp';
				echo CHtml::link('Remove',array('companyTierSubsidy/ajaxRemoveSubsidy','tiersubsidy_id'=>$tierSubsidy->id,'company_id'=>$tierSubsidy->company_id),array('class'=>'btn btn-xs btn-danger btn-remove-tier-subsidy'));
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
else
{
	echo 'List is empty.';
}
?>