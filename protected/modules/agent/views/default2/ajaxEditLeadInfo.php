<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-calendar"></i> 
					Edit Lead Info
				</h4>
			</div>
			
			<div class="modal-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
				<?php echo CHtml::hiddenField('lead_id', $model->id); ?>
				<?php echo CHtml::hiddenField('field_name', $fieldName); ?>
				
				<?php if( $fieldName == 'lead_name' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'first_name'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[first_name]" value="<?php echo $model->first_name; ?>">
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'last_name'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[last_name]" value="<?php echo $model->last_name; ?>">
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'partner_name' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'partner_first_name'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[partner_first_name]" value="<?php echo $model->partner_first_name; ?>">
						</div>
					</div>
					
					<br />
					
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'partner_last_name'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[partner_last_name]" value="<?php echo $model->partner_last_name; ?>">
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'email_address' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'email_address'); ?></label>
						<div class="col-sm-6">
							<input type="text" id="leadEmailAddressInput" class="middle col-xs-12" name="Lead[email_address]" value="<?php echo $model->email_address; ?>">
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'address' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'address'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[address]" value="<?php echo $model->address; ?>">
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'home_phone_number' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'home_phone_number'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12 input-mask-phone" name="Lead[home_phone_number]" value="<?php echo $model->home_phone_number; ?>">
						</div>
					</div>
					
					<br />
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'home_phone_label'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[home_phone_label]" value="<?php echo $model->home_phone_label; ?>">
						</div>
					</div>
					
					<br />
				
					<input type="hidden" name="Lead[current_phone_type]" value="<?php echo $fieldName; ?>">
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Type</label>
						<div class="col-sm-6">
							<select name="Lead[new_phone_type]">
								<option value="home_phone_number" <?php echo $fieldName == 'home_phone_number' ? 'selected' : ''; ?>>Home</option>
								<option value="mobile_phone_number" <?php echo $fieldName == 'mobile_phone_number' ? 'selected' : ''; ?>>Mobile</option>
								<option value="office_phone_number" <?php echo $fieldName == 'office_phone_number' ? 'selected' : ''; ?>>Office</option>
							</select>
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'mobile_phone_number' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'mobile_phone_number'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12 input-mask-phone" name="Lead[mobile_phone_number]" value="<?php echo $model->mobile_phone_number; ?>">
						</div>
					</div>
					
					<br />
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'mobile_phone_label'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[mobile_phone_label]" value="<?php echo $model->mobile_phone_label; ?>">
						</div>
					</div>
					
					<br />
					
					<input type="hidden" name="Lead[current_phone_type]" value="<?php echo $fieldName; ?>">
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Type</label>
						<div class="col-sm-6">
							<select name="Lead[new_phone_type]">
								<option value="home_phone_number" <?php echo $fieldName == 'home_phone_number' ? 'selected' : ''; ?>>Home</option>
								<option value="mobile_phone_number" <?php echo $fieldName == 'mobile_phone_number' ? 'selected' : ''; ?>>Mobile</option>
								<option value="office_phone_number" <?php echo $fieldName == 'office_phone_number' ? 'selected' : ''; ?>>Office</option>
							</select>
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'office_phone_number' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'office_phone_number'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12 input-mask-phone" name="Lead[office_phone_number]" value="<?php echo $model->office_phone_number; ?>">
						</div>
					</div>
					
					<br />
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right"><?php echo $form->labelEx($model, 'office_phone_label'); ?></label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[office_phone_label]" value="<?php echo $model->office_phone_label; ?>">
						</div>
					</div>
					
					<br />
					
					<input type="hidden" name="Lead[current_phone_type]" value="<?php echo $fieldName; ?>">
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Type</label>
						<div class="col-sm-6">
							<select name="Lead[new_phone_type]">
								<option value="home_phone_number" <?php echo $fieldName == 'home_phone_number' ? 'selected' : ''; ?>>Home</option>
								<option value="mobile_phone_number" <?php echo $fieldName == 'mobile_phone_number' ? 'selected' : ''; ?>>Mobile</option>
								<option value="office_phone_number" <?php echo $fieldName == 'office_phone_number' ? 'selected' : ''; ?>>Office</option>
							</select>
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php if( $fieldName == 'manual_dial_phone_number' ): ?>
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Manual</label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12 input-mask-phone" name="Lead[manual_dial_phone_number]" value="<?php echo $_POST['phone_number']; ?>">
						</div>
					</div>
					
					<br />
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Phone Description</label>
						<div class="col-sm-6">
							<input type="text" class="middle col-xs-12" name="Lead[manual_dial_phone_label]" value="">
						</div>
					</div>
					
					<br />
					
					<input type="hidden" name="Lead[current_phone_type]" value="<?php echo $fieldName; ?>">
				
					<div class="row">
						<label class="col-sm-3 control-label no-padding-right">Type</label>
						<div class="col-sm-6">
							<select name="Lead[new_phone_type]">
								<option value="home_phone_number" <?php echo $fieldName == 'home_phone_number' ? 'selected' : ''; ?>>Home</option>
								<option value="mobile_phone_number" <?php echo $fieldName == 'mobile_phone_number' ? 'selected' : ''; ?>>Mobile</option>
								<option value="office_phone_number" <?php echo $fieldName == 'office_phone_number' ? 'selected' : ''; ?>>Office</option>
							</select>
						</div>
					</div>
				
				<?php endif; ?>
				
				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer center">
				<button type="button" class="btn btn-primary btn-sm" data-action="save">Save</button>
			</div>
		</div>
	</div>
</div>