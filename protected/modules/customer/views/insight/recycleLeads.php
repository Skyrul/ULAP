<?php 
 // this view is use by customer/insight && customer/leads controller
?>

<?php 
Yii::app()->clientScript->registerScript(uniqid().'_confirm_buttons', '

	$(".btn-recycle").on("click",function(e){
		
		
		var msg = "Are you sure?";
		
		if(confirm(msg))
			return true;
		
		e.preventDefault();
	});
	
	$(".btn-recertify").on("click",function(e){
		
		var skillMaxLife = $(this).data("days");
		var msg = "I am authorizing these names for another "+skillMaxLife+" days of calling and certify that they are free of do not call restrictions.";
		
		if(confirm(msg))
			return true;
			
		
		e.preventDefault();
	});
	
	$("[data-rel=popover]").popover({
		html: true,
		content: function(){

			 return $($(this).data("contentwrapper")).html();
			
		}
	});
	
',CClientScript::POS_END);
?>

<div class="row">
	<div class="col-sm-6">
		<div class="page-header">
			<h1>RECERTIFY</h1>
			
			<div class="row" style="margin-left:-3px;">
				<div class="col-sm-12">
					In order to maintain compliance with Do Not Call laws it is important to check your list often for any new Do Not Call requests that have been received 
					by your office.  Each name that you import is valid for 30 days, after which you must recertify them.  
					
					<span class="description-container" style="display:none;">
						To recertify, begin by reviewing the names in the 
						list by clicking the number next to the list you would like to recertify.  If there are names on your list that have made a Do Not Call request, 
						press the remove button next to their name.  Once you have verified that all names are compliant with DNC rules you can simply press the recertify button to
						have those names added back into the callable names.   If you have any questions about this please contact Customer Service.
					</span>
				</div>
			</div>
			
			<a href="javascript:void(0);" class="show-description-link">
				<i class="fa fa-chevron-circle-down fa-lg blue" style="margin-left: 280px;margin-top: 10px; position: absolute;"></i>
			</a>
		</div>
		
		<table class="table table-striped table-hover table-condensed">

			<!--<tr>
				<th> Disposition Name </th>
				<th> Number of Leads </th>
				<th> Action </th>
			</tr>-->
			
			<?php 
			/* DISABLE RECYCLE FUNCTIONALITY - 7-30-2016
			if(empty($list_id)) // this view is use by customer/insight && customer/leads controller
			{
				foreach($leadRecyclesGrouped as $leadGrouped)
				{
					echo '<tr>';
						echo "<td>".$leadGrouped->recycleLeadCallHistoryDisposition->skill_disposition_name."</td>";
						echo "<td>".$leadGrouped->ctr."</td>";
						echo "<td>";
							echo CHtml::link('Recycle',array('insight/recycle','customer_id' => $customer->id, 'recycle_lead_call_history_disposition_id' => $leadGrouped->recycle_lead_call_history_disposition_id, 'page' => $page),array('class'=>'btn btn-xs btn-info btn-recycle'));
						echo "</td>";
					echo '</tr>';
				}
			} */
			?>
		
			<?php 
				// if(empty($list_id)) // this view is use by customer/insight && customer/leads controller
				// {
					// echo '<tr>';
						// echo "<td>Non-Completed Leads</td>";
						// echo "<td>".$leadRecertifyCount."</td>";
						// echo "<td>";
							// echo CHtml::link('Recertify',array('insight/recertify','customer_id' => $customer->id,'list_id' => $list_id),array('class'=>'btn btn-xs btn-info btn-recertify'));
						// echo "</td>";
					// echo '</tr>';
				// }
			?>
			
			<?php 
				if( $leadRecertifyGroupedCount )
				{
					foreach($leadRecertifyGroupedCount as $leadRecertifyGrouped)
					{
						$recertifyMessage = '';
						$now = strtotime(date('Y-m-d')); // or your date as well
						$your_date = strtotime($leadRecertifyGrouped->recertify_date);
						$datediff = $now - $your_date;

						$datediff = (30 - (floor($datediff / (60 * 60 * 24))));
						
						if( $datediff > 0 && $now < $your_date )
						{
							$recertifyMessage = "Expires in ".$datediff." day(s)";
						}
						else
						{
							$recertifyMessage = "Expired";
						}
								
						$leads = Lead::model()->findAll(array(
							'with' => 'list',
							'together' => true,
							'condition' => '
								t.list_id = :list_id 
								AND list.customer_id = :customer_id 
								AND t.status = 1 
								AND list.status = 1 
								AND t.type = 1 
								AND (
									t.recertify_date = "0000-00-00" 
									OR t.recertify_date IS NULL 
									OR NOW() >= t.recertify_date
								)',
							'params' => array(
								':list_id' => $leadRecertifyGrouped->list_id,
								':customer_id' => $customer->id,
							),
						));
						
						echo '<tr>';
							echo "<td>".$leadRecertifyGrouped->list->name."</td>";
							// echo "<td>".$leadRecertifyGrouped->ctr."</td>";
							
							echo "<td>".$recertifyMessage."</td>";
							echo '<td>'; 
							
								echo '<a href="#" data-contentwrapper="#myPopoverRecertify'.$leadRecertifyGrouped->list_id.'" data-placement="bottom" data-rel="popover" class="popover-info" data-original-title="'.$leadRecertifyGrouped->list->name.'" list_id="'.$leadRecertifyGrouped->list_id .'">';
									echo $leadRecertifyGrouped->ctr;
								echo '</a>';
								
								echo '<div id="myPopoverRecertify'.$leadRecertifyGrouped->list_id.'" class="hide">';
									
									if( $leads )
									{
										$leadCtr = 1;
										
										echo '<table class="table table-striped table-condensed tabl-hover">';
												
											foreach( $leads as $lead )
											{
												echo '<tr>';
													echo '<td>'.$leadCtr.'</td>';
													echo '<td>'.$lead->first_name.' '.$lead->last_name.'</td>';
													echo '<td width="15%"><button class="btn btn-minier btn-danger btn-recertify-remove-lead" id="'.$lead->id.'">Remove</button></td>';
												echo '</tr>';
												
												$leadCtr++;
											}
										
										echo '</table>';
									}
								
								echo '</div>';
							
							echo '</td>';
							
							echo "<td width='15%'>";
								echo '<button class="btn btn-minier btn-info btn-recertify-list-link" customer_id='.$customer->id.' list_id='.$leadRecertifyGrouped->list_id.' recertify_days="'.$leadRecertifyGrouped->list->skill->max_lead_life_before_recertify.'">';
									echo 'Recertify'; 
								echo '</button>';
							echo "</td>";
						echo '</tr>';
					}
				}
				else
				{
					echo '<tr><td colspan="3">No results found.</td></tr>';
				}
			?>
		</table>
	</div>
	
	<div class="col-sm-6">
		<div class="page-header">
			<h1>RECYCLE</h1>
			
			<div class="row" style="margin-left:-3px;">
				<div class="col-sm-12">
					To prevent customers who have declined appointments or who have similar call dispositions from being call too often, Engagex requires that a certain amount of time pass 
					before another call is made on your behalf. Once that minimum time period has passed, the customer will become eligible for Recycling.  
	
					<span class="description-container" style="display:none;">
						To recycle those names, click on 
						the number next to the disposition and you will see the list of customers who are eligible.  Remove any customers you do not want called again or who have made Do Not Call 
						requests by simply pressing the Remove button.  After the list has been validated, press Recycle and they will be added back into the calling queue.  
						If you have any questions about this please contact Customer Service.  
					</span>
				</div>
			</div>
			
			<a href="javascript:void(0);" class="show-description-link">
				<i class="fa fa-chevron-circle-down fa-lg blue" style="margin-left: 280px;margin-top: 10px; position: absolute;"></i>
			</a>
		</div>
		
		<table class="table table-striped table-hover table-condensed">

			<!--<tr>
				<th> Disposition Name </th>
				<th> Number of Leads </th>
				<th> Action </th>
			</tr>-->
			
			<?php	
				if( $leadRecyclesGrouped )
				{
					foreach($leadRecyclesGrouped as $leadGrouped)
					{
						$leads = Lead::model()->findAll(array(
							'with' => array('list', 'list.skill'),
							'together' => true,
							'condition' => '
								t.customer_id = :customer_id 
								AND list.status = 1 
								AND t.type = 1 
								AND t.is_do_not_call = 0
								AND t.recycle_lead_call_history_id IS NOT NULL
								AND t.recycle_lead_call_history_disposition_id = :recycle_lead_call_history_disposition_id
								AND is_recycle_removed = 0
								AND (
									recycle_date IS NULL
									OR recycle_date = "0000-00-00"
									OR NOW() >= recycle_date 
								)
								AND ( 
									t.status = 3
									OR t.number_of_dials >= (skill.max_dials * 3)
								)
							',
							'params' => array(
								':customer_id' => $customer->id,
								':recycle_lead_call_history_disposition_id' => $leadGrouped->recycle_lead_call_history_disposition_id,
							),
						));
						
						echo '<tr>';
							
							if( $leadGrouped->recycle_lead_call_history_disposition_id == 5 )
							{
								echo '<td>Appointment Cancelled - Not Interested</td>';
							}
							else
							{
								echo "<td>".$leadGrouped->recycleLeadCallHistoryDisposition->skill_disposition_name."</td>";
							}
							
							echo '<td>'; 
							
								echo '<a href="#" data-contentwrapper="#myPopoverRecycle'.$leadGrouped->recycle_lead_call_history_disposition_id.'" data-placement="bottom" data-rel="popover" class="popover-info" data-original-title="'.$leadGrouped->recycleLeadCallHistoryDisposition->skill_disposition_name.'" recycle_lead_call_history_disposition_id="'.$leadGrouped->recycle_lead_call_history_disposition_id .'">';
									echo $leadGrouped->ctr;
								echo '</a>';
								
								echo '<div id="myPopoverRecycle'.$leadGrouped->recycle_lead_call_history_disposition_id.'" class="hide">';
									
									if( $leads )
									{
										$leadCtr = 1;
										
										echo '<table class="table table-striped table-condensed tabl-hover">';
												
											foreach( $leads as $lead )
											{
												echo '<tr>';
													echo '<td>'.$leadCtr.'</td>';
													echo '<td>'.$lead->first_name.' '.$lead->last_name.'</td>';
													echo '<td width="15%"><button class="btn btn-minier btn-danger btn-recycle-remove-lead" id="'.$lead->id.'">Remove</button></td>';
												echo '</tr>';
												
												$leadCtr++;
											}
										
										echo '</table>';
									}
								
								echo '</div>';
							
							echo '</td>';
							
							echo "<td width='15%'>";
								echo '<button class="btn btn-minier btn-info btn-recycle-link" customer_id='.$customer->id.' recycle_lead_call_history_disposition_id='.$leadGrouped->recycle_lead_call_history_disposition_id.'>';
									echo 'Recycle'; 
								echo '</button>';
							echo "</td>";
						echo '</tr>';
					}
				}
				else
				{
					echo '<tr><td colspan="3">No results found.</td></tr>';
				}
			?>
		</table>
	</div>
</div>

