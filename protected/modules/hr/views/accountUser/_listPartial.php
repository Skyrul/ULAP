<tr>
	<?php /*(if(isset($data->accountUser)){ ?>
		<?php if($data->accountUser->getImage() != null){ ?>
		<td><?php echo CHtml::image($data->accountUser->getImage()); ?></td>
		<?php }else{ ?>
		<td><i>No image yet.</i></td>
		<?php } ?>
	<?php } */?>
	
	<?php /* <td><?php echo $data->username; ?></td> */ ?>
	
	<td><?php echo isset($data->accountUser) ? $data->accountUser->first_name : '&nbsp;'; ?></td>
	
	<td><?php echo isset($data->accountUser) ? $data->accountUser->last_name : '&nbsp;'; ?></td>
	
	<td><?php echo isset($data->accountUser) ? $data->accountUser->job_title : '&nbsp;'; ?></td>
	
	<td>
		<?php //echo CHtml::link('View',array('view','id'=>$data->id),array('class'=>'btn btn-xs btn-success')); ?>
		
		<?php 
			if( Yii::app()->user->account->checkPermission('employees_employee_details_button','visible') )
			{
				if( Yii::app()->user->account->checkPermission('employees_employee_details_button','only_for_direct_reports', $data->id) )
				{
					echo CHtml::link('<i class="fa fa-search"></i> Employee Details',array('employeeDetails','id'=>$data->id),array('class'=>'btn btn-minier btn-info')); 
				}
			}
		?> 
		
		<?php //echo CHtml::link('Delete',array('delete','id'=>$data->id),array('class'=>'btn btn-xs btn-info')); ?>
	</td>
</tr>