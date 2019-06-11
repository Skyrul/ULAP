<?php
	$this->pageTitle = Yii::app()->name . ' - Webphone';
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	//css
	$cs->registerCssFile( $baseUrl . '/css/dialer.css');
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');
	
	$cs->registerCssFile($baseUrl . '/template_assets/css/datepicker.css');

	$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } .timeline-container::before { content:none !important; }');
	
	//scripts
	$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

	$cs->registerScriptFile($baseUrl.'/js/webphone/webphone_api.js?ver='.time(),  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/lead_search.js?ver='.time(),  CClientScript::POS_END);	
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/lead_info.js?ver='.time(),  CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/lead_history.js?ver='.time(),  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/dialer_buttons.js?ver='.time(),  CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/disposition.js?ver='.time(),  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/tab.js?ver='.time(),  CClientScript::POS_END);
	// $cs->registerScriptFile($baseUrl.'/js/agent/defaultWebphone/customer_queue_popup.js?ver='.time(),  CClientScript::POS_END);

	$leadId = $lead != null ? $lead->id : '';
	$calendarId = $calendar != null ? $calendar->id : '';
	$customerId = $customer != null ? $customer->id : '';

	$hasMadeCalendarChanges = 0;

	$leadCallHistory = LeadCallHistory::model()->findByPk($leadCallHistoryId);

	if( $leadCallHistory && $leadCallHistory->calendar_appointment_id != null )
	{
		$hasMadeCalendarChanges = 1;
	}
	
	$cs->registerScript(uniqid(), '

		var current_lead_id = "'.$leadId.'";
		var current_calendar_id = "'.$calendarId.'";
		var customer_id = "'.$customerId.'";
		var viewer = "agent";
		
		var is_host_dialer = "'.$authAccount->getIsHostDialer().'";	
		
		var current_call_history_id = "'.$leadCallHistoryId.'";	
		
		var hasMadeCalendarChanges = "'.$hasMadeCalendarChanges.'";
		
		var delayTime = "'.$leadHopperEntry->skill->customer_popup_delay.'";
		
		
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
					url: yii.urls.absoluteUrl + "/agent/defaultWebphone/updateLeadHopper",
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
?>

<?php	
	$cs->registerScript(uniqid(), "
		
		function Call(destnum)
        {
            if (typeof (destnum) === 'undefined' || destnum === null) { destnum = ''; }
            
			destnum = '81' + destnum;
			
			//temp override for testing
			// destnum = '814356501237';
			// destnum = '819093900003';
			
			webphone_api.setparameter('destination', destnum);
            webphone_api.call(destnum);
        }
		
		function Hangup()
        {
            webphone_api.hangup();
        }
        
        var holdstate = true;
        function Hold()
        {
            if (holdstate === true)
            {
                webphone_api.hold(true);
                holdstate = false;
				
				$('.hold-call-btn').html('UNMUTE');
				$('.hold-call-btn').removeClass('btn-yellow');
				$('.hold-call-btn').addClass('btn-warning');
            }
			else
            {
                webphone_api.hold(false);
                holdstate = true;
				
				$('.hold-call-btn').html('MUTE');
				$('.hold-call-btn').removeClass('btn-warning');
				$('.hold-call-btn').addClass('btn-yellow');
            }
        }
		
		function Transfer(destnum)
        {
			destnum = '81' + destnum;
            webphone_api.transfer(destnum);
        }
		
		function Conference(destnum)
        {
			destnum = '81' + destnum;
			webphone_api.call(destnum);
        }
		
		//inbound functions
		function Accept()
        {
            document.getElementById('icoming_call_layout').style.display = 'none';
            webphone_api.accept();
        }
        
        function Reject()
        {
            document.getElementById('icoming_call_layout').style.display = 'none';
            webphone_api.reject();
        }
		
		$(document).ready( function(){
			
			webphone_api.onLoaded(function ()
			{
				var serveraddress = '".$sipServer."';
				var username = '".$sipUsername."';
				var password = '".$sipPassword."';	
				var callerID = '".$callerID."';	
				
				webphone_api.delsettings(1);
				
				webphone_api.setparameter('serveraddress', serveraddress); 
				webphone_api.setparameter('username', username); 
				webphone_api.setparameter('password', password); 
				webphone_api.setparameter('displayname', callerID); 
				
				webphone_api.start();
			});
			
			webphone_api.onEvents(function (evt)
			{
				$('#webphone-events').html(evt);
			});

			webphone_api.onRegistered(function ()
			{
				$('.dial-phonenumber-btn').fadeIn();
				$('.grey').fadeIn();
				
				$('.dial-pad-btn').removeClass('disabled');
				
				if( $('div.alert').text() != ' Lead has no disposition for the last call.' )
				{
					$('.next-lead-btn').removeClass('next-lead-btn-disabled');
				}
				
				// display supported callfunctions
				var funcl = webphone_api.getavailablecallfunc(); // possible values: conference,transfer,numpad,mute,hold,chat
				
				if (typeof (funcl) !== 'undefined' && funcl !== null && funcl.length > 0 && funcl.indexOf('ERROR') < 0)
				{
					var flist = funcl.split(',');
					for (var i = 0; i < flist.length; i++)
					{
						if (typeof (flist[i]) !== 'undefined' && flist[i] !== null && flist[i].length > 0)
						{
							document.getElementById('btn_' + flist[i]).style.display = 'block';
						}
					}
				}
			}); 
			
			webphone_api.onUnRegistered(function ()
			{
				$('.dial-phonenumber-btn').hide();
				$('.grey').hide();
				
				$('.dial-pad-btn').addClass('disabled');
			}); 

			//status: can have following values: callSetup, callRinging, callConnected, callDisconnected
			//direction: 1 (outgoing), 2 (incoming)
			//peername: is the other party username (or phone number or extension)
			//peerdisplayname: is the other party display name if any
			//line number
			webphone_api.onCallStateChange(function (event, direction, peername, peerdisplayname, line)
			{	
				$('#webphone-state').html(event);
				
				if( event === 'callSetup' )
				{
					$('.end-call-btn').removeClass('disabled');
					
					$('.conference-call-btn').removeClass('disabled');
					$('.conference-call-btn-group').removeClass('disabled');
					
					$('.transfer-call-btn').removeClass('disabled');
					$('.transfer-call-btn-group').removeClass('disabled');
					
					$('.hold-call-btn').removeClass('disabled');
					
					webphone_api.voicerecord(true, yii.urls.absoluteUrl + '/webphoneVoice/?filename=callrecord_DATE_CALLID.wav'); 
				}
				
				if( event === 'callConnected' )
				{
					$.ajax({
						url: yii.urls.absoluteUrl + '/agent/defaultWebphone/getRecording',
						type: 'post',
						dataType: 'json',
						data: { 'ajax': 1, 'call_history_id': current_call_history_id },
						success: function(response) { 
							
							alert(response.message);
							
						}
					});
				}
				
				//detecting the end of a call, even if it wasn't successful
				if( event === 'callDisconnected' )
				{
					webphone_api.voicerecord(false, yii.urls.absoluteUrl + '/webphoneVoice/?filename=callrecord_DATE_CALLID.wav'); 
					
					$('.end-call-btn').addClass('disabled');
					
					$('.conference-call-btn').addClass('disabled');
					$('.conference-call-btn-group').addClass('disabled');
					
					$('.transfer-call-btn').addClass('disabled');
					$('.transfer-call-btn-group').addClass('disabled');
					
					$('.hold-call-btn').addClass('disabled');
				
					// reset to default state, after call ends
					
					holdstate = true;
					$('.hold-call-btn').html('MUTE');
					$('.hold-call-btn').removeClass('btn-warning');
					$('.hold-call-btn').addClass('btn-yellow');
					
					$.ajax({
						url: yii.urls.absoluteUrl + '/agent/defaultWebphone/endCall',
						type: 'post',
						dataType: 'json',
						data: { 'ajax': 1, 'call_history_id': current_call_history_id, 'current_lead_id':current_lead_id },
						success: function(response) { 

							if( response.status == 'success' )
							{
								$('.dialer-function-btn-container .btn').addClass('disabled');
								$('table tr.success').removeClass('success');

								$('#leadPhoneNumbers').find('a.green, a.grey').addClass('dial-phonenumber-btn');
								
								enableNextLeadButton();
							}
						}
					});
				}
			});
			
		});
		
", CClientScript::POS_END);
?>

<?php
$cs->registerScript(uniqid(), '
	$(document).ready(function () {
	
		initializeDatePicker();
		
		$(".scrollable").each(function () {
			var $this = $(this);
			$(this).ace_scroll({
				size: $this.data("size") || 380,
			});
		});
		
		$(document).on("click", ".num", function () {
			var num = $(this);
			var text = $.trim(num.find(".txt").clone().children().remove().end().text());
			var telNumber = $("#manualDialInput");
			$(telNumber).val(telNumber.val() + text);
		});
	
		$(document).on("click", ".calendar-details-btn", function(){
			
			$("#calendar-details").animate({width: "toggle"},350);
			
			if( $(".calendar-details-btn > i").hasClass("fa-arrow-right") )
			{
				$(".calendar-details-btn").html("<i class=\"ace-icon fa fa-arrow-left bigger-110 icon-only\"></i>");
				
				$("#calendar-wrapper").removeClass("col-sm-12").addClass("col-sm-8");
				
				$("#calendar").fullCalendar("render");
			}
			else
			{
				$(".calendar-details-btn").html("<i class=\"ace-icon fa fa-arrow-right bigger-110 icon-only\"></i>");
				
				$("#calendar-wrapper").removeClass("col-sm-8").addClass("col-sm-12");

				$("#calendar").fullCalendar("render");
			}
			
		});
		
		$(document).on("change", "#office-select", function(){
			
			var office_id = $(this).val();
			
			if( office_id != "" )
			{
				// $(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id);
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/ajaxUpdateCalendarOptions",
					type: "post",
					dataType: "json",
					data: {"ajax":1, "office_id":office_id, "current_lead_id":current_lead_id },
					success: function(response) { 

						if( response.calendar_html != "" )
						{
							$(".calendar-info-wrapper").html(response.calendar_html);
							
							current_calendar_id = response.first_calendar_id;
						}
						
						if( response.office_html != "" )
						{
							$(".office-info-wrapper").html(response.office_html);
						}
					}
				});
			}
		});
		
		$(document).on("click", ".load-calendar-btn", function(){

			var office_id = $("#office-select").val();
			var calendar_id = $("#calendar-select").val();
			
			$(this).html("Loading <i class=\"fa fa-cog fa-spin fa-lg\"></i>");
			$(this).prop("disabled", true);
			
			if( calendar_id != "" )
			{
				current_calendar_id = calendar_id;
				$("#calendar").fullCalendar("refetchEvents");
				
				$("#calendar").fullCalendar("render"); 
				
				// $(location).attr("href", yii.urls.absoluteUrl + "/agent?action=nextLead&office_id=" + office_id +  "&calendar_id=" + calendar_id);
			}
		});
	});
	
', CClientScript::POS_END);
?>

<?php	
$cs->registerScript(uniqid(), '

	$(document).on("click", ".next-lead-btn", function(){

		var this_button = $(this);
		var lead_hopper_id = this_button.attr("lead_hopper_id");
	
		if( !this_button.hasClass("next-lead-btn-disabled") )
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/agent/defaultWebphone/index",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "action": "nextLead", "lead_hopper_id": lead_hopper_id },
				beforeSend: function(){ 
					this_button.addClass("next-lead-btn-disabled");
					this_button.find("span").text("LOADING...");
					
					$("div.alert").remove();
				},
				error: function(){
					this_button.find("span").text("Error...");
				},
				success: function(response) {
					this_button.find("span").text("NEXT LEAD");
					
					if( response.getFlashesHtml != "" )
					{
						$("div.tab-content").prepend(response.getFlashesHtml);
					}
					
					if( response.status  == "success" )
					{
						current_lead_id = response.current_lead_id;
						current_calendar_id = response.current_calendar_id;
						customer_id = response.customer_id;
						current_call_history_id = response.current_call_history_id;

						//update caller ID					
						if( response.caller_id != webphone_api.getparameter("displayname") )
						{
							webphone_api.setparameter("displayname", response.caller_id);
							webphone_api.start();
						}
						
						$(".dialingAs").text( response.caller_id );
						
						//main tabs
						if( response.html.tabs != "" )
						{ 
							$(".nav-tabs").html( response.html.tabs );
						}
						
						if( response.html.tab_contents != "" )
						{ 
							$(".tab-content div:eq(0)").after( response.html.tab_contents );
						}
						
						//lead info
						if( response.html.title != "" )
						{ 
							$(".lead-info-title-container").html( response.html.title );
						}
						
						if( response.html.lead_info_fields != "" )
						{ 
							$(".lead-info-fields-container").html( response.html.lead_info_fields );
						}
						
						if( response.html.lead_info_dialer_buttons != "" )
						{ 
							$(".dialer-function-btn-container").html( response.html.lead_info_dialer_buttons );
						}
						
						if( response.html.lead_info_lead_phone_numbers != "" )
						{ 
							$(".lead-info-phone-numbers-container").html( response.html.lead_info_lead_phone_numbers );
						}
						
						//customer info
						if( response.html.customer_info_fields != "" )
						{ 
							$(".customer-info-fields-container").html( response.html.customer_info_fields );
						}
						
						//lead history
						if( response.html.lead_history != "" )
						{ 
							$(".lead-history-container").html( response.html.lead_history );
						}
						
						//data tab
						if( response.html.data_tab != "" )
						{
							$("#data").html(response.html.data_tab);
						}
						
						//script tab
						if( response.html.script_tab != "" )
						{
							$("#script").html(response.html.script_tab);
						}
						
						//customer queue popup
						if( response.html.customer_queue_popup != "" )
						{
							var delayTime = response.customer_popup_delay;
							
							$(".page-content-area").prepend( response.html.customer_queue_popup );
							
							$("#customerPopupModal").modal("show");
				
							$("#customerPopupModal .popupDelayCtr").text(delayTime);
								
							setInterval(function(){
								delayTime--;

								if( delayTime >= 0 )
								{
									$(".popupDelayCtr").text(delayTime);
								}
								
							},1000);
							
							setTimeout(function(){
								
								var button = $("#customerPopupModal").find(".modal-footer :button");
							
								button.removeClass("btn-default");
								button.addClass("btn-info");
								button.prop("disabled", false);
								button.text("Close");
								
								// $(".dial-phonenumber-btn").removeClass("popup-delay-disabled");
								
							}, delayTime * 1000);
						}
						
						$("#dialerTab").click();
					}
					
				},
			});
		}
	});
	
', CClientScript::POS_END);
?>

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
		
<div class="tabbable">
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
					// echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn next-lead-btn-disabled'));
					echo CHtml::link('<span>NEXT LEAD</span>', '#', array('class'=>'next-lead-btn next-lead-btn-disabled', 'lead_hopper_id'=>''));
					
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
					// echo CHtml::link('<span>NEXT LEAD</span>', array('index', 'action'=>'nextLead'), array('class'=>'next-lead-btn'));
					echo CHtml::link('<span>NEXT LEAD</span>', '#', array('class'=>'next-lead-btn next-lead-btn-disabled', 'lead_hopper_id'=>''));
					
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

</div>


<button type="button" class="btn btn-info btn-xs calendar-details-btn" style="position: absolute; top: 0px; left: 0px; width: 40px; border-radius: 0px 15px 15px 0px; margin-top: 70px; padding: 8px;">
	<i class="ace-icon fa fa-arrow-left bigger-110 icon-only"></i>
</button>

<button type="button" style="position: absolute; top: 0px; left: 0px; width: 40px; border-radius: 0px 15px 15px 0px; margin-top: 150px; padding: 8px; height:110px;/*! transform: rotate(90deg); */z-index: 9999;" class="btn btn-danger btn-xs end-call-btn disabled">
	<p style="transform: rotate(90deg);transform-origin: left top 0; float:left;margin-left:18px;margin-top: -23px;font-size: 14px;">END CALL</p>
</button>


<?php 
	$this->renderPartial('dialPad', array(
		'lead' => $lead,
		'list' => $list,
		'customer' => $customer,
		'callerID' => $callerID
	)); 
?>