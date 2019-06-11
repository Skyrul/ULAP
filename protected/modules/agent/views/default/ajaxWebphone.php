<?php 
	$showAppointmentTab = false;
	$showSurveyTab = false;
	$showScriptTab = false;
	$showDataTab = false;
	
	//check appointment tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_appointment_tab == 1 )
	{
		$showAppointmentTab = true;
	}
	
	//check appointment tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
	{
		$showSurveyTab = true;
	}
	
			
	//check script tab settings
	if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
	{
		if( $leadHopperEntry->confirmChildSkill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	elseif( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
	{
		if( $leadHopperEntry->rescheduleChildSkill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	else
	{
		if( $leadHopperEntry->skill->enable_dialer_script_tab == 1 )
		{
			$showScriptTab = true;
		}
	}
	
	//check data tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_dialer_data_tab == 1 )
	{
		$showDataTab = true;
	}
?>	

<ul id="myTab" class="nav nav-tabs padding-12 tab-color-blue background-blue">
	<li class="active">
		<a id="dialerTab" href="#dialer" data-toggle="tab">
			<i class="ace-icon fa fa-phone bigger-120"></i>
			DIALER
		</a>
	</li>
	
	<?php if($showDataTab): ?>
	<li>
		<a id="dataTab" href="#data" data-toggle="tab">
			<i class="ace-icon fa fa-edit bigger-120"></i>
			DATA
		</a>
	</li>
	<?php endif; ?>

	<?php if( isset($list->skill) && $list->skill->enable_dialer_appointment_tab == 1): ?>
	<li>
		<a href="#appointments" data-toggle="tab">
			<i class="ace-icon fa fa-calendar bigger-120"></i>
			APPOINTMENTS
		</a>
	</li>
	<?php endif; ?>
	
	<?php if( isset($list->skill) && $list->skill->enable_survey_tab == 1): ?>
	<li>
		<a href="#surveys" data-toggle="tab">
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
</ul>

<div class="tab-content">
	<?php
		foreach(Yii::app()->user->getFlashes() as $key => $message) {
			echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
		}
	?>

	<div id="dialer" class="tab-pane fade in active">		
		<?php 
			if( $lead != null )
			{
				echo CHtml::link('<span>NEXT LEAD</span>', '', array('class'=>'next-lead-btn next-lead-btn-disabled'));
				
				$this->renderPartial('leadInfo', array(
					'lead' => $lead,
					'list' => $list,
					'calendar' => $calendar,
					'customer' => $customer,
					'office' => $office,
					'officeOptions' => $officeOptions,
					'dispositionOptions' => $dispositionOptions,
					'dispositionHtmlOptions' => $dispositionHtmlOptions,
					'leadHistoryDataProvider' => $leadHistoryDataProvider,
					'leadHopperEntry' => $leadHopperEntry,
					'leadCallHistoryId' => $leadCallHistoryId,
					'accountQueuePopup' => $accountQueuePopup,
					'callerID' => $callerID,
					'xfrs' => $xfrs,
				)); 
			}
			else
			{
				echo CHtml::link('<span>NEXT LEAD</span>', '', array('class'=>'next-lead-btn'));
				
				$this->renderPartial('_empty_leadInfo', array(
					'callerID' => $callerID
				));
			}
		?>
	</div>
	
	<?php if( $showAppointmentTab ): ?>
	<div id="appointments" class="tab-pane fade">	
		<?php
			if( $lead != null )
			{
				$this->renderPartial('appointmentsTab', array(
					'lead' => $lead,
					'list' => $list,
					'calendar' => $calendar,
					'customer' => $customer,
					'office' => $office,
					'calendarOptions' => $calendarOptions,
				)); 
			}
			else
			{
				$this->renderPartial('_empty_appointmentsTab');
			}
		?>
	</div>
	<?php endif; ?>
	
	<?php if( $showSurveyTab ): ?>
	<div id="surveys" class="tab-pane fade">	
		<?php
			if( $lead != null )
			{
				$this->renderPartial('_surveyTab', array(
					'lead' => $lead,
					'list' => $list,
					'customer' => $customer
				)); 
			}
		?>
	</div>
	<?php endif; ?>
	
	<div id="googlemap" class="tab-pane fade in">		
		<?php
			if( $lead != null )
			{
				$this->renderPartial('_google_map', array(
					'cs' => $cs, 
					'lead' => $lead,
					'office' => $office,
				)); 
			}
		?>
	</div>
		
	<div id="leadSearch" class="tab-pane fade in">		
		<?php
			$this->renderPartial('leadSearchTab', array(
				'customer' => $customer, 
			)); 
		?>
	</div>
	
	<div id="agentStats" class="tab-pane fade in"></div>
	
	<?php if( $showScriptTab ): ?>
	<div id="script" class="tab-pane fade in">		
		<?php
			$this->renderPartial('scriptTab', array(
				'leadHopperEntry' => $leadHopperEntry, 
			)); 
		?>
	</div>		
	<?php endif; ?>
	
	<?php if( $showDataTab ): ?>
	<div id="data" class="tab-pane fade in">		
		<?php
			$this->renderPartial('dataTab', array(
				'lead' => $lead, 
			)); 
		?>
	</div>	
	<?php endif; ?>
</div>