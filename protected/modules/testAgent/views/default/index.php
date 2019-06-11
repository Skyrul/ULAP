<?php 

$this->pageTitle = Yii::app()->name . ' - Dialer';

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/css/dialer.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
$cs->registerCssFile($baseUrl . '/template_assets/css/datepicker.min.css');

$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } .timeline-container::before { content:none !important; }');

$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScriptFile($baseUrl.'/js/calendar/testcalendar.js',  CClientScript::POS_END);


$leadId = $lead != null ? $lead->id : '';
$calendarId = $calendar != null ? $calendar->id : '';
$customerId = $customer != null ? $customer->id : '';

$cs->registerScript(uniqid(), '

	var current_lead_id = "'.$leadId.'";
	var current_calendar_id = "'.$calendarId.'";
	var customer_id = "'.$customerId.'";
	var viewer = "agent";
	
	var current_call_history_id = "'.$leadCallHistoryId.'";
	
	var hasMadeCalendarChanges = 0;
	
	function initializeDatePicker()
	{
		$(".datepicker").datepicker({
			autoclose: true,
			todayHighlight: true
		});
	}
	
	function enableNextLeadButton()
	{
		if( $(".end-call-btn").hasClass("disabled") && $("#leadPhoneNumbers").find("tr td select.dispo-select:enabled").length == 0 )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/default/updateLeadHopper",
				type: "post",
				dataType: "json",
				data: { "ajax": 1, "current_lead_id":current_lead_id },
				success: function(response) { 
					
					$(".next-lead-btn").removeClass("next-lead-btn-disabled");	
					
				}
			});
		}
	}
	
	function validateEmail(email) 
	{
		var re = /\S+@\S+\.\S+/;
		return re.test(email);
	}
	
', CClientScript::POS_HEAD);

$cs->registerScript(uniqid(), '
	$(document).ready(function () {
	
		initializeDatePicker();
		
		setInterval(function(){ 
		
			$("#calendar").fullCalendar("render"); 
		
			//lead hangup listener
			if( current_call_history_id != "" && !$(".end-call-btn").hasClass("disabled") )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/hangupListener",
					type: "post",
					dataType: "json",
					data: { "ajax":1, "call_history_id":current_call_history_id },
					success: function(response) { 

						if( response.call_status == 1 )
						{
							$(".end-call-btn").click();
						}
					}
				});
			}
		
		}, 1000);

		$(".scrollable").each(function () {
			var $this = $(this);
			$(this).ace_scroll({
				size: $this.data("size") || 380,
			});
		});
		
	});
	
', CClientScript::POS_END);
?>		
			
<div class="tabbable">
	<ul id="myTab" class="nav nav-tabs padding-12 tab-color-blue background-blue">
		<li class="active">
			<a id="dialerTab" href="#dialer" data-toggle="tab">
				<i class="ace-icon fa fa-phone bigger-120"></i>
				DIALER
			</a>
		</li>

		<li>
			<a href="#appointments" data-toggle="tab">
				<i class="ace-icon fa fa-calendar bigger-120"></i>
				APPOINTMENTS
			</a>
		</li>
		
		<li>
			<a id="leadSearchTab" href="javascript:void(0);">
				<i class="ace-icon fa fa-search bigger-120"></i>
				LEAD SEARCH
			</a>
		</li>
		
		<li>
			<a id="mapTab" href="#googlemap" data-toggle="tab" style="display:none;">
				<i class="ace-icon fa fa-map-marker bigger-120"></i>
				MAP DIRECTIONS
				
				<span class="close close-map">×</span>
			</a>
		</li>
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
					echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn next-lead-btn-disabled'));
					
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
					)); 
				}
				else
				{
					echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn'));
					
					$this->renderPartial('_empty_leadInfo');
				}
			?>
		</div>
	
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
	</div>

</div>


<button type="button" class="btn btn-info btn-xs calendar-details-btn" style="position: absolute; top: 0px; left: 0px; width: 40px; border-radius: 0px 15px 15px 0px; margin-top: 70px; padding: 8px;">
	<i class="ace-icon fa fa-arrow-left bigger-110 icon-only"></i>
</button>


<?php 
	$this->renderPartial('dialPad', array(
		'lead' => $lead,
		'list' => $list,
		'customer' => $customer,
	)); 
?>