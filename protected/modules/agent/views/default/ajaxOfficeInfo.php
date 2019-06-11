<div class="profile-user-info profile-user-info-striped">
	<div class="profile-info-row">
		<div class="profile-info-name"> CUSTOMER NAME </div>

		<div class="profile-info-value">
			<span><?php echo $customer->firstname.' '.$customer->lastname; ?></span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE </div>

		<div class="profile-info-value">
			<span>
				<?php echo CHtml::dropDownList('', $office->id, $officeOptions, array('id'=>'office-select', 'prompt'=>'- SELECT -')); ?>
			</span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE ADDRESS </div>

		<div class="profile-info-value">
			<span><?php echo $office->address; ?></span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE CITY </div>

		<div class="profile-info-value">
			<span><?php echo $office->city; ?></span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE STATE </div>

		<div class="profile-info-value">
			<span>
				<?php 
					$state = State::model()->findByPk($office->state);
					
					if( $state )
					{
						echo $state->name;
					}
				?>
			</span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE PHONE # </div>

		<div class="profile-info-value">
			<span><?php echo $office->phone; ?></span>
		</div>
	</div>
	
	<div class="profile-info-row">
		<div class="profile-info-name"> OFFICE EMAIL </div>

		<div class="profile-info-value">
			<span><?php echo $office->email_address; ?></span>
		</div>
	</div>
	
	<!--<div class="profile-info-row">
		<div class="profile-info-name"> CUSTOMER WEBSITE </div>

		<div class="profile-info-value">
			<span></span>
		</div>
	</div>-->
	
	<div class="profile-info-row">
		<div class="profile-info-name"> CUSTOMER NOTES </div>

		<div class="profile-info-value">
			<span>
				<?php echo $customer->notes; ?>
			</span>
		</div>
	</div>
</div>