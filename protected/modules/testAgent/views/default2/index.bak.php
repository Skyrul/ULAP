<?php 

$this->pageTitle = Yii::app()->name . ' - Dialer';

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/css/dialer.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } ');

$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/js/calendar/calendar.js',  CClientScript::POS_END);


$leadId = $lead != null ? $lead->id : '';
$calendarId = $calendar != null ? $calendar->id : '';
$customerId = $customer != null ? $customer->id : '';

$cs->registerScript(uniqid(), '

	var current_lead_id = "'.$leadId.'";
	var current_calendar_id = "'.$calendarId.'";
	var customer_id = "'.$customerId.'";
	var viewer = "agent";
	
', CClientScript::POS_HEAD);

$cs->registerScript(uniqid(), '
	$(document).ready(function () {
		
		setInterval(function(){ $("#calendar").fullCalendar("render"); }, 1000);

		$(".scrollable").each(function () {
			var $this = $(this);
			$(this).ace_scroll({
				size: $this.data("size") || 380,
			});
		});
		
		$(".num").click(function () {
			var num = $(this);
			var text = $.trim(num.find(".txt").clone().children().remove().end().text());
			var telNumber = $("#telNumber");
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
				$(location).attr("href", yii.urls.absoluteUrl + "/agent?office_id=" + office_id);
			}
		});
		
		$(document).on("click", ".load-calendar-btn", function(){
			
			var calendar_id = $("#calendar-select").val();
			
			if( calendar_id != "" )
			{
				$(location).attr("href", yii.urls.absoluteUrl + "/agent?calendar_id=" + calendar_id);
			}
		});
		
		$(document).on("click", ".lead-history-submit-btn", function(){
		
			if( $("#LeadHistory_note").val() != "" )
			{
				data = $("#leadHistoryForm").serialize();
				
				$.ajax({
					url: yii.urls.absoluteUrl + "/agent/default/createLeadHistory",
					type: "post",
					dataType: "json",
					data: data,
					success: function() { 

						$("#LeadHistory_note").val("");
					
						$.fn.yiiListView.update("leadHistoryList", {});
					}
				});
			}
			
		});
		
		$(document).on("click", ".next-lead-btn", function(e){
		
			e.preventDefault();

			if( $(this).hasClass("next-lead-btn-disabled") ) 
			{
				return false;
			}
			else
			{
				$(location).attr("href", $(this).prop("href"));
			}
			
		});
	
	});
	
', CClientScript::POS_END);
?>		
			
<div class="tabbable">
	<ul id="myTab" class="nav nav-tabs padding-12 tab-color-blue background-blue">
		<li class="active">
			<a href="#dialer" data-toggle="tab">
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
						'leadHistoryDataProvider' => $leadHistoryDataProvider,
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
	</div>

</div>


<button type="button" class="btn btn-info btn-xs calendar-details-btn" style="position: absolute; top: 0px; left: 0px; width: 40px; border-radius: 0px 15px 15px 0px; margin-top: 70px; padding: 8px;">
	<i class="ace-icon fa fa-arrow-left bigger-110 icon-only"></i>
</button>


<?php 
	$this->renderPartial('dialPad', array()); 
?>