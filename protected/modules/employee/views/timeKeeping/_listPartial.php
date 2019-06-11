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

	<td><?php echo $data->id; ?></td>
	<td><?php echo $data->account->fullNameReverse; ?></td>
	<td><?php echo $data->requestDateWithTime(); ?></td>
	
	<?php /*
	<td><?php echo $data->is_make_time_up; ?></td>
	*/ ?>
	
	<td><?php echo AccountPtoForm::YesNoName($data->is_full_shift); ?></td>
	<td><?php echo $data->computed_off_hour; ?></td>
	<td><?php echo AccountPtoFOrm::YesNoName($data->is_pto); ?></td>
	<td><?php echo $data->statusName(); ?></td>
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		
		<?php
			// if( Yii::app()->user->account->checkPermission('structure_companies_edit_button','visible') )
			// {
				echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-minier btn-info')); 
			// }
		?> 
		
		<?php 
			// if( Yii::app()->user->account->checkPermission('structure_companies_delete_button','visible') )
			// {
				echo CHtml::link('<i class="fa fa-times"></i> Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-danger btn-minier','confirm'=>'Click OK to continue')); 
			// }
		?>
	</td>
</tr>