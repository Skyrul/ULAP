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
	
	
',CClientScript::POS_END);
?>

<table class="table table-striped">

		<tr>
			<th> Disposition Name </th>
			<th> Number of Leads </th>
			<th> Action </th>
		</tr>
		
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
		
			if(empty($list_id)) // this view is use by customer/insight && customer/leads controller
			{
				echo '<tr>';
					echo "<td>Non-Completed Leads</td>";
					echo "<td>".$leadRecertifyCount."</td>";
					echo "<td>";
						//echo CHtml::link('Recertify',array('insight/recertify','customer_id' => $customer->id,'list_id' => $list_id),array('class'=>'btn btn-xs btn-info btn-recertify'));
					echo "</td>";
				echo '</tr>';
			}
		?>
		
		<?php 
			foreach($leadRecertifyGroupedCount as $leadRecertifyGrouped)
			{
				echo '<tr>';
					echo "<td>".$leadRecertifyGrouped->list->name."</td>";
					echo "<td>".$leadRecertifyGrouped->ctr."</td>";
					echo "<td>";
						echo CHtml::link('Recertify',array('insight/recertify','customer_id' => $customer->id,'list_id' => $leadRecertifyGrouped->list_id, 'page' => $page),array('class'=>'btn btn-xs btn-info btn-recertify','data-days' => $leadRecertifyGrouped->list->skill->max_lead_life_before_recertify));
					echo "</td>";
				echo '</tr>';
			}
		?>
		
</table>