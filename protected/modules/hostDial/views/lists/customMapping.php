<?php 
	$this->pageTitle = 'Engagex - Leads - Custom Mapping';
?>

<?php 
	$this->widget("application.components.HostDialSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id != null ? Customer::model()->findByPk($customer_id) : null,
	));
?>

<?php 
	Yii::app()->clientScript->registerScript(uniqid(), '
	
		$(".custom-mapping-delete-btn").on("click", function(){
			
			if( confirm("Are you sure you want to delete this field?") )
			{
				var this_button = $(this);
				var parent_container = this_button.parent().parent();
				var field_id = $(this).prop("id");
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/hostDial/lists/ajaxDeleteCustomField",
					type: "post",
					dataType: "json",
					data: {
						"ajax": 1,
						"field_id": field_id
					},
					beforeSend: function(){ 
						this_button.html("Deleting please wait...");
					},
					success: function(response) { 

						if( response.status != "success" )
						{
							alert("Database error.")
						}
						
						parent_container.fadeOut();
					}
				});
			}
			
		});
	
	', CClientScript::POS_END);
?>

<div class="page-header">
	<h1>Custom Mapping</h1>
</div>

<div class="row">
	<div class="col-xs-12">
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

		<div class="form">
			<?php $form=$this->beginWidget('CActiveForm', array(
				'enableAjaxValidation'=>false,
				'htmlOptions' => array(
					'class' => 'form-horizontal',
				),
			)); ?>
				
				<div class="row">
					<div class="col-xs-6">
						<div class="widget-box widget-color-blue2 light-border">
							<div class="widget-header widget-header-small">
								<h4 class="widget-title lighter">List Name - <?php echo $list->name; ?></h4>

								<div class="widget-toolbar no-border"></div>
							</div>
							<div class="widget-body">
								<div class="widget-main no-padding">
									<table class="table table-bordered table-condensed table-hover">
										<tr>
											<th class="center" width="20%">Display on Form</th>
											<th class="center" width="20%">Allow Agents to Edit</th>
											<th class="center" width="10%">Order</th>
											<th class="center">Custom Field Name</th>
											<th class="center">Original Field Name</th>
											<th class="center"></th>
										</tr>
										
										<?php 
											if( $listCustomDatas )
											{
												foreach( $listCustomDatas as $listCustomData )
												{
												?>
													<tr>
														<td class="center">
															<label>
																<input class="ace" value="1" type="checkbox" name="updateListCustomDatas[<?php echo $listCustomData->id?>][display_on_form]" <?php echo $listCustomData->display_on_form == 1 ? 'checked':''; ?>>					
																<span class="lbl"> </span>
															</label>
														</td>
														
														<td class="center">
															<label>
																<input class="ace" value="1" type="checkbox" name="updateListCustomDatas[<?php echo $listCustomData->id?>][allow_edit]" <?php echo $listCustomData->allow_edit == 1 ? 'checked':''; ?>>					
																<span class="lbl"> </span>
															</label>
														</td>
														
														<td><input value="<?php echo $listCustomData->ordering; ?>" name="updateListCustomDatas[<?php echo $listCustomData->id?>][ordering]" style="width:100%; text-align:center;" type="text"></td>
														<td><input value="<?php echo $listCustomData->custom_name; ?>" name="updateListCustomDatas[<?php echo $listCustomData->id?>][custom_name]" style="width:100%; text-align:center" type="text"></td>

														<td  class="center" style="line-height:30px;"><?php echo $listCustomData->original_name; ?></td>
														
														<td class="center" style="line-height:30px;">
															<button id="<?php echo $listCustomData->id; ?>" type="button" class="btn btn-minier btn-danger custom-mapping-delete-btn"><i class="fa fa-times"></i> Delete</button>
														</td>
													</tr>
												
												<?php
												}
											}
											else
											{
												echo '<tr><td colspan="6">No custom fields found.</td></tr>';
											}
										?>
											
										<tr>
											<td colspan="6" class="center" style="background-color:#f5f5f5; padding:5px;">
												<button class="btn btn-success btn-minier"><i class="fa fa-plus"></i> Add More Field</button>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="form-actions text-center">
					<button type="submit" class="btn btn-xs btn-primary">Save <i class="fa fa-arrow-right"></i></button>
				</div>

			<?php $this->endWidget(); ?>
		</div>

	</div>
</div>