<script>
	$( function(){
		
		$(document).ready( function(){
			
			$(document).on('click', '.show-div', function(){
				
				console.log( $(this).parent().find('.hidden-div') );
				
				if( $(this).parent().find('.hidden-div').is(":hidden") )
				{
					$(this).parent().find('.hidden-div').fadeIn();
				}
				else
				{
					$(this).parent().find('.hidden-div').hide();
				}
				
			});
			
		});
		
	});
</script>

<?php

$lists = Lists::model()->findAll(array(
	// 'condition' => 'id = 3296',
	'with' => 'customer',
	'condition' => '
		customer.id IS NOT NULL
		AND customer.company_id NOT IN (17, 18, 23)
		AND t.status !=4
		AND DATE(t.date_created) >= "2016-07-15"
	',
	// 'limit' => 800,
));

echo '<table class="table table-bordered table-striped table-condensed table-hover">';

echo '<thead>';
	echo '<th>#</th>';
	echo '<th>List Name</th>';
	echo '<th>Customer Name</th>';
	echo '<th>Office Phone</th>';
	echo '<th>Total Leads in List</th>';
	echo '<th>10% of Total Lead</th>';
	echo '<th>Leads with Different Area Code</th>';
	echo '<th width="30%"></th>';
echo '</thead>';

if( $lists )
{
	$listCtr = 1;
	
	foreach( $lists as $list )
	{
		if( isset($list->customer) && isset($list->calendar) && isset($list->calendar->office) )
		{
			$leadsWithDifferentAreaCode = array();

			$customer = $list->customer;
			$customerAreaCode = substr($list->calendar->office->phone, 1, 3);
			
			$leads = Lead::model()->findAll(array(
				'condition' => 'list_id = :list_id AND type=1 AND status!=4',
				'params' => array(
					':list_id' => $list->id,
				),
			));
			
			$tenPercentOfTotalLeads = (90/100) * count($leads);
			
			if( $leads )
			{
				foreach( $leads as $lead )
				{
					if( !empty($lead->home_phone_number) && substr($lead->home_phone_number,0,3) != $customerAreaCode )
					{
						if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
						{
							$leadsWithDifferentAreaCode[$lead->id] = array(
								'lead_name' => $lead->first_name.' '.$lead->last_name,
								'phone_number' => $lead->home_phone_number
							);
						}
					}
					
					if( !empty($lead->mobile_phone_number) && substr($lead->mobile_phone_number,0,3) != $customerAreaCode )
					{
						if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
						{
							$leadsWithDifferentAreaCode[$lead->id] = array(
								'lead_name' => $lead->first_name.' '.$lead->last_name,
								'phone_number' => $lead->mobile_phone_number
							);
						}
					}
					
					if( !empty($lead->office_phone_number) && substr($lead->office_phone_number,0,3) != $customerAreaCode )
					{
						if ( !array_key_exists($lead->id, $leadsWithDifferentAreaCode) )
						{
							$leadsWithDifferentAreaCode[$lead->id] = array(
								'lead_name' => $lead->first_name.' '.$lead->last_name,
								'phone_number' => $lead->office_phone_number
							);
						}
					}
				}
				
				if( count($leadsWithDifferentAreaCode) >= $tenPercentOfTotalLeads )
				{
					echo '<tr>';
					
						echo '<td>'.$listCtr.'</td>';
						
						echo '<td>'.$list->name.'</td>';
						
						echo '<td>'.CHtml::link($customer->firstname.' '.$customer->lastname, array('/customer/insight/index', 'customer_id'=>$customer->id), array('target'=>'blank')).'</td>';
						
						echo '<td>'.$list->calendar->office->phone.'</td>';
						
						echo '<td class="center">'.count($leads).'</td>';
						
						echo '<td class="center">'.$tenPercentOfTotalLeads.'</td>';
						
						echo '<td class="center">'.count($leadsWithDifferentAreaCode).'</td>';
						echo '<td>';
						
							$popOverContent = '';
							
							$leadCtr = 1;
							
							echo '<button class="btn btn-primary btn-mini show-div">View</button>';
						
							echo '<br>';
						
							echo '<div class="hidden-div" style="display:none;">';
							
								foreach( $leadsWithDifferentAreaCode as $leadWithDifferentAreaCode )
								{
									echo $leadCtr.'. '.$leadWithDifferentAreaCode['lead_name'].' - '.$leadWithDifferentAreaCode['phone_number'];
									echo '<br>';
									
									$leadCtr++;
								}
								
							echo '</div>';
							
						echo '</td>';
						
					echo '</tr>';
					
					$listCtr++;
				}
			}
		}
	}
}

echo '</table>'

?>