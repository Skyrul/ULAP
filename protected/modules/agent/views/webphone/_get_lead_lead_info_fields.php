<div class="profile-info-row">
	<div class="profile-info-name">LEAD NAME</div>

	<div class="profile-info-value">
		<span><?php echo $lead->first_name.' '.$lead->last_name; ?></span>
		<div class="pull-right">
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="lead_name">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</div>
	</div>
</div>

<div class="profile-info-row">
	<div class="profile-info-name"> PARTNER NAME </div>

	<div class="profile-info-value">
		<span><?php echo $lead->partner_first_name.' '.$lead->partner_last_name; ?></span>
		<div class="pull-right">
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="partner_name">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</div>
	</div>
</div>

<div class="profile-info-row">
	<div class="profile-info-name"> EMAIL ADDRESS </div>

	<div class="profile-info-value">
		<span><?php echo $lead->email_address; ?></span>
		<div class="pull-right">
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="email_address">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</div>
	</div>
</div>

<div class="profile-info-row">
	<div class="profile-info-name"> ADDRESS </div>

	<div class="profile-info-value">
		<span><?php echo $lead->address; ?></span>
		<div class="pull-right">
			<a class="edit-lead-info blue" href="javascript:void(0);" title="Edit" lead_id="<?php echo $lead->id; ?>" field_name="address">
				<i class="ace-icon fa fa-pencil bigger-125"></i>
			</a>
		</div>
	</div>
</div>

<?php if( isset($lead->list->skill) && $lead->list->skill->enable_specific_date_calling == 1 ): ?>

<div class="profile-info-row">
	<div class="profile-info-name"> Date to Call </div>

	<div class="profile-info-value">
		<span><?php echo date('m/d/Y', strtotime($lead->specific_date)); ?></span>
	</div>
</div>

<?php endif; ?>

<div class="profile-info-row">
	<div class="profile-info-name"> LANGUAGE </div>

	<div class="profile-info-value">
		<span>
			<?php 
				$languageOptions = array(
					'English' => 'English',
					// 'French' => 'French',
					// 'Korean' => 'Korean',
					// 'Mandarin' => 'Mandarin',
					'Spanish' => 'Spanish',
				);
				
				echo CHtml::dropDownList('Lead[language]', $lead->language, $languageOptions, array('class'=>'edit-lead-info', 'lead_id'=>$lead->id, 'field_name'=>'language')); 
			?>
		</span>
	</div>
</div>

<div class="profile-info-row">
	<div class="profile-info-name"> TIME ZONE </div>

	<div class="profile-info-value">
		<div class="col-sm-7">
			<span>
				<?php
					echo CHtml::dropDownList('Lead[timezone]', $lead->timezone, AreacodeTimezoneLookup::items(), array('class'=>'edit-lead-info', 'lead_id'=>$lead->id, 'field_name'=>'timezone', 'prompt'=>'- Select -'));
				?>
			</span>
		</div>
		
		<div class="col-sm-5 text-right">
			<?php 
				if( !empty($lead->timezone) )
				{
					$date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('America/Chicago'));

					$timeZone = timezone_name_from_abbr($lead->timezone);
					
					if( strtoupper($lead->timezone) == 'AST' )
					{
						$timeZone = 'America/Puerto_Rico';
					}
					
					if( strtoupper($lead->timezone) == 'ADT' )
					{
						$timeZone = 'America/Halifax';
					}
					
					$date->setTimezone(new DateTimeZone( $timeZone ));

					echo $date->format('m/d/Y g:i A'); 
				}
			?>
		</div>
	</div>
</div>
