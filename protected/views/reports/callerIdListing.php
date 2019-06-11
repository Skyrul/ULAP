<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			var ajax_processing = false;
			
			$(document).on("click", ".btn-name-check", function(){
				
				if( !ajax_processing )
				{
					var id = $(this).prop("id");
					var this_button = $(this);
					var this_row = this_button.closest("tr");
					var cname_td = this_row.find(".td-cname");
					
					this_button.attr("disabled", true);
					cname_td.html( "Processing..." );

					ajax_processing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/reports/ajaxOpenCnamApi",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "id": id, "type": "checkName" },
						success: function(response) {
							
							if( response.status == "success" )
							{
								response_text = response.name;
								this_button.attr("disabled", false);
							}
							else
							{
								response_text = "Api request failed.";
							}
							
							cname_td.html( response_text );
							
							ajax_processing = false;
						}
					});
				}
			});
			
			$(document).on("click", ".btn-check-did", function(){
				
				if( !ajax_processing )
				{
					var this_button = $(this);
					
					var did_field = $(".txt-check-did");
					
					var did = did_field.val();

					if( did != "" )
					{						
						this_button.attr("disabled", true);
						this_button.html( "Processing..." );

						ajax_processing = true;
						
						$.ajax({
							url: yii.urls.absoluteUrl + "/reports/ajaxOpenCnamApi",
							type: "post",
							dataType: "json",
							data: { "ajax":1, "did": did, "type": "checkDID" },
							success: function(response) {
								
								if( response.status == "success" )
								{
									response_text = response.name;
								}
								else
								{
									response_text = "Api request failed.";
								}
								
								this_button.attr("disabled", false);
								this_button.html( "Check DID" );
								
								did_field.val( response_text );
								
								ajax_processing = false;
							}
						});
					}
				}
			});
			
			$(document).on("click", ".btn-name-check-all", function(){
				
				if( !ajax_processing )
				{
					var this_button = $(this);
					var original_text = this_button.html();
					
					this_button.attr("disabled", true);
					this_button.html( "Processing..." );

					ajax_processing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/reports/ajaxOpenCnamApi",
						type: "post",
						dataType: "json",
						data: { "ajax":1, "type": "checkNameAll" },
						success: function(response) {

							if( response.html != "" )
							{
								$("#callerIdTbl > tbody").html( response.html );
							}
						
							this_button.attr("disabled", false);
							this_button.html( original_text );
							
							ajax_processing = false;
						}
					});
				}
			});
			
		});
	
	', CClientScript::POS_END);
?>


<div class="page-header">
	<h1>Reports</h1>
</div>

<div class="tabbable tabs-left">
	
	<ul class="nav nav-tabs">
		<?php if( Yii::app()->user->account->checkPermission('reports_real_time_monitors_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'index' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('index'); ?>">Real-Time Monitors</a></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('reports_reports_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'reports' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('reports'); ?>">Reports</a></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('reports_caller_id_listing_tab','visible') ){ ?>
			<li class="<?php echo Yii::app()->controller->action->id == 'callerIdListing' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callerIdListing'); ?>">Caller ID Listing</a></li>
		<?php } ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'conflictMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('conflictMonitor'); ?>">Conflict Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'appointmentMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('appointmentMonitor'); ?>">Confirm Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'rescheduleMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('rescheduleMonitor'); ?>">Reschedule Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'callBackMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('callBackMonitor'); ?>">Call Back Monitor</a></li>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'completedLeadMonitor' ? 'active' : ''; ?>"><a href="<?php echo $this->createUrl('completedLeadMonitor'); ?>">Completed Lead Monitor</a></li>
	</ul>
	
</div>

<div class="tab-content">

	<?php
		foreach(Yii::app()->user->getFlashes() as $key => $message) {
			echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
		} 
	?>

	<div class="row pull-right">
		<?php echo CHtml::link('<i class="fa fa-check"></i> Check All', '', array('class'=>'btn btn-sm btn-primary btn-name-check-all', 'style'=>'margin-right:15px;')); ?>
	</div>
	
	<div class="row">
		<div class="col-xs-12 col-sm-5">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="ace-icon fa fa-check"></i>
				</span>

				<input type="text" class="form-control search-query txt-check-did" placeholder="DID">
				
				<span class="input-group-btn">
					<button type="button" class="btn btn-purple btn-sm btn-check-did">
						Check DID
					</button>
				</span>
			</div>
		</div>
	</div>
	
	<div class="space-12"></div>
	
	<div class="row">
		<div class="col-sm-12">
			
			<table id="callerIdTbl" class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th>#</th>
					<th>Company</th>
					<th>Area Code</th>
					<th>DID</th>
					<th>Cname</th>
					<th>Assigned Customers</th>
					
					<?php if( Yii::app()->user->account->checkPermission('reports_caller_id_listing_remove_button','visible') ){ ?>
					<th width="15%" class="center">Options</th> 
					<?php } ?>
				</thead>
				
				<tbody>
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
				</tbody>
				
			</table>
		</div>
	</div>
</div>