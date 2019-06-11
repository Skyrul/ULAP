<?php 
	if( $models )
	{
		$ctr = 1;
		
		foreach( $models as $model )
		{
		?>

			<?php 
				$customersAssigned = CustomerSkill::model()->count(array(
					// 'group' => 't.customer_id',
					'with' => array('customer', 'customer.company'),
					'condition' => 't.skill_caller_option_customer_choice=2 AND LOWER(company.company_name) = :company_name AND SUBSTR(customer.phone,2,3) = :area_code',
					'params' => array(
						':company_name' => strtolower($model->company_name),
						':area_code' => $model->area_code,
					),
				));
			?>

			<tr>

				<td><?php echo $ctr; ?></td>
				
				<td><?php echo $model->company_name; ?></td>
				
				<td><?php echo $model->area_code; ?></td>
				
				<td><?php echo $model->did; ?></td>
				
				<td class="td-cname"><?php echo $model->cname; ?></td>
				
				<td>
					<?php
						if( $customersAssigned > 0 && Yii::app()->user->account->checkPermission('reports_caller_id_listing_assigned_customers_link','visible') )
						{
							echo CHtml::link($customersAssigned, array('viewDidAssignedCustomers', 'id'=>$model->id)); 
						}
						else
						{
							echo $customersAssigned; 
						}
					?>
				</td>
				
				<?php if( Yii::app()->user->account->checkPermission('reports_caller_id_listing_remove_button','visible') ){ ?>
				
				<td class="center">
					<?php echo CHtml::link('<i class="fa fa-check"></i> Check Name', '', array('id'=>$model->id, 'class'=>'btn btn-minier btn-primary btn-name-check')); ?>
					<?php echo CHtml::link('<i class="fa fa-times"></i> Remove', array('removeDid','id'=>$model->id), array('class'=>'btn btn-minier btn-danger', 'confirm' => 'Are you sure you want to remove this?')); ?>
				</td>
				
				<?php } ?>
			</tr> 
		
		<?php	
		$ctr++;
		}
	}
	else
	{
		echo '<tr><td colspan="5">No results found.</td></tr>';
	}
?>