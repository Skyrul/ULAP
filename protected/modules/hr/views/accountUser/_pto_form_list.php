<?php if($index == 0): ?>

<thead>
	<tr>
		<th class="center"></th>
		<th>Request Date/Time</th>
		<th>Full Shift</th>
		<th class="center">Hours</th>
		<th>Make Time Up</th>
		<th>PTO</th>
		<th>Status</th>
	</tr>
</thead>

<?php endif; ?>

<?php
$rowCss = '';
if($data->status == 1)
	$rowCss = 'success';

if($data->status == 2)
	$rowCss = 'warning';

if($data->status == 3)
	$rowCss = 'danger';



	
?>

<tr class="<?php echo $rowCss; ?>">

	<td>
		<input type="checkbox" class="ace pto-checkbox" value="<?php echo $data->id; ?>">
		<span class="lbl"></span>
	</td>
	<td><?php echo $data->requestDateWithTime(); ?></td>
	
	<?php /*
	<td><?php echo $data->is_make_time_up; ?></td>
	*/ ?>
	
	<td><?php echo AccountPtoForm::YesNoName($data->is_full_shift); ?></td>
	<td><?php echo $data->computed_off_hour; ?></td>
	<td><?php echo AccountPtoForm::YesNoName($data->is_make_time_up); ?></td>
	<td><?php echo AccountPtoForm::YesNoName($data->is_pto); ?></td>
	<td><?php echo $data->statusName(); ?></td>
</tr>