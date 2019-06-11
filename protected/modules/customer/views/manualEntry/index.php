<?php 
	$this->pageTitle = 'Engagex - Leads - Manual Entry';

	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerCss(uniqid(), '
	
		.pager-container{ border-top:1px solid #ccc; padding:15px; margin-bottom:15px; }
		.pagination { margin:0 !important; }
		.summary { text-align:left !important; }
	');
	
	$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');
	
	$cs->registerScript(uniqid(), '

		var customer_id = "'.$customer_id.'";
		var list_id = "'.$list->id.'";
		
	', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(), "
		$.mask.definitions['~']='[+-]';
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-zip').mask('99999');
		
	", CClientScript::POS_END);
	
	$cs->registerScript(uniqid(), '
		$(document).ready( function(){
			
			var ajaxProcessing = false;
			
			$(document).on("focusout", ".manual-entry-first-name, .manual-entry-last-name, .manual-entry-home-phone, .manual-entry-mobile-phone", function(){	

				var this_row = $(this).closest("tr");
			
				var lead_id = this_row.attr("lead_id");
				var first_name = this_row.find(".manual-entry-first-name").val();
				var last_name = this_row.find(".manual-entry-last-name").val();
				var home_phone_number = this_row.find(".manual-entry-home-phone").val().replace(/[^0-9]/gi, "");
				var mobile_phone_number = this_row.find(".manual-entry-mobile-phone").val().replace(/[^0-9]/gi, "");
				
				if( !ajaxProcessing )
				{
					ajaxProcessing = true;
					
					$.ajax({
						url: yii.urls.absoluteUrl + "/customer/lists/manualEntry",
						type: "post",
						dataType: "json",
						data: {
							"ajax": 1,	
							"autosave": 1,
							"customer_id": customer_id,
							"list_id": list_id,
							"lead_id": lead_id,
							"first_name": first_name,
							"last_name": last_name,
							"home_phone_number": home_phone_number,
							"mobile_phone_number": mobile_phone_number,
						},
						success: function(response) {
						
							console.log( response );
						
							if( response.lead_id != "undefined" && response.lead_id != "" )
							{
								this_row.attr("lead_id", response.lead_id);
							}
							
							if( response.errors.home_phone_number != "undefined" && response.errors.home_phone_number != "" )
							{
								if( this_row.find(".manual-entry-home-phone").parent().find("span").length > 0 )
								{
									this_row.find(".manual-entry-home-phone").parent().find("span").html(response.errors.home_phone_number);
								}
								else
								{
									this_row.find(".manual-entry-home-phone").parent().append("<span class=\"red\">" + response.errors.home_phone_number + "</span>");
								}
							}
							
							if( response.errors.mobile_phone_number != "undefined" && response.errors.mobile_phone_number != "" )
							{
								if( this_row.find(".manual-entry-mobile-phone").parent().find("span").length > 0 )
								{
									this_row.find(".manual-entry-mobile-phone").parent().find("span").html(response.errors.mobile_phone_number);
								}
								else
								{
									this_row.find(".manual-entry-mobile-phone").parent().append("<span class=\"red\">" + response.errors.mobile_phone_number + "</span>");
								}
							}
							
							ajaxProcessing = false;
						}
					});
				}
			});
			
			$(document).on("click", ".btn-form-submit", function(){
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/customer/lists/manualEntry",
					type: "post",
					dataType: "json",
					data: {
						"ajax": 1,	
						"customer_id": customer_id,
						"list_id": list_id,
						"formSubmit": 1,
					},
					success: function(response) {
					
					}
				});
				
			});
			
		});
		
	', CClientScript::POS_END);
?>


<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id ? Customer::model()->findByPk($customer_id) : null,
	));
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<div class="page-header">
	<h1>
		Manual Entry
		<button class="btn btn-sm btn-primary btn-add-tbl-rows"><i class="fa fa-plus"></i> Add 50 Rows</button>
	</h1>
</div>

<div class="row">
	<div class="col-xs-12">
		<table id="leadsTbl" class="table table-bordered table-condensed table-hover">
			<thead>
				<th></th>
				<th class="center">First Name</th>
				<th class="center">Last Name</th>
				<th class="center">Mobile Phone</th>
				<th class="center">Home Phone</th>
			</thead>
			<tbody>
			
				<?php 
					$rowsHtml = '';
					
					for($ctr = 1; $ctr<=50; $ctr++)
					{
						$rowsHtml .= '<tr lead_id="">';
						
							$rowsHtml .= '<td class="center">'.$ctr.'</td>';
							
							$rowsHtml .= '<td class="center"><input type="text" name="first_name" class="col-sm-12 manual-entry-first-name"></td>';
							
							$rowsHtml .= '<td class="center"><input type="text" name="last_name" class="col-sm-12 manual-entry-last-name"></td>';
							
							$rowsHtml .= '<td class="center"><input type="text" name="mobile_phone_number" class="col-sm-12 input-mask-phone manual-entry-mobile-phone"></td>';
							
							$rowsHtml .= '<td class="center"><input type="text" name="home_phone_number" class="col-sm-12 input-mask-phone manual-entry-home-phone"></td>';

						$rowsHtml .= '</tr>';
					}
					
					if( $list->isNewRecord )
					{
						echo $rowsHtml;
					}
					else
					{
						$tempLeads = LeadManualEntry::model()->findAll(array(
							'condition' => 'list_id = :list_id',
							'params' => array(
								':list_id' => $list->id
							)
						));
						
						if( $tempLeads )
						{
							$ctr = 1;
							
							foreach( $tempLeads as $tempLead )
							{
								echo '<tr lead_id="">';
									echo '<td class="center">'.$ctr.'</td>';
									
									echo '<td class="center"><input type="text" name="first_name" class="col-sm-12 manual-entry-first-name" value="'.$tempLead->first_name.'"></td>';
									
									echo '<td class="center"><input type="text" name="last_name" class="col-sm-12 manual-entry-last-name" value="'.$tempLead->last_name.'"></td>';
									
									echo '<td class="center"><input type="text" name="mobile_phone_number" class="col-sm-12 input-mask-phone manual-entry-mobile-phone" value="'.$tempLead->mobile_phone_number.'"></td>';
									
									echo '<td class="center"><input type="text" name="home_phone_number" class="col-sm-12 input-mask-phone manual-entry-home-phone" value="'.$tempLead->home_phone_number.'"></td>';

								echo '</tr>';
								
								$ctr++;
							}
							
							if( count($tempLeads) < 50 )
							{
								for($ctr = $ctr; $ctr<=50; $ctr++)
								{
									echo '<tr lead_id="">';
									
										echo '<td class="center">'.$ctr.'</td>';
										
										echo '<td class="center"><input type="text" name="first_name" class="col-sm-12 manual-entry-first-name"></td>';
										
										echo '<td class="center"><input type="text" name="last_name" class="col-sm-12 manual-entry-last-name"></td>';
										
										echo '<td class="center"><input type="text" name="mobile_phone_number" class="col-sm-12 input-mask-phone manual-entry-mobile-phone"></td>';
										
										echo '<td class="center"><input type="text" name="home_phone_number" class="col-sm-12 input-mask-phone manual-entry-home-phone"></td>';

									echo '</tr>';
								}
							}
						}
						else
						{
							echo $rowsHtml;
						}
					}
				?>

			</tbody>
		</table>
	</div><!-- /.col -->
</div><!-- /.row -->

<div class="space-12"></div>

<div class="row form-actions center">
	<button class="btn btn-sm btn-primary btn-form-submit"><i class="fa fa-save"></i> Save as list</button>
	
	<button class="btn btn-sm btn-purple">Export to my files <i class="fa fa-share"></i></button>
</div>

