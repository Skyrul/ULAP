<div class="timeline-item clearfix">
	<div class="timeline-info">
		<i class="timeline-indicator ace-icon fa fa-phone btn btn-primary no-hover"></i>	</div>

	<div class="widget-box transparent">
		<div class="widget-header widget-header-small">
			<h5 class="widget-title smaller">
				 <?php echo $authAccount->getFullName(); ?>
			</h5>
			<span class="grey" style="display: inline;">
				 | Call | <?php echo $disposition; ?> | <?php echo ucfirst($phoneType); ?> <?php echo "(".substr($leadPhoneNumber, 0, 3).") ".substr($leadPhoneNumber, 3, 3)."-".substr($leadPhoneNumber,6); ?>		
			</span>

			<span class="widget-toolbar no-border">
				<i class="ace-icon fa fa-clock-o bigger-110"></i>
				<?php
					$timeZone = AreacodeTimezoneLookup::getAreaCodeTimeZone( preg_replace("/[^0-9]/","", $lead->customer->phone) );
								
					if( !empty($lead->timezone) )
					{
						$timeZone = $lead->timezone;
						$leadLocalTime = new DateTime('', new DateTimeZone(timezone_name_from_abbr($timeZone)) );
						echo $leadLocalTime->format('m/d/Y g:i A');
					}
					else
					{
						echo date('m/d/Y g:i A');
					}
				?>
			</span>

			<span class="widget-toolbar"></span>
		</div>

		<div class="widget-body"></div>
	</div>
</div>