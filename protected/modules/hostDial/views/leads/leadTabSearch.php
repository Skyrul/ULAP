
	<table style="width:1020px;" id="table_by_memberNumber" class="table table-striped">
	
	<thead>
		<tr>
			<th style="width:130px;">Member Number</th>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Office Phone</th>
			<th>Mobile Phone</th>
			<th>Home Phone</th>
			<th>Status</th>
			<th>List</th>
			<th>Actions</th>
		</tr>
	</thead>

	<?php 
	if(!empty($leads))
	{ 
		/*foreach($leads as $lead)
		{
	?>

		<tr data-tt-id=>
			<td><?php echo $lead->_memberNumber; ?></td>
			<td><?php echo $lead->first_name; ?></td>
			<td><?php echo $lead->last_name; ?></td>
			<td><?php echo $lead->office_phone_number; ?></td>
			<td><?php echo $lead->mobile_phone_number; ?></td>
			<td><?php echo $lead->home_phone_number; ?></td>
			<td><?php echo $lead->getStatus(); ?></td>
			<td><?php echo $lead->list->name; ?></td>
			
			<?php if(!empty($listModel)) {?>
			
				<td>Status | Data Tab | View History</td>
				
			<?php }else{ ?>
			
				<td>View</td>
			
			<?php } ?>
		</tr>
	<?php } */

		// if(!empty($leadsByMemberNumber)){
			foreach($leadsByMemberNumber as $memberNumber => $leads)
			{
				$is_parent = count($leads) > 1 ? true : false;
				
				?>
				
				<?php if($is_parent){ ?>
					<tr data-tt-id="<?php echo $memberNumber; ?>">
						<td colspan="9"><?php echo $memberNumber; ?></td>
					</tr>
				<?php } ?>
					
					
				<?php 
					
				foreach($leads as $lead_id => $lead)
				{
					$parentMemberNumber = ($is_parent) ? $memberNumber : '';
					
					?>

						<tr data-tt-id="<?php echo $lead['lead_id']; ?>" data-tt-parent-id="<?php echo $parentMemberNumber; ?>" class="branch collapsed" style="display:none;">
							<td><?php echo $lead['memberNumber']; ?></td>
							<td><?php echo $lead['first_name']; ?></td>
							<td><?php echo $lead['last_name']; ?></td>
							<td><?php echo $lead['office_phone_number']; ?></td>
							<td><?php echo $lead['mobile_phone_number']; ?></td>
							<td><?php echo $lead['home_phone_number']; ?></td>
							<td><?php echo $lead['status']; ?></td>
							<td><?php echo $lead['list_name']; ?></td>
							
							
							<td>
								<?php echo CHtml::link('View',array('view','id'=>$lead_id),array('class'=>'btn btn-minier btn-info lead-details'));  ?> 
							</td>
						</tr>
					<?php 
				} 
			}
		// }
	}
	else
	{
		?>
	<tr>
		<td colspan="9">No lead(s) found in the list.</td>
	</tr>
	<?php } ?>
	</table>
<?php
Yii::app()->clientScript->registerScript('treetableJS','
	$("#table_by_memberNumber").treetable({ expandable: true });
',CClientScript::POS_END); ?>
		