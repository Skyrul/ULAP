<li>
	<a id="dialerTab" href="#dialer" data-toggle="tab">
		<i class="ace-icon fa fa-phone bigger-120"></i>
		DIALER
	</a>
</li>

<?php if( $showDataTab ): ?>
	<li>
		<a id="dataTab" href="#data" data-toggle="tab">
			<i class="ace-icon fa fa-edit bigger-120"></i>
			DATA
		</a>
	</li>
<?php endif; ?>

<?php if( $showAppointmentTab ): ?>
	<li>
		<a id="appointmentsTab" href="#appointments" data-toggle="tab">
			<i class="ace-icon fa fa-calendar bigger-120"></i>
			APPOINTMENTS
		</a>
	</li>
<?php endif; ?>

<?php if( $showSurveyTab ): ?>
	<li>
		<a id="surveyTab" href="#surveys" data-toggle="tab">
			<i class="ace-icon fa fa-question-circle bigger-120"></i>
			SURVEY
		</a>
	</li>
<?php endif; ?>

<li>
	<a href="#leadSearch" data-toggle="tab">
		<i class="ace-icon fa fa-search bigger-120"></i>
		LEAD SEARCH
	</a>
</li>

<li>
	<a id="agentStatsTab" href="#agentStats" data-toggle="tab">
		<i class="ace-icon fa fa-user bigger-120"></i>
		AGENT STATS
	</a>
</li>

<li>
	<a id="mapTab" href="#googlemap" data-toggle="tab" style="display:none;">
		<i class="ace-icon fa fa-map-marker bigger-120"></i>
		MAP DIRECTIONS
		
		<span class="close close-map">×</span>
	</a>
</li>

<?php if($showScriptTab): ?>
	<li>
		<a id="scriptTab" href="#script" data-toggle="tab">
			<i class="ace-icon fa fa-file bigger-120"></i>
			SCRIPT
		</a>
	</li>
<?php endif; ?>