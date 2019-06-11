<div class="modal fade">
	<div class="modal-dialog" style="width:70%;">
		<div class="modal-content">
			<div class="modal-header" style="background:#438EB9;">
				<button type="button" class="close white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title white">
					<?php 
						switch( $_POST['link_name'] )
						{
							default: 
							case 'customer_count': $link_name = 'Customer Count'; break;
							case 'sales_starting_count': $link_name = 'Sales starting Count'; break;
							case 'cancels_affecting_count': $link_name = 'Cancels Affecting Count'; break;
						}
					?>
					
					<?php echo $link_name; ?>
					
					<small class="white">
						<i class="ace-icon fa fa-angle-double-right"></i>
						<?php echo $month_name; ?>
					</small>
				</h4>
			</div>
			
			<div class="modal-body no-padding" style="height:600px; overflow:auto;">
				<table class="table table-condensed table-bordered table-condensed">
					<?php
						if( $models )
						{
							$ctr = 1;
							
							foreach( $models as $model )
							{	
								echo '
									<tr>
										<td class="center" style="width:5%"> '.$ctr.' </td>
										<td> '.CHtml::link($model->customer_name, array('customer/insight', 'customer_id'=>$model->customer_id), array('target'=>'_blank')).' </td>
									</tr>
								';
								
								$ctr++;
							}
						}
						else
						{
							echo 'No records found.';
						}
					?>
				</table>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>

