<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerScriptFile($baseUrl . '/js/plupload/plupload.full.js');
	$cs->registerScriptFile($baseUrl . '/js/customer/history/customer_history_multiple_uploader.js');
	
	$cs->registerCss(uniqid(), '
		.percentage { font-size:12px; font-weight:normal; }
	');
	
	$cs->registerScript(uniqid(), '

		$(document).ready( function(){
			// setInterval(function(){ $.fn.yiiListView.update("historyList", {}); }, 1000);
			
			$(document).on("change", ".ace", function(){
				
				if( $(this).is(":checked") == false )
				{
					$.fn.yiiListView.update("historyList",  { data: {filter: "user"} });
				}
				else
				{
					$.fn.yiiListView.update("historyList", { data: {filter: "all"} });
				}
				
			});
			
			$(document).on("click", ".customer-history-submit-btn", function(){
				
				var this_button = $(this);
				
				var formSending = false;
				
				var data = $("form#customerHistoryForm").serialize();
				
				if( !formSending && $.trim($("#customerHistoryTextArea").val()) != "" )
				{
					formSending = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/history/create",
						type: "post",
						dataType: "json",
						data: data,
						beforeSend: function(){							
							this_button.html("Saving Please Wait...");
						},
						success: function(response){
							
							$.fn.yiiListView.update("historyList", {});
							
							$("#customerHistoryTextArea").val("");
							$(".filelist").empty();
							
							formSending = false;
							this_button.html("Submit");
						},
					});
				}
				
			});
		});
		
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.CompanySideMenu",array(
		'active'=> 'history',
		'company' => $company,
	));
?>

<div class="page-header">
	<h1>
		History
		
		<?php if(Yii::app()->user->account->getIsAdmin()){ ?>
		<label>
			<small>
				<input type="checkbox" class="ace ace-switch ace-switch-1" id="skip-validation" value="1" checked="">
				<span class="lbl middle"></span>
				
				Show Audit Records
			</small>
		</label>
		<?php } ?>
	</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php 
			$this->widget('zii.widgets.CListView', array(
				'id'=>'historyList',
				'dataProvider'=>$dataProvider,
				'itemView'=>'_list',
				'template'=>'<div class="profile-feed" style="height:300px; overflow:auto;">{items}</div>',
			)); 
		?>		
	</div>
</div>

<div class="space-12"></div>
<div class="space-12"></div>

<?php if(Yii::app()->user->account->getIsAdmin()){ ?>
<form id="customerHistoryForm">

	<input type="hidden" name="CustomerHistory[customer_id]" value="<?php echo $customer->id; ?>">

	<h4>
		Add Record
		
		<span id="sources">	
			<a id="plupload-select-files" class="btn btn-info btn-minier" href="#"> 
				<i class="icon-plus"></i>
				Initializing uploader, please wait...
			</a>

			<span class="filelist" style="margin-left:15px;">
				<?php //<span class="label label-white label-inverse">Test attached file 1.txt <a href="#" class="remove-file-link"><i class="fa fa-times red"></i></a></span> ?>
			</span>
		</span>
	</h4>

	<div class="row">
		<div class="col-sm-12">
			<textarea class="col-sm-12" id="customerHistoryTextArea" name="CustomerHistory[content]" placeholder="Add New Note..."> </textarea>

			<div class="clearfix"></div>
			
			<div class="row-fluid" style="text-align:right; margin-top:10px;">	
				<button type="button" class="btn btn-primary btn-xs customer-history-submit-btn">Submit</button>
			</div>
		</div>
	</div>
</form>
<?php } ?>

