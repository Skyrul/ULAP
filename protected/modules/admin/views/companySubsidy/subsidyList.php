<?php 

if(!empty($companySubsidys))
{
	echo '<table class="table table-striped">';
	echo '<tr>';
		echo '<th>Name</th>';
		echo '<th>Date</th>';
		echo '<th>Action</th>';
	echo '</tr>';
	
	foreach($companySubsidys as $companySubsidy)
	{
		echo '<tr>';
			echo '<td>'.$companySubsidy->subsidy_name.'</td>';
			echo '<td>'.$companySubsidy->start_date.'<br>'.$companySubsidy->end_date.'</td>';
			echo '<td>';
				echo CHtml::link('Edit',array('companySubsidy/ajaxEditSubsidy','companysubsidy_id'=>$companySubsidy->id,'id'=>$companySubsidy->company_id),array('class'=>'btn btn-xs btn-info btn-edit-subsidy'));
				echo '&nbsp';
				echo CHtml::link('Remove',array('companySubsidy/ajaxRemoveSubsidy','companysubsidy_id'=>$companySubsidy->id,'id'=>$companySubsidy->company_id),array('class'=>'btn btn-xs btn-danger btn-remove-subsidy'));
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