<div class="modal fade">
	<div class="modal-dialog" style="width:50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-plus"></i> Add a type
				</h4>
			</div>
			
			<div class="modal-body">
			
				<div style="height:300px; max-height:300px; overflow:auto;">
					<table class="table table-bordered table-hover table-striped table-condensed tbl-document-type-list">
						<thead>
							<th>Name</th>
							<th width="15%" class="center">Active</th>
							<th width="15%" class="center">Allow Edit</th>
							<th width="15%" class="center">Show Delete Button</th>
							<th width="15%" class="center"></th>
						</thead>
						
						<tbody>
							<?php 
								if( $models )
								{
									foreach( $models as $modelType )
									{
										$activeChecked = $modelType->status == 1 ? 'checked' : '';
										$showEditChecked = $modelType->show_edit_button == 1 ? 'checked' : '';
										$showDeleteChecked = $modelType->show_delete_button == 1 ? 'checked' : '';
										
										echo '<tr id="'.$modelType->id.'">';
										
											echo '<td><input type="text" class="col-sm-12" value="'.$modelType->name.'" style="margin-top:4px"></td>';
											
											echo '<td class="center">';
												echo '
													<div class="checkbox">
														<label>
															<input name="states[]" class="ace checkbox-status" type="checkbox" value="1" '.$activeChecked.'>
															<span class="lbl"></span>
														</label>
													</div>
												';
											echo '</td>';
											
											echo '<td class="center">';
												echo '
													<div class="checkbox">
														<label>
															<input name="states[]" class="ace checkbox-show-edit" type="checkbox" value="1" '.$showEditChecked.'>
															<span class="lbl"></span>
														</label>
													</div>
												';
											echo '</td>';
											
											echo '<td class="center">';
												echo '
													<div class="checkbox">
														<label>
															<input name="states[]" class="ace checkbox-show-delete" type="checkbox" value="1" '.$showDeleteChecked.'>
															<span class="lbl"></span>
														</label>
													</div>
												';
											echo '</td>';
											
											echo '<td class="center">';
												echo '<button type="button" id="'.$modelType->id.'" class="btn btn-minier btn-danger btn-delete-document-type" style="margin-top:9px"><i class="fa fa-times"></i> Delete</button>';
											echo '</td>';
											
										echo '</tr>';
									}
								}
							?>
						</tbody>
					</table>
				</div>
			
				<div class="space-12"></div>
				
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array('class' => 'form-horizontal'),
				)); ?>
				
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row agent-field-container">
							<div class="profile-info-name">Name <span class="red">*</span></div>

							<div class="profile-info-value">
								<?php 
									echo $form->textField($model, 'name', array('class'=>'col-xs-12')); 
								?>
							</div>
						</div>
					</div>
					
					<div class="space-12"></div>
					
					<div class="center">
						<button type="button" class="btn btn-sm btn-info" data-action="save">Add</button>
					</div>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>