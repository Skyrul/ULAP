<?php 
	$leadCallHistory = LeadCallHistory::model()->find(array(
		'condition' => 'lead_id = :lead_id',
		'params' => array(
			':lead_id' => $lead->id,
		),
		'order' => 'date_created DESC',
	));
?>

<table id="leadPhoneNumbers" class="table table-condensed">
								
	<?php if(!empty($lead->home_phone_number)): ?>
	
	<tr>
		<th><?php echo !empty($lead->home_phone_label) ? $lead->home_phone_label : 'HOME';?></th>
		
		<td width="16%">
			<?php echo !empty($lead->home_phone_number) ? "(".substr($lead->home_phone_number, 0, 3).") ".substr($lead->home_phone_number, 3, 3)."-".substr($lead->home_phone_number,6) : ''; ?>
		</td>
		
		<td>
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="home_phone_number">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</td>
		
		<td width="18%">
			<span style="margin-right:10px;">DIAL COUNT</span> 
			<span><?php echo $lead->home_phone_dial_count; ?></span>
		</td>
		
		<td>
			<div class="form-group">
				<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

				<div class="col-sm-7">
					<?php 
						$homeDispoHtmlOptions =  array(
							'class' => 'dispo-select', 
							'phone_type' => 'home', 
							'field_type' => 'dispo',
							'prompt' => '- Select -',
							'disabled' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->home_phone_number  ? false : true,
							'style'=>'width:250px;',
						);
						
						$homeDispoHtmlOptions = array_merge($dispositionHtmlOptions, $homeDispoHtmlOptions);
						
						echo CHtml::dropDownList('Lead[home_phone_dispo_id]', '', $dispositionOptions, $homeDispoHtmlOptions); 
					?>
				</div>
			</div>
			
			<div class="dispo-detail-container"></div>
		</td>
		
		<td>
			<?php 
				$homePhoneHtmlOptions = array(
					'class' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->home_phone_number ? 'grey' : 'green dial-phonenumber-btn',
					'phone_type'=>'home',
					'lead_id' => $lead->id,
					'list_id' => $list->id,
					'customer_id' => $customer->id,
					'company_id' => $customer->company_id,
					'skill_id' => $list->skill_id,
					'lead_phone_number' => $lead->home_phone_number,
					'title' => 'Dial this number',
				);
				
				echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $homePhoneHtmlOptions); 
			?>
		</td>
	</tr>
	
	<?php endif; ?>
	
	
	<?php if(!empty($lead->mobile_phone_number)): ?>
	
	<tr>
		<th><?php echo !empty($lead->mobile_phone_label) ? $lead->mobile_phone_label : 'MOBILE';?></th>
		
		<td width="16%">
			<?php echo !empty($lead->mobile_phone_number) ? "(".substr($lead->mobile_phone_number, 0, 3).") ".substr($lead->mobile_phone_number, 3, 3)."-".substr($lead->mobile_phone_number,6) : ''; ?>
		</td>
		
		<td>
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="mobile_phone_number">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</td>
		
		<td width="18%">
			<span style="margin-right:10px;">DIAL COUNT</span>  
			<span><?php echo $lead->mobile_phone_dial_count; ?></span>
		</td>
		
		<td>
			<div class="form-group">
				<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

				<div class="col-sm-7">
					<?php 
						$mobileDispoHtmlOptions = array(
							'class' => 'dispo-select', 
							'phone_type' => 'mobile', 
							'field_type' => 'dispo', 
							'prompt' => '- Select -', 
							'disabled' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->mobile_phone_number ? false : true,
							'style'=>'width:250px;',
						);
						
						$mobileDispoHtmlOptions = array_merge($dispositionHtmlOptions, $mobileDispoHtmlOptions);
						
						echo CHtml::dropDownList('Lead[mobile_phone_dispo_id]', '', $dispositionOptions, $mobileDispoHtmlOptions); 
					?>
				</div>
			</div>
			
			<div class="dispo-detail-container"></div>
		</td>
		
		<td>
			<?php 
				$mobilePhoneHtmlOptions = array(
					'class' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->mobile_phone_number ? 'grey' : 'green dial-phonenumber-btn',
					'phone_type'=>'mobile',
					'lead_id' => $lead->id,
					'list_id' => $list->id,
					'customer_id' => $customer->id,
					'company_id' => $customer->company_id,
					'skill_id' => $list->skill_id,
					'lead_phone_number' => $lead->mobile_phone_number,
					'title' => 'Dial this number',
				);
				
				echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $mobilePhoneHtmlOptions); 
			?>
		</td>
	</tr>
	
	<?php endif; ?>
	
	
	<?php if(!empty($lead->office_phone_number)): ?>
	
	<tr>
		<th><?php echo !empty($lead->office_phone_label) ? $lead->office_phone_label : 'OFFICE';?></th>
		
		<td width="16%">
			<?php echo !empty($lead->office_phone_number) ? "(".substr($lead->office_phone_number, 0, 3).") ".substr($lead->office_phone_number, 3, 3)."-".substr($lead->office_phone_number,6) : ''; ?>
		</td>
		
		<td>
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="office_phone_number">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</td>
		
		<td width="18%">
			<span style="margin-right:10px;">DIAL COUNT</span> 
			<span><?php echo $lead->office_phone_dial_count; ?></span>
		</td>
		
		<td>
			<div class="form-group">
				<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

				<div class="col-sm-7">
					<?php
						$officeDispoHtmlOptions = array(
							'class' => 'dispo-select', 
							'phone_type' => 'office',
							'field_type' => 'dispo',
							'prompt' => '- Select -', 
							'disabled' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->office_phone_number ? false : true,
							'style'=>'width:250px;',
						);
						
						$officeDispoHtmlOptions = array_merge($dispositionHtmlOptions, $officeDispoHtmlOptions);
						
						echo CHtml::dropDownList('Lead[office_phone_dispo_id]', '', $dispositionOptions, $officeDispoHtmlOptions); 
					?>
				</div>
			</div>
			
			<div class="dispo-detail-container"></div>
		</td>
		
		<td>
			<?php 
				$officePhoneHtmlOptions = array(
					'class' => !empty($leadCallHistory) &&  "(".substr($leadCallHistory->lead_phone_number, 0, 3).") ".substr($leadCallHistory->lead_phone_number, 3, 3)."-".substr($leadCallHistory->lead_phone_number,6) == $lead->office_phone_number ? 'grey' : 'green dial-phonenumber-btn',
					'phone_type'=>'office',
					'lead_id' => $lead->id,
					'list_id' => $list->id,
					'customer_id' => $customer->id,
					'company_id' => $customer->company_id,
					'skill_id' => $list->skill_id,
					'lead_phone_number' => $lead->office_phone_number,
					'title' => 'Dial this number',
				);
				
				echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $officePhoneHtmlOptions); 
			?>
		</td>
	</tr>
	
	<?php endif; ?>
	
</table>