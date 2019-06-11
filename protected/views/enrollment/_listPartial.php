<tr>
	<td class="col-md-8"><?php echo $data->FullNameReverse; ?></td>

	
	<td class="col-md-4">
		<?php if($data->is_enrolled == 0){ ?>
		<?php echo CHtml::link('Edit',array('update','id'=>$data->id),array('class'=>'btn btn-minier btn-info')); ?>
		&nbsp;
		<?php echo CHtml::link('Complete Enrollment',array('contract','id'=>$data->id),array('class'=>'btn btn-minier btn-info','target'=>'_blank')); ?>
		&nbsp;
		
		
		<?php 
			//if($data->id == 1)
				echo CHtml::link('Send Email',array('sendEmail','id'=>$data->id),array('class'=>'btn btn-minier btn-info','target'=>'_blank'));

		?>
		<?php } ?>
	</td>
	
</tr>