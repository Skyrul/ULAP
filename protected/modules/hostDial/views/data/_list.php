<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScriptFile( $baseUrl . '/js/customer_insight.js');
	
	$cs->registerCss(uniqid(), '
	
		.pager-container{ border-top:1px solid #ccc; padding:15px; margin-bottom:15px; }
		.pagination { margin:0 !important; }
		.summary { text-align:left !important; }
	');
	
	$cs->registerScript(uniqid(), '
	
		var customer_id;
	
		function updateCounters() {
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/customer/insight/index?customer_id=" + customer_id,
				type: "post",
				dataType: "json",
				data: { "ajax":"getCount" },
				success: function(response){
					
					if( response.action_center > 0 )
					{
						$(".action-center-count").html("<span class=\"red\">(" + response.action_center + ")");
					}
					else
					{
						$(".action-center-count").empty();
					}
					
					
					if( response.schedule_conflict > 0 )
					{
						$(".schedule-conflict-count").html("<span class=\"red\">(" + response.schedule_conflict + ")");
					}
					else
					{
						$(".schedule-conflict-count").empty();
					}
					
					
					if( response.location_conflict > 0 )
					{
						$(".location-conflict-count").html("<span class=\"red\">(" + response.location_conflict + ")");
					}
					else
					{
						$(".location-conflict-count").empty();
					}
				},
			});
			
		}
		
	
	', CClientScript::POS_HEAD);
	
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			$(document).on("keyup", ".customer-search-input", function(e) {
		
				e.preventDefault();
				
				var search_query = $(".customer-search-input").val();
				var search_filter = $(".customer-search-filter.active").find(":radio").val();
				
				$.fn.yiiListView.update("customerList", { data: { search_query: search_query, search_filter:search_filter } });
			});
			
			$(document).on("click", ".customer-search-filter", function(e){
				
				e.preventDefault();
				
				var search_query = $(".customer-search-input").val();
				var search_filter = $(this).find(":radio").val();
	
				$.fn.yiiListView.update("customerList", { data: { search_query: search_query, search_filter:search_filter } });
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php /*
	$cs->registerScript('summaryAjax','
	$(document).on("click", ".customer-summary", function(e){
		
		customer_id = $(this).prop("id");
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/customer/insight/index?customer_id=" + customer_id,
			type: "post",
			dataType: "json",
			data: { "ajax":"customerSummary" },
			success: function(response){
				
				$("#summary-container").html(response.html);
				
			}
				
		});
	});
	
	$(".btn-customer-manage").click(function(){alert("Feature not yet available."); });
'
,CClientScript::POS_END); 
*/ ?>

<?php $cs->registerScript('staffListAjax','
	$(document).on("click", ".customer-summary", function(e){
		
		customer_id = $(this).prop("id");
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/customer/insight/staffList?customer_id=" + customer_id,
			type: "post",
			dataType: "json",
			data: { "ajax":"customerSummary" },
			success: function(response){
				
				$("#summary-container").html(response.html);
				
			}
				
		});
	});
	
	$(".btn-customer-manage").click(function(){alert("Feature not yet available."); });
'
,CClientScript::POS_END); ?>

<?php 
	if( Yii::app()->user->account->account_type_id == Account::TYPE_GAMING_PROJECT_MANAGER )
	{
		$showNewOnlyLabel = 'New Hosts Only';
		$searchPlaceHolder = 'Search Hosts...';
	}
	else
	{
		$showNewOnlyLabel = 'New Customers Only';
		$searchPlaceHolder = 'Search Customers...';
	}
?>

<div class="row">
	<div class="col-md-7">
		<div class="row">
			<form id="customerSearchForm">
				<div class="col-md-9">
					<div class="btn-group btn-corner" data-toggle="buttons">
						
						<label class="btn btn-white btn-sm btn-primary customer-search-filter">
							<input type="radio" value="showAll">
							Show All
						</label>
						
						<label class="btn btn-white btn-sm btn-primary customer-search-filter active">
							<input type="radio" value="hideInactive" checked>
							Hide Inactives
						</label>				

						<label class="btn btn-white btn-sm btn-primary customer-search-filter">
							<input type="radio" value="showNewOnly">
							<?php echo $showNewOnlyLabel; ?>
						</label>
							
						<label class="btn btn-white btn-sm btn-primary customer-search-filter">
							<input type="radio" value="showNewFilesOnly">
							New Files Only
						</label>
						
						<label class="btn btn-white btn-sm btn-primary customer-search-filter">
							<input type="radio" value="showTests">
							Show Test/Training
						</label>
					</div>
				</div>
				<div class="col-md-3 text-right">
					<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
						<span class="input-icon">
							<input type="text" autocomplete="off" class="nav-search-input customer-search-input" placeholder="<?php echo $searchPlaceHolder; ?>" style="width:100%;">
							<i class="ace-icon fa fa-search nav-search-icon"></i>
						</span>
					</div>
				</div>
			</form>
		</div>

		<div class="space-6"></div>

		<?php
			$this->widget('zii.widgets.CListView', array(
				'id'=>'customerList',
				'dataProvider'=>$dataProvider,
				'itemView'=>'_customer_list',
				'summaryText' => 'Showing {start} - {end} of {count} records',
				'emptyText' => 'No records found.',
				'template'=>'
					<table class="table table-striped table-hover">{items}</table> 
					<div class="col-sm-12"> 
						<div class="pager-container"> 
							<div class="col-sm-6">{summary}</div> 
							<div class="col-sm-6 text-right">{pager}</div>
						</div>
					</div>
				',
				'pagerCssClass' => 'pagination', 
				'pager' => array(
					'header' => '',
				),
			)); 
		?>
	</div>
	
	
	<div class="col-md-5">
		<?php 
			if( Yii::app()->user->account->checkPermission('customer_list_of_staff_button','visible') )
			{
			?>
				<div id="summary-container">
					<div class="center">No customer selected.</div>
				</div>
				
			<?php
			}
		?>
	</div>
	
</div>