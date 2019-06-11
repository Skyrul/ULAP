<div class="tabpanel-table">

	<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
		<div class="form-search">
			<span class="input-icon">
				<input type="text" name="leadSearchQuery" autocomplete="off" id="lead-search-input" class="nav-search-input" placeholder="Search Leads..." style="width:200px;">
				<i class="ace-icon fa fa-search nav-search-icon"></i>
			</span>
			
			<span id="leadSearchLoader" style="display:none;">Searching, please wait...</span>
		</div>
	</div>
	
	<br>
	
	<div id="leadSearch">			

		<?php $this->renderPartial('leadTabSearch',array(
			'leads' => $leads,
			'leadsByMemberNumber' => $leadsByMemberNumber,
		)); ?>
	</div>
</div>
<?php 
	Yii::app()->clientScript->registerScript('hostLeadSearch','
		$(function() {
			var delay = (function(){
			  var timer = 0;
			  return function(callback, ms){
				clearTimeout (timer);
				timer = setTimeout(callback, ms);
			  };
			})();

		
			$("#lead-search-input").on("keyup", function(e) {
				
				var search_query = $("#lead-search-input").val();
				
				delay(function(){
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/hostDial/leads/index",
						type: "GET",
						// dataType: "json",
						data: {
							"customer_id": '.$customer_id.',
							"search_query": search_query,
							"leadSearch": 1
						},
						beforeSend: function() {
							$("#leadSearchLoader").show();
						},
						complete: function(){
							 $("#leadSearchLoader").hide();
						},
						success: function(result) {
							$("#leadSearch").empty().append(result);
						}
					});
				}, 1000 );
		
				
			});
		});
		
	',CClientScript::POS_END);
?>