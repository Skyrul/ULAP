<div id="<?php echo $data->id; ?>" class="profile-activity clearfix">
	<?php //1:33pm 3/5/2017 | Kevin Bowman | Agents, Team Leaders | No work tomorrow ?>
	
	<?php
		$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

		$date->setTimezone(new DateTimeZone('America/Denver'));

		echo $date->format('m/d/Y g:i A') . ' | ';
		
		if ( isset($data->account->accountUser) )
		{
			echo $data->account->accountUser->getFullName() . ' | ';
		}
		else
		{
			echo $data->account->username . ' | ';
		}
		
		echo $data->security_group_text . ' | ';
		
		echo $data->description;
	?>
</div>