<?php 
	Yii::app()->clientScript->registerMetaTag("5; url=".$this->createUrl('index')."", null, 'refresh');
?>

<div class="row">
	<div class="page-header">
		<h1>Lead Hopper List</h1>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<table class="table table-condensed table-bordered">
			<thead>
				<tr>
					<th>#</th>
					<th>Customer Name</th>
					<th>Skill Name</th>
					<th>Current Agent Name</th>
					<th>List Name</th>
					<th>Lead Name</th>
					<th>Called Count</th>
					<th>Lead Time Zone</th>
					<th>Status</th>
					<th>Type</th>
					<th>Call Back Date</th>
					<th>Appointment Date</th>
				</tr>
			</thead>
			
			<tbody>
				<?php 
					if ( $models )
					{
						$ctr = 1;
						
						foreach( $models as $model )
						{	
							$customer = $model->customer;
							$skill = $model->skill;
							$currentAgent = $model->currentAgentAccount;
							$list = $model->list;
							$lead = $model->lead;
							?>
							
								<tr>
									<td><?php echo $ctr; ?></td>
										
									<td>
										<?php echo CHtml::link($customer->getFullName(), array('/customer/insight', 'customer_id'=>$customer->id), array('target'=>'_blank')); ?>							
									</td>
									
									<td><?php echo $skill->skill_name; ?></td>
									
									<td><?php echo $model->agent_account_id != null ? $currentAgent->getFullName() : ''; ?></td>
									
									<td><?php echo $list->name; ?></td>
									
									<td><?php echo $lead->getFullName(); ?></td>
									
									<td><?php echo $lead->number_of_dials; ?></td>
									
									<td><?php echo $model->lead_timezone; ?></td>
									
									<td><?php echo $model->status; ?></td>
									
									<td>
										<?php 
											switch( $model->type )
											{
												default: case 1: echo 'Contact'; break;
												case 2: echo 'Callback'; break;
												case 3: echo 'Appointment for Confirmation Call'; break;
												case 4: echo 'Lead Search'; break;
												case 5: echo 'Appoitnment Conflict'; break;
											}
										?>
									</td>
									
									<td><?php echo $model->callback_date; ?></td>
									
									<td><?php echo $model->appointment_date	; ?></td>
								</tr>
							
							<?php
							
							$ctr++;
						}
					}
					else
					{
						echo '<tr><td colspan="10">There are no leads in the hopper.</td></tr>';
					}
				?>

			</tbody>
			
		</table>
	</div>
</div>
