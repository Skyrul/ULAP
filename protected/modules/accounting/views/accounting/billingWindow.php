<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> 'billingWindow'
	));
	
	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			// process transaction
			$(document).on("click", ".process-transaction-btn", function(){
				
				var this_row = $(this).closest("tr");
				var this_button = $(this);
				var delete_button = $(this).next();
				
				var customer_id = $(this).attr("customer_id");
				var skill_id = $(this).attr("skill_id");
				var customer_name = $(this).attr("customer_name");
				var amount = $(this).attr("amount");
				var billing_period = $(this).attr("billing_period");
				var contract = $(this).attr("contract");
				var credit_amount = $(this).attr("credit_amount");
				var subsidy_amount = $(this).attr("subsidy_amount");
				var original_amount = $(this).attr("original_amount");
				var credit_description = $(this).attr("credit_description");
				
				$(".pending-table tr").removeClass("info");
				this_row.addClass("info"); 
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/accounting/accounting/processTransaction",
					type: "post",
					dataType: "json",
					data: { 
						"ajax":1, 
						"customer_id": customer_id,
						"skill_id": skill_id,
						"amount": amount,
						"billing_period": billing_period,
						"contract": contract, 
						"credit_amount": credit_amount, 
						"subsidy_amount": subsidy_amount,
						"original_amount": original_amount,
						"credit_description": credit_description
					},
					beforeSend: function(){
							
						this_button.html("Processing...");	
						this_button.prop("disabled", true);	
						delete_button.prop("disabled", true);
						
					},
					success: function(response) {
							
						if(response.status  == "success")
						{
							this_row.fadeOut("slow", function() { $(this).remove(); });
						}
						else if(response.status  == "zeroValue")
						{
							alert(response.message)
							
							$.ajax({
								url: yii.urls.absoluteUrl + "/accounting/accounting/removeCustomer",
								type: "post",
								dataType: "json",
								data: { 
									"ajax":1, 
									"customer_id": customer_id,
									"skill_id": skill_id
								},
								beforeSend: function(){
										
									this_button.html("Removing...");	
									this_button.prop("disabled", true);		
									
								},
								success: function(response) {
										
									if(response.status  == "success")
									{
										this_row.fadeOut("slow", function() { $(this).remove(); });
									}
									else
									{
										alert(response.message);
									}
								}
							});
							
						}
						else
						{
							alert(response.message);
						}
					}
				});
			});
			
			//remove
			$(document).on("click", ".remove-transaction-btn", function(){
				
				var this_row = $(this).closest("tr");
				var this_button = $(this);
				var charge_button = this_button.prev();
				
				var customer_id = $(this).attr("customer_id");
				var skill_id = $(this).attr("skill_id");
				var contract_id = $(this).attr("contract_id");
				var amount = $(this).attr("amount");
				var billing_period = $(this).attr("billing_period");
				var contract = $(this).attr("contract");
				var customer_name = $(this).attr("customer_name");
				var credit_amount = $(this).attr("credit_amount");
				var subsidy_amount = $(this).attr("subsidy_amount");
				var original_amount = $(this).attr("original_amount");
				var transaction_type = $(this).attr("transaction_type");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/accounting/accounting/removeCustomer",
					type: "post",
					dataType: "json",
					data: { 
						"ajax":1, 
						"customer_id": customer_id,
						"skill_id": skill_id,
						"contract_id": contract_id,
						"amount": amount,
						"billing_period": billing_period,
						"contract": contract,
						"customer_name": customer_name,
						"credit_amount": credit_amount,
						"subsidy_amount": subsidy_amount,
						"original_amount": original_amount,
						"transaction_type": transaction_type,
					},
					success: function(response) {
						
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
							
							if( modal.find(".autosize-transition").val() == "" )
							{
								errors++;
								
								alert("Note is required.");
								
								return false;
							}
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize();
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/accounting/accounting/removeCustomer",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
							
										// this_button.html("Removing...");	
										// this_button.prop("disabled", true);	
										
									},
									success: function(response){
										
										if(response.status  == "success")
										{
											this_row.fadeOut("slow", function() { $(this).remove(); });
										}
										else
										{
											alert(response.message);
										}
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal ", function(){
							modal.remove();
						});
					}
				});
			});
			
			//process all button
			$(document).on("click", ".btn-auto-billing", function(){
				
				var this_row = $(this).closest("tr");
				var this_button = $(this);
				
				this_button.html("Processing...");
				this_button.prop("disabled", true);
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/accounting/accounting/autoBilling",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					complete: function(){
						
						this_button.html("Process All");
						this_button.prop("disabled", false);
						
						alert("Billing process is done.");
						
					},
					success: function(response) {
						this_button.html("Process All");
						this_button.prop("disabled", false);
					}
				});
			});
			
			
			//schedule billing
			$(document).on("click", ".btn-schedule-billing", function(){

				var this_button = $(this);

				$.ajax({
					url: yii.urls.absoluteUrl + "/accounting/accounting/scheduleBilling",
					type: "post",
					dataType: "json",
					data: { 
						"ajax":1, 
					},
					success: function(response) {
						
						if(response.status  == "success")
						{
							modal = response.html;
						}
						else
						{
							return false;
						}
						
						var modal = $(modal).appendTo("body");
						
						modal.find("button[data-action=save]").on("click", function() {

							var errors = 0;
							
							if( errors == 0 )
							{
								data = modal.find("form").serialize();
								
								$.ajax({
									url: yii.urls.absoluteUrl + "/accounting/accounting/scheduleBilling",
									type: "post",
									dataType: "json",
									data: data,
									beforeSend: function(){
							
										// this_button.html("Removing...");	
										// this_button.prop("disabled", true);	
										
									},
									success: function(response){
										
										if(response.status  == "success")
										{
											
										}
										else
										{
											alert(response.message);
										}
										
										modal.modal("hide");
									},
								});
							}
						});
						
						modal.modal("show").on("hidden.bs.modal ", function(){
							modal.remove();
						});
					}
				});
			});
			
		});
		
	', CClientScript::POS_END);
	
	$scheduledBillingSettings = CustomerBillingScheduledSettings::model()->findByPk(1);
	
	if( $scheduledBillingSettings->ongoing == 0 )
	{
		$processAllHtmlBtn = '<button class="btn btn-sm btn-primary btn-auto-billing">Process All</button>';
	}
	else
	{
		$processAllHtmlBtn = '<button class="btn btn-sm btn-primary" disabled>Processing...</button>';
	}
	
	$scheduleHtmlBtn = '<button class="btn btn-sm btn-primary btn-schedule-billing">Schedule</button>';
	$pauseHtmlBtn = '<button class="btn btn-sm btn-primary btn-pause-billing">Pause</button>';
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<div class="col-sm-4">
				<h1>
					Billing Window
					<?php echo $processAllHtmlBtn; ?>
					
					<?php if( Yii::app()->user->account->id == 2 ) : ?>
					
						<?php echo $scheduleHtmlBtn; ?>
						
						<?php echo $pauseHtmlBtn; ?>
						
					<?php endif; ?>
				</h1>
			</div>
			
			<div class="col-sm-3">
				Pending - <?php echo $pendingBillingsTotalAmount; ?>
			</div>
			
			<div class="col-sm-3">
				Declined - <?php echo $declinedBillingsTotalAmount; ?>
			</div>
			
			<div class="col-sm-2 text-right">
				<?php 
					if( date('M Y') == $billingPeriod )
					{
						echo CHtml::link('&larr; ' . date('F', strtotime('-1 month')) . ' Billing', array('billingWindow', 'billingPeriod'=>date('M Y', strtotime('-1 month'))), array('class'=>'btn btn-primary btn-white btn-sm'));
					}
					else
					{
						echo CHtml::link('&rarr; ' . date('F') . ' Billing', array('billingWindow'), array('class'=>'btn btn-primary btn-white btn-sm'));
					}
					
					// if( $billingPeriod == 'Aug 2017' )
					// {
						// echo '&nbsp;&nbsp;&nbsp;&nbsp;';
						// echo CHtml::link('Sep 2017 Billing &rarr;', array('billingWindow', 'billingPeriod'=>'Sep 2017'), array('class'=>'btn btn-primary btn-white btn-sm'));
					// }
				?>
			</div>
			
			<br style="clear:both;">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
	
		<div class="tabbable">
		
			<ul id="myTab" class="nav nav-tabs">
				<?php if( Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_tab','visible') ){ ?>
					<li class="<?php echo Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_tab','visible') ? 'active' : ''; ?>">
						<a href="#charge" data-toggle="tab">
							Pending
							<span class="badge badge-success"><?php echo $pendingBillingsCount; ?></span>
						</a>
					</li>
				<?php } ?>

				<?php if( Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_tab','visible') ){ ?>
					<li class="<?php echo !Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_tab','visible') && Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_tab','visible') ? 'active' : ''; ?>">
						<a href="#declines" data-toggle="tab">
							Declined
							<span class="badge badge-danger"><?php echo $declinedBillingsCount; ?></span>
						</a>
					</li>
				<?php } ?>
			</ul>
		
			<div class="tab-content">
				<div class="tab-pane fade in<?php echo Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_tab','visible') ? ' active' : ''; ?>" id="charge">
				
					<div class="row">
						<div class="col-sm-12 text-right">
							<?php 
								if( Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_export_button','visible') )
								{
									echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('billingWindowExport', 'type'=>'pending', 'billingPeriod'=>$billingPeriod), array('class'=>'btn btn-yellow btn-sm')); 
								}
							?>
						</div>
					</div>
					
					<div class="space-6"></div>
				
					<table class="table table-bordered table-striped table-hover table-condensed pending-table">
						<tr>
							<td class="center">#</td>
							<th>Customer ID</th>
							<th>Customer Name</th>
							<th>Contract</th>
							<th>Skill Status</th>
							<th class="center">Quantity</th>
							<th class="center">Original Amount</th>
							<th class="center">Billing Credit</th>
							<th class="center">Subsidy</th>
							<th class="center">Reduced Amount</th> 
							<th class="center">Month</th> 
							<th class="center" width="15%">Options</th>
						</tr>
						
						<?php 
							if( $pendingBillings )
							{
								$ctr = 1;
								
								foreach( $pendingBillings as $customerId => $pendingSkillBilling )
								{
				
									foreach($pendingSkillBilling as $pendingBilling)
									{
										$totalCreditAmount = 0;
										$customerCredits = CustomerCredit::model()->findAll(array(
											'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
											'params' => array(
												':customer_id' => $customerId,
												':contract_id' => $pendingBilling['contract_id'],
											),
										));
										
										if( $customerCredits )
										{
											foreach( $customerCredits as $customerCredit )
											{
												$creditStartDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-01');
											
												if( $customerCredit->type == 2 ) //month range
												{
													if( $customerCredit->end_month == '02' )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-28');
													}
													elseif( $customerCredit->end_month == '12' )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-31');
													}
													else
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-t');
													}
													
													if( $customerCredit->start_month >= $customerCredit->end_month )
													{
														$creditEndDate = date($customerCredit->end_year.'-'.$customerCredit->end_month.'-d', strtotime('+1 year', strtotime($creditEndDate)));
													}
												}
												else
												{
													if( $customerCredit->start_month == '02' )
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-28');
													}
													elseif( $customerCredit->start_month == '12' )
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-31');
													}
													else
													{
														$creditEndDate = date($customerCredit->start_year.'-'.$customerCredit->start_month.'-t');
													}
												}
												
												$monthBillingPeriod = explode(' ',$billingPeriod);
												$monthPeriod = date('m', strtotime("$monthBillingPeriod[0] 1 ".date('Y')));
												$startDayOfBillingPeriod = date("Y-m-d",strtotime(date('Y')."-".$monthPeriod."-1"));
												$lastDayOfBillingPeriod = date("Y-m-t", strtotime($startDayOfBillingPeriod));
																								
												if( (strtotime($startDayOfBillingPeriod) >= strtotime($creditStartDate)) && (strtotime($lastDayOfBillingPeriod) <= strtotime($creditEndDate)) )
												{
													$totalCreditAmount += $customerCredit->amount;
												}
											}
										}
										
										//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
										if($totalCreditAmount > $pendingBilling['amount'])
										{
											$totalCreditAmount = $pendingBilling['amount'] - $pendingBilling['subsidy'];
										}
										
										// if( in_array($pendingBilling['contract'], array('Farmers Per Appointment 2016 FOLIO','Farmers Per Name 2016 FOLIO')) )
										// {
											// $totalReducedAmount = $pendingBilling['amount'];
											
											// if( $totalCreditAmount < 0 )
											// {
												// $totalReducedAmount = $totalReducedAmount + $totalCreditAmount;
											// }
											// else
											// {
												// $totalReducedAmount = $totalReducedAmount - $totalCreditAmount;
											// }											
										// }
										// else
										// {
											$totalReducedAmount = $pendingBilling['amount'];
											$totalReducedAmount = $totalReducedAmount - $pendingBilling['subsidy'];
											
											if( $totalCreditAmount < 0 )
											{
												$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
											}
											else
											{
												$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
											}
										// }
										
										if( $totalReducedAmount < 0 )
										{
											$totalReducedAmount = 0;
										}
										
										$totalReducedAmount = number_format($totalReducedAmount, 2);
										
										if( $totalReducedAmount > 0 && $billingPeriod == 'Jul 2018' )
										{
											$existingScheduledBilling = CustomerBillingScheduled::model()->find(array(
												'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND contract_id = :contract_id',
												'params' => array(
													':customer_id' => $customerId,
													':skill_id' => $pendingBilling['skill_id'],
													':contract_id' => $pendingBilling['contract_id'],
												),
											));
											
											if( !$existingScheduledBilling )
											{
												$newScheduledBilling = new CustomerBillingScheduled;
												
												$newScheduledBilling->setAttributes(array(
													'account_id' => Yii::app()->user->account->id,
													'customer_id' => $customerId,
													'skill_id' => $pendingBilling['skill_id'],
													'contract_id' => $pendingBilling['contract_id'],
													'customer_name' => $pendingBilling['customer_name'],
													'contract' => $pendingBilling['contract'],
													'billing_period' => $pendingBilling['month'],
													'amount' => $totalReducedAmount,
													'original_amount' => $pendingBilling['amount'],
													'credit_amount' => $totalCreditAmount,
													'subsidy_amount' => $pendingBilling['subsidy'],
												));
												
												$newScheduledBilling->save(false);
											}
										}
										
										echo '<tr>';
										
											echo '<td class="center">'.$ctr.'</td>';
											
											echo '<td>'.$pendingBilling['agent_id'].'</td>';
											
											// echo '<td>'.CHtml::link($pendingBilling['customer_name'], array('/customer/insight/index', 'customer_id'=>$customerId), array('target'=>'_blank')).'</td>';
											echo '<td>'.CHtml::link($pendingBilling['customer_name'], array('/customer/billing/index', 'customer_id'=>$customerId), array('target'=>'_blank')).'</td>';
											
											echo '<td>'.$pendingBilling['contract'].'</td>';
											
											echo '<td>'.$pendingBilling['skill_status'].'</td>';
											
											echo '<td class="center">'.$pendingBilling['quantity'].'</td>';
											
											// if( $pendingBilling['no_billing'] != true )
												echo '<td class="center">$'.$pendingBilling['amount'].'</td>';
											// else
												// echo '<td class="center">-</td>';
											
											// if($pendingBilling['no_billing'] != true )
												echo '<td class="center">$'.$totalCreditAmount.'</td>';
											// else
												// echo '<td class="center">-</td>';
											
											// if($pendingBilling['no_billing'] != true )
												echo '<td class="center">$'.$pendingBilling['subsidy'].'</td>';
											// else
												// echo '<td class="center">-</td>';
																				
											// if($pendingBilling['no_billing'] != true )
												echo '<td class="center">$'.$totalReducedAmount.'</td>';
											// else
												// echo '<td class="center">-</td>';
											
											
											echo '<td class="center">'.$pendingBilling['month'].'</td>';
											
											if($pendingBilling['no_billing'] != true)
											{
												echo '<td class="center">';
													if( Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_charge_button','visible') )
													{
														echo '<button customer_id="'.$customerId.'"skill_id="'.$pendingBilling['skill_id'].'" amount="'.$totalReducedAmount.'" original_amount="'.$pendingBilling['amount'].'" subsidy_amount="'.$pendingBilling['subsidy'].'" credit_amount="'.$totalCreditAmount.'" billing_period="'.$pendingBilling['month'].'" contract="'.$pendingBilling['contract'].'" customer_name="'.$pendingBilling['customer_name'].'" class="btn btn-primary btn-minier process-transaction-btn"><i class="fa fa-cog"></i> Charge</button>';
													}
													
													if( Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_remove_button','visible') )
													{
														echo '<button customer_id="'.$customerId.'"skill_id="'.$pendingBilling['skill_id'].'" contract_id="'.$pendingBilling['contract_id'].'" transaction_type="Remove" amount="'.$totalReducedAmount.'" original_amount="'.$pendingBilling['amount'].'" subsidy_amount="'.$pendingBilling['subsidy'].'" credit_amount="'.$totalCreditAmount.'" billing_period="'.$pendingBilling['month'].'" contract="'.$pendingBilling['contract'].'" customer_name="'.$pendingBilling['customer_name'].'" class="btn btn-danger btn-minier remove-transaction-btn" style="margin-left:5px;"><i class="fa fa-times"></i> Remove</button>';
													}
													
												echo '</td>';
											}
											else
											{
												
												echo '<td class="center">';
													if( Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_remove_button','visible') )
													{
														echo '<button customer_id="'.$customerId.'"skill_id="'.$pendingBilling['skill_id'].'" contract_id="'.$pendingBilling['contract_id'].'" transaction_type="Remove" amount="'.$totalReducedAmount.'" original_amount="'.$pendingBilling['amount'].'" subsidy_amount="'.$pendingBilling['subsidy'].'" credit_amount="'.$totalCreditAmount.'" billing_period="'.$pendingBilling['month'].'" contract="'.$pendingBilling['contract'].'" customer_name="'.$pendingBilling['customer_name'].'" class="btn btn-danger btn-minier remove-transaction-btn" style="margin-left:5px;"><i class="fa fa-times"></i> Remove</button>';
													}
												echo '</td>';
											}
										echo '</tr>';
										
										$ctr++;
									}
								}
							}
							else
							{
								echo '<tr><td colspan="7">No result found.</td></tr>';
							}
						?>
						
					</table>
				</div>
				
				<div class="tab-pane fade<?php echo !Yii::app()->user->account->checkPermission('accounting_billing_windows_pending_tab','visible') && Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_tab','visible')  ? ' in active' : ''; ?>" id="declines">
					
					<div class="row">
						<div class="col-sm-12 text-right">
							<?php 
								if( Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_export_button','visible') )
								{
									echo CHtml::link('<i class="fa fa-file-excel-o"></i> Export to Excel', array('billingWindowExport', 'type'=>'declines', 'billingPeriod'=>$billingPeriod), array('class'=>'btn btn-yellow btn-sm')); 
								}
							?>
						</div>
					</div>
					
					<div class="space-6"></div>
					
					<table class="table table-bordered table-striped table-hover table-condensed">
						<tr>
							<th>#</th>
							<th>Customer ID</th>
							<th>Customer Name</th>
							<th>Contract</th>
							<th>Skill Status</th>
							<th class="center">Quantity</th>
							<th class="center">Original Amount</th>
							<th class="center">Billing Credit</th>
							<th class="center">Subsidy</th>
							<th class="center">Reduced Amount</th> 
							<th class="center">Month</th> 
							<th class="center" width="15%">Options</th>
						</tr>

						<?php 
							if( $declinedBillings )
							{
								$ctr = 1;
								
								foreach( $declinedBillings as $customerId => $declinedSkillBilling )
								{
									
									foreach($declinedSkillBilling as $declinedBilling)
									{
										$totalCreditAmount = 0;
										$creditDescriptions = '';
										
										$customerCredits = CustomerCredit::model()->findAll(array(
											'condition' => 'customer_id = :customer_id AND contract_id = :contract_id AND status=1',
											'params' => array(
												':customer_id' => $customerId,
												':contract_id' => $declinedBilling['contract_id'],
											),
										));
										
										if( $customerCredits )
										{
											foreach( $customerCredits as $customerCredit )
											{
												$creditStartDate = date('Y-'.$customerCredit->start_month.'-1');
												
												if( $customerCredit->type == 2 ) //month range
												{
													$creditEndDate = date('Y-'.$customerCredit->end_month.'-t');
													
													if( $customerCredit->start_month >= $customerCredit->end_month )
													{
														$creditEndDate = date('Y-m-d', strtotime('+1 year', strtotime($creditEndDate)));
													}
												}
												else
												{
													$creditEndDate = date('Y-'.$customerCredit->start_month.'-t');
												}
												
												if( (time() >= strtotime($creditStartDate)) && (time() <= strtotime($creditEndDate)) )
												{
													$totalCreditAmount += $customerCredit->amount;
													$creditDescriptions .= "Credit - " . $customerCredit->description.' - '.number_format($customerCredit->amount, 2) . "<br>";
												}
											}
										}
										
										//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
										if($totalCreditAmount > $declinedBilling['amount'])
										{
											$totalCreditAmount = $declinedBilling['amount'] - $declinedBilling['subsidy'];
										}
										
										$totalReducedAmount = ($declinedBilling['amount'] - $totalCreditAmount - $declinedBilling['subsidy']);

										if( $totalReducedAmount < 0 )
										{
											$totalReducedAmount = 0;
										}
										
										$totalReducedAmount = number_format($totalReducedAmount, 2);
										
										echo '<tr>';
										
											echo '<td class="center">'.$ctr.'</td>';
											
											echo '<td>'.$declinedBilling['agent_id'].'</td>';
											
											echo '<td>'.CHtml::link($declinedBilling['customer_name'], array('/customer/insight/index', 'customer_id'=>$customerId), array('target'=>'_blank')).'</td>';
											
											echo '<td>'.$declinedBilling['contract'].'</td>';
											
											echo '<td>'.$declinedBilling['skill_status'].'</td>';
											
											echo '<td class="center">'.$declinedBilling['quantity'].'</td>';
											
											echo '<td class="center">$'.$declinedBilling['amount'].'</td>';

											echo '<td class="center">$'.$totalCreditAmount.'</td>';
											
											echo '<td class="center">$'.$declinedBilling['subsidy'].'</td>';

											echo '<td class="center">';
												
												// echo '<pre>';
												
													// echo 'original: ' . $declinedBilling['amount'];
													// echo '<br>';
													// echo 'credit: ' . $totalCreditAmount;
													// echo '<br>';
													// echo 'subsidy: ' . $declinedBilling['subsidy'];
													// echo '<br>';
													
												// echo '</pre>';
												
												echo '$'.$totalReducedAmount;
												
											echo '</td>';
											
											echo '<td class="center">'.$declinedBilling['month'].'</td>';
											
											echo '<td class="center">';
												if( Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_charge_button','visible') )
												{
													echo '<button customer_id="'.$customerId.'"skill_id="'.$declinedBilling['skill_id'].'" amount="'.$totalReducedAmount.'" original_amount="'.$declinedBilling['amount'].'" subsidy_amount="'.$declinedBilling['subsidy'].'" credit_amount="'.$totalCreditAmount.'" billing_period="'.$declinedBilling['month'].'" contract="'.$declinedBilling['contract'].'" customer_name="'.$declinedBilling['customer_name'].'" credit_description="'.$creditDescriptions.'" class="btn btn-primary btn-minier process-transaction-btn"><i class="fa fa-cog"></i> Charge</button>';
												}
												
												if( Yii::app()->user->account->checkPermission('accounting_billing_windows_decline_write_off_button','visible') )
												{
													echo '<button customer_id="'.$customerId.'"skill_id="'.$declinedBilling['skill_id'].'" contract_id="'.$declinedBilling['contract_id'].'" transaction_type="Write Off" amount="'.$totalReducedAmount.'" original_amount="'.$declinedBilling['amount'].'" subsidy_amount="'.$declinedBilling['subsidy'].'" credit_amount="'.$totalCreditAmount.'" billing_period="'.$declinedBilling['month'].'" contract="'.$declinedBilling['contract'].'" customer_name="'.$declinedBilling['customer_name'].'" class="btn btn-danger btn-minier remove-transaction-btn" style="margin-left:5px;"><i class="fa fa-times"></i> Write Off</button>';
												}
												
											echo '</td>';
											
										echo '</tr>';
										
										$ctr++;
									}
								}
							}
							else
							{
								echo '<tr><td colspan="7">No result found.</td></tr>';
							}
						?>
					
					</table>
				</div>
			</div>
		
		</div>
		
	</div>

</div>