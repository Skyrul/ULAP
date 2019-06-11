<style>.page-content { padding:0 !important; } </style>

<div class="widget-box widget-color-dark">
	<div class="widget-header">
		<h4 class="widget-title">Queue Viewer</h4>

		<div class="widget-toolbar no-border">
			Auto Refresh
			<label>
				<input type="checkbox" class="ace ace-switch ace-switch-3">
				<span class="lbl middle"></span>
			</label>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<table class="table table-condensed table-bordered email-monitor-tbl">
				<thead>
					<tr>
						<th>Customer Name</th>
						<th>Skill</th>
						<th>Priority Reset Date</th>
						<th>Priority</th>
						<th>Pace</th>
						<th>Current Dials</th>
						<th>Available Leads</th>
						<th>Total Potential Dials</th>
						<th>Next Available Calling Time</th>
						<th>Available Calling Blocks</th>
						<th>Call Agent</th>
						<th>Dials until Re-evaluation</th>
						<th>Boost</th>
						<th style="width:16%;">Options</th>
					</tr>
				</thead>
				
				<tbody>
					<?php 
						if ( $customers )
						{
							foreach( $customers as $customer )
							{
							?>
								<tr style="background:#ccc;">
										
									<td>
										<?php echo CHtml::link($customer->getFullName(), array('/customer/insight', 'customer_id'=>$customer->id), array('target'=>'_blank')); ?>							
									</td>
									
									<td></td>
									
									<td></td>
									
									<td style="">0</td>
									
									<td>0</td>
									
									<td>0</td>
									
									<td style="background:red;;">0</td>
									
									<td>0</td>
									
									<td>Now</td>
									
									<td></td>
									
									<td></td>
									
									<td></td>

									<td>
										<a data-toggle="modal" role="button" class="add-boost-link" customer_name="Boost - Rob Joiner" available_leads="0" id="85" href="#add-boost-modal-form">Add Boost</a>
									</td>
									
									<td><span data-content="" title="Rob Joiner" data-placement="bottom" data-rel="popover" data-toggle="button" class="btn btn-danger btn-minier">Debug</span></td>
									
								</tr>
								
							<?php
							}
						}
						else
						{
							echo '<tr><td colspan="14">No active customer found.<td></tr>';
						}
					?>
					
					
				</tbody>
				
			</table>
		</div>
	</div>
</div>
