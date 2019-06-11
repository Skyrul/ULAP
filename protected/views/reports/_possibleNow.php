<?php 

$addedCondition = ' 1';

if( $dateFilterStart != "" && $dateFilterEnd != "" )
{
	$dateFilterStart = date('Y-m-d 00:00:00', strtotime($dateFilterStart));
	$dateFilterEnd = date('Y-m-d 23:59:59', strtotime($dateFilterEnd));
	
	if( !empty($_POST['dateFilterStartTime']) )
	{
		$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.date('H:i:s', strtotime($_POST['dateFilterStartTime']));
		
		$dateFilterStartTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterStartTime'])));
		
		$dateFilterStart = date('Y-m-d', strtotime($dateFilterStart)).' '.$dateFilterStartTime;
	}
	
	if( !empty($_POST['dateFilterEndTime']) )
	{
		$dateFilterEndTime = date('H:i:s', strtotime('+1 hour', strtotime($_POST['dateFilterEndTime'])));
		
		$dateFilterEnd = date('Y-m-d', strtotime($dateFilterEnd)).' '.$dateFilterEndTime;
	}
	
	$addedCondition .= ' AND DATE(t.date_created) >= "'.date('Y-m-d', strtotime($dateFilterStart)).'" AND DATE(t.date_created) <= "'.date('Y-m-d', strtotime($dateFilterEnd)).'"';
}

?>
<div class="row">
	<div class="col-sm-6">
		<form action="" method="post">
			Date:
			<input type="text" name="dateFilterStart" class="datepicker" value="<?php echo $dateFilterStart; ?>" placeholder="From">
			<input type="text" name="dateFilterEnd" class="datepicker" value="<?php echo $dateFilterEnd; ?>" placeholder="To">
			
			<button type="submit" class="btn btn-xs btn-primary">Execute <i class="fa fa-arrow-right"></i> </button>
		</form>
	</div>
						
	<div class="col-sm-6 text-right">
		<?php echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('export', 'page'=>$page, 'selectedSkills'=>'', 'contractId'=>$contractId, 'dateFilterStart'=>$dateFilterStart, 'dateFilterEnd'=>$dateFilterEnd), array('class'=>'btn btn-yellow btn-sm')); ?>
		
	</div>
</div>

<br>
<br>
<?php

$models = array();

if( $dateFilterStart != "" && $dateFilterEnd != "" )
{
	$models = PossibleNowLead::model()->findAll(array(
		'condition' => '1 AND' . $addedCondition,
		'order' => 'date_created DESC',
	));
}

echo '
	<table id="leadsTbl" class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<th>#</th>
			<th>Date/Time</th>
			<th>Customer Name</th>
			<th>Company</th>
			<th>Lead Name</th>
			<th>Phone Type</th>
			<th>Lead Phone</th>
		</thead>';

if(!empty($models) )
{
	$ctr = 1;
	
	foreach( $models as $model )
	{
		// $dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
		$dateTime = new DateTime($model->date_created);
		
		echo '<tr>';
			echo '<td>'.$ctr.'</td>';

			echo '<td>'.$dateTime->format('m/d/Y g:i A').'</td>';
			echo '<td>'.$model->customer->getFullName().'</td>';
			echo '<td>'.$model->company->company_name.'</td>';
			echo '<td>'.$model->lead->getFullName().'</td>';
			echo '<td>'.$model->phone_number_type.'</td>';
			echo '<td>'.$model->phone_number.'</td>';
			 
		echo '</tr>';
		
		$ctr++;
	}
}
else
{
	echo '<tr><td colspan="4">No results found.</td></tr>';
}

echo '</table>';
?>