<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<li><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<li class="active"><a href="<?php echo $this->createUrl('crmList'); ?>">Reports</a></li>
	</ul>
	
</div>

<div class="tab-content text-center">

	<table class="table table-bordered table-condensed table-hover">
		
		<thead>
			<th width="5%"></th>
			<th class="center">Account #</th>
			<th class="center">Company</th>
			<th class="center">Customer Name</th>
			<th class="center">Phone</th>			
			<th class="center">Email</th>			
			<th class="center">Contract</th>			
			<th class="center" width="5%"></th>
		</thead>
		
		<tbody>
			<?php 
				if($customers)
				{	
					$ctr = 1;
					
					foreach($customers as $customer)
					{
						$contract = Contract::model()->find(array(
							'condition' => 'company_id = :company_id',
							'params' => array(
								':company_id' => $customer->company_id,
							),
						));
						
					?>
					
						<tr>
							<td><?php echo $ctr; ?></td>
							<td><?php echo $customer->account_number; ?></td>
							<td align="left"><?php echo isset($customer->company) ? $customer->company->company_name : ''; ?></td>
							<td align="left"><?php echo $customer->getFullName(); ?></td>
							<td align="left"><?php echo $customer->phone; ?></td>							
							<td align="left"><?php echo $customer->email_address; ?></td>							
							<td align="left"><?php echo $contract != null ? $contract->contract_name : ''; ?></td>							
							<td><?php echo CHtml::link('<i class="fa fa-search"></i> View', array('reports/viewCustomerReports', 'id'=>$customer->id), array('class'=>'btn btn-minier btn-primary')); ?></td>
						</tr>
						
					<?php
					
						$ctr++;
					}
				}
				else
				{
					echo '<tr><td colspan="4">No customer found.</td></tr>';
				}
			?>
		</tbody>
		
	</table>

</div>