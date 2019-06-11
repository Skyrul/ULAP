<?php
	$this->pageTitle = 'Engagex - State Cellphone Scrub';
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript( uniqid(), '
	
		$(document).ready(function(){
			
			$(document).on("click", ".save-btn", function(){
				
				this_button = $(this);
				
				data = $("form").serialize() + "&ajax=1";
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/admin/stateCellPhoneDnc/index",
					type: "post",
					dataType: "json",
					data: data,
					beforeSend: function(){ 
						this_button.html("Saving please wait...");
					},
					success: function( response ){
						this_button.html("Save <i class=\"fa fa-check\"></i>");
					}
				});
			});
		});
	', CClientScript::POS_END);
?>

<div class="page-header">
	<h1>State Cellphone DNC</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<form action="" method="post">
			<?php
				if( $states )
				{
					foreach( $states as $state )
					{
						$existingSettings = StateDncSettings::model()->find(array(
							'condition' => 'state_id = :state_id',
							'params' => array(
								':state_id' => $state->id,
							),
						));
						
						$checked = !empty($existingSettings) ? 'checked' : '';
						
						echo '
							<div class="col-sm-2">
								<div class="checkbox">
									<label>
										<input name="states[]" class="ace" type="checkbox" value="'.$state->id.'" '.$checked.'>
										<span class="lbl"> '.$state->name.' ('.$state->abbreviation.')</span>
									</label>
								</div>
							</div>
						';
					}
				}
				
			?>
		</form>
	</div>
</div>

<div class="space-12"></div>

<div class="clearfix form-actions center">
	<div class="col-sm-12">
		<button type="button" class="btn btn-sm btn-primary save-btn">Save</button>
	</div>
</div>