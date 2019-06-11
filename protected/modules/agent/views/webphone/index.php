<?php
	$this->pageTitle = Yii::app()->name . ' - Webphone';
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	//css
	$cs->registerCssFile( $baseUrl . '/css/dialer.css');
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	//scripts
	$cs->registerScriptFile($baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

	$cs->registerScriptFile($baseUrl.'/js/webphone/webphone_api.js?ver='.time(),  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/tab.js?ver='.time(),  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/lead_search.js?ver='.time(),  CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/lead_info.js?ver='.time(),  CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/lead_history.js?ver='.time(),  CClientScript::POS_END);

	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/dialer_buttons.js?ver='.time(),  CClientScript::POS_END);
	$cs->registerScriptFile($baseUrl.'/js/agent/hostDial/disposition.js?ver='.time(),  CClientScript::POS_END);

	$cs->registerScript(uniqid(), '

		var current_lead_id = "'.$leadId.'";
		var customer_id = "'.$customerId.'";
		var viewer = "agent";
		var is_host_dialer = "'.$authAccount->getIsHostDialer().'";
		
		var current_call_history_id = "'.$leadCallHistoryId.'";

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
					url: yii.urls.absoluteUrl + "/agent/webphone/updateLeadHopper",
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
            
			// destnum = '81' + destnum;
			
			//temp override for testing
			// destnum = '814356501237';
			// destnum = '819093900003';	
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
		
		//call timer functions
		var call_seconds = 0, call_minutes = 0, call_hours = 0, t;
			
		function add() 
		{
			call_seconds++;
			
			if( call_seconds >= 60 ) 
			{
				call_seconds = 0;
				call_minutes++;
				
				if( call_minutes >= 60 ) 
				{
					call_minutes = 0;
					call_hours++;
				}
			}
			
			var call_timer_text_content = (call_hours ? (call_hours > 9 ? call_hours : '0' + call_hours) : '00') + ':' + (call_minutes ? (call_minutes > 9 ? call_minutes : '0' + call_minutes) : '00') + ':' + (call_seconds > 9 ? call_seconds : '0' + call_seconds);
			
			$('#webphone-call-timer').text( call_timer_text_content );
			
			start_call_timer();
		}
		
		function start_call_timer()
		{
			t = setTimeout(add, 1000);
		}
		
		function stop_call_timer()
		{
			call_seconds = 0;
			call_minutes = 0;
			call_hours = 0;
			
			clearTimeout(t);
		}
		
		
		$(document).ready( function(){
			
			initializeDatePicker();
			
			webphone_api.onLoaded(function ()
			{
				var serveraddress = '".$sipServer."';
				var username = '".$sipUsername."';
				var password = '".$sipPassword."';	
				var callerID = '".$callerID."';	
				var enginePriorityWebrtc = '".$enginePriorityWebrtc."';	
				var enginePriorityNS = '".$enginePriorityNS."';	
				
				webphone_api.delsettings(1);
				
				webphone_api.setparameter('serveraddress', serveraddress); 
				webphone_api.setparameter('username', username); 
				webphone_api.setparameter('password', password); 
				webphone_api.setparameter('displayname', callerID); 
				webphone_api.setparameter('playdtmfsound', '2');
				webphone_api.setparameter('dtmfmode', '3');				
				webphone_api.setparameter('realm', 'sip1.engagex.com');
				webphone_api.setparameter('enginepriority_webrtc', enginePriorityWebrtc);				
				webphone_api.setparameter('enginepriority_ns', enginePriorityNS);
				
				webphone_api.start();
			});
			
			webphone_api.onEvents(function (event)
			{
				// For example the following status means that there is an incoming call ringing from 2222 on the first line:
				// STATUS,1,Ringing,2222,1111,2,Katie,[callid]
				// parameters are separated by comma(,)
				// the sixth parameter (2) means it is for incoming call. For outgoing call this parameter is 1.
				// example for detecting incoming and outgoing calls:
				
				var evtarray = event.split(',');
				var status_icon = '';
				
				if( evtarray[0] === 'STATUS' )
				{
					if( evtarray[2] !== 'Registered.' )
					{
						status_icon = '<i class=\"ace-icon fa fa-circle light-red\"></i> ';
					}
					else
					{
						status_icon = '<i class=\"ace-icon fa fa-circle light-green\"></i> ';
					}
				}
				
				if( evtarray[2] !== undefined && evtarray[2] !== null ) 
				{
					$('#webphone-events').html( status_icon + evtarray[2] );
				}
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
				
				// $('.dial-pad-btn').addClass('disabled');
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
					start_call_timer();
					
					$('.dial-pad-btn').removeClass('disabled');
					
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
					stop_call_timer();
					
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
						url: yii.urls.absoluteUrl + '/agent/webphone/endCall',
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

	var agentCallLogSending = false;
		
		$(document).on("click", ".export-agent-call-log-btn", function(){

			if( !agentCallLogSending )
			{
				var this_button = $(this);
				
				agentCallLogSending = true;
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/exportAgentCallLog",
					type: "post",
					dataType: "json",
					data: { "ajax":1 },
					beforeSend: function(){ 
						this_button.text("Exporting. Please wait...");
					},
					error: function(){
						this_button.text("Export Error. Please try again later...");
					},
					complete: function() {

						agentCallLogSending = false;
					
						this_button.html("File is sent to your email <i class=\"fa fa-check\"></i>");
					},
				});
			}
		});
	
', CClientScript::POS_END);
?>

<?php	
$cs->registerScript(uniqid(), '

	$(document).ready(function () {
		$(document).on("click", ".next-lead-btn, .skip-call-btn", function(){

			var this_button = $(this);
		
			if( !this_button.hasClass("next-lead-btn-disabled") )
			{
				if( this_button.attr("skipCall") ) 
				{
					var lead_id = this_button.attr("lead_id");
					var data = { "ajax":1, "action": "nextLead", "skipCall": 1, "lead_id":lead_id };
				}
				else
				{
					var lead_hopper_id = this_button.attr("lead_hopper_id");
					var data = { "ajax":1, "action": "nextLead", "lead_hopper_id": lead_hopper_id };
				}
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/webphone/index",
					type: "post",
					dataType: "json",
					data: data,
					beforeSend: function(){ 
						if( this_button.attr("skipCall") ) 
						{
							this_button.removeClass("btn-primary");
							this_button.addClass("btn-grey");
							this_button.addClass("next-lead-btn-disabled");
							this_button.text("LOADING...");
						}
						else
						{
							this_button.addClass("next-lead-btn-disabled");
							this_button.find("span").text("LOADING...");
						}
						
						$("div.alert").remove();
						
						$("#dataTab").remove();
						$("#data").remove();
						
						$("#surveyTab").remove();
						$("#surveys").remove();
						
						$("#scriptTab").remove();
						$("#script").remove();
						
						$("#appointmentsTab").remove();
						$("#appointments").remove();
						
						$("#emailSettingTab").remove();
						$("#emailSetting").remove();
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
							
							$("#dialerTab").click();
						}
						else
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
						}
					},
				});
			}
		});
	});
	
', CClientScript::POS_END);
?>

<?php 
	$showSurveyTab = false;
	$showScriptTab = false;
	$showDataTab = false;

	
	//check appointment tab settings
	if( isset($leadHopperEntry->skill) && $leadHopperEntry->skill->enable_survey_tab == 1 )
	{
		$showSurveyTab = true;
	}
			
	//check script tab settings
	if( $leadHopperEntry->skill->enable_dialer_script_tab == 1 )
	{
		$showScriptTab = true;
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
					echo CHtml::link('<span>NEXT LEAD</span>', '#', array('class'=>'next-lead-btn next-lead-btn-disabled'));
					
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
					echo CHtml::link('<span>NEXT LEAD</span>', '#', array('class'=>'next-lead-btn next-lead-btn-disabled'));
					
					$this->renderPartial('_empty_leadInfo', array(
						'callerID' => $callerID
					));
				}
			?>
		</div>
		
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


<?php 
	$this->renderPartial('dialPad', array(
		'lead' => $lead,
		'list' => $list,
		'customer' => $customer,
		'callerID' => $callerID
	)); 
?>