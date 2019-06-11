<?php 

if(!empty($companyDids))
{
	echo '<table class="table table-striped">';
	echo '<tr>';
		echo '<th>Area Code</th>';
		echo '<th>Prefix</th>';
		echo '<th>Number</th>';
		echo '<th>Caller Option</th>';
		echo '<th>Action</th>';
	echo '</tr>';
	
	foreach($companyDids as $companyDid)
	{
		echo '<tr>';
			echo '<td>'.$companyDid->area_code.'</td>';
			echo '<td>'.$companyDid->prefix.'</td>';
			echo '<td>'.$companyDid->number.'</td>';
			echo '<td>'.$companyDid->caller_option.'</td>';
			echo '<td>';
				echo CHtml::link('Edit',array('company/ajaxEditDid','companydid_id'=>$companyDid->id,'id'=>$companyDid->company_id),array('class'=>'btn btn-xs btn-info btn-edit-did'));
				echo '&nbsp';
				echo CHtml::link('Remove',array('company/ajaxRemoveDid','companydid_id'=>$companyDid->id,'id'=>$companyDid->company_id),array('class'=>'btn btn-xs btn-danger btn-remove-did'));
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