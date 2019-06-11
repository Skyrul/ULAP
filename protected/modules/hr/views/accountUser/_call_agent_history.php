

<?php if( $index == 0 ): ?>

<thead>
	<th>Date & Time</th>
	<th>Skill</th>
	<th>Phone Number</th>
	<th>Lead Name</th>
	<th>Customer Name</th>
	<th>Disposition</th>
	<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER)) ): ?>
			
	<th></th>
	
	<?php endif; ?>
</thead>

<?php endif; ?>

<tr>
	<td>
		<?php
			$callTime = new DateTime($data['start_call_time'], new DateTimeZone('America/Chicago'));
			$callTime->setTimezone(new DateTimeZone('America/Denver'));	
			
			echo $callTime->format('m/d/Y g:i A');
		?>
	</td>
	
	<td><?php echo $data['skill_name']; ?></td>
	
	<td><?php echo !empty($data['lead_phone_number']) ? "(".substr($data['lead_phone_number'], 0, 3).") ".substr($data['lead_phone_number'], 3, 3)."-".substr($data['lead_phone_number'],6) : ''; ?></td>
	
	<td><?php echo CHtml::link($data['lead_name'], 'javascript:void(0);', array('id'=> $data['lead_id'], 'class'=>'lead-history-link')); ?></td>
	
	<td><?php echo $data['customer_name']; ?></td>
	
	<td><?php echo $data['disposition']; ?></td>
	
	<?php if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER)) && $data['type'] != 4 ): ?>
					
	<td>
		<?php 
			$channel = AsteriskChannel::model()->find(array(
				'condition' => 'call_history_id = :call_history_id',
				'params' => array(
					':call_history_id' => $data['id'],
				),
			));
			
			echo '<span class="hide">call_history_id: '.$data['id'].'</span>';
			
			if( $channel )
			{
				if( !Yii::app()->user->account->getIsCustomer() && !Yii::app()->user->account->getIsCustomerOfficeStaff() && Yii::app()->user->account->checkPermission('employees_performance_recording_link','visible') )
				{
					if( $this->urlExists('http://64.251.13.2/outboundrecordings/'.$channel->unique_id.'.wav') )
					{
						echo CHtml::link('Recording Link', 'http://64.251.13.2/outboundrecordings/'.$channel->unique_id.'.wav', array('target'=>'blank'));
					}
				}
				
				if( $this->urlExists('http://sip1.engagex.com/outboundrecordings/'.$channel->unique_id.'.wav') )
				{
					echo CHtml::link('Recording Link', 'http://sip1.engagex.com/outboundrecordings/'.$channel->unique_id.'.wav', array('target'=>'blank'));
				}
			}
			else
			{
				// echo 'Not Available';
			}
		?>
	</td>
	
	<?php endif; ?>
</tr>