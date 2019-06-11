<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	 
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css'); 
	
	$cs->registerCss(uniqid(), '
		.ui-sortable {
			border: 1px solid #eee;
			width: 100%;
			min-height: 40px;
			list-style-type: none;
			margin: 0;
			padding: 5px 0 0 0;
			margin-right: 10px;
		}

		.ui-sortable li {
			margin: 0 5px 5px 5px;
			padding: 5px;
			font-size: 1.2em;
			width: 95%;
		}
	');
	
	$cs->registerScriptFile( $baseUrl.'/js/jquery.bootstrap-duallistbox.min.js' ); 
	
	$cs->registerScript(uniqid(),'
	
		var lists_id = "'.$model->id.'";
		var skill_id = "'.$model->skill_id.'";
		
		$( "#sortableAgentsAvailable, #sortableAgentsAssigned" ).sortable({
		  connectWith: ".agentSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableAgentsAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/lists/updateListAgentAssigned";
				type = "remove";
			}
			
			if(container_id == "sortableAgentsAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/lists/updateListAgentAssigned";
				type = "add";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",	
				data: { 
					"ajax": 1, 
					"lists_id": lists_id, 
					"skill_id": skill_id,
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ },
			});
		}
		  
		}).disableSelection();
		
	',CClientScript::POS_END);
?>

<div class="row">
	<div class="col-sm-6" style="min-height:200px; border-right:1px solid #e3e3e3;">
		<div class="text-center">
			<label>Available</label>
		</div>

		<ul id="sortableAgentsAvailable" class="agentSortable">
			<?php 						
				if( $listAssignedAgents )
				{
					foreach( $listAssignedAgents as $listAssignedAgent )
					{
						unset( $availableAgents[$listAssignedAgent->account_id] );
					}
				}

				foreach( $availableAgents as $availableAgentId => $availableAgentLabel )
				{
					echo '<li class="ui-state-default" data-id="'.$availableAgentId.'" >'.$availableAgentLabel.'</li>';
				}
			?>
		</ul>
	</div>
	
	<div class="col-sm-6">
		<div class="text-center">
			<label>Assigned</label>
		</div>
		
		<ul id="sortableAgentsAssigned" class="agentSortable">
			<?php 
				if( $listAssignedAgents )
				{
					foreach( $listAssignedAgents as $listAssignedAgent )
					{
						echo '<li class="ui-state-default" data-id="'.$listAssignedAgent->account_id.'">'.$listAssignedAgent->account->getFullName().'</li>';
					}
				}
			?>											
		</ul>
	</div>
</div>