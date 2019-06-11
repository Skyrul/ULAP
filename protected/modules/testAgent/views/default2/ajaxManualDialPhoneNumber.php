<tr class="success">
	<th>MANUAL</th>
	
	<td width="16%">
		<?php echo "(".substr($dialed_phone_number, 0, 3).") ".substr($dialed_phone_number, 3, 3)."-".substr($dialed_phone_number,6); ?>
	</td>
	
	<td>
		<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="manual_dial_phone_number">
			<i class="ace-icon fa fa-pencil bigger-125"></i>
		</a>
	</td>
	
	<td width="18%">
		<span style="margin-right:10px;">DIAL COUNT</span>  
		<span>1</span>
	</td>
	
	<td>
		<div class="form-group">
			<label for="form-field-1" class="col-sm-5 control-label no-padding-right"> DISPOSITION </label>

			<div class="col-sm-7">
				<?php 
					$dispoHtmlOptions = array(
						'class' => 'dispo-select', 
						'phone_type' => 'mobile', 
						'field_type' => 'dispo', 
						'prompt' => '- Select -',
						'call_history_id' => $leadCallHistory->id,						
					);
					
					$mobileDispoHtmlOptions = array_merge($dispositionHtmlOptions, $dispoHtmlOptions);
					
					echo CHtml::dropDownList('Lead[mobile_phone_dispo_id]', '', $dispositionOptions, $dispoHtmlOptions); 
				?>
			</div>
		</div>
		
		<div class="dispo-detail-container"></div>
	</td>
	
	<td>
		<?php 
			$phoneHtmlOptions = array(
				'class' => 'green',
				'phone_type'=>'manual',
				'lead_id' => $lead->id,
				'list_id' => $list_id,
				'customer_id' => $customer_id,
				'company_id' => $company_id,
				'lead_phone_number' => $dialed_phone_number,
				'title' => 'Dial this number',
			);
			
			echo CHtml::link('<i class="fa fa-phone fa-2x"></i>', 'javascript:void(0);', $phoneHtmlOptions); 
		?>
	</td>
</tr>