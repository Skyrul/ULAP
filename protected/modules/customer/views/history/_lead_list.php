<?php /*if($index == 0): ?>

<thead>
	<th></th>
	<th class="center">Office Number</th>
	<th class="center">Mobile Number</th>
	<th class="center">Phone Number</th>
	<th class="center">First Name</th>
	<th class="center">Last Name</th>
	<th class="center">Email Address</th>
	<th class="center">Creation Date</th>
	<th class="center"># of Dials</th>
	<th class="center">Status</th>
</thead>

<tr>
	<td class="center">
		
	</td>
	
	<td class="center"><input type="text" name="Lead[office_phone_number]"></td>
	
	<td class="center"><input type="text" name="Lead[mobile_phone_number]"></td>
	
	<td class="center"><input type="text" name="Lead[office_phone_number]"></td>
	
	<td class="center"><input type="text" name="Lead[first_name]"></td>
	
	<td class="center"><input type="text" name="Lead[last_name]"></td>
	
	<td class="center"><input type="text" name="Lead[email_address]"></td>
	
	<td class="center"></td>
	
	<td class="center"><input type="text" name="Lead[number_of_dials]"></td>
	
	<td class="center"><input type="text" name="Lead[status]"></td>
</tr>

<?php endif;*/ ?>

<tr>
	<?php /*
	<td class="center">
	
		<!--<label>
			<input type="checkbox" class="ace" name="leads[]" value="<?php echo $data->id; ?>">
			<span class="lbl">&nbsp;</span>
		</label>-->
		
		<button type="button" id="<?php echo $data->id; ?>" class="btn btn-minier btn-info lead-details"><i class="fa fa-pencil"></i> Edit</button>
		
		<?php if(!Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff()): ?>
		
		<button type="button" id="<?php echo $data->id; ?>" class="btn btn-minier btn-danger lead-delete"><i class="fa fa-times"></i> Delete</button>
		
		<?php endif; ?>
	</td>
	*/ ?>
	<td class="center">
		<?php echo $data->office_phone_number != '' ? "(".substr($data->office_phone_number, 0, 3).") ".substr($data->office_phone_number, 3, 3)."-".substr($data->office_phone_number,6) : ''; ?>
	</td>
	
	<td class="center">
		<?php echo $data->mobile_phone_number != '' ? "(".substr($data->mobile_phone_number, 0, 3).") ".substr($data->mobile_phone_number, 3, 3)."-".substr($data->mobile_phone_number,6) : ''; ?>
	</td>
	
	<td class="center">
		<?php echo $data->home_phone_number != '' ? "(".substr($data->home_phone_number, 0, 3).") ".substr($data->home_phone_number, 3, 3)."-".substr($data->home_phone_number,6) : ''; ?>
	</td>
	
	<td><?php echo $data->first_name; ?></td>
	
	<td><?php echo $data->last_name; ?></td>
	
	<td><?php echo $data->list->name; ?></td>
	
	<td class="center"><?php echo date('m/d/Y', strtotime($data->date_created)); ?></td>
</tr>