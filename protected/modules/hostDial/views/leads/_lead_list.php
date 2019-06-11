
<tr>

	<td class="center"><?php echo $data->_memberNumber; ?></td>
	<td><?php echo $data->first_name; ?></td>
	
	<td><?php echo $data->last_name; ?></td>
	
	<td class="center">
		<?php echo $data->office_phone_number != '' ? "(".substr($data->office_phone_number, 0, 3).") ".substr($data->office_phone_number, 3, 3)."-".substr($data->office_phone_number,6) : ''; ?>
	</td>
	
	<td class="center">
		<?php echo $data->mobile_phone_number != '' ? "(".substr($data->mobile_phone_number, 0, 3).") ".substr($data->mobile_phone_number, 3, 3)."-".substr($data->mobile_phone_number,6) : ''; ?>
	</td>
	
	<td class="center">
		<?php echo $data->home_phone_number != '' ? "(".substr($data->home_phone_number, 0, 3).") ".substr($data->home_phone_number, 3, 3)."-".substr($data->home_phone_number,6) : ''; ?>
	</td>
		
	<td class="center"><?php echo $data->getStatus(); ?></td>
	
	<td>
		<?php echo CHtml::link('Status',array('status','id'=>$data->id),array('class'=>'btn btn-minier btn-info lead-status','onClick'=>'return false;'));  ?> 
		<?php echo CHtml::link('Data Tab',array('dataTab','id'=>$data->id),array('class'=>'btn btn-minier btn-info lead-data-tab','onClick'=>'return false;'));  ?> 
		<?php echo CHtml::link('View History',array('history','id'=>$data->id),array('class'=>'btn btn-minier btn-info lead-history','onClick'=>'return false;'));  ?> 
		<?php 
			if( $data->is_do_not_call == 1 )
			{
				echo CHtml::link('Remove DNC',array('removeDnc','id'=>$data->id),array('class'=>'btn btn-minier btn-info lead-remove-dnc','onClick'=>'return false;'));  
			}
		?> 
	</td>
</tr>